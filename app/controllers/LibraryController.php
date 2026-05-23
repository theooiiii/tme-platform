<?php

defined('BASE_PATH') || exit('Acesso direto nao permitido.');

class LibraryController extends Controller
{
    private LibraryItem $library;
    private Activity $activities;
    private ActionLog $logs;
    private GamificationService $gamification;

    public function __construct()
    {
        $this->library = new LibraryItem();
        $this->activities = new Activity();
        $this->logs = new ActionLog();
        $this->gamification = new GamificationService();
    }

    public function index(): void
    {
        $filters = $this->filters();
        $user = current_user();

        $this->view('library/index', [
            'title' => 'Biblioteca',
            'items' => $this->library->visible($filters, $user),
            'filters' => $filters,
            'categories' => $this->library->categories(),
            'subjects' => $this->library->subjects(),
            'isPublicPage' => ! $user,
        ]);
    }

    public function show(string $id): void
    {
        $user = current_user();
        $item = $this->library->findVisible((int) $id, $user);

        if (! $item) {
            flash('error', 'Material nao encontrado ou indisponivel.');
            $this->redirect('/biblioteca');
        }

        $this->library->recordAccess((int) $item['id'], $user ? (int) $user['id'] : null);
        $this->logs->record($user ? (int) $user['id'] : null, 'library.viewed', ['library_item_id' => (int) $item['id']]);

        $this->view('library/show', [
            'title' => $item['title'],
            'item' => $item,
            'isFavorite' => $user ? $this->library->isFavorite((int) $item['id'], (int) $user['id']) : false,
        ]);
    }

    public function favorites(): void
    {
        $user = current_user();

        $this->view('library/favorites', [
            'title' => 'Meus favoritos',
            'items' => $this->library->favorites((int) $user['id']),
        ]);
    }

    public function toggleFavorite(string $id): void
    {
        $this->guardCsrf('/biblioteca/' . $id);

        $user = current_user();
        $item = $this->library->findVisible((int) $id, $user);

        if (! $item) {
            flash('error', 'Material nao encontrado ou indisponivel.');
            $this->redirect('/biblioteca');
        }

        $favorite = $this->library->toggleFavorite((int) $item['id'], (int) $user['id']);
        $this->logs->record((int) $user['id'], $favorite ? 'library.favorite.added' : 'library.favorite.removed', [
            'library_item_id' => (int) $item['id'],
        ]);

        if ($favorite) {
            $this->gamification->libraryFavoriteAdded((int) $user['id'], (int) $item['id']);
        }

        flash('success', $favorite ? 'Material adicionado aos favoritos.' : 'Material removido dos favoritos.');
        $this->redirect('/biblioteca/' . $item['id']);
    }

    public function contribute(): void
    {
        $this->view('library/contribute_form', $this->formData('Enviar material', null, url('/biblioteca/enviar'), true));
    }

    public function storeContribution(): void
    {
        $this->guardCsrf('/biblioteca/enviar');
        $data = $this->libraryPayload();
        $data['status'] = 'pendente';
        $data['file_path'] = $this->uploadFile('library_file', 'library', $this->documentMimes());
        $data['cover_path'] = $this->uploadFile('cover', 'library-covers', $this->imageMimes());
        $errors = $this->validateLibrary($data);

        if ($errors) {
            $_SESSION['_old'] = $data;
            flash('errors', $errors);
            $this->redirect('/biblioteca/enviar');
        }

        $itemId = $this->library->create($data);
        unset($_SESSION['_old']);
        $this->logs->record((int) current_user()['id'], 'library.created', ['library_item_id' => $itemId, 'moderated' => true]);

        flash('success', 'Material enviado para moderacao.');
        $this->redirect('/biblioteca');
    }

    public function adminIndex(): void
    {
        $filters = $this->filters() + ['status' => trim($_GET['status'] ?? '')];
        $user = current_user();

        $this->view('admin/library/index', [
            'title' => 'Biblioteca admin',
            'items' => $this->library->adminList($filters, $user),
            'filters' => $filters,
            'categories' => $this->library->categories(),
            'subjects' => $this->library->subjects(),
        ]);
    }

    public function create(): void
    {
        $this->view('admin/library/form', $this->formData('Novo item da biblioteca', null, url('/admin/biblioteca'), false));
    }

    public function store(): void
    {
        $this->guardCsrf('/admin/biblioteca/novo');
        $data = $this->libraryPayload();

        if (current_user()['role_slug'] === 'professor') {
            $data['status'] = 'pendente';
        }

        $data['file_path'] = $this->uploadFile('library_file', 'library', $this->documentMimes());
        $data['cover_path'] = $this->uploadFile('cover', 'library-covers', $this->imageMimes());
        $errors = $this->validateLibrary($data);

        if ($errors) {
            $_SESSION['_old'] = $data;
            flash('errors', $errors);
            $this->redirect('/admin/biblioteca/novo');
        }

        $itemId = $this->library->create($data);
        unset($_SESSION['_old']);
        $this->logs->record((int) current_user()['id'], 'library.created', ['library_item_id' => $itemId]);

        flash('success', 'Item cadastrado.');
        $this->redirect('/admin/biblioteca');
    }

    public function edit(string $id): void
    {
        $item = $this->findManageableItem((int) $id);

        $this->view('admin/library/form', $this->formData('Editar item da biblioteca', $item, url('/admin/biblioteca/' . $item['id'] . '/atualizar'), false));
    }

    public function update(string $id): void
    {
        $item = $this->findManageableItem((int) $id);
        $this->guardCsrf('/admin/biblioteca/' . $item['id'] . '/editar');

        $data = $this->libraryPayload();

        if (current_user()['role_slug'] === 'professor') {
            $data['status'] = 'pendente';
        }

        $data['file_path'] = $this->uploadFile('library_file', 'library', $this->documentMimes());
        $data['cover_path'] = $this->uploadFile('cover', 'library-covers', $this->imageMimes());
        $errors = $this->validateLibrary($data, $item);

        if ($errors) {
            $_SESSION['_old'] = $data;
            flash('errors', $errors);
            $this->redirect('/admin/biblioteca/' . $item['id'] . '/editar');
        }

        $this->library->update((int) $item['id'], $data);
        unset($_SESSION['_old']);
        $this->logs->record((int) current_user()['id'], 'library.updated', ['library_item_id' => (int) $item['id']]);

        flash('success', 'Item atualizado.');
        $this->redirect('/admin/biblioteca');
    }

    public function approve(string $id): void
    {
        $this->moderate((int) $id, 'publicado', 'library.approved', 'Material aprovado.');
    }

    public function reject(string $id): void
    {
        $this->moderate((int) $id, 'recusado', 'library.rejected', 'Material recusado.');
    }

    public function archive(string $id): void
    {
        $this->moderate((int) $id, 'arquivado', 'library.archived', 'Material arquivado.');
    }

    private function moderate(int $id, string $status, string $action, string $message): void
    {
        $item = $this->findManageableItem($id);
        $this->guardCsrf('/admin/biblioteca');
        $notes = trim($_POST['moderation_notes'] ?? '');

        $this->library->setModeration((int) $item['id'], (int) current_user()['id'], $status, $notes);
        $this->logs->record((int) current_user()['id'], $action, ['library_item_id' => (int) $item['id']], $status === 'recusado' ? 'warning' : 'info');

        flash('success', $message);
        $this->redirect('/admin/biblioteca');
    }

    private function filters(): array
    {
        return [
            'q' => trim($_GET['q'] ?? ''),
            'category' => trim($_GET['category'] ?? ''),
            'subject' => trim($_GET['subject'] ?? ''),
            'type' => trim($_GET['type'] ?? ''),
        ];
    }

    private function formData(string $title, ?array $item, string $action, bool $contribution): array
    {
        return [
            'title' => $title,
            'item' => $item,
            'action' => $action,
            'courses' => $this->activities->coursesForSelect(),
            'contribution' => $contribution,
        ];
    }

    private function libraryPayload(): array
    {
        $user = current_user();

        return [
            'owner_id' => (int) $user['id'],
            'course_id' => (int) ($_POST['course_id'] ?? 0),
            'class_id' => (int) ($_POST['class_id'] ?? 0),
            'title' => trim($_POST['title'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'category' => trim($_POST['category'] ?? ''),
            'subject' => trim($_POST['subject'] ?? ''),
            'item_type' => trim($_POST['item_type'] ?? 'arquivo'),
            'visibility' => trim($_POST['visibility'] ?? 'publica'),
            'author' => trim($_POST['author'] ?? $user['full_name']),
            'file_path' => null,
            'external_url' => trim($_POST['external_url'] ?? ''),
            'cover_path' => null,
            'status' => trim($_POST['status'] ?? 'pendente'),
        ];
    }

    private function validateLibrary(array $data, ?array $existing = null): array
    {
        $errors = [];

        if (strlen($data['title']) < 3) {
            $errors[] = 'Informe um titulo com pelo menos 3 caracteres.';
        }

        if (! in_array($data['item_type'], ['pdf', 'livro', 'apostila', 'artigo', 'video', 'link', 'apresentacao', 'imagem', 'arquivo'], true)) {
            $errors[] = 'Selecione um tipo valido.';
        }

        if (! in_array($data['visibility'], ['publica', 'logados', 'curso', 'privada_admin'], true)) {
            $errors[] = 'Selecione uma visibilidade valida.';
        }

        if ($data['visibility'] === 'curso' && ! $data['course_id']) {
            $errors[] = 'Escolha o curso vinculado para visibilidade por curso.';
        }

        if (! in_array($data['status'], ['rascunho', 'pendente', 'publicado', 'arquivado', 'recusado'], true)) {
            $errors[] = 'Selecione um status valido.';
        }

        $hasFile = $data['file_path'] || ($existing && ! empty($existing['file_path']));
        $hasLink = $data['external_url'] !== '';

        if ($data['item_type'] === 'link' && ! filter_var($data['external_url'], FILTER_VALIDATE_URL)) {
            $errors[] = 'Informe um link valido.';
        }

        if (! $hasFile && ! $hasLink) {
            $errors[] = 'Envie um arquivo ou informe um link externo.';
        }

        return $errors;
    }

    private function findManageableItem(int $id): array
    {
        $item = $this->library->find($id);
        $user = current_user();

        if (! $item) {
            flash('error', 'Item da biblioteca nao encontrado.');
            $this->redirect('/admin/biblioteca');
        }

        if ($user['role_slug'] === 'professor' && (int) $item['owner_id'] !== (int) $user['id']) {
            http_response_code(403);
            $this->view('errors/403', ['title' => 'Acesso restrito']);
            exit;
        }

        return $item;
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

    private function imageMimes(): array
    {
        return ['image/jpeg', 'image/png', 'image/webp'];
    }
}
