<?php
defined('BASE_PATH') || exit('Acesso direto não permitido.');

$isEdit = (bool) $course;
$value = static fn (string $key, mixed $default = ''): mixed => old($key, $course[$key] ?? $default);
?>

<section class="dashboard-shell">
    <div class="dashboard-heading">
        <span class="eyebrow">Cursos</span>
        <h1><?= $isEdit ? 'Editar curso' : 'Novo curso' ?></h1>
        <p>Defina os dados principais do curso. Módulos, aulas e materiais entram na tela de detalhe.</p>
    </div>

    <form class="form grid-form admin-form" action="<?= e($action) ?>" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <label class="span-2">
            Título
            <input type="text" name="title" value="<?= e($value('title')) ?>" required>
        </label>

        <label>
            Categoria
            <input type="text" name="category" value="<?= e($value('category', 'Geral')) ?>" required>
        </label>

        <label>
            Nível
            <select name="level" required>
                <?php foreach (['livre' => 'Livre', 'iniciante' => 'Iniciante', 'intermediario' => 'Intermediário', 'avancado' => 'Avançado'] as $option => $label): ?>
                    <option value="<?= e($option) ?>" <?= $value('level', 'livre') === $option ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            Carga horária
            <input type="number" name="workload_hours" min="0" value="<?= e($value('workload_hours', 0)) ?>">
        </label>

        <label>
            Preço
            <input type="number" name="price" min="0" step="0.01" value="<?= e($value('price', '0.00')) ?>">
        </label>

        <label>
            Acesso
            <select name="access_level">
                <?php foreach (['gratuito' => 'Gratuito', 'premium' => 'Premium'] as $option => $label): ?>
                    <option value="<?= e($option) ?>" <?= $value('access_level', 'gratuito') === $option ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            Status
            <select name="status">
                <?php foreach (['rascunho' => 'Rascunho', 'publicado' => 'Publicado', 'arquivado' => 'Arquivado'] as $option => $label): ?>
                    <option value="<?= e($option) ?>" <?= $value('status', 'rascunho') === $option ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            Professor responsável
            <select name="responsible_teacher_id">
                <option value="">Sem professor definido</option>
                <?php foreach ($teachers as $teacher): ?>
                    <option value="<?= e($teacher['id']) ?>" <?= (string) $value('responsible_teacher_id') === (string) $teacher['id'] ? 'selected' : '' ?>><?= e($teacher['full_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>

        <label class="span-2">
            Descrição
            <textarea name="description" rows="5"><?= e($value('description')) ?></textarea>
        </label>

        <label class="span-2">
            Imagem opcional
            <input type="file" name="image" accept="image/png,image/jpeg,image/webp">
            <?php if ($isEdit && ! empty($course['image_path'])): ?>
                <span class="muted">Imagem atual: <?= e($course['image_path']) ?></span>
            <?php endif; ?>
        </label>

        <div class="span-2 actions-row">
            <button class="button large" type="submit"><?= $isEdit ? 'Salvar alterações' : 'Criar curso' ?></button>
            <a class="button ghost large" href="<?= e($isEdit ? url('/admin/cursos/' . $course['id']) : url('/admin/cursos')) ?>">Cancelar</a>
        </div>
    </form>
</section>
