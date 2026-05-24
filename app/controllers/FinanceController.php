<?php

defined('BASE_PATH') || exit('Acesso direto nao permitido.');

class FinanceController extends Controller
{
    public function index(): void
    {
        $user = current_user();
        $finance = new Finance();
        $gamification = new Gamification();
        $gamification->ensureProfile((int) $user['id']);

        $this->view('finance/index', [
            'title' => 'Financeiro',
            'user' => $user,
            'activeSubscription' => $finance->activeSubscription((int) $user['id']),
            'subscriptions' => $finance->subscriptionsForUser((int) $user['id']),
            'transactions' => $finance->transactionsForUser((int) $user['id']),
            'summary' => $finance->summaryForUser((int) $user['id']),
            'wallet' => $finance->creatorWallet((int) $user['id']),
            'gamificationProfile' => $gamification->profile((int) $user['id']),
        ]);
    }
}
