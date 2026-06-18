<?php defined('BASE_PATH') || exit('Acesso direto não permitido.'); ?>

<section class="dashboard-shell">
    <div class="dashboard-heading">
        <span class="eyebrow">Aprovações</span>
        <h1>Contas pendentes</h1>
        <p>Revise cadastros de alunos e professores antes de liberar o acesso à plataforma.</p>
    </div>

    <?php if (empty($accounts)): ?>
        <div class="empty-state">
            <h2>Nenhuma conta pendente</h2>
            <p>A fila de aprovação está limpa.</p>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Perfil</th>
                        <th>Local</th>
                        <th>Instituição</th>
                        <th>Interesse</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($accounts as $account): ?>
                        <tr>
                            <td>
                                <strong><?= e($account['full_name']) ?></strong>
                                <span><?= e($account['email']) ?></span>
                                <span><?= e($account['phone']) ?></span>
                            </td>
                            <td><?= e($account['role_name']) ?></td>
                            <td><?= e($account['city']) ?> / <?= e($account['state']) ?></td>
                            <td><?= $account['is_independent'] ? 'Independente' : e($account['institution_name'] ?? 'Não informada') ?></td>
                            <td>
                                <strong><?= e($account['interest_area']) ?></strong>
                                <span><?= e($account['platform_goal']) ?></span>
                            </td>
                            <td class="actions-cell">
                                <form action="<?= e(url('/admin/contas/' . $account['id'] . '/aprovar')) ?>" method="post">
                                    <?= csrf_field() ?>
                                    <button class="button small" type="submit">Aprovar</button>
                                </form>
                                <form action="<?= e(url('/admin/contas/' . $account['id'] . '/recusar')) ?>" method="post">
                                    <?= csrf_field() ?>
                                    <input type="text" name="reason" placeholder="Motivo opcional">
                                    <button class="button ghost small" type="submit">Recusar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
