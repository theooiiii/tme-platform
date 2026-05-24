<?php

defined('BASE_PATH') || exit('Acesso direto nao permitido.');

class DashboardController extends Controller
{
    public function index(): void
    {
        $user = current_user();
        $users = new User();
        $counts = $users->dashboardCounts();
        $gamification = new Gamification();
        $gamification->ensureProfile((int) $user['id']);
        $analyticsModel = new Analytics();
        $period = $analyticsModel->periodFromRequest($_GET);
        $dashboardAnalytics = [];

        if (in_array($user['role_slug'], ['administrador', 'supervisor'], true)) {
            $dashboardAnalytics = $analyticsModel->admin($period);
        } elseif ($user['role_slug'] === 'professor') {
            $dashboardAnalytics = $analyticsModel->teacher((int) $user['id'], $period);
        } elseif ($user['role_slug'] === 'aluno') {
            $dashboardAnalytics = $analyticsModel->student((int) $user['id'], $period);
        }

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
            'profile' => $gamification->profile((int) $user['id']),
            'stats' => $users->profileStats((int) $user['id']),
            'badges' => $gamification->badgesForUser((int) $user['id'], 4),
            'dashboardAnalytics' => $dashboardAnalytics,
            'period' => $period,
            'usesCharts' => ! empty($dashboardAnalytics),
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
