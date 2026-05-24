<?php

defined('BASE_PATH') || exit('Acesso direto nao permitido.');

class Finance extends Model
{
    public function subscribe(int $userId, int $planId): array
    {
        $plan = (new Plan())->find($planId);

        if (! $plan || $plan['status'] !== 'ativo') {
            throw new RuntimeException('Plano indisponivel.');
        }

        $status = ((float) $plan['price'] <= 0.0) ? 'pago' : 'pendente';
        $subscriptionStatus = $status === 'pago' ? 'ativa' : 'pendente';
        $startsAt = $status === 'pago' ? date('Y-m-d H:i:s') : null;
        $endsAt = $status === 'pago' ? date('Y-m-d H:i:s', strtotime('+' . (int) $plan['duration_days'] . ' days')) : null;
        $reference = 'TME-' . strtoupper(bin2hex(random_bytes(5)));

        $this->db->beginTransaction();

        try {
            $transaction = $this->db->prepare(
                'INSERT INTO transactions (
                    user_id, plan_id, transaction_type, amount, platform_fee, creator_amount,
                    status, payment_method, gateway, reference, due_at, expires_at,
                    paid_at, created_at, updated_at
                 ) VALUES (
                    :user_id, :plan_id, "assinatura", :amount, :platform_fee, 0,
                    :status, :payment_method, :gateway, :reference, :due_at, :expires_at,
                    :paid_at, NOW(), NOW()
                 )'
            );
            $transaction->execute([
                'user_id' => $userId,
                'plan_id' => $planId,
                'amount' => (float) $plan['price'],
                'platform_fee' => (float) $plan['price'],
                'status' => $status,
                'payment_method' => ((float) $plan['price'] <= 0.0) ? 'interno' : 'pix',
                'gateway' => ((float) $plan['price'] <= 0.0) ? 'tme-interno' : 'pix-futuro',
                'reference' => $reference,
                'due_at' => date('Y-m-d H:i:s', strtotime('+2 days')),
                'expires_at' => date('Y-m-d H:i:s', strtotime('+2 days')),
                'paid_at' => $status === 'pago' ? date('Y-m-d H:i:s') : null,
            ]);
            $transactionId = (int) $this->db->lastInsertId();

            $subscription = $this->db->prepare(
                'INSERT INTO subscriptions (
                    user_id, plan_id, transaction_id, status, starts_at, ends_at,
                    auto_renew, created_at, updated_at
                 ) VALUES (
                    :user_id, :plan_id, :transaction_id, :status, :starts_at, :ends_at,
                    0, NOW(), NOW()
                 )'
            );
            $subscription->execute([
                'user_id' => $userId,
                'plan_id' => $planId,
                'transaction_id' => $transactionId,
                'status' => $subscriptionStatus,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
            ]);
            $subscriptionId = (int) $this->db->lastInsertId();

            $link = $this->db->prepare('UPDATE transactions SET subscription_id = :subscription_id WHERE id = :id');
            $link->execute(['subscription_id' => $subscriptionId, 'id' => $transactionId]);

            $this->db->commit();

            return [
                'subscription_id' => $subscriptionId,
                'transaction_id' => $transactionId,
                'status' => $status,
                'plan' => $plan,
            ];
        } catch (Throwable $exception) {
            $this->db->rollBack();
            throw $exception;
        }
    }

    public function activeSubscription(int $userId): ?array
    {
        $statement = $this->db->prepare(
            'SELECT subscriptions.*, plans.name AS plan_name, plans.price, plans.is_premium,
                    plans.duration_days, plans.benefits
             FROM subscriptions
             INNER JOIN plans ON plans.id = subscriptions.plan_id
             WHERE subscriptions.user_id = :user_id
               AND subscriptions.status = "ativa"
               AND (subscriptions.ends_at IS NULL OR subscriptions.ends_at >= NOW())
             ORDER BY plans.is_premium DESC, subscriptions.ends_at DESC
             LIMIT 1'
        );
        $statement->execute(['user_id' => $userId]);
        $subscription = $statement->fetch();

        return $subscription ?: null;
    }

    public function hasActivePremium(int $userId): bool
    {
        $subscription = $this->activeSubscription($userId);

        return $subscription && (int) $subscription['is_premium'] === 1;
    }

    public function transactionsForUser(int $userId): array
    {
        $statement = $this->db->prepare(
            'SELECT transactions.*, plans.name AS plan_name, plans.is_premium
             FROM transactions
             LEFT JOIN plans ON plans.id = transactions.plan_id
             WHERE transactions.user_id = :user_id
             ORDER BY transactions.created_at DESC'
        );
        $statement->execute(['user_id' => $userId]);

        return $statement->fetchAll();
    }

    public function subscriptionsForUser(int $userId): array
    {
        $statement = $this->db->prepare(
            'SELECT subscriptions.*, plans.name AS plan_name, plans.price, plans.is_premium
             FROM subscriptions
             INNER JOIN plans ON plans.id = subscriptions.plan_id
             WHERE subscriptions.user_id = :user_id
             ORDER BY subscriptions.created_at DESC'
        );
        $statement->execute(['user_id' => $userId]);

        return $statement->fetchAll();
    }

    public function creatorWallet(int $userId): array
    {
        $statement = $this->db->prepare(
            'INSERT IGNORE INTO creator_wallets (user_id, created_at, updated_at)
             VALUES (:user_id, NOW(), NOW())'
        );
        $statement->execute(['user_id' => $userId]);

        $wallet = $this->db->prepare('SELECT * FROM creator_wallets WHERE user_id = :user_id LIMIT 1');
        $wallet->execute(['user_id' => $userId]);

        return $wallet->fetch() ?: [
            'available_balance' => 0,
            'pending_balance' => 0,
            'lifetime_earnings' => 0,
            'platform_share_percent' => 20,
            'creator_share_percent' => 80,
        ];
    }

    public function summaryForUser(int $userId): array
    {
        $statement = $this->db->prepare(
            'SELECT
                COUNT(*) AS total_transactions,
                SUM(status = "pendente") AS pending_transactions,
                SUM(status = "pago") AS paid_transactions,
                COALESCE(SUM(CASE WHEN status = "pago" THEN amount ELSE 0 END), 0) AS paid_total
             FROM transactions
             WHERE user_id = :user_id'
        );
        $statement->execute(['user_id' => $userId]);

        return $statement->fetch() ?: [
            'total_transactions' => 0,
            'pending_transactions' => 0,
            'paid_transactions' => 0,
            'paid_total' => 0,
        ];
    }
}
