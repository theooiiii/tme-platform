<?php defined('BASE_PATH') || exit('Acesso direto não permitido.'); ?>

<section class="dashboard-shell">
    <div class="dashboard-heading">
        <span class="eyebrow">Turmas</span>
        <h1>Minhas turmas</h1>
        <p>Turmas vinculadas ao seu perfil, com disciplinas, professores, alunos e materiais futuros.</p>
    </div>

    <?php if (empty($classes)): ?>
        <div class="empty-state"><h2>Nenhuma turma vinculada</h2><p>As turmas vinculadas pela administração aparecerão aqui.</p></div>
    <?php else: ?>
        <div class="module-grid">
            <?php foreach ($classes as $class): ?>
                <article class="module-card">
                    <span class="status-badge <?= e($class['status']) ?>"><?= e(human_label($class['status'])) ?></span>
                    <h2><?= e($class['name']) ?></h2>
                    <p><?= e($class['description'] ?: 'Turma acadêmica TME.') ?></p>
                    <a href="<?= e(url('/turmas/' . $class['id'])) ?>">Abrir turma</a>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
