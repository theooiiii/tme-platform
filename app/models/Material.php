<?php

defined('BASE_PATH') || exit('Acesso direto nao permitido.');

class Material extends Model
{
    public function forCourse(int $courseId): array
    {
        $statement = $this->db->prepare(
            'SELECT materials.*
             FROM materials
             WHERE materials.course_id = :course_id
             ORDER BY materials.created_at DESC'
        );
        $statement->execute(['course_id' => $courseId]);

        return $statement->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM materials WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $material = $statement->fetch();

        return $material ?: null;
    }

    public function create(array $data): int
    {
        $statement = $this->db->prepare(
            'INSERT INTO materials (
                course_id, module_id, lesson_id, owner_id, title, description, material_type,
                visibility, file_path, external_url, status, created_at, updated_at
             ) VALUES (
                :course_id, :module_id, :lesson_id, :owner_id, :title, :description, :material_type,
                :visibility, :file_path, :external_url, :status, NOW(), NOW()
             )'
        );
        $statement->execute([
            'course_id' => $data['course_id'],
            'module_id' => $data['module_id'] ?: null,
            'lesson_id' => $data['lesson_id'],
            'owner_id' => $data['owner_id'],
            'title' => $data['title'],
            'description' => $data['description'],
            'material_type' => $data['material_type'],
            'visibility' => $data['visibility'],
            'file_path' => $data['file_path'] ?: null,
            'external_url' => $data['external_url'] ?: null,
            'status' => $data['status'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $statement = $this->db->prepare(
            'UPDATE materials
             SET title = :title,
                 description = :description,
                 material_type = :material_type,
                 visibility = :visibility,
                 file_path = COALESCE(:file_path, file_path),
                 external_url = :external_url,
                 status = :status,
                 updated_at = NOW()
             WHERE id = :id'
        );
        $statement->execute([
            'title' => $data['title'],
            'description' => $data['description'],
            'material_type' => $data['material_type'],
            'visibility' => $data['visibility'],
            'file_path' => $data['file_path'] ?: null,
            'external_url' => $data['external_url'] ?: null,
            'status' => $data['status'],
            'id' => $id,
        ]);

        return $statement->rowCount() >= 0;
    }

    public function delete(int $id): bool
    {
        $statement = $this->db->prepare('DELETE FROM materials WHERE id = :id');
        $statement->execute(['id' => $id]);

        return $statement->rowCount() > 0;
    }
}
