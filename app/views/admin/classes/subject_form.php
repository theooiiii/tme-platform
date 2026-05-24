<?php defined('BASE_PATH') || exit('Acesso direto nao permitido.'); ?>

<section class="dashboard-shell">
    <div class="dashboard-heading">
        <span class="eyebrow">Disciplinas</span>
        <h1><?= e($title) ?></h1>
    </div>
    <form class="admin-form form grid-form" action="<?= e($action) ?>" method="post">
        <?= csrf_field() ?>
        <label>
            Nome
            <input type="text" name="name" value="<?= e(old('name', $subject['name'] ?? '')) ?>" required>
        </label>
        <label>
            Area
            <input type="text" name="area" value="<?= e(old('area', $subject['area'] ?? '')) ?>">
        </label>
        <label>
            Carga horaria
            <input type="number" name="workload_hours" min="0" value="<?= e(old('workload_hours', $subject['workload_hours'] ?? 0)) ?>">
        </label>
        <label>
            Status
            <select name="status">
                <?php foreach (['ativa' => 'Ativa', 'inativa' => 'Inativa', 'arquivada' => 'Arquivada'] as $value => $label): ?>
                    <option value="<?= e($value) ?>" <?= old('status', $subject['status'] ?? 'ativa') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label class="span-2">
            Descricao
            <textarea name="description" rows="4"><?= e(old('description', $subject['description'] ?? '')) ?></textarea>
        </label>
        <button class="button span-2" type="submit">Salvar disciplina</button>
    </form>
</section>
