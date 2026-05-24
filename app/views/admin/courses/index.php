<?php defined('BASE_PATH') || exit('Acesso direto nao permitido.'); ?>

<section class="dashboard-shell">
    <div class="admin-toolbar">
        <div class="dashboard-heading">
            <span class="eyebrow">Administração</span>
            <h1>Cursos</h1>
            <p>Gerencie cursos, módulos, aulas e materiais da TME.</p>
        </div>
        <a class="button large" href="<?= e(url('/admin/cursos/novo')) ?>">Novo curso</a>
    </div>

    <form class="filter-form" action="<?= e(url('/admin/cursos')) ?>" method="get">
        <label>
            Status
            <select name="status">
                <option value="">Todos</option>
                <?php foreach (['rascunho' => 'Rascunho', 'publicado' => 'Publicado', 'arquivado' => 'Arquivado'] as $value => $label): ?>
                    <option value="<?= e($value) ?>" <?= ($filters['status'] ?? '') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>
            Categoria
            <select name="category">
                <option value="">Todas</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= e($category) ?>" <?= ($filters['category'] ?? '') === $category ? 'selected' : '' ?>><?= e($category) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>
            Professor
            <select name="teacher_id">
                <option value="">Todos</option>
                <?php foreach ($teachers as $teacher): ?>
                    <option value="<?= e($teacher['id']) ?>" <?= (string) ($filters['teacher_id'] ?? '') === (string) $teacher['id'] ? 'selected' : '' ?>><?= e($teacher['full_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <button class="button" type="submit">Filtrar</button>
        <a class="button ghost" href="<?= e(url('/admin/cursos')) ?>">Limpar</a>
    </form>

    <?php if (empty($courses)): ?>
        <div class="empty-state">
            <h2>Nenhum curso encontrado</h2>
            <p>Crie o primeiro curso para começar a montar a estrutura acadêmica.</p>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Curso</th>
                        <th>Categoria</th>
                        <th>Professor</th>
                        <th>Estrutura</th>
                        <th>Preço</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($courses as $course): ?>
                        <tr>
                            <td>
                                <strong><?= e($course['title']) ?></strong>
                                <span><?= e($course['level']) ?> - <?= e((int) $course['workload_hours']) ?>h - <?= ($course['access_level'] ?? 'gratuito') === 'premium' ? 'Premium' : 'Gratuito' ?></span>
                                <span>Criado em <?= e(date('d/m/Y', strtotime($course['created_at']))) ?></span>
                            </td>
                            <td><?= e($course['category']) ?></td>
                            <td><?= e($course['teacher_name'] ?? 'Sem professor') ?></td>
                            <td>
                                <span><?= e($course['modules_count']) ?> módulos</span>
                                <span><?= e($course['lessons_count']) ?> aulas</span>
                            </td>
                            <td>R$ <?= e(number_format((float) $course['price'], 2, ',', '.')) ?></td>
                            <td><span class="status-badge <?= e($course['status']) ?>"><?= e($course['status']) ?></span></td>
                            <td class="actions-cell">
                                <a class="button small" href="<?= e(url('/admin/cursos/' . $course['id'])) ?>">Ver</a>
                                <a class="button ghost small" href="<?= e(url('/admin/cursos/' . $course['id'] . '/editar')) ?>">Editar</a>
                                <?php if ($course['status'] !== 'arquivado'): ?>
                                    <form action="<?= e(url('/admin/cursos/' . $course['id'] . '/desativar')) ?>" method="post" data-confirm="Arquivar este curso?">
                                        <?= csrf_field() ?>
                                        <button class="button ghost small" type="submit">Arquivar</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
