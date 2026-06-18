<?php defined('BASE_PATH') || exit('Acesso direto não permitido.'); ?>

<section class="page-section compact">
    <div class="section-heading">
        <span class="eyebrow">403</span>
        <h1>Acesso restrito</h1>
        <p>Seu perfil atual não possui permissão para acessar esta área.</p>
    </div>
    <a class="button" href="<?= e(url('/dashboard')) ?>">Ir para o Dashboard</a>
</section>
