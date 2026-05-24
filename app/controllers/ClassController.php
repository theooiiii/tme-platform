<?php

defined('BASE_PATH') || exit('Acesso direto nao permitido.');

class ClassController extends Controller
{
    private SchoolClass $classes;

    public function __construct()
    {
        $this->classes = new SchoolClass();
    }

    public function myClasses(): void
    {
        $user = current_user();

        $this->view('classes/index', [
            'title' => 'Turmas',
            'classes' => $this->classes->linkedForUser((int) $user['id'], $user['role_slug']),
        ]);
    }

    public function show(string $id): void
    {
        $class = $this->classes->find((int) $id);

        if (! $class) {
            flash('error', 'Turma nao encontrada.');
            $this->redirect('/turmas');
        }

        $this->view('classes/show', [
            'title' => $class['name'],
            'class' => $class,
            'students' => $this->classes->students((int) $class['id']),
            'teachers' => $this->classes->teachers((int) $class['id']),
            'subjects' => $this->classes->subjects((int) $class['id']),
        ]);
    }
}
