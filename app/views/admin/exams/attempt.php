<?php
defined('BASE_PATH') || exit('Acesso direto nao permitido.');

$decodeAlternatives = static function (?string $json): array {
    $decoded = $json ? json_decode($json, true) : [];
    return is_array($decoded) ? $decoded : [];
};
?>

<section class="dashboard-shell exams-shell">
    <div class="admin-toolbar">
        <div class="dashboard-heading">
            <span class="eyebrow">Correcao</span>
            <h1><?= e($attempt['title']) ?></h1>
            <p><?= e($attempt['student_name']) ?> | tentativa <?= e((int) $attempt['attempt_number']) ?> | status <?= e($attempt['status']) ?></p>
        </div>
        <a class="button ghost large" href="<?= e(url('/admin/provas/' . $attempt['exam_id'])) ?>">Voltar</a>
    </div>

    <div class="metric-grid">
        <article class="metric"><span>Objetivas</span><strong><?= e(number_format((float) $attempt['objective_score'], 2, ',', '.')) ?></strong></article>
        <article class="metric"><span>Discursivas</span><strong><?= e(number_format((float) $attempt['manual_score'], 2, ',', '.')) ?></strong></article>
        <article class="metric"><span>Total</span><strong><?= e(number_format((float) $attempt['total_score'], 2, ',', '.')) ?></strong></article>
        <article class="metric"><span>Enviado</span><strong><?= e($attempt['submitted_at'] ? date('d/m H:i', strtotime($attempt['submitted_at'])) : '-') ?></strong></article>
    </div>

    <form class="admin-form form" action="<?= e(url('/admin/provas/tentativas/' . $attempt['id'] . '/corrigir')) ?>" method="post">
        <?= csrf_field() ?>

        <div class="exam-question-list">
            <?php foreach ($answers as $answer): ?>
                <article class="exam-question-card">
                    <span class="status-badge <?= e($answer['status']) ?>"><?= e($answer['question_type']) ?></span>
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
                        <p><strong>Resposta do aluno:</strong> <?= e($answer['selected_option'] ?: '-') ?></p>
                        <p><strong>Correta:</strong> <?= e($answer['correct_answer'] ?: '-') ?></p>
                        <p><strong>Pontos:</strong> <?= e(number_format((float) $answer['score_awarded'], 2, ',', '.')) ?> / <?= e(number_format((float) $answer['max_score'], 2, ',', '.')) ?></p>
                    <?php else: ?>
                        <div class="submission-content">
                            <?= nl2br(e($answer['answer_text'] ?: 'Sem resposta textual.')) ?>
                        </div>
                        <div class="form-grid">
                            <label>
                                Nota (max <?= e(number_format((float) $answer['max_score'], 2, ',', '.')) ?>)
                                <input type="number" step="0.1" min="0" max="<?= e($answer['max_score']) ?>" name="scores[<?= e($answer['id']) ?>]" value="<?= e($answer['score_awarded']) ?>">
                            </label>
                            <label>
                                Feedback
                                <input type="text" name="feedbacks[<?= e($answer['id']) ?>]" value="<?= e($answer['feedback'] ?? '') ?>">
                            </label>
                        </div>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>

        <div class="form-actions">
            <button class="button large" type="submit">Salvar correcao</button>
        </div>
    </form>
</section>
