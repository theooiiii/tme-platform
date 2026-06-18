<?php defined('BASE_PATH') || exit('Acesso direto não permitido.'); ?>

<section class="dashboard-shell">
    <div class="admin-toolbar">
        <div class="dashboard-heading">
            <span class="eyebrow">Aprendizagem</span>
            <h1>Minhas atividades</h1>
            <p>Acompanhe tarefas dos cursos em que você está matriculado, prazos, entregas e correcoes.</p>
        </div>
        <div class="actions-row">
            <a class="button ghost large" href="<?= e(url('/boletim')) ?>">Boletim</a>
            <?php if ($user['role_slug'] === 'professor'): ?>
                <a class="button large" href="<?= e(url('/admin/atividades')) ?>">Gerenciar atividades</a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (empty($activities)): ?>
        <div class="empty-state">
            <h2>Nenhuma atividade disponível</h2>
            <p>As atividades aparecerão aqui quando seus cursos publicarem tarefas.</p>
        </div>
    <?php else: ?>
        <div class="course-card-grid">
            <?php foreach ($activities as $activity): ?>
                <?php
                $status = $activity['submission_status'] ?: 'pendente';
                $late = ! empty($activity['due_at']) && strtotime($activity['due_at']) < time() && ! $activity['submission_id'];
                ?>
                <article class="course-card">
                    <div>
                        <span class="status-badge <?= e($status) ?>"><?= e(human_label($late ? 'pendente atrasada' : $status)) ?></span>
                        <h2><?= e($activity['title']) ?></h2>
                        <p><?= e($activity['description'] ?: 'Atividade vinculada ao curso.') ?></p>
                        <div class="course-meta">
                            <span><?= e($activity['course_title'] ?? 'Curso') ?></span>
                            <span><?= e($activity['activity_type']) ?></span>
                            <span><?= e(number_format((float) $activity['max_score'], 2, ',', '.')) ?> pts</span>
                            <span>Prazo <?= e($activity['due_at'] ? date('d/m/Y H:i', strtotime($activity['due_at'])) : 'livre') ?></span>
                        </div>
                        <?php if ($activity['grade_score'] !== null): ?>
                            <p><strong>Nota:</strong> <?= e(number_format((float) $activity['grade_score'], 2, ',', '.')) ?></p>
                        <?php endif; ?>
                        <a class="button" href="<?= e(url('/atividades/' . $activity['id'])) ?>">Abrir atividade</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
