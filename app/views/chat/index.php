<?php defined('BASE_PATH') || exit('Acesso direto não permitido.'); ?>

<section class="dashboard-shell chat-shell" data-chat-refresh="45000">
    <div class="admin-toolbar">
        <div class="dashboard-heading">
            <span class="eyebrow">Comunicacao interna</span>
            <h1>Chat TME</h1>
            <p>Converse com usuários aprovados e acompanhe grupos vinculados às suas turmas.</p>
        </div>
        <a class="button ghost large" href="<?= e(url('/portal')) ?>">Portal</a>
    </div>

    <div class="chat-layout">
        <aside class="chat-sidebar">
            <form class="chat-start-form" action="<?= e(url('/chat/privado')) ?>" method="post">
                <?= csrf_field() ?>
                <label>
                    Nova conversa
                    <select name="user_id" required>
                        <option value="">Selecionar usuário</option>
                        <?php foreach ($users as $availableUser): ?>
                            <option value="<?= e($availableUser['id']) ?>">
                                <?= e($availableUser['full_name']) ?> (<?= e(role_label($availableUser['role_slug'])) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <button class="button small" type="submit">Abrir</button>
            </form>

            <div class="conversation-list">
                <?php if (empty($conversations)): ?>
                    <p class="muted">Nenhuma conversa ainda.</p>
                <?php else: ?>
                    <?php foreach ($conversations as $conversation): ?>
                        <a class="conversation-item <?= $channel && (int) $channel['id'] === (int) $conversation['id'] ? 'active' : '' ?>" href="<?= e(url('/chat?canal=' . $conversation['id'])) ?>">
                            <strong><?= e($conversation['name']) ?></strong>
                            <span><?= e($conversation['last_message'] ? substr($conversation['last_message'], 0, 80) : 'Sem mensagens') ?></span>
                            <?php if ((int) $conversation['unread_count'] > 0): ?>
                                <em><?= e((int) $conversation['unread_count']) ?></em>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </aside>

        <section class="chat-thread">
            <?php if (! $channel): ?>
                <div class="empty-state">
                    <h2>Selecione uma conversa</h2>
                    <p>Use a lista lateral ou inicie uma conversa privada com outro usuário aprovado.</p>
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
                        <p class="muted">Nenhuma mensagem nesta conversa.</p>
                    <?php else: ?>
                        <?php foreach ($messages as $message): ?>
                            <article class="message-bubble <?= (int) $message['sender_id'] === (int) $user['id'] ? 'mine' : '' ?>">
                                <div>
                                    <strong><?= e($message['sender_name']) ?></strong>
                                    <span><?= e(date('d/m H:i', strtotime($message['created_at']))) ?></span>
                                </div>
                                <?php if (! empty($message['message'])): ?>
                                    <p><?= nl2br(e($message['message'])) ?></p>
                                <?php endif; ?>
                                <?php if (! empty($message['attachment_path'])): ?>
                                    <a class="message-attachment" href="<?= e(url('/' . $message['attachment_path'])) ?>" target="_blank" rel="noopener">
                                        <?= e($message['attachment_name'] ?: 'Arquivo anexado') ?>
                                    </a>
                                <?php endif; ?>
                            </article>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <form class="chat-message-form" action="<?= e(url('/chat/' . $channel['id'] . '/enviar')) ?>" method="post" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <div>
                        <textarea name="message" rows="3" placeholder="Escreva uma mensagem..."></textarea>
                        <input type="file" name="attachment" accept=".pdf,.png,.jpg,.jpeg,.webp,.txt,.zip,.docx,.pptx">
                    </div>
                    <button class="button" type="submit">Enviar</button>
                </form>
            <?php endif; ?>
        </section>
    </div>
</section>
