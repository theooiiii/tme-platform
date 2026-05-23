<?php defined('BASE_PATH') || exit('Acesso direto nao permitido.'); ?>

<section class="dashboard-shell">
    <div class="admin-toolbar">
        <div class="dashboard-heading">
            <span class="eyebrow">Aluno</span>
            <h1>Meus cursos</h1>
            <p>Acompanhe suas matrículas, progresso e última atividade.</p>
        </div>
        <a class="button ghost large" href="<?= e(url('/aluno/cursos')) ?>">Ver catálogo</a>
    </div>

    <?php if (empty($enrollments)): ?>
        <div class="empty-state">
            <h2>Nenhuma matrícula ainda</h2>
            <p>Escolha um curso publicado no catálogo e clique em Matricular-se.</p>
        </div>
    <?php else: ?>
        <div class="course-card-grid">
            <?php foreach ($enrollments as $enrollment): ?>
                <article class="course-card">
                    <?php if (! empty($enrollment['image_path'])): ?>
                        <img src="<?= e(url('/' . $enrollment['image_path'])) ?>" alt="Imagem do curso <?= e($enrollment['title']) ?>">
                    <?php else: ?>
                        <div class="course-card-placeholder">TME</div>
                    <?php endif; ?>
                    <div>
                        <span class="status-badge <?= e($enrollment['status']) ?>"><?= e($enrollment['status']) ?></span>
                        <h2><?= e($enrollment['title']) ?></h2>
                        <p><?= e($enrollment['description'] ?: 'Curso matriculado na TME.') ?></p>
                        <div class="progress-track">
                            <span style="width: <?= e((float) $enrollment['progress_percent']) ?>%;"></span>
                        </div>
                        <div class="course-meta">
                            <span><?= e(number_format((float) $enrollment['progress_percent'], 0)) ?>%</span>
                            <span><?= e((int) $enrollment['lessons_count']) ?> aulas</span>
                            <span>Início <?= e(date('d/m/Y', strtotime($enrollment['enrolled_at']))) ?></span>
                        </div>
                        <a class="button" href="<?= e(url('/aluno/meus-cursos/' . $enrollment['id'])) ?>">Abrir curso</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
