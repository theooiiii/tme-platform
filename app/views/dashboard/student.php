<?php
defined('BASE_PATH') || exit('Acesso direto não permitido.');

$progressChart = [
    'type' => 'bar',
    'labels' => array_column($dashboardAnalytics['progress'] ?? [], 'label'),
    'datasets' => [[
        'label' => 'Progresso',
        'data' => array_map('floatval', array_column($dashboardAnalytics['progress'] ?? [], 'value')),
    ]],
];
$xpChart = [
    'type' => 'line',
    'labels' => array_column($dashboardAnalytics['xp'] ?? [], 'label'),
    'datasets' => [[
        'label' => 'XP',
        'data' => array_map('intval', array_column($dashboardAnalytics['xp'] ?? [], 'value')),
    ]],
];
?>

<section class="dashboard-shell">
    <div class="dashboard-heading">
        <span class="eyebrow">Aluno</span>
        <h1>Olá, <?= e($user['full_name']) ?></h1>
        <p>Seu espaco inicial para cursos, atividades, biblioteca, eventos e evolução acadêmica.</p>
    </div>

    <div class="metric-grid">
        <article class="metric"><span>Cursos ativos</span><strong><?= e($stats['enrolled_courses']) ?></strong></article>
        <article class="metric"><span>XP</span><strong><?= e((int) $profile['xp_total']) ?></strong></article>
        <article class="metric"><span>Atividades</span><strong><?= e($stats['submitted_activities']) ?></strong></article>
        <article class="metric"><span>Certificados</span><strong><?= e($stats['certificates']) ?></strong></article>
    </div>

    <div class="metric-grid">
        <article class="metric"><span>Progresso geral</span><strong><?= e(number_format((float) ($dashboardAnalytics['metrics']['average_progress'] ?? 0), 0)) ?>%</strong></article>
        <article class="metric"><span>Frequência</span><strong><?= e(number_format((float) ($dashboardAnalytics['metrics']['attendance_percent'] ?? 0), 0)) ?>%</strong></article>
        <article class="metric"><span>XP semanal</span><strong><?= e((int) ($dashboardAnalytics['metrics']['weekly_xp'] ?? 0)) ?></strong></article>
        <article class="metric"><span>Média provas</span><strong><?= e(number_format((float) ($dashboardAnalytics['metrics']['exam_average'] ?? 0), 1)) ?></strong></article>
    </div>

    <div class="chart-grid compact-chart-grid">
        <article class="chart-card">
            <div><span class="eyebrow">Cursos</span><h2>Progresso por curso</h2></div>
            <script type="application/json" id="dashboard-student-progress"><?= json_encode($progressChart, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
            <canvas data-chart="#dashboard-student-progress" height="220"></canvas>
        </article>
        <article class="chart-card">
            <div><span class="eyebrow">Gamificacao</span><h2>XP recente</h2></div>
            <script type="application/json" id="dashboard-student-xp"><?= json_encode($xpChart, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
            <canvas data-chart="#dashboard-student-xp" height="220"></canvas>
        </article>
    </div>

    <div class="module-grid">
        <article class="module-card"><h2>Minha aprendizagem</h2><p>Acompanhe matrículas, aulas concluídas e progresso por curso.</p><a href="<?= e(url('/meus-cursos')) ?>">Meus cursos</a></article>
        <article class="module-card"><h2>Catálogo</h2><p>Encontre cursos publicados e faça matrícula com um clique.</p><a href="<?= e(url('/aluno/cursos')) ?>">Ver catálogo</a></article>
        <article class="module-card"><h2>Atividades</h2><p>Veja tarefas abertas, envie respostas e acompanhe correcoes.</p><a href="<?= e(url('/atividades')) ?>">Minhas atividades</a></article>
        <article class="module-card"><h2>Boletim</h2><p>Resumo simples das notas por curso.</p><a href="<?= e(url('/boletim')) ?>">Ver boletim</a></article>
        <article class="module-card"><h2>Certificados</h2><p>Consulte certificados emitidos e valide códigos públicos.</p><a href="<?= e(url('/certificados')) ?>">Abrir certificados</a></article>
        <article class="module-card"><h2>Ranking</h2><p>Acompanhe XP, nível e badges na comunidade TME.</p><a href="<?= e(url('/ranking')) ?>">Ver ranking</a></article>
        <article class="module-card"><h2>Biblioteca</h2><p>Materiais publicados, favoritos e histórico de acesso.</p><a href="<?= e(url('/biblioteca')) ?>">Abrir biblioteca</a></article>
        <article class="module-card"><h2>Comunidade</h2><p>Projetos, publicações e comentários com moderação acadêmica.</p><a href="<?= e(url('/comunidade')) ?>">Entrar</a></article>
        <article class="module-card"><h2>Planos</h2><p>Acompanhe acesso gratuito ou premium.</p><a href="<?= e(url('/planos')) ?>">Ver planos</a></article>
        <article class="module-card"><h2>Notificações</h2><p>Alertas de cursos, provas, chat e conquistas.</p><a href="<?= e(url('/notificacoes')) ?>">Abrir central</a></article>
    </div>
</section>
