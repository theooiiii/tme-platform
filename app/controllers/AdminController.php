<?php

defined('BASE_PATH') || exit('Acesso direto não permitido.');

class AdminController extends Controller
{
    private AdminRepository $admin;

    public function __construct()
    {
        $this->admin = new AdminRepository();
    }

    public function users(): void
    {
        $filters = [
            'q' => trim($_GET['q'] ?? ''),
            'status' => trim($_GET['status'] ?? ''),
            'role' => trim($_GET['role'] ?? ''),
        ];
        $page = max(1, (int) ($_GET['pagina'] ?? 1));

        $this->view('admin/users/index', [
            'title' => 'Usuários',
            'users' => $this->admin->users($filters, $page),
            'roles' => $this->admin->roles(),
            'filters' => $filters,
        ]);
    }

    public function updateUser(string $id): void
    {
        if (! verify_csrf_token($_POST['_csrf'] ?? null)) {
            flash('error', 'Sua sessão expirou. Tente novamente.');
            $this->redirect('/admin/usuarios');
        }

        $updated = $this->admin->updateUser(
            (int) $id,
            trim($_POST['role'] ?? ''),
            trim($_POST['status'] ?? '')
        );

        (new ActionLog())->record((int) current_user()['id'], 'admin.user.updated', [
            'target_user_id' => (int) $id,
            'role' => $_POST['role'] ?? null,
            'status' => $_POST['status'] ?? null,
        ]);

        flash($updated ? 'success' : 'error', $updated ? 'Usuário atualizado.' : 'Não foi possível atualizar o usuário.');
        $this->redirect('/admin/usuarios');
    }

    public function permissions(): void
    {
        $this->view('admin/permissions/index', [
            'title' => 'Permissões',
            'roles' => $this->admin->permissions(),
        ]);
    }

    public function categories(): void
    {
        $this->view('admin/categories/index', [
            'title' => 'Categorias de cursos',
            'categories' => $this->admin->courseCategories(),
        ]);
    }

    public function renameCategory(): void
    {
        if (! verify_csrf_token($_POST['_csrf'] ?? null)) {
            flash('error', 'Sua sessão expirou. Tente novamente.');
            $this->redirect('/admin/categorias');
        }

        $current = trim($_POST['current_category'] ?? '');
        $new = trim($_POST['new_category'] ?? '');

        if ($current === '' || mb_strlen($new) < 2) {
            flash('error', 'Informe a categoria atual e o novo nome.');
            $this->redirect('/admin/categorias');
        }

        $updated = $this->admin->renameCourseCategory($current, $new);
        (new ActionLog())->record((int) current_user()['id'], 'admin.course_category.renamed', [
            'from' => $current,
            'to' => $new,
            'updated' => $updated,
        ]);

        flash('success', $updated . ' curso(s) atualizados.');
        $this->redirect('/admin/categorias');
    }

    public function logs(): void
    {
        $filters = [
            'q' => trim($_GET['q'] ?? ''),
            'level' => trim($_GET['level'] ?? ''),
        ];
        $page = max(1, (int) ($_GET['pagina'] ?? 1));

        $this->view('admin/logs/index', [
            'title' => 'Logs administrativos',
            'logs' => $this->admin->logs($filters, $page),
            'filters' => $filters,
        ]);
    }

    public function search(): void
    {
        $term = trim($_GET['q'] ?? '');

        $this->view('admin/search/index', [
            'title' => 'Busca global',
            'term' => $term,
            'results' => $this->admin->globalSearch($term),
        ]);
    }

    public function pendingAccounts(): void
    {
        $this->view('admin/pending_accounts', [
            'title' => 'Contas pendentes',
            'accounts' => (new User())->pendingAccounts(),
        ]);
    }

    public function approve(string $id): void
    {
        if (! verify_csrf_token($_POST['_csrf'] ?? null)) {
            flash('error', 'Sua sessão expirou. Tente novamente.');
            $this->redirect('/admin/contas-pendentes');
        }

        $approved = (new User())->approve((int) $id, (int) current_user()['id']);
        flash($approved ? 'success' : 'info', $approved ? 'Conta aprovada com sucesso.' : 'A conta já foi analisada.');
        $this->redirect('/admin/contas-pendentes');
    }

    public function reject(string $id): void
    {
        if (! verify_csrf_token($_POST['_csrf'] ?? null)) {
            flash('error', 'Sua sessão expirou. Tente novamente.');
            $this->redirect('/admin/contas-pendentes');
        }

        $reason = trim($_POST['reason'] ?? '');
        $rejected = (new User())->reject((int) $id, (int) current_user()['id'], $reason);
        flash($rejected ? 'success' : 'info', $rejected ? 'Conta recusada.' : 'A conta já foi analisada.');
        $this->redirect('/admin/contas-pendentes');
    }
}
