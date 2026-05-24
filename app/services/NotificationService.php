<?php

defined('BASE_PATH') || exit('Acesso direto nao permitido.');

class NotificationService
{
    private PDO $db;
    private ActionLog $logs;

    public function __construct()
    {
        $this->db = Database::connection();
        $this->logs = new ActionLog();
    }

    public function send(int $userId, string $type, string $title, string $message, ?string $actionUrl = null, array $metadata = []): int
    {
        $statement = $this->db->prepare(
            'INSERT INTO notifications (
                user_id, title, message, notification_type, action_url, metadata,
                priority, created_at, updated_at
             ) VALUES (
                :user_id, :title, :message, :notification_type, :action_url, :metadata,
                :priority, NOW(), NOW()
             )'
        );
        $statement->execute([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'notification_type' => $type,
            'action_url' => $actionUrl,
            'metadata' => json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'priority' => $metadata['priority'] ?? 'normal',
        ]);

        $notificationId = (int) $this->db->lastInsertId();
        $this->logs->record($userId, 'notification.sent', [
            'notification_id' => $notificationId,
            'type' => $type,
        ]);

        return $notificationId;
    }

    public function recent(int $userId, int $limit = 6): array
    {
        $statement = $this->db->prepare(
            'SELECT * FROM notifications
             WHERE user_id = :user_id
             ORDER BY created_at DESC
             LIMIT ' . max(1, $limit)
        );
        $statement->execute(['user_id' => $userId]);

        return $statement->fetchAll();
    }

    public function all(int $userId): array
    {
        $statement = $this->db->prepare(
            'SELECT * FROM notifications
             WHERE user_id = :user_id
             ORDER BY created_at DESC'
        );
        $statement->execute(['user_id' => $userId]);

        return $statement->fetchAll();
    }

    public function unreadCount(int $userId): int
    {
        $statement = $this->db->prepare(
            'SELECT COUNT(*) FROM notifications WHERE user_id = :user_id AND read_at IS NULL'
        );
        $statement->execute(['user_id' => $userId]);

        return (int) $statement->fetchColumn();
    }

    public function markRead(int $notificationId, int $userId): void
    {
        $statement = $this->db->prepare(
            'UPDATE notifications
             SET read_at = NOW(), updated_at = NOW()
             WHERE id = :id AND user_id = :user_id'
        );
        $statement->execute(['id' => $notificationId, 'user_id' => $userId]);
    }

    public function markUnread(int $notificationId, int $userId): void
    {
        $statement = $this->db->prepare(
            'UPDATE notifications
             SET read_at = NULL, updated_at = NOW()
             WHERE id = :id AND user_id = :user_id'
        );
        $statement->execute(['id' => $notificationId, 'user_id' => $userId]);
    }

    public function markAllRead(int $userId): void
    {
        $statement = $this->db->prepare(
            'UPDATE notifications SET read_at = COALESCE(read_at, NOW()), updated_at = NOW() WHERE user_id = :user_id'
        );
        $statement->execute(['user_id' => $userId]);
    }

    public function enrollmentCreated(int $userId, int $enrollmentId, string $courseTitle): void
    {
        $this->send($userId, 'matricula', 'Matricula realizada', 'Voce se matriculou em ' . $courseTitle . '.', '/meus-cursos/' . $enrollmentId);
    }

    public function courseCompleted(int $userId, int $enrollmentId, string $courseTitle): void
    {
        $this->send($userId, 'curso_concluido', 'Curso concluido', 'Parabens pela conclusao de ' . $courseTitle . '.', '/meus-cursos/' . $enrollmentId, ['priority' => 'alta']);
    }

    public function certificateIssued(int $userId, string $code, string $title): void
    {
        $this->send($userId, 'certificado_emitido', 'Certificado emitido', 'Seu certificado de ' . $title . ' esta disponivel.', '/certificados/ver/' . $code, ['priority' => 'alta']);
    }

    public function activityGraded(int $userId, int $activityId, string $title): void
    {
        $this->send($userId, 'atividade_corrigida', 'Atividade corrigida', 'A atividade ' . $title . ' recebeu correcao.', '/atividades/' . $activityId);
    }

    public function commentCreated(int $userId, int $postId, string $postTitle): void
    {
        $this->send($userId, 'comentario_post', 'Novo comentario', 'Seu post recebeu comentario: ' . $postTitle . '.', '/comunidade/' . $postId);
    }

    public function chatMessage(int $userId, int $channelId, string $senderName): void
    {
        $this->send($userId, 'mensagem_chat', 'Nova mensagem', $senderName . ' enviou uma mensagem no chat.', '/chat?canal=' . $channelId);
    }

    public function eventRegistered(int $userId, int $eventId, string $title): void
    {
        $this->send($userId, 'evento_inscrito', 'Inscricao em evento', 'Sua inscricao em ' . $title . ' foi registrada.', '/eventos/' . $eventId);
    }

    public function examReleased(int $userId, int $examId, string $title): void
    {
        $this->send($userId, 'prova_liberada', 'Prova liberada', 'A prova ' . $title . ' esta disponivel.', '/provas/' . $examId);
    }

    public function badgeAwarded(int $userId, string $badgeName): void
    {
        $this->send($userId, 'badge_conquistada', 'Badge conquistada', 'Voce conquistou: ' . $badgeName . '.', '/perfil#estatisticas');
    }
}
