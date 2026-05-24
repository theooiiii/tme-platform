<?php defined('BASE_PATH') || exit('Acesso direto nao permitido.'); ?>

<section class="dashboard-shell">
    <div class="admin-toolbar">
        <div class="dashboard-heading">
            <span class="eyebrow">Administracao</span>
            <h1>Eventos</h1>
            <p>Crie eventos, acompanhe inscritos, confirme presenca e emita certificados de participacao.</p>
        </div>
        <a class="button large" href="<?= e(url('/admin/eventos/novo')) ?>">Novo evento</a>
    </div>

    <div class="table-wrap">
        <table>
            <thead><tr><th>Evento</th><th>Data</th><th>Status</th><th>Inscritos</th><th>Acoes</th></tr></thead>
            <tbody>
                <?php foreach ($events as $event): ?>
                    <tr>
                        <td><strong><?= e($event['title']) ?></strong><span><?= e($event['event_type']) ?></span></td>
                        <td><span><?= e($event['starts_at'] ? date('d/m/Y H:i', strtotime($event['starts_at'])) : 'sem data') ?></span></td>
                        <td><span class="status-badge <?= e($event['status']) ?>"><?= e($event['status']) ?></span></td>
                        <td><span><?= e((int) $event['registrations_count']) ?> inscricoes</span></td>
                        <td><a class="button small" href="<?= e(url('/admin/eventos/' . $event['id'])) ?>">Gerenciar</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
