<?php defined('BASE_PATH') || exit('Acesso direto não permitido.'); ?>

<section class="dashboard-shell attendance-shell">
    <div class="admin-toolbar">
        <div class="dashboard-heading">
            <span class="eyebrow">Relatórios</span>
            <h1>Relatório de frequência</h1>
            <p>Analise percentuais por turma, aluno, disciplina e período.</p>
        </div>
        <a class="button ghost large" href="<?= e(url('/frequencia')) ?>">Registrar chamada</a>
    </div>

    <form class="filter-form" action="<?= e(url('/frequencia/relatorio')) ?>" method="get">
        <label>
            Turma
            <select name="class_id" onchange="this.form.submit()">
                <option value="">Todas</option>
                <?php foreach ($classes as $class): ?>
                    <option value="<?= e($class['id']) ?>" <?= (int) $filters['class_id'] === (int) $class['id'] ? 'selected' : '' ?>>
                        <?= e($class['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>
            Disciplina
            <select name="subject_id">
                <option value="">Todas</option>
                <?php foreach ($subjects as $subject): ?>
                    <option value="<?= e($subject['id']) ?>" <?= (int) $filters['subject_id'] === (int) $subject['id'] ? 'selected' : '' ?>>
                        <?= e($subject['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>
            Aluno
            <select name="student_id">
                <option value="">Todos</option>
                <?php foreach ($students as $student): ?>
                    <option value="<?= e($student['id']) ?>" <?= (int) $filters['student_id'] === (int) $student['id'] ? 'selected' : '' ?>>
                        <?= e($student['full_name']) ?>
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
        <a class="button ghost" href="<?= e(url('/frequencia/relatorio')) ?>">Limpar</a>
    </form>

    <?php if (empty($rows)): ?>
        <div class="empty-state">
            <h2>Sem dados para o filtro</h2>
            <p>Registre chamadas para gerar indicadores de frequência.</p>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Aluno</th>
                        <th>Turma</th>
                        <th>Disciplina</th>
                        <th>Presenças</th>
                        <th>Atrasos</th>
                        <th>Justificadas</th>
                        <th>Faltas</th>
                        <th>Percentual</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <td><strong><?= e($row['student_name']) ?></strong></td>
                            <td><?= e($row['class_name']) ?></td>
                            <td><?= e($row['subject_name']) ?></td>
                            <td><?= e((int) $row['present_count']) ?></td>
                            <td><?= e((int) $row['late_count']) ?></td>
                            <td><?= e((int) $row['justified_count']) ?></td>
                            <td><?= e((int) $row['absence_count']) ?></td>
                            <td><strong><?= e(number_format((float) $row['attendance_percent'], 2, ',', '.')) ?>%</strong></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
