<?php

defined('BASE_PATH') || exit('Acesso direto nao permitido.');

return [
    'name' => env('APP_NAME', 'TME - Theo Mind Educacional'),
    'short_name' => 'TME',
    'slogan' => 'Tecnologia, ensino e evolução em uma única plataforma.',
    'url' => env('APP_URL', 'http://localhost/tme-plataform/public'),
    'timezone' => env('APP_TIMEZONE', 'America/Sao_Paulo'),
    'debug' => env('APP_DEBUG', false),
    'default_theme' => 'light',
    'default_primary_color' => '#1f6feb',
    'account_statuses' => ['pendente', 'aprovado', 'recusado', 'inativo'],
    'roles' => ['aluno', 'professor', 'supervisor', 'administrador', 'secretaria', 'financeiro'],
];
