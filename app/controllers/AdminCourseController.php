<?php

defined('BASE_PATH') || exit('Acesso direto não permitido.');

class AdminCourseController extends Controller
{
    private Course $courses;
    private CourseModule $modules;
    private Lesson $lessons;
    private Material $materials;
    private ActionLog $logs;

    public function __construct()
    {
        $this->courses = new Course();
        $this->modules = new CourseModule();
        $this->lessons = new Lesson();
        $this->materials = new Material();
        $this->logs = new ActionLog();
    }

    public function index(): void
    {
        $filters = [
            'status' => trim($_GET['status'] ?? ''),
            'category' => trim($_GET['category'] ?? ''),
            'teacher_id' => trim($_GET['teacher_id'] ?? ''),
        ];

        $this->view('admin/courses/index', [
            'title' => 'Cursos',
            'courses' => $this->courses->all($filters),
            'filters' => $filters,
            'categories' => $this->courses->categories(),
            'teachers' => $this->courses->teachers(),
        ]);
    }

    public function create(): void
    {
        $this->view('admin/courses/form', [
            'title' => 'Novo curso',
            'course' => null,
            'teachers' => $this->courses->teachers(),
            'action' => url('/admin/cursos'),
        ]);
    }

    public function store(): void
    {
        $this->guardCsrf('/admin/cursos/novo');

        $data = $this->coursePayload();
        $errors = $this->validateCourse($data);

        if ($errors) {
            $_SESSION['_old'] = $data;
            flash('errors', $errors);
            $this->redirect('/admin/cursos/novo');
        }

        $data['creator_id'] = (int) current_user()['id'];
        $data['image_path'] = $this->uploadFile('image', 'course-images', ['image/jpeg', 'image/png', 'image/webp']);

        $courseId = $this->courses->create($data);
        unset($_SESSION['_old']);
        $this->log('course.created', ['course_id' => $courseId, 'title' => $data['title']]);

        flash('success', 'Curso criado com sucesso.');
        $this->redirect('/admin/cursos/' . $courseId);
    }

    public function show(string $id): void
    {
        $course = $this->findCourseOrFail((int) $id);

        $this->view('admin/courses/show', [
            'title' => $course['title'],
            'course' => $course,
            'structure' => $this->courses->structure((int) $course['id']),
        ]);
    }

    public function edit(string $id): void
    {
        $course = $this->findCourseOrFail((int) $id);

        $this->view('admin/courses/form', [
            'title' => 'Editar curso',
            'course' => $course,
            'teachers' => $this->courses->teachers(),
            'action' => url('/admin/cursos/' . $course['id'] . '/atualizar'),
        ]);
    }

    public function update(string $id): void
    {
        $course = $this->findCourseOrFail((int) $id);
        $this->guardCsrf('/admin/cursos/' . $course['id'] . '/editar');

        $data = $this->coursePayload();
        $errors = $this->validateCourse($data);

        if ($errors) {
            $_SESSION['_old'] = $data;
            flash('errors', $errors);
            $this->redirect('/admin/cursos/' . $course['id'] . '/editar');
        }

        $data['image_path'] = $this->uploadFile('image', 'course-images', ['image/jpeg', 'image/png', 'image/webp']);
        $this->courses->update((int) $course['id'], $data);
        unset($_SESSION['_old']);
        $this->log('course.updated', ['course_id' => $course['id'], 'title' => $data['title']]);

        flash('success', 'Curso atualizado.');
        $this->redirect('/admin/cursos/' . $course['id']);
    }

    public function deactivate(string $id): void
    {
        $course = $this->findCourseOrFail((int) $id);
        $this->guardCsrf('/admin/cursos/' . $course['id']);

        $this->courses->deactivate((int) $course['id']);
        $this->log('course.archived', ['course_id' => $course['id'], 'title' => $course['title']], 'warning');

        flash('success', 'Curso arquivado.');
        $this->redirect('/admin/cursos');
    }

    public function createModule(string $courseId): void
    {
        $course = $this->findCourseOrFail((int) $courseId);

        $this->view('admin/courses/module_form', [
            'title' => 'Novo módulo',
            'course' => $course,
            'module' => null,
            'action' => url('/admin/cursos/' . $course['id'] . '/modulos'),
        ]);
    }

    public function storeModule(string $courseId): void
    {
        $course = $this->findCourseOrFail((int) $courseId);
        $this->guardCsrf('/admin/cursos/' . $course['id'] . '/modulos/novo');

        $data = $this->modulePayload((int) $course['id']);
        $errors = $this->validateModule($data);

        if ($errors) {
            $_SESSION['_old'] = $data;
            flash('errors', $errors);
            $this->redirect('/admin/cursos/' . $course['id'] . '/modulos/novo');
        }

        $moduleId = $this->modules->create($data);
        unset($_SESSION['_old']);
        $this->log('course_module.created', ['course_id' => $course['id'], 'module_id' => $moduleId]);

        flash('success', 'Módulo criado.');
        $this->redirect('/admin/cursos/' . $course['id']);
    }

    public function editModule(string $courseId, string $moduleId): void
    {
        $course = $this->findCourseOrFail((int) $courseId);
        $module = $this->findModuleOrFail((int) $moduleId, (int) $course['id']);

        $this->view('admin/courses/module_form', [
            'title' => 'Editar módulo',
            'course' => $course,
            'module' => $module,
            'action' => url('/admin/cursos/' . $course['id'] . '/modulos/' . $module['id'] . '/atualizar'),
        ]);
    }

    public function updateModule(string $courseId, string $moduleId): void
    {
        $course = $this->findCourseOrFail((int) $courseId);
        $module = $this->findModuleOrFail((int) $moduleId, (int) $course['id']);
        $this->guardCsrf('/admin/cursos/' . $course['id'] . '/modulos/' . $module['id'] . '/editar');

        $data = $this->modulePayload((int) $course['id']);
        $errors = $this->validateModule($data);

        if ($errors) {
            $_SESSION['_old'] = $data;
            flash('errors', $errors);
            $this->redirect('/admin/cursos/' . $course['id'] . '/modulos/' . $module['id'] . '/editar');
        }

        $this->modules->update((int) $module['id'], $data);
        unset($_SESSION['_old']);
        $this->log('course_module.updated', ['course_id' => $course['id'], 'module_id' => $module['id']]);

        flash('success', 'Módulo atualizado.');
        $this->redirect('/admin/cursos/' . $course['id']);
    }

    public function deleteModule(string $courseId, string $moduleId): void
    {
        $course = $this->findCourseOrFail((int) $courseId);
        $module = $this->findModuleOrFail((int) $moduleId, (int) $course['id']);
        $this->guardCsrf('/admin/cursos/' . $course['id']);

        $this->modules->delete((int) $module['id']);
        $this->log('course_module.deleted', ['course_id' => $course['id'], 'module_id' => $module['id']], 'warning');

        flash('success', 'Módulo removido.');
        $this->redirect('/admin/cursos/' . $course['id']);
    }

    public function createLesson(string $courseId): void
    {
        $course = $this->findCourseOrFail((int) $courseId);

        $this->view('admin/courses/lesson_form', [
            'title' => 'Nova aula',
            'course' => $course,
            'lesson' => null,
            'modules' => $this->modules->forCourse((int) $course['id']),
            'action' => url('/admin/cursos/' . $course['id'] . '/aulas'),
        ]);
    }

    public function storeLesson(string $courseId): void
    {
        $course = $this->findCourseOrFail((int) $courseId);
        $this->guardCsrf('/admin/cursos/' . $course['id'] . '/aulas/novo');

        $data = $this->lessonPayload((int) $course['id']);
        $errors = $this->validateLesson($data);

        if ($errors) {
            $_SESSION['_old'] = $data;
            flash('errors', $errors);
            $this->redirect('/admin/cursos/' . $course['id'] . '/aulas/novo');
        }

        $lessonId = $this->lessons->create($data);
        unset($_SESSION['_old']);
        $this->log('lesson.created', ['course_id' => $course['id'], 'lesson_id' => $lessonId]);

        flash('success', 'Aula criada.');
        $this->redirect('/admin/cursos/' . $course['id']);
    }

    public function editLesson(string $courseId, string $lessonId): void
    {
        $course = $this->findCourseOrFail((int) $courseId);
        $lesson = $this->findLessonOrFail((int) $lessonId, (int) $course['id']);

        $this->view('admin/courses/lesson_form', [
            'title' => 'Editar aula',
            'course' => $course,
            'lesson' => $lesson,
            'modules' => $this->modules->forCourse((int) $course['id']),
            'action' => url('/admin/cursos/' . $course['id'] . '/aulas/' . $lesson['id'] . '/atualizar'),
        ]);
    }

    public function updateLesson(string $courseId, string $lessonId): void
    {
        $course = $this->findCourseOrFail((int) $courseId);
        $lesson = $this->findLessonOrFail((int) $lessonId, (int) $course['id']);
        $this->guardCsrf('/admin/cursos/' . $course['id'] . '/aulas/' . $lesson['id'] . '/editar');

        $data = $this->lessonPayload((int) $course['id']);
        $errors = $this->validateLesson($data);

        if ($errors) {
            $_SESSION['_old'] = $data;
            flash('errors', $errors);
            $this->redirect('/admin/cursos/' . $course['id'] . '/aulas/' . $lesson['id'] . '/editar');
        }

        $this->lessons->update((int) $lesson['id'], $data);
        unset($_SESSION['_old']);
        $this->log('lesson.updated', ['course_id' => $course['id'], 'lesson_id' => $lesson['id']]);

        flash('success', 'Aula atualizada.');
        $this->redirect('/admin/cursos/' . $course['id']);
    }

    public function deleteLesson(string $courseId, string $lessonId): void
    {
        $course = $this->findCourseOrFail((int) $courseId);
        $lesson = $this->findLessonOrFail((int) $lessonId, (int) $course['id']);
        $this->guardCsrf('/admin/cursos/' . $course['id']);

        $this->lessons->delete((int) $lesson['id']);
        $this->log('lesson.deleted', ['course_id' => $course['id'], 'lesson_id' => $lesson['id']], 'warning');

        flash('success', 'Aula removida.');
        $this->redirect('/admin/cursos/' . $course['id']);
    }

    public function createMaterial(string $courseId, string $lessonId): void
    {
        $course = $this->findCourseOrFail((int) $courseId);
        $lesson = $this->findLessonOrFail((int) $lessonId, (int) $course['id']);

        $this->view('admin/courses/material_form', [
            'title' => 'Novo material',
            'course' => $course,
            'lesson' => $lesson,
            'material' => null,
            'action' => url('/admin/cursos/' . $course['id'] . '/aulas/' . $lesson['id'] . '/materiais'),
        ]);
    }

    public function storeMaterial(string $courseId, string $lessonId): void
    {
        $course = $this->findCourseOrFail((int) $courseId);
        $lesson = $this->findLessonOrFail((int) $lessonId, (int) $course['id']);
        $this->guardCsrf('/admin/cursos/' . $course['id'] . '/aulas/' . $lesson['id'] . '/materiais/novo');

        $data = $this->materialPayload($course, $lesson);
        $data['file_path'] = $this->uploadFile('material_file', 'materials', [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'image/webp',
            'text/plain',
            'application/zip',
            'application/octet-stream',
        ]);
        $errors = $this->validateMaterial($data);

        if ($errors) {
            $_SESSION['_old'] = $data;
            flash('errors', $errors);
            $this->redirect('/admin/cursos/' . $course['id'] . '/aulas/' . $lesson['id'] . '/materiais/novo');
        }

        $materialId = $this->materials->create($data);
        unset($_SESSION['_old']);
        $this->log('material.created', ['course_id' => $course['id'], 'lesson_id' => $lesson['id'], 'material_id' => $materialId]);

        flash('success', 'Material cadastrado.');
        $this->redirect('/admin/cursos/' . $course['id']);
    }

    public function editMaterial(string $courseId, string $lessonId, string $materialId): void
    {
        $course = $this->findCourseOrFail((int) $courseId);
        $lesson = $this->findLessonOrFail((int) $lessonId, (int) $course['id']);
        $material = $this->findMaterialOrFail((int) $materialId, (int) $lesson['id']);

        $this->view('admin/courses/material_form', [
            'title' => 'Editar material',
            'course' => $course,
            'lesson' => $lesson,
            'material' => $material,
            'action' => url('/admin/cursos/' . $course['id'] . '/aulas/' . $lesson['id'] . '/materiais/' . $material['id'] . '/atualizar'),
        ]);
    }

    public function updateMaterial(string $courseId, string $lessonId, string $materialId): void
    {
        $course = $this->findCourseOrFail((int) $courseId);
        $lesson = $this->findLessonOrFail((int) $lessonId, (int) $course['id']);
        $material = $this->findMaterialOrFail((int) $materialId, (int) $lesson['id']);
        $this->guardCsrf('/admin/cursos/' . $course['id'] . '/aulas/' . $lesson['id'] . '/materiais/' . $material['id'] . '/editar');

        $data = $this->materialPayload($course, $lesson);
        $data['file_path'] = $this->uploadFile('material_file', 'materials', [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'image/webp',
            'text/plain',
            'application/zip',
            'application/octet-stream',
        ]);
        $errors = $this->validateMaterial($data, $material);

        if ($errors) {
            $_SESSION['_old'] = $data;
            flash('errors', $errors);
            $this->redirect('/admin/cursos/' . $course['id'] . '/aulas/' . $lesson['id'] . '/materiais/' . $material['id'] . '/editar');
        }

        $this->materials->update((int) $material['id'], $data);
        unset($_SESSION['_old']);
        $this->log('material.updated', ['course_id' => $course['id'], 'lesson_id' => $lesson['id'], 'material_id' => $material['id']]);

        flash('success', 'Material atualizado.');
        $this->redirect('/admin/cursos/' . $course['id']);
    }

    public function deleteMaterial(string $courseId, string $lessonId, string $materialId): void
    {
        $course = $this->findCourseOrFail((int) $courseId);
        $lesson = $this->findLessonOrFail((int) $lessonId, (int) $course['id']);
        $material = $this->findMaterialOrFail((int) $materialId, (int) $lesson['id']);
        $this->guardCsrf('/admin/cursos/' . $course['id']);

        $this->materials->delete((int) $material['id']);
        $this->log('material.deleted', ['course_id' => $course['id'], 'lesson_id' => $lesson['id'], 'material_id' => $material['id']], 'warning');

        flash('success', 'Material removido.');
        $this->redirect('/admin/cursos/' . $course['id']);
    }

    private function coursePayload(): array
    {
        return [
            'title' => trim($_POST['title'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'category' => trim($_POST['category'] ?? 'Geral'),
            'level' => trim($_POST['level'] ?? 'livre'),
            'workload_hours' => max(0, (int) ($_POST['workload_hours'] ?? 0)),
            'price' => max(0, (float) str_replace(',', '.', (string) ($_POST['price'] ?? 0))),
            'access_level' => trim($_POST['access_level'] ?? 'gratuito'),
            'status' => trim($_POST['status'] ?? 'rascunho'),
            'responsible_teacher_id' => (int) ($_POST['responsible_teacher_id'] ?? 0),
            'image_path' => null,
        ];
    }

    private function modulePayload(int $courseId): array
    {
        return [
            'course_id' => $courseId,
            'title' => trim($_POST['title'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'position' => max(1, (int) ($_POST['position'] ?? 1)),
        ];
    }

    private function lessonPayload(int $courseId): array
    {
        return [
            'course_id' => $courseId,
            'module_id' => (int) ($_POST['module_id'] ?? 0),
            'title' => trim($_POST['title'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'lesson_type' => trim($_POST['lesson_type'] ?? 'video'),
            'video_url' => trim($_POST['video_url'] ?? ''),
            'content' => trim($_POST['content'] ?? ''),
            'position' => max(1, (int) ($_POST['position'] ?? 1)),
            'duration_minutes' => max(0, (int) ($_POST['duration_minutes'] ?? 0)),
            'status' => trim($_POST['status'] ?? 'rascunho'),
        ];
    }

    private function materialPayload(array $course, array $lesson): array
    {
        return [
            'course_id' => (int) $course['id'],
            'module_id' => (int) ($lesson['module_id'] ?? 0),
            'lesson_id' => (int) $lesson['id'],
            'owner_id' => (int) current_user()['id'],
            'title' => trim($_POST['title'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'material_type' => trim($_POST['material_type'] ?? 'arquivo'),
            'visibility' => trim($_POST['visibility'] ?? 'privado'),
            'external_url' => trim($_POST['external_url'] ?? ''),
            'status' => trim($_POST['status'] ?? 'ativo'),
            'file_path' => null,
        ];
    }

    private function validateCourse(array $data): array
    {
        $errors = [];

        if (strlen($data['title']) < 3) {
            $errors[] = 'Informe um título de curso com pelo menos 3 caracteres.';
        }

        if ($data['category'] === '') {
            $errors[] = 'Informe a categoria do curso.';
        }

        if (! in_array($data['level'], ['iniciante', 'intermediario', 'avancado', 'livre'], true)) {
            $errors[] = 'Selecione um nível válido.';
        }

        if (! in_array($data['status'], ['rascunho', 'publicado', 'arquivado'], true)) {
            $errors[] = 'Selecione um status válido.';
        }

        if (! in_array($data['access_level'], ['gratuito', 'premium'], true)) {
            $errors[] = 'Selecione um tipo de acesso valido.';
        }

        return $errors;
    }

    private function validateModule(array $data): array
    {
        return strlen($data['title']) < 3 ? ['Informe um título de módulo com pelo menos 3 caracteres.'] : [];
    }

    private function validateLesson(array $data): array
    {
        $errors = [];

        if (strlen($data['title']) < 3) {
            $errors[] = 'Informe um título de aula com pelo menos 3 caracteres.';
        }

        if (! in_array($data['lesson_type'], ['video', 'texto', 'ao_vivo', 'arquivo', 'link'], true)) {
            $errors[] = 'Selecione um tipo de aula válido.';
        }

        if (! in_array($data['status'], ['rascunho', 'publicada', 'arquivada'], true)) {
            $errors[] = 'Selecione um status de aula válido.';
        }

        return $errors;
    }

    private function validateMaterial(array $data, ?array $existing = null): array
    {
        $errors = [];

        if (strlen($data['title']) < 3) {
            $errors[] = 'Informe um título de material com pelo menos 3 caracteres.';
        }

        if (! in_array($data['material_type'], ['pdf', 'imagem', 'link', 'arquivo', 'livro', 'apostila', 'video'], true)) {
            $errors[] = 'Selecione um tipo de material válido.';
        }

        if (! in_array($data['visibility'], ['publico', 'privado', 'institucional'], true)) {
            $errors[] = 'Selecione uma visibilidade válida.';
        }

        if (! in_array($data['status'], ['ativo', 'inativo'], true)) {
            $errors[] = 'Selecione um status válido.';
        }

        $hasFile = $data['file_path'] || ($existing && ! empty($existing['file_path']));
        $hasLink = $data['external_url'] !== '';

        if ($data['material_type'] === 'link' && ! filter_var($data['external_url'], FILTER_VALIDATE_URL)) {
            $errors[] = 'Informe um link externo válido.';
        }

        if ($data['material_type'] !== 'link' && ! $hasFile && ! $hasLink) {
            $errors[] = 'Envie um arquivo ou informe um link para o material.';
        }

        return $errors;
    }

    private function uploadFile(string $field, string $directory, array $allowedMimes): ?string
    {
        if (empty($_FILES[$field]) || ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if (($_FILES[$field]['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            flash('error', 'Não foi possível receber o arquivo enviado.');
            return null;
        }

        if (($_FILES[$field]['size'] ?? 0) > 8 * 1024 * 1024) {
            flash('error', 'O arquivo deve ter no máximo 8 MB.');
            return null;
        }

        $tmp = $_FILES[$field]['tmp_name'];
        $mime = mime_content_type($tmp) ?: 'application/octet-stream';

        if (! in_array($mime, $allowedMimes, true)) {
            flash('error', 'Tipo de arquivo não permitido.');
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
            flash('error', 'Não foi possível salvar o arquivo enviado.');
            return null;
        }

        return $relative;
    }

    private function findCourseOrFail(int $id): array
    {
        $course = $this->courses->find($id);

        if (! $course) {
            flash('error', 'Curso não encontrado.');
            $this->redirect('/admin/cursos');
        }

        return $course;
    }

    private function findModuleOrFail(int $id, int $courseId): array
    {
        $module = $this->modules->find($id);

        if (! $module || (int) $module['course_id'] !== $courseId) {
            flash('error', 'Módulo não encontrado neste curso.');
            $this->redirect('/admin/cursos/' . $courseId);
        }

        return $module;
    }

    private function findLessonOrFail(int $id, int $courseId): array
    {
        $lesson = $this->lessons->find($id);

        if (! $lesson || (int) $lesson['course_id'] !== $courseId) {
            flash('error', 'Aula não encontrada neste curso.');
            $this->redirect('/admin/cursos/' . $courseId);
        }

        return $lesson;
    }

    private function findMaterialOrFail(int $id, int $lessonId): array
    {
        $material = $this->materials->find($id);

        if (! $material || (int) $material['lesson_id'] !== $lessonId) {
            flash('error', 'Material não encontrado nesta aula.');
            $this->redirect('/admin/cursos');
        }

        return $material;
    }

    private function guardCsrf(string $fallback): void
    {
        if (! verify_csrf_token($_POST['_csrf'] ?? null)) {
            flash('error', 'Sua sessão expirou. Tente novamente.');
            $this->redirect($fallback);
        }
    }

    private function log(string $action, array $context = [], string $level = 'info'): void
    {
        $this->logs->record((int) current_user()['id'], $action, $context, $level);
    }
}
