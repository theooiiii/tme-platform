<?php defined('BASE_PATH') || exit('Acesso direto não permitido.'); ?>

<section class="dashboard-shell exams-shell">
    <div class="admin-toolbar">
        <div class="dashboard-heading">
            <span class="eyebrow">Prova</span>
            <h1><?= e($exam['title']) ?></h1>
            <p><?= e($exam['description'] ?: 'Leia as informações antes de iniciar sua tentativa.') ?></p>
        </div>
        <a class="button ghost large" href="<?= e(url('/provas')) ?>">Voltar</a>
    </div>

    <div class="metric-grid">
        <article class="metric"><span>Questões</span><strong><?= e((int) $exam['questions_count']) ?></strong></article>
        <article class="metric"><span>Tempo</span><strong><?= e((int) $exam['time_limit_minutes']) ?> min</strong></article>
        <article class="metric"><span>Tentativas</span><strong><?= e((int) $exam['attempts_used']) ?>/<?= e((int) $exam['attempts_allowed']) ?></strong></article>
        <article class="metric"><span>Disciplina</span><strong><?= e($exam['subject_name'] ?: 'Geral') ?></strong></article>
    </div>

    <section class="detail-card">
        <span class="eyebrow">Resumo</span>
        <h2>Antes de comecar</h2>
        <p>Ao iniciar, o controle de tempo sera aplicado no navegador e confirmado pelo servidor no envio.</p>
        <p><strong>Janela:</strong> <?= e($exam['starts_at'] ? date('d/m/Y H:i', strtotime($exam['starts_at'])) : 'inicio livre') ?> até <?= e($exam['ends_at'] ? date('d/m/Y H:i', strtotime($exam['ends_at'])) : 'sem fim definido') ?></p>

        <?php if ((int) $exam['attempts_used'] >= (int) $exam['attempts_allowed']): ?>
            <div class="flash info">Você já usou todas as tentativas disponiveis.</div>
        <?php else: ?>
            <form action="<?= e(url('/provas/' . $exam['id'] . '/iniciar')) ?>" method="post">
                <?= csrf_field() ?>
                <button class="button large" type="submit">Iniciar tentativa</button>
            </form>
        <?php endif; ?>
    </section>

    <?php if (! empty($ranking)): ?>
        <section class="detail-card">
            <span class="eyebrow">Ranking</span>
            <h2>Melhores resultados</h2>
            <?php foreach ($ranking as $index => $row): ?>
                <p><strong>#<?= e($index + 1) ?> <?= e($row['full_name']) ?></strong> - <?= e(number_format((float) $row['best_score'], 2, ',', '.')) ?> pts</p>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>
</section>
