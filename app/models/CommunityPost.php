<?php

defined('BASE_PATH') || exit('Acesso direto não permitido.');

class CommunityPost extends Model
{
    public function feed(?int $viewerId = null): array
    {
        $sql = $this->baseSelect((bool) $viewerId) . '
                WHERE posts.status = "aprovado"
                ORDER BY posts.is_featured DESC, posts.created_at DESC';

        $statement = $this->db->prepare($sql);
        $statement->execute($viewerId ? [
            'viewer_like_id' => $viewerId,
            'viewer_save_id' => $viewerId,
        ] : []);

        return $statement->fetchAll();
    }

    public function forUser(int $userId): array
    {
        $statement = $this->db->prepare(
            $this->baseSelect(false) . '
             WHERE posts.user_id = :user_id
             ORDER BY posts.created_at DESC
             LIMIT 8'
        );
        $statement->execute(['user_id' => $userId]);

        return $statement->fetchAll();
    }

    public function adminList(array $filters = []): array
    {
        $where = [];
        $params = [];

        if (! empty($filters['status'])) {
            $where[] = 'posts.status = :status';
            $params['status'] = $filters['status'];
        }

        if (! empty($filters['type'])) {
            $where[] = 'posts.post_type = :type';
            $params['type'] = $filters['type'];
        }

        $sql = $this->baseSelect(false) . ($where ? ' WHERE ' . implode(' AND ', $where) : '');
        $sql .= ' ORDER BY posts.created_at DESC';

        $statement = $this->db->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function find(int $id, ?int $viewerId = null, bool $approvedOnly = true): ?array
    {
        $where = ['posts.id = :id'];
        $params = ['id' => $id];

        if ($approvedOnly) {
            $where[] = 'posts.status = "aprovado"';
        }

        if ($viewerId) {
            $params['viewer_like_id'] = $viewerId;
            $params['viewer_save_id'] = $viewerId;
        }

        $statement = $this->db->prepare($this->baseSelect((bool) $viewerId) . ' WHERE ' . implode(' AND ', $where) . ' LIMIT 1');
        $statement->execute($params);
        $post = $statement->fetch();

        return $post ?: null;
    }

    public function create(array $data): int
    {
        $statement = $this->db->prepare(
            'INSERT INTO posts (
                user_id, post_type, title, content, visibility, status, is_featured,
                created_at, updated_at
             ) VALUES (
                :user_id, :post_type, :title, :content, "publico", :status, 0,
                NOW(), NOW()
             )'
        );
        $statement->execute([
            'user_id' => $data['user_id'],
            'post_type' => $data['post_type'],
            'title' => $data['title'],
            'content' => $data['content'],
            'status' => $data['status'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function comments(int $postId): array
    {
        $statement = $this->db->prepare(
            'SELECT comments.*, users.full_name, roles.slug AS role_slug
             FROM comments
             INNER JOIN users ON users.id = comments.user_id
             INNER JOIN roles ON roles.id = users.role_id
             WHERE comments.post_id = :post_id AND comments.status = "aprovado"
             ORDER BY comments.created_at ASC'
        );
        $statement->execute(['post_id' => $postId]);

        return $statement->fetchAll();
    }

    public function addComment(int $postId, int $userId, string $content): int
    {
        $statement = $this->db->prepare(
            'INSERT INTO comments (post_id, user_id, content, status, created_at, updated_at)
             VALUES (:post_id, :user_id, :content, "aprovado", NOW(), NOW())'
        );
        $statement->execute([
            'post_id' => $postId,
            'user_id' => $userId,
            'content' => $content,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function toggleLike(int $postId, int $userId): bool
    {
        if ($this->hasLike($postId, $userId)) {
            $statement = $this->db->prepare('DELETE FROM post_likes WHERE post_id = :post_id AND user_id = :user_id');
            $statement->execute(['post_id' => $postId, 'user_id' => $userId]);
            return false;
        }

        $statement = $this->db->prepare('INSERT INTO post_likes (post_id, user_id, created_at) VALUES (:post_id, :user_id, NOW())');
        $statement->execute(['post_id' => $postId, 'user_id' => $userId]);

        return true;
    }

    public function toggleSave(int $postId, int $userId): bool
    {
        if ($this->hasSave($postId, $userId)) {
            $statement = $this->db->prepare('DELETE FROM post_saves WHERE post_id = :post_id AND user_id = :user_id');
            $statement->execute(['post_id' => $postId, 'user_id' => $userId]);
            return false;
        }

        $statement = $this->db->prepare('INSERT INTO post_saves (post_id, user_id, created_at) VALUES (:post_id, :user_id, NOW())');
        $statement->execute(['post_id' => $postId, 'user_id' => $userId]);

        return true;
    }

    public function moderate(int $id, int $moderatorId, string $status, string $reason = ''): void
    {
        $statement = $this->db->prepare(
            'UPDATE posts
             SET status = :status,
                 moderation_reason = :reason,
                 moderated_by = :moderator_id,
                 moderated_at = NOW(),
                 archived_at = CASE WHEN :archive_status = "arquivado" THEN NOW() ELSE archived_at END,
                 updated_at = NOW()
             WHERE id = :id'
        );
        $statement->execute([
            'status' => $status,
            'reason' => $reason ?: null,
            'moderator_id' => $moderatorId,
            'archive_status' => $status,
            'id' => $id,
        ]);
    }

    public function toggleFeatured(int $id): void
    {
        $statement = $this->db->prepare('UPDATE posts SET is_featured = IF(is_featured = 1, 0, 1), updated_at = NOW() WHERE id = :id');
        $statement->execute(['id' => $id]);
    }

    private function hasLike(int $postId, int $userId): bool
    {
        $statement = $this->db->prepare('SELECT 1 FROM post_likes WHERE post_id = :post_id AND user_id = :user_id LIMIT 1');
        $statement->execute(['post_id' => $postId, 'user_id' => $userId]);

        return (bool) $statement->fetchColumn();
    }

    private function hasSave(int $postId, int $userId): bool
    {
        $statement = $this->db->prepare('SELECT 1 FROM post_saves WHERE post_id = :post_id AND user_id = :user_id LIMIT 1');
        $statement->execute(['post_id' => $postId, 'user_id' => $userId]);

        return (bool) $statement->fetchColumn();
    }

    private function baseSelect(bool $viewer): string
    {
        $viewerColumns = $viewer
            ? 'EXISTS (SELECT 1 FROM post_likes WHERE post_likes.post_id = posts.id AND post_likes.user_id = :viewer_like_id) AS is_liked,
               EXISTS (SELECT 1 FROM post_saves WHERE post_saves.post_id = posts.id AND post_saves.user_id = :viewer_save_id) AS is_saved,'
            : '0 AS is_liked, 0 AS is_saved,';

        return 'SELECT posts.*,
                       users.full_name AS author_name,
                       roles.slug AS author_role,
                       moderator.full_name AS moderator_name,
                       ' . $viewerColumns . '
                       (SELECT COUNT(*) FROM comments WHERE comments.post_id = posts.id AND comments.status = "aprovado") AS comments_count,
                       (SELECT COUNT(*) FROM post_likes WHERE post_likes.post_id = posts.id) AS likes_count,
                       (SELECT COUNT(*) FROM post_saves WHERE post_saves.post_id = posts.id) AS saves_count
                FROM posts
                INNER JOIN users ON users.id = posts.user_id
                INNER JOIN roles ON roles.id = users.role_id
                LEFT JOIN users moderator ON moderator.id = posts.moderated_by';
    }
}
