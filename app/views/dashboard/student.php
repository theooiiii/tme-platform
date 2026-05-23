<?php defined('BASE_PATH') || exit('Acesso direto nao permitido.'); ?>

<section class="dashboard-shell">
    <div class="dashboard-heading">
        <span class="eyebrow">Aluno</span>
        <h1>Olá, <?= e($user['full_name']) ?></h1>
        <p>Seu espaço inicial para cursos, atividades, biblioteca, eventos e evolução acadêmica.</p>
    </div>

    <div class="metric-grid">
        <article class="metric"><span>Cursos ativos</span><strong>0</strong></article>
        <article class="metric"><span>XP</span><strong>0</strong></article>
        <article class="metric"><span>Atividades</span><strong>0</strong></article>
        <article class="metric"><span>Certificados</span><strong>0</strong></article>
    </div>

    <div class="module-grid">
        <article class="module-card"><h2>Minha aprendizagem</h2><p>Acompanhe matrículas, aulas concluídas e progresso por curso.</p><a href="<?= e(url('/aluno/meus-cursos')) ?>">Meus cursos</a></article>
        <article class="module-card"><h2>Catálogo</h2><p>Encontre cursos publicados e faça matrícula com um clique.</p><a href="<?= e(url('/aluno/cursos')) ?>">Ver catálogo</a></article>
        <article class="module-card"><h2>Comunidade</h2><p>Projetos, publicações e comentários com moderação acadêmica.</p></article>
    </div>
</section>
