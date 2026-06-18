<?php defined('BASE_PATH') || exit('Acesso direto não permitido.'); ?>

<section class="dashboard-shell finance-shell">
    <div class="admin-toolbar">
        <div class="dashboard-heading">
            <span class="eyebrow">Financeiro</span>
            <h1>Assinaturas e carteira</h1>
            <p>Histórico financeiro, plano ativo, estrutura inicial de pagamentos e moedas internas.</p>
        </div>
        <a class="button large" href="<?= e(url('/planos')) ?>">Ver planos</a>
    </div>

    <div class="metric-grid">
        <article class="metric"><span>Plano ativo</span><strong><?= e($activeSubscription['plan_name'] ?? 'Nenhum') ?></strong></article>
        <article class="metric"><span>Transações</span><strong><?= e((int) $summary['total_transactions']) ?></strong></article>
        <article class="metric"><span>Pendentes</span><strong><?= e((int) $summary['pending_transactions']) ?></strong></article>
        <article class="metric"><span>Moedas</span><strong><?= e((int) ($gamificationProfile['internal_coins'] ?? 0)) ?></strong></article>
    </div>

    <div class="finance-grid">
        <article class="finance-panel">
            <span class="eyebrow">Assinatura atual</span>
            <?php if ($activeSubscription): ?>
                <h2><?= e($activeSubscription['plan_name']) ?></h2>
                <p><?= (int) $activeSubscription['is_premium'] === 1 ? 'Recursos premium liberados.' : 'Plano gratuito ativo.' ?></p>
                <div class="course-meta">
                    <span><?= e($activeSubscription['status']) ?></span>
                    <span>Expira <?= e($activeSubscription['ends_at'] ? date('d/m/Y', strtotime($activeSubscription['ends_at'])) : 'sem data') ?></span>
                </div>
            <?php else: ?>
                <h2>Nenhum plano ativo</h2>
                <p>Assine um plano gratuito ou premium para organizar seu acesso.</p>
            <?php endif; ?>
        </article>

        <article class="finance-panel">
            <span class="eyebrow">Creator futuro</span>
            <h2>Monetização 80/20</h2>
            <p>Carteira preparada para professores criadores: 80% para o criador e 20% para a plataforma.</p>
            <div class="course-meta">
                <span>Disponível R$ <?= e(number_format((float) ($wallet['available_balance'] ?? 0), 2, ',', '.')) ?></span>
                <span>Pendente R$ <?= e(number_format((float) ($wallet['pending_balance'] ?? 0), 2, ',', '.')) ?></span>
            </div>
        </article>
    </div>

    <div class="section-toolbar">
        <div>
            <span class="eyebrow">Histórico</span>
            <h2>Transações</h2>
        </div>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Referência</th>
                    <th>Plano</th>
                    <th>Valor</th>
                    <th>Pagamento</th>
                    <th>Status</th>
                    <th>Data</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($transactions)): ?>
                    <tr><td colspan="6">Nenhuma transação registrada.</td></tr>
                <?php endif; ?>
                <?php foreach ($transactions as $transaction): ?>
                    <tr>
                        <td><?= e($transaction['reference'] ?? 'TME') ?></td>
                        <td><?= e($transaction['plan_name'] ?? 'Avulso') ?></td>
                        <td>R$ <?= e(number_format((float) $transaction['amount'], 2, ',', '.')) ?></td>
                        <td><?= e($transaction['payment_method'] ?? 'interno') ?></td>
                        <td><span class="status-badge <?= e($transaction['status']) ?>"><?= e(human_label($transaction['status'])) ?></span></td>
                        <td><?= e(date('d/m/Y H:i', strtotime($transaction['created_at']))) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
