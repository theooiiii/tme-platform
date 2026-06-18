<?php

defined('BASE_PATH') || exit('Acesso direto não permitido.');

class Exam extends Model
{
    public function allForManager(array $filters, array $user): array
    {
        $where = [];
        $params = [];

        if (! empty($filters['status'])) {
            $where[] = 'exams.status = :status';
            $params['status'] = $filters['status'];
        }

        if (! empty($filters['course_id'])) {
            $where[] = 'exams.course_id = :course_id';
            $params['course_id'] = (int) $filters['course_id'];
        }

        if (! empty($filters['class_id'])) {
            $where[] = 'exams.class_id = :class_id';
            $params['class_id'] = (int) $filters['class_id'];
        }

        if ($user['role_slug'] === 'professor') {
            $where[] = '(exams.creator_id = :teacher_creator_id
                OR courses.responsible_teacher_id = :teacher_course_id
                OR EXISTS (
                    SELECT 1 FROM class_subjects
                    WHERE class_subjects.class_id = exams.class_id
                      AND class_subjects.teacher_id = :teacher_subject_id
                      AND class_subjects.status = "ativa"
                )
                OR EXISTS (
                    SELECT 1 FROM class_teachers
                    WHERE class_teachers.class_id = exams.class_id
                      AND class_teachers.user_id = :teacher_class_id
                      AND class_teachers.status = "ativo"
                ))';
            $params['teacher_creator_id'] = (int) $user['id'];
            $params['teacher_course_id'] = (int) $user['id'];
            $params['teacher_subject_id'] = (int) $user['id'];
            $params['teacher_class_id'] = (int) $user['id'];
        }

        $sql = 'SELECT exams.*, courses.title AS course_title, classes.name AS class_name,
                       subjects.name AS subject_name, creator.full_name AS creator_name,
                       COUNT(DISTINCT exam_questions.question_id) AS questions_count,
                       COUNT(DISTINCT exam_attempts.id) AS attempts_count
                FROM exams
                LEFT JOIN courses ON courses.id = exams.course_id
                LEFT JOIN classes ON classes.id = exams.class_id
                LEFT JOIN subjects ON subjects.id = exams.subject_id
                LEFT JOIN users creator ON creator.id = exams.creator_id
                LEFT JOIN exam_questions ON exam_questions.exam_id = exams.id
                LEFT JOIN exam_attempts ON exam_attempts.exam_id = exams.id';

        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' GROUP BY exams.id, courses.title, classes.name, subjects.name, creator.full_name
                  ORDER BY exams.created_at DESC';

        $statement = $this->db->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function create(array $data): int
    {
        $statement = $this->db->prepare(
            'INSERT INTO exams (
                course_id, class_id, subject_id, creator_id, title, description,
                time_limit_minutes, starts_at, ends_at, attempts_allowed,
                auto_correction_enabled, ranking_enabled, status, created_at, updated_at
             ) VALUES (
                :course_id, :class_id, :subject_id, :creator_id, :title, :description,
                :time_limit_minutes, :starts_at, :ends_at, :attempts_allowed,
                :auto_correction_enabled, :ranking_enabled, :status, NOW(), NOW()
             )'
        );
        $statement->execute($this->params($data));

        return (int) $this->db->lastInsertId();
    }

    public function find(int $id): ?array
    {
        $statement = $this->db->prepare(
            'SELECT exams.*, courses.title AS course_title, classes.name AS class_name,
                    subjects.name AS subject_name, creator.full_name AS creator_name
             FROM exams
             LEFT JOIN courses ON courses.id = exams.course_id
             LEFT JOIN classes ON classes.id = exams.class_id
             LEFT JOIN subjects ON subjects.id = exams.subject_id
             LEFT JOIN users creator ON creator.id = exams.creator_id
             WHERE exams.id = :id
             LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $exam = $statement->fetch();

        return $exam ?: null;
    }

    public function findAvailableForStudent(int $examId, int $studentId): ?array
    {
        $exams = $this->availableForStudent($studentId, $examId);

        return $exams[0] ?? null;
    }

    public function availableForStudent(int $studentId, ?int $examId = null): array
    {
        $where = [
            'exams.status = "publicado"',
            '(exams.starts_at IS NULL OR exams.starts_at <= NOW())',
            '(exams.ends_at IS NULL OR exams.ends_at >= NOW())',
            '(
                (exams.course_id IS NULL AND exams.class_id IS NULL AND exams.subject_id IS NULL)
                OR (exams.course_id IS NOT NULL AND EXISTS (
                    SELECT 1 FROM enrollments
                    WHERE enrollments.course_id = exams.course_id
                      AND enrollments.user_id = :course_student_id
                      AND enrollments.status IN ("ativa", "concluida")
                ))
                OR (exams.class_id IS NOT NULL AND EXISTS (
                    SELECT 1 FROM class_students
                    WHERE class_students.class_id = exams.class_id
                      AND class_students.user_id = :class_student_id
                      AND class_students.status = "ativo"
                ))
                OR (exams.subject_id IS NOT NULL AND EXISTS (
                    SELECT 1
                    FROM class_subjects
                    INNER JOIN class_students ON class_students.class_id = class_subjects.class_id
                    WHERE class_subjects.subject_id = exams.subject_id
                      AND class_subjects.status = "ativa"
                      AND class_students.user_id = :subject_student_id
                      AND class_students.status = "ativo"
                ))
            )',
        ];
        $params = [
            'course_student_id' => $studentId,
            'class_student_id' => $studentId,
            'subject_student_id' => $studentId,
            'attempt_student_id' => $studentId,
        ];

        if ($examId !== null) {
            $where[] = 'exams.id = :exam_id';
            $params['exam_id'] = $examId;
        }

        $statement = $this->db->prepare(
            'SELECT exams.*, courses.title AS course_title, classes.name AS class_name,
                    subjects.name AS subject_name,
                    COUNT(DISTINCT exam_questions.question_id) AS questions_count,
                    COUNT(DISTINCT exam_attempts.id) AS attempts_used,
                    MAX(exam_attempts.total_score) AS best_score,
                    MAX(exam_attempts.status = "corrigida") AS has_corrected_attempt
             FROM exams
             LEFT JOIN courses ON courses.id = exams.course_id
             LEFT JOIN classes ON classes.id = exams.class_id
             LEFT JOIN subjects ON subjects.id = exams.subject_id
             LEFT JOIN exam_questions ON exam_questions.exam_id = exams.id
             LEFT JOIN exam_attempts ON exam_attempts.exam_id = exams.id
                AND exam_attempts.student_id = :attempt_student_id
             WHERE ' . implode(' AND ', $where) . '
             GROUP BY exams.id, courses.title, classes.name, subjects.name
             ORDER BY COALESCE(exams.ends_at, exams.created_at) ASC'
        );
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function questionsForExam(int $examId): array
    {
        $statement = $this->db->prepare(
            'SELECT question_bank.*, exam_questions.position, exam_questions.score AS exam_score
             FROM exam_questions
             INNER JOIN question_bank ON question_bank.id = exam_questions.question_id
             WHERE exam_questions.exam_id = :exam_id
             ORDER BY exam_questions.position, question_bank.id'
        );
        $statement->execute(['exam_id' => $examId]);

        return $statement->fetchAll();
    }

    public function createQuestionForExam(int $examId, array $data): int
    {
        $this->db->beginTransaction();

        try {
            $statement = $this->db->prepare(
                'INSERT INTO question_bank (
                    creator_id, subject_id, statement_text, question_type, alternatives,
                    correct_answer, points, explanation, difficulty, status, created_at, updated_at
                 ) VALUES (
                    :creator_id, :subject_id, :statement_text, :question_type, :alternatives,
                    :correct_answer, :points, :explanation, :difficulty, "ativa", NOW(), NOW()
                 )'
            );
            $statement->execute([
                'creator_id' => $data['creator_id'],
                'subject_id' => $data['subject_id'] ?: null,
                'statement_text' => $data['statement_text'],
                'question_type' => $data['question_type'],
                'alternatives' => $data['alternatives'] ? json_encode($data['alternatives'], JSON_UNESCAPED_UNICODE) : null,
                'correct_answer' => $data['correct_answer'] ?: null,
                'points' => $data['points'],
                'explanation' => $data['explanation'] ?: null,
                'difficulty' => $data['difficulty'],
            ]);

            $questionId = (int) $this->db->lastInsertId();
            $position = $this->nextQuestionPosition($examId);
            $attach = $this->db->prepare(
                'INSERT INTO exam_questions (exam_id, question_id, position, score)
                 VALUES (:exam_id, :question_id, :position, :score)'
            );
            $attach->execute([
                'exam_id' => $examId,
                'question_id' => $questionId,
                'position' => $position,
                'score' => $data['points'],
            ]);

            $this->db->commit();

            return $questionId;
        } catch (Throwable $exception) {
            $this->db->rollBack();
            throw $exception;
        }
    }

    public function startAttempt(int $examId, int $studentId): int
    {
        $exam = $this->findAvailableForStudent($examId, $studentId);

        if (! $exam) {
            throw new RuntimeException('Prova não encontrada ou fora do período.');
        }

        if ((int) $exam['questions_count'] === 0) {
            throw new RuntimeException('Esta prova ainda não possui questões.');
        }

        $count = $this->attemptCount($examId, $studentId);

        if ($count >= (int) $exam['attempts_allowed']) {
            throw new RuntimeException('Limite de tentativas atingido.');
        }

        $statement = $this->db->prepare(
            'INSERT INTO exam_attempts (
                exam_id, student_id, attempt_number, status, started_at, objective_score,
                manual_score, total_score
             ) VALUES (
                :exam_id, :student_id, :attempt_number, "em_andamento", NOW(), 0, 0, 0
             )'
        );
        $statement->execute([
            'exam_id' => $examId,
            'student_id' => $studentId,
            'attempt_number' => $count + 1,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function findAttemptForStudent(int $attemptId, int $studentId): ?array
    {
        $statement = $this->db->prepare(
            'SELECT exam_attempts.*, exams.title, exams.description, exams.time_limit_minutes,
                    exams.ranking_enabled, exams.ends_at, courses.title AS course_title,
                    classes.name AS class_name, subjects.name AS subject_name
             FROM exam_attempts
             INNER JOIN exams ON exams.id = exam_attempts.exam_id
             LEFT JOIN courses ON courses.id = exams.course_id
             LEFT JOIN classes ON classes.id = exams.class_id
             LEFT JOIN subjects ON subjects.id = exams.subject_id
             WHERE exam_attempts.id = :id AND exam_attempts.student_id = :student_id
             LIMIT 1'
        );
        $statement->execute(['id' => $attemptId, 'student_id' => $studentId]);
        $attempt = $statement->fetch();

        return $attempt ?: null;
    }

    public function findAttempt(int $attemptId): ?array
    {
        $statement = $this->db->prepare(
            'SELECT exam_attempts.*, exams.title, exams.description, exams.time_limit_minutes,
                    exams.ranking_enabled, users.full_name AS student_name, users.email AS student_email
             FROM exam_attempts
             INNER JOIN exams ON exams.id = exam_attempts.exam_id
             INNER JOIN users ON users.id = exam_attempts.student_id
             WHERE exam_attempts.id = :id
             LIMIT 1'
        );
        $statement->execute(['id' => $attemptId]);
        $attempt = $statement->fetch();

        return $attempt ?: null;
    }

    public function questionsForAttempt(int $attemptId): array
    {
        $statement = $this->db->prepare(
            'SELECT question_bank.*, exam_questions.position, exam_questions.score AS exam_score
             FROM exam_attempts
             INNER JOIN exam_questions ON exam_questions.exam_id = exam_attempts.exam_id
             INNER JOIN question_bank ON question_bank.id = exam_questions.question_id
             WHERE exam_attempts.id = :attempt_id
             ORDER BY exam_questions.position, question_bank.id'
        );
        $statement->execute(['attempt_id' => $attemptId]);

        return $statement->fetchAll();
    }

    public function submitAttempt(int $attemptId, int $studentId, array $answers): array
    {
        $attempt = $this->findAttemptForStudent($attemptId, $studentId);

        if (! $attempt) {
            throw new RuntimeException('Tentativa não encontrada.');
        }

        if ($attempt['status'] !== 'em_andamento') {
            throw new RuntimeException('Esta tentativa já foi enviada.');
        }

        if ($this->isAttemptExpired($attempt)) {
            throw new RuntimeException('Tempo limite encerrado para está tentativa.');
        }

        $questions = $this->questionsForAttempt($attemptId);
        $objectiveScore = 0.0;
        $hasDiscursive = false;
        $answersJson = [];

        $this->db->beginTransaction();

        try {
            $answerStatement = $this->db->prepare(
                'INSERT INTO exam_answers (
                    attempt_id, question_id, selected_option, answer_text, is_correct,
                    score_awarded, feedback, status, created_at, updated_at
                 ) VALUES (
                    :attempt_id, :question_id, :selected_option, :answer_text, :is_correct,
                    :score_awarded, NULL, :status, NOW(), NOW()
                 )
                 ON DUPLICATE KEY UPDATE
                    selected_option = VALUES(selected_option),
                    answer_text = VALUES(answer_text),
                    is_correct = VALUES(is_correct),
                    score_awarded = VALUES(score_awarded),
                    status = VALUES(status),
                    updated_at = NOW()'
            );

            foreach ($questions as $question) {
                $questionId = (int) $question['id'];
                $answer = trim((string) ($answers[$questionId] ?? ''));
                $answersJson[$questionId] = $answer;
                $score = 0.0;
                $isCorrect = null;
                $status = 'pendente';
                $selectedOption = null;
                $answerText = $answer ?: null;

                if ($question['question_type'] === 'objetiva') {
                    $alternatives = $this->decodeAlternatives($question['alternatives'] ?? null);
                    $selectedOption = $answer ?: null;
                    $answerText = null;
                    $isCorrect = $this->isObjectiveCorrect($answer, (string) ($question['correct_answer'] ?? ''), $alternatives) ? 1 : 0;
                    $score = $isCorrect ? (float) $question['exam_score'] : 0.0;
                    $objectiveScore += $score;
                    $status = 'corrigida';
                } else {
                    $hasDiscursive = true;
                }

                $answerStatement->execute([
                    'attempt_id' => $attemptId,
                    'question_id' => $questionId,
                    'selected_option' => $selectedOption,
                    'answer_text' => $answerText,
                    'is_correct' => $isCorrect,
                    'score_awarded' => $score,
                    'status' => $status,
                ]);
            }

            $finalStatus = $hasDiscursive ? 'pendente_correcao' : 'corrigida';
            $update = $this->db->prepare(
                'UPDATE exam_attempts
                 SET answers = :answers,
                     status = :status,
                     score = :score,
                     objective_score = :objective_score,
                     manual_score = 0,
                     total_score = :total_score,
                     submitted_at = NOW(),
                     finished_at = NOW()
                 WHERE id = :id'
            );
            $update->execute([
                'answers' => json_encode($answersJson, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'status' => $finalStatus,
                'score' => $objectiveScore,
                'objective_score' => $objectiveScore,
                'total_score' => $objectiveScore,
                'id' => $attemptId,
            ]);

            $this->db->commit();

            return [
                'status' => $finalStatus,
                'objective_score' => $objectiveScore,
                'has_discursive' => $hasDiscursive,
            ];
        } catch (Throwable $exception) {
            $this->db->rollBack();
            throw $exception;
        }
    }

    public function attemptsForExam(int $examId): array
    {
        $statement = $this->db->prepare(
            'SELECT exam_attempts.*, users.full_name AS student_name, users.email AS student_email
             FROM exam_attempts
             INNER JOIN users ON users.id = exam_attempts.student_id
             WHERE exam_attempts.exam_id = :exam_id
             ORDER BY exam_attempts.started_at DESC'
        );
        $statement->execute(['exam_id' => $examId]);

        return $statement->fetchAll();
    }

    public function answersForAttempt(int $attemptId): array
    {
        $statement = $this->db->prepare(
            'SELECT exam_answers.*, question_bank.statement_text, question_bank.question_type,
                    question_bank.correct_answer, question_bank.alternatives,
                    exam_questions.score AS max_score
             FROM exam_answers
             INNER JOIN question_bank ON question_bank.id = exam_answers.question_id
             INNER JOIN exam_attempts ON exam_attempts.id = exam_answers.attempt_id
             INNER JOIN exam_questions ON exam_questions.exam_id = exam_attempts.exam_id
                AND exam_questions.question_id = exam_answers.question_id
             WHERE exam_answers.attempt_id = :attempt_id
             ORDER BY exam_questions.position, question_bank.id'
        );
        $statement->execute(['attempt_id' => $attemptId]);

        return $statement->fetchAll();
    }

    public function gradeAttempt(int $attemptId, array $scores, array $feedbacks, int $graderId): void
    {
        $answers = $this->answersForAttempt($attemptId);

        $this->db->beginTransaction();

        try {
            $updateAnswer = $this->db->prepare(
                'UPDATE exam_answers
                 SET score_awarded = :score_awarded,
                     feedback = :feedback,
                     status = "corrigida",
                     updated_at = NOW()
                 WHERE id = :id'
            );

            foreach ($answers as $answer) {
                if ($answer['question_type'] !== 'discursiva') {
                    continue;
                }

                $maxScore = (float) $answer['max_score'];
                $score = max(0.0, min($maxScore, (float) str_replace(',', '.', (string) ($scores[(int) $answer['id']] ?? 0))));
                $feedback = trim((string) ($feedbacks[(int) $answer['id']] ?? ''));

                $updateAnswer->execute([
                    'score_awarded' => $score,
                    'feedback' => $feedback ?: null,
                    'id' => (int) $answer['id'],
                ]);
            }

            $totals = $this->attemptTotals($attemptId);
            $updateAttempt = $this->db->prepare(
                'UPDATE exam_attempts
                 SET status = "corrigida",
                     score = :total_score,
                     objective_score = :objective_score,
                     manual_score = :manual_score,
                     total_score = :total_score_again,
                     graded_by = :graded_by,
                     graded_at = NOW()
                 WHERE id = :id'
            );
            $updateAttempt->execute([
                'total_score' => $totals['total_score'],
                'objective_score' => $totals['objective_score'],
                'manual_score' => $totals['manual_score'],
                'total_score_again' => $totals['total_score'],
                'graded_by' => $graderId,
                'id' => $attemptId,
            ]);

            $this->db->commit();
        } catch (Throwable $exception) {
            $this->db->rollBack();
            throw $exception;
        }
    }

    public function studentAttempts(int $studentId): array
    {
        $statement = $this->db->prepare(
            'SELECT exam_attempts.*, exams.title, exams.ranking_enabled, subjects.name AS subject_name,
                    courses.title AS course_title, classes.name AS class_name
             FROM exam_attempts
             INNER JOIN exams ON exams.id = exam_attempts.exam_id
             LEFT JOIN subjects ON subjects.id = exams.subject_id
             LEFT JOIN courses ON courses.id = exams.course_id
             LEFT JOIN classes ON classes.id = exams.class_id
             WHERE exam_attempts.student_id = :student_id
             ORDER BY exam_attempts.started_at DESC'
        );
        $statement->execute(['student_id' => $studentId]);

        return $statement->fetchAll();
    }

    public function performanceBySubject(int $studentId): array
    {
        $statement = $this->db->prepare(
            'SELECT COALESCE(subjects.name, "Geral") AS subject_name,
                    COUNT(*) AS attempts_count,
                    ROUND(AVG(exam_attempts.total_score), 2) AS average_score
             FROM exam_attempts
             INNER JOIN exams ON exams.id = exam_attempts.exam_id
             LEFT JOIN subjects ON subjects.id = exams.subject_id
             WHERE exam_attempts.student_id = :student_id
               AND exam_attempts.status IN ("pendente_correcao", "corrigida")
             GROUP BY COALESCE(subjects.name, "Geral")
             ORDER BY subject_name'
        );
        $statement->execute(['student_id' => $studentId]);

        return $statement->fetchAll();
    }

    public function ranking(int $examId): array
    {
        $statement = $this->db->prepare(
            'SELECT users.full_name, MAX(exam_attempts.total_score) AS best_score,
                    MIN(exam_attempts.finished_at) AS finished_at
             FROM exam_attempts
             INNER JOIN users ON users.id = exam_attempts.student_id
             WHERE exam_attempts.exam_id = :exam_id
               AND exam_attempts.status = "corrigida"
             GROUP BY users.id, users.full_name
             ORDER BY best_score DESC, finished_at ASC
             LIMIT 20'
        );
        $statement->execute(['exam_id' => $examId]);

        return $statement->fetchAll();
    }

    public function targetUsersForExam(int $examId): array
    {
        $exam = $this->find($examId);

        if (! $exam) {
            return [];
        }

        $statement = $this->db->prepare(
            'SELECT DISTINCT users.id
             FROM users
             INNER JOIN roles ON roles.id = users.role_id
             WHERE users.status = "aprovado"
               AND roles.slug IN ("aluno", "professor")
               AND (
                    :is_global = 1
                    OR (:course_id IS NOT NULL AND EXISTS (
                        SELECT 1 FROM enrollments
                        WHERE enrollments.user_id = users.id
                          AND enrollments.course_id = :course_id_match
                          AND enrollments.status IN ("ativa", "concluida")
                    ))
                    OR (:class_id IS NOT NULL AND EXISTS (
                        SELECT 1 FROM class_students
                        WHERE class_students.user_id = users.id
                          AND class_students.class_id = :class_id_match
                          AND class_students.status = "ativo"
                    ))
                    OR (:subject_id IS NOT NULL AND EXISTS (
                        SELECT 1
                        FROM class_subjects
                        INNER JOIN class_students ON class_students.class_id = class_subjects.class_id
                        WHERE class_students.user_id = users.id
                          AND class_subjects.subject_id = :subject_id_match
                          AND class_subjects.status = "ativa"
                          AND class_students.status = "ativo"
                    ))
               )'
        );
        $courseId = $exam['course_id'] ? (int) $exam['course_id'] : null;
        $classId = $exam['class_id'] ? (int) $exam['class_id'] : null;
        $subjectId = $exam['subject_id'] ? (int) $exam['subject_id'] : null;
        $statement->execute([
            'is_global' => (! $courseId && ! $classId && ! $subjectId) ? 1 : 0,
            'course_id' => $courseId,
            'course_id_match' => $courseId,
            'class_id' => $classId,
            'class_id_match' => $classId,
            'subject_id' => $subjectId,
            'subject_id_match' => $subjectId,
        ]);

        return $statement->fetchAll();
    }

    public function canManage(int $examId, array $user): bool
    {
        if (in_array($user['role_slug'], ['administrador', 'supervisor'], true)) {
            return true;
        }

        $statement = $this->db->prepare(
            'SELECT 1
             FROM exams
             LEFT JOIN courses ON courses.id = exams.course_id
             WHERE exams.id = :exam_id
               AND (
                    exams.creator_id = :creator_id
                    OR courses.responsible_teacher_id = :course_teacher_id
                    OR EXISTS (
                        SELECT 1 FROM class_subjects
                        WHERE class_subjects.class_id = exams.class_id
                          AND class_subjects.teacher_id = :subject_teacher_id
                          AND class_subjects.status = "ativa"
                    )
                    OR EXISTS (
                        SELECT 1 FROM class_teachers
                        WHERE class_teachers.class_id = exams.class_id
                          AND class_teachers.user_id = :class_teacher_id
                          AND class_teachers.status = "ativo"
                    )
               )
             LIMIT 1'
        );
        $statement->execute([
            'exam_id' => $examId,
            'creator_id' => (int) $user['id'],
            'course_teacher_id' => (int) $user['id'],
            'subject_teacher_id' => (int) $user['id'],
            'class_teacher_id' => (int) $user['id'],
        ]);

        return (bool) $statement->fetchColumn();
    }

    public function coursesForSelect(): array
    {
        return $this->db->query('SELECT id, title FROM courses ORDER BY title')->fetchAll();
    }

    public function classesForSelect(): array
    {
        return $this->db->query('SELECT id, name FROM classes WHERE status = "ativa" ORDER BY name')->fetchAll();
    }

    public function subjectsForSelect(): array
    {
        return $this->db->query('SELECT id, name FROM subjects WHERE status = "ativa" ORDER BY name')->fetchAll();
    }

    public function decodeAlternatives(?string $alternatives): array
    {
        if (! $alternatives) {
            return [];
        }

        $decoded = json_decode($alternatives, true);

        return is_array($decoded) ? array_values($decoded) : [];
    }

    public function remainingSeconds(array $attempt): ?int
    {
        if (empty($attempt['time_limit_minutes'])) {
            return null;
        }

        $startedAt = strtotime((string) $attempt['started_at']);

        if (! $startedAt) {
            return null;
        }

        $deadline = $startedAt + ((int) $attempt['time_limit_minutes'] * 60);

        return max(0, $deadline - time());
    }

    private function params(array $data): array
    {
        return [
            'course_id' => $data['course_id'] ?: null,
            'class_id' => $data['class_id'] ?: null,
            'subject_id' => $data['subject_id'] ?: null,
            'creator_id' => $data['creator_id'],
            'title' => $data['title'],
            'description' => $data['description'] ?: null,
            'time_limit_minutes' => $data['time_limit_minutes'] ?: null,
            'starts_at' => $data['starts_at'] ?: null,
            'ends_at' => $data['ends_at'] ?: null,
            'attempts_allowed' => max(1, (int) $data['attempts_allowed']),
            'auto_correction_enabled' => ! empty($data['auto_correction_enabled']) ? 1 : 0,
            'ranking_enabled' => ! empty($data['ranking_enabled']) ? 1 : 0,
            'status' => in_array($data['status'], ['rascunho', 'publicado', 'encerrado'], true) ? $data['status'] : 'rascunho',
        ];
    }

    private function nextQuestionPosition(int $examId): int
    {
        $statement = $this->db->prepare('SELECT COALESCE(MAX(position), 0) + 1 FROM exam_questions WHERE exam_id = :exam_id');
        $statement->execute(['exam_id' => $examId]);

        return (int) $statement->fetchColumn();
    }

    private function attemptCount(int $examId, int $studentId): int
    {
        $statement = $this->db->prepare('SELECT COUNT(*) FROM exam_attempts WHERE exam_id = :exam_id AND student_id = :student_id');
        $statement->execute(['exam_id' => $examId, 'student_id' => $studentId]);

        return (int) $statement->fetchColumn();
    }

    private function isAttemptExpired(array $attempt): bool
    {
        $remaining = $this->remainingSeconds($attempt);

        return $remaining !== null && $remaining <= 0;
    }

    private function isObjectiveCorrect(string $selected, string $correct, array $alternatives): bool
    {
        $selected = $this->normalizeAnswer($selected);
        $correct = trim($correct);

        if ($selected === '' || $correct === '') {
            return false;
        }

        $letters = ['A', 'B', 'C', 'D', 'E'];
        $upperCorrect = strtoupper($correct);

        if (in_array($upperCorrect, $letters, true)) {
            $index = array_search($upperCorrect, $letters, true);
            $expected = $alternatives[$index] ?? '';

            return $selected === $this->normalizeAnswer($expected);
        }

        return $selected === $this->normalizeAnswer($correct);
    }

    private function normalizeAnswer(string $value): string
    {
        $value = trim(preg_replace('/\s+/', ' ', $value) ?: '');

        return function_exists('mb_strtolower') ? mb_strtolower($value, 'UTF-8') : strtolower($value);
    }

    private function attemptTotals(int $attemptId): array
    {
        $statement = $this->db->prepare(
            'SELECT
                SUM(CASE WHEN question_bank.question_type = "objetiva" THEN exam_answers.score_awarded ELSE 0 END) AS objective_score,
                SUM(CASE WHEN question_bank.question_type = "discursiva" THEN exam_answers.score_awarded ELSE 0 END) AS manual_score,
                SUM(exam_answers.score_awarded) AS total_score
             FROM exam_answers
             INNER JOIN question_bank ON question_bank.id = exam_answers.question_id
             WHERE exam_answers.attempt_id = :attempt_id'
        );
        $statement->execute(['attempt_id' => $attemptId]);
        $totals = $statement->fetch() ?: [];

        return [
            'objective_score' => (float) ($totals['objective_score'] ?? 0),
            'manual_score' => (float) ($totals['manual_score'] ?? 0),
            'total_score' => (float) ($totals['total_score'] ?? 0),
        ];
    }
}
