<?php defined('BASE_PATH') || exit('Acesso direto não permitido.'); ?>

<section class="dashboard-shell">
    <div class="detail-grid">
        <?php if (! empty($item['cover_path'])): ?>
            <img class="course-cover" src="<?= e(url('/' . $item['cover_path'])) ?>" alt="Capa de <?= e($item['title']) ?>">
        <?php else: ?>
            <div class="course-cover placeholder"><?= e(strtoupper(substr($item['item_type'], 0, 3))) ?></div>
        <?php endif; ?>

        <div class="dashboard-heading">
            <span class="eyebrow"><?= e($item['category'] ?: 'Biblioteca') ?></span>
            <h1><?= e($item['title']) ?></h1>
            <p><?= e($item['description'] ?: 'Material educacional publicado na TME.') ?></p>
            <div class="course-meta spacious">
                <span><?= e(human_label($item['item_type'])) ?></span>
                <span><?= e($item['subject'] ?: 'Geral') ?></span>
                <span><?= e($item['author'] ?: 'Autor não informado') ?></span>
                <span><?= e((int) $item['access_count']) ?> acessos</span>
            </div>

            <div class="actions-row">
                <?php if (! empty($item['external_url'])): ?>
                    <a class="button large" href="<?= e($item['external_url']) ?>" target="_blank" rel="noopener">Abrir link</a>
                <?php endif; ?>
                <?php if (! empty($item['file_path'])): ?>
                    <a class="button large" href="<?= e(url('/' . $item['file_path'])) ?>" target="_blank" rel="noopener">Abrir arquivo</a>
                <?php endif; ?>
                <?php if (current_user()): ?>
                    <form action="<?= e(url('/biblioteca/' . $item['id'] . '/favoritar')) ?>" method="post">
                        <?= csrf_field() ?>
                        <button class="button ghost large" type="submit"><?= $isFavorite ? 'Remover favorito' : 'Favoritar' ?></button>
                    </form>
                <?php endif; ?>
                <a class="button ghost large" href="<?= e(url('/biblioteca')) ?>">Voltar</a>
            </div>
        </div>
    </div>
</section>
