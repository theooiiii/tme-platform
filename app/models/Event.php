<?php

defined('BASE_PATH') || exit('Acesso direto não permitido.');

class Event extends Model
{
    public function published(?int $viewerId = null): array
    {
        $params = [];

        if ($viewerId) {
            $params['viewer_id'] = $viewerId;
        }

        $statement = $this->db->prepare($this->baseSelect((bool) $viewerId) . '
            WHERE events.status = "publicado"
            ORDER BY events.starts_at ASC, events.created_at DESC'
        );
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function adminList(): array
    {
        $statement = $this->db->query($this->baseSelect(false) . ' ORDER BY events.starts_at DESC, events.created_at DESC');

        return $statement->fetchAll();
    }

    public function find(int $id, ?int $viewerId = null): ?array
    {
        $params = ['id' => $id];

        if ($viewerId) {
            $params['viewer_id'] = $viewerId;
        }

        $statement = $this->db->prepare($this->baseSelect((bool) $viewerId) . ' WHERE events.id = :id LIMIT 1');
        $statement->execute($params);
        $event = $statement->fetch();

        return $event ?: null;
    }

    public function create(array $data): int
    {
        $statement = $this->db->prepare(
            'INSERT INTO events (
                creator_id, title, event_type, description, starts_at, ends_at,
                location, is_online, meeting_url, capacity, workload_hours,
                image_path, certificate_enabled, status, created_at, updated_at
             ) VALUES (
                :creator_id, :title, :event_type, :description, :starts_at, :ends_at,
                :location, :is_online, :meeting_url, :capacity, :workload_hours,
                :image_path, :certificate_enabled, :status, NOW(), NOW()
             )'
        );
        $statement->execute($this->writeParams($data));

        return (int) $this->db->lastInsertId();
    }

    public function setStatus(int $id, string $status): void
    {
        $statement = $this->db->prepare('UPDATE events SET status = :status, updated_at = NOW() WHERE id = :id');
        $statement->execute([
            'status' => $status,
            'id' => $id,
        ]);
    }

    public function registrations(int $eventId): array
    {
        $statement = $this->db->prepare(
            'SELECT event_registrations.*, users.full_name, users.email, certificates.code AS certificate_code
             FROM event_registrations
             INNER JOIN users ON users.id = event_registrations.user_id
             LEFT JOIN certificates ON certificates.id = event_registrations.certificate_id
             WHERE event_registrations.event_id = :event_id
             ORDER BY event_registrations.registered_at DESC'
        );
        $statement->execute(['event_id' => $eventId]);

        return $statement->fetchAll();
    }

    public function register(int $eventId, int $userId): int
    {
        $statement = $this->db->prepare(
            'INSERT INTO event_registrations (event_id, user_id, status, registered_at, created_at, updated_at)
             VALUES (:event_id, :user_id, "inscrito", NOW(), NOW(), NOW())'
        );
        $statement->execute(['event_id' => $eventId, 'user_id' => $userId]);

        return (int) $this->db->lastInsertId();
    }

    public function findRegistration(int $registrationId): ?array
    {
        $statement = $this->db->prepare(
            'SELECT event_registrations.*, events.title AS event_title, events.workload_hours,
                    events.status AS event_status, events.certificate_enabled
             FROM event_registrations
             INNER JOIN events ON events.id = event_registrations.event_id
             WHERE event_registrations.id = :id
             LIMIT 1'
        );
        $statement->execute(['id' => $registrationId]);
        $registration = $statement->fetch();

        return $registration ?: null;
    }

    public function registrationForUser(int $eventId, int $userId): ?array
    {
        $statement = $this->db->prepare(
            'SELECT * FROM event_registrations WHERE event_id = :event_id AND user_id = :user_id LIMIT 1'
        );
        $statement->execute(['event_id' => $eventId, 'user_id' => $userId]);
        $registration = $statement->fetch();

        return $registration ?: null;
    }

    public function confirmPresence(int $registrationId): void
    {
        $statement = $this->db->prepare(
            'UPDATE event_registrations
             SET status = "confirmado", attended_at = NOW(), updated_at = NOW()
             WHERE id = :id'
        );
        $statement->execute(['id' => $registrationId]);
    }

    public function setCertificate(int $registrationId, int $certificateId): void
    {
        $statement = $this->db->prepare(
            'UPDATE event_registrations
             SET certificate_id = :certificate_id, updated_at = NOW()
             WHERE id = :id'
        );
        $statement->execute([
            'certificate_id' => $certificateId,
            'id' => $registrationId,
        ]);
    }

    public function registrationsForUser(int $userId): array
    {
        $statement = $this->db->prepare(
            $this->baseSelect(false) . '
             INNER JOIN event_registrations viewer_registration ON viewer_registration.event_id = events.id
             WHERE viewer_registration.user_id = :user_id
             ORDER BY events.starts_at ASC'
        );
        $statement->execute(['user_id' => $userId]);

        return $statement->fetchAll();
    }

    private function baseSelect(bool $viewer): string
    {
        $viewerColumn = $viewer
            ? '(SELECT status FROM event_registrations WHERE event_registrations.event_id = events.id AND event_registrations.user_id = :viewer_id LIMIT 1) AS viewer_registration_status,'
            : 'NULL AS viewer_registration_status,';

        return 'SELECT events.*,
                       creator.full_name AS creator_name,
                       ' . $viewerColumn . '
                       (SELECT COUNT(*) FROM event_registrations WHERE event_registrations.event_id = events.id AND event_registrations.status <> "cancelado") AS registrations_count
                FROM events
                LEFT JOIN users creator ON creator.id = events.creator_id';
    }

    private function writeParams(array $data): array
    {
        return [
            'creator_id' => $data['creator_id'],
            'title' => $data['title'],
            'event_type' => $data['event_type'],
            'description' => $data['description'] ?: null,
            'starts_at' => $data['starts_at'] ?: null,
            'ends_at' => $data['ends_at'] ?: null,
            'location' => $data['location'] ?: null,
            'is_online' => ! empty($data['is_online']) ? 1 : 0,
            'meeting_url' => $data['meeting_url'] ?: null,
            'capacity' => $data['capacity'] ?: null,
            'workload_hours' => $data['workload_hours'],
            'image_path' => $data['image_path'] ?: null,
            'certificate_enabled' => ! empty($data['certificate_enabled']) ? 1 : 0,
            'status' => $data['status'],
        ];
    }
}
