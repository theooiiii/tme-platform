<?php defined('BASE_PATH') || exit('Acesso direto nao permitido.'); ?>

<section class="dashboard-shell exams-shell">
    <div class="admin-toolbar sticky-exam-header">
        <div class="dashboard-heading">
            <span class="eyebrow">Tentativa em andamento</span>
            <h1><?= e($attempt['title']) ?></h1>
            <p>Responda as questoes e envie dentro do tempo limite.</p>
        </div>
        <?php if ($remainingSeconds !== null): ?>
            <div class="exam-timer" data-exam-timer="<?= e($remainingSeconds) ?>">
                <span>Tempo restante</span>
                <strong data-exam-timer-output>--:--</strong>
            </div>
        <?php endif; ?>
    </div>

    <form class="admin-form form exam-attempt-form" action="<?= e(url('/provas/tentativas/' . $attempt['id'] . '/enviar')) ?>" method="post" data-confirm="Enviar esta tentativa agora?">
        <?= csrf_field() ?>
        <div class="exam-question-list">
            <?php foreach ($questions as $question): ?>
                <?php $alternatives = $examModel->decodeAlternatives($question['alternatives'] ?? null); ?>
                <article class="exam-question-card">
                    <span class="status-badge"><?= e($question['question_type']) ?></span>
                    <h2><?= e($question['position']) ?>. <?= e($question['statement_text']) ?></h2>
                    <p><?= e(number_format((float) $question['exam_score'], 2, ',', '.')) ?> ponto(s)</p>

                    <?php if ($question['question_type'] === 'objetiva'): ?>
                        <div class="answer-options">
                            <?php foreach ($alternatives as $index => $alternative): ?>
                                <label class="answer-option">
                                    <input type="radio" name="answers[<?= e($question['id']) ?>]" value="<?= e($alternative) ?>" required>
                                    <span><?= e(chr(65 + $index)) ?>. <?= e($alternative) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <label>
                            Resposta discursiva
                            <textarea name="answers[<?= e($question['id']) ?>]" rows="7" required></textarea>
                        </label>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>

        <div class="form-actions">
            <button class="button large" type="submit">Enviar prova</button>
            <a class="button ghost large" href="<?= e(url('/provas')) ?>">Sair sem enviar</a>
        </div>
    </form>
</section>
