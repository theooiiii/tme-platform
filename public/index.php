<?php

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

session_start();

require BASE_PATH . '/app/core/Env.php';

Env::load(BASE_PATH . '/.env');

spl_autoload_register(function (string $class): void {
    $directories = [
        'app/core',
        'app/controllers',
        'app/models',
        'app/middleware',
        'app/services',
        'app/helpers',
    ];

    foreach ($directories as $directory) {
        $file = BASE_PATH . '/' . $directory . '/' . $class . '.php';

        if (is_file($file)) {
            require $file;
            return;
        }
    }
});

require BASE_PATH . '/app/helpers/functions.php';

date_default_timezone_set((string) config('app.timezone', 'America/Sao_Paulo'));

$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$isAssetRequest = isset($_GET['_asset']) || str_starts_with($requestPath, '/assets/');

if ($isAssetRequest) {
    $requestedAsset = isset($_GET['_asset'])
        ? (string) $_GET['_asset']
        : substr($requestPath, strlen('/assets/'));
    $requestedAsset = str_replace('\\', '/', $requestedAsset);
    $assetsRoot = realpath(BASE_PATH . '/assets');
    $assetFile = realpath(BASE_PATH . '/assets/' . $requestedAsset);

    if (
        str_contains($requestedAsset, '..') ||
        ! $assetsRoot ||
        ! $assetFile ||
        ! str_starts_with($assetFile, $assetsRoot) ||
        ! is_file($assetFile)
    ) {
        http_response_code(404);
        exit;
    }

    $extension = strtolower(pathinfo($assetFile, PATHINFO_EXTENSION));
    $contentTypes = [
        'css' => 'text/css; charset=utf-8',
        'js' => 'application/javascript; charset=utf-8',
        'svg' => 'image/svg+xml',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'webp' => 'image/webp',
        'ico' => 'image/x-icon',
    ];

    header('Content-Type: ' . ($contentTypes[$extension] ?? 'application/octet-stream'));
    header('Cache-Control: public, max-age=3600');
    readfile($assetFile);
    exit;
}

if ((bool) config('app.debug', false)) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
}

$routes = require BASE_PATH . '/config/routes.php';

$router = new Router($routes);
$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
