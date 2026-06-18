<?php
defined('BASE_PATH') || exit('Acesso direto não permitido.');

$decodeAlternatives = static function (?string $json): array {
    $decoded = $json ? json_decode($json, true) : [];
    return is_array($decoded) ? $decoded : [];
};
?>

<section class="dashboard-shell exams-shell">
    <div class="admin-toolbar">
        <div class="dashboard-heading">
            <span class="eyebrow">Resultado</span>
            <h1><?= e($attempt['title']) ?></h1>
            <p>Status da tentativa: <?= e($attempt['status']) ?></p>
        </div>
        <a class="button ghost large" href="<?= e(url('/provas')) ?>">Minhas provas</a>
    </div>

    <div class="metric-grid">
        <article class="metric"><span>Objetivas</span><strong><?= e(number_format((float) $attempt['objective_score'], 2, ',', '.')) ?></strong></article>
        <article class="metric"><span>Discursivas</span><strong><?= e(number_format((float) $attempt['manual_score'], 2, ',', '.')) ?></strong></article>
        <article class="metric"><span>Total</span><strong><?= e(number_format((float) $attempt['total_score'], 2, ',', '.')) ?></strong></article>
        <article class="metric"><span>Enviado</span><strong><?= e($attempt['submitted_at'] ? date('d/m H:i', strtotime($attempt['submitted_at'])) : '-') ?></strong></article>
    </div>

    <?php if ($attempt['status'] === 'pendente_correcao'): ?>
        <div class="flash info">Sua prova possui respostas discursivas aguardando correção manual.</div>
    <?php endif; ?>

    <div class="exam-question-list">
        <?php foreach ($answers as $answer): ?>
            <article class="exam-question-card">
                <span class="status-badge <?= e($answer['status']) ?>"><?= e(human_label($answer['question_type'])) ?></span>
                <h2><?= e($answer['statement_text']) ?></h2>

                <?php if ($answer['question_type'] === 'objetiva'): ?>
                    <?php $alternatives = $decodeAlternatives($answer['alternatives'] ?? null); ?>
                    <?php if ($alternatives): ?>
                        <ol class="answer-list">
                            <?php foreach ($alternatives as $alternative): ?>
                                <li class="<?= $answer['selected_option'] === $alternative ? 'selected' : '' ?>"><?= e($alternative) ?></li>
                            <?php endforeach; ?>
                        </ol>
                    <?php endif; ?>
                    <p><strong>Sua resposta:</strong> <?= e($answer['selected_option'] ?: '-') ?></p>
                    <p><strong>Resposta correta:</strong> <?= e($answer['correct_answer'] ?: '-') ?></p>
                <?php else: ?>
                    <div class="submission-content"><?= nl2br(e($answer['answer_text'] ?: 'Sem resposta textual.')) ?></div>
                    <?php if ($answer['feedback']): ?><p><strong>Feedback:</strong> <?= e($answer['feedback']) ?></p><?php endif; ?>
                <?php endif; ?>

                <p><strong>Pontos:</strong> <?= e(number_format((float) $answer['score_awarded'], 2, ',', '.')) ?> / <?= e(number_format((float) $answer['max_score'], 2, ',', '.')) ?></p>
            </article>
        <?php endforeach; ?>
    </div>

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
