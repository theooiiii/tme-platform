<?php

defined('BASE_PATH') || exit('Acesso direto nao permitido.');

class NotificationController extends Controller
{
    private NotificationService $notifications;

    public function __construct()
    {
        $this->notifications = new NotificationService();
    }

    public function index(): void
    {
        $user = current_user();

        $this->view('notifications/index', [
            'title' => 'Notificacoes',
            'notifications' => $this->notifications->all((int) $user['id']),
            'unreadCount' => $this->notifications->unreadCount((int) $user['id']),
        ]);
    }

    public function markRead(string $id): void
    {
        $this->guardCsrf('/notificacoes');
        $user = current_user();
        $this->notifications->markRead((int) $id, (int) $user['id']);

        $this->redirectBack();
    }

    public function markUnread(string $id): void
    {
        $this->guardCsrf('/notificacoes');
        $user = current_user();
        $this->notifications->markUnread((int) $id, (int) $user['id']);

        $this->redirectBack();
    }

    public function markAllRead(): void
    {
        $this->guardCsrf('/notificacoes');
        $user = current_user();
        $this->notifications->markAllRead((int) $user['id']);

        flash('success', 'Notificacoes marcadas como lidas.');
        $this->redirectBack();
    }

    private function redirectBack(): void
    {
        $redirect = (string) ($_POST['redirect_to'] ?? '/notificacoes');
        $this->redirect(str_starts_with($redirect, '/') ? $redirect : '/notificacoes');
    }

    private function guardCsrf(string $fallback): void
    {
        if (! verify_csrf_token($_POST['_csrf'] ?? null)) {
            flash('error', 'Sua sessao expirou. Tente novamente.');
            $this->redirect($fallback);
        }
    }
}
