<?php

defined('BASE_PATH') || exit('Acesso direto não permitido.');

class Controller
{
    public function view(string $view, array $data = [], string $layout = 'main'): void
    {
        $viewFile = BASE_PATH . '/app/views/' . $view . '.php';

        if (! is_file($viewFile)) {
            http_response_code(500);
            echo 'View não encontrada: ' . e($view);
            return;
        }

        extract($data, EXTR_SKIP);

        if ($layout === '') {
            require $viewFile;
            return;
        }

        $layoutFile = BASE_PATH . '/app/views/layouts/' . $layout . '.php';

        if (! is_file($layoutFile)) {
            http_response_code(500);
            echo 'Layout não encontrado: ' . e($layout);
            return;
        }

        require $layoutFile;
    }

    protected function redirect(string $path): void
    {
        redirect_to($path);
    }

    protected function json(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
