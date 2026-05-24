<?php

defined('BASE_PATH') || exit('Acesso direto nao permitido.');

class EventController extends Controller
{
    private Event $events;
    private ActionLog $logs;
    private NotificationService $notifications;

    public function __construct()
    {
        $this->events = new Event();
        $this->logs = new ActionLog();
        $this->notifications = new NotificationService();
    }

    public function index(): void
    {
        $user = current_user();

        $this->view('events/index', [
            'title' => 'Eventos',
            'events' => $this->events->published($user ? (int) $user['id'] : null),
            'user' => $user,
        ]);
    }

    public function show(string $id): void
    {
        $user = current_user();
        $event = $this->events->find((int) $id, $user ? (int) $user['id'] : null);

        if (! $event || ($event['status'] !== 'publicado' && ! $this->isAdmin($user))) {
            flash('error', 'Evento nao encontrado.');
            $this->redirect('/eventos');
        }

        $this->view('events/show', [
            'title' => $event['title'],
            'event' => $event,
            'user' => $user,
        ]);
    }

    public function register(string $id): void
    {
        $this->guardCsrf('/eventos/' . $id);
        $user = current_user();
        $event = $this->events->find((int) $id, (int) $user['id']);

        if (! $event || $event['status'] !== 'publicado') {
            flash('error', 'Evento indisponivel para inscricao.');
            $this->redirect('/eventos');
        }

        if ($event['capacity'] && (int) $event['registrations_count'] >= (int) $event['capacity']) {
            flash('error', 'As vagas deste evento foram preenchidas.');
            $this->redirect('/eventos/' . $id);
        }

        if ($this->events->registrationForUser((int) $event['id'], (int) $user['id'])) {
            flash('info', 'Voce ja esta inscrito neste evento.');
            $this->redirect('/eventos/' . $id);
        }

        $registrationId = $this->events->register((int) $event['id'], (int) $user['id']);
        $this->logs->record((int) $user['id'], 'event.registered', ['event_id' => (int) $event['id'], 'registration_id' => $registrationId]);
        $this->notifications->eventRegistered((int) $user['id'], (int) $event['id'], (string) $event['title']);

        flash('success', 'Inscricao realizada.');
        $this->redirect('/eventos/' . $id);
    }

    public function adminIndex(): void
    {
        $this->view('admin/events/index', [
            'title' => 'Eventos admin',
            'events' => $this->events->adminList(),
        ]);
    }

    public function create(): void
    {
        $this->view('admin/events/form', [
            'title' => 'Novo evento',
            'event' => null,
            'action' => url('/admin/eventos'),
        ]);
    }

    public function store(): void
    {
        $this->guardCsrf('/admin/eventos/novo');
        $data = $this->payload();
        $errors = $this->validateEvent($data);

        if ($errors) {
            $_SESSION['_old'] = $data;
            flash('errors', $errors);
            $this->redirect('/admin/eventos/novo');
        }

        $data['creator_id'] = (int) current_user()['id'];
        $data['image_path'] = $this->uploadImage('image');
        $eventId = $this->events->create($data);
        unset($_SESSION['_old']);
        $this->logs->record((int) current_user()['id'], 'event.created', ['event_id' => $eventId]);

        flash('success', 'Evento criado.');
        $this->redirect('/admin/eventos/' . $eventId);
    }

    public function adminShow(string $id): void
    {
        $event = $this->events->find((int) $id);

        if (! $event) {
            flash('error', 'Evento nao encontrado.');
            $this->redirect('/admin/eventos');
        }

        $this->view('admin/events/show', [
            'title' => $event['title'],
            'event' => $event,
            'registrations' => $this->events->registrations((int) $event['id']),
        ]);
    }

    public function updateStatus(string $id): void
    {
        $this->guardCsrf('/admin/eventos/' . $id);
        $event = $this->events->find((int) $id);

        if (! $event) {
            flash('error', 'Evento nao encontrado.');
            $this->redirect('/admin/eventos');
        }

        $status = trim($_POST['status'] ?? '');

        if (! in_array($status, ['rascunho', 'publicado', 'encerrado'], true)) {
            flash('error', 'Status invalido.');
            $this->redirect('/admin/eventos/' . $id);
        }

        $this->events->setStatus((int) $event['id'], $status);
        $this->logs->record((int) current_user()['id'], 'event.status_updated', ['event_id' => (int) $event['id'], 'status' => $status]);

        flash('success', 'Status do evento atualizado.');
        $this->redirect('/admin/eventos/' . $id);
    }

    public function confirmPresence(string $registrationId): void
    {
        $this->guardCsrf('/admin/eventos');
        $registration = $this->events->findRegistration((int) $registrationId);

        if (! $registration) {
            flash('error', 'Inscricao nao encontrada.');
            $this->redirect('/admin/eventos');
        }

        $this->events->confirmPresence((int) $registration['id']);
        $this->logs->record((int) current_user()['id'], 'event.presence_confirmed', [
            'event_id' => (int) $registration['event_id'],
            'registration_id' => (int) $registration['id'],
            'user_id' => (int) $registration['user_id'],
        ]);

        flash('success', 'Presenca confirmada.');
        $this->redirect('/admin/eventos/' . $registration['event_id']);
    }

    public function issueCertificate(string $registrationId): void
    {
        $this->guardCsrf('/admin/eventos');
        $registration = $this->events->findRegistration((int) $registrationId);

        if (! $registration) {
            flash('error', 'Inscricao nao encontrada.');
            $this->redirect('/admin/eventos');
        }

        $certificate = (new CertificateService())->issueForEventRegistration((int) $registration['id']);

        if (! $certificate) {
            flash('error', 'Certificado disponivel apenas para evento encerrado, com presenca confirmada e certificado habilitado.');
            $this->redirect('/admin/eventos/' . $registration['event_id']);
        }

        $this->logs->record((int) current_user()['id'], 'event.certificate_generated_by_admin', [
            'event_id' => (int) $registration['event_id'],
            'registration_id' => (int) $registration['id'],
            'certificate_id' => (int) $certificate['id'],
        ]);

        flash('success', 'Certificado de participacao emitido.');
        $this->redirect('/admin/eventos/' . $registration['event_id']);
    }

    private function payload(): array
    {
        return [
            'event_type' => trim($_POST['event_type'] ?? 'palestra'),
            'title' => trim($_POST['title'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'starts_at' => $this->normalizeDateTime($_POST['starts_at'] ?? ''),
            'ends_at' => $this->normalizeDateTime($_POST['ends_at'] ?? ''),
            'location' => trim($_POST['location'] ?? ''),
            'is_online' => isset($_POST['is_online']),
            'meeting_url' => trim($_POST['meeting_url'] ?? ''),
            'capacity' => max(0, (int) ($_POST['capacity'] ?? 0)),
            'workload_hours' => max(0, (int) ($_POST['workload_hours'] ?? 0)),
            'certificate_enabled' => isset($_POST['certificate_enabled']),
            'status' => trim($_POST['status'] ?? 'rascunho'),
            'image_path' => null,
        ];
    }

    private function validateEvent(array $data): array
    {
        $errors = [];

        if (strlen($data['title']) < 4) {
            $errors[] = 'Informe um titulo com pelo menos 4 caracteres.';
        }

        if (! in_array($data['event_type'], ['palestra', 'workshop', 'aula_ao_vivo', 'simulado', 'olimpiada', 'hackathon'], true)) {
            $errors[] = 'Selecione um tipo valido.';
        }

        if (! in_array($data['status'], ['rascunho', 'publicado', 'encerrado'], true)) {
            $errors[] = 'Selecione um status valido.';
        }

        if (! $data['starts_at']) {
            $errors[] = 'Informe data e horario do evento.';
        }

        return $errors;
    }

    private function normalizeDateTime(string $value): ?string
    {
        $timestamp = strtotime(trim($value));

        return $timestamp ? date('Y-m-d H:i:s', $timestamp) : null;
    }

    private function uploadImage(string $field): ?string
    {
        if (empty($_FILES[$field]) || ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        $mime = mime_content_type($_FILES[$field]['tmp_name']) ?: '';

        if (! in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            flash('error', 'Imagem do evento em formato invalido.');
            return null;
        }

        $extension = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
        $safeName = bin2hex(random_bytes(12)) . ($extension ? '.' . $extension : '');
        $relative = 'uploads/events/' . $safeName;
        $targetDirectory = BASE_PATH . '/public/uploads/events';

        if (! is_dir($targetDirectory)) {
            mkdir($targetDirectory, 0775, true);
        }

        return move_uploaded_file($_FILES[$field]['tmp_name'], BASE_PATH . '/public/' . $relative) ? $relative : null;
    }

    private function isAdmin(?array $user): bool
    {
        return $user && in_array($user['role_slug'], ['administrador', 'supervisor'], true);
    }

    private function guardCsrf(string $fallback): void
    {
        if (! verify_csrf_token($_POST['_csrf'] ?? null)) {
            flash('error', 'Sua sessao expirou. Tente novamente.');
            $this->redirect($fallback);
        }
    }
}
