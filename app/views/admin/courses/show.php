<?php defined('BASE_PATH') || exit('Acesso direto nao permitido.'); ?>

<section class="dashboard-shell">
    <div class="admin-toolbar">
        <div class="dashboard-heading">
            <span class="eyebrow">Curso</span>
            <h1><?= e($course['title']) ?></h1>
            <p><?= e($course['description'] ?: 'Sem descrição cadastrada.') ?></p>
        </div>
        <div class="actions-row">
            <a class="button" href="<?= e(url('/admin/cursos/' . $course['id'] . '/editar')) ?>">Editar curso</a>
            <a class="button ghost" href="<?= e(url('/admin/cursos')) ?>">Voltar</a>
        </div>
    </div>

    <div class="detail-grid">
        <?php if (! empty($course['image_path'])): ?>
            <img class="course-cover" src="<?= e(url('/' . $course['image_path'])) ?>" alt="Imagem do curso <?= e($course['title']) ?>">
        <?php else: ?>
            <div class="course-cover placeholder">TME</div>
        <?php endif; ?>

        <div class="detail-panel">
            <span class="status-badge <?= e($course['status']) ?>"><?= e($course['status']) ?></span>
            <dl class="meta-list">
                <div><dt>Categoria</dt><dd><?= e($course['category']) ?></dd></div>
                <div><dt>Nível</dt><dd><?= e($course['level']) ?></dd></div>
                <div><dt>Carga horária</dt><dd><?= e((int) $course['workload_hours']) ?>h</dd></div>
                <div><dt>Preço</dt><dd>R$ <?= e(number_format((float) $course['price'], 2, ',', '.')) ?></dd></div>
                <div><dt>Professor</dt><dd><?= e($course['teacher_name'] ?? 'Sem professor') ?></dd></div>
                <div><dt>Criado em</dt><dd><?= e(date('d/m/Y H:i', strtotime($course['created_at']))) ?></dd></div>
            </dl>
        </div>
    </div>

    <div class="admin-toolbar section-toolbar">
        <div>
            <span class="eyebrow">Currículo</span>
            <h2>Módulos, aulas e materiais</h2>
        </div>
        <div class="actions-row">
            <a class="button" href="<?= e(url('/admin/cursos/' . $course['id'] . '/modulos/novo')) ?>">Novo módulo</a>
            <a class="button ghost" href="<?= e(url('/admin/cursos/' . $course['id'] . '/aulas/novo')) ?>">Nova aula</a>
        </div>
    </div>

    <div class="curriculum-list">
        <?php foreach ($structure['modules'] as $module): ?>
            <article class="curriculum-module">
                <header>
                    <div>
                        <strong><?= e($module['position']) ?>. <?= e($module['title']) ?></strong>
                        <p><?= e($module['description'] ?: 'Sem descrição.') ?></p>
                    </div>
                    <div class="inline-actions">
                        <a href="<?= e(url('/admin/cursos/' . $course['id'] . '/modulos/' . $module['id'] . '/editar')) ?>">Editar</a>
                        <form action="<?= e(url('/admin/cursos/' . $course['id'] . '/modulos/' . $module['id'] . '/excluir')) ?>" method="post" data-confirm="Excluir este módulo?">
                            <?= csrf_field() ?>
                            <button type="submit">Excluir</button>
                        </form>
                    </div>
                </header>

                <?php if (empty($module['lessons'])): ?>
                    <p class="muted">Nenhuma aula neste módulo.</p>
                <?php else: ?>
                    <?php foreach ($module['lessons'] as $lesson): ?>
                        <?php require BASE_PATH . '/app/views/admin/courses/partials/lesson_row.php'; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>

        <?php if (! empty($structure['unassigned_lessons'])): ?>
            <article class="curriculum-module">
                <header>
                    <div>
                        <strong>Aulas sem módulo</strong>
                        <p>Aulas vinculadas diretamente ao curso.</p>
                    </div>
                </header>
                <?php foreach ($structure['unassigned_lessons'] as $lesson): ?>
                    <?php require BASE_PATH . '/app/views/admin/courses/partials/lesson_row.php'; ?>
                <?php endforeach; ?>
            </article>
        <?php endif; ?>

        <?php if (empty($structure['modules']) && empty($structure['unassigned_lessons'])): ?>
            <div class="empty-state">
                <h2>Currículo vazio</h2>
                <p>Crie módulos e aulas para publicar uma experiência de aprendizagem organizada.</p>
            </div>
        <?php endif; ?>
    </div>
</section>
