<?php

defined('BASE_PATH') || exit('Acesso direto não permitido.');

class RoleMiddleware
{
    public static function handle(array $roles): void
    {
        $user = current_user();

        if ($user && in_array($user['role_slug'], $roles, true)) {
            return;
        }

        http_response_code(403);
        (new Controller())->view('errors/403', ['title' => 'Acesso restrito']);
        exit;
    }
}
