<?php

defined('BASE_PATH') || exit('Acesso direto nao permitido.');

class QueueService
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function push(string $jobType, array $payload, ?DateTimeInterface $availableAt = null, ?int $organizationId = null): int
    {
        $statement = $this->db->prepare(
            'INSERT INTO automation_jobs (organization_id, job_type, payload, status, available_at, created_at)
             VALUES (:organization_id, :job_type, :payload, :status, :available_at, NOW())'
        );
        $statement->execute([
            'organization_id' => $organizationId,
            'job_type' => $jobType,
            'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'status' => 'pendente',
            'available_at' => ($availableAt ?? new DateTimeImmutable())->format('Y-m-d H:i:s'),
        ]);

        return (int) $this->db->lastInsertId();
    }
}
