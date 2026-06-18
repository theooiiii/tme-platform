<?php

defined('BASE_PATH') || exit('Acesso direto não permitido.');

class CertificateService
{
    private Certificate $certificates;
    private Enrollment $enrollments;
    private ActionLog $logs;
    private GamificationService $gamification;
    private NotificationService $notifications;

    public function __construct()
    {
        $this->certificates = new Certificate();
        $this->enrollments = new Enrollment();
        $this->logs = new ActionLog();
        $this->gamification = new GamificationService();
        $this->notifications = new NotificationService();
    }

    public function issueForEnrollment(int $enrollmentId): ?array
    {
        $existing = $this->certificates->findByEnrollment($enrollmentId);

        if ($existing) {
            return $existing;
        }

        $enrollment = $this->enrollments->find($enrollmentId);

        if (
            ! $enrollment ||
            $enrollment['status'] !== 'concluida' ||
            (float) $enrollment['progress_percent'] < 100
        ) {
            return null;
        }

        $certificateId = $this->certificates->createCourseCertificate([
            'user_id' => (int) $enrollment['user_id'],
            'enrollment_id' => $enrollmentId,
            'course_id' => (int) $enrollment['course_id'],
            'code' => $this->uniqueCode(),
            'title' => 'Certificado de conclusao - ' . $enrollment['course_title'],
            'workload_hours' => (int) $enrollment['workload_hours'],
        ]);

        $certificate = $this->certificates->find($certificateId);
        $this->logs->record((int) $enrollment['user_id'], 'certificate.issued', [
            'certificate_id' => $certificateId,
            'enrollment_id' => $enrollmentId,
            'course_id' => (int) $enrollment['course_id'],
            'code' => $certificate['code'] ?? null,
        ]);

        $this->gamification->certificateIssued((int) $enrollment['user_id'], $certificateId, (int) $enrollment['course_id']);
        $this->notifications->certificateIssued((int) $enrollment['user_id'], (string) $certificate['code'], (string) $enrollment['course_title']);

        return $certificate;
    }

    public function issueForEventRegistration(int $registrationId): ?array
    {
        $existing = $this->certificates->findByEventRegistration($registrationId);

        if ($existing) {
            return $existing;
        }

        $events = new Event();
        $registration = $events->findRegistration($registrationId);

        if (
            ! $registration ||
            $registration['status'] !== 'confirmado' ||
            $registration['event_status'] !== 'encerrado' ||
            ! (bool) $registration['certificate_enabled']
        ) {
            return null;
        }

        $certificateId = $this->certificates->createEventCertificate([
            'user_id' => (int) $registration['user_id'],
            'event_registration_id' => $registrationId,
            'event_id' => (int) $registration['event_id'],
            'code' => $this->uniqueCode('EVT'),
            'title' => 'Certificado de participacao - ' . $registration['event_title'],
            'workload_hours' => (int) $registration['workload_hours'],
        ]);

        $events->setCertificate($registrationId, $certificateId);
        $certificate = $this->certificates->find($certificateId);
        $this->logs->record((int) $registration['user_id'], 'event.certificate_issued', [
            'certificate_id' => $certificateId,
            'event_id' => (int) $registration['event_id'],
            'registration_id' => $registrationId,
            'code' => $certificate['code'] ?? null,
        ]);
        $this->notifications->certificateIssued((int) $registration['user_id'], (string) $certificate['code'], (string) $registration['event_title']);

        return $certificate;
    }

    private function uniqueCode(string $type = 'CUR'): string
    {
        do {
            $code = 'TME-' . $type . '-' . date('Y') . '-' . strtoupper(bin2hex(random_bytes(4)));
        } while ($this->certificates->findByCode($code));

        return $code;
    }
}
