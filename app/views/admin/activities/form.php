<?php
defined('BASE_PATH') || exit('Acesso direto não permitido.');

$isEdit = (bool) $activity;
$value = static fn (string $key, mixed $default = ''): mixed => old($key, $activity[$key] ?? $default);
$dateValue = $value('due_at') ? date('Y-m-d\TH:i', strtotime($value('due_at'))) : '';
?>

<section class="dashboard-shell">
    <div class="dashboard-heading">
        <span class="eyebrow">Atividades</span>
        <h1><?= e($isEdit ? 'Editar atividade' : 'Nova atividade') ?></h1>
        <p>Vincule a atividade a um curso e, opcionalmente, a módulo e aula.</p>
    </div>

    <form class="form grid-form admin-form" action="<?= e($action) ?>" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <label class="span-2">
            Título
            <input type="text" name="title" value="<?= e($value('title')) ?>" required>
        </label>

        <label>
            Curso
            <select name="course_id" required>
                <option value="">Selecione</option>
                <?php foreach ($courses as $course): ?>
                    <option value="<?= e($course['id']) ?>" <?= (string) $value('course_id') === (string) $course['id'] ? 'selected' : '' ?>><?= e($course['title']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            Professor responsavel
            <select name="teacher_id">
                <?php foreach ($teachers as $teacher): ?>
                    <option value="<?= e($teacher['id']) ?>" <?= (string) $value('teacher_id') === (string) $teacher['id'] ? 'selected' : '' ?>><?= e($teacher['full_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            Módulo opcional
            <select name="module_id">
                <option value="">Sem módulo</option>
                <?php foreach ($modules as $module): ?>
                    <option value="<?= e($module['id']) ?>" <?= (string) $value('module_id') === (string) $module['id'] ? 'selected' : '' ?>><?= e($module['course_title']) ?> - <?= e($module['title']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            Aula opcional
            <select name="lesson_id">
                <option value="">Sem aula</option>
                <?php foreach ($lessons as $lesson): ?>
                    <option value="<?= e($lesson['id']) ?>" <?= (string) $value('lesson_id') === (string) $lesson['id'] ? 'selected' : '' ?>><?= e($lesson['course_title']) ?> - <?= e($lesson['title']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            Tipo
            <select name="activity_type">
                <?php foreach (['texto' => 'Texto', 'arquivo' => 'Arquivo', 'quiz' => 'Quiz futuro', 'tarefa_pratica' => 'Tarefa prática', 'projeto' => 'Projeto'] as $option => $label): ?>
                    <option value="<?= e($option) ?>" <?= $value('activity_type', 'texto') === $option ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            Pontuação maxima
            <input type="number" name="max_score" min="0.01" step="0.01" value="<?= e($value('max_score', '10.00')) ?>">
        </label>

        <label>
            Prazo
            <input type="datetime-local" name="due_at" value="<?= e($dateValue) ?>">
        </label>

        <label>
            Status
            <select name="status">
                <?php foreach (['rascunho' => 'Rascunho', 'publicada' => 'Publicada', 'encerrada' => 'Encerrada'] as $option => $label): ?>
                    <option value="<?= e($option) ?>" <?= $value('status', 'rascunho') === $option ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </label>

        <label class="span-2 check-field">
            <input type="checkbox" name="allow_late" value="1" <?= (bool) $value('allow_late', true) ? 'checked' : '' ?>>
            Permitir envio atrasado marcando entrega como atrasada
        </label>

        <label class="span-2">
            Descrição
            <textarea name="description" rows="4"><?= e($value('description')) ?></textarea>
        </label>

        <label class="span-2">
            Instrucoes
            <textarea name="instructions" rows="6"><?= e($value('instructions')) ?></textarea>
        </label>

        <label class="span-2">
            Anexo opcional
            <input type="file" name="attachment">
            <?php if ($isEdit && ! empty($activity['attachment_path'])): ?>
                <span class="muted">Anexo atual: <?= e($activity['attachment_path']) ?></span>
            <?php endif; ?>
        </label>

        <div class="span-2 actions-row">
            <button class="button large" type="submit"><?= $isEdit ? 'Salvar atividade' : 'Criar atividade' ?></button>
            <a class="button ghost large" href="<?= e(url('/admin/atividades')) ?>">Cancelar</a>
        </div>
    </form>
</section>
