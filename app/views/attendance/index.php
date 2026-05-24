<?php
defined('BASE_PATH') || exit('Acesso direto nao permitido.');

$statusLabels = [
    'presente' => 'Presente',
    'falta' => 'Falta',
    'atraso' => 'Atraso',
    'justificado' => 'Justificado',
];
?>

<section class="dashboard-shell attendance-shell">
    <div class="admin-toolbar">
        <div class="dashboard-heading">
            <span class="eyebrow">Registro academico</span>
            <h1>Frequencia</h1>
            <p>Registre presenca por turma, disciplina e data com observacoes individuais.</p>
        </div>
        <a class="button ghost large" href="<?= e(url('/frequencia/relatorio')) ?>">Relatorios</a>
    </div>

    <form class="filter-form" action="<?= e(url('/frequencia')) ?>" method="get">
        <label>
            Turma
            <select name="class_id" onchange="this.form.submit()">
                <?php foreach ($classes as $class): ?>
                    <option value="<?= e($class['id']) ?>" <?= (int) $classId === (int) $class['id'] ? 'selected' : '' ?>>
                        <?= e($class['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>
            Disciplina
            <select name="subject_id">
                <?php foreach ($subjects as $subject): ?>
                    <option value="<?= e($subject['id']) ?>" <?= (int) $subjectId === (int) $subject['id'] ? 'selected' : '' ?>>
                        <?= e($subject['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>
            Data
            <input type="date" name="date" value="<?= e($date) ?>">
        </label>
        <button class="button" type="submit">Carregar chamada</button>
    </form>

    <?php if (empty($classes)): ?>
        <div class="empty-state">
            <h2>Nenhuma turma disponivel</h2>
            <p>Vincule turmas e disciplinas antes de registrar frequencia.</p>
        </div>
    <?php elseif (empty($students)): ?>
        <div class="empty-state">
            <h2>Sem alunos vinculados</h2>
            <p>A turma selecionada ainda nao possui alunos ativos.</p>
        </div>
    <?php else: ?>
        <form action="<?= e(url('/frequencia')) ?>" method="post" class="attendance-form">
            <?= csrf_field() ?>
            <input type="hidden" name="class_id" value="<?= e($classId) ?>">
            <input type="hidden" name="subject_id" value="<?= e($subjectId) ?>">
            <input type="hidden" name="date" value="<?= e($date) ?>">

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Aluno</th>
                            <th>Status</th>
                            <th>Observacao</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <?php
                            $record = $records[(int) $student['id']] ?? [];
                            $selectedStatus = $record['status'] ?? 'presente';
                            ?>
                            <tr>
                                <td>
                                    <strong><?= e($student['full_name']) ?></strong>
                                    <span><?= e($student['email']) ?></span>
                                </td>
                                <td>
                                    <div class="attendance-status-grid">
                                        <?php foreach ($statusLabels as $value => $label): ?>
                                            <label class="status-option">
                                                <input
                                                    type="radio"
                                                    name="attendance[<?= e($student['id']) ?>]"
                                                    value="<?= e($value) ?>"
                                                    <?= $selectedStatus === $value ? 'checked' : '' ?>
                                                >
                                                <span><?= e($label) ?></span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                                <td>
                                    <input
                                        type="text"
                                        name="notes[<?= e($student['id']) ?>]"
                                        value="<?= e($record['note'] ?? '') ?>"
                                        placeholder="Opcional"
                                    >
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="form-actions">
                <button class="button large" type="submit">Salvar chamada</button>
            </div>
        </form>
    <?php endif; ?>
</section>
