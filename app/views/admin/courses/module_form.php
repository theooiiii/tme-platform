<?php
defined('BASE_PATH') || exit('Acesso direto não permitido.');
$isEdit = (bool) $module;
$value = static fn (string $key, mixed $default = ''): mixed => old($key, $module[$key] ?? $default);
?>

<section class="dashboard-shell">
    <div class="dashboard-heading">
        <span class="eyebrow"><?= e($course['title']) ?></span>
        <h1><?= $isEdit ? 'Editar módulo' : 'Novo módulo' ?></h1>
        <p>Organize o curso em blocos de aprendizagem.</p>
    </div>

    <form class="form grid-form admin-form" action="<?= e($action) ?>" method="post">
        <?= csrf_field() ?>
        <label>
            Título
            <input type="text" name="title" value="<?= e($value('title')) ?>" required>
        </label>
        <label>
            Ordem
            <input type="number" name="position" min="1" value="<?= e($value('position', 1)) ?>">
        </label>
        <label class="span-2">
            Descrição
            <textarea name="description" rows="5"><?= e($value('description')) ?></textarea>
        </label>
        <div class="span-2 actions-row">
            <button class="button large" type="submit">Salvar módulo</button>
            <a class="button ghost large" href="<?= e(url('/admin/cursos/' . $course['id'])) ?>">Cancelar</a>
        </div>
    </form>
</section>
