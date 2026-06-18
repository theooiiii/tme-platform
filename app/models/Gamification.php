<?php

defined('BASE_PATH') || exit('Acesso direto não permitido.');

class Gamification extends Model
{
    public function ensureProfile(int $userId): void
    {
        $statement = $this->db->prepare(
            'INSERT IGNORE INTO gamification_profiles (
                user_id, xp, xp_total, level, internal_coins, streak_days, created_at, updated_at
             ) VALUES (
                :user_id, 0, 0, 1, 0, 0, NOW(), NOW()
             )'
        );
        $statement->execute(['user_id' => $userId]);
    }

    public function profile(int $userId): array
    {
        $this->ensureProfile($userId);

        $statement = $this->db->prepare('SELECT * FROM gamification_profiles WHERE user_id = :user_id LIMIT 1');
        $statement->execute(['user_id' => $userId]);

        return $statement->fetch() ?: [
            'user_id' => $userId,
            'xp' => 0,
            'xp_total' => 0,
            'level' => 1,
            'internal_coins' => 0,
            'streak_days' => 0,
            'last_activity_at' => null,
        ];
    }

    public function recordEvent(int $userId, string $action, string $referenceType, int $referenceId, int $xp, int $coins, array $context = []): bool
    {
        $statement = $this->db->prepare(
            'INSERT IGNORE INTO gamification_events (
                user_id, action, reference_type, reference_id, xp_awarded,
                coins_awarded, context, created_at
             ) VALUES (
                :user_id, :action, :reference_type, :reference_id, :xp_awarded,
                :coins_awarded, :context, NOW()
             )'
        );
        $statement->execute([
            'user_id' => $userId,
            'action' => $action,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'xp_awarded' => $xp,
            'coins_awarded' => $coins,
            'context' => json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        return $statement->rowCount() > 0;
    }

    public function addXp(int $userId, int $xp, int $coins): array
    {
        $profile = $this->profile($userId);
        $total = (int) ($profile['xp_total'] ?? $profile['xp'] ?? 0) + $xp;
        $level = max(1, (int) floor($total / 250) + 1);
        $streak = $this->nextStreak((int) ($profile['streak_days'] ?? 0), $profile['last_activity_at'] ?? null);

        $statement = $this->db->prepare(
            'UPDATE gamification_profiles
             SET xp = :xp,
                 xp_total = :xp_total,
                 level = :level,
                 internal_coins = internal_coins + :coins,
                 streak_days = :streak_days,
                 last_activity_at = NOW(),
                 updated_at = NOW()
             WHERE user_id = :user_id'
        );
        $statement->execute([
            'xp' => $total,
            'xp_total' => $total,
            'level' => $level,
            'coins' => $coins,
            'streak_days' => $streak,
            'user_id' => $userId,
        ]);

        return $this->profile($userId);
    }

    public function badgeBySlug(string $slug): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM badges WHERE slug = :slug LIMIT 1');
        $statement->execute(['slug' => $slug]);
        $badge = $statement->fetch();

        return $badge ?: null;
    }

    public function addBadge(int $userId, int $badgeId): bool
    {
        $statement = $this->db->prepare(
            'INSERT IGNORE INTO user_badges (user_id, badge_id, earned_at)
             VALUES (:user_id, :badge_id, NOW())'
        );
        $statement->execute([
            'user_id' => $userId,
            'badge_id' => $badgeId,
        ]);

        return $statement->rowCount() > 0;
    }

    public function badgesForUser(int $userId, int $limit = 50): array
    {
        $statement = $this->db->prepare(
            'SELECT badges.*, user_badges.earned_at
             FROM user_badges
             INNER JOIN badges ON badges.id = user_badges.badge_id
             WHERE user_badges.user_id = :user_id
             ORDER BY user_badges.earned_at DESC
             LIMIT ' . max(1, $limit)
        );
        $statement->execute(['user_id' => $userId]);

        return $statement->fetchAll();
    }

    public function ranking(?int $courseId = null): array
    {
        $where = ['users.status = "aprovado"'];
        $params = [];

        $join = 'INNER JOIN gamification_profiles ON gamification_profiles.user_id = users.id
                 INNER JOIN roles ON roles.id = users.role_id';

        if ($courseId) {
            $join .= ' INNER JOIN enrollments ON enrollments.user_id = users.id AND enrollments.course_id = :course_id';
            $params['course_id'] = $courseId;
        }

        $sql = 'SELECT users.id, users.full_name, roles.slug AS role_slug,
                       gamification_profiles.xp_total,
                       gamification_profiles.level,
                       gamification_profiles.internal_coins,
                       gamification_profiles.streak_days,
                       COUNT(DISTINCT user_badges.badge_id) AS badges_count
                FROM users
                ' . $join . '
                LEFT JOIN user_badges ON user_badges.user_id = users.id
                WHERE ' . implode(' AND ', $where) . '
                GROUP BY users.id, users.full_name, roles.slug, gamification_profiles.id,
                         gamification_profiles.xp_total, gamification_profiles.level,
                         gamification_profiles.internal_coins, gamification_profiles.streak_days
                ORDER BY gamification_profiles.xp_total DESC, gamification_profiles.level DESC, users.full_name ASC
                LIMIT 100';

        $statement = $this->db->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function coursesForRanking(): array
    {
        $statement = $this->db->query(
            'SELECT DISTINCT courses.id, courses.title
             FROM courses
             INNER JOIN enrollments ON enrollments.course_id = courses.id
             ORDER BY courses.title'
        );

        return $statement->fetchAll();
    }

    public function countsForUser(int $userId): array
    {
        $queries = [
            'enrollments' => 'SELECT COUNT(*) FROM enrollments WHERE user_id = :user_id',
            'completed_courses' => 'SELECT COUNT(*) FROM enrollments WHERE user_id = :user_id AND status = "concluida"',
            'completed_lessons' => 'SELECT COUNT(*) FROM lesson_progress WHERE user_id = :user_id AND status = "concluida"',
            'favorites' => 'SELECT COUNT(*) FROM library_favorites WHERE user_id = :user_id',
        ];

        $counts = [];

        foreach ($queries as $key => $sql) {
            $statement = $this->db->prepare($sql);
            $statement->execute(['user_id' => $userId]);
            $counts[$key] = (int) $statement->fetchColumn();
        }

        return $counts;
    }

    private function nextStreak(int $current, ?string $lastActivityAt): int
    {
        if (! $lastActivityAt) {
            return 1;
        }

        $last = date('Y-m-d', strtotime($lastActivityAt));
        $today = date('Y-m-d');

        if ($last === $today) {
            return max(1, $current);
        }

        if ($last === date('Y-m-d', strtotime('-1 day'))) {
            return $current + 1;
        }

        return 1;
    }
}
