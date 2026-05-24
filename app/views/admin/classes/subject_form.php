<?php defined('BASE_PATH') || exit('Acesso direto nao permitido.'); ?>

<section class="dashboard-shell">
    <div class="dashboard-heading">
        <span class="eyebrow">Disciplinas</span>
        <h1><?= e($title) ?></h1>
    </div>
    <form class="admin-form form grid-form" action="<?= e($action) ?>" method="post">
        <?= csrf_field() ?>
        <label>
            Nome
            <input type="text" name="name" required>
        </label>
        <label>
            Area
            <input type="text" name="area">
        </label>
        <label>
            Carga horaria
            <input type="number" name="workload_hours" min="0">
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
        <button class="button span-2" type="submit">Salvar disciplina</button>
    </form>
</section>
