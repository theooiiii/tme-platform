USE tme_platform;

ALTER TABLE courses
    ADD COLUMN IF NOT EXISTS responsible_teacher_id BIGINT UNSIGNED NULL AFTER creator_id,
    ADD COLUMN IF NOT EXISTS category VARCHAR(120) NOT NULL DEFAULT 'Geral' AFTER description,
    ADD COLUMN IF NOT EXISTS level ENUM('iniciante', 'intermediario', 'avancado', 'livre') NOT NULL DEFAULT 'livre' AFTER category,
    ADD COLUMN IF NOT EXISTS workload_hours SMALLINT UNSIGNED NOT NULL DEFAULT 0 AFTER level,
    ADD COLUMN IF NOT EXISTS image_path VARCHAR(255) NULL AFTER price;

ALTER TABLE lessons
    ADD COLUMN IF NOT EXISTS description TEXT NULL AFTER title,
    ADD COLUMN IF NOT EXISTS lesson_type ENUM('video', 'texto', 'ao_vivo', 'arquivo', 'link') NOT NULL DEFAULT 'video' AFTER description;

ALTER TABLE materials
    ADD COLUMN IF NOT EXISTS description TEXT NULL AFTER title,
    MODIFY material_type ENUM('pdf', 'imagem', 'link', 'arquivo', 'livro', 'apostila', 'video') NOT NULL DEFAULT 'arquivo',
    ADD COLUMN IF NOT EXISTS status ENUM('ativo', 'inativo') NOT NULL DEFAULT 'ativo' AFTER external_url;

CREATE INDEX IF NOT EXISTS courses_category_index ON courses(category);
CREATE INDEX IF NOT EXISTS courses_teacher_index ON courses(responsible_teacher_id);
