<?php
defined('BASE_PATH') || exit('Acesso direto não permitido.');

$role = $user['role_slug'];
$isLearner = in_array($role, ['aluno', 'professor'], true);
$isTeacher = $role === 'professor';
$isAdmin = in_array($role, ['administrador', 'supervisor'], true);

$metrics = [];
$cards = [];

if ($isLearner) {
    $metrics = [
        ['label' => 'Matrículas', 'value' => $learningStats['enrolled']],
        ['label' => 'Progresso médio', 'value' => $learningStats['average_progress'] . '%'],
        ['label' => 'Cursos concluídos', 'value' => $learningStats['completed']],
        ['label' => 'XP', 'value' => (int) ($gamificationProfile['xp_total'] ?? 0)],
        ['label' => 'Nivel', 'value' => (int) ($gamificationProfile['level'] ?? 1)],
        ['label' => 'Eventos inscritos', 'value' => count($registeredEvents ?? [])],
        ['label' => 'Turmas', 'value' => count($linkedClasses ?? [])],
        ['label' => 'Plano', 'value' => $activeSubscription['plan_name'] ?? 'Sem plano'],
        ['label' => 'Cursos publicados', 'value' => $publishedCoursesCount],
    ];

    $cards = array_merge($cards, [
        ['title' => 'Meus cursos', 'text' => 'Continue suas aulas, materiais e progresso.', 'href' => '/meus-cursos', 'action' => 'Abrir'],
        ['title' => 'Catálogo de cursos', 'text' => 'Explore cursos publicados e inicie novas trilhas.', 'href' => '/aluno/cursos', 'action' => 'Explorar'],
        ['title' => 'Progresso', 'text' => 'Veja matrículas ativas, conclusões e última atividade.', 'href' => '/meus-cursos', 'action' => 'Acompanhar'],
        ['title' => 'Atividades', 'text' => 'Entregue tarefas dos cursos e acompanhe correcoes.', 'href' => '/atividades', 'action' => 'Ver tarefas'],
        ['title' => 'Boletim', 'text' => 'Veja notas simples por curso e entregas corrigidas.', 'href' => '/boletim', 'action' => 'Abrir'],
        ['title' => 'Provas', 'text' => 'Acesse simulados, tentativas e resultados por disciplina.', 'href' => '/provas', 'action' => 'Ver provas'],
        ['title' => 'Minha frequência', 'text' => 'Consulte chamadas, presenças, faltas e justificativas.', 'href' => '/minha-frequencia', 'action' => 'Acompanhar'],
        ['title' => 'Biblioteca', 'text' => 'Acesse materiais, leituras e referencias da TME.', 'href' => '/biblioteca', 'action' => 'Abrir'],
        ['title' => 'Favoritos', 'text' => 'Retome materiais salvos na biblioteca digital.', 'href' => '/biblioteca/favoritos', 'action' => 'Ver favoritos'],
        ['title' => 'Eventos', 'text' => 'Acompanhe palestras, workshops e encontros acadêmicos.', 'href' => '/eventos', 'action' => 'Ver agenda'],
        ['title' => 'Minhas turmas', 'text' => 'Veja turmas, disciplinas, professores e materiais futuros.', 'href' => '/turmas', 'action' => 'Abrir'],
        ['title' => 'Comunidade', 'text' => 'Participe da rede acadêmica e acompanhe publicações.', 'href' => '/comunidade', 'action' => 'Entrar'],
        ['title' => 'Chat', 'text' => 'Converse com colegas, professores e grupos de turma.', 'href' => '/chat', 'action' => 'Abrir'],
        ['title' => 'Certificados', 'text' => 'Acesse certificados emitidos e valide códigos públicos.', 'href' => '/certificados', 'action' => 'Ver certificados'],
        ['title' => 'Ranking', 'text' => 'Compare XP, níveis, badges e conquistas da comunidade.', 'href' => '/ranking', 'action' => 'Ver ranking'],
        ['title' => 'Planos', 'text' => 'Escolha acesso gratuito ou premium e acompanhe sua assinatura.', 'href' => '/planos', 'action' => 'Ver planos'],
        ['title' => 'Financeiro', 'text' => 'Veja histórico financeiro, moedas internas e carteira futura.', 'href' => '/financeiro', 'action' => 'Abrir'],
        ['title' => 'Notificações', 'text' => 'Acompanhe alertas de cursos, chat, provas e conquistas.', 'href' => '/notificacoes', 'action' => 'Ver central'],
    ]);
}

if ($isTeacher) {
    $cards = array_merge($cards, [
        ['title' => 'Minhas turmas', 'text' => 'Gestão de turmas e disciplinas do professor.', 'href' => '/dashboard', 'action' => 'Ver painel'],
        ['title' => 'Meus cursos publicados', 'text' => 'Área preparada para conteúdos assinados pelo docente.', 'href' => '/dashboard', 'action' => 'Acompanhar'],
        ['title' => 'Chamada', 'text' => 'Registre frequência por turma, disciplina e data.', 'href' => '/frequencia', 'action' => 'Registrar'],
        ['title' => 'Provas e simulados', 'text' => 'Crie avaliações com objetivas, discursivas e tentativas.', 'href' => '/admin/provas', 'action' => 'Gerenciar'],
        ['title' => 'Criar atividade', 'text' => 'Publique tarefas, projetos e avaliações dos cursos.', 'href' => '/admin/atividades/nova', 'action' => 'Criar'],
        ['title' => 'Materiais', 'text' => 'Envie materiais para a biblioteca e acompanhe moderação.', 'href' => '/admin/biblioteca', 'action' => 'Gerenciar'],
        ['title' => 'Correções futuras', 'text' => 'Espaço para atividades, feedbacks e avaliações.', 'href' => '', 'action' => 'Planejado'],
        ['title' => 'Analytics futuro', 'text' => 'Indicadores de engajamento e desempenho por turma.', 'href' => '', 'action' => 'Planejado'],
        ['title' => 'Monetização creator', 'text' => 'Carteira preparada para repasse 80/20 de conteúdos futuros.', 'href' => '/financeiro', 'action' => 'Abrir'],
    ]);
}

if ($isAdmin) {
    $metrics = [
        ['label' => 'Contas pendentes', 'value' => $counts['pending_users']],
        ['label' => 'Usuários aprovados', 'value' => $counts['approved_users']],
        ['label' => 'Cursos', 'value' => $counts['courses']],
        ['label' => 'Matrículas', 'value' => $counts['enrollments']],
        ['label' => 'Receita paga', 'value' => 'R$ ' . number_format((float) ($financeSummary['paid_total'] ?? 0), 2, ',', '.')],
    ];

    $cards = array_merge($cards, [
        ['title' => 'Dashboard administrativo', 'text' => 'Visao operacional por perfil e indicadores iniciais.', 'href' => '/dashboard', 'action' => 'Abrir'],
        ['title' => 'Aprovações', 'text' => 'Analise cadastros pendentes de alunos e professores.', 'href' => '/admin/contas-pendentes', 'action' => 'Revisar'],
        ['title' => 'Cursos admin', 'text' => 'Gerencie cursos, módulos, aulas e materiais.', 'href' => '/admin/cursos', 'action' => 'Gerenciar'],
        ['title' => 'Matrículas', 'text' => 'Visualize alunos por curso, status e progresso.', 'href' => '/admin/matriculas', 'action' => 'Ver lista'],
        ['title' => 'Atividades', 'text' => 'Crie tarefas e corrija entregas com nota e feedback.', 'href' => '/admin/atividades', 'action' => 'Gerenciar'],
        ['title' => 'Frequência', 'text' => 'Registre chamadas e consulte relatórios por turma.', 'href' => '/frequencia', 'action' => 'Abrir'],
        ['title' => 'Provas admin', 'text' => 'Crie provas, acompanhe tentativas e corrija discursivas.', 'href' => '/admin/provas', 'action' => 'Gerenciar'],
        ['title' => 'Biblioteca admin', 'text' => 'Aprove, recuse e publique materiais educacionais.', 'href' => '/admin/biblioteca', 'action' => 'Moderar'],
        ['title' => 'Certificados', 'text' => 'Liste certificados emitidos e revogue registros inválidos.', 'href' => '/admin/certificados', 'action' => 'Gerenciar'],
        ['title' => 'Comunidade admin', 'text' => 'Modere posts acadêmicos, destaques e recusas.', 'href' => '/admin/comunidade', 'action' => 'Moderar'],
        ['title' => 'Eventos admin', 'text' => 'Crie eventos, confirme presença e emita certificados.', 'href' => '/admin/eventos', 'action' => 'Gerenciar'],
        ['title' => 'Turmas admin', 'text' => 'Gerencie turmas, disciplinas e vínculos acadêmicos.', 'href' => '/admin/turmas', 'action' => 'Gerenciar'],
        ['title' => 'Chat auditoria', 'text' => 'Audite conversas apenas para segurança e moderação.', 'href' => '/admin/chat', 'action' => 'Auditar'],
        ['title' => 'Planos admin', 'text' => 'Gerencie planos gratuitos, premium e benefícios.', 'href' => '/admin/planos', 'action' => 'Gerenciar'],
        ['title' => 'Analytics', 'text' => 'Acompanhe crescimento, atividade e cursos populares.', 'href' => '/analytics', 'action' => 'Abrir'],
        ['title' => 'Financeiro', 'text' => 'Veja histórico financeiro e estrutura de pagamentos.', 'href' => '/financeiro', 'action' => 'Abrir'],
        ['title' => 'Notificações', 'text' => 'Central de avisos persistentes do sistema.', 'href' => '/notificacoes', 'action' => 'Abrir'],
        ['title' => 'Ranking', 'text' => 'Acompanhe XP, níveis e conquistas da comunidade.', 'href' => '/ranking', 'action' => 'Ver ranking'],
        ['title' => 'Usuários', 'text' => 'Base preparada para gestão completa de usuários.', 'href' => '/admin/contas-pendentes', 'action' => 'Acessar'],
        ['title' => 'Logs', 'text' => 'Ações importantes já são registradas para auditoria.', 'href' => '', 'action' => 'Planejado'],
        ['title' => 'Relatórios futuros', 'text' => 'Indicadores acadêmicos, financeiros e operacionais.', 'href' => '', 'action' => 'Planejado'],
    ]);
}

if (! $cards) {
    $metrics = [
        ['label' => 'Cursos', 'value' => $counts['courses']],
        ['label' => 'Eventos', 'value' => $counts['events']],
        ['label' => 'Matrículas', 'value' => $counts['enrollments']],
        ['label' => 'Perfil', 'value' => role_label($role)],
    ];

    $cards = [
        ['title' => 'Dashboard', 'text' => 'Acesse o painel principal do seu perfil.', 'href' => '/dashboard', 'action' => 'Abrir'],
        ['title' => 'Biblioteca', 'text' => 'Consulte materiais e referencias academicas.', 'href' => '/biblioteca', 'action' => 'Abrir'],
        ['title' => 'Eventos', 'text' => 'Veja a programação acadêmica da TME.', 'href' => '/eventos', 'action' => 'Ver'],
        ['title' => 'Comunidade', 'text' => 'Acompanhe publicações e projetos acadêmicos.', 'href' => '/comunidade', 'action' => 'Entrar'],
    ];
}
?>

<section class="dashboard-shell portal-shell">
    <div class="portal-hero">
        <div>
            <span class="eyebrow">Portal TME</span>
            <h1>Olá, <?= e(explode(' ', trim($user['full_name']))[0] ?: $user['full_name']) ?>.</h1>
            <p>Esta é sua central inicial autenticada para acessar aprendizagem, gestão, comunidade e atalhos do seu perfil.</p>
        </div>
        <div class="portal-actions">
            <a class="button large" href="<?= e(url('/dashboard')) ?>">Dashboard</a>
            <?php if ($isLearner): ?>
                <a class="button ghost large" href="<?= e(url('/meus-cursos')) ?>">Meus cursos</a>
            <?php elseif ($isAdmin): ?>
                <a class="button ghost large" href="<?= e(url('/admin/contas-pendentes')) ?>">Administração</a>
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
                <span class="muted">Badges serão liberados conforme suas primeiras ações.</span>
            <?php else: ?>
                <?php foreach ($badges as $badge): ?>
                    <span class="badge-pill"><?= e($badge['name']) ?></span>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($isLearner): ?>
        <div class="portal-linked-panel">
            <section>
                <span class="eyebrow">Eventos inscritos</span>
                <?php if (empty($registeredEvents)): ?>
                    <p class="muted">Nenhuma inscrição em eventos ainda.</p>
                <?php else: ?>
                    <?php foreach (array_slice($registeredEvents, 0, 3) as $event): ?>
                        <p><strong><?= e($event['title']) ?></strong><br><span class="muted"><?= e($event['starts_at'] ? date('d/m/Y H:i', strtotime($event['starts_at'])) : 'data a definir') ?></span></p>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>
            <section>
                <span class="eyebrow">Turmas vinculadas</span>
                <?php if (empty($linkedClasses)): ?>
                    <p class="muted">Nenhuma turma vinculada ainda.</p>
                <?php else: ?>
                    <?php foreach (array_slice($linkedClasses, 0, 3) as $class): ?>
                        <p><strong><?= e($class['name']) ?></strong><br><span class="muted"><?= e($class['period'] ?: 'período a definir') ?></span></p>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>
        </div>
    <?php endif; ?>

    <div class="portal-section-title">
        <span class="eyebrow">Atalhos</span>
        <h2>Áreas principais</h2>
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
