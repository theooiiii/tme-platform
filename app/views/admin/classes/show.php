<?php defined('BASE_PATH') || exit('Acesso direto não permitido.'); ?>

<section class="dashboard-shell">
    <div class="admin-toolbar">
        <div class="dashboard-heading">
            <span class="eyebrow">Turma</span>
            <h1><?= e($class['name']) ?></h1>
            <p><?= e($class['description'] ?: 'Gerencie alunos, professores e disciplinas vinculadas.') ?></p>
        </div>
        <a class="button ghost large" href="<?= e(url('/admin/turmas')) ?>">Voltar</a>
    </div>

    <div class="class-link-grid">
        <form class="admin-form form" action="<?= e(url('/admin/turmas/' . $class['id'] . '/alunos')) ?>" method="post">
            <?= csrf_field() ?>
            <label>Aluno
                <select name="user_id"><?php foreach ($availableStudents as $student): ?><option value="<?= e($student['id']) ?>"><?= e($student['full_name']) ?></option><?php endforeach; ?></select>
            </label>
            <button class="button" type="submit">Vincular aluno</button>
        </form>
        <form class="admin-form form" action="<?= e(url('/admin/turmas/' . $class['id'] . '/professores')) ?>" method="post">
            <?= csrf_field() ?>
            <label>Professor
                <select name="user_id"><?php foreach ($availableTeachers as $teacher): ?><option value="<?= e($teacher['id']) ?>"><?= e($teacher['full_name']) ?></option><?php endforeach; ?></select>
            </label>
            <button class="button" type="submit">Vincular professor</button>
        </form>
        <form class="admin-form form" action="<?= e(url('/admin/turmas/' . $class['id'] . '/disciplinas')) ?>" method="post">
            <?= csrf_field() ?>
            <label>Disciplina
                <select name="subject_id"><?php foreach ($availableSubjects as $subject): ?><option value="<?= e($subject['id']) ?>"><?= e($subject['name']) ?></option><?php endforeach; ?></select>
            </label>
            <label>Professor responsavel
                <select name="teacher_id"><option value="">A definir</option><?php foreach ($availableTeachers as $teacher): ?><option value="<?= e($teacher['id']) ?>"><?= e($teacher['full_name']) ?></option><?php endforeach; ?></select>
            </label>
            <button class="button" type="submit">Vincular disciplina</button>
        </form>
    </div>

    <div class="detail-columns">
        <section class="profile-panel">
            <h2>Alunos</h2>
            <?php foreach ($students as $student): ?><p><?= e($student['full_name']) ?> <span class="muted"><?= e($student['email']) ?></span></p><?php endforeach; ?>
        </section>
        <section class="profile-panel">
            <h2>Professores</h2>
            <?php foreach ($teachers as $teacher): ?><p><?= e($teacher['full_name']) ?> <span class="muted"><?= e($teacher['email']) ?></span></p><?php endforeach; ?>
        </section>
        <section class="profile-panel">
            <h2>Disciplinas</h2>
            <?php foreach ($subjects as $subject): ?><p><strong><?= e($subject['name']) ?></strong><br><span class="muted"><?= e($subject['teacher_name'] ?: 'Professor a definir') ?></span></p><?php endforeach; ?>
        </section>
    </div>
</section>
