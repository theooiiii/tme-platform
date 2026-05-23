<?php

defined('BASE_PATH') || exit('Acesso direto nao permitido.');

class Enrollment extends Model
{
    public function find(int $id): ?array
    {
        $statement = $this->db->prepare(
            'SELECT enrollments.*, courses.title AS course_title, courses.status AS course_status,
                    courses.category, courses.level, courses.workload_hours, courses.image_path,
                    users.full_name AS student_name, users.email AS student_email
             FROM enrollments
             INNER JOIN courses ON courses.id = enrollments.course_id
             INNER JOIN users ON users.id = enrollments.user_id
             WHERE enrollments.id = :id
             LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $enrollment = $statement->fetch();

        return $enrollment ?: null;
    }

    public function findForStudent(int $id, int $userId): ?array
    {
        $statement = $this->db->prepare(
            'SELECT enrollments.*, courses.title AS course_title, courses.description AS course_description,
                    courses.category, courses.level, courses.workload_hours, courses.price,
                    courses.image_path, courses.status AS course_status,
                    teacher.full_name AS teacher_name
             FROM enrollments
             INNER JOIN courses ON courses.id = enrollments.course_id
             LEFT JOIN users teacher ON teacher.id = courses.responsible_teacher_id
             WHERE enrollments.id = :id AND enrollments.user_id = :user_id
             LIMIT 1'
        );
        $statement->execute(['id' => $id, 'user_id' => $userId]);
        $enrollment = $statement->fetch();

        return $enrollment ?: null;
    }

    public function findByUserAndCourse(int $userId, int $courseId): ?array
    {
        $statement = $this->db->prepare(
            'SELECT enrollments.*, courses.title AS course_title
             FROM enrollments
             INNER JOIN courses ON courses.id = enrollments.course_id
             WHERE enrollments.user_id = :user_id AND enrollments.course_id = :course_id
             LIMIT 1'
        );
        $statement->execute([
            'user_id' => $userId,
            'course_id' => $courseId,
        ]);
        $enrollment = $statement->fetch();

        return $enrollment ?: null;
    }

    public function create(int $userId, int $courseId): int
    {
        $statement = $this->db->prepare(
            'INSERT INTO enrollments (
                user_id, course_id, role, status, progress_percent, enrolled_at,
                last_activity_at, completed_at
             ) VALUES (
                :user_id, :course_id, "aluno", "ativa", 0.00, NOW(), NOW(), NULL
             )'
        );
        $statement->execute([
            'user_id' => $userId,
            'course_id' => $courseId,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function forStudent(int $userId): array
    {
        $statement = $this->db->prepare(
            'SELECT enrollments.*, courses.title, courses.description, courses.category,
                    courses.level, courses.workload_hours, courses.image_path,
                    teacher.full_name AS teacher_name,
                    COUNT(DISTINCT lessons.id) AS lessons_count
             FROM enrollments
             INNER JOIN courses ON courses.id = enrollments.course_id
             LEFT JOIN users teacher ON teacher.id = courses.responsible_teacher_id
             LEFT JOIN lessons ON lessons.course_id = courses.id AND lessons.status = "publicada"
             WHERE enrollments.user_id = :user_id
             GROUP BY enrollments.id, courses.id, teacher.full_name
             ORDER BY enrollments.last_activity_at DESC, enrollments.enrolled_at DESC'
        );
        $statement->execute(['user_id' => $userId]);

        return $statement->fetchAll();
    }

    public function adminList(array $filters = []): array
    {
        $where = [];
        $params = [];

        if (! empty($filters['course_id'])) {
            $where[] = 'enrollments.course_id = :course_id';
            $params['course_id'] = (int) $filters['course_id'];
        }

        if (! empty($filters['student_id'])) {
            $where[] = 'enrollments.user_id = :student_id';
            $params['student_id'] = (int) $filters['student_id'];
        }

        if (! empty($filters['status'])) {
            $where[] = 'enrollments.status = :status';
            $params['status'] = $filters['status'];
        }

        $sql = 'SELECT enrollments.*, courses.title AS course_title,
                       users.full_name AS student_name, users.email AS student_email,
                       COUNT(DISTINCT lessons.id) AS lessons_count,
                       COUNT(DISTINCT lesson_progress.id) AS completed_lessons
                FROM enrollments
                INNER JOIN courses ON courses.id = enrollments.course_id
                INNER JOIN users ON users.id = enrollments.user_id
                LEFT JOIN lessons ON lessons.course_id = courses.id AND lessons.status = "publicada"
                LEFT JOIN lesson_progress ON lesson_progress.enrollment_id = enrollments.id
                    AND lesson_progress.status = "concluida"';

        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' GROUP BY enrollments.id, courses.title, users.full_name, users.email
                  ORDER BY enrollments.last_activity_at DESC, enrollments.enrolled_at DESC';

        $statement = $this->db->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function studentsWithEnrollments(): array
    {
        $statement = $this->db->query(
            'SELECT DISTINCT users.id, users.full_name, users.email
             FROM users
             INNER JOIN enrollments ON enrollments.user_id = users.id
             ORDER BY users.full_name'
        );

        return $statement->fetchAll();
    }

    public function coursesWithEnrollments(): array
    {
        $statement = $this->db->query(
            'SELECT DISTINCT courses.id, courses.title
             FROM courses
             INNER JOIN enrollments ON enrollments.course_id = courses.id
             ORDER BY courses.title'
        );

        return $statement->fetchAll();
    }

    public function markLessonCompleted(int $enrollmentId, int $userId, int $lessonId): array
    {
        $enrollment = $this->findForStudent($enrollmentId, $userId);

        if (! $enrollment || $enrollment['status'] === 'cancelada') {
            throw new RuntimeException('Matrícula não encontrada ou cancelada.');
        }

        $lesson = (new Lesson())->find($lessonId);

        if (! $lesson || (int) $lesson['course_id'] !== (int) $enrollment['course_id'] || $lesson['status'] !== 'publicada') {
            throw new RuntimeException('Aula não encontrada nesta matrícula.');
        }

        $wasConcluded = $enrollment['status'] === 'concluida';

        $this->db->beginTransaction();

        try {
            $statement = $this->db->prepare(
                'INSERT INTO lesson_progress (
                    enrollment_id, user_id, course_id, lesson_id, status,
                    completed_at, last_activity_at, created_at, updated_at
                 ) VALUES (
                    :enrollment_id, :user_id, :course_id, :lesson_id, "concluida",
                    NOW(), NOW(), NOW(), NOW()
                 )
                 ON DUPLICATE KEY UPDATE
                    status = "concluida",
                    completed_at = COALESCE(completed_at, NOW()),
                    last_activity_at = NOW(),
                    updated_at = NOW()'
            );
            $statement->execute([
                'enrollment_id' => $enrollmentId,
                'user_id' => $userId,
                'course_id' => (int) $enrollment['course_id'],
                'lesson_id' => $lessonId,
            ]);

            $progress = $this->recalculateProgressInsideTransaction($enrollmentId, (int) $enrollment['course_id']);
            $this->db->commit();

            $progress['course_completed_now'] = $progress['status'] === 'concluida' && ! $wasConcluded;
            return $progress;
        } catch (Throwable $exception) {
            $this->db->rollBack();
            throw $exception;
        }
    }

    public function progressMap(int $enrollmentId): array
    {
        $statement = $this->db->prepare(
            'SELECT lesson_id, status, completed_at
             FROM lesson_progress
             WHERE enrollment_id = :enrollment_id'
        );
        $statement->execute(['enrollment_id' => $enrollmentId]);

        $map = [];

        foreach ($statement->fetchAll() as $row) {
            $map[(int) $row['lesson_id']] = $row;
        }

        return $map;
    }

    private function recalculateProgressInsideTransaction(int $enrollmentId, int $courseId): array
    {
        $totalStatement = $this->db->prepare('SELECT COUNT(*) FROM lessons WHERE course_id = :course_id AND status = "publicada"');
        $totalStatement->execute(['course_id' => $courseId]);
        $total = (int) $totalStatement->fetchColumn();

        $completedStatement = $this->db->prepare(
            'SELECT COUNT(DISTINCT lesson_progress.lesson_id)
             FROM lesson_progress
             INNER JOIN lessons ON lessons.id = lesson_progress.lesson_id
             WHERE lesson_progress.enrollment_id = :enrollment_id
               AND lesson_progress.status = "concluida"
               AND lessons.status = "publicada"
               AND lessons.course_id = :course_id'
        );
        $completedStatement->execute([
            'enrollment_id' => $enrollmentId,
            'course_id' => $courseId,
        ]);
        $completed = (int) $completedStatement->fetchColumn();

        $percent = $total > 0 ? round(($completed / $total) * 100, 2) : 0.00;
        $status = ($total > 0 && $completed >= $total) ? 'concluida' : 'ativa';

        $update = $this->db->prepare(
            'UPDATE enrollments
             SET progress_percent = :progress_percent,
                 status = :status,
                 last_activity_at = NOW(),
                 completed_at = CASE WHEN :completed_status = "concluida" THEN COALESCE(completed_at, NOW()) ELSE NULL END
             WHERE id = :id'
        );
        $update->execute([
            'progress_percent' => $percent,
            'status' => $status,
            'completed_status' => $status,
            'id' => $enrollmentId,
        ]);

        return [
            'total_lessons' => $total,
            'completed_lessons' => $completed,
            'progress_percent' => $percent,
            'status' => $status,
        ];
    }
}
