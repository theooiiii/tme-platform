<?php

defined('BASE_PATH') || exit('Acesso direto nao permitido.');

$appName = config('app.name');
$slogan = config('app.slogan');
$currentUser = current_user();
$settings = current_settings();
$pageTitle = isset($title) ? $title . ' | ' . $appName : $appName;
$path = current_path();
$isAuthenticated = (bool) $currentUser;
$role = $currentUser['role_slug'] ?? null;
$isLearner = in_array($role, ['aluno', 'professor'], true);
$isAdmin = in_array($role, ['administrador', 'supervisor'], true);

$guestLinks = [
    '/' => 'Home',
    '/sobre' => 'Sobre',
    '/cursos' => 'Cursos',
    '/eventos' => 'Eventos',
    '/biblioteca' => 'Biblioteca',
    '/comunidade' => 'Comunidade',
    '/login' => 'Login',
    '/cadastro' => 'Cadastro',
];

$internalLinks = [
    '/portal' => 'Inicio',
    '/dashboard' => 'Dashboard',
];

if ($isLearner) {
    $internalLinks['/aluno/cursos'] = 'Cursos';
    $internalLinks['/meus-cursos'] = 'Meus cursos';
    $internalLinks['/atividades'] = 'Atividades';
    $internalLinks['/boletim'] = 'Boletim';
} elseif ($isAdmin) {
    $internalLinks['/admin/cursos'] = 'Cursos';
}

$internalLinks['/biblioteca'] = 'Biblioteca';
$internalLinks['/eventos'] = 'Eventos';
$internalLinks['/comunidade'] = 'Comunidade';
$internalLinks['/configuracoes'] = 'Configuracoes/Tema';

if ($isAdmin) {
    $internalLinks['/admin/contas-pendentes'] = 'Administracao';
    $internalLinks['/admin/atividades'] = 'Atividades admin';
    $internalLinks['/admin/biblioteca'] = 'Biblioteca admin';
} elseif ($role === 'professor') {
    $internalLinks['/admin/atividades'] = 'Gestao atividades';
    $internalLinks['/admin/biblioteca'] = 'Biblioteca admin';
}

$isActive = static function (string $href) use ($path): bool {
    if ($href === '/') {
        return $path === '/';
    }

    if ($href === '/admin/contas-pendentes') {
        return str_starts_with($path, '/admin');
    }

    if ($href === '/meus-cursos') {
        return str_starts_with($path, '/meus-cursos') || str_starts_with($path, '/aluno/meus-cursos');
    }

    return $path === $href || str_starts_with($path, $href . '/');
};
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?></title>
    <link rel="stylesheet" href="<?= e(asset('assets/css/style.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('assets/css/themes.css')) ?>">
</head>
<body data-theme="<?= e($settings['theme']) ?>" data-base-url="<?= e(rtrim(url('/'), '/')) ?>" style="--accent: <?= e($settings['primary_color']) ?>;">
    <header class="site-header <?= $isAuthenticated ? 'authenticated' : 'public' ?>">
        <a class="brand" href="<?= e(url($isAuthenticated ? '/portal' : '/')) ?>" aria-label="TME">
            <span class="brand-mark">TME</span>
            <span>
                <strong>Theo Mind Educacional</strong>
                <small><?= e($slogan) ?></small>
            </span>
        </a>

        <button class="nav-toggle" type="button" data-nav-toggle aria-label="Abrir menu">Menu</button>

        <nav class="site-nav" data-site-nav>
            <?php foreach (($isAuthenticated ? $internalLinks : $guestLinks) as $href => $label): ?>
                <a class="<?= $isActive($href) ? 'active' : '' ?>" href="<?= e(url($href)) ?>"><?= e($label) ?></a>
            <?php endforeach; ?>
        </nav>

        <div class="header-actions">
            <?php if ($isAuthenticated): ?>
                <span class="user-chip">Ola, <?= e(explode(' ', trim($currentUser['full_name']))[0] ?: $currentUser['full_name']) ?></span>
                <form class="theme-form compact-theme-form" action="<?= e(url('/settings')) ?>" method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="redirect_to" value="<?= e($path) ?>">
                    <select name="theme" aria-label="Tema">
                        <option value="light" <?= $settings['theme'] === 'light' ? 'selected' : '' ?>>Claro</option>
                        <option value="dark" <?= $settings['theme'] === 'dark' ? 'selected' : '' ?>>Escuro</option>
                    </select>
                    <input type="color" name="primary_color" value="<?= e($settings['primary_color']) ?>" aria-label="Cor principal">
                    <button type="submit" class="icon-button" title="Salvar preferencias">OK</button>
                </form>
                <form action="<?= e(url('/logout')) ?>" method="post">
                    <?= csrf_field() ?>
                    <button class="button ghost" type="submit">Sair</button>
                </form>
            <?php endif; ?>
        </div>
    </header>

    <main>
        <div class="flash-stack">
            <?php if ($message = flash('success')): ?>
                <div class="flash success"><?= e($message) ?></div>
            <?php endif; ?>
            <?php if ($message = flash('info')): ?>
                <div class="flash info"><?= e($message) ?></div>
            <?php endif; ?>
            <?php if ($message = flash('error')): ?>
                <div class="flash error"><?= e($message) ?></div>
            <?php endif; ?>
            <?php if ($errors = flash('errors')): ?>
                <div class="flash error">
                    <?php foreach ((array) $errors as $error): ?>
                        <p><?= e($error) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php require $viewFile; ?>
    </main>

    <footer class="site-footer">
        <span>&copy; <?= date('Y') ?> TME - Theo Mind Educacional</span>
        <span><?= e($slogan) ?></span>
    </footer>

    <script src="<?= e(asset('assets/js/app.js')) ?>"></script>
</body>
</html>
