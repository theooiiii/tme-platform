<?php

defined('BASE_PATH') || exit('Acesso direto nao permitido.');

class Router
{
    public function __construct(private array $routes)
    {
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = $this->normalizePath(parse_url($uri, PHP_URL_PATH) ?: '/');

        foreach ($this->routes as $route) {
            [$routeMethod, $pattern, $handler, $middleware] = $route + [3 => []];

            if (strtoupper($method) !== strtoupper($routeMethod)) {
                continue;
            }

            $params = $this->match($pattern, $path);

            if ($params === null) {
                continue;
            }

            $this->runMiddleware($middleware);
            $this->call($handler, $params);
            return;
        }

        http_response_code(404);
        (new Controller())->view('errors/404', ['title' => 'Pagina nao encontrada']);
    }

    private function normalizePath(string $path): string
    {
        $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));

        if ($scriptDir !== '/' && $scriptDir !== '.' && str_starts_with($path, $scriptDir)) {
            $path = substr($path, strlen($scriptDir)) ?: '/';
        }

        if ($path === '/index.php') {
            $path = '/';
        }

        $path = '/' . trim($path, '/');

        return $path === '//' ? '/' : $path;
    }

    private function match(string $pattern, string $path): ?array
    {
        $pattern = '/' . trim($pattern, '/');
        $pattern = $pattern === '//' ? '/' : $pattern;

        $regex = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';

        if (! preg_match($regex, $path, $matches)) {
            return null;
        }

        return array_filter(
            $matches,
            static fn ($key): bool => is_string($key),
            ARRAY_FILTER_USE_KEY
        );
    }

    private function runMiddleware(array $middleware): void
    {
        foreach ($middleware as $item) {
            if ($item === 'auth') {
                AuthMiddleware::handle();
                continue;
            }

            if (str_starts_with($item, 'role:')) {
                $roles = explode(',', substr($item, 5));
                RoleMiddleware::handle($roles);
                continue;
            }

            if ($item === 'premium') {
                PremiumMiddleware::handle();
            }
        }
    }

    private function call(array $handler, array $params): void
    {
        [$class, $method] = $handler;
        $controller = new $class();
        $controller->{$method}(...array_values($params));
    }
}
