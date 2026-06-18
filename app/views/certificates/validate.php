<?php defined('BASE_PATH') || exit('Acesso direto não permitido.'); ?>

<section class="auth-page">
    <div class="auth-panel">
        <span class="eyebrow">Validação pública</span>
        <h1>Validar certificado TME</h1>
        <p class="muted">Digite o código único do certificado para verificar autenticidade e status.</p>

        <form class="form" action="<?= e(url('/certificados/validar')) ?>" method="post">
            <?= csrf_field() ?>
            <label>
                Código do certificado
                <input type="text" name="code" placeholder="TME-CUR-2026-XXXXXXXX" required>
            </label>
            <button class="button large" type="submit">Validar</button>
        </form>
    </div>
</section>
