<?php defined('BASE_PATH') || exit('Acesso direto nao permitido.'); ?>

<section class="dashboard-shell">
    <div class="dashboard-heading">
        <span class="eyebrow">Aluno</span>
        <h1>Ola, <?= e($user['full_name']) ?></h1>
        <p>Seu espaco inicial para cursos, atividades, biblioteca, eventos e evolucao academica.</p>
    </div>

    <div class="metric-grid">
        <article class="metric"><span>Cursos ativos</span><strong><?= e($stats['enrolled_courses']) ?></strong></article>
        <article class="metric"><span>XP</span><strong><?= e((int) $profile['xp_total']) ?></strong></article>
        <article class="metric"><span>Atividades</span><strong><?= e($stats['submitted_activities']) ?></strong></article>
        <article class="metric"><span>Certificados</span><strong><?= e($stats['certificates']) ?></strong></article>
    </div>

    <div class="module-grid">
        <article class="module-card"><h2>Minha aprendizagem</h2><p>Acompanhe matriculas, aulas concluidas e progresso por curso.</p><a href="<?= e(url('/meus-cursos')) ?>">Meus cursos</a></article>
        <article class="module-card"><h2>Catalogo</h2><p>Encontre cursos publicados e faca matricula com um clique.</p><a href="<?= e(url('/aluno/cursos')) ?>">Ver catalogo</a></article>
        <article class="module-card"><h2>Atividades</h2><p>Veja tarefas abertas, envie respostas e acompanhe correcoes.</p><a href="<?= e(url('/atividades')) ?>">Minhas atividades</a></article>
        <article class="module-card"><h2>Boletim</h2><p>Resumo simples das notas por curso.</p><a href="<?= e(url('/boletim')) ?>">Ver boletim</a></article>
        <article class="module-card"><h2>Certificados</h2><p>Consulte certificados emitidos e valide codigos publicos.</p><a href="<?= e(url('/certificados')) ?>">Abrir certificados</a></article>
        <article class="module-card"><h2>Ranking</h2><p>Acompanhe XP, nivel e badges na comunidade TME.</p><a href="<?= e(url('/ranking')) ?>">Ver ranking</a></article>
        <article class="module-card"><h2>Biblioteca</h2><p>Materiais publicados, favoritos e historico de acesso.</p><a href="<?= e(url('/biblioteca')) ?>">Abrir biblioteca</a></article>
        <article class="module-card"><h2>Comunidade</h2><p>Projetos, publicacoes e comentarios com moderacao academica.</p><a href="<?= e(url('/comunidade')) ?>">Entrar</a></article>
    </div>
</section>
