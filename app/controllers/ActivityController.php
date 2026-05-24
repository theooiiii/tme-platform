<?php

defined('BASE_PATH') || exit('Acesso direto nao permitido.');

class ActivityController extends Controller
{
    private Activity $activities;
    private Submission $submissions;
    private Course $courses;
    private ActionLog $logs;
    private GamificationService $gamification;
    private NotificationService $notifications;

    public function __construct()
    {
        $this->activities = new Activity();
        $this->submissions = new Submission();
        $this->courses = new Course();
        $this->logs = new ActionLog();
        $this->gamification = new GamificationService();
        $this->notifications = new NotificationService();
    }

    public function myActivities(): void
    {
        $user = current_user();

        $this->view('activities/my', [
            'title' => 'Minhas atividades',
            'activities' => $this->activities->forStudent((int) $user['id']),
            'user' => $user,
        ]);
    }

    public function showForStudent(string $id): void
    {
        $user = current_user();
        $activity = $this->activities->findForStudent((int) $id, (int) $user['id']);

        if (! $activity) {
            flash('error', 'Atividade nao encontrada para seus cursos.');
            $this->redirect('/atividades');
        }

        $this->view('activities/show', [
            'title' => $activity['title'],
            'activity' => $activity,
            'submission' => $this->submissions->findByActivityAndStudent((int) $activity['id'], (int) $user['id']),
        ]);
    }

    public function submit(string $id): void
    {
        $this->guardCsrf('/atividades/' . $id);

        $user = current_user();
        $activity = $this->activities->findForStudent((int) $id, (int) $user['id']);

        if (! $activity) {
            flash('error', 'Atividade nao encontrada para seus cursos.');
            $this->redirect('/atividades');
        }

        $isLate = $this->isLate($activity);

        if ($isLate && ! (bool) $activity['allow_late']) {
            flash('error', 'O prazo desta atividade encerrou e envios atrasados nao estao habilitados.');
            $this->redirect('/atividades/' . $activity['id']);
        }

        $content = trim($_POST['content'] ?? '');
        $filePath = $this->uploadFile('submission_file', 'activity-submissions', $this->documentMimes());

        if ($content === '' && ! $filePath) {
            flash('error', 'Envie uma resposta textual ou um arquivo.');
            $this->redirect('/atividades/' . $activity['id']);
        }

        try {
            $submissionId = $this->submissions->submit(
                (int) $activity['id'],
                (int) $user['id'],
                $content,
                $filePath,
                $isLate ? 'atrasada' : 'enviada'
            );

            $this->logs->record((int) $user['id'], 'activity.submitted', [
                'activity_id' => (int) $activity['id'],
                'submission_id' => $submissionId,
                'late' => $isLate,
            ]);
            try {
                $this->gamification->activitySubmitted((int) $user['id'], $submissionId, (int) $activity['id']);
            } catch (Throwable $eventException) {
                $this->logs->record((int) $user['id'], 'gamification.error', ['message' => $eventException->getMessage()], 'warning');
            }

            flash('success', $isLate ? 'Entrega atrasada registrada.' : 'Entrega enviada com sucesso.');
        } catch (Throwable $exception) {
            flash('error', $exception->getMessage());
        }

        $this->redirect('/atividades/' . $activity['id']);
    }

    public function gradebook(): void
    {
        $user = current_user();

        $this->view('activities/gradebook', [
            'title' => 'Boletim',
            'rows' => $this->activities->gradebookForStudent((int) $user['id']),
        ]);
    }

    public function adminIndex(): void
    {
        $filters = [
            'course_id' => trim($_GET['course_id'] ?? ''),
            'status' => trim($_GET['status'] ?? ''),
            'type' => trim($_GET['type'] ?? ''),
        ];
        $user = current_user();

        $this->view('admin/activities/index', [
            'title' => 'Atividades',
            'activities' => $this->activities->allForManager($filters, $user),
            'filters' => $filters,
            'courses' => $this->activities->coursesForSelect(),
        ]);
    }

    public function create(): void
    {
        $this->view('admin/activities/form', $this->formData('Nova atividade', null, url('/admin/atividades')));
    }

    public function store(): void
    {
        $this->guardCsrf('/admin/atividades/nova');

        $data = $this->activityPayload();
        $errors = $this->validateActivity($data);

        if ($errors) {
            $_SESSION['_old'] = $data;
            flash('errors', $errors);
            $this->redirect('/admin/atividades/nova');
        }

        $data['attachment_path'] = $this->uploadFile('attachment', 'activity-attachments', $this->documentMimes());
        $activityId = $this->activities->create($data);
        unset($_SESSION['_old']);

        $this->logs->record((int) current_user()['id'], 'activity.created', [
            'activity_id' => $activityId,
            'course_id' => $data['course_id'],
        ]);

        flash('success', 'Atividade criada.');
        $this->redirect('/admin/atividades/' . $activityId);
    }

    public function adminShow(string $id): void
    {
        $activity = $this->findManageableActivity((int) $id);

        $this->view('admin/activities/show', [
            'title' => $activity['title'],
            'activity' => $activity,
            'submissions' => $this->activities->submissions((int) $activity['id']),
        ]);
    }

    public function edit(string $id): void
    {
        $activity = $this->findManageableActivity((int) $id);

        $this->view('admin/activities/form', $this->formData('Editar atividade', $activity, url('/admin/atividades/' . $activity['id'] . '/atualizar')));
    }

    public function update(string $id): void
    {
        $activity = $this->findManageableActivity((int) $id);
        $this->guardCsrf('/admin/atividades/' . $activity['id'] . '/editar');

        $data = $this->activityPayload();
        $errors = $this->validateActivity($data);

        if ($errors) {
            $_SESSION['_old'] = $data;
            flash('errors', $errors);
            $this->redirect('/admin/atividades/' . $activity['id'] . '/editar');
        }

        $data['attachment_path'] = $this->uploadFile('attachment', 'activity-attachments', $this->documentMimes());
        $this->activities->update((int) $activity['id'], $data);
        unset($_SESSION['_old']);

        $this->logs->record((int) current_user()['id'], 'activity.updated', ['activity_id' => (int) $activity['id']]);

        flash('success', 'Atividade atualizada.');
        $this->redirect('/admin/atividades/' . $activity['id']);
    }

    public function archive(string $id): void
    {
        $activity = $this->findManageableActivity((int) $id);
        $this->guardCsrf('/admin/atividades/' . $activity['id']);

        $this->activities->archive((int) $activity['id']);
        $this->logs->record((int) current_user()['id'], 'activity.closed', ['activity_id' => (int) $activity['id']], 'warning');

        flash('success', 'Atividade encerrada.');
        $this->redirect('/admin/atividades');
    }

    public function gradeSubmission(string $submissionId): void
    {
        $submission = $this->submissions->find((int) $submissionId);

        if (! $submission) {
            flash('error', 'Entrega nao encontrada.');
            $this->redirect('/admin/atividades');
        }

        $activity = $this->findManageableActivity((int) $submission['activity_id']);
        $this->guardCsrf('/admin/atividades/' . $activity['id']);

        $score = max(0, (float) str_replace(',', '.', (string) ($_POST['score'] ?? 0)));
        $feedback = trim($_POST['feedback'] ?? '');
        $status = trim($_POST['status'] ?? 'corrigida');

        if ($score > (float) $activity['max_score']) {
            flash('error', 'A nota nao pode ultrapassar a pontuacao maxima.');
            $this->redirect('/admin/atividades/' . $activity['id']);
        }

        $this->submissions->grade((int) $submission['id'], (int) current_user()['id'], $score, $feedback, $status);
        $this->logs->record((int) current_user()['id'], 'activity.graded', [
            'activity_id' => (int) $activity['id'],
            'submission_id' => (int) $submission['id'],
            'score' => $score,
        ]);
        $this->notifications->activityGraded((int) $submission['student_id'], (int) $activity['id'], (string) $activity['title']);
        try {
            $this->gamification->activityGraded((int) $submission['student_id'], (int) $submission['id'], $score, (float) $activity['max_score']);
        } catch (Throwable $eventException) {
            $this->logs->record((int) current_user()['id'], 'gamification.error', ['message' => $eventException->getMessage()], 'warning');
        }

        flash('success', 'Entrega corrigida.');
        $this->redirect('/admin/atividades/' . $activity['id']);
    }

    private function formData(string $title, ?array $activity, string $action): array
    {
        return [
            'title' => $title,
            'activity' => $activity,
            'action' => $action,
            'courses' => $this->activities->coursesForSelect(),
            'modules' => $this->activities->modulesForSelect(),
            'lessons' => $this->activities->lessonsForSelect(),
            'teachers' => $this->courses->teachers(),
        ];
    }

    private function activityPayload(): array
    {
        $user = current_user();
        $teacherId = (int) ($_POST['teacher_id'] ?? 0);

        if ($user['role_slug'] === 'professor') {
            $teacherId = (int) $user['id'];
        } elseif (! $teacherId) {
            $teacherId = (int) $user['id'];
        }

        return [
            'course_id' => (int) ($_POST['course_id'] ?? 0),
            'module_id' => (int) ($_POST['module_id'] ?? 0),
            'lesson_id' => (int) ($_POST['lesson_id'] ?? 0),
            'class_id' => (int) ($_POST['class_id'] ?? 0),
            'subject_id' => (int) ($_POST['subject_id'] ?? 0),
            'teacher_id' => $teacherId,
            'title' => trim($_POST['title'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'instructions' => trim($_POST['instructions'] ?? ''),
            'activity_type' => trim($_POST['activity_type'] ?? 'texto'),
            'due_at' => $this->normalizeDateTime($_POST['due_at'] ?? ''),
            'max_score' => max(0, (float) str_replace(',', '.', (string) ($_POST['max_score'] ?? 10))),
            'allow_late' => isset($_POST['allow_late']),
            'attachment_path' => null,
            'status' => trim($_POST['status'] ?? 'rascunho'),
        ];
    }

    private function validateActivity(array $data): array
    {
        $errors = [];

        if (strlen($data['title']) < 3) {
            $errors[] = 'Informe um titulo com pelo menos 3 caracteres.';
        }

        if (! $data['course_id']) {
            $errors[] = 'Vincule a atividade a um curso.';
        }

        if (! in_array($data['activity_type'], ['texto', 'arquivo', 'quiz', 'tarefa_pratica', 'projeto'], true)) {
            $errors[] = 'Selecione um tipo de atividade valido.';
        }

        if (! in_array($data['status'], ['rascunho', 'publicada', 'encerrada'], true)) {
            $errors[] = 'Selecione um status valido.';
        }

        if ($data['max_score'] <= 0) {
            $errors[] = 'A pontuacao maxima deve ser maior que zero.';
        }

        return $errors;
    }

    private function findManageableActivity(int $id): array
    {
        $activity = $this->activities->find($id);
        $user = current_user();

        if (! $activity) {
            flash('error', 'Atividade nao encontrada.');
            $this->redirect('/admin/atividades');
        }

        if ($user['role_slug'] === 'professor' && (int) $activity['teacher_id'] !== (int) $user['id']) {
            http_response_code(403);
            $this->view('errors/403', ['title' => 'Acesso restrito']);
            exit;
        }

        return $activity;
    }

    private function isLate(array $activity): bool
    {
        return ! empty($activity['due_at']) && strtotime($activity['due_at']) < time();
    }

    private function normalizeDateTime(string $value): ?string
    {
        $value = trim($value);

        if ($value === '') {
            return null;
        }

        $timestamp = strtotime($value);

        return $timestamp ? date('Y-m-d H:i:s', $timestamp) : null;
    }

    private function guardCsrf(string $fallback): void
    {
        if (! verify_csrf_token($_POST['_csrf'] ?? null)) {
            flash('error', 'Sua sessao expirou. Tente novamente.');
            $this->redirect($fallback);
        }
    }

    private function uploadFile(string $field, string $directory, array $allowedMimes): ?string
    {
        if (empty($_FILES[$field]) || ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if (($_FILES[$field]['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            flash('error', 'Nao foi possivel receber o arquivo enviado.');
            return null;
        }

        if (($_FILES[$field]['size'] ?? 0) > 12 * 1024 * 1024) {
            flash('error', 'O arquivo deve ter no maximo 12 MB.');
            return null;
        }

        $tmp = $_FILES[$field]['tmp_name'];
        $mime = mime_content_type($tmp) ?: 'application/octet-stream';

        if (! in_array($mime, $allowedMimes, true)) {
            flash('error', 'Tipo de arquivo nao permitido.');
            return null;
        }

        $extension = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
        $safeName = bin2hex(random_bytes(12)) . ($extension ? '.' . $extension : '');
        $relative = 'uploads/' . trim($directory, '/') . '/' . $safeName;
        $targetDirectory = BASE_PATH . '/public/uploads/' . trim($directory, '/');

        if (! is_dir($targetDirectory)) {
            mkdir($targetDirectory, 0775, true);
        }

        if (! move_uploaded_file($tmp, BASE_PATH . '/public/' . $relative)) {
            flash('error', 'Nao foi possivel salvar o arquivo enviado.');
            return null;
        }

        return $relative;
    }

    private function documentMimes(): array
    {
        return [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'image/webp',
            'text/plain',
            'application/zip',
            'application/x-zip-compressed',
            'application/octet-stream',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        ];
    }
}
