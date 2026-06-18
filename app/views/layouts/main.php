<?php

defined('BASE_PATH') || exit('Acesso direto não permitido.');

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
$recentNotifications = [];
$unreadNotifications = 0;

if ($isAuthenticated) {
    $notificationService = new NotificationService();
    $recentNotifications = $notificationService->recent((int) $currentUser['id'], 5);
    $unreadNotifications = $notificationService->unreadCount((int) $currentUser['id']);
}

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

$primaryLinks = [
    '/portal' => 'Início',
    '/dashboard' => 'Dashboard',
];

if ($isLearner) {
    $primaryLinks['/aluno/cursos'] = 'Cursos';
    $primaryLinks['/meus-cursos'] = 'Meus cursos';
}

$primaryLinks['/biblioteca'] = 'Biblioteca';
$primaryLinks['/eventos'] = 'Eventos';
$primaryLinks['/comunidade'] = 'Comunidade';

$moduleLinks = [
    '/ranking' => 'Ranking',
    '/planos' => 'Planos',
    '/financeiro' => 'Financeiro',
    '/chat' => 'Chat',
];

if ($isLearner) {
    $moduleLinks = [
        '/atividades' => 'Atividades',
        '/boletim' => 'Boletim',
        '/provas' => 'Provas',
        '/minha-frequencia' => 'Frequência',
        '/certificados' => 'Certificados',
        '/turmas' => 'Turmas',
    ] + $moduleLinks;
}

if ($role === 'professor') {
    $moduleLinks['/frequencia'] = 'Chamada';
    $moduleLinks['/admin/provas'] = 'Gestão provas';
    $moduleLinks['/admin/atividades'] = 'Gestão atividades';
    $moduleLinks['/admin/biblioteca'] = 'Biblioteca admin';
}

$adminLinks = [];

if ($isAdmin) {
    $adminLinks = [
        '/admin/contas-pendentes' => 'Aprovações',
        '/admin/busca' => 'Busca global',
        '/admin/usuarios' => 'Usuários',
        '/admin/permissoes' => 'Permissões',
        '/analytics' => 'Analytics',
        '/admin/cursos' => 'Cursos',
        '/admin/categorias' => 'Categorias',
        '/admin/planos' => 'Planos',
        '/admin/matriculas' => 'Matrículas',
        '/admin/atividades' => 'Atividades',
        '/frequencia' => 'Frequência',
        '/admin/provas' => 'Provas',
        '/admin/biblioteca' => 'Biblioteca',
        '/admin/certificados' => 'Certificados',
        '/admin/comunidade' => 'Comunidade',
        '/admin/eventos' => 'Eventos',
        '/admin/turmas' => 'Turmas',
        '/admin/chat' => 'Chat auditoria',
        '/admin/logs' => 'Logs',
    ];
}

$isActive = static function (string $href) use ($path): bool {
    if ($href === '/') {
        return $path === '/';
    }

    if ($href === '/meus-cursos') {
        return str_starts_with($path, '/meus-cursos') || str_starts_with($path, '/aluno/meus-cursos');
    }

    return $path === $href || str_starts_with($path, $href . '/');
};

$isGroupActive = static function (array $links) use ($isActive): bool {
    foreach ($links as $href => $_label) {
        if ($isActive($href)) {
            return true;
        }
    }

    return false;
};
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?></title>
    <link rel="stylesheet" href="<?= e(asset('assets/css/base.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('assets/css/layout.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('assets/css/components.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('assets/css/dashboard.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('assets/css/modules.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('assets/css/responsive.css')) ?>">
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

        <nav class="site-nav" data-site-nav aria-label="Navegacao principal">
            <?php if ($isAuthenticated): ?>
                <?php foreach ($primaryLinks as $href => $label): ?>
                    <a class="<?= $isActive($href) ? 'active' : '' ?>" href="<?= e(url($href)) ?>"><?= e($label) ?></a>
                <?php endforeach; ?>

                <?php if (! empty($moduleLinks)): ?>
                    <div class="nav-dropdown <?= $isGroupActive($moduleLinks) ? 'active' : '' ?>" data-dropdown>
                        <button type="button" data-dropdown-toggle aria-expanded="false">Módulos</button>
                        <div class="nav-dropdown-panel" data-dropdown-panel>
                            <?php foreach ($moduleLinks as $href => $label): ?>
                                <a class="<?= $isActive($href) ? 'active' : '' ?>" href="<?= e(url($href)) ?>"><?= e($label) ?></a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (! empty($adminLinks)): ?>
                    <div class="nav-dropdown admin-dropdown <?= $isGroupActive($adminLinks) ? 'active' : '' ?>" data-dropdown>
                        <button type="button" data-dropdown-toggle aria-expanded="false">Administração</button>
                        <div class="nav-dropdown-panel wide" data-dropdown-panel>
                            <?php foreach ($adminLinks as $href => $label): ?>
                                <a class="<?= $isActive($href) ? 'active' : '' ?>" href="<?= e(url($href)) ?>"><?= e($label) ?></a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <?php foreach ($guestLinks as $href => $label): ?>
                    <a class="<?= $isActive($href) ? 'active' : '' ?>" href="<?= e(url($href)) ?>"><?= e($label) ?></a>
                <?php endforeach; ?>
            <?php endif; ?>
        </nav>

        <div class="header-actions">
            <?php if ($isAuthenticated): ?>
                <?php if ($isAdmin): ?>
                    <form class="global-search" action="<?= e(url('/admin/busca')) ?>" method="get" role="search">
                        <input type="search" name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="Buscar na TME">
                    </form>
                <?php endif; ?>
                <span class="user-chip">Olá, <?= e(explode(' ', trim($currentUser['full_name']))[0] ?: $currentUser['full_name']) ?></span>
                <div class="notification-menu" data-notification-menu>
                    <button class="icon-button notification-button" type="button" data-notification-toggle aria-label="Abrir notificações">
                        <span>!</span>
                        <?php if ($unreadNotifications > 0): ?>
                            <strong><?= e($unreadNotifications > 99 ? '99+' : $unreadNotifications) ?></strong>
                        <?php endif; ?>
                    </button>
                    <div class="notification-dropdown" data-notification-dropdown>
                        <div class="notification-dropdown-header">
                            <strong>Notificações</strong>
                            <?php if ($unreadNotifications > 0): ?>
                                <form action="<?= e(url('/notificacoes/ler-todas')) ?>" method="post">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="redirect_to" value="<?= e($path) ?>">
                                    <button type="submit">Ler todas</button>
                                </form>
                            <?php endif; ?>
                        </div>
                        <?php if (empty($recentNotifications)): ?>
                            <p class="notification-empty">Sem notificações recentes.</p>
                        <?php else: ?>
                            <?php foreach ($recentNotifications as $notification): ?>
                                <a class="notification-item <?= empty($notification['read_at']) ? 'unread' : '' ?>" href="<?= e(url($notification['action_url'] ?: '/notificacoes')) ?>">
                                    <strong><?= e($notification['title']) ?></strong>
                                    <span><?= e($notification['message']) ?></span>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <a class="notification-all" href="<?= e(url('/notificacoes')) ?>">Ver central</a>
                    </div>
                </div>
                <a class="profile-chip" href="<?= e(url('/perfil')) ?>" aria-label="Abrir perfil">
                    <span><?= e(strtoupper(substr($currentUser['full_name'], 0, 1))) ?></span>
                    Perfil
                </a>
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

    <?php if (! empty($usesCharts)): ?>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <?php endif; ?>
    <script src="<?= e(asset('assets/js/app.js')) ?>"></script>
</body>
</html>
