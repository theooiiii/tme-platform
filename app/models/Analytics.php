<?php

defined('BASE_PATH') || exit('Acesso direto nao permitido.');

class Analytics extends Model
{
    public function admin(array $period): array
    {
        return [
            'metrics' => [
                'active_users' => $this->scalar('SELECT COUNT(*) FROM users WHERE status = "aprovado" AND (last_login_at IS NULL OR last_login_at >= :from_date)', $period),
                'enrollments' => $this->scalar('SELECT COUNT(*) FROM enrollments WHERE enrolled_at >= :from_date', $period),
                'certificates' => $this->scalar('SELECT COUNT(*) FROM certificates WHERE issued_at >= :from_date', $period),
                'revenue' => $this->money('SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE status = "pago" AND created_at >= :from_date', $period),
            ],
            'popular_courses' => $this->rows(
                'SELECT courses.title AS label, COUNT(enrollments.id) AS value
                 FROM enrollments
                 INNER JOIN courses ON courses.id = enrollments.course_id
                 WHERE enrollments.enrolled_at >= :from_date
                 GROUP BY courses.id, courses.title
                 ORDER BY value DESC
                 LIMIT 6',
                $period
            ),
            'activity' => $this->dailyRows('logs', 'created_at', $period),
            'growth' => $this->dailyRows('users', 'created_at', $period),
        ];
    }

    public function teacher(int $teacherId, array $period): array
    {
        $params = $period + [
            'teacher_id' => $teacherId,
            'teacher_id_alt' => $teacherId,
            'teacher_id_grades' => $teacherId,
            'teacher_id_progress' => $teacherId,
        ];

        return [
            'metrics' => [
                'active_students' => $this->scalar(
                    'SELECT COUNT(DISTINCT class_students.user_id)
                     FROM class_students
                     INNER JOIN class_subjects ON class_subjects.class_id = class_students.class_id
                     WHERE class_subjects.teacher_id = :teacher_id',
                    $params
                ),
                'pending_submissions' => $this->scalar(
                    'SELECT COUNT(*)
                     FROM submissions
                     INNER JOIN activities ON activities.id = submissions.activity_id
                     WHERE activities.teacher_id = :teacher_id_alt
                       AND submissions.status IN ("enviada", "atrasada")',
                    $params
                ),
                'average_score' => $this->money(
                    'SELECT COALESCE(AVG(grades.score), 0)
                     FROM grades
                     INNER JOIN activities ON activities.id = grades.activity_id
                     WHERE activities.teacher_id = :teacher_id_grades
                       AND grades.graded_at >= :from_date',
                    $params
                ),
                'average_progress' => $this->money(
                    'SELECT COALESCE(AVG(enrollments.progress_percent), 0)
                     FROM enrollments
                     INNER JOIN courses ON courses.id = enrollments.course_id
                     WHERE courses.responsible_teacher_id = :teacher_id_progress',
                    $params
                ),
            ],
            'submissions' => $this->dailyRowsForTeacher($teacherId, $period),
            'progress' => $this->rows(
                'SELECT courses.title AS label, ROUND(AVG(enrollments.progress_percent), 2) AS value
                 FROM enrollments
                 INNER JOIN courses ON courses.id = enrollments.course_id
                 WHERE courses.responsible_teacher_id = :teacher_id
                 GROUP BY courses.id, courses.title
                 ORDER BY value DESC
                 LIMIT 6',
                ['teacher_id' => $teacherId]
            ),
        ];
    }

    public function student(int $userId, array $period): array
    {
        $params = $period + [
            'user_id' => $userId,
            'user_id_attendance' => $userId,
            'user_id_xp' => $userId,
            'user_id_exam' => $userId,
            'user_id_cert' => $userId,
        ];

        return [
            'metrics' => [
                'average_progress' => $this->money('SELECT COALESCE(AVG(progress_percent), 0) FROM enrollments WHERE user_id = :user_id', $params),
                'attendance_percent' => $this->attendancePercent($userId, $period),
                'weekly_xp' => $this->scalar('SELECT COALESCE(SUM(xp_awarded), 0) FROM gamification_events WHERE user_id = :user_id_xp AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)', $params),
                'exam_average' => $this->money('SELECT COALESCE(AVG(total_score), 0) FROM exam_attempts WHERE student_id = :user_id_exam AND status IN ("corrigida", "pendente_correcao")', $params),
                'certificates' => $this->scalar('SELECT COUNT(*) FROM certificates WHERE user_id = :user_id_cert', $params),
            ],
            'progress' => $this->rows(
                'SELECT courses.title AS label, enrollments.progress_percent AS value
                 FROM enrollments
                 INNER JOIN courses ON courses.id = enrollments.course_id
                 WHERE enrollments.user_id = :user_id
                 ORDER BY enrollments.last_activity_at DESC
                 LIMIT 6',
                ['user_id' => $userId]
            ),
            'xp' => $this->dailyRowsForUser('gamification_events', 'created_at', 'xp_awarded', $userId, $period),
            'exams' => $this->rows(
                'SELECT exams.title AS label, exam_attempts.total_score AS value
                 FROM exam_attempts
                 INNER JOIN exams ON exams.id = exam_attempts.exam_id
                 WHERE exam_attempts.student_id = :user_id
                 ORDER BY exam_attempts.started_at DESC
                 LIMIT 6',
                ['user_id' => $userId]
            ),
        ];
    }

    public function periodFromRequest(array $query): array
    {
        $days = max(7, min(365, (int) ($query['dias'] ?? 30)));

        return [
            'days' => $days,
            'from_date' => date('Y-m-d 00:00:00', strtotime('-' . $days . ' days')),
        ];
    }

    private function scalar(string $sql, array $params = []): int
    {
        $statement = $this->db->prepare($sql);
        $statement->execute($this->filterParams($sql, $params));

        return (int) $statement->fetchColumn();
    }

    private function money(string $sql, array $params = []): float
    {
        $statement = $this->db->prepare($sql);
        $statement->execute($this->filterParams($sql, $params));

        return (float) $statement->fetchColumn();
    }

    private function rows(string $sql, array $params = []): array
    {
        $statement = $this->db->prepare($sql);
        $statement->execute($this->filterParams($sql, $params));

        return $statement->fetchAll();
    }

    private function dailyRows(string $table, string $column, array $period): array
    {
        return $this->rows(
            'SELECT DATE(' . $column . ') AS label, COUNT(*) AS value
             FROM ' . $table . '
             WHERE ' . $column . ' >= :from_date
             GROUP BY DATE(' . $column . ')
             ORDER BY label ASC',
            $period
        );
    }

    private function dailyRowsForTeacher(int $teacherId, array $period): array
    {
        return $this->rows(
            'SELECT DATE(submissions.submitted_at) AS label, COUNT(*) AS value
             FROM submissions
             INNER JOIN activities ON activities.id = submissions.activity_id
             WHERE activities.teacher_id = :teacher_id
               AND submissions.submitted_at >= :from_date
             GROUP BY DATE(submissions.submitted_at)
             ORDER BY label ASC',
            ['teacher_id' => $teacherId, 'from_date' => $period['from_date']]
        );
    }

    private function dailyRowsForUser(string $table, string $column, string $valueColumn, int $userId, array $period): array
    {
        return $this->rows(
            'SELECT DATE(' . $column . ') AS label, COALESCE(SUM(' . $valueColumn . '), 0) AS value
             FROM ' . $table . '
             WHERE user_id = :user_id AND ' . $column . ' >= :from_date
             GROUP BY DATE(' . $column . ')
             ORDER BY label ASC',
            ['user_id' => $userId, 'from_date' => $period['from_date']]
        );
    }

    private function attendancePercent(int $userId, array $period): float
    {
        $statement = $this->db->prepare(
            'SELECT
                COUNT(*) AS total_records,
                SUM(status IN ("presente", "atraso", "justificado")) AS positive_records
             FROM attendance_records
             WHERE student_id = :user_id AND attendance_date >= :from_date'
        );
        $statement->execute(['user_id' => $userId, 'from_date' => substr($period['from_date'], 0, 10)]);
        $row = $statement->fetch() ?: [];
        $total = (int) ($row['total_records'] ?? 0);

        return $total > 0 ? round(((int) $row['positive_records'] / $total) * 100, 2) : 0.0;
    }

    private function filterParams(string $sql, array $params): array
    {
        return array_filter(
            $params,
            static fn (string $key): bool => str_contains($sql, ':' . $key),
            ARRAY_FILTER_USE_KEY
        );
    }
}
