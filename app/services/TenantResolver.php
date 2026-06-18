<?php

defined('BASE_PATH') || exit('Acesso direto nao permitido.');

class TenantResolver
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function currentOrganizationId(): ?int
    {
        $user = current_user();

        if ($user && ! empty($user['organization_id'])) {
            return (int) $user['organization_id'];
        }

        $organization = $this->fromHost($this->host());

        return $organization ? (int) $organization['id'] : null;
    }

    public function currentOrganization(): ?array
    {
        $organizationId = $this->currentOrganizationId();

        if (! $organizationId) {
            return null;
        }

        try {
            $statement = $this->db->prepare('SELECT * FROM organizations WHERE id = :id LIMIT 1');
            $statement->execute(['id' => $organizationId]);
            $organization = $statement->fetch();

            return $organization ?: null;
        } catch (PDOException) {
            return null;
        }
    }

    private function fromHost(string $host): ?array
    {
        if ($host === '' || in_array($host, ['localhost', '127.0.0.1'], true)) {
            return null;
        }

        try {
            $statement = $this->db->prepare(
                'SELECT o.*
                 FROM organizations o
                 LEFT JOIN organization_domains od ON od.organization_id = o.id
                 WHERE o.primary_domain = :host OR od.domain = :host
                 LIMIT 1'
            );
            $statement->execute(['host' => $host]);
            $organization = $statement->fetch();

            return $organization ?: null;
        } catch (PDOException) {
            return null;
        }
    }

    private function host(): string
    {
        $host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
        $host = preg_replace('/:\d+$/', '', $host) ?: '';

        return trim($host);
    }
}
