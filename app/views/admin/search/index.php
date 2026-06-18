<?php defined('BASE_PATH') || exit('Acesso direto não permitido.'); ?>

<?php
$labels = [
    'users' => ['Usuários', '/admin/usuarios?q='],
    'courses' => ['Cursos', '/admin/cursos'],
    'library' => ['Biblioteca', '/admin/biblioteca'],
    'events' => ['Eventos', '/admin/eventos'],
    'certificates' => ['Certificados', '/admin/certificados'],
];
?>

<section class="page-section">
    <div class="page-header">
        <div>
            <span class="eyebrow">Administração</span>
            <h1>Busca global</h1>
            <p>Encontre rapidamente usuários, cursos, biblioteca, eventos e certificados.</p>
        </div>
    </div>

    <form class="filter-bar prominent" method="get">
        <input type="search" name="q" value="<?= e($term) ?>" placeholder="Buscar na TME">
        <button class="button" type="submit">Buscar</button>
    </form>

    <?php if ($term === ''): ?>
        <div class="empty-state">
            <h2>Digite um termo para buscar</h2>
            <p>Use nome, e-mail, título, categoria, código de certificado ou palavra-chave.</p>
        </div>
    <?php else: ?>
        <div class="module-grid">
            <?php foreach ($results as $group => $items): ?>
                <article class="module-card search-result-card">
                    <h2><?= e($labels[$group][0] ?? ucfirst($group)) ?></h2>
                    <?php if (empty($items)): ?>
                        <p>Nenhum resultado encontrado.</p>
                    <?php else: ?>
                        <?php foreach ($items as $item): ?>
                            <a class="search-result-item" href="<?= e(url($group === 'users' ? $labels[$group][1] . urlencode($term) : $labels[$group][1])) ?>">
                                <strong><?= e($item['title']) ?></strong>
                                <span><?= e(($item['subtitle'] ?? '-') . ' · ' . human_label($item['status'] ?? '')) ?></span>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
