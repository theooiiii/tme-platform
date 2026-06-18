<?php

defined('BASE_PATH') || exit('Acesso direto não permitido.');

class Institution extends Model
{
    public function searchByName(string $term, int $limit = 10): array
    {
        $statement = $this->db->prepare(
            'SELECT id, name, city, state, institution_type
             FROM institutions
             WHERE name LIKE :term
             ORDER BY name
             LIMIT :limit'
        );

        $statement->bindValue(':term', '%' . $term . '%');
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    public function findByName(string $name): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM institutions WHERE name = :name LIMIT 1');
        $statement->execute(['name' => $name]);
        $institution = $statement->fetch();

        return $institution ?: null;
    }

    public function findOrCreateManual(string $name, ?string $city = null, ?string $state = null): int
    {
        $name = trim($name);

        if ($name === '') {
            throw new InvalidArgumentException('Nome da instituição é obrigatório.');
        }

        $existing = $this->findByName($name);

        if ($existing) {
            return (int) $existing['id'];
        }

        $statement = $this->db->prepare(
            'INSERT INTO institutions (name, city, state, source, verification_status, created_at, updated_at)
             VALUES (:name, :city, :state, :source, :verification_status, NOW(), NOW())'
        );

        $statement->execute([
            'name' => $name,
            'city' => $city,
            'state' => $state,
            'source' => 'manual',
            'verification_status' => 'nao_verificada',
        ]);

        return (int) $this->db->lastInsertId();
    }
}
