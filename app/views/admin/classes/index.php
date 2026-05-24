<?php defined('BASE_PATH') || exit('Acesso direto nao permitido.'); ?>

<section class="dashboard-shell">
    <div class="admin-toolbar">
        <div class="dashboard-heading">
            <span class="eyebrow">Administracao</span>
            <h1>Turmas e disciplinas</h1>
            <p>Gerencie turmas, disciplinas e vinculos de alunos/professores.</p>
        </div>
        <div class="actions-row">
            <a class="button large" href="<?= e(url('/admin/turmas/nova')) ?>">Nova turma</a>
            <a class="button ghost large" href="<?= e(url('/admin/disciplinas/nova')) ?>">Nova disciplina</a>
        </div>
    </div>

    <div class="table-wrap">
        <table>
            <thead><tr><th>Turma</th><th>Instituicao</th><th>Status</th><th>Vinculos</th><th>Acoes</th></tr></thead>
            <tbody>
                <?php foreach ($classes as $class): ?>
                    <tr>
                        <td><strong><?= e($class['name']) ?></strong><span><?= e($class['period'] ?: 'periodo a definir') ?></span></td>
                        <td><span><?= e($class['institution_name'] ?: 'sem instituicao') ?></span></td>
                        <td><span class="status-badge <?= e($class['status']) ?>"><?= e($class['status']) ?></span></td>
                        <td><span><?= e((int) $class['students_count']) ?> alunos</span><span><?= e((int) $class['subjects_count']) ?> disciplinas</span></td>
                        <td class="actions-cell">
                            <a class="button small" href="<?= e(url('/admin/turmas/' . $class['id'])) ?>">Gerenciar</a>
                            <a class="button ghost small" href="<?= e(url('/admin/turmas/' . $class['id'] . '/editar')) ?>">Editar</a>
                            <form action="<?= e(url('/admin/turmas/' . $class['id'] . '/arquivar')) ?>" method="post" data-confirm="Arquivar esta turma?">
                                <?= csrf_field() ?>
                                <button class="button ghost small" type="submit">Arquivar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="section-toolbar">
        <span class="eyebrow">Disciplinas</span>
        <h2>Catalogo</h2>
    </div>
    <div class="module-grid">
        <?php foreach ($subjects as $subject): ?>
            <article class="module-card">
                <span class="status-badge <?= e($subject['status'] ?? 'ativa') ?>"><?= e($subject['status'] ?? 'ativa') ?></span>
                <h2><?= e($subject['name']) ?></h2>
                <p><?= e($subject['area'] ?: 'Area nao informada') ?> | <?= e((int) $subject['workload_hours']) ?>h</p>
                <div class="inline-actions">
                    <a href="<?= e(url('/admin/disciplinas/' . $subject['id'] . '/editar')) ?>">Editar</a>
                    <form action="<?= e(url('/admin/disciplinas/' . $subject['id'] . '/arquivar')) ?>" method="post" data-confirm="Arquivar esta disciplina?">
                        <?= csrf_field() ?>
                        <button type="submit">Arquivar</button>
                    </form>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>
