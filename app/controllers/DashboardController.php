<?php

defined('BASE_PATH') || exit('Acesso direto nao permitido.');

class DashboardController extends Controller
{
    public function index(): void
    {
        $user = current_user();
        $counts = (new User())->dashboardCounts();

        $views = [
            'aluno' => 'dashboard/student',
            'professor' => 'dashboard/teacher',
            'supervisor' => 'dashboard/supervisor',
            'administrador' => 'dashboard/admin',
            'secretaria' => 'dashboard/secretary',
            'financeiro' => 'dashboard/finance',
        ];

        $view = $views[$user['role_slug']] ?? 'dashboard/student';

        $this->view($view, [
            'title' => 'Dashboard',
            'user' => $user,
            'counts' => $counts,
        ]);
    }

    public function updateSettings(): void
    {
        if (! verify_csrf_token($_POST['_csrf'] ?? null)) {
            flash('error', 'Sua sessão expirou. Tente novamente.');
            $this->redirect('/dashboard');
        }

        $theme = $_POST['theme'] ?? config('app.default_theme', 'light');
        $primaryColor = $_POST['primary_color'] ?? config('app.default_primary_color', '#1f6feb');

        if (! in_array($theme, ['light', 'dark'], true)) {
            $theme = 'light';
        }

        if (! preg_match('/^#[0-9a-fA-F]{6}$/', $primaryColor)) {
            $primaryColor = config('app.default_primary_color', '#1f6feb');
        }

        (new User())->updateSettings((int) current_user()['id'], $theme, $primaryColor);

        flash('success', 'Preferências visuais atualizadas.');

        $redirect = (string) ($_POST['redirect_to'] ?? '/dashboard');
        $this->redirect(str_starts_with($redirect, '/') ? $redirect : '/dashboard');
    }
}
