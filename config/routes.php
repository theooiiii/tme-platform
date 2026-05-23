<?php

defined('BASE_PATH') || exit('Acesso direto nao permitido.');

return [
    ['GET', '/', [PublicController::class, 'home']],
    ['GET', '/sobre', [PublicController::class, 'about']],
    ['GET', '/cursos', [PublicController::class, 'courses']],
    ['GET', '/eventos', [PublicController::class, 'events']],
    ['GET', '/biblioteca', [PublicController::class, 'library']],
    ['GET', '/comunidade', [PublicController::class, 'community']],

    ['GET', '/login', [AuthController::class, 'showLogin']],
    ['POST', '/login', [AuthController::class, 'login']],
    ['GET', '/cadastro', [AuthController::class, 'showRegister']],
    ['POST', '/cadastro', [AuthController::class, 'register']],
    ['POST', '/logout', [AuthController::class, 'logout'], ['auth']],

    ['GET', '/instituicoes/buscar', [InstitutionController::class, 'search']],

    ['GET', '/dashboard', [DashboardController::class, 'index'], ['auth']],
    ['POST', '/settings', [DashboardController::class, 'updateSettings'], ['auth']],

    ['GET', '/aluno/cursos', [CourseCatalogController::class, 'index'], ['auth', 'role:aluno']],
    ['GET', '/aluno/cursos/{id}', [CourseCatalogController::class, 'show'], ['auth', 'role:aluno']],
    ['POST', '/aluno/cursos/{id}/matricular', [CourseCatalogController::class, 'enroll'], ['auth', 'role:aluno']],
    ['GET', '/aluno/meus-cursos', [CourseCatalogController::class, 'myCourses'], ['auth', 'role:aluno']],
    ['GET', '/aluno/meus-cursos/{id}', [CourseCatalogController::class, 'enrollment'], ['auth', 'role:aluno']],
    ['POST', '/aluno/meus-cursos/{enrollmentId}/aulas/{lessonId}/concluir', [CourseCatalogController::class, 'completeLesson'], ['auth', 'role:aluno']],

    ['GET', '/admin/contas-pendentes', [AdminController::class, 'pendingAccounts'], ['auth', 'role:administrador,supervisor']],
    ['POST', '/admin/contas/{id}/aprovar', [AdminController::class, 'approve'], ['auth', 'role:administrador,supervisor']],
    ['POST', '/admin/contas/{id}/recusar', [AdminController::class, 'reject'], ['auth', 'role:administrador,supervisor']],
    ['GET', '/admin/matriculas', [AdminEnrollmentController::class, 'index'], ['auth', 'role:administrador,supervisor']],

    ['GET', '/admin/cursos', [AdminCourseController::class, 'index'], ['auth', 'role:administrador,supervisor']],
    ['GET', '/admin/cursos/novo', [AdminCourseController::class, 'create'], ['auth', 'role:administrador,supervisor']],
    ['POST', '/admin/cursos', [AdminCourseController::class, 'store'], ['auth', 'role:administrador,supervisor']],
    ['GET', '/admin/cursos/{id}', [AdminCourseController::class, 'show'], ['auth', 'role:administrador,supervisor']],
    ['GET', '/admin/cursos/{id}/editar', [AdminCourseController::class, 'edit'], ['auth', 'role:administrador,supervisor']],
    ['POST', '/admin/cursos/{id}/atualizar', [AdminCourseController::class, 'update'], ['auth', 'role:administrador,supervisor']],
    ['POST', '/admin/cursos/{id}/desativar', [AdminCourseController::class, 'deactivate'], ['auth', 'role:administrador,supervisor']],

    ['GET', '/admin/cursos/{courseId}/modulos/novo', [AdminCourseController::class, 'createModule'], ['auth', 'role:administrador,supervisor']],
    ['POST', '/admin/cursos/{courseId}/modulos', [AdminCourseController::class, 'storeModule'], ['auth', 'role:administrador,supervisor']],
    ['GET', '/admin/cursos/{courseId}/modulos/{moduleId}/editar', [AdminCourseController::class, 'editModule'], ['auth', 'role:administrador,supervisor']],
    ['POST', '/admin/cursos/{courseId}/modulos/{moduleId}/atualizar', [AdminCourseController::class, 'updateModule'], ['auth', 'role:administrador,supervisor']],
    ['POST', '/admin/cursos/{courseId}/modulos/{moduleId}/excluir', [AdminCourseController::class, 'deleteModule'], ['auth', 'role:administrador,supervisor']],

    ['GET', '/admin/cursos/{courseId}/aulas/novo', [AdminCourseController::class, 'createLesson'], ['auth', 'role:administrador,supervisor']],
    ['POST', '/admin/cursos/{courseId}/aulas', [AdminCourseController::class, 'storeLesson'], ['auth', 'role:administrador,supervisor']],
    ['GET', '/admin/cursos/{courseId}/aulas/{lessonId}/editar', [AdminCourseController::class, 'editLesson'], ['auth', 'role:administrador,supervisor']],
    ['POST', '/admin/cursos/{courseId}/aulas/{lessonId}/atualizar', [AdminCourseController::class, 'updateLesson'], ['auth', 'role:administrador,supervisor']],
    ['POST', '/admin/cursos/{courseId}/aulas/{lessonId}/excluir', [AdminCourseController::class, 'deleteLesson'], ['auth', 'role:administrador,supervisor']],

    ['GET', '/admin/cursos/{courseId}/aulas/{lessonId}/materiais/novo', [AdminCourseController::class, 'createMaterial'], ['auth', 'role:administrador,supervisor']],
    ['POST', '/admin/cursos/{courseId}/aulas/{lessonId}/materiais', [AdminCourseController::class, 'storeMaterial'], ['auth', 'role:administrador,supervisor']],
    ['GET', '/admin/cursos/{courseId}/aulas/{lessonId}/materiais/{materialId}/editar', [AdminCourseController::class, 'editMaterial'], ['auth', 'role:administrador,supervisor']],
    ['POST', '/admin/cursos/{courseId}/aulas/{lessonId}/materiais/{materialId}/atualizar', [AdminCourseController::class, 'updateMaterial'], ['auth', 'role:administrador,supervisor']],
    ['POST', '/admin/cursos/{courseId}/aulas/{lessonId}/materiais/{materialId}/excluir', [AdminCourseController::class, 'deleteMaterial'], ['auth', 'role:administrador,supervisor']],
];
