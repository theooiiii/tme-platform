<?php

defined('BASE_PATH') || exit('Acesso direto nao permitido.');

class PlanController extends Controller
{
    private Plan $plans;
    private Finance $finance;
    private ActionLog $logs;
    private NotificationService $notifications;

    public function __construct()
    {
        $this->plans = new Plan();
        $this->finance = new Finance();
        $this->logs = new ActionLog();
        $this->notifications = new NotificationService();
    }

    public function index(): void
    {
        $user = current_user();

        $this->view('plans/index', [
            'title' => 'Planos',
            'plans' => $this->plans->active(),
            'currentSubscription' => $user ? $this->finance->activeSubscription((int) $user['id']) : null,
            'user' => $user,
        ]);
    }

    public function subscribe(string $id): void
    {
        $this->guardCsrf('/planos');
        $user = current_user();
        $plan = $this->plans->find((int) $id);

        if (! $plan || $plan['status'] !== 'ativo') {
            flash('error', 'Plano indisponivel.');
            $this->redirect('/planos');
        }

        $active = $this->finance->activeSubscription((int) $user['id']);

        if ($active && (int) $active['plan_id'] === (int) $plan['id']) {
            flash('info', 'Voce ja possui este plano ativo.');
            $this->redirect('/financeiro');
        }

        try {
            $result = $this->finance->subscribe((int) $user['id'], (int) $plan['id']);
            $this->logs->record((int) $user['id'], 'finance.subscription_requested', [
                'plan_id' => (int) $plan['id'],
                'subscription_id' => $result['subscription_id'],
                'transaction_id' => $result['transaction_id'],
                'status' => $result['status'],
            ]);

            $this->notifications->send(
                (int) $user['id'],
                'financeiro',
                'Assinatura registrada',
                'Seu pedido de assinatura do plano ' . $plan['name'] . ' foi registrado.',
                '/financeiro',
                ['priority' => $result['status'] === 'pago' ? 'normal' : 'alta']
            );

            flash(
                $result['status'] === 'pago' ? 'success' : 'info',
                $result['status'] === 'pago'
                    ? 'Plano ativado com sucesso.'
                    : 'Assinatura criada como pendente. A integracao PIX/cartao esta preparada para uma etapa futura.'
            );
        } catch (Throwable $exception) {
            $this->logs->record((int) $user['id'], 'finance.subscription_error', [
                'plan_id' => (int) $plan['id'],
                'message' => $exception->getMessage(),
            ], 'warning');
            flash('error', 'Nao foi possivel assinar este plano agora.');
        }

        $this->redirect('/financeiro');
    }

    public function adminIndex(): void
    {
        $this->view('admin/plans/index', [
            'title' => 'Planos',
            'plans' => $this->plans->all(),
        ]);
    }

    public function create(): void
    {
        $this->view('admin/plans/form', [
            'title' => 'Novo plano',
            'plan' => null,
            'action' => url('/admin/planos'),
        ]);
    }

    public function store(): void
    {
        $this->guardCsrf('/admin/planos/novo');
        $data = $this->payload();
        $errors = $this->validatePlan($data);

        if ($errors) {
            $_SESSION['_old'] = $data;
            flash('errors', $errors);
            $this->redirect('/admin/planos/novo');
        }

        $planId = $this->plans->create($data);
        unset($_SESSION['_old']);
        $this->logs->record((int) current_user()['id'], 'finance.plan_created', ['plan_id' => $planId]);

        flash('success', 'Plano criado.');
        $this->redirect('/admin/planos');
    }

    public function edit(string $id): void
    {
        $plan = $this->findPlanOrRedirect((int) $id);

        $this->view('admin/plans/form', [
            'title' => 'Editar plano',
            'plan' => $plan,
            'action' => url('/admin/planos/' . $plan['id'] . '/atualizar'),
        ]);
    }

    public function update(string $id): void
    {
        $plan = $this->findPlanOrRedirect((int) $id);
        $this->guardCsrf('/admin/planos/' . $plan['id'] . '/editar');
        $data = $this->payload();
        $errors = $this->validatePlan($data);

        if ($errors) {
            $_SESSION['_old'] = $data;
            flash('errors', $errors);
            $this->redirect('/admin/planos/' . $plan['id'] . '/editar');
        }

        $this->plans->update((int) $plan['id'], $data);
        unset($_SESSION['_old']);
        $this->logs->record((int) current_user()['id'], 'finance.plan_updated', ['plan_id' => (int) $plan['id']]);

        flash('success', 'Plano atualizado.');
        $this->redirect('/admin/planos');
    }

    public function archive(string $id): void
    {
        $plan = $this->findPlanOrRedirect((int) $id);
        $this->guardCsrf('/admin/planos');
        $this->plans->archive((int) $plan['id']);
        $this->logs->record((int) current_user()['id'], 'finance.plan_archived', ['plan_id' => (int) $plan['id']], 'warning');

        flash('success', 'Plano desativado.');
        $this->redirect('/admin/planos');
    }

    private function payload(): array
    {
        return [
            'name' => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'price' => max(0, (float) str_replace(',', '.', (string) ($_POST['price'] ?? 0))),
            'billing_cycle' => trim($_POST['billing_cycle'] ?? 'mensal'),
            'duration_days' => max(1, (int) ($_POST['duration_days'] ?? 30)),
            'benefits_text' => trim($_POST['benefits_text'] ?? ''),
            'is_premium' => isset($_POST['is_premium']),
            'sort_order' => max(1, (int) ($_POST['sort_order'] ?? 1)),
            'status' => trim($_POST['status'] ?? 'ativo'),
        ];
    }

    private function validatePlan(array $data): array
    {
        $errors = [];

        if (strlen($data['name']) < 3) {
            $errors[] = 'Informe um nome de plano com pelo menos 3 caracteres.';
        }

        if (! in_array($data['billing_cycle'], ['mensal', 'anual', 'unico'], true)) {
            $errors[] = 'Selecione uma duracao de cobranca valida.';
        }

        if (! in_array($data['status'], ['ativo', 'inativo'], true)) {
            $errors[] = 'Selecione um status valido.';
        }

        if ($data['benefits_text'] === '') {
            $errors[] = 'Informe pelo menos um beneficio.';
        }

        return $errors;
    }

    private function findPlanOrRedirect(int $id): array
    {
        $plan = $this->plans->find($id);

        if (! $plan) {
            flash('error', 'Plano nao encontrado.');
            $this->redirect('/admin/planos');
        }

        return $plan;
    }

    private function guardCsrf(string $fallback): void
    {
        if (! verify_csrf_token($_POST['_csrf'] ?? null)) {
            flash('error', 'Sua sessao expirou. Tente novamente.');
            $this->redirect($fallback);
        }
    }
}
