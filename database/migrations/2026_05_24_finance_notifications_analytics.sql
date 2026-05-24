USE tme_platform;

ALTER TABLE plans
    ADD COLUMN IF NOT EXISTS duration_days SMALLINT UNSIGNED NOT NULL DEFAULT 30 AFTER billing_cycle,
    ADD COLUMN IF NOT EXISTS benefits JSON NULL AFTER features,
    ADD COLUMN IF NOT EXISTS is_premium TINYINT(1) NOT NULL DEFAULT 0 AFTER benefits,
    ADD COLUMN IF NOT EXISTS sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 1 AFTER is_premium;

ALTER TABLE transactions
    MODIFY status ENUM('pendente', 'pago', 'cancelado', 'expirado', 'estornado') NOT NULL DEFAULT 'pendente',
    ADD COLUMN IF NOT EXISTS subscription_id BIGINT UNSIGNED NULL AFTER plan_id,
    ADD COLUMN IF NOT EXISTS payment_method ENUM('manual', 'pix', 'cartao', 'interno') NOT NULL DEFAULT 'manual' AFTER status,
    ADD COLUMN IF NOT EXISTS gateway VARCHAR(80) NULL AFTER payment_method,
    ADD COLUMN IF NOT EXISTS gateway_reference VARCHAR(160) NULL AFTER gateway,
    ADD COLUMN IF NOT EXISTS due_at TIMESTAMP NULL AFTER reference,
    ADD COLUMN IF NOT EXISTS expires_at TIMESTAMP NULL AFTER due_at,
    ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

CREATE INDEX IF NOT EXISTS transactions_status_index ON transactions (status);
CREATE INDEX IF NOT EXISTS transactions_user_status_index ON transactions (user_id, status);
CREATE INDEX IF NOT EXISTS transactions_plan_index ON transactions (plan_id);

CREATE TABLE IF NOT EXISTS subscriptions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    plan_id BIGINT UNSIGNED NOT NULL,
    transaction_id BIGINT UNSIGNED NULL,
    status ENUM('pendente', 'ativa', 'cancelada', 'expirada') NOT NULL DEFAULT 'pendente',
    starts_at TIMESTAMP NULL,
    ends_at TIMESTAMP NULL,
    auto_renew TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY subscriptions_user_status_index (user_id, status),
    KEY subscriptions_plan_index (plan_id),
    CONSTRAINT fk_subscriptions_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_subscriptions_plan FOREIGN KEY (plan_id) REFERENCES plans(id) ON DELETE CASCADE,
    CONSTRAINT fk_subscriptions_transaction FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS creator_wallets (
    user_id BIGINT UNSIGNED PRIMARY KEY,
    available_balance DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    pending_balance DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    lifetime_earnings DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    platform_share_percent DECIMAL(5,2) NOT NULL DEFAULT 20.00,
    creator_share_percent DECIMAL(5,2) NOT NULL DEFAULT 80.00,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_creator_wallets_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE courses
    ADD COLUMN IF NOT EXISTS access_level ENUM('gratuito', 'premium') NOT NULL DEFAULT 'gratuito' AFTER price;

CREATE INDEX IF NOT EXISTS courses_access_level_index ON courses (access_level);

ALTER TABLE notifications
    ADD COLUMN IF NOT EXISTS action_url VARCHAR(255) NULL AFTER notification_type,
    ADD COLUMN IF NOT EXISTS metadata JSON NULL AFTER action_url,
    ADD COLUMN IF NOT EXISTS priority ENUM('baixa', 'normal', 'alta') NOT NULL DEFAULT 'normal' AFTER metadata,
    ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

CREATE INDEX IF NOT EXISTS notifications_user_read_index ON notifications (user_id, read_at);
CREATE INDEX IF NOT EXISTS notifications_type_index ON notifications (notification_type);

INSERT IGNORE INTO plans (name, description, price, billing_cycle, duration_days, features, benefits, is_premium, sort_order, status) VALUES
('TME Gratuito', 'Acesso inicial para estudar, participar da comunidade e usar recursos basicos.', 0.00, 'mensal', 30, JSON_ARRAY('Catalogo publico', 'Comunidade', 'Biblioteca publica'), JSON_ARRAY('Cursos gratuitos', 'Eventos abertos', 'Perfil e ranking'), 0, 1, 'ativo'),
('TME Premium Mensal', 'Plano premium para liberar cursos e recursos avancados da plataforma.', 39.90, 'mensal', 30, JSON_ARRAY('Cursos premium', 'Certificados', 'Analytics pessoal'), JSON_ARRAY('Acesso premium', 'Provas e simulados avancados', 'Suporte academico futuro'), 1, 2, 'ativo'),
('TME Premium Anual', 'Plano anual com acesso premium e melhor custo-beneficio.', 399.00, 'anual', 365, JSON_ARRAY('Cursos premium', 'Certificados', 'Analytics pessoal'), JSON_ARRAY('Acesso premium por 12 meses', 'Recursos avancados', 'Prioridade em eventos futuros'), 1, 3, 'ativo');
