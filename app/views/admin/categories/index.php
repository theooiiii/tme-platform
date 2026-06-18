<?php defined('BASE_PATH') || exit('Acesso direto não permitido.'); ?>

<section class="page-section">
    <div class="page-header">
        <div>
            <span class="eyebrow">Cursos</span>
            <h1>Categorias</h1>
            <p>Organize as categorias usadas nos cursos sem alterar os conteúdos publicados.</p>
        </div>
        <a class="button ghost" href="<?= e(url('/admin/cursos')) ?>">Voltar para cursos</a>
    </div>

    <div class="table-card">
        <?php if (empty($categories)): ?>
            <div class="empty-state">
                <h2>Nenhuma categoria cadastrada</h2>
                <p>As categorias aparecerão aqui quando os cursos forem criados.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Categoria</th>
                            <th>Cursos</th>
                            <th>Publicados</th>
                            <th>Renomear</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $category): ?>
                            <tr>
                                <td><strong><?= e($category['category']) ?></strong></td>
                                <td><?= e((int) $category['courses_count']) ?></td>
                                <td><?= e((int) $category['published_count']) ?></td>
                                <td>
                                    <form class="inline-admin-form" method="post" action="<?= e(url('/admin/categorias/renomear')) ?>">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="current_category" value="<?= e($category['category']) ?>">
                                        <input type="text" name="new_category" value="<?= e($category['category']) ?>" required>
                                        <button class="button small" type="submit">Salvar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</section>
