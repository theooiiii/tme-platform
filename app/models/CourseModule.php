<?php

defined('BASE_PATH') || exit('Acesso direto não permitido.');

class CourseModule extends Model
{
    public function forCourse(int $courseId): array
    {
        $statement = $this->db->prepare(
            'SELECT * FROM course_modules WHERE course_id = :course_id ORDER BY position, id'
        );
        $statement->execute(['course_id' => $courseId]);

        return $statement->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM course_modules WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $module = $statement->fetch();

        return $module ?: null;
    }

    public function create(array $data): int
    {
        $statement = $this->db->prepare(
            'INSERT INTO course_modules (course_id, title, description, position, created_at, updated_at)
             VALUES (:course_id, :title, :description, :position, NOW(), NOW())'
        );
        $statement->execute([
            'course_id' => $data['course_id'],
            'title' => $data['title'],
            'description' => $data['description'],
            'position' => $data['position'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $statement = $this->db->prepare(
            'UPDATE course_modules
             SET title = :title, description = :description, position = :position, updated_at = NOW()
             WHERE id = :id'
        );
        $statement->execute([
            'title' => $data['title'],
            'description' => $data['description'],
            'position' => $data['position'],
            'id' => $id,
        ]);

        return $statement->rowCount() >= 0;
    }

    public function delete(int $id): bool
    {
        $statement = $this->db->prepare('DELETE FROM course_modules WHERE id = :id');
        $statement->execute(['id' => $id]);

        return $statement->rowCount() > 0;
    }
}
