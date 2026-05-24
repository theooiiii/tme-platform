<?php

defined('BASE_PATH') || exit('Acesso direto nao permitido.');

class Plan extends Model
{
    public function active(): array
    {
        $statement = $this->db->query(
            'SELECT * FROM plans WHERE status = "ativo" ORDER BY sort_order, price, id'
        );

        return $this->hydrate($statement->fetchAll());
    }

    public function all(): array
    {
        $statement = $this->db->query('SELECT * FROM plans ORDER BY sort_order, id');

        return $this->hydrate($statement->fetchAll());
    }

    public function find(int $id): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM plans WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $plan = $statement->fetch();

        return $plan ? $this->hydrateOne($plan) : null;
    }

    public function create(array $data): int
    {
        $statement = $this->db->prepare(
            'INSERT INTO plans (
                name, description, price, billing_cycle, duration_days, features,
                benefits, is_premium, sort_order, status, created_at, updated_at
             ) VALUES (
                :name, :description, :price, :billing_cycle, :duration_days, :features,
                :benefits, :is_premium, :sort_order, :status, NOW(), NOW()
             )'
        );
        $statement->execute($this->params($data));

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $params = $this->params($data);
        $params['id'] = $id;

        $statement = $this->db->prepare(
            'UPDATE plans
             SET name = :name,
                 description = :description,
                 price = :price,
                 billing_cycle = :billing_cycle,
                 duration_days = :duration_days,
                 features = :features,
                 benefits = :benefits,
                 is_premium = :is_premium,
                 sort_order = :sort_order,
                 status = :status,
                 updated_at = NOW()
             WHERE id = :id'
        );
        $statement->execute($params);
    }

    public function archive(int $id): void
    {
        $statement = $this->db->prepare('UPDATE plans SET status = "inativo", updated_at = NOW() WHERE id = :id');
        $statement->execute(['id' => $id]);
    }

    private function params(array $data): array
    {
        $benefits = $this->linesToArray((string) ($data['benefits_text'] ?? ''));

        return [
            'name' => $data['name'],
            'description' => $data['description'] ?: null,
            'price' => $data['price'],
            'billing_cycle' => $data['billing_cycle'],
            'duration_days' => max(1, (int) $data['duration_days']),
            'features' => json_encode($benefits, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'benefits' => json_encode($benefits, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'is_premium' => ! empty($data['is_premium']) ? 1 : 0,
            'sort_order' => max(1, (int) ($data['sort_order'] ?? 1)),
            'status' => in_array($data['status'], ['ativo', 'inativo'], true) ? $data['status'] : 'ativo',
        ];
    }

    private function hydrate(array $plans): array
    {
        return array_map(fn (array $plan): array => $this->hydrateOne($plan), $plans);
    }

    private function hydrateOne(array $plan): array
    {
        $decoded = json_decode($plan['benefits'] ?: $plan['features'] ?: '[]', true);
        $plan['benefits_list'] = is_array($decoded) ? array_values($decoded) : [];
        $plan['benefits_text'] = implode("\n", $plan['benefits_list']);

        return $plan;
    }

    private function linesToArray(string $text): array
    {
        return array_values(array_filter(array_map(
            static fn (string $line): string => trim($line),
            preg_split('/\R/', $text) ?: []
        )));
    }
}
