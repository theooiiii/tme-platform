<?php

defined('BASE_PATH') || exit('Acesso direto nao permitido.');

class AdminRepository extends BaseRepository
{
    public function overview(): array
    {
        return [
            'users_total' => $this->count('users'),
            'users_active' => $this->count('users', 'status = "aprovado"'),
            'pending_users' => $this->count('users', 'status = "pendente"'),
            'courses_published' => $this->count('courses', 'status = "publicado"'),
            'enrollments_active' => $this->count('enrollments', 'status = "ativa"'),
            'enrollments_completed' => $this->count('enrollments', 'status = "concluida"'),
            'certificates_valid' => $this->count('certificates', 'validation_status = "valido"'),
            'revenue_paid' => $this->sum('transactions', 'amount', 'status = "pago"'),
            'activity_24h' => $this->count('logs', 'created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)'),
        ];
    }

    public function users(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $where = [];
        $params = [];

        if (! empty($filters['q'])) {
            $where[] = '(users.full_name LIKE :q OR users.email LIKE :q OR users.cpf LIKE :q)';
            $params['q'] = '%' . $filters['q'] . '%';
        }

        if (! empty($filters['status'])) {
            $where[] = 'users.status = :status';
            $params['status'] = $filters['status'];
        }

        if (! empty($filters['role'])) {
            $where[] = 'roles.slug = :role';
            $params['role'] = $filters['role'];
        }

        $whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';
        $perPage = $this->limit($perPage);
        $offset = $this->offset($page, $perPage);

        $count = $this->db->prepare(
            'SELECT COUNT(*)
             FROM users
             INNER JOIN roles ON roles.id = users.role_id' . $whereSql
        );
        $count->execute($params);

        $statement = $this->db->prepare(
            'SELECT users.id, users.full_name, users.email, users.phone, users.status,
                    users.created_at, users.last_login_at, roles.slug AS role_slug,
                    roles.name AS role_name, institutions.name AS institution_name
             FROM users
             INNER JOIN roles ON roles.id = users.role_id
             LEFT JOIN institutions ON institutions.id = users.institution_id' . $whereSql . '
             ORDER BY users.created_at DESC
             LIMIT ' . $perPage . ' OFFSET ' . $offset
        );
        $statement->execute($params);

        return [
            'data' => $statement->fetchAll(),
            'total' => (int) $count->fetchColumn(),
            'page' => $page,
            'per_page' => $perPage,
        ];
    }

    public function updateUser(int $userId, string $roleSlug, string $status): bool
    {
        $roleId = $this->roleId($roleSlug);

        if (! $roleId || ! in_array($status, config('app.account_statuses', []), true)) {
            return false;
        }

        $statement = $this->db->prepare(
            'UPDATE users
             SET role_id = :role_id, status = :status, updated_at = NOW()
             WHERE id = :id'
        );
        $statement->execute([
            'role_id' => $roleId,
            'status' => $status,
            'id' => $userId,
        ]);

        return $statement->rowCount() >= 0;
    }

    public function roles(): array
    {
        $statement = $this->db->query('SELECT * FROM roles ORDER BY id');

        return $statement->fetchAll();
    }

    public function permissions(): array
    {
        $statement = $this->db->query(
            'SELECT roles.name AS role_name, roles.slug AS role_slug,
                    permissions.name AS permission_name, permissions.slug AS permission_slug
             FROM roles
             LEFT JOIN role_permissions ON role_permissions.role_id = roles.id
             LEFT JOIN permissions ON permissions.id = role_permissions.permission_id
             ORDER BY roles.id, permissions.name'
        );

        $grouped = [];

        foreach ($statement->fetchAll() as $row) {
            $slug = $row['role_slug'];
            $grouped[$slug] ??= [
                'role_name' => $row['role_name'],
                'role_slug' => $slug,
                'permissions' => [],
            ];

            if (! empty($row['permission_slug'])) {
                $grouped[$slug]['permissions'][] = [
                    'name' => $row['permission_name'],
                    'slug' => $row['permission_slug'],
                ];
            }
        }

        return array_values($grouped);
    }

    public function courseCategories(): array
    {
        $statement = $this->db->query(
            'SELECT category, COUNT(*) AS courses_count,
                    SUM(status = "publicado") AS published_count
             FROM courses
             WHERE category IS NOT NULL AND category <> ""
             GROUP BY category
             ORDER BY category'
        );

        return $statement->fetchAll();
    }

    public function renameCourseCategory(string $current, string $new): int
    {
        $statement = $this->db->prepare(
            'UPDATE courses
             SET category = :new_category, updated_at = NOW()
             WHERE category = :current_category'
        );
        $statement->execute([
            'new_category' => $new,
            'current_category' => $current,
        ]);

        return $statement->rowCount();
    }

    public function logs(array $filters = [], int $page = 1, int $perPage = 30): array
    {
        $where = [];
        $params = [];

        if (! empty($filters['level'])) {
            $where[] = 'logs.level = :level';
            $params['level'] = $filters['level'];
        }

        if (! empty($filters['q'])) {
            $where[] = '(logs.action LIKE :q OR logs.context LIKE :q OR users.full_name LIKE :q)';
            $params['q'] = '%' . $filters['q'] . '%';
        }

        $whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';
        $perPage = $this->limit($perPage, 100);
        $offset = $this->offset($page, $perPage);

        $count = $this->db->prepare('SELECT COUNT(*) FROM logs LEFT JOIN users ON users.id = logs.user_id' . $whereSql);
        $count->execute($params);

        $statement = $this->db->prepare(
            'SELECT logs.*, users.full_name AS user_name, users.email AS user_email
             FROM logs
             LEFT JOIN users ON users.id = logs.user_id' . $whereSql . '
             ORDER BY logs.created_at DESC
             LIMIT ' . $perPage . ' OFFSET ' . $offset
        );
        $statement->execute($params);

        return [
            'data' => $statement->fetchAll(),
            'total' => (int) $count->fetchColumn(),
            'page' => $page,
            'per_page' => $perPage,
        ];
    }

    public function globalSearch(string $term): array
    {
        $term = trim($term);

        if ($term === '') {
            return [];
        }

        $like = '%' . $term . '%';

        return [
            'users' => $this->searchRows(
                'SELECT id, full_name AS title, email AS subtitle, status
                 FROM users
                 WHERE full_name LIKE :q OR email LIKE :q OR cpf LIKE :q
                 ORDER BY created_at DESC
                 LIMIT 8',
                $like
            ),
            'courses' => $this->searchRows(
                'SELECT id, title, category AS subtitle, status
                 FROM courses
                 WHERE title LIKE :q OR description LIKE :q OR category LIKE :q
                 ORDER BY created_at DESC
                 LIMIT 8',
                $like
            ),
            'library' => $this->searchRows(
                'SELECT id, title, category AS subtitle, status
                 FROM library_items
                 WHERE title LIKE :q OR description LIKE :q OR category LIKE :q OR discipline LIKE :q
                 ORDER BY created_at DESC
                 LIMIT 8',
                $like
            ),
            'events' => $this->searchRows(
                'SELECT id, title, event_type AS subtitle, status
                 FROM events
                 WHERE title LIKE :q OR description LIKE :q OR event_type LIKE :q
                 ORDER BY starts_at DESC
                 LIMIT 8',
                $like
            ),
            'certificates' => $this->searchRows(
                'SELECT id, code AS title, title AS subtitle, validation_status AS status
                 FROM certificates
                 WHERE code LIKE :q OR title LIKE :q
                 ORDER BY issued_at DESC
                 LIMIT 8',
                $like
            ),
        ];
    }

    private function count(string $table, string $where = '1 = 1'): int
    {
        try {
            return (int) $this->db->query('SELECT COUNT(*) FROM ' . $table . ' WHERE ' . $where)->fetchColumn();
        } catch (PDOException) {
            return 0;
        }
    }

    private function sum(string $table, string $column, string $where = '1 = 1'): float
    {
        try {
            return (float) $this->db->query('SELECT COALESCE(SUM(' . $column . '), 0) FROM ' . $table . ' WHERE ' . $where)->fetchColumn();
        } catch (PDOException) {
            return 0.0;
        }
    }

    private function roleId(string $slug): ?int
    {
        $statement = $this->db->prepare('SELECT id FROM roles WHERE slug = :slug LIMIT 1');
        $statement->execute(['slug' => $slug]);
        $id = $statement->fetchColumn();

        return $id ? (int) $id : null;
    }

    private function searchRows(string $sql, string $like): array
    {
        try {
            $statement = $this->db->prepare($sql);
            $statement->execute(['q' => $like]);

            return $statement->fetchAll();
        } catch (PDOException) {
            return [];
        }
    }
}
