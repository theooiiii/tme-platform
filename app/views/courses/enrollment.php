<?php defined('BASE_PATH') || exit('Acesso direto não permitido.'); ?>

<section class="dashboard-shell">
    <div class="detail-grid">
        <?php if (! empty($enrollment['image_path'])): ?>
            <img class="course-cover" src="<?= e(url('/' . $enrollment['image_path'])) ?>" alt="Imagem do curso <?= e($enrollment['course_title']) ?>">
        <?php else: ?>
            <div class="course-cover placeholder">TME</div>
        <?php endif; ?>

        <div class="dashboard-heading">
            <span class="eyebrow">Matrícula <?= e($enrollment['status']) ?></span>
            <h1><?= e($enrollment['course_title']) ?></h1>
            <p><?= e($enrollment['course_description'] ?: 'Curso matriculado na TME.') ?></p>
            <div class="progress-track large">
                <span style="width: <?= e((float) $enrollment['progress_percent']) ?>%;"></span>
            </div>
            <div class="course-meta spacious">
                <span><?= e(number_format((float) $enrollment['progress_percent'], 0)) ?>% concluído</span>
                <span>Início <?= e(date('d/m/Y', strtotime($enrollment['enrolled_at']))) ?></span>
                <span>Última atividade <?= e($enrollment['last_activity_at'] ? date('d/m/Y H:i', strtotime($enrollment['last_activity_at'])) : 'sem registro') ?></span>
                <?php if (! empty($enrollment['completed_at'])): ?>
                    <span>Concluído em <?= e(date('d/m/Y', strtotime($enrollment['completed_at']))) ?></span>
                <?php endif; ?>
            </div>
            <?php if (! empty($certificate)): ?>
                <div class="actions-row">
                    <a class="button large" href="<?= e(url('/certificados/ver/' . $certificate['code'])) ?>">Ver certificado</a>
                    <a class="button ghost large" href="<?= e(url('/certificados/validar/' . $certificate['code'])) ?>">Validar código</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="section-toolbar">
        <span class="eyebrow">Aulas</span>
        <h2>Progresso por aula</h2>
    </div>

    <div class="curriculum-list student-view">
        <?php foreach ($structure['modules'] as $module): ?>
            <article class="curriculum-module">
                <header>
                    <div>
                        <strong><?= e($module['position']) ?>. <?= e($module['title']) ?></strong>
                        <p><?= e($module['description'] ?: 'Módulo do curso.') ?></p>
                    </div>
                </header>
                <?php foreach ($module['lessons'] as $lesson): ?>
                    <?php require BASE_PATH . '/app/views/courses/partials/enrollment_lesson.php'; ?>
                <?php endforeach; ?>
            </article>
        <?php endforeach; ?>

        <?php if (! empty($structure['unassigned_lessons'])): ?>
            <article class="curriculum-module">
                <header>
                    <div>
                        <strong>Aulas do curso</strong>
                        <p>Aulas publicadas diretamente no curso.</p>
                    </div>
                </header>
                <?php foreach ($structure['unassigned_lessons'] as $lesson): ?>
                    <?php require BASE_PATH . '/app/views/courses/partials/enrollment_lesson.php'; ?>
                <?php endforeach; ?>
            </article>
        <?php endif; ?>

        <?php if (empty($structure['modules']) && empty($structure['unassigned_lessons'])): ?>
            <div class="empty-state">
                <h2>Sem aulas publicadas</h2>
                <p>O conteúdo deste curso ainda será liberado pela equipe.</p>
            </div>
        <?php endif; ?>
    </div>
</section>
