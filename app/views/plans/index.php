<?php
defined('BASE_PATH') || exit('Acesso direto nao permitido.');

$activePlanId = $currentSubscription['plan_id'] ?? null;
?>

<section class="dashboard-shell plans-shell">
    <div class="dashboard-heading">
        <span class="eyebrow">Financeiro TME</span>
        <h1>Planos e assinaturas</h1>
        <p>Escolha entre acesso gratuito e premium. Pagamentos PIX/cartao estao preparados para integracao futura.</p>
    </div>

    <?php if ($currentSubscription): ?>
        <div class="finance-highlight">
            <div>
                <span class="eyebrow">Plano atual</span>
                <h2><?= e($currentSubscription['plan_name']) ?></h2>
                <p><?= (int) $currentSubscription['is_premium'] === 1 ? 'Acesso premium ativo.' : 'Acesso gratuito ativo.' ?></p>
            </div>
            <a class="button ghost" href="<?= e(url('/financeiro')) ?>">Ver financeiro</a>
        </div>
    <?php endif; ?>

    <div class="plan-grid">
        <?php foreach ($plans as $plan): ?>
            <?php $isActive = (int) $activePlanId === (int) $plan['id']; ?>
            <article class="plan-card <?= (int) $plan['is_premium'] === 1 ? 'premium' : '' ?>">
                <div class="plan-card-head">
                    <span class="status-badge <?= (int) $plan['is_premium'] === 1 ? 'publicado' : 'ativo' ?>">
                        <?= (int) $plan['is_premium'] === 1 ? 'Premium' : 'Gratuito' ?>
                    </span>
                    <strong>R$ <?= e(number_format((float) $plan['price'], 2, ',', '.')) ?></strong>
                </div>
                <h2><?= e($plan['name']) ?></h2>
                <p><?= e($plan['description'] ?: 'Plano TME para aprendizagem e evolucao academica.') ?></p>
                <ul class="check-list">
                    <?php foreach ($plan['benefits_list'] as $benefit): ?>
                        <li><?= e($benefit) ?></li>
                    <?php endforeach; ?>
                </ul>
                <div class="plan-meta">
                    <span><?= e($plan['billing_cycle']) ?></span>
                    <span><?= e((int) $plan['duration_days']) ?> dias</span>
                </div>

                <?php if (! $user): ?>
                    <a class="button" href="<?= e(url('/login')) ?>">Entrar para assinar</a>
                <?php elseif ($isActive): ?>
                    <span class="button ghost disabled">Plano atual</span>
                <?php else: ?>
                    <form action="<?= e(url('/planos/' . $plan['id'] . '/assinar')) ?>" method="post">
                        <?= csrf_field() ?>
                        <button class="button" type="submit"><?= (float) $plan['price'] <= 0 ? 'Ativar gratuito' : 'Assinar plano' ?></button>
                    </form>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>
</section>
