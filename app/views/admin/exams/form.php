<?php defined('BASE_PATH') || exit('Acesso direto não permitido.'); ?>

<section class="dashboard-shell exams-shell">
    <div class="admin-toolbar">
        <div class="dashboard-heading">
            <span class="eyebrow">Avaliação</span>
            <h1><?= e($title) ?></h1>
            <p>Configure escopo, janela de realizacao, tentativas e status inicial.</p>
        </div>
        <a class="button ghost large" href="<?= e(url('/admin/provas')) ?>">Voltar</a>
    </div>

    <form class="admin-form form" action="<?= e($action) ?>" method="post">
        <?= csrf_field() ?>

        <div class="form-grid">
            <label class="full">
                Título
                <input type="text" name="title" value="<?= e(old('title', $exam['title'] ?? '')) ?>" required>
            </label>
            <label class="full">
                Descrição
                <textarea name="description" rows="4"><?= e(old('description', $exam['description'] ?? '')) ?></textarea>
            </label>
            <label>
                Curso
                <select name="course_id">
                    <option value="">Sem curso especifico</option>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?= e($course['id']) ?>" <?= (int) old('course_id', $exam['course_id'] ?? 0) === (int) $course['id'] ? 'selected' : '' ?>>
                            <?= e($course['title']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                Turma
                <select name="class_id">
                    <option value="">Sem turma especifica</option>
                    <?php foreach ($classes as $class): ?>
                        <option value="<?= e($class['id']) ?>" <?= (int) old('class_id', $exam['class_id'] ?? 0) === (int) $class['id'] ? 'selected' : '' ?>>
                            <?= e($class['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                Disciplina
                <select name="subject_id">
                    <option value="">Geral</option>
                    <?php foreach ($subjects as $subject): ?>
                        <option value="<?= e($subject['id']) ?>" <?= (int) old('subject_id', $exam['subject_id'] ?? 0) === (int) $subject['id'] ? 'selected' : '' ?>>
                            <?= e($subject['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                Tempo limite (minutos)
                <input type="number" min="0" name="time_limit_minutes" value="<?= e(old('time_limit_minutes', $exam['time_limit_minutes'] ?? 60)) ?>">
            </label>
            <label>
                Início
                <input type="datetime-local" name="starts_at" value="<?= e(old('starts_at')) ?>">
            </label>
            <label>
                Fim
                <input type="datetime-local" name="ends_at" value="<?= e(old('ends_at')) ?>">
            </label>
            <label>
                Tentativas
                <input type="number" min="1" max="10" name="attempts_allowed" value="<?= e(old('attempts_allowed', $exam['attempts_allowed'] ?? 1)) ?>">
            </label>
            <label>
                Status
                <select name="status">
                    <?php foreach (['rascunho', 'publicado', 'encerrado'] as $status): ?>
                        <option value="<?= e($status) ?>" <?= old('status', $exam['status'] ?? 'rascunho') === $status ? 'selected' : '' ?>>
                            <?= e($status) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
        </div>

        <label class="checkbox-line">
            <input type="checkbox" name="auto_correction_enabled" value="1" checked>
            <span>Corrigir objetivas automaticamente</span>
        </label>
        <label class="checkbox-line">
            <input type="checkbox" name="ranking_enabled" value="1">
            <span>Habilitar ranking da prova</span>
        </label>

        <div class="form-actions">
            <button class="button large" type="submit">Salvar prova</button>
        </div>
    </form>
</section>
