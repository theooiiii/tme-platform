<?php defined('BASE_PATH') || exit('Acesso direto nao permitido.'); ?>

<div class="lesson-row">
    <div>
        <strong><?= e($lesson['position']) ?>. <?= e($lesson['title']) ?></strong>
        <span><?= e($lesson['lesson_type']) ?> • <?= e((int) $lesson['duration_minutes']) ?> min • <?= e($lesson['status']) ?></span>
        <?php if (! empty($lesson['description'])): ?>
            <p><?= e($lesson['description']) ?></p>
        <?php endif; ?>
        <?php if (! empty($lesson['materials'])): ?>
            <div class="material-list">
                <?php foreach ($lesson['materials'] as $material): ?>
                    <span>
                        <?= e($material['material_type']) ?>:
                        <?php if (! empty($material['external_url'])): ?>
                            <a href="<?= e($material['external_url']) ?>" target="_blank" rel="noopener"><?= e($material['title']) ?></a>
                        <?php elseif (! empty($material['file_path'])): ?>
                            <a href="<?= e(url('/' . $material['file_path'])) ?>" target="_blank" rel="noopener"><?= e($material['title']) ?></a>
                        <?php else: ?>
                            <?= e($material['title']) ?>
                        <?php endif; ?>
                        <a href="<?= e(url('/admin/cursos/' . $course['id'] . '/aulas/' . $lesson['id'] . '/materiais/' . $material['id'] . '/editar')) ?>">editar</a>
                    </span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <div class="inline-actions">
        <a href="<?= e(url('/admin/cursos/' . $course['id'] . '/aulas/' . $lesson['id'] . '/materiais/novo')) ?>">Material</a>
        <a href="<?= e(url('/admin/cursos/' . $course['id'] . '/aulas/' . $lesson['id'] . '/editar')) ?>">Editar</a>
        <form action="<?= e(url('/admin/cursos/' . $course['id'] . '/aulas/' . $lesson['id'] . '/excluir')) ?>" method="post" data-confirm="Excluir esta aula?">
            <?= csrf_field() ?>
            <button type="submit">Excluir</button>
        </form>
    </div>
</div>
