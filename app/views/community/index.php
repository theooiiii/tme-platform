<?php
defined('BASE_PATH') || exit('Acesso direto nao permitido.');

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
    <div class="admin-toolbar">
        <div class="dashboard-heading">
            <span class="eyebrow">Comunidade academica</span>
            <h1>Feed TME</h1>
            <p>Compartilhe duvidas, artigos, projetos, materiais, conquistas e avisos com moderacao academica.</p>
        </div>
        <a class="button ghost large" href="<?= e(url('/perfil')) ?>">Meu perfil</a>
    </div>

    <div class="community-layout">
        <form class="admin-form form community-composer" action="<?= e(url('/comunidade')) ?>" method="post">
            <?= csrf_field() ?>
            <label>
                Tipo
                <select name="post_type">
                    <?php foreach ($typeLabels as $value => $label): ?>
                        <option value="<?= e($value) ?>" <?= old('post_type', 'duvida') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                Titulo
                <input type="text" name="title" value="<?= e(old('title')) ?>" required>
            </label>
            <label>
                Conteudo
                <textarea name="content" rows="5" required><?= e(old('content')) ?></textarea>
            </label>
            <button class="button" type="submit">Publicar para moderacao</button>
        </form>

        <div class="community-feed">
            <?php if (empty($posts)): ?>
                <div class="empty-state">
                    <h2>Feed em construcao</h2>
                    <p>Posts aprovados pela moderacao aparecerao aqui.</p>
                </div>
            <?php else: ?>
                <?php foreach ($posts as $post): ?>
                    <article class="post-card <?= $post['is_featured'] ? 'featured' : '' ?>">
                        <div class="post-card-header">
                            <span class="status-badge"><?= e($typeLabels[$post['post_type']] ?? $post['post_type']) ?></span>
                            <?php if ($post['is_featured']): ?><span class="badge-pill">Destaque</span><?php endif; ?>
                        </div>
                        <h2><a href="<?= e(url('/comunidade/' . $post['id'])) ?>"><?= e($post['title']) ?></a></h2>
                        <p><?= nl2br(e(strlen($post['content']) > 260 ? substr($post['content'], 0, 260) . '...' : $post['content'])) ?></p>
                        <div class="post-meta">
                            <span><?= e($post['author_name']) ?></span>
                            <span><?= e(role_label($post['author_role'])) ?></span>
                            <span><?= e(date('d/m/Y H:i', strtotime($post['created_at']))) ?></span>
                        </div>
                        <div class="inline-actions">
                            <a href="<?= e(url('/comunidade/' . $post['id'])) ?>"><?= e((int) $post['comments_count']) ?> comentarios</a>
                            <form action="<?= e(url('/comunidade/' . $post['id'] . '/curtir')) ?>" method="post">
                                <?= csrf_field() ?>
                                <button type="submit"><?= $post['is_liked'] ? 'Curtido' : 'Curtir' ?> (<?= e((int) $post['likes_count']) ?>)</button>
                            </form>
                            <form action="<?= e(url('/comunidade/' . $post['id'] . '/salvar')) ?>" method="post">
                                <?= csrf_field() ?>
                                <button type="submit"><?= $post['is_saved'] ? 'Salvo' : 'Salvar' ?></button>
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>
