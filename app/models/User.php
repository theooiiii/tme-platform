<?php

defined('BASE_PATH') || exit('Acesso direto nao permitido.');

class User extends Model
{
    public function findById(int $id): ?array
    {
        $statement = $this->db->prepare(
            'SELECT users.*, roles.slug AS role_slug, roles.name AS role_name,
                    user_settings.theme, user_settings.primary_color,
                    institutions.name AS institution_name
             FROM users
             INNER JOIN roles ON roles.id = users.role_id
             LEFT JOIN user_settings ON user_settings.user_id = users.id
             LEFT JOIN institutions ON institutions.id = users.institution_id
             WHERE users.id = :id
             LIMIT 1'
        );

        $statement->execute(['id' => $id]);
        $user = $statement->fetch();

        return $user ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $statement = $this->db->prepare(
            'SELECT users.*, roles.slug AS role_slug, roles.name AS role_name
             FROM users
             INNER JOIN roles ON roles.id = users.role_id
             WHERE users.email = :email
             LIMIT 1'
        );

        $statement->execute(['email' => strtolower(trim($email))]);
        $user = $statement->fetch();

        return $user ?: null;
    }

    public function emailExists(string $email): bool
    {
        $statement = $this->db->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
        $statement->execute(['email' => strtolower(trim($email))]);

        return (bool) $statement->fetchColumn();
    }

    public function createPending(array $data): int
    {
        $roleId = $this->roleIdBySlug($data['account_type']);
        $institutionId = null;

        $this->db->beginTransaction();

        try {
            if (! $data['is_independent'] && ! empty($data['institution'])) {
                $institutionId = (new Institution())->findOrCreateManual(
                    $data['institution'],
                    $data['city'] ?: null,
                    $data['state'] ?: null
                );
            }

            $statement = $this->db->prepare(
                'INSERT INTO users (
                    role_id, institution_id, full_name, email, password_hash, phone, cpf,
                    birth_date, state, city, is_independent, interest_area, platform_goal,
                    terms_accepted_at, status, created_at, updated_at
                 ) VALUES (
                    :role_id, :institution_id, :full_name, :email, :password_hash, :phone, :cpf,
                    :birth_date, :state, :city, :is_independent, :interest_area, :platform_goal,
                    NOW(), :status, NOW(), NOW()
                 )'
            );

            $statement->execute([
                'role_id' => $roleId,
                'institution_id' => $institutionId,
                'full_name' => $data['full_name'],
                'email' => strtolower($data['email']),
                'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
                'phone' => $data['phone'],
                'cpf' => $data['cpf'],
                'birth_date' => $data['birth_date'],
                'state' => strtoupper($data['state']),
                'city' => $data['city'],
                'is_independent' => $data['is_independent'] ? 1 : 0,
                'interest_area' => $data['interest_area'],
                'platform_goal' => $data['platform_goal'],
                'status' => 'pendente',
            ]);

            $userId = (int) $this->db->lastInsertId();
            $this->createDefaultSettings($userId);
            $this->createGamificationProfile($userId);
            $this->db->commit();

            return $userId;
        } catch (Throwable $exception) {
            $this->db->rollBack();
            throw $exception;
        }
    }

    public function pendingAccounts(): array
    {
        $statement = $this->db->query(
            'SELECT users.id, users.full_name, users.email, users.phone, users.cpf,
                    users.birth_date, users.state, users.city, users.interest_area,
                    users.platform_goal, users.is_independent, users.created_at,
                    roles.name AS role_name, roles.slug AS role_slug,
                    institutions.name AS institution_name
             FROM users
             INNER JOIN roles ON roles.id = users.role_id
             LEFT JOIN institutions ON institutions.id = users.institution_id
             WHERE users.status = "pendente"
             ORDER BY users.created_at ASC'
        );

        return $statement->fetchAll();
    }

    public function approve(int $userId, int $approverId): bool
    {
        $statement = $this->db->prepare(
            'UPDATE users
             SET status = :status, approved_by = :approved_by, approved_at = NOW(),
                 rejection_reason = NULL, updated_at = NOW()
             WHERE id = :id AND status = "pendente"'
        );

        $statement->execute([
            'status' => 'aprovado',
            'approved_by' => $approverId,
            'id' => $userId,
        ]);

        return $statement->rowCount() > 0;
    }

    public function reject(int $userId, int $approverId, string $reason = ''): bool
    {
        $statement = $this->db->prepare(
            'UPDATE users
             SET status = :status, approved_by = :approved_by, approved_at = NOW(),
                 rejection_reason = :reason, updated_at = NOW()
             WHERE id = :id AND status = "pendente"'
        );

        $statement->execute([
            'status' => 'recusado',
            'approved_by' => $approverId,
            'reason' => $reason ?: null,
            'id' => $userId,
        ]);

        return $statement->rowCount() > 0;
    }

    public function markLastLogin(int $userId): void
    {
        $statement = $this->db->prepare('UPDATE users SET last_login_at = NOW() WHERE id = :id');
        $statement->execute(['id' => $userId]);
    }

    public function updateSettings(int $userId, string $theme, string $primaryColor): void
    {
        $statement = $this->db->prepare(
            'INSERT INTO user_settings (user_id, theme, primary_color, created_at, updated_at)
             VALUES (:user_id, :theme, :primary_color, NOW(), NOW())
             ON DUPLICATE KEY UPDATE theme = VALUES(theme), primary_color = VALUES(primary_color), updated_at = NOW()'
        );

        $statement->execute([
            'user_id' => $userId,
            'theme' => $theme,
            'primary_color' => $primaryColor,
        ]);
    }

    public function dashboardCounts(): array
    {
        return [
            'pending_users' => (int) $this->db->query('SELECT COUNT(*) FROM users WHERE status = "pendente"')->fetchColumn(),
            'approved_users' => (int) $this->db->query('SELECT COUNT(*) FROM users WHERE status = "aprovado"')->fetchColumn(),
            'courses' => (int) $this->db->query('SELECT COUNT(*) FROM courses')->fetchColumn(),
            'enrollments' => (int) $this->db->query('SELECT COUNT(*) FROM enrollments')->fetchColumn(),
            'events' => (int) $this->db->query('SELECT COUNT(*) FROM events')->fetchColumn(),
        ];
    }

    private function roleIdBySlug(string $slug): int
    {
        $statement = $this->db->prepare('SELECT id FROM roles WHERE slug = :slug LIMIT 1');
        $statement->execute(['slug' => $slug]);
        $roleId = $statement->fetchColumn();

        if (! $roleId) {
            throw new RuntimeException('Tipo de conta inválido.');
        }

        return (int) $roleId;
    }

    private function createDefaultSettings(int $userId): void
    {
        $statement = $this->db->prepare(
            'INSERT INTO user_settings (user_id, theme, primary_color, created_at, updated_at)
             VALUES (:user_id, :theme, :primary_color, NOW(), NOW())'
        );

        $statement->execute([
            'user_id' => $userId,
            'theme' => config('app.default_theme', 'light'),
            'primary_color' => config('app.default_primary_color', '#1f6feb'),
        ]);
    }

    private function createGamificationProfile(int $userId): void
    {
        $statement = $this->db->prepare(
            'INSERT INTO gamification_profiles (user_id, xp, level, internal_coins, created_at, updated_at)
             VALUES (:user_id, 0, 1, 0, NOW(), NOW())'
        );

        $statement->execute(['user_id' => $userId]);
    }
}
