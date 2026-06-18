<?php

defined('BASE_PATH') || exit('Acesso direto nao permitido.');

$databaseUrl = env('DATABASE_URL', env('MYSQL_URL'));
$parsed = is_string($databaseUrl) && $databaseUrl !== '' ? parse_url($databaseUrl) : [];
$path = is_array($parsed) ? trim((string) ($parsed['path'] ?? ''), '/') : '';

return [
    'driver' => env('DB_CONNECTION', 'mysql'),
    'host' => env('DB_HOST', $parsed['host'] ?? '127.0.0.1'),
    'port' => env('DB_PORT', (string) ($parsed['port'] ?? '3306')),
    'database' => env('DB_DATABASE', $path ?: 'tme_platform'),
    'username' => env('DB_USERNAME', isset($parsed['user']) ? rawurldecode((string) $parsed['user']) : 'root'),
    'password' => env('DB_PASSWORD', isset($parsed['pass']) ? rawurldecode((string) $parsed['pass']) : ''),
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ],
];
