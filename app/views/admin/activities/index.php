<?php defined('BASE_PATH') || exit('Acesso direto não permitido.'); ?>

<section class="dashboard-shell">
    <div class="admin-toolbar">
        <div class="dashboard-heading">
            <span class="eyebrow">Gestão acadêmica</span>
            <h1>Atividades</h1>
            <p>Crie tarefas, projetos e avaliações vinculadas a cursos, módulos e aulas.</p>
        </div>
        <a class="button large" href="<?= e(url('/admin/atividades/nova')) ?>">Nova atividade</a>
    </div>

    <form class="filter-form" action="<?= e(url('/admin/atividades')) ?>" method="get">
        <label>
            Curso
            <select name="course_id">
                <option value="">Todos</option>
                <?php foreach ($courses as $course): ?>
                    <option value="<?= e($course['id']) ?>" <?= (string) ($filters['course_id'] ?? '') === (string) $course['id'] ? 'selected' : '' ?>><?= e($course['title']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>
            Status
            <select name="status">
                <option value="">Todos</option>
                <?php foreach (['rascunho' => 'Rascunho', 'publicada' => 'Publicada', 'encerrada' => 'Encerrada'] as $value => $label): ?>
                    <option value="<?= e($value) ?>" <?= ($filters['status'] ?? '') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>
            Tipo
            <select name="type">
                <option value="">Todos</option>
                <?php foreach (['texto', 'arquivo', 'quiz', 'tarefa_pratica', 'projeto'] as $type): ?>
                    <option value="<?= e($type) ?>" <?= ($filters['type'] ?? '') === $type ? 'selected' : '' ?>><?= e(human_label($type)) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <button class="button" type="submit">Filtrar</button>
        <a class="button ghost" href="<?= e(url('/admin/atividades')) ?>">Limpar</a>
    </form>

    <?php if (empty($activities)): ?>
        <div class="empty-state">
            <h2>Nenhuma atividade encontrada</h2>
            <p>Crie uma atividade para receber entregas dos alunos matriculados.</p>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Atividade</th>
                        <th>Curso</th>
                        <th>Prazo</th>
                        <th>Entregas</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($activities as $activity): ?>
                        <tr>
                            <td>
                                <strong><?= e($activity['title']) ?></strong>
                                <span><?= e($activity['activity_type']) ?> - <?= e(number_format((float) $activity['max_score'], 2, ',', '.')) ?> pts</span>
                            </td>
                            <td>
                                <?= e($activity['course_title'] ?? '-') ?>
                                <?php if (! empty($activity['lesson_title'])): ?><span>Aula: <?= e($activity['lesson_title']) ?></span><?php endif; ?>
                            </td>
                            <td><?= e($activity['due_at'] ? date('d/m/Y H:i', strtotime($activity['due_at'])) : 'Sem prazo') ?></td>
                            <td><?= e((int) $activity['submissions_count']) ?></td>
                            <td><span class="status-badge <?= e($activity['status']) ?>"><?= e(human_label($activity['status'])) ?></span></td>
                            <td class="actions-cell">
                                <a class="button small" href="<?= e(url('/admin/atividades/' . $activity['id'])) ?>">Ver</a>
                                <a class="button ghost small" href="<?= e(url('/admin/atividades/' . $activity['id'] . '/editar')) ?>">Editar</a>
                                <?php if ($activity['status'] !== 'encerrada'): ?>
                                    <form action="<?= e(url('/admin/atividades/' . $activity['id'] . '/encerrar')) ?>" method="post" data-confirm="Encerrar está atividade?">
                                        <?= csrf_field() ?>
                                        <button class="button ghost small" type="submit">Encerrar</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
