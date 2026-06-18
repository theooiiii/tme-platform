<?php

defined('BASE_PATH') || exit('Acesso direto não permitido.');

class GamificationService
{
    private const ACTION_XP = [
        'auth.first_login' => 25,
        'enrollment.created' => 40,
        'lesson.completed' => 15,
        'course.completed' => 100,
        'activity.submitted' => 20,
        'activity.good_grade' => 60,
        'library.favorite.added' => 10,
        'certificate.issued' => 50,
    ];

    private Gamification $gamification;
    private ActionLog $logs;

    public function __construct()
    {
        $this->gamification = new Gamification();
        $this->logs = new ActionLog();
    }

    public function ensureProfile(int $userId): void
    {
        $this->gamification->ensureProfile($userId);
    }

    public function firstLogin(int $userId): void
    {
        $this->grant($userId, 'auth.first_login', 'user', $userId);
        $this->awardBadge($userId, 'primeiro-login');
    }

    public function enrollmentCreated(int $userId, int $enrollmentId, int $courseId): void
    {
        $this->grant($userId, 'enrollment.created', 'course', $courseId, ['enrollment_id' => $enrollmentId]);
        $this->awardBadge($userId, 'primeiro-curso');
        $this->syncMilestones($userId);
    }

    public function lessonCompleted(int $userId, int $lessonId, int $enrollmentId): void
    {
        $this->grant($userId, 'lesson.completed', 'lesson', $lessonId, ['enrollment_id' => $enrollmentId]);
        $this->awardBadge($userId, 'primeira-aula-concluida');
        $this->syncMilestones($userId);
    }

    public function courseCompleted(int $userId, int $enrollmentId, int $courseId): void
    {
        $this->grant($userId, 'course.completed', 'enrollment', $enrollmentId, ['course_id' => $courseId]);
        $this->awardBadge($userId, 'curso-finalizado');
        $this->syncMilestones($userId);
    }

    public function activitySubmitted(int $userId, int $submissionId, int $activityId): void
    {
        $this->grant($userId, 'activity.submitted', 'submission', $submissionId, ['activity_id' => $activityId]);
        $this->syncMilestones($userId);
    }

    public function activityGraded(int $studentId, int $submissionId, float $score, float $maxScore): void
    {
        if ($maxScore <= 0 || ($score / $maxScore) < 0.8) {
            return;
        }

        $this->grant($studentId, 'activity.good_grade', 'submission', $submissionId, [
            'score' => $score,
            'max_score' => $maxScore,
        ]);
        $this->syncMilestones($studentId);
    }

    public function libraryFavoriteAdded(int $userId, int $itemId): void
    {
        $this->grant($userId, 'library.favorite.added', 'library_item', $itemId);
        $this->awardBadge($userId, 'explorador-biblioteca');
        $this->syncMilestones($userId);
    }

    public function certificateIssued(int $userId, int $certificateId, int $courseId): void
    {
        $this->grant($userId, 'certificate.issued', 'certificate', $certificateId, ['course_id' => $courseId]);
        $this->syncMilestones($userId);
    }

    public function syncMilestones(int $userId): void
    {
        $counts = $this->gamification->countsForUser($userId);

        if ($counts['enrollments'] > 0) {
            $this->awardBadge($userId, 'primeiro-curso');
        }

        if ($counts['completed_lessons'] > 0) {
            $this->awardBadge($userId, 'primeira-aula-concluida');
        }

        if ($counts['completed_courses'] > 0) {
            $this->awardBadge($userId, 'curso-finalizado');
        }

        if ($counts['favorites'] > 0) {
            $this->awardBadge($userId, 'explorador-biblioteca');
        }

        if ($counts['completed_lessons'] >= 5) {
            $this->awardBadge($userId, 'aluno-dedicado');
        }
    }

    private function grant(int $userId, string $action, string $referenceType, int $referenceId, array $context = []): bool
    {
        $xp = self::ACTION_XP[$action] ?? 0;

        if ($xp <= 0) {
            return false;
        }

        $coins = max(1, (int) floor($xp / 10));
        $inserted = $this->gamification->recordEvent($userId, $action, $referenceType, $referenceId, $xp, $coins, $context);

        if (! $inserted) {
            return false;
        }

        $profile = $this->gamification->addXp($userId, $xp, $coins);
        $this->logs->record($userId, 'gamification.xp_awarded', [
            'action' => $action,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'xp' => $xp,
            'coins' => $coins,
            'level' => (int) $profile['level'],
        ]);

        return true;
    }

    private function awardBadge(int $userId, string $slug): bool
    {
        $badge = $this->gamification->badgeBySlug($slug);

        if (! $badge) {
            return false;
        }

        if (! $this->gamification->addBadge($userId, (int) $badge['id'])) {
            return false;
        }

        $this->logs->record($userId, 'gamification.badge_awarded', [
            'badge_id' => (int) $badge['id'],
            'badge' => $badge['slug'],
        ]);
        (new NotificationService())->badgeAwarded($userId, (string) $badge['name']);

        $reward = (int) ($badge['xp_reward'] ?? 0);

        if ($reward > 0) {
            $this->gamification->recordEvent($userId, 'badge.' . $badge['slug'], 'badge', (int) $badge['id'], $reward, 0, []);
            $this->gamification->addXp($userId, $reward, 0);
        }

        return true;
    }
}
