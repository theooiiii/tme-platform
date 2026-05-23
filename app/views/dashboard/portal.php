<?php
defined('BASE_PATH') || exit('Acesso direto nao permitido.');

$role = $user['role_slug'];
$isLearner = in_array($role, ['aluno', 'professor'], true);
$isTeacher = $role === 'professor';
$isAdmin = in_array($role, ['administrador', 'supervisor'], true);

$metrics = [];
$cards = [];

if ($isLearner) {
    $metrics = [
        ['label' => 'Matriculas', 'value' => $learningStats['enrolled']],
        ['label' => 'Progresso medio', 'value' => $learningStats['average_progress'] . '%'],
        ['label' => 'Cursos concluidos', 'value' => $learningStats['completed']],
        ['label' => 'XP', 'value' => (int) ($gamificationProfile['xp_total'] ?? 0)],
        ['label' => 'Nivel', 'value' => (int) ($gamificationProfile['level'] ?? 1)],
        ['label' => 'Cursos publicados', 'value' => $publishedCoursesCount],
    ];

    $cards = array_merge($cards, [
        ['title' => 'Meus cursos', 'text' => 'Continue suas aulas, materiais e progresso.', 'href' => '/meus-cursos', 'action' => 'Abrir'],
        ['title' => 'Catalogo de cursos', 'text' => 'Explore cursos publicados e inicie novas trilhas.', 'href' => '/aluno/cursos', 'action' => 'Explorar'],
        ['title' => 'Progresso', 'text' => 'Veja matriculas ativas, conclusoes e ultima atividade.', 'href' => '/meus-cursos', 'action' => 'Acompanhar'],
        ['title' => 'Atividades', 'text' => 'Entregue tarefas dos cursos e acompanhe correcoes.', 'href' => '/atividades', 'action' => 'Ver tarefas'],
        ['title' => 'Boletim', 'text' => 'Veja notas simples por curso e entregas corrigidas.', 'href' => '/boletim', 'action' => 'Abrir'],
        ['title' => 'Biblioteca', 'text' => 'Acesse materiais, leituras e referencias da TME.', 'href' => '/biblioteca', 'action' => 'Abrir'],
        ['title' => 'Favoritos', 'text' => 'Retome materiais salvos na biblioteca digital.', 'href' => '/biblioteca/favoritos', 'action' => 'Ver favoritos'],
        ['title' => 'Eventos', 'text' => 'Acompanhe palestras, workshops e encontros academicos.', 'href' => '/eventos', 'action' => 'Ver agenda'],
        ['title' => 'Comunidade', 'text' => 'Participe da rede academica e acompanhe publicacoes.', 'href' => '/comunidade', 'action' => 'Entrar'],
        ['title' => 'Certificados', 'text' => 'Acesse certificados emitidos e valide codigos publicos.', 'href' => '/certificados', 'action' => 'Ver certificados'],
        ['title' => 'Ranking', 'text' => 'Compare XP, niveis, badges e conquistas da comunidade.', 'href' => '/ranking', 'action' => 'Ver ranking'],
    ]);
}

if ($isTeacher) {
    $cards = array_merge($cards, [
        ['title' => 'Minhas turmas', 'text' => 'Gestao de turmas e disciplinas do professor.', 'href' => '/dashboard', 'action' => 'Ver painel'],
        ['title' => 'Meus cursos publicados', 'text' => 'Area preparada para conteudos assinados pelo docente.', 'href' => '/dashboard', 'action' => 'Acompanhar'],
        ['title' => 'Criar atividade', 'text' => 'Publique tarefas, projetos e avaliacoes dos cursos.', 'href' => '/admin/atividades/nova', 'action' => 'Criar'],
        ['title' => 'Materiais', 'text' => 'Envie materiais para a biblioteca e acompanhe moderacao.', 'href' => '/admin/biblioteca', 'action' => 'Gerenciar'],
        ['title' => 'Correcoes futuras', 'text' => 'Espaco para atividades, feedbacks e avaliacoes.', 'href' => '', 'action' => 'Planejado'],
        ['title' => 'Analytics futuro', 'text' => 'Indicadores de engajamento e desempenho por turma.', 'href' => '', 'action' => 'Planejado'],
    ]);
}

if ($isAdmin) {
    $metrics = [
        ['label' => 'Contas pendentes', 'value' => $counts['pending_users']],
        ['label' => 'Usuarios aprovados', 'value' => $counts['approved_users']],
        ['label' => 'Cursos', 'value' => $counts['courses']],
        ['label' => 'Matriculas', 'value' => $counts['enrollments']],
    ];

    $cards = array_merge($cards, [
        ['title' => 'Dashboard administrativo', 'text' => 'Visao operacional por perfil e indicadores iniciais.', 'href' => '/dashboard', 'action' => 'Abrir'],
        ['title' => 'Aprovacoes', 'text' => 'Analise cadastros pendentes de alunos e professores.', 'href' => '/admin/contas-pendentes', 'action' => 'Revisar'],
        ['title' => 'Cursos admin', 'text' => 'Gerencie cursos, modulos, aulas e materiais.', 'href' => '/admin/cursos', 'action' => 'Gerenciar'],
        ['title' => 'Matriculas', 'text' => 'Visualize alunos por curso, status e progresso.', 'href' => '/admin/matriculas', 'action' => 'Ver lista'],
        ['title' => 'Atividades', 'text' => 'Crie tarefas e corrija entregas com nota e feedback.', 'href' => '/admin/atividades', 'action' => 'Gerenciar'],
        ['title' => 'Biblioteca admin', 'text' => 'Aprove, recuse e publique materiais educacionais.', 'href' => '/admin/biblioteca', 'action' => 'Moderar'],
        ['title' => 'Certificados', 'text' => 'Liste certificados emitidos e revogue registros invalidos.', 'href' => '/admin/certificados', 'action' => 'Gerenciar'],
        ['title' => 'Ranking', 'text' => 'Acompanhe XP, niveis e conquistas da comunidade.', 'href' => '/ranking', 'action' => 'Ver ranking'],
        ['title' => 'Usuarios', 'text' => 'Base preparada para gestao completa de usuarios.', 'href' => '/admin/contas-pendentes', 'action' => 'Acessar'],
        ['title' => 'Logs', 'text' => 'Acoes importantes ja sao registradas para auditoria.', 'href' => '', 'action' => 'Planejado'],
        ['title' => 'Relatorios futuros', 'text' => 'Indicadores academicos, financeiros e operacionais.', 'href' => '', 'action' => 'Planejado'],
    ]);
}

if (! $cards) {
    $metrics = [
        ['label' => 'Cursos', 'value' => $counts['courses']],
        ['label' => 'Eventos', 'value' => $counts['events']],
        ['label' => 'Matriculas', 'value' => $counts['enrollments']],
        ['label' => 'Perfil', 'value' => role_label($role)],
    ];

    $cards = [
        ['title' => 'Dashboard', 'text' => 'Acesse o painel principal do seu perfil.', 'href' => '/dashboard', 'action' => 'Abrir'],
        ['title' => 'Biblioteca', 'text' => 'Consulte materiais e referencias academicas.', 'href' => '/biblioteca', 'action' => 'Abrir'],
        ['title' => 'Eventos', 'text' => 'Veja a programacao academica da TME.', 'href' => '/eventos', 'action' => 'Ver'],
        ['title' => 'Comunidade', 'text' => 'Acompanhe publicacoes e projetos academicos.', 'href' => '/comunidade', 'action' => 'Entrar'],
    ];
}
?>

<section class="dashboard-shell portal-shell">
    <div class="portal-hero">
        <div>
            <span class="eyebrow">Portal TME</span>
            <h1>Ola, <?= e(explode(' ', trim($user['full_name']))[0] ?: $user['full_name']) ?>.</h1>
            <p>Esta e sua central inicial autenticada para acessar aprendizagem, gestao, comunidade e atalhos do seu perfil.</p>
        </div>
        <div class="portal-actions">
            <a class="button large" href="<?= e(url('/dashboard')) ?>">Dashboard</a>
            <?php if ($isLearner): ?>
                <a class="button ghost large" href="<?= e(url('/meus-cursos')) ?>">Meus cursos</a>
            <?php elseif ($isAdmin): ?>
                <a class="button ghost large" href="<?= e(url('/admin/contas-pendentes')) ?>">Administracao</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="metric-grid portal-metrics">
        <?php foreach ($metrics as $metric): ?>
            <article class="metric">
                <span><?= e($metric['label']) ?></span>
                <strong><?= e($metric['value']) ?></strong>
            </article>
        <?php endforeach; ?>
    </div>

    <div class="portal-gamification-panel">
        <div>
            <span class="eyebrow">Conquistas</span>
            <h2>Nivel <?= e((int) ($gamificationProfile['level'] ?? 1)) ?> | <?= e((int) ($gamificationProfile['xp_total'] ?? 0)) ?> XP</h2>
            <p><?= e((int) ($gamificationProfile['internal_coins'] ?? 0)) ?> moedas internas e streak de <?= e((int) ($gamificationProfile['streak_days'] ?? 0)) ?> dia(s).</p>
        </div>
        <div class="badge-strip">
            <?php if (empty($badges)): ?>
                <span class="muted">Badges serao liberados conforme suas primeiras acoes.</span>
            <?php else: ?>
                <?php foreach ($badges as $badge): ?>
                    <span class="badge-pill"><?= e($badge['name']) ?></span>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="portal-section-title">
        <span class="eyebrow">Atalhos</span>
        <h2>Areas principais</h2>
    </div>

    <div class="module-grid portal-grid">
        <?php foreach ($cards as $card): ?>
            <article class="module-card <?= empty($card['href']) ? 'muted-card' : '' ?>">
                <h2><?= e($card['title']) ?></h2>
                <p><?= e($card['text']) ?></p>
                <?php if (! empty($card['href'])): ?>
                    <a href="<?= e(url($card['href'])) ?>"><?= e($card['action']) ?></a>
                <?php else: ?>
                    <span class="module-card-note"><?= e($card['action']) ?></span>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>
</section>
