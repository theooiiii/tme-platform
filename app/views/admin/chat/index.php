<?php defined('BASE_PATH') || exit('Acesso direto nao permitido.'); ?>

<section class="dashboard-shell chat-shell">
    <div class="admin-toolbar">
        <div class="dashboard-heading">
            <span class="eyebrow">Seguranca e moderacao</span>
            <h1>Auditoria de chat</h1>
            <p>Visualizacao restrita para seguranca, moderacao e apuracao de incidentes.</p>
        </div>
        <a class="button ghost large" href="<?= e(url('/chat')) ?>">Meu chat</a>
    </div>

    <div class="chat-layout">
        <aside class="chat-sidebar">
            <div class="conversation-list">
                <?php if (empty($conversations)): ?>
                    <p class="muted">Nenhuma conversa criada.</p>
                <?php else: ?>
                    <?php foreach ($conversations as $conversation): ?>
                        <a class="conversation-item <?= $channel && (int) $channel['id'] === (int) $conversation['id'] ? 'active' : '' ?>" href="<?= e(url('/admin/chat?canal=' . $conversation['id'])) ?>">
                            <strong><?= e($conversation['name']) ?></strong>
                            <span><?= e((int) $conversation['members_count']) ?> membros | <?= e((int) $conversation['messages_count']) ?> mensagens</span>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </aside>

        <section class="chat-thread">
            <?php if (! $channel): ?>
                <div class="empty-state">
                    <h2>Selecione uma conversa</h2>
                    <p>O acesso de auditoria fica registrado nos logs do sistema.</p>
                </div>
            <?php else: ?>
                <div class="chat-thread-header">
                    <div>
                        <span class="eyebrow"><?= e($channel['channel_type']) ?></span>
                        <h2><?= e($channel['name']) ?></h2>
                    </div>
                    <?php if ($channel['class_name']): ?><span class="badge-pill"><?= e($channel['class_name']) ?></span><?php endif; ?>
                </div>

                <div class="message-list">
                    <?php if (empty($messages)): ?>
                        <p class="muted">Sem mensagens para auditar.</p>
                    <?php else: ?>
                        <?php foreach ($messages as $message): ?>
                            <article class="message-bubble audit">
                                <div>
                                    <strong><?= e($message['sender_name']) ?></strong>
                                    <span><?= e(role_label($message['sender_role'])) ?> | <?= e(date('d/m/Y H:i', strtotime($message['created_at']))) ?></span>
                                </div>
                                <p><?= nl2br(e($message['message'])) ?></p>
                            </article>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
</section>
