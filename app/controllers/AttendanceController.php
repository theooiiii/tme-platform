<?php

defined('BASE_PATH') || exit('Acesso direto nao permitido.');

class AttendanceController extends Controller
{
    private Attendance $attendance;
    private ActionLog $logs;

    public function __construct()
    {
        $this->attendance = new Attendance();
        $this->logs = new ActionLog();
    }

    public function index(): void
    {
        $user = current_user();
        $classes = $this->attendance->classesForManager($user);
        $classId = (int) ($_GET['class_id'] ?? ($classes[0]['id'] ?? 0));

        if ($classId && ! $this->attendance->canManageClass($classId, $user)) {
            flash('error', 'Turma indisponivel para seu perfil.');
            $classId = 0;
        }

        $subjects = $classId ? $this->attendance->subjectsForClass($classId, $user) : [];
        $subjectId = (int) ($_GET['subject_id'] ?? ($subjects[0]['id'] ?? 0));
        $date = $this->normalizeDate($_GET['date'] ?? date('Y-m-d'));
        $students = ($classId && $subjectId) ? $this->attendance->studentsForClass($classId) : [];
        $records = ($classId && $subjectId) ? $this->attendance->recordsForSession($classId, $subjectId, $date) : [];

        $this->view('attendance/index', [
            'title' => 'Frequencia',
            'classes' => $classes,
            'subjects' => $subjects,
            'students' => $students,
            'records' => $records,
            'classId' => $classId,
            'subjectId' => $subjectId,
            'date' => $date,
        ]);
    }

    public function store(): void
    {
        $this->guardCsrf('/frequencia');
        $user = current_user();
        $classId = (int) ($_POST['class_id'] ?? 0);
        $subjectId = (int) ($_POST['subject_id'] ?? 0);
        $date = $this->normalizeDate($_POST['date'] ?? date('Y-m-d'));

        if (! $classId || ! $subjectId || ! $this->attendance->canManageClass($classId, $user)) {
            flash('error', 'Selecione uma turma e disciplina validas.');
            $this->redirect('/frequencia');
        }

        $students = $this->attendance->studentsForClass($classId);
        $statuses = $_POST['attendance'] ?? [];
        $notes = $_POST['notes'] ?? [];
        $entries = [];

        foreach ($students as $student) {
            $studentId = (int) $student['id'];
            $entries[] = [
                'student_id' => $studentId,
                'status' => $statuses[$studentId] ?? 'presente',
                'note' => $notes[$studentId] ?? '',
            ];
        }

        if (! $entries) {
            flash('error', 'A turma selecionada ainda nao possui alunos vinculados.');
            $this->redirect('/frequencia?class_id=' . $classId . '&subject_id=' . $subjectId . '&date=' . $date);
        }

        $this->attendance->saveBatch($classId, $subjectId, $date, $entries, (int) $user['id']);
        $this->logs->record((int) $user['id'], 'attendance.recorded', [
            'class_id' => $classId,
            'subject_id' => $subjectId,
            'date' => $date,
            'students' => count($entries),
        ]);

        flash('success', 'Chamada registrada.');
        $this->redirect('/frequencia?class_id=' . $classId . '&subject_id=' . $subjectId . '&date=' . $date);
    }

    public function myAttendance(): void
    {
        $user = current_user();
        $filters = [
            'class_id' => (int) ($_GET['class_id'] ?? 0),
            'date_from' => $this->optionalDate($_GET['date_from'] ?? ''),
            'date_to' => $this->optionalDate($_GET['date_to'] ?? ''),
        ];

        $this->view('attendance/my', [
            'title' => 'Minha frequencia',
            'classes' => $this->attendance->classesForStudent((int) $user['id']),
            'filters' => $filters,
            'records' => $this->attendance->historyForStudent((int) $user['id'], $filters),
            'summary' => $this->attendance->report([
                'student_id' => (int) $user['id'],
                'class_id' => $filters['class_id'] ?: null,
                'date_from' => $filters['date_from'],
                'date_to' => $filters['date_to'],
            ]),
        ]);
    }

    public function report(): void
    {
        $user = current_user();
        $classes = $this->attendance->classesForManager($user);
        $filters = [
            'class_id' => (int) ($_GET['class_id'] ?? 0),
            'subject_id' => (int) ($_GET['subject_id'] ?? 0),
            'student_id' => (int) ($_GET['student_id'] ?? 0),
            'date_from' => $this->optionalDate($_GET['date_from'] ?? ''),
            'date_to' => $this->optionalDate($_GET['date_to'] ?? ''),
        ];

        if ($user['role_slug'] === 'professor' && ! $filters['class_id'] && ! empty($classes)) {
            $filters['class_id'] = (int) $classes[0]['id'];
        }

        if ($filters['class_id'] && ! $this->attendance->canManageClass($filters['class_id'], $user)) {
            flash('error', 'Turma indisponivel para relatorio.');
            $this->redirect('/frequencia/relatorio');
        }

        $subjects = $filters['class_id']
            ? $this->attendance->subjectsForClass($filters['class_id'], $user)
            : [];

        $this->view('attendance/report', [
            'title' => 'Relatorio de frequencia',
            'classes' => $classes,
            'subjects' => $subjects,
            'students' => $this->attendance->studentsWithAttendance(),
            'filters' => $filters,
            'rows' => $this->attendance->report($filters),
        ]);
    }

    private function guardCsrf(string $fallback): void
    {
        if (! verify_csrf_token($_POST['_csrf'] ?? null)) {
            flash('error', 'Sua sessao expirou. Tente novamente.');
            $this->redirect($fallback);
        }
    }

    private function normalizeDate(string $date): string
    {
        $timestamp = strtotime($date);

        return $timestamp ? date('Y-m-d', $timestamp) : date('Y-m-d');
    }

    private function optionalDate(string $date): ?string
    {
        $date = trim($date);

        if ($date === '') {
            return null;
        }

        $timestamp = strtotime($date);

        return $timestamp ? date('Y-m-d', $timestamp) : null;
    }
}
