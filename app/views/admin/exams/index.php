<?php defined('BASE_PATH') || exit('Acesso direto não permitido.'); ?>

<section class="dashboard-shell exams-shell">
    <div class="admin-toolbar">
        <div class="dashboard-heading">
            <span class="eyebrow">Avaliação</span>
            <h1>Provas e simulados</h1>
            <p>Crie provas com questões objetivas, discursivas, tentativas e ranking opcional.</p>
        </div>
        <a class="button large" href="<?= e(url('/admin/provas/nova')) ?>">Nova prova</a>
    </div>

    <form class="filter-form" action="<?= e(url('/admin/provas')) ?>" method="get">
        <label>
            Curso
            <select name="course_id">
                <option value="">Todos</option>
                <?php foreach ($courses as $course): ?>
                    <option value="<?= e($course['id']) ?>" <?= (int) $filters['course_id'] === (int) $course['id'] ? 'selected' : '' ?>>
                        <?= e($course['title']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>
            Turma
            <select name="class_id">
                <option value="">Todas</option>
                <?php foreach ($classes as $class): ?>
                    <option value="<?= e($class['id']) ?>" <?= (int) $filters['class_id'] === (int) $class['id'] ? 'selected' : '' ?>>
                        <?= e($class['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>
            Status
            <select name="status">
                <option value="">Todos</option>
                <?php foreach (['rascunho', 'publicado', 'encerrado'] as $status): ?>
                    <option value="<?= e($status) ?>" <?= ($filters['status'] ?? '') === $status ? 'selected' : '' ?>>
                        <?= e($status) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <button class="button" type="submit">Filtrar</button>
        <a class="button ghost" href="<?= e(url('/admin/provas')) ?>">Limpar</a>
    </form>

    <?php if (empty($exams)): ?>
        <div class="empty-state">
            <h2>Nenhuma prova encontrada</h2>
            <p>Crie uma avaliação para publicar aos alunos vinculados.</p>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Prova</th>
                        <th>Vínculo</th>
                        <th>Período</th>
                        <th>Questões</th>
                        <th>Tentativas</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($exams as $exam): ?>
                        <tr>
                            <td>
                                <strong><?= e($exam['title']) ?></strong>
                                <span><?= e((int) $exam['time_limit_minutes']) ?> min | <?= e((int) $exam['attempts_allowed']) ?> tentativa(s)</span>
                            </td>
                            <td>
                                <span><?= e($exam['course_title'] ?: 'Curso livre') ?></span>
                                <span><?= e($exam['class_name'] ?: 'Sem turma') ?><?= $exam['subject_name'] ? ' | ' . e($exam['subject_name']) : '' ?></span>
                            </td>
                            <td>
                                <span><?= e($exam['starts_at'] ? date('d/m/Y H:i', strtotime($exam['starts_at'])) : 'inicio livre') ?></span>
                                <span><?= e($exam['ends_at'] ? date('d/m/Y H:i', strtotime($exam['ends_at'])) : 'sem fim') ?></span>
                            </td>
                            <td><?= e((int) $exam['questions_count']) ?></td>
                            <td><?= e((int) $exam['attempts_count']) ?></td>
                            <td><span class="status-badge <?= e($exam['status']) ?>"><?= e(human_label($exam['status'])) ?></span></td>
                            <td class="actions-cell">
                                <a class="button small" href="<?= e(url('/admin/provas/' . $exam['id'])) ?>">Gerenciar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
