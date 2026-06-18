<?php
defined('BASE_PATH') || exit('Acesso direto não permitido.');

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
        'label' => 'Matrículas',
        'data' => array_map('intval', array_column($dashboardAnalytics['popular_courses'] ?? [], 'value')),
    ]],
];
$adminOverview = $adminOverview ?? [];
$totalFinishedEnrollments = (int) ($adminOverview['enrollments_active'] ?? 0) + (int) ($adminOverview['enrollments_completed'] ?? 0);
$completionRate = $totalFinishedEnrollments > 0 ? round(((int) ($adminOverview['enrollments_completed'] ?? 0) / $totalFinishedEnrollments) * 100) : 0;
?>

<section class="dashboard-shell">
    <div class="dashboard-heading">
        <span class="eyebrow">Administrador</span>
        <h1>Administração TME</h1>
        <p>Controle inicial de usuários, permissões, aprovações, cursos, atividades, biblioteca e matrículas.</p>
    </div>

    <div class="metric-grid">
        <article class="metric"><span>Contas pendentes</span><strong><?= e($counts['pending_users']) ?></strong></article>
        <article class="metric"><span>Usuários aprovados</span><strong><?= e($counts['approved_users']) ?></strong></article>
        <article class="metric"><span>Cursos</span><strong><?= e($counts['courses']) ?></strong></article>
        <article class="metric"><span>Certificados</span><strong><?= e($counts['certificates']) ?></strong></article>
        <article class="metric"><span>Receita confirmada</span><strong>R$ <?= e(number_format((float) ($adminOverview['revenue_paid'] ?? 0), 2, ',', '.')) ?></strong></article>
        <article class="metric"><span>Matrículas ativas</span><strong><?= e((int) ($adminOverview['enrollments_active'] ?? 0)) ?></strong></article>
        <article class="metric"><span>Taxa de conclusão</span><strong><?= e($completionRate) ?>%</strong></article>
        <article class="metric"><span>Atividade 24h</span><strong><?= e((int) ($adminOverview['activity_24h'] ?? 0)) ?></strong></article>
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
        <article class="module-card"><h2>Aprovação de contas</h2><p>Cadastros de alunos e professores entram como pendentes.</p><a href="<?= e(url('/admin/contas-pendentes')) ?>">Abrir fila</a></article>
        <article class="module-card"><h2>Usuários</h2><p>Controle perfis, status e instituições de toda a plataforma.</p><a href="<?= e(url('/admin/usuarios')) ?>">Gerenciar usuários</a></article>
        <article class="module-card"><h2>Permissões</h2><p>Audite papéis, permissões e responsabilidades por perfil.</p><a href="<?= e(url('/admin/permissoes')) ?>">Ver permissões</a></article>
        <article class="module-card"><h2>Cursos</h2><p>Crie cursos, módulos, aulas e materiais com filtros administrativos.</p><a href="<?= e(url('/admin/cursos')) ?>">Gerenciar cursos</a></article>
        <article class="module-card"><h2>Categorias</h2><p>Organize categorias usadas no catálogo e nos filtros de cursos.</p><a href="<?= e(url('/admin/categorias')) ?>">Organizar categorias</a></article>
        <article class="module-card"><h2>Matrículas</h2><p>Veja alunos matriculados, status e progresso por curso.</p><a href="<?= e(url('/admin/matriculas')) ?>">Ver matrículas</a></article>
        <article class="module-card"><h2>Atividades</h2><p>Crie tarefas e corrija entregas com nota e feedback.</p><a href="<?= e(url('/admin/atividades')) ?>">Gerenciar atividades</a></article>
        <article class="module-card"><h2>Biblioteca</h2><p>Modere materiais enviados e gerencie publicações.</p><a href="<?= e(url('/admin/biblioteca')) ?>">Gerenciar biblioteca</a></article>
        <article class="module-card"><h2>Certificados</h2><p>Liste certificados emitidos e revogue registros inválidos.</p><a href="<?= e(url('/admin/certificados')) ?>">Gerenciar certificados</a></article>
        <article class="module-card"><h2>Planos</h2><p>Gerencie planos gratuitos, premium e benefícios.</p><a href="<?= e(url('/admin/planos')) ?>">Gerenciar planos</a></article>
        <article class="module-card"><h2>Analytics</h2><p>Explore indicadores avançados por período.</p><a href="<?= e(url('/analytics')) ?>">Abrir analytics</a></article>
        <article class="module-card"><h2>Ranking</h2><p>Veja XP, níveis, moedas e badges da comunidade.</p><a href="<?= e(url('/ranking')) ?>">Abrir ranking</a></article>
        <article class="module-card"><h2>Logs</h2><p>Acompanhe eventos administrativos, segurança e atividades críticas.</p><a href="<?= e(url('/admin/logs')) ?>">Ver logs</a></article>
    </div>
</section>
