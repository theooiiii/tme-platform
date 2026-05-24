<?php

defined('BASE_PATH') || exit('Acesso direto nao permitido.');

class CommunityController extends Controller
{
    private CommunityPost $posts;
    private ActionLog $logs;
    private NotificationService $notifications;

    public function __construct()
    {
        $this->posts = new CommunityPost();
        $this->logs = new ActionLog();
        $this->notifications = new NotificationService();
    }

    public function index(): void
    {
        $user = current_user();

        if (! $user) {
            $this->view('public/community', ['title' => 'Comunidade']);
            return;
        }

        $this->view('community/index', [
            'title' => 'Comunidade',
            'posts' => $this->posts->feed((int) $user['id']),
            'user' => $user,
        ]);
    }

    public function store(): void
    {
        $this->guardCsrf('/comunidade');
        $user = current_user();
        $data = $this->payload();
        $errors = $this->validatePost($data);

        if ($errors) {
            $_SESSION['_old'] = $data;
            flash('errors', $errors);
            $this->redirect('/comunidade');
        }

        $data['user_id'] = (int) $user['id'];
        $data['status'] = in_array($user['role_slug'], ['administrador', 'supervisor'], true) ? 'aprovado' : 'pendente';

        $postId = $this->posts->create($data);
        unset($_SESSION['_old']);
        $this->logs->record((int) $user['id'], 'community.post_created', ['post_id' => $postId, 'status' => $data['status']]);

        flash('success', $data['status'] === 'aprovado' ? 'Post publicado.' : 'Post enviado para moderacao.');
        $this->redirect('/comunidade');
    }

    public function show(string $id): void
    {
        $user = current_user();
        $post = $this->posts->find((int) $id, $user ? (int) $user['id'] : null, true);

        if (! $post) {
            flash('error', 'Post nao encontrado ou indisponivel.');
            $this->redirect('/comunidade');
        }

        $this->view('community/show', [
            'title' => $post['title'],
            'post' => $post,
            'comments' => $this->posts->comments((int) $post['id']),
            'user' => $user,
        ]);
    }

    public function comment(string $id): void
    {
        $this->guardCsrf('/comunidade/' . $id);
        $user = current_user();
        $post = $this->posts->find((int) $id, (int) $user['id'], true);

        if (! $post) {
            flash('error', 'Post nao encontrado.');
            $this->redirect('/comunidade');
        }

        $content = trim($_POST['content'] ?? '');

        if (strlen($content) < 2) {
            flash('error', 'Escreva um comentario com pelo menos 2 caracteres.');
            $this->redirect('/comunidade/' . $id);
        }

        $commentId = $this->posts->addComment((int) $post['id'], (int) $user['id'], $content);
        $this->logs->record((int) $user['id'], 'community.comment_created', ['post_id' => (int) $post['id'], 'comment_id' => $commentId]);
        if ((int) $post['user_id'] !== (int) $user['id']) {
            $this->notifications->commentCreated((int) $post['user_id'], (int) $post['id'], (string) $post['title']);
        }

        flash('success', 'Comentario publicado.');
        $this->redirect('/comunidade/' . $id);
    }

    public function like(string $id): void
    {
        $this->toggle($id, 'like');
    }

    public function save(string $id): void
    {
        $this->toggle($id, 'save');
    }

    public function adminIndex(): void
    {
        $filters = [
            'status' => trim($_GET['status'] ?? ''),
            'type' => trim($_GET['type'] ?? ''),
        ];

        $this->view('admin/community/index', [
            'title' => 'Comunidade admin',
            'posts' => $this->posts->adminList($filters),
            'filters' => $filters,
        ]);
    }

    public function approve(string $id): void
    {
        $this->moderate((int) $id, 'aprovado', 'community.post_approved', 'Post aprovado.');
    }

    public function reject(string $id): void
    {
        $this->moderate((int) $id, 'recusado', 'community.post_rejected', 'Post recusado.');
    }

    public function archive(string $id): void
    {
        $this->moderate((int) $id, 'arquivado', 'community.post_archived', 'Post arquivado.');
    }

    public function feature(string $id): void
    {
        $this->guardCsrf('/admin/comunidade');
        $this->posts->toggleFeatured((int) $id);
        $this->logs->record((int) current_user()['id'], 'community.post_featured_toggled', ['post_id' => (int) $id]);

        flash('success', 'Destaque atualizado.');
        $this->redirect('/admin/comunidade');
    }

    private function toggle(string $id, string $type): void
    {
        $this->guardCsrf('/comunidade');
        $user = current_user();
        $post = $this->posts->find((int) $id, (int) $user['id'], true);

        if (! $post) {
            flash('error', 'Post nao encontrado.');
            $this->redirect('/comunidade');
        }

        $active = $type === 'like'
            ? $this->posts->toggleLike((int) $post['id'], (int) $user['id'])
            : $this->posts->toggleSave((int) $post['id'], (int) $user['id']);

        $this->logs->record((int) $user['id'], $type === 'like' ? 'community.post_liked' : 'community.post_saved', [
            'post_id' => (int) $post['id'],
            'active' => $active,
        ]);

        flash('success', $type === 'like' ? ($active ? 'Post curtido.' : 'Curtida removida.') : ($active ? 'Post salvo.' : 'Post removido dos salvos.'));
        $this->redirect('/comunidade/' . $post['id']);
    }

    private function moderate(int $id, string $status, string $action, string $message): void
    {
        $this->guardCsrf('/admin/comunidade');
        $reason = trim($_POST['reason'] ?? '');
        $this->posts->moderate($id, (int) current_user()['id'], $status, $reason);
        $this->logs->record((int) current_user()['id'], $action, ['post_id' => $id, 'reason' => $reason], $status === 'recusado' ? 'warning' : 'info');

        flash('success', $message);
        $this->redirect('/admin/comunidade');
    }

    private function payload(): array
    {
        return [
            'post_type' => trim($_POST['post_type'] ?? 'duvida'),
            'title' => trim($_POST['title'] ?? ''),
            'content' => trim($_POST['content'] ?? ''),
        ];
    }

    private function validatePost(array $data): array
    {
        $errors = [];

        if (! in_array($data['post_type'], ['duvida', 'artigo', 'projeto', 'material', 'conquista', 'aviso'], true)) {
            $errors[] = 'Selecione um tipo valido.';
        }

        if (strlen($data['title']) < 4) {
            $errors[] = 'Informe um titulo com pelo menos 4 caracteres.';
        }

        if (strlen($data['content']) < 10) {
            $errors[] = 'Escreva um conteudo com pelo menos 10 caracteres.';
        }

        return $errors;
    }

    private function guardCsrf(string $fallback): void
    {
        if (! verify_csrf_token($_POST['_csrf'] ?? null)) {
            flash('error', 'Sua sessao expirou. Tente novamente.');
            $this->redirect($fallback);
        }
    }
}
