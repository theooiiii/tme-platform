<?php defined('BASE_PATH') || exit('Acesso direto nao permitido.'); ?>

<section class="dashboard-shell">
    <div class="detail-grid">
        <?php if (! empty($event['image_path'])): ?>
            <img class="course-cover" src="<?= e(url('/' . $event['image_path'])) ?>" alt="Imagem do evento <?= e($event['title']) ?>">
        <?php else: ?>
            <div class="course-cover placeholder">TME</div>
        <?php endif; ?>

        <div class="dashboard-heading">
            <span class="eyebrow"><?= e($event['event_type']) ?></span>
            <h1><?= e($event['title']) ?></h1>
            <p><?= e($event['description'] ?: 'Evento academico TME.') ?></p>
            <div class="course-meta spacious">
                <span><?= e($event['starts_at'] ? date('d/m/Y H:i', strtotime($event['starts_at'])) : 'data a definir') ?></span>
                <span><?= e($event['is_online'] ? 'Online' : ($event['location'] ?: 'Local a definir')) ?></span>
                <span><?= e((int) $event['registrations_count']) ?> / <?= e($event['capacity'] ?: 'sem limite') ?> inscritos</span>
                <span><?= e((int) $event['workload_hours']) ?>h</span>
            </div>

            <?php if (! $user): ?>
                <a class="button large" href="<?= e(url('/login')) ?>">Entrar para se inscrever</a>
            <?php elseif ($event['viewer_registration_status']): ?>
                <span class="status-badge <?= e($event['viewer_registration_status']) ?>">Inscricao <?= e($event['viewer_registration_status']) ?></span>
            <?php else: ?>
                <form action="<?= e(url('/eventos/' . $event['id'] . '/inscrever')) ?>" method="post">
                    <?= csrf_field() ?>
                    <button class="button large" type="submit">Inscrever-se</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</section>
