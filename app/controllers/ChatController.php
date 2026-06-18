<?php

defined('BASE_PATH') || exit('Acesso direto não permitido.');

class ChatController extends Controller
{
    private Chat $chat;
    private ActionLog $logs;
    private NotificationService $notifications;

    public function __construct()
    {
        $this->chat = new Chat();
        $this->logs = new ActionLog();
        $this->notifications = new NotificationService();
    }

    public function index(): void
    {
        $user = current_user();

        if ($user['status'] !== 'aprovado') {
            flash('error', 'Chat disponível apenas para usuários aprovados.');
            $this->redirect('/portal');
        }

        $this->chat->syncClassChannelsForUser($user);
        $conversations = $this->chat->conversationsForUser((int) $user['id']);
        $channelId = (int) ($_GET['canal'] ?? ($conversations[0]['id'] ?? 0));
        $channel = null;
        $messages = [];

        if ($channelId) {
            if (! $this->chat->canAccessChannel($channelId, $user)) {
                flash('error', 'Conversa indisponível.');
                $this->redirect('/chat');
            }

            $channel = $this->chat->findChannel($channelId);
            $messages = $this->chat->messages($channelId);
            $this->chat->markRead($channelId, (int) $user['id']);
        }

        $this->view('chat/index', [
            'title' => 'Chat',
            'user' => $user,
            'conversations' => $conversations,
            'channel' => $channel,
            'messages' => $messages,
            'users' => $this->chat->approvedUsers((int) $user['id']),
        ]);
    }

    public function startPrivate(): void
    {
        $this->guardCsrf('/chat');
        $user = current_user();
        $recipientId = (int) ($_POST['user_id'] ?? 0);

        try {
            $channelId = $this->chat->privateChannel((int) $user['id'], $recipientId);
            $this->logs->record((int) $user['id'], 'chat.private_started', [
                'channel_id' => $channelId,
                'recipient_id' => $recipientId,
            ]);
            $this->redirect('/chat?canal=' . $channelId);
        } catch (Throwable $exception) {
            flash('error', $exception->getMessage());
            $this->redirect('/chat');
        }
    }

    public function send(string $channelId): void
    {
        $this->guardCsrf('/chat?canal=' . $channelId);
        $user = current_user();
        $channel = $this->chat->findChannel((int) $channelId);

        if (! $channel || ! $this->chat->canAccessChannel((int) $channelId, $user)) {
            flash('error', 'Conversa indisponível.');
            $this->redirect('/chat');
        }

        $message = trim($_POST['message'] ?? '');
        $attachment = null;
        $hasAttachment = isset($_FILES['attachment']) && ($_FILES['attachment']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;

        if ($message === '' && ! $hasAttachment) {
            flash('error', 'Digite uma mensagem ou envie um arquivo.');
            $this->redirect('/chat?canal=' . $channelId);
        }

        try {
            if ($hasAttachment) {
                $path = (new UploadService())->storePublic(
                    $_FILES['attachment'],
                    'chat',
                    [
                        'application/pdf',
                        'image/png',
                        'image/jpeg',
                        'image/webp',
                        'text/plain',
                        'application/zip',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                    ],
                    8 * 1024 * 1024
                );
                $attachment = [
                    'path' => $path,
                    'name' => $_FILES['attachment']['name'] ?? 'arquivo',
                    'type' => $_FILES['attachment']['type'] ?? 'arquivo',
                    'size' => (int) ($_FILES['attachment']['size'] ?? 0),
                ];
            }

            $messageId = $this->chat->sendMessage((int) $channelId, (int) $user['id'], $message, $attachment);
            $this->logs->record((int) $user['id'], 'chat.message_sent', [
                'channel_id' => (int) $channelId,
                'message_id' => $messageId,
                'has_attachment' => (bool) $attachment,
            ]);
            foreach ($this->chat->members((int) $channelId) as $member) {
                if ((int) $member['id'] !== (int) $user['id']) {
                    $this->notifications->chatMessage((int) $member['id'], (int) $channelId, (string) $user['full_name']);
                }
            }
        } catch (Throwable $exception) {
            flash('error', $exception->getMessage());
        }

        $this->redirect('/chat?canal=' . $channelId);
    }

    public function audit(): void
    {
        $channelId = (int) ($_GET['canal'] ?? 0);
        $channel = $channelId ? $this->chat->findChannel($channelId) : null;
        $messages = $channel ? $this->chat->messages($channelId) : [];

        if ($channel) {
            $this->logs->record((int) current_user()['id'], 'chat.audit_viewed', [
                'channel_id' => $channelId,
            ], 'security');
        }

        $this->view('admin/chat/index', [
            'title' => 'Auditoria de chat',
            'conversations' => $this->chat->allConversationsForAudit(),
            'channel' => $channel,
            'messages' => $messages,
        ]);
    }

    private function guardCsrf(string $fallback): void
    {
        if (! verify_csrf_token($_POST['_csrf'] ?? null)) {
            flash('error', 'Sua sessao expirou. Tente novamente.');
            $this->redirect($fallback);
        }
    }
}
