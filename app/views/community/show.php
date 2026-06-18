<?php
defined('BASE_PATH') || exit('Acesso direto não permitido.');

$typeLabels = [
    'duvida' => 'Duvida',
    'artigo' => 'Artigo',
    'projeto' => 'Projeto',
    'material' => 'Material',
    'conquista' => 'Conquista',
    'aviso' => 'Aviso',
];
?>

<section class="dashboard-shell community-shell">
    <article class="post-card post-detail">
        <div class="post-card-header">
            <span class="status-badge"><?= e($typeLabels[$post['post_type']] ?? $post['post_type']) ?></span>
            <?php if ($post['is_featured']): ?><span class="badge-pill">Destaque</span><?php endif; ?>
        </div>
        <h1><?= e($post['title']) ?></h1>
        <div class="post-meta">
            <span><?= e($post['author_name']) ?></span>
            <span><?= e(role_label($post['author_role'])) ?></span>
            <span><?= e(date('d/m/Y H:i', strtotime($post['created_at']))) ?></span>
        </div>
        <div class="post-content"><?= nl2br(e($post['content'])) ?></div>
        <div class="inline-actions">
            <form action="<?= e(url('/comunidade/' . $post['id'] . '/curtir')) ?>" method="post">
                <?= csrf_field() ?>
                <button type="submit"><?= $post['is_liked'] ? 'Curtido' : 'Curtir' ?> (<?= e((int) $post['likes_count']) ?>)</button>
            </form>
            <form action="<?= e(url('/comunidade/' . $post['id'] . '/salvar')) ?>" method="post">
                <?= csrf_field() ?>
                <button type="submit"><?= $post['is_saved'] ? 'Salvo' : 'Salvar' ?></button>
            </form>
            <a href="<?= e(url('/comunidade')) ?>">Voltar ao feed</a>
        </div>
    </article>

    <div class="section-toolbar">
        <span class="eyebrow">Comentarios</span>
        <h2>Discussão acadêmica</h2>
    </div>

    <form class="admin-form form" action="<?= e(url('/comunidade/' . $post['id'] . '/comentar')) ?>" method="post">
        <?= csrf_field() ?>
        <label>
            Novo comentario
            <textarea name="content" rows="3" required></textarea>
        </label>
        <button class="button" type="submit">Comentar</button>
    </form>

    <div class="comment-list">
        <?php if (empty($comments)): ?>
            <div class="empty-state"><h2>Sem comentarios</h2><p>Seja a primeira pessoa a contribuir.</p></div>
        <?php else: ?>
            <?php foreach ($comments as $comment): ?>
                <article class="comment-card">
                    <strong><?= e($comment['full_name']) ?></strong>
                    <span><?= e(role_label($comment['role_slug'])) ?> | <?= e(date('d/m/Y H:i', strtotime($comment['created_at']))) ?></span>
                    <p><?= nl2br(e($comment['content'])) ?></p>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>
