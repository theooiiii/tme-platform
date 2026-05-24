<?php

defined('BASE_PATH') || exit('Acesso direto nao permitido.');

class ExamController extends Controller
{
    private Exam $exams;
    private ActionLog $logs;
    private NotificationService $notifications;

    public function __construct()
    {
        $this->exams = new Exam();
        $this->logs = new ActionLog();
        $this->notifications = new NotificationService();
    }

    public function adminIndex(): void
    {
        $filters = [
            'status' => trim($_GET['status'] ?? ''),
            'course_id' => (int) ($_GET['course_id'] ?? 0),
            'class_id' => (int) ($_GET['class_id'] ?? 0),
        ];

        $this->view('admin/exams/index', [
            'title' => 'Provas e simulados',
            'filters' => $filters,
            'exams' => $this->exams->allForManager($filters, current_user()),
            'courses' => $this->exams->coursesForSelect(),
            'classes' => $this->exams->classesForSelect(),
        ]);
    }

    public function create(): void
    {
        $this->view('admin/exams/form', [
            'title' => 'Nova prova',
            'action' => url('/admin/provas'),
            'exam' => null,
            'courses' => $this->exams->coursesForSelect(),
            'classes' => $this->exams->classesForSelect(),
            'subjects' => $this->exams->subjectsForSelect(),
        ]);
    }

    public function store(): void
    {
        $this->guardCsrf('/admin/provas/nova');

        $data = $this->examPayload();
        $errors = $this->validateExam($data);

        if ($errors) {
            $_SESSION['_old'] = $data;
            flash('errors', $errors);
            $this->redirect('/admin/provas/nova');
        }

        $examId = $this->exams->create($data);
        unset($_SESSION['_old']);

        $this->logs->record((int) current_user()['id'], 'exam.created', [
            'exam_id' => $examId,
            'status' => $data['status'],
        ]);
        if ($data['status'] === 'publicado') {
            foreach ($this->exams->targetUsersForExam($examId) as $targetUser) {
                $this->notifications->examReleased((int) $targetUser['id'], $examId, (string) $data['title']);
            }
        }

        flash('success', 'Prova criada. Agora adicione questoes.');
        $this->redirect('/admin/provas/' . $examId);
    }

    public function adminShow(string $id): void
    {
        $exam = $this->findManageableExam((int) $id);

        $this->view('admin/exams/show', [
            'title' => $exam['title'],
            'exam' => $exam,
            'questions' => $this->exams->questionsForExam((int) $exam['id']),
            'attempts' => $this->exams->attemptsForExam((int) $exam['id']),
            'subjects' => $this->exams->subjectsForSelect(),
            'ranking' => $this->exams->ranking((int) $exam['id']),
        ]);
    }

    public function storeQuestion(string $id): void
    {
        $exam = $this->findManageableExam((int) $id);
        $this->guardCsrf('/admin/provas/' . $exam['id']);

        $data = $this->questionPayload($exam);
        $errors = $this->validateQuestion($data);

        if ($errors) {
            flash('errors', $errors);
            $this->redirect('/admin/provas/' . $exam['id']);
        }

        $questionId = $this->exams->createQuestionForExam((int) $exam['id'], $data);
        $this->logs->record((int) current_user()['id'], 'exam.question_created', [
            'exam_id' => (int) $exam['id'],
            'question_id' => $questionId,
        ]);

        flash('success', 'Questao adicionada.');
        $this->redirect('/admin/provas/' . $exam['id']);
    }

    public function adminAttempt(string $attemptId): void
    {
        $attempt = $this->findManageableAttempt((int) $attemptId);

        $this->view('admin/exams/attempt', [
            'title' => 'Correcao de prova',
            'attempt' => $attempt,
            'answers' => $this->exams->answersForAttempt((int) $attempt['id']),
        ]);
    }

    public function gradeAttempt(string $attemptId): void
    {
        $attempt = $this->findManageableAttempt((int) $attemptId);
        $this->guardCsrf('/admin/provas/tentativas/' . $attempt['id']);

        $this->exams->gradeAttempt(
            (int) $attempt['id'],
            $_POST['scores'] ?? [],
            $_POST['feedbacks'] ?? [],
            (int) current_user()['id']
        );
        $this->logs->record((int) current_user()['id'], 'exam.attempt_graded', [
            'attempt_id' => (int) $attempt['id'],
            'exam_id' => (int) $attempt['exam_id'],
            'student_id' => (int) $attempt['student_id'],
        ]);

        flash('success', 'Tentativa corrigida.');
        $this->redirect('/admin/provas/' . $attempt['exam_id']);
    }

    public function index(): void
    {
        $user = current_user();

        $this->view('exams/index', [
            'title' => 'Provas',
            'availableExams' => $this->exams->availableForStudent((int) $user['id']),
            'attempts' => $this->exams->studentAttempts((int) $user['id']),
            'performance' => $this->exams->performanceBySubject((int) $user['id']),
        ]);
    }

    public function show(string $id): void
    {
        $user = current_user();
        $exam = $this->exams->findAvailableForStudent((int) $id, (int) $user['id']);

        if (! $exam) {
            flash('error', 'Prova nao encontrada ou indisponivel.');
            $this->redirect('/provas');
        }

        $this->view('exams/show', [
            'title' => $exam['title'],
            'exam' => $exam,
            'questions' => $this->exams->questionsForExam((int) $exam['id']),
            'ranking' => $exam['ranking_enabled'] ? $this->exams->ranking((int) $exam['id']) : [],
        ]);
    }

    public function start(string $id): void
    {
        $this->guardCsrf('/provas/' . $id);
        $user = current_user();

        try {
            $attemptId = $this->exams->startAttempt((int) $id, (int) $user['id']);
            $this->logs->record((int) $user['id'], 'exam.attempt_started', [
                'exam_id' => (int) $id,
                'attempt_id' => $attemptId,
            ]);

            $this->redirect('/provas/tentativas/' . $attemptId);
        } catch (Throwable $exception) {
            flash('error', $exception->getMessage());
            $this->redirect('/provas/' . $id);
        }
    }

    public function attempt(string $attemptId): void
    {
        $user = current_user();
        $attempt = $this->exams->findAttemptForStudent((int) $attemptId, (int) $user['id']);

        if (! $attempt) {
            flash('error', 'Tentativa nao encontrada.');
            $this->redirect('/provas');
        }

        if ($attempt['status'] !== 'em_andamento') {
            $this->redirect('/provas/tentativas/' . $attempt['id'] . '/resultado');
        }

        $this->view('exams/attempt', [
            'title' => $attempt['title'],
            'attempt' => $attempt,
            'questions' => $this->exams->questionsForAttempt((int) $attempt['id']),
            'remainingSeconds' => $this->exams->remainingSeconds($attempt),
            'examModel' => $this->exams,
        ]);
    }

    public function submit(string $attemptId): void
    {
        $this->guardCsrf('/provas/tentativas/' . $attemptId);
        $user = current_user();

        try {
            $result = $this->exams->submitAttempt((int) $attemptId, (int) $user['id'], $_POST['answers'] ?? []);
            $this->logs->record((int) $user['id'], 'exam.attempt_submitted', [
                'attempt_id' => (int) $attemptId,
                'status' => $result['status'],
                'objective_score' => $result['objective_score'],
            ]);

            flash('success', $result['has_discursive']
                ? 'Prova enviada. Questoes discursivas aguardam correcao.'
                : 'Prova enviada e corrigida automaticamente.');
        } catch (Throwable $exception) {
            flash('error', $exception->getMessage());
            $this->redirect('/provas/tentativas/' . $attemptId);
        }

        $this->redirect('/provas/tentativas/' . $attemptId . '/resultado');
    }

    public function result(string $attemptId): void
    {
        $user = current_user();
        $attempt = $this->exams->findAttemptForStudent((int) $attemptId, (int) $user['id']);

        if (! $attempt) {
            flash('error', 'Resultado nao encontrado.');
            $this->redirect('/provas');
        }

        $this->view('exams/result', [
            'title' => 'Resultado',
            'attempt' => $attempt,
            'answers' => $this->exams->answersForAttempt((int) $attempt['id']),
            'ranking' => $attempt['ranking_enabled'] ? $this->exams->ranking((int) $attempt['exam_id']) : [],
        ]);
    }

    private function examPayload(): array
    {
        return [
            'course_id' => (int) ($_POST['course_id'] ?? 0),
            'class_id' => (int) ($_POST['class_id'] ?? 0),
            'subject_id' => (int) ($_POST['subject_id'] ?? 0),
            'creator_id' => (int) current_user()['id'],
            'title' => trim($_POST['title'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'time_limit_minutes' => max(0, (int) ($_POST['time_limit_minutes'] ?? 0)),
            'starts_at' => $this->normalizeDateTime($_POST['starts_at'] ?? ''),
            'ends_at' => $this->normalizeDateTime($_POST['ends_at'] ?? ''),
            'attempts_allowed' => max(1, (int) ($_POST['attempts_allowed'] ?? 1)),
            'auto_correction_enabled' => isset($_POST['auto_correction_enabled']),
            'ranking_enabled' => isset($_POST['ranking_enabled']),
            'status' => trim($_POST['status'] ?? 'rascunho'),
        ];
    }

    private function questionPayload(array $exam): array
    {
        $alternatives = array_values(array_filter(array_map(
            static fn (string $line): string => trim($line),
            preg_split('/\R/', (string) ($_POST['alternatives'] ?? '')) ?: []
        )));

        return [
            'creator_id' => (int) current_user()['id'],
            'subject_id' => (int) ($_POST['subject_id'] ?? ($exam['subject_id'] ?? 0)),
            'statement_text' => trim($_POST['statement_text'] ?? ''),
            'question_type' => trim($_POST['question_type'] ?? 'objetiva'),
            'alternatives' => $alternatives,
            'correct_answer' => trim($_POST['correct_answer'] ?? ''),
            'points' => max(0.1, (float) str_replace(',', '.', (string) ($_POST['points'] ?? 1))),
            'explanation' => trim($_POST['explanation'] ?? ''),
            'difficulty' => trim($_POST['difficulty'] ?? 'media'),
        ];
    }

    private function validateExam(array $data): array
    {
        $errors = [];

        if (strlen($data['title']) < 3) {
            $errors[] = 'Informe um titulo com pelo menos 3 caracteres.';
        }

        if (! in_array($data['status'], ['rascunho', 'publicado', 'encerrado'], true)) {
            $errors[] = 'Selecione um status valido.';
        }

        if ($data['ends_at'] && $data['starts_at'] && strtotime($data['ends_at']) < strtotime($data['starts_at'])) {
            $errors[] = 'A data final deve ser posterior ao inicio.';
        }

        return $errors;
    }

    private function validateQuestion(array $data): array
    {
        $errors = [];

        if (strlen($data['statement_text']) < 5) {
            $errors[] = 'Informe o enunciado da questao.';
        }

        if (! in_array($data['question_type'], ['objetiva', 'discursiva'], true)) {
            $errors[] = 'Selecione um tipo de questao valido.';
        }

        if ($data['question_type'] === 'objetiva') {
            if (count($data['alternatives']) < 2) {
                $errors[] = 'Questoes objetivas precisam de pelo menos duas alternativas.';
            }

            if ($data['correct_answer'] === '') {
                $errors[] = 'Informe a resposta correta.';
            }
        }

        if (! in_array($data['difficulty'], ['facil', 'media', 'dificil'], true)) {
            $errors[] = 'Selecione uma dificuldade valida.';
        }

        return $errors;
    }

    private function findManageableExam(int $id): array
    {
        $exam = $this->exams->find($id);

        if (! $exam || ! $this->exams->canManage($id, current_user())) {
            flash('error', 'Prova nao encontrada ou indisponivel.');
            $this->redirect('/admin/provas');
        }

        return $exam;
    }

    private function findManageableAttempt(int $attemptId): array
    {
        $attempt = $this->exams->findAttempt($attemptId);

        if (! $attempt || ! $this->exams->canManage((int) $attempt['exam_id'], current_user())) {
            flash('error', 'Tentativa nao encontrada ou indisponivel.');
            $this->redirect('/admin/provas');
        }

        return $attempt;
    }

    private function guardCsrf(string $fallback): void
    {
        if (! verify_csrf_token($_POST['_csrf'] ?? null)) {
            flash('error', 'Sua sessao expirou. Tente novamente.');
            $this->redirect($fallback);
        }
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
}
