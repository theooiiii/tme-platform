<?php

defined('BASE_PATH') || exit('Acesso direto não permitido.');

class AuthMiddleware
{
    public static function handle(): void
    {
        if (current_user()) {
            return;
        }

        flash('info', 'Faça login para acessar esta área.');
        redirect_to('/login');
    }
}
