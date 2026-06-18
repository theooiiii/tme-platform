<?php

defined('BASE_PATH') || exit('Acesso direto não permitido.');

function env(string $key, mixed $default = null): mixed
{
    $value = $_ENV[$key] ?? getenv($key);

    if ($value === false || $value === null) {
        return $default;
    }

    return match (strtolower((string) $value)) {
        'true' => true,
        'false' => false,
        'null' => null,
        default => $value,
    };
}

function config(string $key, mixed $default = null): mixed
{
    static $configs = [];

    [$file, $path] = array_pad(explode('.', $key, 2), 2, null);

    if (! isset($configs[$file])) {
        $configFile = BASE_PATH . '/config/' . $file . '.php';
        $configs[$file] = is_file($configFile) ? require $configFile : [];
    }

    if ($path === null) {
        return $configs[$file] ?? $default;
    }

    $value = $configs[$file];

    foreach (explode('.', $path) as $segment) {
        if (! is_array($value) || ! array_key_exists($segment, $value)) {
            return $default;
        }

        $value = $value[$segment];
    }

    return $value;
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function route_base_path(): string
{
    if (env('VERCEL') || env('TME_SERVERLESS')) {
        return '';
    }

    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));

    if ($scriptDir === '/' || $scriptDir === '.') {
        return '';
    }

    return rtrim($scriptDir, '/');
}

function project_base_path(): string
{
    if (env('VERCEL') || env('TME_SERVERLESS')) {
        return '';
    }

    $base = route_base_path();

    if (str_ends_with($base, '/public')) {
        return substr($base, 0, -7) ?: '';
    }

    return $base;
}

function url(string $path = '/'): string
{
    $path = '/' . ltrim($path, '/');

    return route_base_path() . ($path === '/' ? '/' : $path);
}

function current_path(): string
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $base = route_base_path();

    if ($base !== '' && str_starts_with($path, $base)) {
        $path = substr($path, strlen($base)) ?: '/';
    }

    if ($path === '/index.php') {
        $path = '/';
    }

    $path = '/' . trim($path, '/');

    return $path === '//' ? '/' : $path;
}

function asset(string $path): string
{
    if (preg_match('#^https?://#', $path)) {
        return $path;
    }

    return project_base_path() . '/' . ltrim($path, '/');
}

function redirect_to(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

function flash(string $key, mixed $message = null): mixed
{
    if ($message !== null) {
        $_SESSION['_flash'][$key] = $message;
        return null;
    }

    $value = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);

    return $value;
}

function old(string $key, mixed $default = ''): mixed
{
    return $_SESSION['_old'][$key] ?? $default;
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf_token(?string $token): bool
{
    return is_string($token) && hash_equals($_SESSION['_csrf'] ?? '', $token);
}

function current_user(): ?array
{
    static $user = null;
    static $loaded = false;

    if ($loaded) {
        return $user;
    }

    $loaded = true;
    $id = $_SESSION['user_id'] ?? null;

    if (! $id) {
        return null;
    }

    $user = (new User())->findById((int) $id);

    if (! $user) {
        unset($_SESSION['user_id']);
    }

    return $user ?: null;
}

function current_settings(): array
{
    $user = current_user();

    if (! $user) {
        return [
            'theme' => config('app.default_theme', 'light'),
            'primary_color' => config('app.default_primary_color', '#1f6feb'),
        ];
    }

    return [
        'theme' => $user['theme'] ?? config('app.default_theme', 'light'),
        'primary_color' => $user['primary_color'] ?? config('app.default_primary_color', '#1f6feb'),
    ];
}

function role_label(string $slug): string
{
    return [
        'aluno' => 'Aluno',
        'professor' => 'Professor',
        'supervisor' => 'Supervisor',
        'administrador' => 'Administrador',
        'secretaria' => 'Secretaria',
        'financeiro' => 'Financeiro',
    ][$slug] ?? ucfirst($slug);
}

function human_label(?string $value): string
{
    $value = trim((string) $value);

    if ($value === '') {
        return '-';
    }

    $labels = [
        'ao_vivo' => 'Ao vivo',
        'apresentacao' => 'Apresentação',
        'aprovado' => 'Aprovado',
        'arquivo' => 'Arquivo',
        'arquivado' => 'Arquivado',
        'arquivada' => 'Arquivada',
        'ativa' => 'Ativa',
        'ativo' => 'Ativo',
        'atrasada' => 'Atrasada',
        'cancelada' => 'Cancelada',
        'cancelado' => 'Cancelado',
        'cartao' => 'Cartão',
        'comissao' => 'Comissão',
        'concluida' => 'Concluída',
        'concluido' => 'Concluído',
        'discursiva' => 'Discursiva',
        'encerrado' => 'Encerrado',
        'encerrada' => 'Encerrada',
        'estornado' => 'Estornado',
        'facil' => 'Fácil',
        'imagem' => 'Imagem',
        'institucional' => 'Institucional',
        'logados' => 'Somente logados',
        'media' => 'Média',
        'mensalidade' => 'Mensalidade',
        'objetiva' => 'Objetiva',
        'pendente' => 'Pendente',
        'pendente atrasada' => 'Pendente atrasada',
        'pendente_correcao' => 'Pendente de correção',
        'privada_admin' => 'Privada/admin',
        'privado' => 'Privado',
        'processando' => 'Processando',
        'publica' => 'Pública',
        'publicada' => 'Publicada',
        'publicado' => 'Publicado',
        'publico' => 'Público',
        'rascunho' => 'Rascunho',
        'recusado' => 'Recusado',
        'recusada' => 'Recusada',
        'revogado' => 'Revogado',
        'unico' => 'Único',
        'valido' => 'Válido',
        'video' => 'Vídeo',
    ];

    return $labels[$value] ?? ucwords(str_replace('_', ' ', $value));
}

function slugify(string $value): string
{
    $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
    $value = strtolower($value);
    $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?: '';
    $value = trim($value, '-');

    return $value !== '' ? $value : 'item';
}

function media_embed_url(?string $url): ?string
{
    $url = trim((string) $url);

    if ($url === '') {
        return null;
    }

    $host = strtolower((string) parse_url($url, PHP_URL_HOST));
    $path = (string) parse_url($url, PHP_URL_PATH);

    if (str_contains($host, 'youtube.com')) {
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
        $video = $query['v'] ?? trim($path, '/');

        return $video ? 'https://www.youtube.com/embed/' . rawurlencode((string) $video) : null;
    }

    if (str_contains($host, 'youtu.be')) {
        $video = trim($path, '/');

        return $video ? 'https://www.youtube.com/embed/' . rawurlencode($video) : null;
    }

    if (str_contains($host, 'vimeo.com')) {
        $video = trim($path, '/');

        return $video ? 'https://player.vimeo.com/video/' . rawurlencode($video) : null;
    }

    return null;
}

function is_direct_video_url(?string $url): bool
{
    return (bool) preg_match('/\.(mp4|webm|ogg)(\?.*)?$/i', (string) $url);
}
