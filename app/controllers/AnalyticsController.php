<?php

defined('BASE_PATH') || exit('Acesso direto nao permitido.');

class AnalyticsController extends Controller
{
    public function index(): void
    {
        $analytics = new Analytics();
        $period = $analytics->periodFromRequest($_GET);

        $this->view('analytics/index', [
            'title' => 'Analytics',
            'period' => $period,
            'analytics' => $analytics->admin($period),
            'usesCharts' => true,
        ]);
    }
}
