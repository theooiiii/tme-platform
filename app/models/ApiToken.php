<?php

defined('BASE_PATH') || exit('Acesso direto nao permitido.');

class ApiToken extends Model
{
    public function userFromToken(string $plainToken): ?array
    {
        $hash = hash('sha256', $plainToken);

        try {
            $statement = $this->db->prepare(
                'SELECT users.*, roles.slug AS role_slug, roles.name AS role_name, api_tokens.id AS token_id
                 FROM api_tokens
                 INNER JOIN users ON users.id = api_tokens.user_id
                 INNER JOIN roles ON roles.id = users.role_id
                 WHERE api_tokens.token_hash = :hash
                   AND api_tokens.revoked_at IS NULL
                   AND (api_tokens.expires_at IS NULL OR api_tokens.expires_at > NOW())
                 LIMIT 1'
            );
            $statement->execute(['hash' => $hash]);
            $user = $statement->fetch();

            if ($user) {
                $this->touch((int) $user['token_id']);
            }

            return $user ?: null;
        } catch (PDOException) {
            return null;
        }
    }

    public function issue(int $userId, string $name = 'API Token', array $abilities = ['*']): array
    {
        $plainToken = bin2hex(random_bytes(32));

        $statement = $this->db->prepare(
            'INSERT INTO api_tokens (user_id, name, token_hash, abilities, created_at)
             VALUES (:user_id, :name, :token_hash, :abilities, NOW())'
        );
        $statement->execute([
            'user_id' => $userId,
            'name' => $name,
            'token_hash' => hash('sha256', $plainToken),
            'abilities' => json_encode($abilities, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        return [
            'id' => (int) $this->db->lastInsertId(),
            'token' => $plainToken,
        ];
    }

    private function touch(int $tokenId): void
    {
        $statement = $this->db->prepare('UPDATE api_tokens SET last_used_at = NOW() WHERE id = :id');
        $statement->execute(['id' => $tokenId]);
    }
}
