<?php defined('BASE_PATH') || exit('Acesso direto nao permitido.'); ?>

<section class="dashboard-shell">
    <div class="dashboard-heading">
        <span class="eyebrow">Professor</span>
        <h1>Painel docente</h1>
        <p>Bem-vindo, <?= e($user['full_name']) ?>. A base docente está pronta para cursos, turmas, atividades e correções.</p>
    </div>

    <div class="metric-grid">
        <article class="metric"><span>Cursos</span><strong><?= e($counts['courses']) ?></strong></article>
        <article class="metric"><span>Turmas</span><strong>0</strong></article>
        <article class="metric"><span>Entregas</span><strong>0</strong></article>
        <article class="metric"><span>Eventos</span><strong><?= e($counts['events']) ?></strong></article>
    </div>

    <div class="module-grid">
        <article class="module-card"><h2>Estudar na TME</h2><p>Professores tambem podem se matricular e acompanhar progresso como alunos.</p><a href="<?= e(url('/aluno/cursos')) ?>">Catalogo</a></article>
        <article class="module-card"><h2>Meus cursos</h2><p>Continue cursos em andamento e revise aulas concluidas.</p><a href="<?= e(url('/meus-cursos')) ?>">Abrir cursos</a></article>
        <article class="module-card"><h2>Conteúdo</h2><p>Planejamento de módulos, aulas, materiais e simulados.</p></article>
        <article class="module-card"><h2>Avaliações</h2><p>Atividades, entregas, feedbacks e notas ficarão centralizados.</p></article>
        <article class="module-card"><h2>Publicações</h2><p>Posts e projetos podem alimentar a comunidade acadêmica.</p></article>
    </div>
</section>
