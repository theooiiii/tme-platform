<?php defined('BASE_PATH') || exit('Acesso direto não permitido.'); ?>

<section class="dashboard-shell">
    <div class="dashboard-heading">
        <span class="eyebrow">Eventos</span>
        <h1><?= e($title) ?></h1>
        <p>Cadastre eventos publicados ou deixe como rascunho para preparar a agenda.</p>
    </div>

    <form class="admin-form form grid-form" action="<?= e($action) ?>" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <label>
            Título
            <input type="text" name="title" value="<?= e(old('title')) ?>" required>
        </label>
        <label>
            Tipo
            <select name="event_type">
                <?php foreach (['palestra', 'workshop', 'aula_ao_vivo', 'simulado', 'olimpiada', 'hackathon'] as $type): ?>
                    <option value="<?= e($type) ?>"><?= e(human_label($type)) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>
            Data/hora início
            <input type="datetime-local" name="starts_at" required>
        </label>
        <label>
            Data/hora fim
            <input type="datetime-local" name="ends_at">
        </label>
        <label>
            Local
            <input type="text" name="location" value="<?= e(old('location')) ?>">
        </label>
        <label>
            Link online
            <input type="url" name="meeting_url" value="<?= e(old('meeting_url')) ?>">
        </label>
        <label>
            Vagas
            <input type="number" name="capacity" min="0" value="<?= e(old('capacity', 0)) ?>">
        </label>
        <label>
            Carga horária
            <input type="number" name="workload_hours" min="0" value="<?= e(old('workload_hours', 0)) ?>">
        </label>
        <label>
            Status
            <select name="status">
                <?php foreach (['rascunho', 'publicado', 'encerrado'] as $status): ?>
                    <option value="<?= e($status) ?>"><?= e(human_label($status)) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>
            Imagem opcional
            <input type="file" name="image" accept="image/png,image/jpeg,image/webp">
        </label>
        <label class="check-field span-2">
            <input type="checkbox" name="is_online" value="1">
            Evento online
        </label>
        <label class="check-field span-2">
            <input type="checkbox" name="certificate_enabled" value="1">
            Habilitar certificado de participacao
        </label>
        <label class="span-2">
            Descrição
            <textarea name="description" rows="5"><?= e(old('description')) ?></textarea>
        </label>
        <button class="button span-2" type="submit">Salvar evento</button>
    </form>
</section>
