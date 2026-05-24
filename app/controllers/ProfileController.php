<?php

defined('BASE_PATH') || exit('Acesso direto nao permitido.');

class ProfileController extends Controller
{
    private User $users;
    private Gamification $gamification;
    private ActionLog $logs;

    public function __construct()
    {
        $this->users = new User();
        $this->gamification = new Gamification();
        $this->logs = new ActionLog();
    }

    public function index(): void
    {
        $user = current_user();
        $this->gamification->ensureProfile((int) $user['id']);

        $this->view('profile/index', [
            'title' => 'Perfil',
            'user' => $this->users->findById((int) $user['id']),
            'settings' => current_settings(),
            'profile' => $this->gamification->profile((int) $user['id']),
            'badges' => $this->gamification->badgesForUser((int) $user['id'], 8),
            'stats' => $this->users->profileStats((int) $user['id']),
            'posts' => (new CommunityPost())->forUser((int) $user['id']),
            'activeSubscription' => (new Finance())->activeSubscription((int) $user['id']),
            'unreadNotifications' => (new NotificationService())->unreadCount((int) $user['id']),
        ]);
    }

    public function updateProfile(): void
    {
        $this->guardCsrf('/perfil');
        $bio = trim($_POST['bio_short'] ?? '');

        if (strlen($bio) > 280) {
            flash('error', 'A biografia curta deve ter no maximo 280 caracteres.');
            $this->redirect('/perfil#perfil');
        }

        $this->users->updateProfileInfo((int) current_user()['id'], $bio);
        $this->logs->record((int) current_user()['id'], 'profile.updated', ['field' => 'bio_short']);

        flash('success', 'Perfil atualizado.');
        $this->redirect('/perfil#perfil');
    }

    public function updateAppearance(): void
    {
        $this->guardCsrf('/perfil');

        $theme = $_POST['theme'] ?? config('app.default_theme', 'light');
        $primaryColor = $_POST['primary_color'] ?? config('app.default_primary_color', '#1f6feb');

        if (! in_array($theme, ['light', 'dark'], true)) {
            $theme = 'light';
        }

        if (! preg_match('/^#[0-9a-fA-F]{6}$/', $primaryColor)) {
            $primaryColor = config('app.default_primary_color', '#1f6feb');
        }

        $this->users->updateSettings((int) current_user()['id'], $theme, $primaryColor);
        $this->logs->record((int) current_user()['id'], 'profile.appearance_updated', [
            'theme' => $theme,
            'primary_color' => $primaryColor,
        ]);

        flash('success', 'Preferencias visuais atualizadas.');
        $this->redirect('/perfil#aparencia');
    }

    public function updatePassword(): void
    {
        $this->guardCsrf('/perfil');

        $user = $this->users->findById((int) current_user()['id']);
        $current = (string) ($_POST['current_password'] ?? '');
        $password = (string) ($_POST['password'] ?? '');
        $confirmation = (string) ($_POST['password_confirmation'] ?? '');

        if (! $user || ! password_verify($current, $user['password_hash'])) {
            flash('error', 'Senha atual incorreta.');
            $this->redirect('/perfil#seguranca');
        }

        if (strlen($password) < 8) {
            flash('error', 'A nova senha deve ter pelo menos 8 caracteres.');
            $this->redirect('/perfil#seguranca');
        }

        if ($password !== $confirmation) {
            flash('error', 'A confirmacao da senha nao confere.');
            $this->redirect('/perfil#seguranca');
        }

        $this->users->updatePassword((int) $user['id'], password_hash($password, PASSWORD_DEFAULT));
        $this->logs->record((int) $user['id'], 'profile.password_updated', [], 'security');

        flash('success', 'Senha atualizada.');
        $this->redirect('/perfil#seguranca');
    }

    private function guardCsrf(string $fallback): void
    {
        if (! verify_csrf_token($_POST['_csrf'] ?? null)) {
            flash('error', 'Sua sessao expirou. Tente novamente.');
            $this->redirect($fallback);
        }
    }
}
