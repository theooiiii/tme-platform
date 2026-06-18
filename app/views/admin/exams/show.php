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
            <span class="eyebrow">Prova</span>
            <h1><?= e($exam['title']) ?></h1>
            <p><?= e($exam['description'] ?: 'Sem descrição.') ?></p>
        </div>
        <div class="actions-row">
            <a class="button ghost large" href="<?= e(url('/admin/provas')) ?>">Voltar</a>
            <a class="button large" href="<?= e(url('/provas/' . $exam['id'])) ?>">Ver como aluno</a>
        </div>
    </div>

    <div class="metric-grid">
        <article class="metric"><span>Status</span><strong><?= e($exam['status']) ?></strong></article>
        <article class="metric"><span>Tempo</span><strong><?= e((int) $exam['time_limit_minutes']) ?> min</strong></article>
        <article class="metric"><span>Tentativas</span><strong><?= e((int) $exam['attempts_allowed']) ?></strong></article>
        <article class="metric"><span>Questões</span><strong><?= e(count($questions)) ?></strong></article>
    </div>

    <div class="admin-detail-grid">
        <section class="detail-card">
            <span class="eyebrow">Banco de questões</span>
            <h2>Adicionar questão</h2>
            <form class="admin-form form" action="<?= e(url('/admin/provas/' . $exam['id'] . '/questoes')) ?>" method="post">
                <?= csrf_field() ?>
                <label>
                    Tipo
                    <select name="question_type">
                        <option value="objetiva">Objetiva</option>
                        <option value="discursiva">Discursiva</option>
                    </select>
                </label>
                <label>
                    Disciplina
                    <select name="subject_id">
                        <option value="<?= e($exam['subject_id'] ?? 0) ?>">Usar disciplina da prova</option>
                        <?php foreach ($subjects as $subject): ?>
                            <option value="<?= e($subject['id']) ?>"><?= e($subject['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    Enunciado
                    <textarea name="statement_text" rows="4" required></textarea>
                </label>
                <label>
                    Alternativas (uma por linha)
                    <textarea name="alternatives" rows="5" placeholder="A primeira linha pode ser a correta, ou informe a letra abaixo."></textarea>
                </label>
                <div class="form-grid">
                    <label>
                        Resposta correta
                        <input type="text" name="correct_answer" placeholder="Texto da alternativa ou A/B/C">
                    </label>
                    <label>
                        Pontos
                        <input type="number" step="0.1" min="0.1" name="points" value="1">
                    </label>
                    <label>
                        Dificuldade
                        <select name="difficulty">
                            <option value="facil">Fácil</option>
                            <option value="media" selected>Média</option>
                            <option value="dificil">Difícil</option>
                        </select>
                    </label>
                </div>
                <label>
                    Explicação para correção
                    <textarea name="explanation" rows="3"></textarea>
                </label>
                <button class="button" type="submit">Adicionar questão</button>
            </form>
        </section>

        <section class="detail-card">
            <span class="eyebrow">Estrutura</span>
            <h2>Questões da prova</h2>
            <?php if (empty($questions)): ?>
                <p class="muted">Nenhuma questão adicionada ainda.</p>
            <?php else: ?>
                <div class="exam-question-list">
                    <?php foreach ($questions as $question): ?>
                        <article class="exam-question-card">
                            <span class="status-badge"><?= e(human_label($question['question_type'])) ?></span>
                            <h3><?= e($question['position']) ?>. <?= e($question['statement_text']) ?></h3>
                            <p><?= e(number_format((float) $question['exam_score'], 2, ',', '.')) ?> ponto(s) | <?= e($question['difficulty']) ?></p>
                            <?php $alternatives = $decodeAlternatives($question['alternatives'] ?? null); ?>
                            <?php if ($alternatives): ?>
                                <ol class="answer-list">
                                    <?php foreach ($alternatives as $alternative): ?>
                                        <li><?= e($alternative) ?></li>
                                    <?php endforeach; ?>
                                </ol>
                            <?php endif; ?>
                            <?php if ($question['correct_answer']): ?>
                                <p><strong>Correta:</strong> <?= e($question['correct_answer']) ?></p>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <section class="detail-card">
        <div class="section-toolbar">
            <span class="eyebrow">Tentativas</span>
            <h2>Envios dos alunos</h2>
        </div>
        <?php if (empty($attempts)): ?>
            <p class="muted">Nenhuma tentativa iniciada.</p>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Aluno</th><th>Status</th><th>Nota objetiva</th><th>Nota total</th><th>Envio</th><th>Ações</th></tr></thead>
                    <tbody>
                        <?php foreach ($attempts as $attempt): ?>
                            <tr>
                                <td><strong><?= e($attempt['student_name']) ?></strong><span><?= e($attempt['student_email']) ?></span></td>
                                <td><span class="status-badge <?= e($attempt['status']) ?>"><?= e(human_label($attempt['status'])) ?></span></td>
                                <td><?= e(number_format((float) $attempt['objective_score'], 2, ',', '.')) ?></td>
                                <td><?= e(number_format((float) $attempt['total_score'], 2, ',', '.')) ?></td>
                                <td><?= e($attempt['submitted_at'] ? date('d/m/Y H:i', strtotime($attempt['submitted_at'])) : '-') ?></td>
                                <td class="actions-cell">
                                    <a class="button small" href="<?= e(url('/admin/provas/tentativas/' . $attempt['id'])) ?>">Corrigir/ver</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <?php if (! empty($ranking)): ?>
        <section class="detail-card">
            <span class="eyebrow">Ranking</span>
            <h2>Melhores resultados</h2>
            <div class="ranking-list">
                <?php foreach ($ranking as $index => $row): ?>
                    <p><strong>#<?= e($index + 1) ?> <?= e($row['full_name']) ?></strong> - <?= e(number_format((float) $row['best_score'], 2, ',', '.')) ?> pts</p>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</section>
