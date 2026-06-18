<?php defined('BASE_PATH') || exit('Acesso direto não permitido.'); ?>

<section class="dashboard-shell exams-shell">
    <div class="admin-toolbar">
        <div class="dashboard-heading">
            <span class="eyebrow">Avaliações</span>
            <h1>Provas e simulados</h1>
            <p>Acesse provas publicadas, acompanhe tentativas e veja seu desempenho por disciplina.</p>
        </div>
        <a class="button ghost large" href="<?= e(url('/portal')) ?>">Portal</a>
    </div>

    <div class="metric-grid">
        <article class="metric"><span>Disponiveis</span><strong><?= e(count($availableExams)) ?></strong></article>
        <article class="metric"><span>Tentativas</span><strong><?= e(count($attempts)) ?></strong></article>
        <article class="metric"><span>Disciplinas</span><strong><?= e(count($performance)) ?></strong></article>
    </div>

    <div class="section-toolbar">
        <span class="eyebrow">Para fazer</span>
        <h2>Provas disponiveis</h2>
    </div>

    <?php if (empty($availableExams)): ?>
        <div class="empty-state">
            <h2>Nenhuma prova publicada</h2>
            <p>As avaliações aparecerão aqui quando forem liberadas para seus cursos ou turmas.</p>
        </div>
    <?php else: ?>
        <div class="course-card-grid">
            <?php foreach ($availableExams as $exam): ?>
                <article class="course-card">
                    <div>
                        <span class="status-badge <?= e($exam['status']) ?>"><?= e(human_label($exam['status'])) ?></span>
                        <h2><?= e($exam['title']) ?></h2>
                        <p><?= e($exam['description'] ?: 'Prova vinculada a sua jornada acadêmica.') ?></p>
                        <div class="course-meta">
                            <span><?= e($exam['course_title'] ?: 'Geral') ?></span>
                            <span><?= e($exam['class_name'] ?: 'Sem turma') ?></span>
                            <span><?= e((int) $exam['questions_count']) ?> questões</span>
                            <span><?= e((int) $exam['attempts_used']) ?>/<?= e((int) $exam['attempts_allowed']) ?> tentativas</span>
                        </div>
                        <a class="button" href="<?= e(url('/provas/' . $exam['id'])) ?>">Abrir prova</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="admin-detail-grid">
        <section class="detail-card">
            <span class="eyebrow">Histórico</span>
            <h2>Minhas tentativas</h2>
            <?php if (empty($attempts)): ?>
                <p class="muted">Nenhuma tentativa iniciada.</p>
            <?php else: ?>
                <div class="table-wrap compact-table">
                    <table>
                        <thead><tr><th>Prova</th><th>Status</th><th>Nota</th><th>Ação</th></tr></thead>
                        <tbody>
                            <?php foreach ($attempts as $attempt): ?>
                                <tr>
                                    <td><strong><?= e($attempt['title']) ?></strong><span><?= e($attempt['subject_name'] ?: 'Geral') ?></span></td>
                                    <td><span class="status-badge <?= e($attempt['status']) ?>"><?= e(human_label($attempt['status'])) ?></span></td>
                                    <td><?= e(number_format((float) $attempt['total_score'], 2, ',', '.')) ?></td>
                                    <td><a href="<?= e(url('/provas/tentativas/' . $attempt['id'] . '/resultado')) ?>">Resultado</a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>

        <section class="detail-card">
            <span class="eyebrow">Desempenho</span>
            <h2>Por disciplina</h2>
            <?php if (empty($performance)): ?>
                <p class="muted">Sem resultados suficientes para calcular desempenho.</p>
            <?php else: ?>
                <div class="ranking-list">
                    <?php foreach ($performance as $row): ?>
                        <p><strong><?= e($row['subject_name']) ?></strong> - media <?= e(number_format((float) $row['average_score'], 2, ',', '.')) ?> em <?= e((int) $row['attempts_count']) ?> tentativa(s)</p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
</section>
