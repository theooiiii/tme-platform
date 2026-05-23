<?php defined('BASE_PATH') || exit('Acesso direto nao permitido.'); ?>

<section class="dashboard-shell">
    <div class="dashboard-heading">
        <span class="eyebrow">Administrador</span>
        <h1>Administração TME</h1>
        <p>Controle inicial de usuários, permissões, aprovações, instituições e módulos da plataforma.</p>
    </div>

    <div class="metric-grid">
        <article class="metric"><span>Contas pendentes</span><strong><?= e($counts['pending_users']) ?></strong></article>
        <article class="metric"><span>Usuários aprovados</span><strong><?= e($counts['approved_users']) ?></strong></article>
        <article class="metric"><span>Cursos</span><strong><?= e($counts['courses']) ?></strong></article>
        <article class="metric"><span>Eventos</span><strong><?= e($counts['events']) ?></strong></article>
    </div>

    <div class="module-grid">
        <article class="module-card"><h2>Aprovação de contas</h2><p>Cadastros de alunos e professores entram como pendentes.</p><a href="<?= e(url('/admin/contas-pendentes')) ?>">Abrir fila</a></article>
        <article class="module-card"><h2>Cursos</h2><p>Crie cursos, módulos, aulas e materiais com filtros administrativos.</p><a href="<?= e(url('/admin/cursos')) ?>">Gerenciar cursos</a></article>
        <article class="module-card"><h2>Matrículas</h2><p>Veja alunos matriculados, status e progresso por curso.</p><a href="<?= e(url('/admin/matriculas')) ?>">Ver matrículas</a></article>
    </div>
</section>
