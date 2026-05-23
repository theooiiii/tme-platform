<?php
defined('BASE_PATH') || exit('Acesso direto nao permitido.');
$isEdit = (bool) $material;
$value = static fn (string $key, mixed $default = ''): mixed => old($key, $material[$key] ?? $default);
?>

<section class="dashboard-shell">
    <div class="dashboard-heading">
        <span class="eyebrow"><?= e($lesson['title']) ?></span>
        <h1><?= $isEdit ? 'Editar material' : 'Novo material' ?></h1>
        <p>Vincule PDFs, imagens, links externos ou arquivos de apoio à aula.</p>
    </div>

    <form class="form grid-form admin-form" action="<?= e($action) ?>" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <label class="span-2">
            Título
            <input type="text" name="title" value="<?= e($value('title')) ?>" required>
        </label>
        <label>
            Tipo
            <select name="material_type" data-material-type>
                <?php foreach (['pdf' => 'PDF', 'imagem' => 'Imagem', 'link' => 'Link externo', 'arquivo' => 'Arquivo', 'livro' => 'Livro', 'apostila' => 'Apostila', 'video' => 'Vídeo'] as $option => $label): ?>
                    <option value="<?= e($option) ?>" <?= $value('material_type', 'arquivo') === $option ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>
            Visibilidade
            <select name="visibility">
                <?php foreach (['privado' => 'Privado', 'publico' => 'Público', 'institucional' => 'Institucional'] as $option => $label): ?>
                    <option value="<?= e($option) ?>" <?= $value('visibility', 'privado') === $option ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>
            Status
            <select name="status">
                <option value="ativo" <?= $value('status', 'ativo') === 'ativo' ? 'selected' : '' ?>>Ativo</option>
                <option value="inativo" <?= $value('status', 'ativo') === 'inativo' ? 'selected' : '' ?>>Inativo</option>
            </select>
        </label>
        <label>
            Link externo
            <input type="url" name="external_url" value="<?= e($value('external_url')) ?>" placeholder="https://">
        </label>
        <label class="span-2">
            Arquivo
            <input type="file" name="material_file">
            <?php if ($isEdit && ! empty($material['file_path'])): ?>
                <span class="muted">Arquivo atual: <?= e($material['file_path']) ?></span>
            <?php endif; ?>
        </label>
        <label class="span-2">
            Descrição
            <textarea name="description" rows="5"><?= e($value('description')) ?></textarea>
        </label>
        <div class="span-2 actions-row">
            <button class="button large" type="submit">Salvar material</button>
            <a class="button ghost large" href="<?= e(url('/admin/cursos/' . $course['id'])) ?>">Cancelar</a>
        </div>
    </form>
</section>
