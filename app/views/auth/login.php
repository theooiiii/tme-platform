<?php defined('BASE_PATH') || exit('Acesso direto não permitido.'); ?>

<section class="auth-page">
    <div class="auth-panel">
        <div class="section-heading">
            <span class="eyebrow">Acesso</span>
            <h1>Entrar na TME</h1>
            <p>Contas pendentes precisam ser aprovadas por administrador ou supervisor antes do primeiro acesso.</p>
        </div>

        <form class="form" action="<?= e(url('/login')) ?>" method="post" novalidate>
            <?= csrf_field() ?>

            <label>
                E-mail
                <input type="email" name="email" autocomplete="email" required>
            </label>

            <label>
                Senha
                <input type="password" name="password" autocomplete="current-password" required>
            </label>

            <button class="button large" type="submit">Entrar</button>
        </form>

        <p class="muted">Ainda não tem acesso? <a href="<?= e(url('/cadastro')) ?>">Crie seu cadastro</a>.</p>
    </div>
</section>
