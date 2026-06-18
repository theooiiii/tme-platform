<?php
defined('BASE_PATH') || exit('Acesso direto não permitido.');

$typeLabels = ['duvida' => 'Duvida', 'artigo' => 'Artigo', 'projeto' => 'Projeto', 'material' => 'Material', 'conquista' => 'Conquista', 'aviso' => 'Aviso'];
?>

<section class="dashboard-shell">
    <div class="dashboard-heading">
        <span class="eyebrow">Administração</span>
        <h1>Comunidade acadêmica</h1>
        <p>Modere posts, aprove conteúdos acadêmicos, recuse itens inadequados, arquive ou destaque publicações.</p>
    </div>

    <form class="filter-form ranking-filter-form" action="<?= e(url('/admin/comunidade')) ?>" method="get">
        <label>
            Status
            <select name="status">
                <option value="">Todos</option>
                <?php foreach (['pendente', 'aprovado', 'recusado', 'arquivado'] as $status): ?>
                    <option value="<?= e($status) ?>" <?= ($filters['status'] ?? '') === $status ? 'selected' : '' ?>><?= e(human_label($status)) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>
            Tipo
            <select name="type">
                <option value="">Todos</option>
                <?php foreach ($typeLabels as $value => $label): ?>
                    <option value="<?= e($value) ?>" <?= ($filters['type'] ?? '') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <button class="button" type="submit">Filtrar</button>
        <a class="button ghost" href="<?= e(url('/admin/comunidade')) ?>">Limpar</a>
    </form>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Post</th>
                    <th>Autor</th>
                    <th>Status</th>
                    <th>Interacoes</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($posts as $post): ?>
                    <tr>
                        <td>
                            <strong><?= e($post['title']) ?></strong>
                            <span><?= e($typeLabels[$post['post_type']] ?? $post['post_type']) ?> | <?= e(date('d/m/Y H:i', strtotime($post['created_at']))) ?></span>
                        </td>
                        <td>
                            <strong><?= e($post['author_name']) ?></strong>
                            <span><?= e(role_label($post['author_role'])) ?></span>
                        </td>
                        <td>
                            <span class="status-badge <?= e($post['status']) ?>"><?= e(human_label($post['status'])) ?></span>
                            <?php if ($post['is_featured']): ?><span>Destaque</span><?php endif; ?>
                        </td>
                        <td>
                            <span><?= e((int) $post['likes_count']) ?> curtidas</span>
                            <span><?= e((int) $post['comments_count']) ?> comentarios</span>
                            <span><?= e((int) $post['saves_count']) ?> salvos</span>
                        </td>
                        <td class="actions-cell">
                            <?php foreach ([['aprovar', 'Aprovar'], ['recusar', 'Recusar'], ['arquivar', 'Arquivar'], ['destacar', 'Destacar']] as $action): ?>
                                <form action="<?= e(url('/admin/comunidade/' . $post['id'] . '/' . $action[0])) ?>" method="post">
                                    <?= csrf_field() ?>
                                    <?php if ($action[0] === 'recusar'): ?>
                                        <input type="text" name="reason" placeholder="Motivo">
                                    <?php endif; ?>
                                    <button class="button small <?= in_array($action[0], ['recusar', 'arquivar'], true) ? 'ghost' : '' ?>" type="submit"><?= e($action[1]) ?></button>
                                </form>
                            <?php endforeach; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
