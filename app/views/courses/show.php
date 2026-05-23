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
                            <?php if (! empty($lesson['video_url'])): ?>
                                <a href="<?= e($lesson['video_url']) ?>" target="_blank" rel="noopener">Abrir link da aula</a>
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
                        </div>
                    </div>
                <?php endforeach; ?>
            </article>
        <?php endforeach; ?>

        <?php if (empty($structure['modules'])): ?>
            <div class="empty-state">
                <h2>Currículo em montagem</h2>
                <p>As aulas deste curso ainda serão organizadas em módulos.</p>
            </div>
        <?php endif; ?>
    </div>
</section>
