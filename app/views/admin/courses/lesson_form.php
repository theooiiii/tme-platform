<?php
defined('BASE_PATH') || exit('Acesso direto nao permitido.');
$isEdit = (bool) $lesson;
$value = static fn (string $key, mixed $default = ''): mixed => old($key, $lesson[$key] ?? $default);
?>

<section class="dashboard-shell">
    <div class="dashboard-heading">
        <span class="eyebrow"><?= e($course['title']) ?></span>
        <h1><?= $isEdit ? 'Editar aula' : 'Nova aula' ?></h1>
        <p>Cadastre aulas de vídeo, texto, links, encontros ao vivo ou arquivos.</p>
    </div>

    <form class="form grid-form admin-form" action="<?= e($action) ?>" method="post">
        <?= csrf_field() ?>
        <label class="span-2">
            Título
            <input type="text" name="title" value="<?= e($value('title')) ?>" required>
        </label>
        <label>
            Módulo
            <select name="module_id">
                <option value="">Sem módulo</option>
                <?php foreach ($modules as $module): ?>
                    <option value="<?= e($module['id']) ?>" <?= (string) $value('module_id') === (string) $module['id'] ? 'selected' : '' ?>><?= e($module['position']) ?>. <?= e($module['title']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>
            Tipo de aula
            <select name="lesson_type">
                <?php foreach (['video' => 'Vídeo', 'texto' => 'Texto', 'ao_vivo' => 'Ao vivo', 'arquivo' => 'Arquivo', 'link' => 'Link'] as $option => $label): ?>
                    <option value="<?= e($option) ?>" <?= $value('lesson_type', 'video') === $option ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>
            Ordem
            <input type="number" name="position" min="1" value="<?= e($value('position', 1)) ?>">
        </label>
        <label>
            Duração em minutos
            <input type="number" name="duration_minutes" min="0" value="<?= e($value('duration_minutes', 0)) ?>">
        </label>
        <label>
            Status
            <select name="status">
                <?php foreach (['rascunho' => 'Rascunho', 'publicada' => 'Publicada', 'arquivada' => 'Arquivada'] as $option => $label): ?>
                    <option value="<?= e($option) ?>" <?= $value('status', 'rascunho') === $option ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>
            Vídeo ou link
            <input type="url" name="video_url" value="<?= e($value('video_url')) ?>" placeholder="https://">
        </label>
        <label class="span-2">
            Descrição
            <textarea name="description" rows="4"><?= e($value('description')) ?></textarea>
        </label>
        <label class="span-2">
            Conteúdo textual
            <textarea name="content" rows="8"><?= e($value('content')) ?></textarea>
        </label>
        <div class="span-2 actions-row">
            <button class="button large" type="submit">Salvar aula</button>
            <a class="button ghost large" href="<?= e(url('/admin/cursos/' . $course['id'])) ?>">Cancelar</a>
        </div>
    </form>
</section>
