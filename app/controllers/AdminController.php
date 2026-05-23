<?php

defined('BASE_PATH') || exit('Acesso direto nao permitido.');

class AdminController extends Controller
{
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
