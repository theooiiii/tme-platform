<?php

defined('BASE_PATH') || exit('Acesso direto não permitido.');

class AdminClassController extends Controller
{
    private SchoolClass $classes;
    private Subject $subjects;
    private ActionLog $logs;

    public function __construct()
    {
        $this->classes = new SchoolClass();
        $this->subjects = new Subject();
        $this->logs = new ActionLog();
    }

    public function index(): void
    {
        $this->view('admin/classes/index', [
            'title' => 'Turmas',
            'classes' => $this->classes->all(),
            'subjects' => $this->subjects->all(),
        ]);
    }

    public function createClass(): void
    {
        $this->view('admin/classes/class_form', $this->classFormData('Nova turma', null, url('/admin/turmas')));
    }

    public function storeClass(): void
    {
        $this->guardCsrf('/admin/turmas/nova');
        $data = $this->classPayload();

        if (strlen($data['name']) < 3) {
            flash('error', 'Informe o nome da turma.');
            $this->redirect('/admin/turmas/nova');
        }

        $id = $this->classes->create($data);
        $this->logs->record((int) current_user()['id'], 'class.created', ['class_id' => $id]);

        flash('success', 'Turma criada.');
        $this->redirect('/admin/turmas/' . $id);
    }

    public function showClass(string $id): void
    {
        $class = $this->findClass((int) $id);

        $this->view('admin/classes/show', [
            'title' => $class['name'],
            'class' => $class,
            'students' => $this->classes->students((int) $class['id']),
            'teachers' => $this->classes->teachers((int) $class['id']),
            'subjects' => $this->classes->subjects((int) $class['id']),
            'availableStudents' => $this->classes->approvedStudents(),
            'availableTeachers' => $this->classes->approvedTeachers(),
            'availableSubjects' => $this->subjects->all(),
        ]);
    }

    public function editClass(string $id): void
    {
        $class = $this->findClass((int) $id);

        $this->view('admin/classes/class_form', $this->classFormData('Editar turma', $class, url('/admin/turmas/' . $class['id'] . '/atualizar')));
    }

    public function updateClass(string $id): void
    {
        $class = $this->findClass((int) $id);
        $this->guardCsrf('/admin/turmas/' . $class['id'] . '/editar');
        $data = $this->classPayload();

        if (strlen($data['name']) < 3) {
            flash('error', 'Informe o nome da turma.');
            $this->redirect('/admin/turmas/' . $class['id'] . '/editar');
        }

        $this->classes->update((int) $class['id'], $data);
        $this->logs->record((int) current_user()['id'], 'class.updated', ['class_id' => (int) $class['id']]);

        flash('success', 'Turma atualizada.');
        $this->redirect('/admin/turmas/' . $class['id']);
    }

    public function archiveClass(string $id): void
    {
        $class = $this->findClass((int) $id);
        $this->guardCsrf('/admin/turmas/' . $class['id']);
        $this->classes->archive((int) $class['id']);
        $this->logs->record((int) current_user()['id'], 'class.archived', ['class_id' => (int) $class['id']], 'warning');

        flash('success', 'Turma arquivada.');
        $this->redirect('/admin/turmas');
    }

    public function createSubject(): void
    {
        $this->view('admin/classes/subject_form', [
            'title' => 'Nova disciplina',
            'subject' => null,
            'action' => url('/admin/disciplinas'),
        ]);
    }

    public function storeSubject(): void
    {
        $this->guardCsrf('/admin/disciplinas/nova');
        $data = $this->subjectPayload();

        if (strlen($data['name']) < 3) {
            flash('error', 'Informe o nome da disciplina.');
            $this->redirect('/admin/disciplinas/nova');
        }

        $id = $this->subjects->create($data);
        $this->logs->record((int) current_user()['id'], 'subject.created', ['subject_id' => $id]);

        flash('success', 'Disciplina criada.');
        $this->redirect('/admin/turmas');
    }

    public function editSubject(string $id): void
    {
        $subject = $this->findSubject((int) $id);

        $this->view('admin/classes/subject_form', [
            'title' => 'Editar disciplina',
            'subject' => $subject,
            'action' => url('/admin/disciplinas/' . $subject['id'] . '/atualizar'),
        ]);
    }

    public function updateSubject(string $id): void
    {
        $subject = $this->findSubject((int) $id);
        $this->guardCsrf('/admin/disciplinas/' . $subject['id'] . '/editar');
        $data = $this->subjectPayload();

        if (strlen($data['name']) < 3) {
            flash('error', 'Informe o nome da disciplina.');
            $this->redirect('/admin/disciplinas/' . $subject['id'] . '/editar');
        }

        $this->subjects->update((int) $subject['id'], $data);
        $this->logs->record((int) current_user()['id'], 'subject.updated', ['subject_id' => (int) $subject['id']]);

        flash('success', 'Disciplina atualizada.');
        $this->redirect('/admin/turmas');
    }

    public function archiveSubject(string $id): void
    {
        $subject = $this->findSubject((int) $id);
        $this->guardCsrf('/admin/turmas');
        $this->subjects->archive((int) $subject['id']);
        $this->logs->record((int) current_user()['id'], 'subject.archived', ['subject_id' => (int) $subject['id']], 'warning');

        flash('success', 'Disciplina arquivada.');
        $this->redirect('/admin/turmas');
    }

    public function linkStudent(string $id): void
    {
        $this->guardCsrf('/admin/turmas/' . $id);
        $userId = (int) ($_POST['user_id'] ?? 0);
        $this->classes->linkStudent((int) $id, $userId);
        $this->logs->record((int) current_user()['id'], 'class.student_linked', ['class_id' => (int) $id, 'user_id' => $userId]);

        flash('success', 'Aluno vinculado.');
        $this->redirect('/admin/turmas/' . $id);
    }

    public function linkTeacher(string $id): void
    {
        $this->guardCsrf('/admin/turmas/' . $id);
        $userId = (int) ($_POST['user_id'] ?? 0);
        $this->classes->linkTeacher((int) $id, $userId);
        $this->logs->record((int) current_user()['id'], 'class.teacher_linked', ['class_id' => (int) $id, 'user_id' => $userId]);

        flash('success', 'Professor vinculado.');
        $this->redirect('/admin/turmas/' . $id);
    }

    public function linkSubject(string $id): void
    {
        $this->guardCsrf('/admin/turmas/' . $id);
        $subjectId = (int) ($_POST['subject_id'] ?? 0);
        $teacherId = (int) ($_POST['teacher_id'] ?? 0);
        $this->classes->linkSubject((int) $id, $subjectId, $teacherId ?: null);
        $this->logs->record((int) current_user()['id'], 'class.subject_linked', [
            'class_id' => (int) $id,
            'subject_id' => $subjectId,
            'teacher_id' => $teacherId ?: null,
        ]);

        flash('success', 'Disciplina vinculada.');
        $this->redirect('/admin/turmas/' . $id);
    }

    private function classFormData(string $title, ?array $class, string $action): array
    {
        return [
            'title' => $title,
            'class' => $class,
            'action' => $action,
            'institutions' => $this->classes->institutions(),
        ];
    }

    private function classPayload(): array
    {
        return [
            'institution_id' => (int) ($_POST['institution_id'] ?? 0),
            'name' => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'period' => trim($_POST['period'] ?? ''),
            'status' => trim($_POST['status'] ?? 'ativa'),
        ];
    }

    private function subjectPayload(): array
    {
        return [
            'name' => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'area' => trim($_POST['area'] ?? ''),
            'workload_hours' => max(0, (int) ($_POST['workload_hours'] ?? 0)),
            'status' => trim($_POST['status'] ?? 'ativa'),
        ];
    }

    private function findClass(int $id): array
    {
        $class = $this->classes->find($id);

        if (! $class) {
            flash('error', 'Turma não encontrada.');
            $this->redirect('/admin/turmas');
        }

        return $class;
    }

    private function findSubject(int $id): array
    {
        $subject = $this->subjects->find($id);

        if (! $subject) {
            flash('error', 'Disciplina não encontrada.');
            $this->redirect('/admin/turmas');
        }

        return $subject;
    }

    private function guardCsrf(string $fallback): void
    {
        if (! verify_csrf_token($_POST['_csrf'] ?? null)) {
            flash('error', 'Sua sessao expirou. Tente novamente.');
            $this->redirect($fallback);
        }
    }
}
