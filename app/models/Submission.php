<?php

defined('BASE_PATH') || exit('Acesso direto nao permitido.');

class Submission extends Model
{
    public function find(int $id): ?array
    {
        $statement = $this->db->prepare(
            'SELECT submissions.*, activities.title AS activity_title, activities.max_score,
                    activities.teacher_id, users.full_name AS student_name
             FROM submissions
             INNER JOIN activities ON activities.id = submissions.activity_id
             INNER JOIN users ON users.id = submissions.student_id
             WHERE submissions.id = :id
             LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $submission = $statement->fetch();

        return $submission ?: null;
    }

    public function findByActivityAndStudent(int $activityId, int $studentId): ?array
    {
        $statement = $this->db->prepare(
            'SELECT submissions.*, grades.score, grades.feedback, grades.graded_at
             FROM submissions
             LEFT JOIN grades ON grades.submission_id = submissions.id
             WHERE submissions.activity_id = :activity_id AND submissions.student_id = :student_id
             LIMIT 1'
        );
        $statement->execute([
            'activity_id' => $activityId,
            'student_id' => $studentId,
        ]);
        $submission = $statement->fetch();

        return $submission ?: null;
    }

    public function submit(int $activityId, int $studentId, string $content, ?string $filePath, string $status): int
    {
        $existing = $this->findByActivityAndStudent($activityId, $studentId);

        if ($existing && in_array($existing['status'], ['corrigida', 'devolvida'], true)) {
            throw new RuntimeException('Esta entrega ja foi corrigida ou devolvida.');
        }

        $statement = $this->db->prepare(
            'INSERT INTO submissions (activity_id, student_id, content, file_path, status, submitted_at, updated_at)
             VALUES (:activity_id, :student_id, :content, :file_path, :status, NOW(), NOW())
             ON DUPLICATE KEY UPDATE
                content = VALUES(content),
                file_path = COALESCE(VALUES(file_path), file_path),
                status = VALUES(status),
                submitted_at = NOW(),
                updated_at = NOW()'
        );
        $statement->execute([
            'activity_id' => $activityId,
            'student_id' => $studentId,
            'content' => $content ?: null,
            'file_path' => $filePath,
            'status' => $status,
        ]);

        return $existing ? (int) $existing['id'] : (int) $this->db->lastInsertId();
    }

    public function grade(int $submissionId, int $teacherId, float $score, string $feedback, string $status): void
    {
        $submission = $this->find($submissionId);

        if (! $submission) {
            throw new RuntimeException('Entrega nao encontrada.');
        }

        $this->db->beginTransaction();

        try {
            $statement = $this->db->prepare(
                'INSERT INTO grades (
                    submission_id, activity_id, student_id, teacher_id, score, feedback, graded_at, created_at, updated_at
                 ) VALUES (
                    :submission_id, :activity_id, :student_id, :teacher_id, :score, :feedback, NOW(), NOW(), NOW()
                 )
                 ON DUPLICATE KEY UPDATE
                    submission_id = VALUES(submission_id),
                    teacher_id = VALUES(teacher_id),
                    score = VALUES(score),
                    feedback = VALUES(feedback),
                    graded_at = NOW(),
                    updated_at = NOW()'
            );
            $statement->execute([
                'submission_id' => $submissionId,
                'activity_id' => (int) $submission['activity_id'],
                'student_id' => (int) $submission['student_id'],
                'teacher_id' => $teacherId,
                'score' => $score,
                'feedback' => $feedback ?: null,
            ]);

            $update = $this->db->prepare('UPDATE submissions SET status = :status, updated_at = NOW() WHERE id = :id');
            $update->execute([
                'status' => in_array($status, ['corrigida', 'devolvida'], true) ? $status : 'corrigida',
                'id' => $submissionId,
            ]);

            $this->db->commit();
        } catch (Throwable $exception) {
            $this->db->rollBack();
            throw $exception;
        }
    }
}
