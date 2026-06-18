<?php defined('BASE_PATH') || exit('Acesso direto não permitido.'); ?>

<section class="dashboard-shell">
    <div class="dashboard-heading">
        <span class="eyebrow">Turmas</span>
        <h1><?= e($title) ?></h1>
    </div>
    <form class="admin-form form grid-form" action="<?= e($action) ?>" method="post">
        <?= csrf_field() ?>
        <label>
            Nome
            <input type="text" name="name" value="<?= e(old('name', $class['name'] ?? '')) ?>" required>
        </label>
        <label>
            Instituição
            <select name="institution_id">
                <option value="">Nenhuma</option>
                <?php foreach ($institutions as $institution): ?>
                    <option value="<?= e($institution['id']) ?>" <?= (string) old('institution_id', $class['institution_id'] ?? '') === (string) $institution['id'] ? 'selected' : '' ?>><?= e($institution['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>
            Período
            <input type="text" name="period" value="<?= e(old('period', $class['period'] ?? '')) ?>" placeholder="2026.1, manha, noite">
        </label>
        <label>
            Status
            <select name="status">
                <?php foreach (['ativa' => 'Ativa', 'inativa' => 'Inativa', 'arquivada' => 'Arquivada'] as $value => $label): ?>
                    <option value="<?= e($value) ?>" <?= old('status', $class['status'] ?? 'ativa') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label class="span-2">
            Descrição
            <textarea name="description" rows="4"><?= e(old('description', $class['description'] ?? '')) ?></textarea>
        </label>
        <button class="button span-2" type="submit">Salvar turma</button>
    </form>
</section>
