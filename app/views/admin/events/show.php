<?php defined('BASE_PATH') || exit('Acesso direto nao permitido.'); ?>

<section class="dashboard-shell">
    <div class="admin-toolbar">
        <div class="dashboard-heading">
            <span class="eyebrow">Evento</span>
            <h1><?= e($event['title']) ?></h1>
            <p><?= e($event['description'] ?: 'Gestao de inscritos e certificados.') ?></p>
        </div>
        <a class="button ghost large" href="<?= e(url('/admin/eventos')) ?>">Voltar</a>
    </div>

    <div class="metric-grid">
        <article class="metric"><span>Status</span><strong><?= e($event['status']) ?></strong></article>
        <article class="metric"><span>Inscritos</span><strong><?= e((int) $event['registrations_count']) ?></strong></article>
        <article class="metric"><span>Carga</span><strong><?= e((int) $event['workload_hours']) ?>h</strong></article>
        <article class="metric"><span>Certificado</span><strong><?= (bool) $event['certificate_enabled'] ? 'Sim' : 'Nao' ?></strong></article>
    </div>

    <form class="filter-form ranking-filter-form" action="<?= e(url('/admin/eventos/' . $event['id'] . '/status')) ?>" method="post">
        <?= csrf_field() ?>
        <label>
            Status do evento
            <select name="status">
                <?php foreach (['rascunho', 'publicado', 'encerrado'] as $status): ?>
                    <option value="<?= e($status) ?>" <?= $event['status'] === $status ? 'selected' : '' ?>><?= e($status) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <button class="button" type="submit">Atualizar status</button>
    </form>

    <div class="section-toolbar">
        <span class="eyebrow">Inscricoes</span>
        <h2>Participantes</h2>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Usuario</th><th>Status</th><th>Certificado</th><th>Acoes</th></tr></thead>
            <tbody>
                <?php foreach ($registrations as $registration): ?>
                    <tr>
                        <td><strong><?= e($registration['full_name']) ?></strong><span><?= e($registration['email']) ?></span></td>
                        <td><span class="status-badge <?= e($registration['status']) ?>"><?= e($registration['status']) ?></span></td>
                        <td><span><?= e($registration['certificate_code'] ?: 'nao emitido') ?></span></td>
                        <td class="actions-cell">
                            <form action="<?= e(url('/admin/eventos/inscricoes/' . $registration['id'] . '/presenca')) ?>" method="post">
                                <?= csrf_field() ?>
                                <button class="button small" type="submit">Confirmar presenca</button>
                            </form>
                            <form action="<?= e(url('/admin/eventos/inscricoes/' . $registration['id'] . '/certificado')) ?>" method="post">
                                <?= csrf_field() ?>
                                <button class="button ghost small" type="submit">Gerar certificado</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
