CREATE DATABASE IF NOT EXISTS tme_platform
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE tme_platform;

CREATE TABLE IF NOT EXISTS roles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(40) NOT NULL UNIQUE,
    name VARCHAR(80) NOT NULL,
    description VARCHAR(255) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS permissions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(80) NOT NULL UNIQUE,
    name VARCHAR(120) NOT NULL,
    description VARCHAR(255) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS role_permissions (
    role_id INT UNSIGNED NOT NULL,
    permission_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (role_id, permission_id),
    CONSTRAINT fk_role_permissions_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    CONSTRAINT fk_role_permissions_permission FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS institutions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    source ENUM('manual', 'inep', 'emec') NOT NULL DEFAULT 'manual',
    external_code VARCHAR(80) NULL,
    name VARCHAR(180) NOT NULL,
    legal_name VARCHAR(220) NULL,
    institution_type ENUM('escola', 'ensino_superior', 'curso_livre', 'outra') NOT NULL DEFAULT 'outra',
    state CHAR(2) NULL,
    city VARCHAR(120) NULL,
    address VARCHAR(255) NULL,
    verification_status ENUM('nao_verificada', 'verificada', 'arquivada') NOT NULL DEFAULT 'nao_verificada',
    imported_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY institutions_source_code_unique (source, external_code),
    KEY institutions_name_index (name),
    KEY institutions_location_index (state, city)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_id INT UNSIGNED NOT NULL,
    institution_id BIGINT UNSIGNED NULL,
    full_name VARCHAR(160) NOT NULL,
    email VARCHAR(160) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(30) NOT NULL,
    cpf VARCHAR(14) NOT NULL UNIQUE,
    birth_date DATE NOT NULL,
    state CHAR(2) NOT NULL,
    city VARCHAR(120) NOT NULL,
    is_independent TINYINT(1) NOT NULL DEFAULT 0,
    interest_area VARCHAR(160) NOT NULL,
    platform_goal VARCHAR(255) NOT NULL,
    terms_accepted_at TIMESTAMP NULL,
    status ENUM('pendente', 'aprovado', 'recusado', 'inativo') NOT NULL DEFAULT 'pendente',
    approved_by BIGINT UNSIGNED NULL,
    approved_at TIMESTAMP NULL,
    rejection_reason VARCHAR(255) NULL,
    last_login_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY users_status_index (status),
    KEY users_role_index (role_id),
    KEY users_institution_index (institution_id),
    CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id),
    CONSTRAINT fk_users_institution FOREIGN KEY (institution_id) REFERENCES institutions(id) ON DELETE SET NULL,
    CONSTRAINT fk_users_approved_by FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_settings (
    user_id BIGINT UNSIGNED PRIMARY KEY,
    theme ENUM('light', 'dark') NOT NULL DEFAULT 'light',
    primary_color CHAR(7) NOT NULL DEFAULT '#1f6feb',
    bio_short VARCHAR(280) NULL,
    avatar_path VARCHAR(255) NULL,
    notifications_enabled TINYINT(1) NOT NULL DEFAULT 1,
    density ENUM('comfortable', 'compact') NOT NULL DEFAULT 'comfortable',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_user_settings_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS courses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    institution_id BIGINT UNSIGNED NULL,
    creator_id BIGINT UNSIGNED NULL,
    responsible_teacher_id BIGINT UNSIGNED NULL,
    title VARCHAR(180) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    description TEXT NULL,
    category VARCHAR(120) NOT NULL DEFAULT 'Geral',
    level ENUM('iniciante', 'intermediario', 'avancado', 'livre') NOT NULL DEFAULT 'livre',
    workload_hours SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    visibility ENUM('publico', 'privado', 'institucional') NOT NULL DEFAULT 'privado',
    status ENUM('rascunho', 'publicado', 'arquivado') NOT NULL DEFAULT 'rascunho',
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    access_level ENUM('gratuito', 'premium') NOT NULL DEFAULT 'gratuito',
    image_path VARCHAR(255) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY courses_status_index (status),
    KEY courses_access_level_index (access_level),
    KEY courses_category_index (category),
    KEY courses_teacher_index (responsible_teacher_id),
    CONSTRAINT fk_courses_institution FOREIGN KEY (institution_id) REFERENCES institutions(id) ON DELETE SET NULL,
    CONSTRAINT fk_courses_creator FOREIGN KEY (creator_id) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_courses_responsible_teacher FOREIGN KEY (responsible_teacher_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS course_modules (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(180) NOT NULL,
    description TEXT NULL,
    position SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_course_modules_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS lessons (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_id BIGINT UNSIGNED NOT NULL,
    module_id BIGINT UNSIGNED NULL,
    title VARCHAR(180) NOT NULL,
    description TEXT NULL,
    lesson_type ENUM('video', 'texto', 'ao_vivo', 'arquivo', 'link') NOT NULL DEFAULT 'video',
    content LONGTEXT NULL,
    video_url VARCHAR(255) NULL,
    duration_minutes SMALLINT UNSIGNED NULL,
    position SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    status ENUM('rascunho', 'publicada', 'arquivada') NOT NULL DEFAULT 'rascunho',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_lessons_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    CONSTRAINT fk_lessons_module FOREIGN KEY (module_id) REFERENCES course_modules(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS materials (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_id BIGINT UNSIGNED NULL,
    module_id BIGINT UNSIGNED NULL,
    lesson_id BIGINT UNSIGNED NULL,
    owner_id BIGINT UNSIGNED NULL,
    title VARCHAR(180) NOT NULL,
    description TEXT NULL,
    material_type ENUM('pdf', 'imagem', 'link', 'arquivo', 'livro', 'apostila', 'video') NOT NULL DEFAULT 'arquivo',
    visibility ENUM('publico', 'privado', 'institucional') NOT NULL DEFAULT 'privado',
    file_path VARCHAR(255) NULL,
    external_url VARCHAR(255) NULL,
    status ENUM('ativo', 'inativo') NOT NULL DEFAULT 'ativo',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_materials_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL,
    CONSTRAINT fk_materials_module FOREIGN KEY (module_id) REFERENCES course_modules(id) ON DELETE SET NULL,
    CONSTRAINT fk_materials_lesson FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE SET NULL,
    CONSTRAINT fk_materials_owner FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS library_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    owner_id BIGINT UNSIGNED NULL,
    approved_by BIGINT UNSIGNED NULL,
    course_id BIGINT UNSIGNED NULL,
    class_id BIGINT UNSIGNED NULL,
    title VARCHAR(180) NOT NULL,
    description TEXT NULL,
    category VARCHAR(120) NULL,
    subject VARCHAR(120) NULL,
    item_type ENUM('pdf', 'livro', 'apostila', 'artigo', 'video', 'link', 'apresentacao', 'imagem', 'arquivo') NOT NULL DEFAULT 'arquivo',
    visibility ENUM('publica', 'logados', 'curso', 'privada_admin') NOT NULL DEFAULT 'publica',
    author VARCHAR(160) NULL,
    file_path VARCHAR(255) NULL,
    external_url VARCHAR(255) NULL,
    cover_path VARCHAR(255) NULL,
    status ENUM('rascunho', 'pendente', 'publicado', 'arquivado', 'recusado') NOT NULL DEFAULT 'pendente',
    moderation_notes VARCHAR(255) NULL,
    approved_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY library_status_index (status),
    KEY library_visibility_index (visibility),
    KEY library_category_index (category),
    KEY library_subject_index (subject),
    KEY library_type_index (item_type),
    CONSTRAINT fk_library_owner FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_library_approved_by FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_library_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL,
    CONSTRAINT fk_library_class FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS library_favorites (
    user_id BIGINT UNSIGNED NOT NULL,
    library_item_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, library_item_id),
    CONSTRAINT fk_library_favorites_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_library_favorites_item FOREIGN KEY (library_item_id) REFERENCES library_items(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS library_access_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    library_item_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NULL,
    accessed_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    KEY library_access_item_index (library_item_id),
    KEY library_access_user_index (user_id),
    CONSTRAINT fk_library_access_item FOREIGN KEY (library_item_id) REFERENCES library_items(id) ON DELETE CASCADE,
    CONSTRAINT fk_library_access_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS classes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    institution_id BIGINT UNSIGNED NULL,
    name VARCHAR(140) NOT NULL,
    code VARCHAR(60) NULL UNIQUE,
    description TEXT NULL,
    period VARCHAR(80) NULL,
    status ENUM('ativa', 'inativa', 'arquivada') NOT NULL DEFAULT 'ativa',
    academic_year SMALLINT UNSIGNED NULL,
    starts_at DATE NULL,
    ends_at DATE NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_classes_institution FOREIGN KEY (institution_id) REFERENCES institutions(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS subjects (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    class_id BIGINT UNSIGNED NULL,
    teacher_id BIGINT UNSIGNED NULL,
    name VARCHAR(140) NOT NULL,
    description TEXT NULL,
    area VARCHAR(120) NULL,
    workload_hours SMALLINT UNSIGNED NULL,
    status ENUM('ativa', 'inativa', 'arquivada') NOT NULL DEFAULT 'ativa',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_subjects_class FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE SET NULL,
    CONSTRAINT fk_subjects_teacher FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS class_students (
    class_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    status ENUM('ativo', 'inativo') NOT NULL DEFAULT 'ativo',
    linked_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (class_id, user_id),
    CONSTRAINT fk_class_students_class FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    CONSTRAINT fk_class_students_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS class_teachers (
    class_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    status ENUM('ativo', 'inativo') NOT NULL DEFAULT 'ativo',
    linked_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (class_id, user_id),
    CONSTRAINT fk_class_teachers_class FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    CONSTRAINT fk_class_teachers_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS class_subjects (
    class_id BIGINT UNSIGNED NOT NULL,
    subject_id BIGINT UNSIGNED NOT NULL,
    teacher_id BIGINT UNSIGNED NULL,
    status ENUM('ativa', 'inativa') NOT NULL DEFAULT 'ativa',
    linked_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (class_id, subject_id),
    CONSTRAINT fk_class_subjects_class FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    CONSTRAINT fk_class_subjects_subject FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    CONSTRAINT fk_class_subjects_teacher FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS subject_teachers (
    subject_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    status ENUM('ativo', 'inativo') NOT NULL DEFAULT 'ativo',
    linked_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (subject_id, user_id),
    CONSTRAINT fk_subject_teachers_subject FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    CONSTRAINT fk_subject_teachers_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS enrollments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    course_id BIGINT UNSIGNED NULL,
    class_id BIGINT UNSIGNED NULL,
    subject_id BIGINT UNSIGNED NULL,
    role ENUM('aluno', 'professor') NOT NULL DEFAULT 'aluno',
    status ENUM('ativa', 'concluida', 'cancelada') NOT NULL DEFAULT 'ativa',
    progress_percent DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    enrolled_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    last_activity_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    UNIQUE KEY enrollments_user_course_unique (user_id, course_id),
    KEY enrollments_status_index (status),
    KEY enrollments_progress_index (progress_percent),
    CONSTRAINT fk_enrollments_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_enrollments_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    CONSTRAINT fk_enrollments_class FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    CONSTRAINT fk_enrollments_subject FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

CREATE TABLE IF NOT EXISTS activities (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_id BIGINT UNSIGNED NULL,
    module_id BIGINT UNSIGNED NULL,
    lesson_id BIGINT UNSIGNED NULL,
    class_id BIGINT UNSIGNED NULL,
    subject_id BIGINT UNSIGNED NULL,
    teacher_id BIGINT UNSIGNED NULL,
    title VARCHAR(180) NOT NULL,
    description TEXT NULL,
    instructions LONGTEXT NULL,
    activity_type ENUM('texto', 'arquivo', 'quiz', 'tarefa_pratica', 'projeto', 'atividade', 'prova', 'forum') NOT NULL DEFAULT 'texto',
    due_at TIMESTAMP NULL,
    max_score DECIMAL(6,2) NOT NULL DEFAULT 10.00,
    allow_late TINYINT(1) NOT NULL DEFAULT 1,
    attachment_path VARCHAR(255) NULL,
    status ENUM('rascunho', 'publicada', 'encerrada') NOT NULL DEFAULT 'rascunho',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY activities_status_index (status),
    KEY activities_due_index (due_at),
    CONSTRAINT fk_activities_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL,
    CONSTRAINT fk_activities_module FOREIGN KEY (module_id) REFERENCES course_modules(id) ON DELETE SET NULL,
    CONSTRAINT fk_activities_lesson FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE SET NULL,
    CONSTRAINT fk_activities_class FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE SET NULL,
    CONSTRAINT fk_activities_subject FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE SET NULL,
    CONSTRAINT fk_activities_teacher FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS submissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    activity_id BIGINT UNSIGNED NOT NULL,
    student_id BIGINT UNSIGNED NOT NULL,
    content LONGTEXT NULL,
    file_path VARCHAR(255) NULL,
    status ENUM('pendente', 'enviada', 'atrasada', 'corrigida', 'devolvida') NOT NULL DEFAULT 'enviada',
    submitted_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY submissions_activity_student_unique (activity_id, student_id),
    KEY submissions_status_index (status),
    CONSTRAINT fk_submissions_activity FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE CASCADE,
    CONSTRAINT fk_submissions_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS grades (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    submission_id BIGINT UNSIGNED NULL,
    activity_id BIGINT UNSIGNED NOT NULL,
    student_id BIGINT UNSIGNED NOT NULL,
    teacher_id BIGINT UNSIGNED NULL,
    score DECIMAL(6,2) NOT NULL,
    feedback TEXT NULL,
    graded_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY grades_activity_student_unique (activity_id, student_id),
    CONSTRAINT fk_grades_submission FOREIGN KEY (submission_id) REFERENCES submissions(id) ON DELETE SET NULL,
    CONSTRAINT fk_grades_activity FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE CASCADE,
    CONSTRAINT fk_grades_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_grades_teacher FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

CREATE TABLE IF NOT EXISTS question_bank (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    creator_id BIGINT UNSIGNED NULL,
    subject_id BIGINT UNSIGNED NULL,
    statement_text TEXT NOT NULL,
    question_type ENUM('objetiva', 'discursiva') NOT NULL DEFAULT 'objetiva',
    alternatives JSON NULL,
    correct_answer TEXT NULL,
    points DECIMAL(6,2) NOT NULL DEFAULT 1.00,
    explanation TEXT NULL,
    difficulty ENUM('facil', 'media', 'dificil') NOT NULL DEFAULT 'media',
    status ENUM('ativa', 'inativa') NOT NULL DEFAULT 'ativa',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_question_bank_creator FOREIGN KEY (creator_id) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_question_bank_subject FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS exams (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_id BIGINT UNSIGNED NULL,
    class_id BIGINT UNSIGNED NULL,
    subject_id BIGINT UNSIGNED NULL,
    creator_id BIGINT UNSIGNED NULL,
    title VARCHAR(180) NOT NULL,
    description TEXT NULL,
    time_limit_minutes SMALLINT UNSIGNED NULL,
    starts_at TIMESTAMP NULL,
    ends_at TIMESTAMP NULL,
    attempts_allowed TINYINT UNSIGNED NOT NULL DEFAULT 1,
    auto_correction_enabled TINYINT(1) NOT NULL DEFAULT 0,
    ranking_enabled TINYINT(1) NOT NULL DEFAULT 0,
    status ENUM('rascunho', 'publicado', 'encerrado') NOT NULL DEFAULT 'rascunho',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY exams_subject_index (subject_id),
    KEY exams_status_window_index (status, starts_at, ends_at),
    CONSTRAINT fk_exams_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL,
    CONSTRAINT fk_exams_class FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE SET NULL,
    CONSTRAINT fk_exams_subject FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE SET NULL,
    CONSTRAINT fk_exams_creator FOREIGN KEY (creator_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS exam_questions (
    exam_id BIGINT UNSIGNED NOT NULL,
    question_id BIGINT UNSIGNED NOT NULL,
    position SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    score DECIMAL(6,2) NOT NULL DEFAULT 1.00,
    PRIMARY KEY (exam_id, question_id),
    CONSTRAINT fk_exam_questions_exam FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE,
    CONSTRAINT fk_exam_questions_question FOREIGN KEY (question_id) REFERENCES question_bank(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS exam_attempts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    exam_id BIGINT UNSIGNED NOT NULL,
    student_id BIGINT UNSIGNED NOT NULL,
    attempt_number TINYINT UNSIGNED NOT NULL DEFAULT 1,
    answers JSON NULL,
    status ENUM('em_andamento', 'enviada', 'pendente_correcao', 'corrigida') NOT NULL DEFAULT 'em_andamento',
    score DECIMAL(6,2) NULL,
    objective_score DECIMAL(6,2) NOT NULL DEFAULT 0.00,
    manual_score DECIMAL(6,2) NOT NULL DEFAULT 0.00,
    total_score DECIMAL(6,2) NOT NULL DEFAULT 0.00,
    started_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    submitted_at TIMESTAMP NULL,
    finished_at TIMESTAMP NULL,
    graded_by BIGINT UNSIGNED NULL,
    graded_at TIMESTAMP NULL,
    UNIQUE KEY exam_attempts_exam_student_number_unique (exam_id, student_id, attempt_number),
    KEY exam_attempts_status_index (status),
    CONSTRAINT fk_exam_attempts_exam FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE,
    CONSTRAINT fk_exam_attempts_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_exam_attempts_grader FOREIGN KEY (graded_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

CREATE TABLE IF NOT EXISTS posts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    post_type ENUM('duvida', 'artigo', 'projeto', 'material', 'conquista', 'aviso') NOT NULL DEFAULT 'duvida',
    title VARCHAR(180) NOT NULL,
    content TEXT NOT NULL,
    visibility ENUM('publico', 'privado', 'turma') NOT NULL DEFAULT 'publico',
    status ENUM('pendente', 'aprovado', 'recusado', 'arquivado') NOT NULL DEFAULT 'pendente',
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    moderation_reason VARCHAR(255) NULL,
    moderated_by BIGINT UNSIGNED NULL,
    moderated_at TIMESTAMP NULL,
    archived_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY posts_status_index (status),
    KEY posts_featured_index (is_featured),
    KEY posts_type_index (post_type),
    CONSTRAINT fk_posts_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_posts_moderated_by FOREIGN KEY (moderated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS comments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    post_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    parent_id BIGINT UNSIGNED NULL,
    content TEXT NOT NULL,
    status ENUM('pendente', 'aprovado', 'recusado', 'arquivado') NOT NULL DEFAULT 'aprovado',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_comments_post FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    CONSTRAINT fk_comments_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_comments_parent FOREIGN KEY (parent_id) REFERENCES comments(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS post_likes (
    post_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (post_id, user_id),
    CONSTRAINT fk_post_likes_post FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    CONSTRAINT fk_post_likes_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS post_saves (
    post_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (post_id, user_id),
    CONSTRAINT fk_post_saves_post FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    CONSTRAINT fk_post_saves_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS content_moderation (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    content_type ENUM('post', 'comment', 'project', 'portfolio') NOT NULL,
    content_id BIGINT UNSIGNED NOT NULL,
    moderator_id BIGINT UNSIGNED NULL,
    status ENUM('pendente', 'aprovado', 'recusado') NOT NULL DEFAULT 'pendente',
    reason VARCHAR(255) NULL,
    reviewed_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    KEY content_moderation_content_index (content_type, content_id),
    CONSTRAINT fk_content_moderation_moderator FOREIGN KEY (moderator_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS events (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    institution_id BIGINT UNSIGNED NULL,
    creator_id BIGINT UNSIGNED NULL,
    title VARCHAR(180) NOT NULL,
    event_type ENUM('palestra', 'workshop', 'aula_ao_vivo', 'simulado', 'olimpiada', 'hackathon', 'outro') NOT NULL DEFAULT 'palestra',
    description TEXT NULL,
    starts_at TIMESTAMP NULL,
    ends_at TIMESTAMP NULL,
    location VARCHAR(180) NULL,
    is_online TINYINT(1) NOT NULL DEFAULT 0,
    meeting_url VARCHAR(255) NULL,
    capacity INT UNSIGNED NULL,
    workload_hours SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    image_path VARCHAR(255) NULL,
    certificate_enabled TINYINT(1) NOT NULL DEFAULT 0,
    status ENUM('rascunho', 'publicado', 'encerrado') NOT NULL DEFAULT 'rascunho',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_events_institution FOREIGN KEY (institution_id) REFERENCES institutions(id) ON DELETE SET NULL,
    CONSTRAINT fk_events_creator FOREIGN KEY (creator_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS event_registrations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    status ENUM('inscrito', 'confirmado', 'cancelado') NOT NULL DEFAULT 'inscrito',
    registered_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    attended_at TIMESTAMP NULL,
    certificate_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY event_registrations_event_user_unique (event_id, user_id),
    KEY event_registrations_status_index (status),
    CONSTRAINT fk_event_registrations_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    CONSTRAINT fk_event_registrations_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS certificates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    enrollment_id BIGINT UNSIGNED NULL UNIQUE,
    event_registration_id BIGINT UNSIGNED NULL UNIQUE,
    event_id BIGINT UNSIGNED NULL,
    course_id BIGINT UNSIGNED NULL,
    certificate_type ENUM('curso', 'evento') NOT NULL DEFAULT 'curso',
    code VARCHAR(80) NOT NULL UNIQUE,
    title VARCHAR(180) NOT NULL,
    workload_hours SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    validation_status ENUM('valido', 'revogado') NOT NULL DEFAULT 'valido',
    qr_code_path VARCHAR(255) NULL,
    revoked_by BIGINT UNSIGNED NULL,
    revoked_at TIMESTAMP NULL,
    revocation_reason VARCHAR(255) NULL,
    issued_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY certificates_user_index (user_id),
    KEY certificates_course_index (course_id),
    KEY certificates_status_index (validation_status),
    CONSTRAINT fk_certificates_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_certificates_enrollment FOREIGN KEY (enrollment_id) REFERENCES enrollments(id) ON DELETE SET NULL,
    CONSTRAINT fk_certificates_event_registration FOREIGN KEY (event_registration_id) REFERENCES event_registrations(id) ON DELETE SET NULL,
    CONSTRAINT fk_certificates_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE SET NULL,
    CONSTRAINT fk_certificates_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL,
    CONSTRAINT fk_certificates_revoked_by FOREIGN KEY (revoked_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS gamification_profiles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL UNIQUE,
    xp INT UNSIGNED NOT NULL DEFAULT 0,
    xp_total INT UNSIGNED NOT NULL DEFAULT 0,
    level INT UNSIGNED NOT NULL DEFAULT 1,
    internal_coins INT UNSIGNED NOT NULL DEFAULT 0,
    streak_days INT UNSIGNED NOT NULL DEFAULT 0,
    last_activity_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_gamification_profiles_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS badges (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(80) NOT NULL UNIQUE,
    name VARCHAR(120) NOT NULL,
    description VARCHAR(255) NULL,
    icon_path VARCHAR(255) NULL,
    xp_reward INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_badges (
    user_id BIGINT UNSIGNED NOT NULL,
    badge_id BIGINT UNSIGNED NOT NULL,
    earned_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, badge_id),
    CONSTRAINT fk_user_badges_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_user_badges_badge FOREIGN KEY (badge_id) REFERENCES badges(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

CREATE TABLE IF NOT EXISTS plans (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    description VARCHAR(255) NULL,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    billing_cycle ENUM('mensal', 'anual', 'unico') NOT NULL DEFAULT 'mensal',
    duration_days SMALLINT UNSIGNED NOT NULL DEFAULT 30,
    features JSON NULL,
    benefits JSON NULL,
    is_premium TINYINT(1) NOT NULL DEFAULT 0,
    sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    status ENUM('ativo', 'inativo') NOT NULL DEFAULT 'ativo',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS transactions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    plan_id BIGINT UNSIGNED NULL,
    subscription_id BIGINT UNSIGNED NULL,
    creator_id BIGINT UNSIGNED NULL,
    transaction_type ENUM('assinatura', 'mensalidade', 'marketplace', 'comissao') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    platform_fee DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    creator_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    status ENUM('pendente', 'pago', 'cancelado', 'expirado', 'estornado') NOT NULL DEFAULT 'pendente',
    payment_method ENUM('manual', 'pix', 'cartao', 'interno') NOT NULL DEFAULT 'manual',
    gateway VARCHAR(80) NULL,
    gateway_reference VARCHAR(160) NULL,
    reference VARCHAR(120) NULL,
    due_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY transactions_status_index (status),
    KEY transactions_user_status_index (user_id, status),
    KEY transactions_plan_index (plan_id),
    CONSTRAINT fk_transactions_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_transactions_plan FOREIGN KEY (plan_id) REFERENCES plans(id) ON DELETE SET NULL,
    CONSTRAINT fk_transactions_creator FOREIGN KEY (creator_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

CREATE TABLE IF NOT EXISTS notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(160) NOT NULL,
    message TEXT NOT NULL,
    notification_type VARCHAR(60) NOT NULL DEFAULT 'sistema',
    action_url VARCHAR(255) NULL,
    metadata JSON NULL,
    priority ENUM('baixa', 'normal', 'alta') NOT NULL DEFAULT 'normal',
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY notifications_user_read_index (user_id, read_at),
    KEY notifications_type_index (notification_type),
    CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    level ENUM('info', 'warning', 'error', 'security') NOT NULL DEFAULT 'info',
    action VARCHAR(120) NOT NULL,
    context JSON NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_logs_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS chat_channels (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    class_id BIGINT UNSIGNED NULL,
    created_by BIGINT UNSIGNED NULL,
    name VARCHAR(140) NOT NULL,
    channel_type ENUM('turma', 'grupo', 'privado') NOT NULL DEFAULT 'turma',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY chat_channels_type_index (channel_type),
    KEY chat_channels_class_index (class_id),
    CONSTRAINT fk_chat_channels_class FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE SET NULL,
    CONSTRAINT fk_chat_channels_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS chat_channel_members (
    channel_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    joined_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    last_read_at TIMESTAMP NULL,
    PRIMARY KEY (channel_id, user_id),
    KEY chat_members_user_index (user_id),
    CONSTRAINT fk_chat_members_channel FOREIGN KEY (channel_id) REFERENCES chat_channels(id) ON DELETE CASCADE,
    CONSTRAINT fk_chat_members_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS chat_messages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    channel_id BIGINT UNSIGNED NOT NULL,
    sender_id BIGINT UNSIGNED NOT NULL,
    message TEXT NOT NULL,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    KEY chat_messages_channel_created_index (channel_id, created_at),
    CONSTRAINT fk_chat_messages_channel FOREIGN KEY (channel_id) REFERENCES chat_channels(id) ON DELETE CASCADE,
    CONSTRAINT fk_chat_messages_sender FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ai_requests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    request_type ENUM('tutor', 'correcao', 'resumo', 'quiz', 'recomendacao', 'desempenho', 'plagio') NOT NULL,
    input_reference VARCHAR(255) NULL,
    status ENUM('pendente', 'processando', 'concluido', 'falhou') NOT NULL DEFAULT 'pendente',
    output JSON NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_ai_requests_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO roles (slug, name, description) VALUES
('aluno', 'Aluno', 'Acesso discente a cursos, atividades, biblioteca e comunidade.'),
('professor', 'Professor', 'Criação de conteúdos, atividades, correções e acompanhamento de turmas.'),
('supervisor', 'Supervisor', 'Supervisão acadêmica, aprovação de contas e moderação.'),
('administrador', 'Administrador', 'Acesso administrativo completo.'),
('secretaria', 'Secretaria', 'Gestão acadêmica, matrículas, turmas e registros.'),
('financeiro', 'Financeiro', 'Gestão de planos, transações, mensalidades e marketplace.');

INSERT IGNORE INTO permissions (slug, name, description) VALUES
('accounts.approve', 'Aprovar contas', 'Permite aprovar ou recusar cadastros pendentes.'),
('users.manage', 'Gerenciar usuários', 'Permite administrar usuários e perfis.'),
('courses.manage', 'Gerenciar cursos', 'Permite criar e publicar cursos.'),
('classes.manage', 'Gerenciar turmas', 'Permite administrar turmas e disciplinas.'),
('finance.manage', 'Gerenciar financeiro', 'Permite administrar planos, mensalidades e transações.'),
('community.moderate', 'Moderar comunidade', 'Permite revisar posts, comentários e projetos.'),
('reports.view', 'Ver relatórios', 'Permite consultar indicadores administrativos e acadêmicos.');

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT roles.id, permissions.id
FROM roles
JOIN permissions
WHERE roles.slug = 'administrador';

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT roles.id, permissions.id
FROM roles
JOIN permissions ON permissions.slug IN ('accounts.approve', 'courses.manage', 'classes.manage', 'community.moderate', 'reports.view')
WHERE roles.slug = 'supervisor';

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT roles.id, permissions.id
FROM roles
JOIN permissions ON permissions.slug IN ('courses.manage', 'classes.manage')
WHERE roles.slug = 'professor';

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT roles.id, permissions.id
FROM roles
JOIN permissions ON permissions.slug IN ('classes.manage')
WHERE roles.slug = 'secretaria';

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT roles.id, permissions.id
FROM roles
JOIN permissions ON permissions.slug IN ('finance.manage', 'reports.view')
WHERE roles.slug = 'financeiro';

INSERT IGNORE INTO institutions (source, external_code, name, legal_name, institution_type, state, city, verification_status)
VALUES ('manual', 'TME-DEMO', 'Theo Mind Educacional', 'Theo Mind Educacional', 'curso_livre', 'SP', 'São Paulo', 'verificada');

INSERT IGNORE INTO users (
    role_id, institution_id, full_name, email, password_hash, phone, cpf, birth_date,
    state, city, is_independent, interest_area, platform_goal, terms_accepted_at,
    status, approved_at, created_at, updated_at
)
SELECT
    roles.id,
    institutions.id,
    'Administrador TME',
    'admin@tme.local',
    '$2y$10$G/2aVViv0w618ygGzZ38b.0wiJ7Kw0QgZ.yXDathSzUIDDm2D/wRy',
    '11999999999',
    '00000000000',
    '2000-01-01',
    'SP',
    'São Paulo',
    1,
    'Gestão educacional',
    'Administrar a plataforma',
    NOW(),
    'aprovado',
    NOW(),
    NOW(),
    NOW()
FROM roles
JOIN institutions ON institutions.external_code = 'TME-DEMO'
WHERE roles.slug = 'administrador';

INSERT IGNORE INTO user_settings (user_id, theme, primary_color, created_at, updated_at)
SELECT id, 'light', '#1f6feb', NOW(), NOW()
FROM users
WHERE email = 'admin@tme.local';

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

INSERT IGNORE INTO plans (name, description, price, billing_cycle, duration_days, features, benefits, is_premium, sort_order, status) VALUES
('TME Gratuito', 'Acesso inicial para estudar, participar da comunidade e usar recursos básicos.', 0.00, 'mensal', 30, JSON_ARRAY('Catálogo público', 'Comunidade', 'Biblioteca pública'), JSON_ARRAY('Cursos gratuitos', 'Eventos abertos', 'Perfil e ranking'), 0, 1, 'ativo'),
('TME Premium Mensal', 'Plano premium para liberar cursos e recursos avançados da plataforma.', 39.90, 'mensal', 30, JSON_ARRAY('Cursos premium', 'Certificados', 'Analytics pessoal'), JSON_ARRAY('Acesso premium', 'Provas e simulados avançados', 'Suporte acadêmico futuro'), 1, 2, 'ativo'),
('TME Premium Anual', 'Plano anual com acesso premium e melhor custo-beneficio.', 399.00, 'anual', 365, JSON_ARRAY('Cursos premium', 'Certificados', 'Analytics pessoal'), JSON_ARRAY('Acesso premium por 12 meses', 'Recursos avancados', 'Prioridade em eventos futuros'), 1, 3, 'ativo');
