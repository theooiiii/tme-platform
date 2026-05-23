<?php defined('BASE_PATH') || exit('Acesso direto nao permitido.'); ?>

<section class="dashboard-shell">
    <div class="dashboard-heading">
        <span class="eyebrow">Aluno</span>
        <h1>Cursos disponíveis</h1>
        <p>Explore cursos publicados pela equipe TME e acompanhe a estrutura de módulos, aulas e materiais.</p>
    </div>

    <?php if (empty($courses)): ?>
        <div class="empty-state">
            <h2>Nenhum curso publicado</h2>
            <p>Novos cursos aparecerão aqui quando forem publicados pela administração.</p>
        </div>
    <?php else: ?>
        <div class="course-card-grid">
            <?php foreach ($courses as $course): ?>
                <article class="course-card">
                    <?php if (! empty($course['image_path'])): ?>
                        <img src="<?= e(url('/' . $course['image_path'])) ?>" alt="Imagem do curso <?= e($course['title']) ?>">
                    <?php else: ?>
                        <div class="course-card-placeholder">TME</div>
                    <?php endif; ?>
                    <div>
                        <span class="eyebrow"><?= e($course['category']) ?></span>
                        <h2><?= e($course['title']) ?></h2>
                        <p><?= e($course['description'] ?: 'Curso publicado na TME.') ?></p>
                        <div class="course-meta">
                            <span><?= e($course['level']) ?></span>
                            <span><?= e((int) $course['workload_hours']) ?>h</span>
                            <span><?= e($course['lessons_count']) ?> aulas</span>
                        </div>
                        <a class="button" href="<?= e(url('/aluno/cursos/' . $course['id'])) ?>">Ver detalhes</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
