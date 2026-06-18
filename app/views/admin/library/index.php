<?php defined('BASE_PATH') || exit('Acesso direto não permitido.'); ?>

<section class="dashboard-shell">
    <div class="admin-toolbar">
        <div class="dashboard-heading">
            <span class="eyebrow">Administração</span>
            <h1>Biblioteca</h1>
            <p>Gerencie materiais públicos, restritos, pendentes e arquivados.</p>
        </div>
        <a class="button large" href="<?= e(url('/admin/biblioteca/novo')) ?>">Novo material</a>
    </div>

    <form class="filter-form library-filter-form" action="<?= e(url('/admin/biblioteca')) ?>" method="get">
        <label>
            Busca
            <input type="search" name="q" value="<?= e($filters['q'] ?? '') ?>">
        </label>
        <label>
            Status
            <select name="status">
                <option value="">Todos</option>
                <?php foreach (['rascunho', 'pendente', 'publicado', 'arquivado', 'recusado'] as $status): ?>
                    <option value="<?= e($status) ?>" <?= ($filters['status'] ?? '') === $status ? 'selected' : '' ?>><?= e(human_label($status)) ?></option>
                <?php endforeach; ?>
            </select>
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
            Tipo
            <select name="type">
                <option value="">Todos</option>
                <?php foreach (['pdf', 'livro', 'apostila', 'artigo', 'video', 'link', 'apresentacao', 'imagem', 'arquivo'] as $type): ?>
                    <option value="<?= e($type) ?>" <?= ($filters['type'] ?? '') === $type ? 'selected' : '' ?>><?= e(human_label($type)) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <button class="button" type="submit">Filtrar</button>
        <a class="button ghost" href="<?= e(url('/admin/biblioteca')) ?>">Limpar</a>
    </form>

    <?php if (empty($items)): ?>
        <div class="empty-state">
            <h2>Nenhum item encontrado</h2>
            <p>Cadastre ou aprove materiais enviados para publicar na biblioteca.</p>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Material</th>
                        <th>Tipo</th>
                        <th>Visibilidade</th>
                        <th>Autor</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td>
                                <strong><?= e($item['title']) ?></strong>
                                <span><?= e($item['category'] ?: '-') ?> / <?= e($item['subject'] ?: '-') ?></span>
                                <?php if (! empty($item['course_title'])): ?><span>Curso: <?= e($item['course_title']) ?></span><?php endif; ?>
                            </td>
                            <td><?= e($item['item_type']) ?></td>
                            <td><?= e($item['visibility']) ?></td>
                            <td><?= e($item['author'] ?: $item['owner_name'] ?: '-') ?></td>
                            <td><span class="status-badge <?= e($item['status']) ?>"><?= e(human_label($item['status'])) ?></span></td>
                            <td class="actions-cell">
                                <a class="button small" href="<?= e(url('/biblioteca/' . $item['id'])) ?>">Ver</a>
                                <a class="button ghost small" href="<?= e(url('/admin/biblioteca/' . $item['id'] . '/editar')) ?>">Editar</a>
                                <?php if (in_array(current_user()['role_slug'], ['administrador', 'supervisor'], true) && $item['status'] === 'pendente'): ?>
                                    <form action="<?= e(url('/admin/biblioteca/' . $item['id'] . '/aprovar')) ?>" method="post">
                                        <?= csrf_field() ?>
                                        <button class="button small" type="submit">Aprovar</button>
                                    </form>
                                    <form action="<?= e(url('/admin/biblioteca/' . $item['id'] . '/recusar')) ?>" method="post">
                                        <?= csrf_field() ?>
                                        <input type="text" name="moderation_notes" placeholder="Motivo opcional">
                                        <button class="button ghost small" type="submit">Recusar</button>
                                    </form>
                                <?php endif; ?>
                                <?php if ($item['status'] !== 'arquivado'): ?>
                                    <form action="<?= e(url('/admin/biblioteca/' . $item['id'] . '/arquivar')) ?>" method="post" data-confirm="Arquivar este material?">
                                        <?= csrf_field() ?>
                                        <button class="button ghost small" type="submit">Arquivar</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
