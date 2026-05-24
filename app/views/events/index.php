<?php defined('BASE_PATH') || exit('Acesso direto nao permitido.'); ?>

<section class="dashboard-shell">
    <div class="dashboard-heading">
        <span class="eyebrow">Eventos</span>
        <h1>Agenda TME</h1>
        <p>Palestras, workshops, aulas ao vivo, simulados, olimpiadas e hackathons publicados pela equipe.</p>
    </div>

    <?php if (empty($events)): ?>
        <div class="empty-state"><h2>Nenhum evento publicado</h2><p>A agenda academica aparecera aqui.</p></div>
    <?php else: ?>
        <div class="event-grid">
            <?php foreach ($events as $event): ?>
                <article class="event-card">
                    <?php if (! empty($event['image_path'])): ?>
                        <img src="<?= e(url('/' . $event['image_path'])) ?>" alt="Imagem do evento <?= e($event['title']) ?>">
                    <?php else: ?>
                        <div class="course-card-placeholder">TME</div>
                    <?php endif; ?>
                    <div>
                        <span class="status-badge"><?= e($event['event_type']) ?></span>
                        <h2><?= e($event['title']) ?></h2>
                        <p><?= e($event['description'] ?: 'Evento academico TME.') ?></p>
                        <div class="course-meta">
                            <span><?= e($event['starts_at'] ? date('d/m/Y H:i', strtotime($event['starts_at'])) : 'data a definir') ?></span>
                            <span><?= e((int) $event['registrations_count']) ?> inscritos</span>
                            <span><?= e((int) $event['workload_hours']) ?>h</span>
                        </div>
                        <a class="button" href="<?= e(url('/eventos/' . $event['id'])) ?>">Ver evento</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
