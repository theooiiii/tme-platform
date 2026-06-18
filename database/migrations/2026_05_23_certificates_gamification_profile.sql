USE tme_platform;

ALTER TABLE certificates
    ADD COLUMN IF NOT EXISTS enrollment_id BIGINT UNSIGNED NULL AFTER user_id,
    ADD COLUMN IF NOT EXISTS certificate_type ENUM('curso', 'evento') NOT NULL DEFAULT 'curso' AFTER course_id,
    ADD COLUMN IF NOT EXISTS workload_hours SMALLINT UNSIGNED NOT NULL DEFAULT 0 AFTER title,
    ADD COLUMN IF NOT EXISTS revoked_by BIGINT UNSIGNED NULL AFTER qr_code_path,
    ADD COLUMN IF NOT EXISTS revoked_at TIMESTAMP NULL AFTER revoked_by,
    ADD COLUMN IF NOT EXISTS revocation_reason VARCHAR(255) NULL AFTER revoked_at,
    ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

CREATE INDEX IF NOT EXISTS certificates_user_index ON certificates (user_id);
CREATE INDEX IF NOT EXISTS certificates_course_index ON certificates (course_id);
CREATE INDEX IF NOT EXISTS certificates_enrollment_index ON certificates (enrollment_id);
CREATE INDEX IF NOT EXISTS certificates_status_index ON certificates (validation_status);
CREATE UNIQUE INDEX IF NOT EXISTS certificates_enrollment_unique ON certificates (enrollment_id);

ALTER TABLE user_settings
    ADD COLUMN IF NOT EXISTS bio_short VARCHAR(280) NULL AFTER primary_color,
    ADD COLUMN IF NOT EXISTS avatar_path VARCHAR(255) NULL AFTER bio_short;

ALTER TABLE gamification_profiles
    ADD COLUMN IF NOT EXISTS xp_total INT UNSIGNED NOT NULL DEFAULT 0 AFTER xp,
    ADD COLUMN IF NOT EXISTS streak_days INT UNSIGNED NOT NULL DEFAULT 0 AFTER internal_coins,
    ADD COLUMN IF NOT EXISTS last_activity_at TIMESTAMP NULL AFTER streak_days;

UPDATE gamification_profiles SET xp_total = xp WHERE xp_total = 0 AND xp > 0;

CREATE TABLE IF NOT EXISTS gamification_events (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    action VARCHAR(120) NOT NULL,
    reference_type VARCHAR(80) NULL,
    reference_id BIGINT UNSIGNED NULL,
    xp_awarded INT UNSIGNED NOT NULL DEFAULT 0,
    coins_awarded INT UNSIGNED NOT NULL DEFAULT 0,
    context JSON NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY gamification_event_unique (user_id, action, reference_type, reference_id),
    KEY gamification_events_user_index (user_id),
    KEY gamification_events_action_index (action),
    CONSTRAINT fk_gamification_events_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO gamification_profiles (user_id, xp, xp_total, level, internal_coins, streak_days, created_at, updated_at)
SELECT id, 0, 0, 1, 0, 0, NOW(), NOW()
FROM users
WHERE status = 'aprovado';

INSERT IGNORE INTO badges (slug, name, description, xp_reward) VALUES
('primeiro-login', 'Primeiro Login', 'Primeiro acesso aprovado na TME.', 25),
('primeiro-curso', 'Primeiro Curso', 'Primeira matricula realizada em um curso.', 40),
('primeira-aula-concluida', 'Primeira Aula Concluída', 'Primeira aula marcada como concluída.', 40),
('curso-finalizado', 'Curso Finalizado', 'Primeiro curso concluído na plataforma.', 100),
('explorador-biblioteca', 'Explorador da Biblioteca', 'Primeiro material favoritado na biblioteca.', 35),
('aluno-dedicado', 'Aluno Dedicado', 'Marcou pelo menos cinco aulas como concluidas.', 120);
