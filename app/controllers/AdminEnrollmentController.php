<?php

defined('BASE_PATH') || exit('Acesso direto nao permitido.');

class AdminEnrollmentController extends Controller
{
    private Enrollment $enrollments;

    public function __construct()
    {
        $this->enrollments = new Enrollment();
    }

    public function index(): void
    {
        $filters = [
            'course_id' => trim($_GET['course_id'] ?? ''),
            'student_id' => trim($_GET['student_id'] ?? ''),
            'status' => trim($_GET['status'] ?? ''),
        ];

        $this->view('admin/enrollments/index', [
            'title' => 'Matrículas',
            'enrollments' => $this->enrollments->adminList($filters),
            'courses' => $this->enrollments->coursesWithEnrollments(),
            'students' => $this->enrollments->studentsWithEnrollments(),
            'filters' => $filters,
        ]);
    }
}
