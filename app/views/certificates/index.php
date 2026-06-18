<?php defined('BASE_PATH') || exit('Acesso direto não permitido.'); ?>

<section class="dashboard-shell">
    <div class="admin-toolbar">
        <div class="dashboard-heading">
            <span class="eyebrow">Aprendizagem</span>
            <h1>Certificados</h1>
            <p>Veja certificados emitidos automaticamente quando um curso chega a 100% de progresso.</p>
        </div>
        <div class="actions-row">
            <a class="button ghost large" href="<?= e(url('/certificados/validar')) ?>">Validar código</a>
            <a class="button large" href="<?= e(url('/ranking')) ?>">Ranking</a>
        </div>
    </div>

    <?php if (empty($certificates)): ?>
        <div class="empty-state">
            <h2>Nenhum certificado emitido</h2>
            <p>Conclua todos os módulos e aulas publicados de um curso para receber o certificado automaticamente.</p>
        </div>
    <?php else: ?>
        <div class="certificate-grid">
            <?php foreach ($certificates as $certificate): ?>
                <article class="certificate-card">
                    <span class="status-badge <?= e($certificate['validation_status']) ?>"><?= e(human_label($certificate['validation_status'])) ?></span>
                    <h2><?= e($certificate['course_title'] ?: $certificate['title']) ?></h2>
                    <p><?= e($certificate['title']) ?></p>
                    <div class="course-meta">
                        <span><?= e((int) $certificate['workload_hours']) ?>h</span>
                        <span><?= e(date('d/m/Y', strtotime($certificate['issued_at']))) ?></span>
                        <span><?= e($certificate['code']) ?></span>
                    </div>
                    <?php if ($certificate['validation_status'] === 'revogado'): ?>
                        <p class="danger-text">Revogado: <?= e($certificate['revocation_reason'] ?: 'sem motivo informado') ?></p>
                    <?php endif; ?>
                    <div class="inline-actions">
                        <a href="<?= e(url('/certificados/ver/' . $certificate['code'])) ?>">Visualizar</a>
                        <a href="<?= e(url('/certificados/validar/' . $certificate['code'])) ?>">Validar</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
