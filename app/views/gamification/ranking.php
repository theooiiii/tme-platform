<?php defined('BASE_PATH') || exit('Acesso direto não permitido.'); ?>

<section class="dashboard-shell">
    <div class="admin-toolbar">
        <div class="dashboard-heading">
            <span class="eyebrow">Gamificacao</span>
            <h1>Ranking TME</h1>
            <p>Classificação global por XP, nível, moedas e conquistas. O filtro por curso considera alunos matriculados.</p>
        </div>
        <a class="button ghost large" href="<?= e(url('/perfil#estatisticas')) ?>">Meu perfil</a>
    </div>

    <form class="filter-form ranking-filter-form" action="<?= e(url('/ranking')) ?>" method="get">
        <label>
            Curso
            <select name="course_id">
                <option value="">Ranking global</option>
                <?php foreach ($courses as $course): ?>
                    <option value="<?= e($course['id']) ?>" <?= (int) $selectedCourseId === (int) $course['id'] ? 'selected' : '' ?>><?= e($course['title']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <button class="button" type="submit">Aplicar</button>
        <a class="button ghost" href="<?= e(url('/ranking')) ?>">Global</a>
    </form>

    <?php if (empty($ranking)): ?>
        <div class="empty-state">
            <h2>Ranking em formacao</h2>
            <p>XP sera exibido conforme alunos e professores estudarem, enviarem atividades e concluirem cursos.</p>
        </div>
    <?php else: ?>
        <div class="ranking-list">
            <?php foreach ($ranking as $index => $row): ?>
                <article class="ranking-row">
                    <strong class="ranking-position">#<?= e($index + 1) ?></strong>
                    <div class="profile-avatar small"><?= e(strtoupper(substr($row['full_name'], 0, 1))) ?></div>
                    <div>
                        <h2><?= e($row['full_name']) ?></h2>
                        <p><?= e(role_label($row['role_slug'])) ?> | <?= e((int) $row['badges_count']) ?> badges</p>
                    </div>
                    <div class="ranking-stats">
                        <span><?= e((int) $row['xp_total']) ?> XP</span>
                        <span>Nivel <?= e((int) $row['level']) ?></span>
                        <span><?= e((int) $row['internal_coins']) ?> moedas</span>
                        <span><?= e((int) $row['streak_days']) ?> dias</span>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
