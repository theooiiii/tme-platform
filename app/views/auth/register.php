<?php defined('BASE_PATH') || exit('Acesso direto não permitido.'); ?>

<section class="auth-page wide">
    <div class="auth-panel">
        <div class="section-heading">
            <span class="eyebrow">Cadastro</span>
            <h1>Solicitar conta</h1>
            <p>O cadastro inicial está disponível para alunos e professores. A liberação acontece após aprovação interna.</p>
        </div>

        <form class="form grid-form" action="<?= e(url('/cadastro')) ?>" method="post" novalidate>
            <?= csrf_field() ?>

            <label class="span-2">
                Nome completo
                <input type="text" name="full_name" value="<?= e(old('full_name')) ?>" autocomplete="name" required>
            </label>

            <label>
                E-mail
                <input type="email" name="email" value="<?= e(old('email')) ?>" autocomplete="email" required>
            </label>

            <label>
                Senha
                <input type="password" name="password" autocomplete="new-password" minlength="8" required>
            </label>

            <label>
                Tipo de conta
                <select name="account_type" required>
                    <option value="">Selecione</option>
                    <option value="aluno" <?= old('account_type') === 'aluno' ? 'selected' : '' ?>>Aluno</option>
                    <option value="professor" <?= old('account_type') === 'professor' ? 'selected' : '' ?>>Professor</option>
                </select>
            </label>

            <label>
                Telefone
                <input type="tel" name="phone" value="<?= e(old('phone')) ?>" autocomplete="tel" required>
            </label>

            <label>
                CPF
                <input type="text" name="cpf" value="<?= e(old('cpf')) ?>" inputmode="numeric" maxlength="14" required>
            </label>

            <label>
                Data de nascimento
                <input type="date" name="birth_date" value="<?= e(old('birth_date')) ?>" required>
            </label>

            <label>
                Estado
                <input type="text" name="state" value="<?= e(old('state')) ?>" maxlength="2" required>
            </label>

            <label>
                Cidade
                <input type="text" name="city" value="<?= e(old('city')) ?>" required>
            </label>

            <label class="span-2" data-institution-field>
                Instituição
                <input type="text" name="institution" value="<?= e(old('institution')) ?>" list="institution-suggestions" data-institution-search autocomplete="organization">
                <datalist id="institution-suggestions"></datalist>
            </label>

            <label class="check-field span-2">
                <input type="checkbox" name="is_independent" value="1" <?= old('is_independent') ? 'checked' : '' ?> data-independent-toggle>
                <span>Sou independente</span>
            </label>

            <label>
                Área de interesse
                <input type="text" name="interest_area" value="<?= e(old('interest_area')) ?>" required>
            </label>

            <label>
                Objetivo dentro da plataforma
                <input type="text" name="platform_goal" value="<?= e(old('platform_goal')) ?>" required>
            </label>

            <label class="check-field span-2">
                <input type="checkbox" name="terms_accepted" value="1" <?= old('terms_accepted') ? 'checked' : '' ?> required>
                <span>Aceito os termos de uso e a política de privacidade da TME.</span>
            </label>

            <button class="button large span-2" type="submit">Enviar para aprovação</button>
        </form>
    </div>
</section>
