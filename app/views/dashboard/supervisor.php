<?php defined('BASE_PATH') || exit('Acesso direto não permitido.'); ?>

<section class="dashboard-shell">
    <div class="dashboard-heading">
        <span class="eyebrow">Supervisor</span>
        <h1>Supervisão acadêmica</h1>
        <p>Monitore aprovações, moderação, turmas e indicadores gerais da operação educacional.</p>
    </div>

    <div class="metric-grid">
        <article class="metric"><span>Contas pendentes</span><strong><?= e($counts['pending_users']) ?></strong></article>
        <article class="metric"><span>Usuários aprovados</span><strong><?= e($counts['approved_users']) ?></strong></article>
        <article class="metric"><span>Cursos</span><strong><?= e($counts['courses']) ?></strong></article>
        <article class="metric"><span>Eventos</span><strong><?= e($counts['events']) ?></strong></article>
    </div>

    <div class="actions-row">
        <a class="button" href="<?= e(url('/admin/contas-pendentes')) ?>">Analisar contas pendentes</a>
    </div>
</section>
