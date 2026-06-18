<?php

defined('BASE_PATH') || exit('Acesso direto não permitido.');

class PortalController extends Controller
{
    public function index(): void
    {
        $user = current_user();
        $counts = (new User())->dashboardCounts();
        $publishedCourses = (new Course())->published();
        $gamification = new Gamification();
        $gamification->ensureProfile((int) $user['id']);
        $finance = new Finance();
        $enrollments = [];
        $learningStats = [
            'enrolled' => 0,
            'completed' => 0,
            'average_progress' => 0,
        ];

        if (in_array($user['role_slug'], ['aluno', 'professor'], true)) {
            $enrollments = (new Enrollment())->forStudent((int) $user['id']);
            $learningStats = $this->learningStats($enrollments);
        }

        $registeredEvents = in_array($user['role_slug'], ['aluno', 'professor'], true)
            ? (new Event())->registrationsForUser((int) $user['id'])
            : [];

        $linkedClasses = in_array($user['role_slug'], ['aluno', 'professor'], true)
            ? (new SchoolClass())->linkedForUser((int) $user['id'], $user['role_slug'])
            : [];

        $this->view('dashboard/portal', [
            'title' => 'Portal TME',
            'user' => $user,
            'counts' => $counts,
            'publishedCoursesCount' => count($publishedCourses),
            'enrollments' => $enrollments,
            'learningStats' => $learningStats,
            'gamificationProfile' => $gamification->profile((int) $user['id']),
            'badges' => $gamification->badgesForUser((int) $user['id'], 6),
            'registeredEvents' => $registeredEvents,
            'linkedClasses' => $linkedClasses,
            'activeSubscription' => $finance->activeSubscription((int) $user['id']),
            'financeSummary' => $finance->summaryForUser((int) $user['id']),
        ]);
    }

    public function settings(): void
    {
        $this->view('dashboard/settings', [
            'title' => 'Configurações',
            'user' => current_user(),
            'settings' => current_settings(),
        ]);
    }

    private function learningStats(array $enrollments): array
    {
        $total = count($enrollments);

        if ($total === 0) {
            return [
                'enrolled' => 0,
                'completed' => 0,
                'average_progress' => 0,
            ];
        }

        $completed = 0;
        $progressSum = 0.0;

        foreach ($enrollments as $enrollment) {
            $progress = (float) $enrollment['progress_percent'];
            $progressSum += $progress;

            if ($enrollment['status'] === 'concluida' || $progress >= 100) {
                $completed++;
            }
        }

        return [
            'enrolled' => $total,
            'completed' => $completed,
            'average_progress' => (int) round($progressSum / $total),
        ];
    }
}
