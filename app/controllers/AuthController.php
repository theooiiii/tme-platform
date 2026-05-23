<?php

defined('BASE_PATH') || exit('Acesso direto nao permitido.');

class AuthController extends Controller
{
    private User $users;
    private GamificationService $gamification;

    public function __construct()
    {
        $this->users = new User();
        $this->gamification = new GamificationService();
    }

    public function showLogin(): void
    {
        if (current_user()) {
            $this->redirect('/portal');
        }

        $this->view('auth/login', ['title' => 'Login']);
    }

    public function showRegister(): void
    {
        if (current_user()) {
            $this->redirect('/portal');
        }

        $this->view('auth/register', ['title' => 'Cadastro']);
    }

    public function register(): void
    {
        if (! verify_csrf_token($_POST['_csrf'] ?? null)) {
            flash('error', 'Sua sessão expirou. Tente novamente.');
            $this->redirect('/cadastro');
        }

        $data = $this->registrationPayload();
        $errors = $this->validateRegistration($data);

        if ($errors) {
            unset($data['password']);
            $_SESSION['_old'] = $data;
            flash('errors', $errors);
            $this->redirect('/cadastro');
        }

        try {
            $this->users->createPending($data);
            unset($_SESSION['_old']);
            flash('success', 'Cadastro enviado com sucesso. Aguarde a aprovação da equipe TME.');
            $this->redirect('/login');
        } catch (PDOException $exception) {
            $_SESSION['_old'] = $data;
            flash('error', 'Não foi possível concluir o cadastro. Verifique e-mail/CPF e tente novamente.');
            $this->redirect('/cadastro');
        }
    }

    public function login(): void
    {
        if (! verify_csrf_token($_POST['_csrf'] ?? null)) {
            flash('error', 'Sua sessão expirou. Tente novamente.');
            $this->redirect('/login');
        }

        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $user = $this->users->findByEmail($email);

        if (! $user || ! password_verify($password, $user['password_hash'])) {
            flash('error', 'E-mail ou senha inválidos.');
            $this->redirect('/login');
        }

        if ($user['status'] !== 'aprovado') {
            $messages = [
                'pendente' => 'Sua conta ainda está pendente de aprovação.',
                'recusado' => 'Sua conta foi recusada. Entre em contato com a TME para revisar o cadastro.',
                'inativo' => 'Sua conta está inativa.',
            ];

            flash('error', $messages[$user['status']] ?? 'Sua conta não está liberada para acesso.');
            $this->redirect('/login');
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['id'];
        $this->users->markLastLogin((int) $user['id']);
        $this->gamification->firstLogin((int) $user['id']);

        $this->redirect('/portal');
    }

    public function logout(): void
    {
        if (! verify_csrf_token($_POST['_csrf'] ?? null)) {
            flash('error', 'Não foi possível encerrar a sessão.');
            $this->redirect('/portal');
        }

        $_SESSION = [];
        session_destroy();
        session_start();
        flash('success', 'Você saiu da plataforma.');
        $this->redirect('/');
    }

    private function registrationPayload(): array
    {
        return [
            'full_name' => trim($_POST['full_name'] ?? ''),
            'email' => strtolower(trim($_POST['email'] ?? '')),
            'password' => (string) ($_POST['password'] ?? ''),
            'account_type' => trim($_POST['account_type'] ?? ''),
            'phone' => preg_replace('/\D+/', '', (string) ($_POST['phone'] ?? '')),
            'cpf' => preg_replace('/\D+/', '', (string) ($_POST['cpf'] ?? '')),
            'birth_date' => trim($_POST['birth_date'] ?? ''),
            'state' => strtoupper(trim($_POST['state'] ?? '')),
            'city' => trim($_POST['city'] ?? ''),
            'institution' => trim($_POST['institution'] ?? ''),
            'is_independent' => isset($_POST['is_independent']),
            'interest_area' => trim($_POST['interest_area'] ?? ''),
            'platform_goal' => trim($_POST['platform_goal'] ?? ''),
            'terms_accepted' => isset($_POST['terms_accepted']),
        ];
    }

    private function validateRegistration(array $data): array
    {
        $errors = [];

        if (strlen($data['full_name']) < 3) {
            $errors[] = 'Informe seu nome completo.';
        }

        if (! filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Informe um e-mail válido.';
        } elseif ($this->users->emailExists($data['email'])) {
            $errors[] = 'Este e-mail já está cadastrado.';
        }

        if (strlen($data['password']) < 8) {
            $errors[] = 'A senha deve ter pelo menos 8 caracteres.';
        }

        if (! in_array($data['account_type'], ['aluno', 'professor'], true)) {
            $errors[] = 'Escolha entre conta de aluno ou professor.';
        }

        if (strlen($data['phone']) < 10) {
            $errors[] = 'Informe um telefone válido com DDD.';
        }

        if (strlen($data['cpf']) !== 11) {
            $errors[] = 'Informe um CPF com 11 dígitos.';
        }

        if ($data['birth_date'] === '') {
            $errors[] = 'Informe sua data de nascimento.';
        }

        if (strlen($data['state']) !== 2) {
            $errors[] = 'Informe o estado com a sigla de 2 letras.';
        }

        if ($data['city'] === '') {
            $errors[] = 'Informe sua cidade.';
        }

        if (! $data['is_independent'] && $data['institution'] === '') {
            $errors[] = 'Informe a instituição ou marque a opção sou independente.';
        }

        if ($data['interest_area'] === '') {
            $errors[] = 'Informe sua área de interesse.';
        }

        if ($data['platform_goal'] === '') {
            $errors[] = 'Informe seu objetivo dentro da plataforma.';
        }

        if (! $data['terms_accepted']) {
            $errors[] = 'Você precisa aceitar os termos para continuar.';
        }

        return $errors;
    }
}
