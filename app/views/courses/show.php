<?php defined('BASE_PATH') || exit('Acesso direto nao permitido.'); ?>

<section class="dashboard-shell">
    <div class="detail-grid">
        <?php if (! empty($course['image_path'])): ?>
            <img class="course-cover" src="<?= e(url('/' . $course['image_path'])) ?>" alt="Imagem do curso <?= e($course['title']) ?>">
        <?php else: ?>
            <div class="course-cover placeholder">TME</div>
        <?php endif; ?>

        <div class="dashboard-heading">
            <span class="eyebrow"><?= e($course['category']) ?></span>
            <h1><?= e($course['title']) ?></h1>
            <p><?= e($course['description'] ?: 'Curso publicado na TME.') ?></p>
            <div class="course-meta spacious">
                <span><?= e($course['level']) ?></span>
                <span><?= e((int) $course['workload_hours']) ?>h</span>
                <span>R$ <?= e(number_format((float) $course['price'], 2, ',', '.')) ?></span>
                <span><?= e($course['teacher_name'] ?? 'Equipe TME') ?></span>
            </div>

            <?php if ($enrollment): ?>
                <div class="enrollment-callout">
                    <strong>Matrícula <?= e($enrollment['status']) ?></strong>
                    <div class="progress-track" aria-label="Progresso do curso">
                        <span style="width: <?= e((float) $enrollment['progress_percent']) ?>%;"></span>
                    </div>
                    <p><?= e(number_format((float) $enrollment['progress_percent'], 0)) ?>% concluído</p>
                    <a class="button" href="<?= e(url('/aluno/meus-cursos/' . $enrollment['id'])) ?>">Continuar curso</a>
                </div>
            <?php else: ?>
                <form action="<?= e(url('/aluno/cursos/' . $course['id'] . '/matricular')) ?>" method="post" class="actions-row">
                    <?= csrf_field() ?>
                    <button class="button large" type="submit">Matricular-se</button>
                    <a class="button ghost large" href="<?= e(url('/aluno/cursos')) ?>">Voltar ao catálogo</a>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <div class="section-toolbar">
        <span class="eyebrow">Conteúdo</span>
        <h2>Módulos e aulas</h2>
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
                    <div class="lesson-row">
                        <div>
                            <strong><?= e($lesson['position']) ?>. <?= e($lesson['title']) ?></strong>
                            <span><?= e($lesson['lesson_type']) ?> • <?= e((int) $lesson['duration_minutes']) ?> min</span>
                            <?php if (! empty($lesson['description'])): ?>
                                <p><?= e($lesson['description']) ?></p>
                            <?php endif; ?>
                            <?php if (! empty($lesson['materials'])): ?>
                                <div class="material-list">
                                    <?php foreach ($lesson['materials'] as $material): ?>
                                        <?php if ($material['status'] !== 'ativo'): continue; endif; ?>
                                        <span><?= e($material['material_type']) ?>: <?= e($material['title']) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
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
                    <div class="lesson-row">
                        <div>
                            <strong><?= e($lesson['position']) ?>. <?= e($lesson['title']) ?></strong>
                            <span><?= e($lesson['lesson_type']) ?> • <?= e((int) $lesson['duration_minutes']) ?> min</span>
                            <?php if (! empty($lesson['description'])): ?>
                                <p><?= e($lesson['description']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </article>
        <?php endif; ?>

        <?php if (empty($structure['modules']) && empty($structure['unassigned_lessons'])): ?>
            <div class="empty-state">
                <h2>Currículo em montagem</h2>
                <p>As aulas deste curso ainda serão organizadas em módulos.</p>
            </div>
        <?php endif; ?>
    </div>
</section>
