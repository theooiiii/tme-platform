<?php defined('BASE_PATH') || exit('Acesso direto nao permitido.'); ?>

<section class="dashboard-shell notifications-shell">
    <div class="admin-toolbar">
        <div class="dashboard-heading">
            <span class="eyebrow">Central</span>
            <h1>Notificacoes</h1>
            <p>Acompanhe eventos importantes de cursos, certificados, atividades, comunidade, chat, provas e badges.</p>
        </div>
        <?php if ($unreadCount > 0): ?>
            <form action="<?= e(url('/notificacoes/ler-todas')) ?>" method="post">
                <?= csrf_field() ?>
                <button class="button large" type="submit">Marcar todas como lidas</button>
            </form>
        <?php endif; ?>
    </div>

    <div class="notification-list">
        <?php if (empty($notifications)): ?>
            <div class="empty-state">
                <h2>Nenhuma notificacao</h2>
                <p>Quando algo importante acontecer, voce vera aqui.</p>
            </div>
        <?php endif; ?>

        <?php foreach ($notifications as $notification): ?>
            <article class="notification-row <?= empty($notification['read_at']) ? 'unread' : '' ?>">
                <div>
                    <span class="status-badge <?= empty($notification['read_at']) ? 'pendente' : 'ativo' ?>">
                        <?= empty($notification['read_at']) ? 'Nao lida' : 'Lida' ?>
                    </span>
                    <h2><?= e($notification['title']) ?></h2>
                    <p><?= e($notification['message']) ?></p>
                    <span class="muted"><?= e(date('d/m/Y H:i', strtotime($notification['created_at']))) ?> | <?= e($notification['notification_type']) ?></span>
                </div>
                <div class="actions-row">
                    <?php if (! empty($notification['action_url'])): ?>
                        <a class="button small" href="<?= e(url($notification['action_url'])) ?>">Abrir</a>
                    <?php endif; ?>
                    <?php if (empty($notification['read_at'])): ?>
                        <form action="<?= e(url('/notificacoes/' . $notification['id'] . '/ler')) ?>" method="post">
                            <?= csrf_field() ?>
                            <button class="button small ghost" type="submit">Marcar lida</button>
                        </form>
                    <?php else: ?>
                        <form action="<?= e(url('/notificacoes/' . $notification['id'] . '/nao-lida')) ?>" method="post">
                            <?= csrf_field() ?>
                            <button class="button small ghost" type="submit">Nao lida</button>
                        </form>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>
