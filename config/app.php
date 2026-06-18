<?php

defined('BASE_PATH') || exit('Acesso direto nao permitido.');

return [
    'name' => env('APP_NAME', 'TME - Theo Mind Educacional'),
    'short_name' => 'TME',
    'slogan' => 'Tecnologia, ensino e evolução em uma única plataforma.',
    'url' => env('APP_URL', env('VERCEL_URL') ? 'https://' . env('VERCEL_URL') : 'http://localhost/tme-plataform/public'),
    'timezone' => env('APP_TIMEZONE', 'America/Sao_Paulo'),
    'debug' => env('APP_DEBUG', false),
    'default_theme' => 'light',
    'default_primary_color' => '#1f6feb',
    'account_statuses' => ['pendente', 'aprovado', 'recusado', 'inativo'],
    'roles' => ['aluno', 'professor', 'supervisor', 'administrador', 'secretaria', 'financeiro'],
    'security' => [
        'session_name' => env('SESSION_NAME', 'TMESESSID'),
        'session_secure' => env('SESSION_SECURE', false),
        'session_samesite' => env('SESSION_SAMESITE', 'Lax'),
        'session_lifetime' => (int) env('SESSION_LIFETIME', 7200),
        'csp_enabled' => env('CSP_ENABLED', true),
    ],
    'rate_limits' => [
        'login' => [
            'max_attempts' => (int) env('LOGIN_MAX_ATTEMPTS', 5),
            'decay_seconds' => (int) env('LOGIN_DECAY_SECONDS', 900),
        ],
    ],
    'cache' => [
        'analytics_ttl' => (int) env('ANALYTICS_CACHE_TTL', 90),
    ],
];
