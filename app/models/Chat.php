<?php

defined('BASE_PATH') || exit('Acesso direto não permitido.');

class Chat extends Model
{
    public function approvedUsers(int $currentUserId): array
    {
        $statement = $this->db->prepare(
            'SELECT users.id, users.full_name, users.email, roles.slug AS role_slug
             FROM users
             INNER JOIN roles ON roles.id = users.role_id
             WHERE users.status = "aprovado"
               AND users.id <> :current_user_id
             ORDER BY users.full_name'
        );
        $statement->execute(['current_user_id' => $currentUserId]);

        return $statement->fetchAll();
    }

    public function syncClassChannelsForUser(array $user): void
    {
        if (! in_array($user['role_slug'], ['aluno', 'professor'], true)) {
            return;
        }

        $classes = (new SchoolClass())->linkedForUser((int) $user['id'], $user['role_slug']);

        foreach ($classes as $class) {
            $channelId = $this->ensureClassChannel((int) $class['id'], (string) $class['name'], (int) $user['id']);
            $this->syncClassMembers((int) $class['id'], $channelId);
        }
    }

    public function conversationsForUser(int $userId): array
    {
        $statement = $this->db->prepare(
            'SELECT chat_channels.*, chat_channel_members.last_read_at,
                    latest.message AS last_message,
                    latest.created_at AS last_message_at,
                    sender.full_name AS last_sender_name,
                    (
                        SELECT COUNT(*)
                        FROM chat_messages unread_messages
                        WHERE unread_messages.channel_id = chat_channels.id
                          AND unread_messages.sender_id <> :unread_user_id
                          AND (
                              chat_channel_members.last_read_at IS NULL
                              OR unread_messages.created_at > chat_channel_members.last_read_at
                          )
                    ) AS unread_count
             FROM chat_channel_members
             INNER JOIN chat_channels ON chat_channels.id = chat_channel_members.channel_id
             LEFT JOIN chat_messages latest ON latest.id = (
                SELECT chat_messages.id
                FROM chat_messages
                WHERE chat_messages.channel_id = chat_channels.id
                ORDER BY chat_messages.created_at DESC, chat_messages.id DESC
                LIMIT 1
             )
             LEFT JOIN users sender ON sender.id = latest.sender_id
             WHERE chat_channel_members.user_id = :user_id
             ORDER BY COALESCE(latest.created_at, chat_channels.created_at) DESC'
        );
        $statement->execute([
            'unread_user_id' => $userId,
            'user_id' => $userId,
        ]);

        return $statement->fetchAll();
    }

    public function allConversationsForAudit(): array
    {
        return $this->db->query(
            'SELECT chat_channels.*, classes.name AS class_name,
                    COUNT(DISTINCT chat_channel_members.user_id) AS members_count,
                    COUNT(DISTINCT chat_messages.id) AS messages_count,
                    MAX(chat_messages.created_at) AS last_message_at
             FROM chat_channels
             LEFT JOIN classes ON classes.id = chat_channels.class_id
             LEFT JOIN chat_channel_members ON chat_channel_members.channel_id = chat_channels.id
             LEFT JOIN chat_messages ON chat_messages.channel_id = chat_channels.id
             GROUP BY chat_channels.id, classes.name
             ORDER BY COALESCE(MAX(chat_messages.created_at), chat_channels.created_at) DESC'
        )->fetchAll();
    }

    public function findChannel(int $channelId): ?array
    {
        $statement = $this->db->prepare(
            'SELECT chat_channels.*, classes.name AS class_name
             FROM chat_channels
             LEFT JOIN classes ON classes.id = chat_channels.class_id
             WHERE chat_channels.id = :id
             LIMIT 1'
        );
        $statement->execute(['id' => $channelId]);
        $channel = $statement->fetch();

        return $channel ?: null;
    }

    public function canAccessChannel(int $channelId, array $user): bool
    {
        if (in_array($user['role_slug'], ['administrador', 'supervisor'], true)) {
            return true;
        }

        $statement = $this->db->prepare(
            'SELECT 1 FROM chat_channel_members WHERE channel_id = :channel_id AND user_id = :user_id LIMIT 1'
        );
        $statement->execute([
            'channel_id' => $channelId,
            'user_id' => (int) $user['id'],
        ]);

        return (bool) $statement->fetchColumn();
    }

    public function privateChannel(int $currentUserId, int $otherUserId): int
    {
        $recipient = $this->approvedUser($otherUserId);

        if (! $recipient) {
            throw new RuntimeException('Usuário indisponível para conversa.');
        }

        $statement = $this->db->prepare(
            'SELECT chat_channels.id
             FROM chat_channels
             INNER JOIN chat_channel_members first_member ON first_member.channel_id = chat_channels.id
             INNER JOIN chat_channel_members second_member ON second_member.channel_id = chat_channels.id
             WHERE chat_channels.channel_type = "privado"
               AND first_member.user_id = :current_user_id
               AND second_member.user_id = :other_user_id
             LIMIT 1'
        );
        $statement->execute([
            'current_user_id' => $currentUserId,
            'other_user_id' => $otherUserId,
        ]);
        $existing = $statement->fetchColumn();

        if ($existing) {
            return (int) $existing;
        }

        $this->db->beginTransaction();

        try {
            $insertChannel = $this->db->prepare(
                'INSERT INTO chat_channels (class_id, created_by, name, channel_type, created_at, updated_at)
                 VALUES (NULL, :created_by, :name, "privado", NOW(), NOW())'
            );
            $insertChannel->execute([
                'created_by' => $currentUserId,
                'name' => 'Conversa privada',
            ]);
            $channelId = (int) $this->db->lastInsertId();

            $this->addMember($channelId, $currentUserId);
            $this->addMember($channelId, $otherUserId);

            $this->db->commit();

            return $channelId;
        } catch (Throwable $exception) {
            $this->db->rollBack();
            throw $exception;
        }
    }

    public function messages(int $channelId): array
    {
        $statement = $this->db->prepare(
            'SELECT chat_messages.*, users.full_name AS sender_name, roles.slug AS sender_role
             FROM chat_messages
             INNER JOIN users ON users.id = chat_messages.sender_id
             INNER JOIN roles ON roles.id = users.role_id
             WHERE chat_messages.channel_id = :channel_id
             ORDER BY chat_messages.created_at ASC, chat_messages.id ASC'
        );
        $statement->execute(['channel_id' => $channelId]);

        return $statement->fetchAll();
    }

    public function members(int $channelId): array
    {
        $statement = $this->db->prepare(
            'SELECT users.id, users.full_name
             FROM chat_channel_members
             INNER JOIN users ON users.id = chat_channel_members.user_id
             WHERE chat_channel_members.channel_id = :channel_id
               AND users.status = "aprovado"'
        );
        $statement->execute(['channel_id' => $channelId]);

        return $statement->fetchAll();
    }

    public function sendMessage(int $channelId, int $senderId, string $message, ?array $attachment = null): int
    {
        $sender = $this->approvedUser($senderId);

        if (! $sender) {
            throw new RuntimeException('Somente usuários aprovados podem enviar mensagens.');
        }

        if ($attachment && ! $this->supportsAttachments()) {
            throw new RuntimeException('Aplique a migration de anexos do chat antes de enviar arquivos.');
        }

        if ($this->supportsAttachments()) {
            $statement = $this->db->prepare(
                'INSERT INTO chat_messages (
                    channel_id, sender_id, message, attachment_path, attachment_name,
                    attachment_type, attachment_size, created_at
                 ) VALUES (
                    :channel_id, :sender_id, :message, :attachment_path, :attachment_name,
                    :attachment_type, :attachment_size, NOW()
                 )'
            );
            $statement->execute([
                'channel_id' => $channelId,
                'sender_id' => $senderId,
                'message' => $message,
                'attachment_path' => $attachment['path'] ?? null,
                'attachment_name' => $attachment['name'] ?? null,
                'attachment_type' => $attachment['type'] ?? null,
                'attachment_size' => $attachment['size'] ?? null,
            ]);
        } else {
            $statement = $this->db->prepare(
                'INSERT INTO chat_messages (channel_id, sender_id, message, created_at)
                 VALUES (:channel_id, :sender_id, :message, NOW())'
            );
            $statement->execute([
                'channel_id' => $channelId,
                'sender_id' => $senderId,
                'message' => $message,
            ]);
        }

        $update = $this->db->prepare('UPDATE chat_channels SET updated_at = NOW() WHERE id = :id');
        $update->execute(['id' => $channelId]);

        return (int) $this->db->lastInsertId();
    }

    public function markRead(int $channelId, int $userId): void
    {
        $statement = $this->db->prepare(
            'UPDATE chat_channel_members
             SET last_read_at = NOW()
             WHERE channel_id = :channel_id AND user_id = :user_id'
        );
        $statement->execute([
            'channel_id' => $channelId,
            'user_id' => $userId,
        ]);

        $messages = $this->db->prepare(
            'UPDATE chat_messages
             SET read_at = COALESCE(read_at, NOW())
             WHERE channel_id = :channel_id AND sender_id <> :user_id'
        );
        $messages->execute([
            'channel_id' => $channelId,
            'user_id' => $userId,
        ]);
    }

    public function unreadTotal(int $userId): int
    {
        $statement = $this->db->prepare(
            'SELECT COUNT(*)
             FROM chat_channel_members
             INNER JOIN chat_messages ON chat_messages.channel_id = chat_channel_members.channel_id
             WHERE chat_channel_members.user_id = :user_id
               AND chat_messages.sender_id <> :sender_user_id
               AND (
                   chat_channel_members.last_read_at IS NULL
                   OR chat_messages.created_at > chat_channel_members.last_read_at
               )'
        );
        $statement->execute([
            'user_id' => $userId,
            'sender_user_id' => $userId,
        ]);

        return (int) $statement->fetchColumn();
    }

    private function ensureClassChannel(int $classId, string $className, int $createdBy): int
    {
        $statement = $this->db->prepare(
            'SELECT id FROM chat_channels WHERE class_id = :class_id AND channel_type = "turma" LIMIT 1'
        );
        $statement->execute(['class_id' => $classId]);
        $existing = $statement->fetchColumn();

        if ($existing) {
            return (int) $existing;
        }

        $insert = $this->db->prepare(
            'INSERT INTO chat_channels (class_id, created_by, name, channel_type, created_at, updated_at)
             VALUES (:class_id, :created_by, :name, "turma", NOW(), NOW())'
        );
        $insert->execute([
            'class_id' => $classId,
            'created_by' => $createdBy,
            'name' => 'Turma: ' . $className,
        ]);

        return (int) $this->db->lastInsertId();
    }

    private function syncClassMembers(int $classId, int $channelId): void
    {
        $studentStatement = $this->db->prepare(
            'SELECT user_id FROM class_students WHERE class_id = :class_id AND status = "ativo"'
        );
        $studentStatement->execute(['class_id' => $classId]);

        foreach ($studentStatement->fetchAll() as $member) {
            $this->addMember($channelId, (int) $member['user_id']);
        }

        $teacherStatement = $this->db->prepare(
            'SELECT user_id FROM class_teachers WHERE class_id = :class_id AND status = "ativo"
             UNION
             SELECT teacher_id AS user_id FROM class_subjects
             WHERE class_id = :subject_class_id AND teacher_id IS NOT NULL AND status = "ativa"'
        );
        $teacherStatement->execute([
            'class_id' => $classId,
            'subject_class_id' => $classId,
        ]);

        foreach ($teacherStatement->fetchAll() as $member) {
            $this->addMember($channelId, (int) $member['user_id']);
        }
    }

    private function addMember(int $channelId, int $userId): void
    {
        $statement = $this->db->prepare(
            'INSERT IGNORE INTO chat_channel_members (channel_id, user_id, joined_at)
             VALUES (:channel_id, :user_id, NOW())'
        );
        $statement->execute([
            'channel_id' => $channelId,
            'user_id' => $userId,
        ]);
    }

    private function approvedUser(int $userId): ?array
    {
        $statement = $this->db->prepare(
            'SELECT users.id, users.full_name, users.status, roles.slug AS role_slug
             FROM users
             INNER JOIN roles ON roles.id = users.role_id
             WHERE users.id = :id AND users.status = "aprovado"
             LIMIT 1'
        );
        $statement->execute(['id' => $userId]);
        $user = $statement->fetch();

        return $user ?: null;
    }

    private function supportsAttachments(): bool
    {
        static $supported = null;

        if ($supported !== null) {
            return $supported;
        }

        try {
            $statement = $this->db->query("SHOW COLUMNS FROM chat_messages LIKE 'attachment_path'");
            $supported = (bool) $statement->fetch();
        } catch (PDOException) {
            $supported = false;
        }

        return $supported;
    }
}
