<?php
defined('BASE_PATH') || exit('Acesso direto não permitido.');

$value = static fn (string $key, mixed $default = ''): mixed => old($key, $default);
?>

<section class="dashboard-shell">
    <div class="dashboard-heading">
        <span class="eyebrow">Biblioteca</span>
        <h1>Enviar material</h1>
        <p>Materiais enviados por alunos e professores entram como pendentes para moderação.</p>
    </div>

    <?php require BASE_PATH . '/app/views/library/partials/form_fields.php'; ?>
</section>
