USE tme_platform;

ALTER TABLE enrollments
    ADD COLUMN IF NOT EXISTS last_activity_at TIMESTAMP NULL AFTER enrolled_at;

ALTER TABLE enrollments
    MODIFY status ENUM('ativo', 'concluido', 'cancelado', 'ativa', 'concluida', 'cancelada') NOT NULL DEFAULT 'ativa';

UPDATE enrollments SET status = 'ativa' WHERE status = 'ativo';
UPDATE enrollments SET status = 'concluida' WHERE status = 'concluido';
UPDATE enrollments SET status = 'cancelada' WHERE status = 'cancelado';

ALTER TABLE enrollments
    MODIFY status ENUM('ativa', 'concluida', 'cancelada') NOT NULL DEFAULT 'ativa';

UPDATE enrollments
SET last_activity_at = COALESCE(last_activity_at, enrolled_at, NOW())
WHERE last_activity_at IS NULL;

CREATE UNIQUE INDEX IF NOT EXISTS enrollments_user_course_unique ON enrollments(user_id, course_id);
CREATE INDEX IF NOT EXISTS enrollments_status_index ON enrollments(status);
CREATE INDEX IF NOT EXISTS enrollments_progress_index ON enrollments(progress_percent);

CREATE TABLE IF NOT EXISTS lesson_progress (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    enrollment_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    course_id BIGINT UNSIGNED NOT NULL,
    lesson_id BIGINT UNSIGNED NOT NULL,
    status ENUM('pendente', 'concluida') NOT NULL DEFAULT 'pendente',
    completed_at TIMESTAMP NULL,
    last_activity_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY lesson_progress_enrollment_lesson_unique (enrollment_id, lesson_id),
    KEY lesson_progress_user_course_index (user_id, course_id),
    KEY lesson_progress_status_index (status),
    CONSTRAINT fk_lesson_progress_enrollment FOREIGN KEY (enrollment_id) REFERENCES enrollments(id) ON DELETE CASCADE,
    CONSTRAINT fk_lesson_progress_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_lesson_progress_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    CONSTRAINT fk_lesson_progress_lesson FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
