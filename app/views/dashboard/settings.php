<?php defined('BASE_PATH') || exit('Acesso direto não permitido.'); ?>

<section class="dashboard-shell">
    <div class="dashboard-heading">
        <span class="eyebrow">Configurações</span>
        <h1>Tema e identidade visual</h1>
        <p>Ajuste o modo claro/escuro e a cor principal da sua experiencia na TME.</p>
    </div>

    <form class="admin-form form" action="<?= e(url('/settings')) ?>" method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="redirect_to" value="/configuracoes">

        <label>
            Tema
            <select name="theme">
                <option value="light" <?= $settings['theme'] === 'light' ? 'selected' : '' ?>>Claro</option>
                <option value="dark" <?= $settings['theme'] === 'dark' ? 'selected' : '' ?>>Escuro</option>
            </select>
        </label>

        <label>
            Cor principal
            <input type="color" name="primary_color" value="<?= e($settings['primary_color']) ?>">
        </label>

        <div class="actions-row">
            <button class="button large" type="submit">Salvar preferências</button>
            <a class="button ghost large" href="<?= e(url('/portal')) ?>">Voltar ao portal</a>
        </div>
    </form>
</section>
