<?php
defined('BASE_PATH') || exit('Acesso direto nao permitido.');

$adminActivityChart = [
    'type' => 'line',
    'labels' => array_column($dashboardAnalytics['activity'] ?? [], 'label'),
    'datasets' => [[
        'label' => 'Atividade',
        'data' => array_map('intval', array_column($dashboardAnalytics['activity'] ?? [], 'value')),
    ]],
];
$adminPopularChart = [
    'type' => 'bar',
    'labels' => array_column($dashboardAnalytics['popular_courses'] ?? [], 'label'),
    'datasets' => [[
        'label' => 'Matriculas',
        'data' => array_map('intval', array_column($dashboardAnalytics['popular_courses'] ?? [], 'value')),
    ]],
];
?>

<section class="dashboard-shell">
    <div class="dashboard-heading">
        <span class="eyebrow">Administrador</span>
        <h1>Administracao TME</h1>
        <p>Controle inicial de usuarios, permissoes, aprovacoes, cursos, atividades, biblioteca e matriculas.</p>
    </div>

    <div class="metric-grid">
        <article class="metric"><span>Contas pendentes</span><strong><?= e($counts['pending_users']) ?></strong></article>
        <article class="metric"><span>Usuarios aprovados</span><strong><?= e($counts['approved_users']) ?></strong></article>
        <article class="metric"><span>Cursos</span><strong><?= e($counts['courses']) ?></strong></article>
        <article class="metric"><span>Certificados</span><strong><?= e($counts['certificates']) ?></strong></article>
    </div>

    <div class="chart-grid compact-chart-grid">
        <article class="chart-card">
            <div><span class="eyebrow">Plataforma</span><h2>Atividade recente</h2></div>
            <script type="application/json" id="dashboard-admin-activity"><?= json_encode($adminActivityChart, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
            <canvas data-chart="#dashboard-admin-activity" height="220"></canvas>
        </article>
        <article class="chart-card">
            <div><span class="eyebrow">Cursos</span><h2>Mais populares</h2></div>
            <script type="application/json" id="dashboard-admin-popular"><?= json_encode($adminPopularChart, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
            <canvas data-chart="#dashboard-admin-popular" height="220"></canvas>
        </article>
    </div>

    <div class="module-grid">
        <article class="module-card"><h2>Aprovacao de contas</h2><p>Cadastros de alunos e professores entram como pendentes.</p><a href="<?= e(url('/admin/contas-pendentes')) ?>">Abrir fila</a></article>
        <article class="module-card"><h2>Cursos</h2><p>Crie cursos, modulos, aulas e materiais com filtros administrativos.</p><a href="<?= e(url('/admin/cursos')) ?>">Gerenciar cursos</a></article>
        <article class="module-card"><h2>Matriculas</h2><p>Veja alunos matriculados, status e progresso por curso.</p><a href="<?= e(url('/admin/matriculas')) ?>">Ver matriculas</a></article>
        <article class="module-card"><h2>Atividades</h2><p>Crie tarefas e corrija entregas com nota e feedback.</p><a href="<?= e(url('/admin/atividades')) ?>">Gerenciar atividades</a></article>
        <article class="module-card"><h2>Biblioteca</h2><p>Modere materiais enviados e gerencie publicacoes.</p><a href="<?= e(url('/admin/biblioteca')) ?>">Gerenciar biblioteca</a></article>
        <article class="module-card"><h2>Certificados</h2><p>Liste certificados emitidos e revogue registros invalidos.</p><a href="<?= e(url('/admin/certificados')) ?>">Gerenciar certificados</a></article>
        <article class="module-card"><h2>Planos</h2><p>Gerencie planos gratuitos, premium e beneficios.</p><a href="<?= e(url('/admin/planos')) ?>">Gerenciar planos</a></article>
        <article class="module-card"><h2>Analytics</h2><p>Explore indicadores avancados por periodo.</p><a href="<?= e(url('/analytics')) ?>">Abrir analytics</a></article>
        <article class="module-card"><h2>Ranking</h2><p>Veja XP, niveis, moedas e badges da comunidade.</p><a href="<?= e(url('/ranking')) ?>">Abrir ranking</a></article>
    </div>
</section>
