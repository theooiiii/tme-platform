<?php

defined('BASE_PATH') || exit('Acesso direto nao permitido.');

class LibraryItem extends Model
{
    public function visible(array $filters = [], ?array $user = null): array
    {
        $where = ['library_items.status = "publicado"'];
        $params = [];
        $visibility = ['library_items.visibility = "publica"'];

        if ($user) {
            $visibility[] = 'library_items.visibility = "logados"';
            $visibility[] = 'library_items.visibility = "curso" AND EXISTS (
                SELECT 1 FROM enrollments
                WHERE enrollments.course_id = library_items.course_id
                  AND enrollments.user_id = :viewer_id
                  AND enrollments.status IN ("ativa", "concluida")
            )';
            $params['viewer_id'] = (int) $user['id'];
            $params['favorite_viewer_id'] = (int) $user['id'];

            if (in_array($user['role_slug'], ['administrador', 'supervisor'], true)) {
                $visibility[] = 'library_items.visibility = "privada_admin"';
            }
        }

        $where[] = '(' . implode(' OR ', $visibility) . ')';
        $this->applyFilters($where, $params, $filters);

        $sql = $this->baseSelect($user) . ' WHERE ' . implode(' AND ', $where) . ' ORDER BY library_items.created_at DESC';
        $statement = $this->db->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function adminList(array $filters = [], array $user = []): array
    {
        $where = [];
        $params = [];

        if (! empty($user['id'])) {
            $params['favorite_viewer_id'] = (int) $user['id'];
        }

        if (($user['role_slug'] ?? '') === 'professor') {
            $where[] = 'library_items.owner_id = :owner_id';
            $params['owner_id'] = (int) $user['id'];
        }

        if (! empty($filters['status'])) {
            $where[] = 'library_items.status = :status';
            $params['status'] = $filters['status'];
        }

        $this->applyFilters($where, $params, $filters);

        $sql = $this->baseSelect($user) . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY library_items.created_at DESC';
        $statement = $this->db->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function favorites(int $userId): array
    {
        $statement = $this->db->prepare(
            $this->baseSelect(['id' => $userId]) . '
             INNER JOIN library_favorites fav_filter ON fav_filter.library_item_id = library_items.id
                AND fav_filter.user_id = :user_id
             WHERE library_items.status = "publicado"
             ORDER BY fav_filter.created_at DESC'
        );
        $statement->execute(['user_id' => $userId, 'favorite_viewer_id' => $userId]);

        return $statement->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->db->prepare($this->baseSelect(null) . ' WHERE library_items.id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $item = $statement->fetch();

        return $item ?: null;
    }

    public function findVisible(int $id, ?array $user): ?array
    {
        $items = $this->visible(['id' => $id], $user);

        return $items[0] ?? null;
    }

    public function create(array $data): int
    {
        $statement = $this->db->prepare(
            'INSERT INTO library_items (
                owner_id, course_id, class_id, title, description, category, subject,
                item_type, visibility, author, file_path, external_url, cover_path,
                status, created_at, updated_at
             ) VALUES (
                :owner_id, :course_id, :class_id, :title, :description, :category, :subject,
                :item_type, :visibility, :author, :file_path, :external_url, :cover_path,
                :status, NOW(), NOW()
             )'
        );
        $statement->execute($this->writeParams($data));

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $params = $this->writeParams($data);
        $params['id'] = $id;

        $statement = $this->db->prepare(
            'UPDATE library_items
             SET course_id = :course_id,
                 class_id = :class_id,
                 title = :title,
                 description = :description,
                 category = :category,
                 subject = :subject,
                 item_type = :item_type,
                 visibility = :visibility,
                 author = :author,
                 file_path = COALESCE(:file_path, file_path),
                 external_url = :external_url,
                 cover_path = COALESCE(:cover_path, cover_path),
                 status = :status,
                 updated_at = NOW()
             WHERE id = :id'
        );
        $statement->execute($params);
    }

    public function setModeration(int $id, int $userId, string $status, string $notes = ''): void
    {
        $statement = $this->db->prepare(
            'UPDATE library_items
             SET status = :status,
                 approved_by = :approved_by,
                 approved_at = CASE WHEN :published_status = "publicado" THEN NOW() ELSE approved_at END,
                 moderation_notes = :notes,
                 updated_at = NOW()
             WHERE id = :id'
        );
        $statement->execute([
            'status' => $status,
            'published_status' => $status,
            'approved_by' => $userId,
            'notes' => $notes ?: null,
            'id' => $id,
        ]);
    }

    public function toggleFavorite(int $itemId, int $userId): bool
    {
        if ($this->isFavorite($itemId, $userId)) {
            $statement = $this->db->prepare('DELETE FROM library_favorites WHERE library_item_id = :item_id AND user_id = :user_id');
            $statement->execute(['item_id' => $itemId, 'user_id' => $userId]);
            return false;
        }

        $statement = $this->db->prepare(
            'INSERT INTO library_favorites (library_item_id, user_id, created_at)
             VALUES (:item_id, :user_id, NOW())'
        );
        $statement->execute(['item_id' => $itemId, 'user_id' => $userId]);

        return true;
    }

    public function recordAccess(int $itemId, ?int $userId): void
    {
        $statement = $this->db->prepare(
            'INSERT INTO library_access_logs (library_item_id, user_id, ip_address, user_agent, accessed_at)
             VALUES (:item_id, :user_id, :ip_address, :user_agent, NOW())'
        );
        $statement->execute([
            'item_id' => $itemId,
            'user_id' => $userId,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
        ]);
    }

    public function categories(): array
    {
        return array_column($this->db->query(
            'SELECT DISTINCT category FROM library_items WHERE category IS NOT NULL AND category <> "" ORDER BY category'
        )->fetchAll(), 'category');
    }

    public function subjects(): array
    {
        return array_column($this->db->query(
            'SELECT DISTINCT subject FROM library_items WHERE subject IS NOT NULL AND subject <> "" ORDER BY subject'
        )->fetchAll(), 'subject');
    }

    public function isFavorite(int $itemId, int $userId): bool
    {
        $statement = $this->db->prepare(
            'SELECT 1 FROM library_favorites WHERE library_item_id = :item_id AND user_id = :user_id LIMIT 1'
        );
        $statement->execute(['item_id' => $itemId, 'user_id' => $userId]);

        return (bool) $statement->fetchColumn();
    }

    private function applyFilters(array &$where, array &$params, array $filters): void
    {
        if (! empty($filters['id'])) {
            $where[] = 'library_items.id = :id';
            $params['id'] = (int) $filters['id'];
        }

        if (! empty($filters['q'])) {
            $where[] = '(library_items.title LIKE :q OR library_items.description LIKE :q OR library_items.author LIKE :q)';
            $params['q'] = '%' . $filters['q'] . '%';
        }

        if (! empty($filters['category'])) {
            $where[] = 'library_items.category = :category';
            $params['category'] = $filters['category'];
        }

        if (! empty($filters['subject'])) {
            $where[] = 'library_items.subject = :subject';
            $params['subject'] = $filters['subject'];
        }

        if (! empty($filters['type'])) {
            $where[] = 'library_items.item_type = :type';
            $params['type'] = $filters['type'];
        }
    }

    private function baseSelect(?array $user): string
    {
        $favoriteSelect = $user
            ? 'EXISTS (SELECT 1 FROM library_favorites WHERE library_favorites.library_item_id = library_items.id AND library_favorites.user_id = :favorite_viewer_id) AS is_favorite,'
            : '0 AS is_favorite,';

        return 'SELECT library_items.*,
                       owner.full_name AS owner_name,
                       approver.full_name AS approver_name,
                       courses.title AS course_title,
                       ' . $favoriteSelect . '
                       (SELECT COUNT(*) FROM library_access_logs WHERE library_access_logs.library_item_id = library_items.id) AS access_count
                FROM library_items
                LEFT JOIN users owner ON owner.id = library_items.owner_id
                LEFT JOIN users approver ON approver.id = library_items.approved_by
                LEFT JOIN courses ON courses.id = library_items.course_id';
    }

    private function writeParams(array $data): array
    {
        return [
            'owner_id' => $data['owner_id'] ?: null,
            'course_id' => $data['course_id'] ?: null,
            'class_id' => $data['class_id'] ?: null,
            'title' => $data['title'],
            'description' => $data['description'] ?: null,
            'category' => $data['category'] ?: null,
            'subject' => $data['subject'] ?: null,
            'item_type' => $data['item_type'],
            'visibility' => $data['visibility'],
            'author' => $data['author'] ?: null,
            'file_path' => $data['file_path'] ?: null,
            'external_url' => $data['external_url'] ?: null,
            'cover_path' => $data['cover_path'] ?: null,
            'status' => $data['status'],
        ];
    }
}
