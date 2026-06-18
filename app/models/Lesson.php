<?php

defined('BASE_PATH') || exit('Acesso direto não permitido.');

class Lesson extends Model
{
    public function forCourse(int $courseId, ?string $status = null): array
    {
        $sql = 'SELECT lessons.*, course_modules.title AS module_title
                FROM lessons
                LEFT JOIN course_modules ON course_modules.id = lessons.module_id
                WHERE lessons.course_id = :course_id';
        $params = ['course_id' => $courseId];

        if ($status !== null) {
            $sql .= ' AND lessons.status = :status';
            $params['status'] = $status;
        }

        $sql .= ' ORDER BY COALESCE(course_modules.position, 9999), lessons.position, lessons.id';

        $statement = $this->db->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function countPublishedForCourse(int $courseId): int
    {
        $statement = $this->db->prepare('SELECT COUNT(*) FROM lessons WHERE course_id = :course_id AND status = "publicada"');
        $statement->execute(['course_id' => $courseId]);

        return (int) $statement->fetchColumn();
    }

    public function find(int $id): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM lessons WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $lesson = $statement->fetch();

        return $lesson ?: null;
    }

    public function create(array $data): int
    {
        $statement = $this->db->prepare(
            'INSERT INTO lessons (
                course_id, module_id, title, description, lesson_type, content, video_url,
                duration_minutes, position, status, created_at, updated_at
             ) VALUES (
                :course_id, :module_id, :title, :description, :lesson_type, :content, :video_url,
                :duration_minutes, :position, :status, NOW(), NOW()
             )'
        );
        $statement->execute([
            'course_id' => $data['course_id'],
            'module_id' => $data['module_id'] ?: null,
            'title' => $data['title'],
            'description' => $data['description'],
            'lesson_type' => $data['lesson_type'],
            'content' => $data['content'],
            'video_url' => $data['video_url'] ?: null,
            'duration_minutes' => $data['duration_minutes'] ?: null,
            'position' => $data['position'],
            'status' => $data['status'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $statement = $this->db->prepare(
            'UPDATE lessons
             SET module_id = :module_id,
                 title = :title,
                 description = :description,
                 lesson_type = :lesson_type,
                 content = :content,
                 video_url = :video_url,
                 duration_minutes = :duration_minutes,
                 position = :position,
                 status = :status,
                 updated_at = NOW()
             WHERE id = :id'
        );
        $statement->execute([
            'module_id' => $data['module_id'] ?: null,
            'title' => $data['title'],
            'description' => $data['description'],
            'lesson_type' => $data['lesson_type'],
            'content' => $data['content'],
            'video_url' => $data['video_url'] ?: null,
            'duration_minutes' => $data['duration_minutes'] ?: null,
            'position' => $data['position'],
            'status' => $data['status'],
            'id' => $id,
        ]);

        return $statement->rowCount() >= 0;
    }

    public function delete(int $id): bool
    {
        $statement = $this->db->prepare('DELETE FROM lessons WHERE id = :id');
        $statement->execute(['id' => $id]);

        return $statement->rowCount() > 0;
    }
}
