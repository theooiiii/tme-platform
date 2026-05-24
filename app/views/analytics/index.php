<?php
defined('BASE_PATH') || exit('Acesso direto nao permitido.');

$activityChart = [
    'type' => 'line',
    'labels' => array_column($analytics['activity'], 'label'),
    'datasets' => [[
        'label' => 'Atividade da plataforma',
        'data' => array_map('intval', array_column($analytics['activity'], 'value')),
    ]],
];
$growthChart = [
    'type' => 'bar',
    'labels' => array_column($analytics['growth'], 'label'),
    'datasets' => [[
        'label' => 'Crescimento de usuarios',
        'data' => array_map('intval', array_column($analytics['growth'], 'value')),
    ]],
];
$popularChart = [
    'type' => 'bar',
    'labels' => array_column($analytics['popular_courses'], 'label'),
    'datasets' => [[
        'label' => 'Matriculas',
        'data' => array_map('intval', array_column($analytics['popular_courses'], 'value')),
    ]],
];
?>

<section class="dashboard-shell analytics-shell">
    <div class="admin-toolbar">
        <div class="dashboard-heading">
            <span class="eyebrow">Analytics</span>
            <h1>Dashboard avancado</h1>
            <p>Indicadores reais integrados aos modulos de usuarios, matriculas, certificados, logs e financeiro.</p>
        </div>
        <form class="filter-form compact-filter" method="get" action="<?= e(url('/analytics')) ?>">
            <label>
                Periodo
                <select name="dias">
                    <?php foreach ([7, 30, 90, 180, 365] as $days): ?>
                        <option value="<?= e($days) ?>" <?= (int) $period['days'] === $days ? 'selected' : '' ?>><?= e($days) ?> dias</option>
                    <?php endforeach; ?>
                </select>
            </label>
            <button class="button" type="submit">Filtrar</button>
        </form>
    </div>

    <div class="metric-grid">
        <article class="metric"><span>Usuarios ativos</span><strong><?= e($analytics['metrics']['active_users']) ?></strong></article>
        <article class="metric"><span>Matriculas</span><strong><?= e($analytics['metrics']['enrollments']) ?></strong></article>
        <article class="metric"><span>Certificados</span><strong><?= e($analytics['metrics']['certificates']) ?></strong></article>
        <article class="metric"><span>Receita paga</span><strong>R$ <?= e(number_format((float) $analytics['metrics']['revenue'], 2, ',', '.')) ?></strong></article>
    </div>

    <div class="chart-grid">
        <article class="chart-card">
            <div>
                <span class="eyebrow">Logs</span>
                <h2>Atividade da plataforma</h2>
            </div>
            <script type="application/json" id="chart-activity"><?= json_encode($activityChart, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
            <canvas data-chart="#chart-activity" height="260"></canvas>
        </article>

        <article class="chart-card">
            <div>
                <span class="eyebrow">Usuarios</span>
                <h2>Crescimento</h2>
            </div>
            <script type="application/json" id="chart-growth"><?= json_encode($growthChart, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
            <canvas data-chart="#chart-growth" height="260"></canvas>
        </article>

        <article class="chart-card span-2">
            <div>
                <span class="eyebrow">Cursos</span>
                <h2>Cursos populares</h2>
            </div>
            <script type="application/json" id="chart-popular"><?= json_encode($popularChart, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
            <canvas data-chart="#chart-popular" height="260"></canvas>
        </article>
    </div>
</section>
