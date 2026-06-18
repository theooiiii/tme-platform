<?php

defined('BASE_PATH') || exit('Acesso direto não permitido.');

class SchoolClass extends Model
{
    public function all(): array
    {
        return $this->db->query(
            'SELECT classes.*, institutions.name AS institution_name,
                    COUNT(DISTINCT class_students.user_id) AS students_count,
                    COUNT(DISTINCT class_subjects.subject_id) AS subjects_count
             FROM classes
             LEFT JOIN institutions ON institutions.id = classes.institution_id
             LEFT JOIN class_students ON class_students.class_id = classes.id AND class_students.status = "ativo"
             LEFT JOIN class_subjects ON class_subjects.class_id = classes.id AND class_subjects.status = "ativa"
             GROUP BY classes.id, institutions.name
             ORDER BY classes.created_at DESC'
        )->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->db->prepare(
            'SELECT classes.*, institutions.name AS institution_name
             FROM classes
             LEFT JOIN institutions ON institutions.id = classes.institution_id
             WHERE classes.id = :id
             LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $class = $statement->fetch();

        return $class ?: null;
    }

    public function linkedForUser(int $userId, string $role): array
    {
        if ($role === 'professor') {
            $sql = 'SELECT DISTINCT classes.*, institutions.name AS institution_name
                    FROM classes
                    LEFT JOIN institutions ON institutions.id = classes.institution_id
                    LEFT JOIN class_teachers ON class_teachers.class_id = classes.id
                    LEFT JOIN class_subjects ON class_subjects.class_id = classes.id
                    WHERE (class_teachers.user_id = :teacher_id OR class_subjects.teacher_id = :subject_teacher_id)
                      AND classes.status = "ativa"
                    ORDER BY classes.name';
            $statement = $this->db->prepare($sql);
            $statement->execute(['teacher_id' => $userId, 'subject_teacher_id' => $userId]);
            return $statement->fetchAll();
        }

        $statement = $this->db->prepare(
            'SELECT classes.*, institutions.name AS institution_name
             FROM class_students
             INNER JOIN classes ON classes.id = class_students.class_id
             LEFT JOIN institutions ON institutions.id = classes.institution_id
             WHERE class_students.user_id = :user_id
               AND class_students.status = "ativo"
               AND classes.status = "ativa"
             ORDER BY classes.name'
        );
        $statement->execute(['user_id' => $userId]);

        return $statement->fetchAll();
    }

    public function create(array $data): int
    {
        $statement = $this->db->prepare(
            'INSERT INTO classes (institution_id, name, description, period, status, created_at, updated_at)
             VALUES (:institution_id, :name, :description, :period, :status, NOW(), NOW())'
        );
        $statement->execute($this->params($data));

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $params = $this->params($data);
        $params['id'] = $id;

        $statement = $this->db->prepare(
            'UPDATE classes
             SET institution_id = :institution_id, name = :name, description = :description,
                 period = :period, status = :status, updated_at = NOW()
             WHERE id = :id'
        );
        $statement->execute($params);
    }

    public function archive(int $id): void
    {
        $statement = $this->db->prepare('UPDATE classes SET status = "arquivada", updated_at = NOW() WHERE id = :id');
        $statement->execute(['id' => $id]);
    }

    public function students(int $classId): array
    {
        $statement = $this->db->prepare(
            'SELECT users.id, users.full_name, users.email, class_students.status
             FROM class_students
             INNER JOIN users ON users.id = class_students.user_id
             WHERE class_students.class_id = :class_id
             ORDER BY users.full_name'
        );
        $statement->execute(['class_id' => $classId]);

        return $statement->fetchAll();
    }

    public function teachers(int $classId): array
    {
        $statement = $this->db->prepare(
            'SELECT users.id, users.full_name, users.email, class_teachers.status
             FROM class_teachers
             INNER JOIN users ON users.id = class_teachers.user_id
             WHERE class_teachers.class_id = :class_id
             ORDER BY users.full_name'
        );
        $statement->execute(['class_id' => $classId]);

        return $statement->fetchAll();
    }

    public function subjects(int $classId): array
    {
        $statement = $this->db->prepare(
            'SELECT subjects.*, class_subjects.teacher_id, teacher.full_name AS teacher_name
             FROM class_subjects
             INNER JOIN subjects ON subjects.id = class_subjects.subject_id
             LEFT JOIN users teacher ON teacher.id = class_subjects.teacher_id
             WHERE class_subjects.class_id = :class_id
             ORDER BY subjects.name'
        );
        $statement->execute(['class_id' => $classId]);

        return $statement->fetchAll();
    }

    public function linkStudent(int $classId, int $userId): void
    {
        $statement = $this->db->prepare(
            'INSERT INTO class_students (class_id, user_id, status, linked_at)
             VALUES (:class_id, :user_id, "ativo", NOW())
             ON DUPLICATE KEY UPDATE status = "ativo"'
        );
        $statement->execute(['class_id' => $classId, 'user_id' => $userId]);
    }

    public function linkTeacher(int $classId, int $userId): void
    {
        $statement = $this->db->prepare(
            'INSERT INTO class_teachers (class_id, user_id, status, linked_at)
             VALUES (:class_id, :user_id, "ativo", NOW())
             ON DUPLICATE KEY UPDATE status = "ativo"'
        );
        $statement->execute(['class_id' => $classId, 'user_id' => $userId]);
    }

    public function linkSubject(int $classId, int $subjectId, ?int $teacherId): void
    {
        $statement = $this->db->prepare(
            'INSERT INTO class_subjects (class_id, subject_id, teacher_id, status, linked_at)
             VALUES (:class_id, :subject_id, :teacher_id, "ativa", NOW())
             ON DUPLICATE KEY UPDATE teacher_id = VALUES(teacher_id), status = "ativa"'
        );
        $statement->execute([
            'class_id' => $classId,
            'subject_id' => $subjectId,
            'teacher_id' => $teacherId,
        ]);
    }

    public function approvedStudents(): array
    {
        return $this->usersByRole('aluno');
    }

    public function approvedTeachers(): array
    {
        return $this->usersByRole('professor');
    }

    public function institutions(): array
    {
        return $this->db->query('SELECT id, name FROM institutions ORDER BY name')->fetchAll();
    }

    private function usersByRole(string $role): array
    {
        $statement = $this->db->prepare(
            'SELECT users.id, users.full_name, users.email
             FROM users
             INNER JOIN roles ON roles.id = users.role_id
             WHERE roles.slug = :role AND users.status = "aprovado"
             ORDER BY users.full_name'
        );
        $statement->execute(['role' => $role]);

        return $statement->fetchAll();
    }

    private function params(array $data): array
    {
        return [
            'institution_id' => $data['institution_id'] ?: null,
            'name' => $data['name'],
            'description' => $data['description'] ?: null,
            'period' => $data['period'] ?: null,
            'status' => $data['status'],
        ];
    }
}
