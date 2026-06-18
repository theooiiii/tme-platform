<?php
defined('BASE_PATH') || exit('Acesso direto não permitido.');

$isLate = ! empty($activity['due_at']) && strtotime($activity['due_at']) < time();
$canSubmit = ! $isLate || (bool) $activity['allow_late'];
$submissionStatus = $submission['status'] ?? 'pendente';
?>

<section class="dashboard-shell">
    <div class="detail-grid">
        <div class="detail-panel">
            <span class="eyebrow"><?= e($activity['course_title'] ?? 'Curso') ?></span>
            <h1><?= e($activity['title']) ?></h1>
            <p><?= e($activity['description'] ?: 'Atividade do curso.') ?></p>
            <div class="course-meta spacious">
                <span><?= e($activity['activity_type']) ?></span>
                <span><?= e(number_format((float) $activity['max_score'], 2, ',', '.')) ?> pts</span>
                <span>Prazo <?= e($activity['due_at'] ? date('d/m/Y H:i', strtotime($activity['due_at'])) : 'livre') ?></span>
                <span>Entrega <?= e($submissionStatus) ?></span>
            </div>
            <?php if (! empty($activity['instructions'])): ?>
                <div class="lesson-content-preview"><?= nl2br(e($activity['instructions'])) ?></div>
            <?php endif; ?>
            <?php if (! empty($activity['attachment_path'])): ?>
                <p><a class="button ghost" href="<?= e(url('/' . $activity['attachment_path'])) ?>" target="_blank" rel="noopener">Abrir anexo da atividade</a></p>
            <?php endif; ?>
        </div>

        <div class="detail-panel">
            <span class="eyebrow">Entrega</span>
            <?php if ($submission): ?>
                <h2>Status: <?= e($submission['status']) ?></h2>
                <p>Enviado em <?= e(date('d/m/Y H:i', strtotime($submission['submitted_at']))) ?></p>
                <?php if (! empty($submission['content'])): ?>
                    <div class="lesson-content-preview"><?= nl2br(e($submission['content'])) ?></div>
                <?php endif; ?>
                <?php if (! empty($submission['file_path'])): ?>
                    <p><a href="<?= e(url('/' . $submission['file_path'])) ?>" target="_blank" rel="noopener">Abrir arquivo enviado</a></p>
                <?php endif; ?>
                <?php if ($submission['score'] !== null): ?>
                    <p><strong>Nota:</strong> <?= e(number_format((float) $submission['score'], 2, ',', '.')) ?> / <?= e(number_format((float) $activity['max_score'], 2, ',', '.')) ?></p>
                    <?php if (! empty($submission['feedback'])): ?>
                        <p><strong>Feedback:</strong> <?= nl2br(e($submission['feedback'])) ?></p>
                    <?php endif; ?>
                <?php endif; ?>
            <?php else: ?>
                <h2>Entrega pendente</h2>
                <p><?= $isLate ? 'O prazo já passou.' : 'Envie sua resposta antes do prazo.' ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="section-toolbar">
        <span class="eyebrow">Responder</span>
        <h2>Enviar atividade</h2>
    </div>

    <?php if (! $canSubmit): ?>
        <div class="empty-state">
            <h2>Prazo encerrado</h2>
            <p>Esta atividade não aceita envios atrasados.</p>
        </div>
    <?php elseif ($submission && in_array($submission['status'], ['corrigida', 'devolvida'], true)): ?>
        <div class="empty-state">
            <h2>Entrega já avaliada</h2>
            <p>Entregas corrigidas ou devolvidas ficam bloqueadas para novo envio nesta versao.</p>
        </div>
    <?php else: ?>
        <form class="admin-form form" action="<?= e(url('/atividades/' . $activity['id'] . '/entregar')) ?>" method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <label>
                Resposta textual
                <textarea name="content" rows="8"><?= e($submission['content'] ?? '') ?></textarea>
            </label>
            <label>
                Arquivo opcional
                <input type="file" name="submission_file">
            </label>
            <button class="button large" type="submit"><?= $isLate ? 'Enviar atrasada' : 'Enviar atividade' ?></button>
        </form>
    <?php endif; ?>
</section>
