<?php

defined('BASE_PATH') || exit('Acesso direto nao permitido.');

class Attendance extends Model
{
    public function classesForManager(array $user): array
    {
        if (in_array($user['role_slug'], ['administrador', 'supervisor'], true)) {
            return $this->db->query(
                'SELECT classes.*, institutions.name AS institution_name
                 FROM classes
                 LEFT JOIN institutions ON institutions.id = classes.institution_id
                 WHERE classes.status = "ativa"
                 ORDER BY classes.name'
            )->fetchAll();
        }

        $statement = $this->db->prepare(
            'SELECT DISTINCT classes.*, institutions.name AS institution_name
             FROM classes
             LEFT JOIN institutions ON institutions.id = classes.institution_id
             LEFT JOIN class_teachers ON class_teachers.class_id = classes.id AND class_teachers.status = "ativo"
             LEFT JOIN class_subjects ON class_subjects.class_id = classes.id AND class_subjects.status = "ativa"
             WHERE classes.status = "ativa"
               AND (class_teachers.user_id = :teacher_id OR class_subjects.teacher_id = :subject_teacher_id)
             ORDER BY classes.name'
        );
        $statement->execute([
            'teacher_id' => (int) $user['id'],
            'subject_teacher_id' => (int) $user['id'],
        ]);

        return $statement->fetchAll();
    }

    public function canManageClass(int $classId, array $user): bool
    {
        if (in_array($user['role_slug'], ['administrador', 'supervisor'], true)) {
            return true;
        }

        $statement = $this->db->prepare(
            'SELECT 1
             FROM classes
             LEFT JOIN class_teachers ON class_teachers.class_id = classes.id AND class_teachers.status = "ativo"
             LEFT JOIN class_subjects ON class_subjects.class_id = classes.id AND class_subjects.status = "ativa"
             WHERE classes.id = :class_id
               AND (class_teachers.user_id = :teacher_id OR class_subjects.teacher_id = :subject_teacher_id)
             LIMIT 1'
        );
        $statement->execute([
            'class_id' => $classId,
            'teacher_id' => (int) $user['id'],
            'subject_teacher_id' => (int) $user['id'],
        ]);

        return (bool) $statement->fetchColumn();
    }

    public function subjectsForClass(int $classId, ?array $user = null): array
    {
        $params = ['class_id' => $classId];
        $where = 'class_subjects.class_id = :class_id AND class_subjects.status = "ativa"';

        if ($user && $user['role_slug'] === 'professor') {
            $where .= ' AND (class_subjects.teacher_id = :teacher_id OR EXISTS (
                SELECT 1 FROM class_teachers
                WHERE class_teachers.class_id = class_subjects.class_id
                  AND class_teachers.user_id = :teacher_member_id
                  AND class_teachers.status = "ativo"
            ))';
            $params['teacher_id'] = (int) $user['id'];
            $params['teacher_member_id'] = (int) $user['id'];
        }

        $statement = $this->db->prepare(
            'SELECT subjects.*, teacher.full_name AS teacher_name
             FROM class_subjects
             INNER JOIN subjects ON subjects.id = class_subjects.subject_id
             LEFT JOIN users teacher ON teacher.id = class_subjects.teacher_id
             WHERE ' . $where . '
             ORDER BY subjects.name'
        );
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function studentsForClass(int $classId): array
    {
        $statement = $this->db->prepare(
            'SELECT users.id, users.full_name, users.email
             FROM class_students
             INNER JOIN users ON users.id = class_students.user_id
             WHERE class_students.class_id = :class_id
               AND class_students.status = "ativo"
               AND users.status = "aprovado"
             ORDER BY users.full_name'
        );
        $statement->execute(['class_id' => $classId]);

        return $statement->fetchAll();
    }

    public function recordsForSession(int $classId, int $subjectId, string $date): array
    {
        $statement = $this->db->prepare(
            'SELECT attendance_records.*, users.full_name AS recorder_name
             FROM attendance_records
             LEFT JOIN users ON users.id = attendance_records.recorded_by
             WHERE attendance_records.class_id = :class_id
               AND attendance_records.subject_id = :subject_id
               AND attendance_records.attendance_date = :attendance_date'
        );
        $statement->execute([
            'class_id' => $classId,
            'subject_id' => $subjectId,
            'attendance_date' => $date,
        ]);

        $records = [];

        foreach ($statement->fetchAll() as $record) {
            $records[(int) $record['student_id']] = $record;
        }

        return $records;
    }

    public function saveBatch(int $classId, int $subjectId, string $date, array $entries, int $recordedBy): void
    {
        $statement = $this->db->prepare(
            'INSERT INTO attendance_records (
                class_id, subject_id, student_id, recorded_by, attendance_date, status, note, created_at, updated_at
             ) VALUES (
                :class_id, :subject_id, :student_id, :recorded_by, :attendance_date, :status, :note, NOW(), NOW()
             )
             ON DUPLICATE KEY UPDATE
                recorded_by = VALUES(recorded_by),
                status = VALUES(status),
                note = VALUES(note),
                updated_at = NOW()'
        );

        foreach ($entries as $entry) {
            $status = $this->normalizeStatus((string) ($entry['status'] ?? 'presente'));

            $statement->execute([
                'class_id' => $classId,
                'subject_id' => $subjectId,
                'student_id' => (int) $entry['student_id'],
                'recorded_by' => $recordedBy,
                'attendance_date' => $date,
                'status' => $status,
                'note' => trim((string) ($entry['note'] ?? '')) ?: null,
            ]);
        }
    }

    public function historyForStudent(int $studentId, array $filters = []): array
    {
        $where = ['attendance_records.student_id = :student_id'];
        $params = ['student_id' => $studentId];

        if (! empty($filters['class_id'])) {
            $where[] = 'attendance_records.class_id = :class_id';
            $params['class_id'] = (int) $filters['class_id'];
        }

        if (! empty($filters['date_from'])) {
            $where[] = 'attendance_records.attendance_date >= :date_from';
            $params['date_from'] = $filters['date_from'];
        }

        if (! empty($filters['date_to'])) {
            $where[] = 'attendance_records.attendance_date <= :date_to';
            $params['date_to'] = $filters['date_to'];
        }

        $statement = $this->db->prepare(
            'SELECT attendance_records.*, classes.name AS class_name, subjects.name AS subject_name,
                    recorder.full_name AS recorder_name
             FROM attendance_records
             INNER JOIN classes ON classes.id = attendance_records.class_id
             INNER JOIN subjects ON subjects.id = attendance_records.subject_id
             LEFT JOIN users recorder ON recorder.id = attendance_records.recorded_by
             WHERE ' . implode(' AND ', $where) . '
             ORDER BY attendance_records.attendance_date DESC, classes.name, subjects.name'
        );
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function report(array $filters = []): array
    {
        $where = [];
        $params = [];

        if (! empty($filters['class_id'])) {
            $where[] = 'attendance_records.class_id = :class_id';
            $params['class_id'] = (int) $filters['class_id'];
        }

        if (! empty($filters['subject_id'])) {
            $where[] = 'attendance_records.subject_id = :subject_id';
            $params['subject_id'] = (int) $filters['subject_id'];
        }

        if (! empty($filters['student_id'])) {
            $where[] = 'attendance_records.student_id = :student_id';
            $params['student_id'] = (int) $filters['student_id'];
        }

        if (! empty($filters['date_from'])) {
            $where[] = 'attendance_records.attendance_date >= :date_from';
            $params['date_from'] = $filters['date_from'];
        }

        if (! empty($filters['date_to'])) {
            $where[] = 'attendance_records.attendance_date <= :date_to';
            $params['date_to'] = $filters['date_to'];
        }

        $sql = 'SELECT users.id AS student_id, users.full_name AS student_name,
                       classes.id AS class_id, classes.name AS class_name,
                       subjects.id AS subject_id, subjects.name AS subject_name,
                       COUNT(*) AS total_records,
                       SUM(attendance_records.status = "presente") AS present_count,
                       SUM(attendance_records.status = "atraso") AS late_count,
                       SUM(attendance_records.status = "justificado") AS justified_count,
                       SUM(attendance_records.status = "falta") AS absence_count,
                       ROUND((SUM(attendance_records.status IN ("presente", "atraso", "justificado")) / COUNT(*)) * 100, 2) AS attendance_percent
                FROM attendance_records
                INNER JOIN users ON users.id = attendance_records.student_id
                INNER JOIN classes ON classes.id = attendance_records.class_id
                INNER JOIN subjects ON subjects.id = attendance_records.subject_id';

        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' GROUP BY users.id, users.full_name, classes.id, classes.name, subjects.id, subjects.name
                  ORDER BY classes.name, subjects.name, users.full_name';

        $statement = $this->db->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function classesForStudent(int $studentId): array
    {
        $statement = $this->db->prepare(
            'SELECT classes.id, classes.name
             FROM class_students
             INNER JOIN classes ON classes.id = class_students.class_id
             WHERE class_students.user_id = :student_id
               AND class_students.status = "ativo"
             ORDER BY classes.name'
        );
        $statement->execute(['student_id' => $studentId]);

        return $statement->fetchAll();
    }

    public function studentsWithAttendance(): array
    {
        return $this->db->query(
            'SELECT DISTINCT users.id, users.full_name, users.email
             FROM attendance_records
             INNER JOIN users ON users.id = attendance_records.student_id
             ORDER BY users.full_name'
        )->fetchAll();
    }

    private function normalizeStatus(string $status): string
    {
        return in_array($status, ['presente', 'falta', 'atraso', 'justificado'], true)
            ? $status
            : 'presente';
    }
}
