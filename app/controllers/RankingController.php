<?php

defined('BASE_PATH') || exit('Acesso direto não permitido.');

class RankingController extends Controller
{
    public function index(): void
    {
        $gamification = new Gamification();
        $courseId = (int) ($_GET['course_id'] ?? 0);

        $this->view('gamification/ranking', [
            'title' => 'Ranking',
            'ranking' => $gamification->ranking($courseId ?: null),
            'courses' => $gamification->coursesForRanking(),
            'selectedCourseId' => $courseId,
        ]);
    }
}
