<?php defined('BASE_PATH') || exit('Acesso direto não permitido.'); ?>

<section class="dashboard-shell">
    <div class="admin-toolbar">
        <div class="dashboard-heading">
            <span class="eyebrow">Biblioteca</span>
            <h1>Meus favoritos</h1>
            <p>Materiais salvos para acesso rapido.</p>
        </div>
        <a class="button ghost large" href="<?= e(url('/biblioteca')) ?>">Voltar a biblioteca</a>
    </div>

    <?php if (empty($items)): ?>
        <div class="empty-state">
            <h2>Nenhum favorito</h2>
            <p>Abra um material publicado e marque como favorito.</p>
        </div>
    <?php else: ?>
        <div class="course-card-grid">
            <?php foreach ($items as $item): ?>
                <article class="course-card">
                    <div class="course-card-placeholder"><?= e(strtoupper(substr($item['item_type'], 0, 3))) ?></div>
                    <div>
                        <span class="eyebrow"><?= e($item['category'] ?: $item['item_type']) ?></span>
                        <h2><?= e($item['title']) ?></h2>
                        <p><?= e($item['description'] ?: 'Material favoritado.') ?></p>
                        <a class="button" href="<?= e(url('/biblioteca/' . $item['id'])) ?>">Abrir</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
