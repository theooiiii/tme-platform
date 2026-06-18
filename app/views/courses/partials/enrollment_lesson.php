<?php
defined('BASE_PATH') || exit('Acesso direto não permitido.');

$lessonProgress = $progressMap[(int) $lesson['id']] ?? null;
$isCompleted = $lessonProgress && $lessonProgress['status'] === 'concluida';
$videoUrl = trim((string) ($lesson['video_url'] ?? ''));
$embedUrl = media_embed_url($videoUrl);
?>

<div class="lesson-row <?= $isCompleted ? 'completed' : '' ?>">
    <div>
        <strong><?= e($lesson['position']) ?>. <?= e($lesson['title']) ?></strong>
        <span><?= e($lesson['lesson_type']) ?> • <?= e((int) $lesson['duration_minutes']) ?> min</span>
        <?php if (! empty($lesson['description'])): ?>
            <p><?= e($lesson['description']) ?></p>
        <?php endif; ?>
        <?php if ($videoUrl !== ''): ?>
            <div class="lesson-player">
                <?php if ($embedUrl): ?>
                    <iframe src="<?= e($embedUrl) ?>" title="Player da aula <?= e($lesson['title']) ?>" loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                <?php elseif (is_direct_video_url($videoUrl)): ?>
                    <video controls preload="metadata" playsinline>
                        <source src="<?= e($videoUrl) ?>">
                    </video>
                <?php else: ?>
                    <a class="button ghost" href="<?= e($videoUrl) ?>" target="_blank" rel="noopener">Abrir link da aula</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <?php if (! empty($lesson['content'])): ?>
            <div class="lesson-content-preview"><?= nl2br(e($lesson['content'])) ?></div>
        <?php endif; ?>
        <?php if (! empty($lesson['materials'])): ?>
            <div class="material-list">
                <?php foreach ($lesson['materials'] as $material): ?>
                    <?php if ($material['status'] !== 'ativo'): continue; endif; ?>
                    <span>
                        <?= e($material['material_type']) ?>:
                        <?php if (! empty($material['external_url'])): ?>
                            <a href="<?= e($material['external_url']) ?>" target="_blank" rel="noopener"><?= e($material['title']) ?></a>
                        <?php elseif (! empty($material['file_path'])): ?>
                            <a href="<?= e(url('/' . $material['file_path'])) ?>" target="_blank" rel="noopener"><?= e($material['title']) ?></a>
                        <?php else: ?>
                            <?= e($material['title']) ?>
                        <?php endif; ?>
                    </span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <details class="lesson-notes">
            <summary>Anotações da aula</summary>
            <textarea data-lesson-notes="<?= e($enrollment['id'] . ':' . $lesson['id']) ?>" rows="4" placeholder="Escreva suas anotações pessoais desta aula"></textarea>
        </details>
    </div>
    <div class="inline-actions lesson-progress-actions">
        <?php if ($isCompleted): ?>
            <span class="status-badge concluida">Concluída</span>
            <?php if (! empty($lessonProgress['completed_at'])): ?>
                <small><?= e(date('d/m/Y H:i', strtotime($lessonProgress['completed_at']))) ?></small>
            <?php endif; ?>
        <?php elseif ($enrollment['status'] !== 'cancelada'): ?>
            <form action="<?= e(url('/meus-cursos/' . $enrollment['id'] . '/aulas/' . $lesson['id'] . '/concluir')) ?>" method="post">
                <?= csrf_field() ?>
                <button class="button small" type="submit">Marcar concluída</button>
            </form>
        <?php else: ?>
            <span class="status-badge cancelada">Cancelada</span>
        <?php endif; ?>
    </div>
</div>
