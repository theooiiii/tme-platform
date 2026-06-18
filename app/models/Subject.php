<?php

defined('BASE_PATH') || exit('Acesso direto não permitido.');

class Subject extends Model
{
    public function all(): array
    {
        return $this->db->query(
            'SELECT subjects.*, teacher.full_name AS teacher_name
             FROM subjects
             LEFT JOIN users teacher ON teacher.id = subjects.teacher_id
             ORDER BY subjects.name'
        )->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM subjects WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $subject = $statement->fetch();

        return $subject ?: null;
    }

    public function create(array $data): int
    {
        $statement = $this->db->prepare(
            'INSERT INTO subjects (name, description, area, workload_hours, status, created_at, updated_at)
             VALUES (:name, :description, :area, :workload_hours, :status, NOW(), NOW())'
        );
        $statement->execute($this->params($data));

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $params = $this->params($data);
        $params['id'] = $id;

        $statement = $this->db->prepare(
            'UPDATE subjects
             SET name = :name, description = :description, area = :area,
                 workload_hours = :workload_hours, status = :status, updated_at = NOW()
             WHERE id = :id'
        );
        $statement->execute($params);
    }

    public function archive(int $id): void
    {
        $statement = $this->db->prepare('UPDATE subjects SET status = "arquivada", updated_at = NOW() WHERE id = :id');
        $statement->execute(['id' => $id]);
    }

    private function params(array $data): array
    {
        return [
            'name' => $data['name'],
            'description' => $data['description'] ?: null,
            'area' => $data['area'] ?: null,
            'workload_hours' => $data['workload_hours'] ?: null,
            'status' => $data['status'],
        ];
    }
}
