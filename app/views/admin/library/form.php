<?php
defined('BASE_PATH') || exit('Acesso direto não permitido.');

$value = static fn (string $key, mixed $default = ''): mixed => old($key, $item[$key] ?? $default);
?>

<section class="dashboard-shell">
    <div class="dashboard-heading">
        <span class="eyebrow">Biblioteca admin</span>
        <h1><?= e($item ? 'Editar item' : 'Novo item') ?></h1>
        <p>Publique ou modere materiais da biblioteca digital separada dos materiais de aula.</p>
    </div>

    <?php require BASE_PATH . '/app/views/library/partials/form_fields.php'; ?>
</section>
