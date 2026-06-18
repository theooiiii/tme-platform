<?php defined('BASE_PATH') || exit('Acesso direto não permitido.'); ?>

<section class="<?= $isPublicPage ? 'page-section compact' : 'dashboard-shell' ?>">
    <div class="admin-toolbar">
        <div class="<?= $isPublicPage ? 'section-heading' : 'dashboard-heading' ?>">
            <span class="eyebrow">Biblioteca</span>
            <h1>Biblioteca digital</h1>
            <p>Materiais educacionais publicados: PDFs, livros, apostilas, artigos, vídeos, links e arquivos.</p>
        </div>
        <?php if (current_user()): ?>
            <div class="actions-row">
                <a class="button ghost large" href="<?= e(url('/biblioteca/favoritos')) ?>">Meus favoritos</a>
                <?php if (in_array(current_user()['role_slug'], ['aluno', 'professor'], true)): ?>
                    <a class="button large" href="<?= e(url('/biblioteca/enviar')) ?>">Enviar material</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <form class="filter-form library-filter-form" action="<?= e(url('/biblioteca')) ?>" method="get">
        <label>
            Busca
            <input type="search" name="q" value="<?= e($filters['q'] ?? '') ?>" placeholder="Título, descrição ou autor">
        </label>
        <label>
            Categoria
            <select name="category">
                <option value="">Todas</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= e($category) ?>" <?= ($filters['category'] ?? '') === $category ? 'selected' : '' ?>><?= e($category) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>
            Disciplina
            <select name="subject">
                <option value="">Todas</option>
                <?php foreach ($subjects as $subject): ?>
                    <option value="<?= e($subject) ?>" <?= ($filters['subject'] ?? '') === $subject ? 'selected' : '' ?>><?= e($subject) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>
            Tipo
            <select name="type">
                <option value="">Todos</option>
                <?php foreach (['pdf', 'livro', 'apostila', 'artigo', 'video', 'link', 'apresentacao', 'imagem', 'arquivo'] as $type): ?>
                    <option value="<?= e($type) ?>" <?= ($filters['type'] ?? '') === $type ? 'selected' : '' ?>><?= e(human_label($type)) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <button class="button" type="submit">Buscar</button>
        <a class="button ghost" href="<?= e(url('/biblioteca')) ?>">Limpar</a>
    </form>

    <?php if (empty($items)): ?>
        <div class="empty-state">
            <h2>Nenhum material encontrado</h2>
            <p>Novos itens aparecerão aqui quando forem publicados.</p>
        </div>
    <?php else: ?>
        <div class="course-card-grid">
            <?php foreach ($items as $item): ?>
                <article class="course-card library-card">
                    <?php if (! empty($item['cover_path'])): ?>
                        <img src="<?= e(url('/' . $item['cover_path'])) ?>" alt="Capa de <?= e($item['title']) ?>">
                    <?php else: ?>
                        <div class="course-card-placeholder"><?= e(strtoupper(substr($item['item_type'], 0, 3))) ?></div>
                    <?php endif; ?>
                    <div>
                        <span class="eyebrow"><?= e($item['category'] ?: $item['item_type']) ?></span>
                        <h2><?= e($item['title']) ?></h2>
                        <p><?= e($item['description'] ?: 'Material da biblioteca TME.') ?></p>
                        <div class="course-meta">
                            <span><?= e(human_label($item['item_type'])) ?></span>
                            <span><?= e($item['subject'] ?: 'Geral') ?></span>
                            <span><?= e(human_label($item['visibility'])) ?></span>
                        </div>
                        <a class="button" href="<?= e(url('/biblioteca/' . $item['id'])) ?>">Abrir</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
