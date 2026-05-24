<?php defined('BASE_PATH') || exit('Acesso direto nao permitido.'); ?>

<section class="dashboard-shell">
    <div class="dashboard-heading">
        <span class="eyebrow">Turmas</span>
        <h1><?= e($title) ?></h1>
    </div>
    <form class="admin-form form grid-form" action="<?= e($action) ?>" method="post">
        <?= csrf_field() ?>
        <label>
            Nome
            <input type="text" name="name" required>
        </label>
        <label>
            Instituicao
            <select name="institution_id">
                <option value="">Nenhuma</option>
                <?php foreach ($institutions as $institution): ?>
                    <option value="<?= e($institution['id']) ?>"><?= e($institution['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>
            Periodo
            <input type="text" name="period" placeholder="2026.1, manha, noite">
        </label>
        <label>
            Status
            <select name="status">
                <option value="ativa">Ativa</option>
                <option value="inativa">Inativa</option>
                <option value="arquivada">Arquivada</option>
            </select>
        </label>
        <label class="span-2">
            Descricao
            <textarea name="description" rows="4"></textarea>
        </label>
        <button class="button span-2" type="submit">Salvar turma</button>
    </form>
</section>
