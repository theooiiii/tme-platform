<?php defined('BASE_PATH') || exit('Acesso direto não permitido.'); ?>

<section class="dashboard-shell">
    <div class="dashboard-heading">
        <span class="eyebrow">Administração</span>
        <h1>Certificados emitidos</h1>
        <p>Acompanhe certificados gerados automaticamente, valide codigos e revogue registros quando necessario.</p>
    </div>

    <form class="filter-form certificate-filter-form" action="<?= e(url('/admin/certificados')) ?>" method="get">
        <label>
            Busca
            <input type="search" name="q" value="<?= e($filters['q'] ?? '') ?>" placeholder="Código, título ou aluno">
        </label>
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
            <select name="user_id">
                <option value="">Todos</option>
                <?php foreach ($students as $student): ?>
                    <option value="<?= e($student['id']) ?>" <?= (string) ($filters['user_id'] ?? '') === (string) $student['id'] ? 'selected' : '' ?>><?= e($student['full_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>
            Status
            <select name="status">
                <option value="">Todos</option>
                <option value="valido" <?= ($filters['status'] ?? '') === 'valido' ? 'selected' : '' ?>>Valido</option>
                <option value="revogado" <?= ($filters['status'] ?? '') === 'revogado' ? 'selected' : '' ?>>Revogado</option>
            </select>
        </label>
        <button class="button" type="submit">Filtrar</button>
        <a class="button ghost" href="<?= e(url('/admin/certificados')) ?>">Limpar</a>
    </form>

    <?php if (empty($certificates)): ?>
        <div class="empty-state">
            <h2>Nenhum certificado encontrado</h2>
            <p>Certificados aparecem aqui quando alunos concluem cursos com 100% de progresso.</p>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Aluno</th>
                        <th>Curso</th>
                        <th>Código</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($certificates as $certificate): ?>
                        <tr>
                            <td>
                                <strong><?= e($certificate['student_name']) ?></strong>
                                <span><?= e($certificate['student_email']) ?></span>
                            </td>
                            <td>
                                <strong><?= e($certificate['course_title'] ?: $certificate['title']) ?></strong>
                                <span><?= e((int) $certificate['workload_hours']) ?>h | <?= e(date('d/m/Y', strtotime($certificate['issued_at']))) ?></span>
                            </td>
                            <td>
                                <strong><?= e($certificate['code']) ?></strong>
                                <span><?= e($certificate['certificate_type']) ?></span>
                            </td>
                            <td>
                                <span class="status-badge <?= e($certificate['validation_status']) ?>"><?= e(human_label($certificate['validation_status'])) ?></span>
                                <?php if ($certificate['validation_status'] === 'revogado'): ?>
                                    <span><?= e($certificate['revocation_reason'] ?: 'sem motivo') ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="actions-cell">
                                <a class="button small" href="<?= e(url('/certificados/ver/' . $certificate['code'])) ?>">Visualizar</a>
                                <a class="button ghost small" href="<?= e(url('/certificados/validar/' . $certificate['code'])) ?>">Validar</a>
                                <?php if ($certificate['validation_status'] === 'valido'): ?>
                                    <form action="<?= e(url('/admin/certificados/' . $certificate['id'] . '/revogar')) ?>" method="post" data-confirm="Revogar este certificado?">
                                        <?= csrf_field() ?>
                                        <input type="text" name="revocation_reason" placeholder="Motivo da revogação" required>
                                        <button class="button ghost small" type="submit">Revogar</button>
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
