<?php
defined('BASE_PATH') || exit('Acesso direto não permitido.');

$totals = [
    'total' => 0,
    'present' => 0,
    'late' => 0,
    'justified' => 0,
    'absence' => 0,
];

foreach ($summary as $row) {
    $totals['total'] += (int) $row['total_records'];
    $totals['present'] += (int) $row['present_count'];
    $totals['late'] += (int) $row['late_count'];
    $totals['justified'] += (int) $row['justified_count'];
    $totals['absence'] += (int) $row['absence_count'];
}

$frequencyPercent = $totals['total'] > 0
    ? round((($totals['present'] + $totals['late'] + $totals['justified']) / $totals['total']) * 100, 2)
    : 0;
?>

<section class="dashboard-shell attendance-shell">
    <div class="admin-toolbar">
        <div class="dashboard-heading">
            <span class="eyebrow">Minha jornada</span>
            <h1>Minha frequência</h1>
            <p>Acompanhe seu histórico por turma, disciplina e período.</p>
        </div>
        <a class="button ghost large" href="<?= e(url('/portal')) ?>">Portal</a>
    </div>

    <div class="metric-grid">
        <article class="metric"><span>Frequência</span><strong><?= e(number_format($frequencyPercent, 2, ',', '.')) ?>%</strong></article>
        <article class="metric"><span>Presenças</span><strong><?= e($totals['present']) ?></strong></article>
        <article class="metric"><span>Atrasos</span><strong><?= e($totals['late']) ?></strong></article>
        <article class="metric"><span>Faltas</span><strong><?= e($totals['absence']) ?></strong></article>
    </div>

    <form class="filter-form" action="<?= e(url('/minha-frequencia')) ?>" method="get">
        <label>
            Turma
            <select name="class_id">
                <option value="">Todas</option>
                <?php foreach ($classes as $class): ?>
                    <option value="<?= e($class['id']) ?>" <?= (int) $filters['class_id'] === (int) $class['id'] ? 'selected' : '' ?>>
                        <?= e($class['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>
            De
            <input type="date" name="date_from" value="<?= e($filters['date_from'] ?? '') ?>">
        </label>
        <label>
            Até
            <input type="date" name="date_to" value="<?= e($filters['date_to'] ?? '') ?>">
        </label>
        <button class="button" type="submit">Filtrar</button>
        <a class="button ghost" href="<?= e(url('/minha-frequencia')) ?>">Limpar</a>
    </form>

    <?php if (empty($records)): ?>
        <div class="empty-state">
            <h2>Nenhum registro encontrado</h2>
            <p>Quando professores registrarem chamadas, seu histórico aparecerá aqui.</p>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Turma</th>
                        <th>Disciplina</th>
                        <th>Status</th>
                        <th>Observacao</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($records as $record): ?>
                        <tr>
                            <td><?= e(date('d/m/Y', strtotime($record['attendance_date']))) ?></td>
                            <td><?= e($record['class_name']) ?></td>
                            <td><?= e($record['subject_name']) ?></td>
                            <td><span class="status-badge <?= e($record['status']) ?>"><?= e(human_label($record['status'])) ?></span></td>
                            <td><?= e($record['note'] ?: '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
