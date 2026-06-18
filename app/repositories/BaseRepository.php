<?php

defined('BASE_PATH') || exit('Acesso direto nao permitido.');

abstract class BaseRepository
{
    protected PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    protected function limit(int $limit, int $max = 100): int
    {
        return max(1, min($limit, $max));
    }

    protected function offset(int $page, int $perPage): int
    {
        return max(0, ($page - 1) * $perPage);
    }

    protected function organizationFilter(?int $organizationId, string $column = 'organization_id'): array
    {
        if ($organizationId === null) {
            return ['', []];
        }

        return [' AND ' . $column . ' = :organization_id', ['organization_id' => $organizationId]];
    }
}
