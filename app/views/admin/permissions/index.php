<?php defined('BASE_PATH') || exit('Acesso direto não permitido.'); ?>

<section class="page-section">
    <div class="page-header">
        <div>
            <span class="eyebrow">Administração</span>
            <h1>Permissões</h1>
            <p>Mapa de papéis e permissões configuradas no banco da TME.</p>
        </div>
    </div>

    <div class="module-grid">
        <?php foreach ($roles as $role): ?>
            <article class="module-card">
                <h2><?= e($role['role_name']) ?></h2>
                <p><?= e(role_label($role['role_slug'])) ?> na estrutura de acesso.</p>
                <?php if (empty($role['permissions'])): ?>
                    <span class="status-badge">Sem permissões granulares</span>
                <?php else: ?>
                    <div class="badge-row">
                        <?php foreach ($role['permissions'] as $permission): ?>
                            <span class="status-badge"><?= e($permission['name']) ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>
</section>
