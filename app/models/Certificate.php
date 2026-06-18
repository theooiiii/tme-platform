<?php

defined('BASE_PATH') || exit('Acesso direto não permitido.');

class Certificate extends Model
{
    public function find(int $id): ?array
    {
        $statement = $this->db->prepare($this->baseSelect() . ' WHERE certificates.id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $certificate = $statement->fetch();

        return $certificate ?: null;
    }

    public function findByCode(string $code): ?array
    {
        $statement = $this->db->prepare($this->baseSelect() . ' WHERE certificates.code = :code LIMIT 1');
        $statement->execute(['code' => strtoupper(trim($code))]);
        $certificate = $statement->fetch();

        return $certificate ?: null;
    }

    public function findByEnrollment(int $enrollmentId): ?array
    {
        $statement = $this->db->prepare($this->baseSelect() . ' WHERE certificates.enrollment_id = :enrollment_id LIMIT 1');
        $statement->execute(['enrollment_id' => $enrollmentId]);
        $certificate = $statement->fetch();

        return $certificate ?: null;
    }

    public function findByEventRegistration(int $registrationId): ?array
    {
        $statement = $this->db->prepare($this->baseSelect() . ' WHERE certificates.event_registration_id = :registration_id LIMIT 1');
        $statement->execute(['registration_id' => $registrationId]);
        $certificate = $statement->fetch();

        return $certificate ?: null;
    }

    public function forUser(int $userId): array
    {
        $statement = $this->db->prepare(
            $this->baseSelect() . '
             WHERE certificates.user_id = :user_id
             ORDER BY certificates.issued_at DESC, certificates.created_at DESC'
        );
        $statement->execute(['user_id' => $userId]);

        return $statement->fetchAll();
    }

    public function adminList(array $filters = []): array
    {
        $where = [];
        $params = [];

        if (! empty($filters['status'])) {
            $where[] = 'certificates.validation_status = :status';
            $params['status'] = $filters['status'];
        }

        if (! empty($filters['course_id'])) {
            $where[] = 'certificates.course_id = :course_id';
            $params['course_id'] = (int) $filters['course_id'];
        }

        if (! empty($filters['user_id'])) {
            $where[] = 'certificates.user_id = :user_id';
            $params['user_id'] = (int) $filters['user_id'];
        }

        if (! empty($filters['q'])) {
            $where[] = '(certificates.code LIKE :q_code OR certificates.title LIKE :q_title OR users.full_name LIKE :q_student)';
            $params['q_code'] = '%' . $filters['q'] . '%';
            $params['q_title'] = '%' . $filters['q'] . '%';
            $params['q_student'] = '%' . $filters['q'] . '%';
        }

        $sql = $this->baseSelect() . ($where ? ' WHERE ' . implode(' AND ', $where) : '');
        $sql .= ' ORDER BY certificates.issued_at DESC, certificates.created_at DESC';

        $statement = $this->db->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function createCourseCertificate(array $data): int
    {
        $statement = $this->db->prepare(
            'INSERT INTO certificates (
                user_id, enrollment_id, course_id, certificate_type, code, title,
                workload_hours, validation_status, issued_at, created_at, updated_at
             ) VALUES (
                :user_id, :enrollment_id, :course_id, "curso", :code, :title,
                :workload_hours, "valido", NOW(), NOW(), NOW()
             )'
        );
        $statement->execute([
            'user_id' => $data['user_id'],
            'enrollment_id' => $data['enrollment_id'],
            'course_id' => $data['course_id'],
            'code' => strtoupper($data['code']),
            'title' => $data['title'],
            'workload_hours' => $data['workload_hours'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function createEventCertificate(array $data): int
    {
        $statement = $this->db->prepare(
            'INSERT INTO certificates (
                user_id, event_registration_id, event_id, certificate_type, code, title,
                workload_hours, validation_status, issued_at, created_at, updated_at
             ) VALUES (
                :user_id, :event_registration_id, :event_id, "evento", :code, :title,
                :workload_hours, "valido", NOW(), NOW(), NOW()
             )'
        );
        $statement->execute([
            'user_id' => $data['user_id'],
            'event_registration_id' => $data['event_registration_id'],
            'event_id' => $data['event_id'],
            'code' => strtoupper($data['code']),
            'title' => $data['title'],
            'workload_hours' => $data['workload_hours'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function revoke(int $id, int $adminId, string $reason): void
    {
        $statement = $this->db->prepare(
            'UPDATE certificates
             SET validation_status = "revogado",
                 revoked_by = :revoked_by,
                 revoked_at = NOW(),
                 revocation_reason = :reason,
                 updated_at = NOW()
             WHERE id = :id'
        );
        $statement->execute([
            'revoked_by' => $adminId,
            'reason' => $reason,
            'id' => $id,
        ]);
    }

    public function coursesWithCertificates(): array
    {
        $statement = $this->db->query(
            'SELECT DISTINCT courses.id, courses.title
             FROM courses
             INNER JOIN certificates ON certificates.course_id = courses.id
             ORDER BY courses.title'
        );

        return $statement->fetchAll();
    }

    public function usersWithCertificates(): array
    {
        $statement = $this->db->query(
            'SELECT DISTINCT users.id, users.full_name, users.email
             FROM users
             INNER JOIN certificates ON certificates.user_id = users.id
             ORDER BY users.full_name'
        );

        return $statement->fetchAll();
    }

    private function baseSelect(): string
    {
        return 'SELECT certificates.*,
                       users.full_name AS student_name,
                       users.email AS student_email,
                       courses.title AS course_title,
                       courses.category AS course_category,
                       courses.workload_hours AS course_workload_hours,
                       events.title AS event_title,
                       enrollments.completed_at AS enrollment_completed_at,
                       revoker.full_name AS revoked_by_name
                FROM certificates
                INNER JOIN users ON users.id = certificates.user_id
                LEFT JOIN courses ON courses.id = certificates.course_id
                LEFT JOIN events ON events.id = certificates.event_id
                LEFT JOIN enrollments ON enrollments.id = certificates.enrollment_id
                LEFT JOIN users revoker ON revoker.id = certificates.revoked_by';
    }
}
