<?php defined('BASE_PATH') || exit('Acesso direto nao permitido.'); ?>

<section class="dashboard-shell">
    <div class="dashboard-heading">
        <span class="eyebrow">Administração</span>
        <h1>Matrículas</h1>
        <p>Visualize matrículas por curso, aluno e status, com progresso e datas de atividade.</p>
    </div>

    <form class="filter-form" action="<?= e(url('/admin/matriculas')) ?>" method="get">
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
            Aluno
            <select name="student_id">
                <option value="">Todos</option>
                <?php foreach ($students as $student): ?>
                    <option value="<?= e($student['id']) ?>" <?= (string) ($filters['student_id'] ?? '') === (string) $student['id'] ? 'selected' : '' ?>><?= e($student['full_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>
            Status
            <select name="status">
                <option value="">Todos</option>
                <?php foreach (['ativa' => 'Ativa', 'concluida' => 'Concluída', 'cancelada' => 'Cancelada'] as $value => $label): ?>
                    <option value="<?= e($value) ?>" <?= ($filters['status'] ?? '') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <button class="button" type="submit">Filtrar</button>
        <a class="button ghost" href="<?= e(url('/admin/matriculas')) ?>">Limpar</a>
    </form>

    <?php if (empty($enrollments)): ?>
        <div class="empty-state">
            <h2>Nenhuma matrícula encontrada</h2>
            <p>As matrículas de alunos em cursos publicados aparecerão aqui.</p>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Aluno</th>
                        <th>Curso</th>
                        <th>Progresso</th>
                        <th>Status</th>
                        <th>Datas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($enrollments as $enrollment): ?>
                        <tr>
                            <td>
                                <strong><?= e($enrollment['student_name']) ?></strong>
                                <span><?= e($enrollment['student_email']) ?></span>
                            </td>
                            <td>
                                <strong><?= e($enrollment['course_title']) ?></strong>
                                <span><?= e((int) $enrollment['completed_lessons']) ?> de <?= e((int) $enrollment['lessons_count']) ?> aulas</span>
                            </td>
                            <td>
                                <div class="progress-track table-progress">
                                    <span style="width: <?= e((float) $enrollment['progress_percent']) ?>%;"></span>
                                </div>
                                <span><?= e(number_format((float) $enrollment['progress_percent'], 0)) ?>%</span>
                            </td>
                            <td><span class="status-badge <?= e($enrollment['status']) ?>"><?= e($enrollment['status']) ?></span></td>
                            <td>
                                <span>Início: <?= e(date('d/m/Y H:i', strtotime($enrollment['enrolled_at']))) ?></span>
                                <span>Última: <?= e($enrollment['last_activity_at'] ? date('d/m/Y H:i', strtotime($enrollment['last_activity_at'])) : 'sem registro') ?></span>
                                <?php if (! empty($enrollment['completed_at'])): ?>
                                    <span>Conclusão: <?= e(date('d/m/Y H:i', strtotime($enrollment['completed_at']))) ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
