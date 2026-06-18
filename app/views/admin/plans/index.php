<?php defined('BASE_PATH') || exit('Acesso direto não permitido.'); ?>

<section class="dashboard-shell">
    <div class="admin-toolbar">
        <div class="dashboard-heading">
            <span class="eyebrow">Administração</span>
            <h1>Planos</h1>
            <p>Gerencie planos gratuitos e premium para assinaturas, pagamentos futuros e acesso premium.</p>
        </div>
        <a class="button large" href="<?= e(url('/admin/planos/novo')) ?>">Novo plano</a>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Plano</th>
                    <th>Preço</th>
                    <th>Duração</th>
                    <th>Tipo</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($plans as $plan): ?>
                    <tr>
                        <td>
                            <strong><?= e($plan['name']) ?></strong>
                            <span class="muted"><?= e($plan['description'] ?: 'Sem descrição') ?></span>
                        </td>
                        <td>R$ <?= e(number_format((float) $plan['price'], 2, ',', '.')) ?></td>
                        <td><?= e((int) $plan['duration_days']) ?> dias</td>
                        <td><?= (int) $plan['is_premium'] === 1 ? 'Premium' : 'Gratuito' ?></td>
                        <td><span class="status-badge <?= e($plan['status']) ?>"><?= e(human_label($plan['status'])) ?></span></td>
                        <td class="actions-cell">
                            <a class="button small ghost" href="<?= e(url('/admin/planos/' . $plan['id'] . '/editar')) ?>">Editar</a>
                            <form action="<?= e(url('/admin/planos/' . $plan['id'] . '/desativar')) ?>" method="post" data-confirm="Desativar este plano?">
                                <?= csrf_field() ?>
                                <button class="button small ghost" type="submit">Desativar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
