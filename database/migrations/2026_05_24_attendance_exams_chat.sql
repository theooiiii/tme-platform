USE tme_platform;

CREATE TABLE IF NOT EXISTS attendance_records (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    class_id BIGINT UNSIGNED NOT NULL,
    subject_id BIGINT UNSIGNED NOT NULL,
    student_id BIGINT UNSIGNED NOT NULL,
    recorded_by BIGINT UNSIGNED NULL,
    attendance_date DATE NOT NULL,
    status ENUM('presente', 'falta', 'atraso', 'justificado') NOT NULL DEFAULT 'presente',
    note TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY attendance_session_student_unique (class_id, subject_id, student_id, attendance_date),
    KEY attendance_student_index (student_id),
    KEY attendance_date_index (attendance_date),
    KEY attendance_status_index (status),
    CONSTRAINT fk_attendance_class FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    CONSTRAINT fk_attendance_subject FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    CONSTRAINT fk_attendance_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_attendance_recorder FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE question_bank
    ADD COLUMN IF NOT EXISTS points DECIMAL(6,2) NOT NULL DEFAULT 1.00 AFTER correct_answer,
    ADD COLUMN IF NOT EXISTS explanation TEXT NULL AFTER points,
    ADD COLUMN IF NOT EXISTS status ENUM('ativa', 'inativa') NOT NULL DEFAULT 'ativa' AFTER difficulty;

ALTER TABLE exams
    ADD COLUMN IF NOT EXISTS subject_id BIGINT UNSIGNED NULL AFTER class_id,
    ADD COLUMN IF NOT EXISTS starts_at TIMESTAMP NULL AFTER time_limit_minutes,
    ADD COLUMN IF NOT EXISTS ends_at TIMESTAMP NULL AFTER starts_at,
    ADD COLUMN IF NOT EXISTS attempts_allowed TINYINT UNSIGNED NOT NULL DEFAULT 1 AFTER ends_at,
    ADD COLUMN IF NOT EXISTS ranking_enabled TINYINT(1) NOT NULL DEFAULT 0 AFTER auto_correction_enabled;

CREATE INDEX IF NOT EXISTS exams_subject_index ON exams (subject_id);
CREATE INDEX IF NOT EXISTS exams_status_window_index ON exams (status, starts_at, ends_at);

ALTER TABLE exam_attempts
    ADD COLUMN IF NOT EXISTS attempt_number TINYINT UNSIGNED NOT NULL DEFAULT 1 AFTER student_id,
    ADD COLUMN IF NOT EXISTS status ENUM('em_andamento', 'enviada', 'pendente_correcao', 'corrigida') NOT NULL DEFAULT 'em_andamento' AFTER answers,
    ADD COLUMN IF NOT EXISTS objective_score DECIMAL(6,2) NOT NULL DEFAULT 0.00 AFTER score,
    ADD COLUMN IF NOT EXISTS manual_score DECIMAL(6,2) NOT NULL DEFAULT 0.00 AFTER objective_score,
    ADD COLUMN IF NOT EXISTS total_score DECIMAL(6,2) NOT NULL DEFAULT 0.00 AFTER manual_score,
    ADD COLUMN IF NOT EXISTS submitted_at TIMESTAMP NULL AFTER started_at,
    ADD COLUMN IF NOT EXISTS graded_by BIGINT UNSIGNED NULL AFTER finished_at,
    ADD COLUMN IF NOT EXISTS graded_at TIMESTAMP NULL AFTER graded_by;

CREATE UNIQUE INDEX IF NOT EXISTS exam_attempts_exam_student_number_unique ON exam_attempts (exam_id, student_id, attempt_number);
CREATE INDEX IF NOT EXISTS exam_attempts_status_index ON exam_attempts (status);

CREATE TABLE IF NOT EXISTS exam_answers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    attempt_id BIGINT UNSIGNED NOT NULL,
    question_id BIGINT UNSIGNED NOT NULL,
    selected_option TEXT NULL,
    answer_text LONGTEXT NULL,
    is_correct TINYINT(1) NULL,
    score_awarded DECIMAL(6,2) NOT NULL DEFAULT 0.00,
    feedback TEXT NULL,
    status ENUM('pendente', 'corrigida') NOT NULL DEFAULT 'pendente',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY exam_answers_attempt_question_unique (attempt_id, question_id),
    KEY exam_answers_status_index (status),
    CONSTRAINT fk_exam_answers_attempt FOREIGN KEY (attempt_id) REFERENCES exam_attempts(id) ON DELETE CASCADE,
    CONSTRAINT fk_exam_answers_question FOREIGN KEY (question_id) REFERENCES question_bank(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE chat_channels
    ADD COLUMN IF NOT EXISTS created_by BIGINT UNSIGNED NULL AFTER class_id,
    ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

CREATE INDEX IF NOT EXISTS chat_channels_type_index ON chat_channels (channel_type);
CREATE INDEX IF NOT EXISTS chat_channels_class_index ON chat_channels (class_id);

ALTER TABLE chat_channel_members
    ADD COLUMN IF NOT EXISTS last_read_at TIMESTAMP NULL AFTER joined_at;

CREATE INDEX IF NOT EXISTS chat_members_user_index ON chat_channel_members (user_id);
CREATE INDEX IF NOT EXISTS chat_messages_channel_created_index ON chat_messages (channel_id, created_at);
