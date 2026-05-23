<?php

defined('BASE_PATH') || exit('Acesso direto nao permitido.');

class Activity extends Model
{
    public function allForManager(array $filters, array $user): array
    {
        $where = [];
        $params = [];

        if ($user['role_slug'] === 'professor') {
            $where[] = 'activities.teacher_id = :teacher_id';
            $params['teacher_id'] = (int) $user['id'];
        }

        if (! empty($filters['course_id'])) {
            $where[] = 'activities.course_id = :course_id';
            $params['course_id'] = (int) $filters['course_id'];
        }

        if (! empty($filters['status'])) {
            $where[] = 'activities.status = :status';
            $params['status'] = $filters['status'];
        }

        if (! empty($filters['type'])) {
            $where[] = 'activities.activity_type = :type';
            $params['type'] = $filters['type'];
        }

        $sql = $this->baseSelect() . '
                LEFT JOIN submissions ON submissions.activity_id = activities.id';

        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' GROUP BY activities.id, courses.title, course_modules.title, lessons.title, teacher.full_name
                  ORDER BY COALESCE(activities.due_at, activities.created_at) DESC';

        $statement = $this->db->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function forStudent(int $studentId): array
    {
        $statement = $this->db->prepare(
            $this->baseSelect() . '
             INNER JOIN enrollments ON enrollments.course_id = activities.course_id
                AND enrollments.user_id = :student_id
                AND enrollments.status IN ("ativa", "concluida")
             LEFT JOIN submissions ON submissions.activity_id = activities.id AND submissions.student_id = :student_id
             LEFT JOIN grades ON grades.activity_id = activities.id AND grades.student_id = :student_id
             WHERE activities.status IN ("publicada", "encerrada")
             GROUP BY activities.id, courses.title, course_modules.title, lessons.title, teacher.full_name,
                      submissions.id, submissions.status, submissions.submitted_at, grades.score, grades.feedback
             ORDER BY COALESCE(activities.due_at, activities.created_at) ASC'
        );
        $statement->execute(['student_id' => $studentId]);

        return $statement->fetchAll();
    }

    public function gradebookForStudent(int $studentId): array
    {
        $statement = $this->db->prepare(
            'SELECT courses.id AS course_id, courses.title AS course_title,
                    COUNT(DISTINCT activities.id) AS activities_count,
                    COUNT(DISTINCT grades.id) AS graded_count,
                    ROUND(AVG(grades.score), 2) AS average_score,
                    SUM(grades.score) AS total_score,
                    SUM(activities.max_score) AS max_total
             FROM enrollments
             INNER JOIN courses ON courses.id = enrollments.course_id
             LEFT JOIN activities ON activities.course_id = courses.id AND activities.status IN ("publicada", "encerrada")
             LEFT JOIN grades ON grades.activity_id = activities.id AND grades.student_id = enrollments.user_id
             WHERE enrollments.user_id = :student_id
             GROUP BY courses.id, courses.title
             ORDER BY courses.title'
        );
        $statement->execute(['student_id' => $studentId]);

        return $statement->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->db->prepare(
            $this->baseSelect() . '
             WHERE activities.id = :id
             GROUP BY activities.id, courses.title, course_modules.title, lessons.title, teacher.full_name
             LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $activity = $statement->fetch();

        return $activity ?: null;
    }

    public function findForStudent(int $id, int $studentId): ?array
    {
        $statement = $this->db->prepare(
            $this->baseSelect() . '
             INNER JOIN enrollments ON enrollments.course_id = activities.course_id
                AND enrollments.user_id = :student_id
                AND enrollments.status IN ("ativa", "concluida")
             LEFT JOIN submissions ON submissions.activity_id = activities.id AND submissions.student_id = :student_id
             LEFT JOIN grades ON grades.activity_id = activities.id AND grades.student_id = :student_id
             WHERE activities.id = :id AND activities.status IN ("publicada", "encerrada")
             GROUP BY activities.id, courses.title, course_modules.title, lessons.title, teacher.full_name,
                      submissions.id, submissions.status, submissions.content, submissions.file_path,
                      submissions.submitted_at, grades.score, grades.feedback, grades.graded_at
             LIMIT 1'
        );
        $statement->execute(['id' => $id, 'student_id' => $studentId]);
        $activity = $statement->fetch();

        return $activity ?: null;
    }

    public function create(array $data): int
    {
        $statement = $this->db->prepare(
            'INSERT INTO activities (
                course_id, module_id, lesson_id, class_id, subject_id, teacher_id,
                title, description, instructions, activity_type, due_at, max_score,
                allow_late, attachment_path, status, created_at, updated_at
             ) VALUES (
                :course_id, :module_id, :lesson_id, :class_id, :subject_id, :teacher_id,
                :title, :description, :instructions, :activity_type, :due_at, :max_score,
                :allow_late, :attachment_path, :status, NOW(), NOW()
             )'
        );
        $statement->execute($this->writeParams($data));

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $params = $this->writeParams($data);
        $params['id'] = $id;

        $statement = $this->db->prepare(
            'UPDATE activities
             SET course_id = :course_id,
                 module_id = :module_id,
                 lesson_id = :lesson_id,
                 class_id = :class_id,
                 subject_id = :subject_id,
                 teacher_id = :teacher_id,
                 title = :title,
                 description = :description,
                 instructions = :instructions,
                 activity_type = :activity_type,
                 due_at = :due_at,
                 max_score = :max_score,
                 allow_late = :allow_late,
                 attachment_path = COALESCE(:attachment_path, attachment_path),
                 status = :status,
                 updated_at = NOW()
             WHERE id = :id'
        );
        $statement->execute($params);
    }

    public function archive(int $id): void
    {
        $statement = $this->db->prepare('UPDATE activities SET status = "encerrada", updated_at = NOW() WHERE id = :id');
        $statement->execute(['id' => $id]);
    }

    public function submissions(int $activityId): array
    {
        $statement = $this->db->prepare(
            'SELECT submissions.*, users.full_name AS student_name, users.email AS student_email,
                    grades.score, grades.feedback, grades.graded_at, grader.full_name AS grader_name
             FROM submissions
             INNER JOIN users ON users.id = submissions.student_id
             LEFT JOIN grades ON grades.submission_id = submissions.id
             LEFT JOIN users grader ON grader.id = grades.teacher_id
             WHERE submissions.activity_id = :activity_id
             ORDER BY submissions.submitted_at DESC'
        );
        $statement->execute(['activity_id' => $activityId]);

        return $statement->fetchAll();
    }

    public function coursesForSelect(): array
    {
        return $this->db->query('SELECT id, title FROM courses ORDER BY title')->fetchAll();
    }

    public function modulesForSelect(): array
    {
        return $this->db->query(
            'SELECT course_modules.id, course_modules.title, courses.title AS course_title
             FROM course_modules
             INNER JOIN courses ON courses.id = course_modules.course_id
             ORDER BY courses.title, course_modules.position'
        )->fetchAll();
    }

    public function lessonsForSelect(): array
    {
        return $this->db->query(
            'SELECT lessons.id, lessons.title, courses.title AS course_title
             FROM lessons
             INNER JOIN courses ON courses.id = lessons.course_id
             ORDER BY courses.title, lessons.position'
        )->fetchAll();
    }

    private function baseSelect(): string
    {
        return 'SELECT activities.*,
                       courses.title AS course_title,
                       course_modules.title AS module_title,
                       lessons.title AS lesson_title,
                       teacher.full_name AS teacher_name,
                       COUNT(DISTINCT submissions.id) AS submissions_count,
                       submissions.id AS submission_id,
                       submissions.status AS submission_status,
                       submissions.content AS submission_content,
                       submissions.file_path AS submission_file_path,
                       submissions.submitted_at AS submitted_at,
                       grades.score AS grade_score,
                       grades.feedback AS grade_feedback,
                       grades.graded_at AS graded_at
                FROM activities
                LEFT JOIN courses ON courses.id = activities.course_id
                LEFT JOIN course_modules ON course_modules.id = activities.module_id
                LEFT JOIN lessons ON lessons.id = activities.lesson_id
                LEFT JOIN users teacher ON teacher.id = activities.teacher_id';
    }

    private function writeParams(array $data): array
    {
        return [
            'course_id' => $data['course_id'] ?: null,
            'module_id' => $data['module_id'] ?: null,
            'lesson_id' => $data['lesson_id'] ?: null,
            'class_id' => $data['class_id'] ?: null,
            'subject_id' => $data['subject_id'] ?: null,
            'teacher_id' => $data['teacher_id'] ?: null,
            'title' => $data['title'],
            'description' => $data['description'] ?: null,
            'instructions' => $data['instructions'] ?: null,
            'activity_type' => $data['activity_type'],
            'due_at' => $data['due_at'] ?: null,
            'max_score' => $data['max_score'],
            'allow_late' => ! empty($data['allow_late']) ? 1 : 0,
            'attachment_path' => $data['attachment_path'] ?: null,
            'status' => $data['status'],
        ];
    }
}
