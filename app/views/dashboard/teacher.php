<?php
defined('BASE_PATH') || exit('Acesso direto não permitido.');

$teacherSubmissionsChart = [
    'type' => 'line',
    'labels' => array_column($dashboardAnalytics['submissions'] ?? [], 'label'),
    'datasets' => [[
        'label' => 'Entregas',
        'data' => array_map('intval', array_column($dashboardAnalytics['submissions'] ?? [], 'value')),
    ]],
];
$teacherProgressChart = [
    'type' => 'bar',
    'labels' => array_column($dashboardAnalytics['progress'] ?? [], 'label'),
    'datasets' => [[
        'label' => 'Progresso médio',
        'data' => array_map('floatval', array_column($dashboardAnalytics['progress'] ?? [], 'value')),
    ]],
];
?>

<section class="dashboard-shell">
    <div class="dashboard-heading">
        <span class="eyebrow">Professor</span>
        <h1>Painel docente</h1>
        <p>Bem-vindo, <?= e($user['full_name']) ?>. A base docente está pronta para cursos, turmas, atividades, biblioteca e correcoes.</p>
    </div>

    <div class="metric-grid">
        <article class="metric"><span>Cursos</span><strong><?= e($counts['courses']) ?></strong></article>
        <article class="metric"><span>XP</span><strong><?= e((int) $profile['xp_total']) ?></strong></article>
        <article class="metric"><span>Nivel</span><strong><?= e((int) $profile['level']) ?></strong></article>
        <article class="metric"><span>Certificados</span><strong><?= e($stats['certificates']) ?></strong></article>
    </div>

    <div class="metric-grid">
        <article class="metric"><span>Alunos ativos</span><strong><?= e((int) ($dashboardAnalytics['metrics']['active_students'] ?? 0)) ?></strong></article>
        <article class="metric"><span>Entregas pendentes</span><strong><?= e((int) ($dashboardAnalytics['metrics']['pending_submissions'] ?? 0)) ?></strong></article>
        <article class="metric"><span>Desempenho medio</span><strong><?= e(number_format((float) ($dashboardAnalytics['metrics']['average_score'] ?? 0), 1)) ?></strong></article>
        <article class="metric"><span>Progresso turmas</span><strong><?= e(number_format((float) ($dashboardAnalytics['metrics']['average_progress'] ?? 0), 0)) ?>%</strong></article>
    </div>

    <div class="chart-grid compact-chart-grid">
        <article class="chart-card">
            <div><span class="eyebrow">Correções</span><h2>Entregas recentes</h2></div>
            <script type="application/json" id="dashboard-teacher-submissions"><?= json_encode($teacherSubmissionsChart, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
            <canvas data-chart="#dashboard-teacher-submissions" height="220"></canvas>
        </article>
        <article class="chart-card">
            <div><span class="eyebrow">Cursos</span><h2>Progresso médio</h2></div>
            <script type="application/json" id="dashboard-teacher-progress"><?= json_encode($teacherProgressChart, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
            <canvas data-chart="#dashboard-teacher-progress" height="220"></canvas>
        </article>
    </div>

    <div class="module-grid">
        <article class="module-card"><h2>Estudar na TME</h2><p>Professores também podem se matricular e acompanhar progresso como alunos.</p><a href="<?= e(url('/aluno/cursos')) ?>">Catálogo</a></article>
        <article class="module-card"><h2>Meus cursos</h2><p>Continue cursos em andamento e revise aulas concluidas.</p><a href="<?= e(url('/meus-cursos')) ?>">Abrir cursos</a></article>
        <article class="module-card"><h2>Atividades</h2><p>Crie tarefas, projetos e acompanhe entregas dos alunos.</p><a href="<?= e(url('/admin/atividades')) ?>">Gerenciar</a></article>
        <article class="module-card"><h2>Correções</h2><p>Avalie entregas com nota, feedback e status.</p><a href="<?= e(url('/admin/atividades')) ?>">Corrigir</a></article>
        <article class="module-card"><h2>Biblioteca</h2><p>Envie materiais didáticos para moderação e publicação.</p><a href="<?= e(url('/admin/biblioteca')) ?>">Materiais</a></article>
        <article class="module-card"><h2>Certificados</h2><p>Professores também recebem certificados ao concluir cursos.</p><a href="<?= e(url('/certificados')) ?>">Abrir certificados</a></article>
        <article class="module-card"><h2>Ranking</h2><p>Acompanhe XP, nível, moedas e badges.</p><a href="<?= e(url('/ranking')) ?>">Ver ranking</a></article>
        <article class="module-card"><h2>Publicações</h2><p>Posts e projetos podem alimentar a comunidade acadêmica.</p><a href="<?= e(url('/comunidade')) ?>">Comunidade</a></article>
        <article class="module-card"><h2>Financeiro</h2><p>Carteira de creator preparada para monetização 80/20.</p><a href="<?= e(url('/financeiro')) ?>">Abrir financeiro</a></article>
        <article class="module-card"><h2>Notificações</h2><p>Acompanhe avisos de entregas, chat e comunidade.</p><a href="<?= e(url('/notificacoes')) ?>">Abrir central</a></article>
    </div>
</section>
