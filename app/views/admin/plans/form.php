<?php
defined('BASE_PATH') || exit('Acesso direto nao permitido.');

$isEdit = (bool) $plan;
$value = static fn (string $key, mixed $default = ''): mixed => old($key, $plan[$key] ?? $default);
?>

<section class="dashboard-shell">
    <div class="dashboard-heading">
        <span class="eyebrow">Planos</span>
        <h1><?= $isEdit ? 'Editar plano' : 'Novo plano' ?></h1>
        <p>Configure preco, duracao, beneficios e status. Pagamentos reais entram em uma integracao futura.</p>
    </div>

    <form class="form grid-form admin-form" action="<?= e($action) ?>" method="post">
        <?= csrf_field() ?>

        <label>
            Nome
            <input type="text" name="name" value="<?= e($value('name')) ?>" required>
        </label>

        <label>
            Preco
            <input type="number" name="price" min="0" step="0.01" value="<?= e($value('price', '0.00')) ?>">
        </label>

        <label>
            Cobranca
            <select name="billing_cycle">
                <?php foreach (['mensal' => 'Mensal', 'anual' => 'Anual', 'unico' => 'Unico'] as $option => $label): ?>
                    <option value="<?= e($option) ?>" <?= $value('billing_cycle', 'mensal') === $option ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            Duracao em dias
            <input type="number" name="duration_days" min="1" value="<?= e($value('duration_days', 30)) ?>">
        </label>

        <label>
            Ordem
            <input type="number" name="sort_order" min="1" value="<?= e($value('sort_order', 1)) ?>">
        </label>

        <label>
            Status
            <select name="status">
                <?php foreach (['ativo' => 'Ativo', 'inativo' => 'Inativo'] as $option => $label): ?>
                    <option value="<?= e($option) ?>" <?= $value('status', 'ativo') === $option ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </label>

        <label class="check-card">
            <input type="checkbox" name="is_premium" value="1" <?= $value('is_premium', 0) ? 'checked' : '' ?>>
            <span>Plano premium com recursos e cursos restritos</span>
        </label>

        <label class="span-2">
            Descricao
            <textarea name="description" rows="3"><?= e($value('description')) ?></textarea>
        </label>

        <label class="span-2">
            Beneficios, um por linha
            <textarea name="benefits_text" rows="7" required><?= e($value('benefits_text')) ?></textarea>
        </label>

        <div class="span-2 actions-row">
            <button class="button large" type="submit"><?= $isEdit ? 'Salvar alteracoes' : 'Criar plano' ?></button>
            <a class="button ghost large" href="<?= e(url('/admin/planos')) ?>">Cancelar</a>
        </div>
    </form>
</section>
