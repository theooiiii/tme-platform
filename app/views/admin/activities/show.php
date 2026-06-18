<?php defined('BASE_PATH') || exit('Acesso direto não permitido.'); ?>

<section class="dashboard-shell">
    <div class="admin-toolbar">
        <div class="dashboard-heading">
            <span class="eyebrow">Atividade</span>
            <h1><?= e($activity['title']) ?></h1>
            <p><?= e($activity['description'] ?: 'Atividade acadêmica da TME.') ?></p>
        </div>
        <div class="actions-row">
            <a class="button ghost large" href="<?= e(url('/admin/atividades/' . $activity['id'] . '/editar')) ?>">Editar</a>
            <a class="button large" href="<?= e(url('/admin/atividades')) ?>">Voltar</a>
        </div>
    </div>

    <div class="metric-grid">
        <article class="metric"><span>Curso</span><strong><?= e($activity['course_title'] ?? '-') ?></strong></article>
        <article class="metric"><span>Tipo</span><strong><?= e($activity['activity_type']) ?></strong></article>
        <article class="metric"><span>Nota maxima</span><strong><?= e(number_format((float) $activity['max_score'], 1, ',', '.')) ?></strong></article>
        <article class="metric"><span>Entregas</span><strong><?= e(count($submissions)) ?></strong></article>
    </div>

    <?php if (! empty($activity['instructions'])): ?>
        <div class="detail-panel">
            <h2>Instrucoes</h2>
            <p><?= nl2br(e($activity['instructions'])) ?></p>
        </div>
    <?php endif; ?>

    <div class="section-toolbar">
        <span class="eyebrow">Entregas</span>
        <h2>Correções</h2>
    </div>

    <?php if (empty($submissions)): ?>
        <div class="empty-state">
            <h2>Nenhuma entrega recebida</h2>
            <p>As entregas dos alunos aparecerão aqui.</p>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Aluno</th>
                        <th>Entrega</th>
                        <th>Nota atual</th>
                        <th>Corrigir</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($submissions as $submission): ?>
                        <tr>
                            <td>
                                <strong><?= e($submission['student_name']) ?></strong>
                                <span><?= e($submission['student_email']) ?></span>
                            </td>
                            <td>
                                <span class="status-badge <?= e($submission['status']) ?>"><?= e(human_label($submission['status'])) ?></span>
                                <span><?= e(date('d/m/Y H:i', strtotime($submission['submitted_at']))) ?></span>
                                <?php if (! empty($submission['file_path'])): ?>
                                    <a href="<?= e(url('/' . $submission['file_path'])) ?>" target="_blank" rel="noopener">Arquivo enviado</a>
                                <?php endif; ?>
                                <?php if (! empty($submission['content'])): ?>
                                    <div class="lesson-content-preview"><?= nl2br(e($submission['content'])) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= $submission['score'] !== null ? e(number_format((float) $submission['score'], 2, ',', '.')) : '-' ?>
                                <?php if (! empty($submission['feedback'])): ?>
                                    <span><?= e($submission['feedback']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form class="form compact-correction-form" action="<?= e(url('/admin/atividades/entregas/' . $submission['id'] . '/corrigir')) ?>" method="post">
                                    <?= csrf_field() ?>
                                    <input type="number" name="score" min="0" max="<?= e($activity['max_score']) ?>" step="0.01" value="<?= e($submission['score'] ?? '') ?>" placeholder="Nota">
                                    <select name="status">
                                        <option value="corrigida" <?= $submission['status'] === 'corrigida' ? 'selected' : '' ?>>Corrigida</option>
                                        <option value="devolvida" <?= $submission['status'] === 'devolvida' ? 'selected' : '' ?>>Devolvida</option>
                                    </select>
                                    <textarea name="feedback" rows="3" placeholder="Feedback"><?= e($submission['feedback'] ?? '') ?></textarea>
                                    <button class="button small" type="submit">Salvar correção</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
