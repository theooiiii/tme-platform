<?php

defined('BASE_PATH') || exit('Acesso direto nao permitido.');

class PremiumMiddleware
{
    public static function handle(): void
    {
        $user = current_user();

        if (! $user) {
            flash('info', 'Faca login para acessar recursos premium.');
            redirect_to('/login');
        }

        if (in_array($user['role_slug'], ['administrador', 'supervisor'], true)) {
            return;
        }

        if ((new Finance())->hasActivePremium((int) $user['id'])) {
            return;
        }

        flash('error', 'Este recurso exige uma assinatura premium ativa.');
        redirect_to('/planos');
    }
}
