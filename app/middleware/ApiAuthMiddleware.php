<?php

defined('BASE_PATH') || exit('Acesso direto nao permitido.');

class ApiAuthMiddleware
{
    public static function handle(): void
    {
        $token = self::bearerToken();

        if (! $token) {
            self::deny('Token ausente.');
        }

        $user = (new ApiToken())->userFromToken($token);

        if (! $user) {
            self::deny('Token invalido.');
        }

        if (($user['status'] ?? '') !== 'aprovado') {
            self::deny('Usuario sem acesso ativo.', 403);
        }

        $_SESSION['api_user'] = $user;
        $_SESSION['user_id'] = (int) $user['id'];
    }

    private static function bearerToken(): ?string
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';

        if (preg_match('/Bearer\s+(.+)/i', $header, $matches)) {
            return trim($matches[1]);
        }

        if (! empty($_SERVER['HTTP_X_API_TOKEN'])) {
            return trim((string) $_SERVER['HTTP_X_API_TOKEN']);
        }

        return null;
    }

    private static function deny(string $message, int $status = 401): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'error' => [
                'code' => $status === 401 ? 'unauthenticated' : 'forbidden',
                'message' => $message,
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
