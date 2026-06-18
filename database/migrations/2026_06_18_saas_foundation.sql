USE tme_platform;

CREATE TABLE IF NOT EXISTS organizations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(180) NOT NULL,
    legal_name VARCHAR(180) NULL,
    slug VARCHAR(140) NOT NULL UNIQUE,
    organization_type ENUM('escola', 'universidade', 'empresa', 'curso_livre', 'ong', 'governo', 'outro') NOT NULL DEFAULT 'curso_livre',
    status ENUM('trial', 'ativa', 'suspensa', 'cancelada') NOT NULL DEFAULT 'trial',
    owner_user_id BIGINT UNSIGNED NULL,
    logo_path VARCHAR(255) NULL,
    primary_domain VARCHAR(180) NULL UNIQUE,
    primary_color VARCHAR(20) NULL,
    theme_mode ENUM('light', 'dark', 'system') NOT NULL DEFAULT 'light',
    settings JSON NULL,
    trial_ends_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY organizations_status_index (status),
    KEY organizations_type_index (organization_type),
    KEY organizations_owner_index (owner_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS organization_domains (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_id BIGINT UNSIGNED NOT NULL,
    domain VARCHAR(180) NOT NULL UNIQUE,
    status ENUM('pendente', 'verificado', 'bloqueado') NOT NULL DEFAULT 'pendente',
    verification_token VARCHAR(120) NULL,
    verified_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    KEY organization_domains_organization_index (organization_id),
    CONSTRAINT fk_organization_domains_organization FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS organization_members (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    role_slug VARCHAR(80) NOT NULL,
    status ENUM('ativo', 'pendente', 'suspenso') NOT NULL DEFAULT 'ativo',
    joined_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY organization_members_unique (organization_id, user_id),
    KEY organization_members_role_index (organization_id, role_slug),
    KEY organization_members_user_index (user_id),
    CONSTRAINT fk_organization_members_organization FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
    CONSTRAINT fk_organization_members_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE users ADD COLUMN IF NOT EXISTS organization_id BIGINT UNSIGNED NULL AFTER institution_id;
ALTER TABLE users ADD INDEX IF NOT EXISTS users_organization_status_index (organization_id, status);
ALTER TABLE institutions ADD COLUMN IF NOT EXISTS organization_id BIGINT UNSIGNED NULL AFTER id;
ALTER TABLE institutions ADD INDEX IF NOT EXISTS institutions_organization_index (organization_id);
ALTER TABLE courses ADD COLUMN IF NOT EXISTS organization_id BIGINT UNSIGNED NULL AFTER id;
ALTER TABLE courses ADD INDEX IF NOT EXISTS courses_organization_status_index (organization_id, status);
ALTER TABLE classes ADD COLUMN IF NOT EXISTS organization_id BIGINT UNSIGNED NULL AFTER id;
ALTER TABLE classes ADD INDEX IF NOT EXISTS classes_organization_status_index (organization_id, status);
ALTER TABLE subjects ADD COLUMN IF NOT EXISTS organization_id BIGINT UNSIGNED NULL AFTER id;
ALTER TABLE subjects ADD INDEX IF NOT EXISTS subjects_organization_status_index (organization_id, status);
ALTER TABLE library_items ADD COLUMN IF NOT EXISTS organization_id BIGINT UNSIGNED NULL AFTER id;
ALTER TABLE library_items ADD INDEX IF NOT EXISTS library_items_organization_status_index (organization_id, status);
ALTER TABLE events ADD COLUMN IF NOT EXISTS organization_id BIGINT UNSIGNED NULL AFTER id;
ALTER TABLE events ADD INDEX IF NOT EXISTS events_organization_status_index (organization_id, status);
ALTER TABLE posts ADD COLUMN IF NOT EXISTS organization_id BIGINT UNSIGNED NULL AFTER id;
ALTER TABLE posts ADD INDEX IF NOT EXISTS posts_organization_status_index (organization_id, status);
ALTER TABLE plans ADD COLUMN IF NOT EXISTS organization_id BIGINT UNSIGNED NULL AFTER id;
ALTER TABLE plans ADD INDEX IF NOT EXISTS plans_organization_status_index (organization_id, status);
ALTER TABLE transactions ADD COLUMN IF NOT EXISTS organization_id BIGINT UNSIGNED NULL AFTER id;
ALTER TABLE transactions ADD INDEX IF NOT EXISTS transactions_organization_status_index (organization_id, status);
ALTER TABLE notifications ADD COLUMN IF NOT EXISTS organization_id BIGINT UNSIGNED NULL AFTER id;
ALTER TABLE notifications ADD INDEX IF NOT EXISTS notifications_organization_user_index (organization_id, user_id, read_at);
ALTER TABLE chat_channels ADD COLUMN IF NOT EXISTS organization_id BIGINT UNSIGNED NULL AFTER id;
ALTER TABLE chat_channels ADD INDEX IF NOT EXISTS chat_channels_organization_index (organization_id);
ALTER TABLE logs ADD COLUMN IF NOT EXISTS organization_id BIGINT UNSIGNED NULL AFTER id;
ALTER TABLE logs ADD INDEX IF NOT EXISTS logs_organization_action_index (organization_id, action, created_at);

CREATE TABLE IF NOT EXISTS password_resets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    token_hash CHAR(64) NOT NULL UNIQUE,
    expires_at TIMESTAMP NOT NULL,
    used_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    KEY password_resets_user_index (user_id),
    CONSTRAINT fk_password_resets_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS login_attempts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_id BIGINT UNSIGNED NULL,
    email VARCHAR(160) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent VARCHAR(255) NULL,
    successful TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    KEY login_attempts_email_ip_index (email, ip_address, created_at),
    KEY login_attempts_organization_index (organization_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS crm_leads (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_id BIGINT UNSIGNED NULL,
    owner_user_id BIGINT UNSIGNED NULL,
    name VARCHAR(180) NOT NULL,
    email VARCHAR(180) NULL,
    phone VARCHAR(40) NULL,
    source VARCHAR(80) NULL,
    stage ENUM('novo', 'contato', 'interessado', 'proposta', 'matriculado', 'perdido') NOT NULL DEFAULT 'novo',
    value DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    tags JSON NULL,
    notes TEXT NULL,
    next_action_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY crm_leads_organization_stage_index (organization_id, stage),
    KEY crm_leads_owner_index (owner_user_id),
    CONSTRAINT fk_crm_leads_owner FOREIGN KEY (owner_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS crm_lead_contacts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lead_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NULL,
    contact_type ENUM('email', 'telefone', 'whatsapp', 'reuniao', 'nota') NOT NULL DEFAULT 'nota',
    summary VARCHAR(255) NOT NULL,
    details TEXT NULL,
    contacted_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    KEY crm_lead_contacts_lead_index (lead_id, contacted_at),
    CONSTRAINT fk_crm_lead_contacts_lead FOREIGN KEY (lead_id) REFERENCES crm_leads(id) ON DELETE CASCADE,
    CONSTRAINT fk_crm_lead_contacts_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS support_tickets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_id BIGINT UNSIGNED NULL,
    requester_id BIGINT UNSIGNED NOT NULL,
    assignee_id BIGINT UNSIGNED NULL,
    subject VARCHAR(180) NOT NULL,
    category VARCHAR(80) NULL,
    priority ENUM('baixa', 'normal', 'alta', 'critica') NOT NULL DEFAULT 'normal',
    status ENUM('aberto', 'em_andamento', 'aguardando_usuario', 'resolvido', 'fechado') NOT NULL DEFAULT 'aberto',
    sla_due_at TIMESTAMP NULL,
    closed_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY support_tickets_organization_status_index (organization_id, status),
    KEY support_tickets_requester_index (requester_id),
    CONSTRAINT fk_support_tickets_requester FOREIGN KEY (requester_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_support_tickets_assignee FOREIGN KEY (assignee_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS support_ticket_messages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ticket_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    message TEXT NOT NULL,
    is_internal TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    KEY support_ticket_messages_ticket_index (ticket_id, created_at),
    CONSTRAINT fk_support_ticket_messages_ticket FOREIGN KEY (ticket_id) REFERENCES support_tickets(id) ON DELETE CASCADE,
    CONSTRAINT fk_support_ticket_messages_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS automation_jobs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_id BIGINT UNSIGNED NULL,
    job_type VARCHAR(120) NOT NULL,
    payload JSON NOT NULL,
    status ENUM('pendente', 'processando', 'concluido', 'falhou', 'cancelado') NOT NULL DEFAULT 'pendente',
    attempts SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    available_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    processed_at TIMESTAMP NULL,
    last_error TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    KEY automation_jobs_status_available_index (status, available_at),
    KEY automation_jobs_organization_index (organization_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS api_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_id BIGINT UNSIGNED NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(120) NOT NULL,
    token_hash CHAR(64) NOT NULL UNIQUE,
    abilities JSON NULL,
    last_used_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    revoked_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    KEY api_tokens_user_index (user_id),
    KEY api_tokens_organization_index (organization_id),
    CONSTRAINT fk_api_tokens_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS webhook_endpoints (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_id BIGINT UNSIGNED NULL,
    name VARCHAR(120) NOT NULL,
    target_url VARCHAR(255) NOT NULL,
    secret_hash CHAR(64) NOT NULL,
    events JSON NOT NULL,
    status ENUM('ativo', 'pausado', 'falhou') NOT NULL DEFAULT 'ativo',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY webhook_endpoints_organization_status_index (organization_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS audit_events (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_id BIGINT UNSIGNED NULL,
    user_id BIGINT UNSIGNED NULL,
    event_type VARCHAR(120) NOT NULL,
    entity_type VARCHAR(120) NULL,
    entity_id BIGINT UNSIGNED NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    metadata JSON NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    KEY audit_events_organization_event_index (organization_id, event_type, created_at),
    KEY audit_events_user_index (user_id, created_at),
    KEY audit_events_entity_index (entity_type, entity_id),
    CONSTRAINT fk_audit_events_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
