<?php

defined('BASE_PATH') || exit('Acesso direto nao permitido.');

class CourseCatalogController extends Controller
{
    private Course $courses;
    private Enrollment $enrollments;
    private ActionLog $logs;
    private GamificationService $gamification;
    private CertificateService $certificates;

    public function __construct()
    {
        $this->courses = new Course();
        $this->enrollments = new Enrollment();
        $this->logs = new ActionLog();
        $this->gamification = new GamificationService();
        $this->certificates = new CertificateService();
    }

    public function index(): void
    {
        $this->view('courses/index', [
            'title' => 'Cursos disponíveis',
            'courses' => $this->courses->published(),
        ]);
    }

    public function show(string $id): void
    {
        $user = current_user();
        $course = $this->courses->findPublished((int) $id);

        if (! $course) {
            flash('error', 'Curso não encontrado ou indisponível.');
            $this->redirect('/aluno/cursos');
        }

        $this->view('courses/show', [
            'title' => $course['title'],
            'course' => $course,
            'structure' => $this->courses->structure((int) $course['id'], true),
            'enrollment' => $this->enrollments->findByUserAndCourse((int) $user['id'], (int) $course['id']),
        ]);
    }

    public function enroll(string $id): void
    {
        if (! verify_csrf_token($_POST['_csrf'] ?? null)) {
            flash('error', 'Sua sessão expirou. Tente novamente.');
            $this->redirect('/aluno/cursos/' . $id);
        }

        $user = current_user();
        $course = $this->courses->findPublished((int) $id);

        if (! $course) {
            flash('error', 'Curso não encontrado ou indisponível.');
            $this->redirect('/aluno/cursos');
        }

        $existing = $this->enrollments->findByUserAndCourse((int) $user['id'], (int) $course['id']);

        if ($existing) {
            flash('info', 'Você já possui matrícula neste curso.');
            $this->redirect('/meus-cursos/' . $existing['id']);
        }

        try {
            $enrollmentId = $this->enrollments->create((int) $user['id'], (int) $course['id']);
            $this->logs->record((int) $user['id'], 'enrollment.created', [
                'enrollment_id' => $enrollmentId,
                'course_id' => (int) $course['id'],
            ]);
            try {
                $this->gamification->enrollmentCreated((int) $user['id'], $enrollmentId, (int) $course['id']);
            } catch (Throwable $eventException) {
                $this->logs->record((int) $user['id'], 'gamification.error', ['message' => $eventException->getMessage()], 'warning');
            }

            flash('success', 'Matrícula realizada. Bom estudo!');
            $this->redirect('/meus-cursos/' . $enrollmentId);
        } catch (PDOException $exception) {
            flash('error', 'Não foi possível criar a matrícula. Verifique se você já está matriculado.');
            $this->redirect('/aluno/cursos/' . $course['id']);
        }
    }

    public function myCourses(): void
    {
        $user = current_user();

        $this->view('courses/my_courses', [
            'title' => 'Meus cursos',
            'enrollments' => $this->enrollments->forStudent((int) $user['id']),
        ]);
    }

    public function enrollment(string $id): void
    {
        $user = current_user();
        $enrollment = $this->findStudentEnrollmentOrRedirect((int) $id, (int) $user['id']);

        $this->view('courses/enrollment', [
            'title' => $enrollment['course_title'],
            'enrollment' => $enrollment,
            'structure' => $this->courses->structure((int) $enrollment['course_id'], true),
            'progressMap' => $this->enrollments->progressMap((int) $enrollment['id']),
            'certificate' => (new Certificate())->findByEnrollment((int) $enrollment['id']),
        ]);
    }

    public function completeLesson(string $enrollmentId, string $lessonId): void
    {
        if (! verify_csrf_token($_POST['_csrf'] ?? null)) {
            flash('error', 'Sua sessão expirou. Tente novamente.');
            $this->redirect('/meus-cursos/' . $enrollmentId);
        }

        $user = current_user();

        try {
            $progress = $this->enrollments->markLessonCompleted((int) $enrollmentId, (int) $user['id'], (int) $lessonId);
            $this->logs->record((int) $user['id'], 'lesson.completed', [
                'enrollment_id' => (int) $enrollmentId,
                'lesson_id' => (int) $lessonId,
                'progress_percent' => $progress['progress_percent'],
            ]);
            try {
                $this->gamification->lessonCompleted((int) $user['id'], (int) $lessonId, (int) $enrollmentId);
            } catch (Throwable $eventException) {
                $this->logs->record((int) $user['id'], 'gamification.error', ['message' => $eventException->getMessage()], 'warning');
            }

            if ($progress['course_completed_now']) {
                $enrollment = $this->enrollments->find((int) $enrollmentId);
                $this->logs->record((int) $user['id'], 'course.completed', [
                    'enrollment_id' => (int) $enrollmentId,
                    'progress_percent' => $progress['progress_percent'],
                ]);
                if ($enrollment) {
                    try {
                        $this->gamification->courseCompleted((int) $user['id'], (int) $enrollmentId, (int) $enrollment['course_id']);
                        $this->certificates->issueForEnrollment((int) $enrollmentId);
                    } catch (Throwable $eventException) {
                        $this->logs->record((int) $user['id'], 'certificate_or_gamification.error', ['message' => $eventException->getMessage()], 'warning');
                    }
                }
                flash('success', 'Curso concluído. Progresso em 100%.');
            } else {
                flash('success', 'Aula marcada como concluída.');
            }
        } catch (Throwable $exception) {
            flash('error', $exception->getMessage());
        }

        $this->redirect('/meus-cursos/' . $enrollmentId);
    }

    private function findStudentEnrollmentOrRedirect(int $id, int $userId): array
    {
        $enrollment = $this->enrollments->findForStudent($id, $userId);

        if (! $enrollment) {
            flash('error', 'Matrícula não encontrada.');
            $this->redirect('/meus-cursos');
        }

        return $enrollment;
    }
}
