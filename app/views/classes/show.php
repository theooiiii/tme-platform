<?php defined('BASE_PATH') || exit('Acesso direto nao permitido.'); ?>

<section class="dashboard-shell">
    <div class="dashboard-heading">
        <span class="eyebrow">Turma</span>
        <h1><?= e($class['name']) ?></h1>
        <p><?= e($class['description'] ?: 'Detalhes da turma, disciplinas, alunos e professores.') ?></p>
    </div>

    <div class="metric-grid">
        <article class="metric"><span>Disciplinas</span><strong><?= e(count($subjects)) ?></strong></article>
        <article class="metric"><span>Alunos</span><strong><?= e(count($students)) ?></strong></article>
        <article class="metric"><span>Professores</span><strong><?= e(count($teachers)) ?></strong></article>
        <article class="metric"><span>Ranking</span><strong>Futuro</strong></article>
    </div>

    <div class="detail-columns">
        <section class="profile-panel">
            <h2>Disciplinas</h2>
            <?php foreach ($subjects as $subject): ?>
                <p><strong><?= e($subject['name']) ?></strong><br><span class="muted"><?= e($subject['teacher_name'] ?: 'Professor a definir') ?></span></p>
            <?php endforeach; ?>
        </section>
        <section class="profile-panel">
            <h2>Professores</h2>
            <?php foreach ($teachers as $teacher): ?><p><?= e($teacher['full_name']) ?></p><?php endforeach; ?>
        </section>
        <section class="profile-panel">
            <h2>Materiais futuros</h2>
            <p class="muted">Area preparada para materiais, calendario, frequencia e ranking por turma.</p>
        </section>
    </div>
</section>
