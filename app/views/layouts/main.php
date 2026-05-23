<?php

defined('BASE_PATH') || exit('Acesso direto nao permitido.');

$appName = config('app.name');
$slogan = config('app.slogan');
$currentUser = current_user();
$settings = current_settings();
$pageTitle = isset($title) ? $title . ' | ' . $appName : $appName;
$path = current_path();

$publicLinks = [
    '/' => 'Home',
    '/sobre' => 'Sobre',
    '/cursos' => 'Cursos',
    '/eventos' => 'Eventos',
    '/biblioteca' => 'Biblioteca',
    '/comunidade' => 'Comunidade',
];
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
    <header class="site-header">
        <a class="brand" href="<?= e(url('/')) ?>" aria-label="TME Home">
            <span class="brand-mark">TME</span>
            <span>
                <strong>Theo Mind Educacional</strong>
                <small><?= e($slogan) ?></small>
            </span>
        </a>

        <button class="nav-toggle" type="button" data-nav-toggle aria-label="Abrir menu">☰</button>

        <nav class="site-nav" data-site-nav>
            <?php foreach ($publicLinks as $href => $label): ?>
                <a class="<?= $path === $href ? 'active' : '' ?>" href="<?= e(url($href)) ?>"><?= e($label) ?></a>
            <?php endforeach; ?>

            <?php if ($currentUser): ?>
                <a class="<?= $path === '/dashboard' ? 'active' : '' ?>" href="<?= e(url('/dashboard')) ?>">Dashboard</a>
                <?php if ($currentUser['role_slug'] === 'aluno'): ?>
                    <a class="<?= str_starts_with($path, '/aluno/cursos') ? 'active' : '' ?>" href="<?= e(url('/aluno/cursos')) ?>">Catálogo</a>
                    <a class="<?= str_starts_with($path, '/aluno/meus-cursos') ? 'active' : '' ?>" href="<?= e(url('/aluno/meus-cursos')) ?>">Meus cursos</a>
                <?php endif; ?>
                <?php if (in_array($currentUser['role_slug'], ['administrador', 'supervisor'], true)): ?>
                    <a class="<?= $path === '/admin/contas-pendentes' ? 'active' : '' ?>" href="<?= e(url('/admin/contas-pendentes')) ?>">Aprovações</a>
                    <a class="<?= str_starts_with($path, '/admin/cursos') ? 'active' : '' ?>" href="<?= e(url('/admin/cursos')) ?>">Cursos admin</a>
                    <a class="<?= str_starts_with($path, '/admin/matriculas') ? 'active' : '' ?>" href="<?= e(url('/admin/matriculas')) ?>">Matrículas</a>
                <?php endif; ?>
            <?php endif; ?>
        </nav>

        <div class="header-actions">
            <?php if ($currentUser): ?>
                <form class="theme-form" action="<?= e(url('/settings')) ?>" method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="redirect_to" value="<?= e($path) ?>">
                    <select name="theme" aria-label="Tema">
                        <option value="light" <?= $settings['theme'] === 'light' ? 'selected' : '' ?>>Claro</option>
                        <option value="dark" <?= $settings['theme'] === 'dark' ? 'selected' : '' ?>>Escuro</option>
                    </select>
                    <input type="color" name="primary_color" value="<?= e($settings['primary_color']) ?>" aria-label="Cor principal">
                    <button type="submit" class="icon-button" title="Salvar preferências">✓</button>
                </form>
                <form action="<?= e(url('/logout')) ?>" method="post">
                    <?= csrf_field() ?>
                    <button class="button ghost" type="submit">Sair</button>
                </form>
            <?php else: ?>
                <a class="button ghost" href="<?= e(url('/login')) ?>">Login</a>
                <a class="button" href="<?= e(url('/cadastro')) ?>">Cadastro</a>
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
        <span>© <?= date('Y') ?> TME — Theo Mind Educacional</span>
        <span><?= e($slogan) ?></span>
    </footer>

    <script src="<?= e(asset('assets/js/app.js')) ?>"></script>
</body>
</html>
