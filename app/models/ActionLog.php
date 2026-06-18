<?php

defined('BASE_PATH') || exit('Acesso direto não permitido.');

class ActionLog extends Model
{
    public function record(?int $userId, string $action, array $context = [], string $level = 'info'): void
    {
        $statement = $this->db->prepare(
            'INSERT INTO logs (user_id, level, action, context, ip_address, user_agent, created_at)
             VALUES (:user_id, :level, :action, :context, :ip_address, :user_agent, NOW())'
        );

        $statement->execute([
            'user_id' => $userId,
            'level' => in_array($level, ['info', 'warning', 'error', 'security'], true) ? $level : 'info',
            'action' => $action,
            'context' => json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
        ]);
    }
}
