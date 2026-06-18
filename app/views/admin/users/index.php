<?php defined('BASE_PATH') || exit('Acesso direto não permitido.'); ?>

<?php
$rows = $users['data'] ?? [];
$total = (int) ($users['total'] ?? 0);
$page = (int) ($users['page'] ?? 1);
$perPage = (int) ($users['per_page'] ?? 20);
$pages = max(1, (int) ceil($total / max(1, $perPage)));
?>

<section class="page-section">
    <div class="page-header">
        <div>
            <span class="eyebrow">Administração</span>
            <h1>Usuários</h1>
            <p>Controle status, papéis e acessos dos usuários da plataforma.</p>
        </div>
        <a class="button ghost" href="<?= e(url('/admin/contas-pendentes')) ?>">Contas pendentes</a>
    </div>

    <form class="filter-bar" method="get">
        <input type="search" name="q" value="<?= e($filters['q'] ?? '') ?>" placeholder="Nome, e-mail ou CPF">
        <select name="status">
            <option value="">Todos os status</option>
            <?php foreach (config('app.account_statuses', []) as $status): ?>
                <option value="<?= e($status) ?>" <?= ($filters['status'] ?? '') === $status ? 'selected' : '' ?>><?= e(human_label($status)) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="role">
            <option value="">Todos os perfis</option>
            <?php foreach ($roles as $role): ?>
                <option value="<?= e($role['slug']) ?>" <?= ($filters['role'] ?? '') === $role['slug'] ? 'selected' : '' ?>><?= e($role['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <button class="button" type="submit">Filtrar</button>
    </form>

    <div class="table-card">
        <?php if (empty($rows)): ?>
            <div class="empty-state">
                <h2>Nenhum usuário encontrado</h2>
                <p>Ajuste os filtros para ampliar a busca.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Usuário</th>
                            <th>Perfil</th>
                            <th>Status</th>
                            <th>Instituição</th>
                            <th>Último login</th>
                            <th>Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $row): ?>
                            <tr>
                                <td>
                                    <strong><?= e($row['full_name']) ?></strong>
                                    <span><?= e($row['email']) ?></span>
                                </td>
                                <td><?= e($row['role_name']) ?></td>
                                <td><span class="status-badge"><?= e(human_label($row['status'])) ?></span></td>
                                <td><?= e($row['institution_name'] ?: 'Independente') ?></td>
                                <td><?= e($row['last_login_at'] ?: '-') ?></td>
                                <td>
                                    <form class="inline-admin-form" method="post" action="<?= e(url('/admin/usuarios/' . $row['id'] . '/atualizar')) ?>">
                                        <?= csrf_field() ?>
                                        <select name="role" aria-label="Perfil">
                                            <?php foreach ($roles as $role): ?>
                                                <option value="<?= e($role['slug']) ?>" <?= $row['role_slug'] === $role['slug'] ? 'selected' : '' ?>><?= e($role['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <select name="status" aria-label="Status">
                                            <?php foreach (config('app.account_statuses', []) as $status): ?>
                                                <option value="<?= e($status) ?>" <?= $row['status'] === $status ? 'selected' : '' ?>><?= e(human_label($status)) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button class="button small" type="submit">Salvar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <div class="pagination">
        <span><?= e($total) ?> registros</span>
        <?php if ($page > 1): ?>
            <a href="<?= e(url('/admin/usuarios?pagina=' . ($page - 1))) ?>">Anterior</a>
        <?php endif; ?>
        <strong>Página <?= e($page) ?> de <?= e($pages) ?></strong>
        <?php if ($page < $pages): ?>
            <a href="<?= e(url('/admin/usuarios?pagina=' . ($page + 1))) ?>">Próxima</a>
        <?php endif; ?>
    </div>
</section>
