<?php

defined('BASE_PATH') || exit('Acesso direto não permitido.');

class CertificateController extends Controller
{
    private Certificate $certificates;
    private ActionLog $logs;

    public function __construct()
    {
        $this->certificates = new Certificate();
        $this->logs = new ActionLog();
    }

    public function index(): void
    {
        $user = current_user();

        $this->view('certificates/index', [
            'title' => 'Certificados',
            'certificates' => $this->certificates->forUser((int) $user['id']),
        ]);
    }

    public function show(string $code): void
    {
        $certificate = $this->findCertificateOrRedirect($code, '/certificados');
        $user = current_user();

        if (! $this->canViewCertificate($certificate, $user)) {
            http_response_code(403);
            $this->view('errors/403', ['title' => 'Acesso restrito']);
            return;
        }

        $this->logs->record((int) $user['id'], 'certificate.viewed', [
            'certificate_id' => (int) $certificate['id'],
            'code' => $certificate['code'],
        ]);

        $this->view('certificates/show', [
            'title' => 'Certificado ' . $certificate['code'],
            'certificate' => $certificate,
        ]);
    }

    public function validateForm(): void
    {
        $this->view('certificates/validate', [
            'title' => 'Validar certificado',
        ]);
    }

    public function validatePost(): void
    {
        if (! verify_csrf_token($_POST['_csrf'] ?? null)) {
            flash('error', 'Sua sessao expirou. Tente novamente.');
            $this->redirect('/certificados/validar');
        }

        $code = strtoupper(trim($_POST['code'] ?? ''));

        if ($code === '') {
            flash('error', 'Informe o código do certificado.');
            $this->redirect('/certificados/validar');
        }

        $this->redirect('/certificados/validar/' . rawurlencode($code));
    }

    public function validateCode(string $code): void
    {
        $certificate = $this->certificates->findByCode($code);
        $viewer = current_user();
        $this->logs->record($viewer ? (int) $viewer['id'] : null, 'certificate.validated', [
            'code' => strtoupper(trim($code)),
            'found' => (bool) $certificate,
            'status' => $certificate['validation_status'] ?? 'nao_encontrado',
        ]);

        $this->view('certificates/validation_result', [
            'title' => 'Resultado da validação',
            'certificate' => $certificate,
            'code' => strtoupper(trim($code)),
        ]);
    }

    public function adminIndex(): void
    {
        $filters = [
            'status' => trim($_GET['status'] ?? ''),
            'course_id' => trim($_GET['course_id'] ?? ''),
            'user_id' => trim($_GET['user_id'] ?? ''),
            'q' => trim($_GET['q'] ?? ''),
        ];

        $this->view('admin/certificates/index', [
            'title' => 'Certificados emitidos',
            'certificates' => $this->certificates->adminList($filters),
            'courses' => $this->certificates->coursesWithCertificates(),
            'students' => $this->certificates->usersWithCertificates(),
            'filters' => $filters,
        ]);
    }

    public function revoke(string $id): void
    {
        if (! verify_csrf_token($_POST['_csrf'] ?? null)) {
            flash('error', 'Sua sessao expirou. Tente novamente.');
            $this->redirect('/admin/certificados');
        }

        $certificate = $this->certificates->find((int) $id);

        if (! $certificate) {
            flash('error', 'Certificado não encontrado.');
            $this->redirect('/admin/certificados');
        }

        $reason = trim($_POST['revocation_reason'] ?? '');

        if (strlen($reason) < 6) {
            flash('error', 'Informe um motivo de revogação com pelo menos 6 caracteres.');
            $this->redirect('/admin/certificados');
        }

        $user = current_user();
        $this->certificates->revoke((int) $certificate['id'], (int) $user['id'], $reason);
        $this->logs->record((int) $user['id'], 'certificate.revoked', [
            'certificate_id' => (int) $certificate['id'],
            'code' => $certificate['code'],
            'reason' => $reason,
        ], 'warning');

        flash('success', 'Certificado revogado.');
        $this->redirect('/admin/certificados');
    }

    private function findCertificateOrRedirect(string $code, string $fallback): array
    {
        $certificate = $this->certificates->findByCode($code);

        if (! $certificate) {
            flash('error', 'Certificado não encontrado.');
            $this->redirect($fallback);
        }

        return $certificate;
    }

    private function canViewCertificate(array $certificate, ?array $user): bool
    {
        if (! $user) {
            return false;
        }

        if ((int) $certificate['user_id'] === (int) $user['id']) {
            return true;
        }

        return in_array($user['role_slug'], ['administrador', 'supervisor'], true);
    }
}
