<?php defined('BASE_PATH') || exit('Acesso direto não permitido.'); ?>

<?php
$rows = $logs['data'] ?? [];
$total = (int) ($logs['total'] ?? 0);
$page = (int) ($logs['page'] ?? 1);
$perPage = (int) ($logs['per_page'] ?? 30);
$pages = max(1, (int) ceil($total / max(1, $perPage)));
?>

<section class="page-section">
    <div class="page-header">
        <div>
            <span class="eyebrow">Observabilidade</span>
            <h1>Logs administrativos</h1>
            <p>Eventos de segurança, ações críticas e trilha operacional da plataforma.</p>
        </div>
    </div>

    <form class="filter-bar" method="get">
        <input type="search" name="q" value="<?= e($filters['q'] ?? '') ?>" placeholder="Ação, contexto ou usuário">
        <select name="level">
            <option value="">Todos os níveis</option>
            <?php foreach (['info', 'warning', 'error', 'security'] as $level): ?>
                <option value="<?= e($level) ?>" <?= ($filters['level'] ?? '') === $level ? 'selected' : '' ?>><?= e(human_label($level)) ?></option>
            <?php endforeach; ?>
        </select>
        <button class="button" type="submit">Filtrar</button>
    </form>

    <div class="table-card">
        <?php if (empty($rows)): ?>
            <div class="empty-state">
                <h2>Nenhum log encontrado</h2>
                <p>Os eventos aparecerão aqui conforme a plataforma for utilizada.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Nível</th>
                            <th>Ação</th>
                            <th>Usuário</th>
                            <th>IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $row): ?>
                            <tr>
                                <td><?= e($row['created_at']) ?></td>
                                <td><span class="status-badge"><?= e(human_label($row['level'])) ?></span></td>
                                <td>
                                    <strong><?= e($row['action']) ?></strong>
                                    <span><?= e(mb_strimwidth((string) $row['context'], 0, 120, '...')) ?></span>
                                </td>
                                <td><?= e($row['user_name'] ?: 'Sistema') ?></td>
                                <td><?= e($row['ip_address'] ?: '-') ?></td>
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
            <a href="<?= e(url('/admin/logs?pagina=' . ($page - 1))) ?>">Anterior</a>
        <?php endif; ?>
        <strong>Página <?= e($page) ?> de <?= e($pages) ?></strong>
        <?php if ($page < $pages): ?>
            <a href="<?= e(url('/admin/logs?pagina=' . ($page + 1))) ?>">Próxima</a>
        <?php endif; ?>
    </div>
</section>
