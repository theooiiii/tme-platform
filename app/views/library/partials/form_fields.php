<?php defined('BASE_PATH') || exit('Acesso direto não permitido.'); ?>

<form class="form grid-form admin-form" action="<?= e($action) ?>" method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <label class="span-2">
        Título
        <input type="text" name="title" value="<?= e($value('title')) ?>" required>
    </label>

    <label>
        Categoria
        <input type="text" name="category" value="<?= e($value('category')) ?>" placeholder="Ex.: Matemática aplicada">
    </label>

    <label>
        Disciplina
        <input type="text" name="subject" value="<?= e($value('subject')) ?>" placeholder="Ex.: Álgebra">
    </label>

    <label>
        Tipo
        <select name="item_type">
            <?php foreach (['pdf', 'livro', 'apostila', 'artigo', 'video', 'link', 'apresentacao', 'imagem', 'arquivo'] as $type): ?>
                <option value="<?= e($type) ?>" <?= $value('item_type', 'arquivo') === $type ? 'selected' : '' ?>><?= e(human_label($type)) ?></option>
            <?php endforeach; ?>
        </select>
    </label>

    <label>
        Visibilidade
        <select name="visibility">
            <?php foreach (['publica' => 'Pública', 'logados' => 'Somente logados', 'curso' => 'Curso específico', 'privada_admin' => 'Privada/admin'] as $option => $label): ?>
                <option value="<?= e($option) ?>" <?= $value('visibility', 'publica') === $option ? 'selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select>
    </label>

    <label>
        Curso vinculado
        <select name="course_id">
            <option value="">Sem curso especifico</option>
            <?php foreach ($courses as $course): ?>
                <option value="<?= e($course['id']) ?>" <?= (string) $value('course_id') === (string) $course['id'] ? 'selected' : '' ?>><?= e($course['title']) ?></option>
            <?php endforeach; ?>
        </select>
    </label>

    <label>
        Autor
        <input type="text" name="author" value="<?= e($value('author', current_user()['full_name'] ?? '')) ?>">
    </label>

    <?php if (empty($contribution)): ?>
        <label>
            Status
            <select name="status">
                <?php foreach (['rascunho' => 'Rascunho', 'pendente' => 'Pendente', 'publicado' => 'Publicado', 'arquivado' => 'Arquivado'] as $option => $label): ?>
                    <option value="<?= e($option) ?>" <?= $value('status', 'pendente') === $option ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
    <?php endif; ?>

    <label class="span-2">
        Descrição
        <textarea name="description" rows="5"><?= e($value('description')) ?></textarea>
    </label>

    <label class="span-2">
        Link externo
        <input type="url" name="external_url" value="<?= e($value('external_url')) ?>" placeholder="https://...">
    </label>

    <label>
        Arquivo
        <input type="file" name="library_file">
    </label>

    <label>
        Capa opcional
        <input type="file" name="cover" accept="image/png,image/jpeg,image/webp">
    </label>

    <div class="span-2 actions-row">
        <button class="button large" type="submit"><?= empty($contribution) ? 'Salvar item' : 'Enviar para moderação' ?></button>
        <a class="button ghost large" href="<?= e(empty($contribution) ? url('/admin/biblioteca') : url('/biblioteca')) ?>">Cancelar</a>
    </div>
</form>
