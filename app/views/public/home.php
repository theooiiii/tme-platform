<?php defined('BASE_PATH') || exit('Acesso direto não permitido.'); ?>

<section class="hero">
    <div class="hero-content">
        <span class="eyebrow">LMS • EAD • Gestão • Comunidade</span>
        <h1>TME — Theo Mind Educacional</h1>
        <p>Tecnologia, ensino e evolução em uma única plataforma.</p>
        <div class="hero-actions">
            <a class="button large" href="<?= e(url('/cadastro')) ?>">Começar cadastro</a>
            <a class="button ghost large" href="<?= e(url('/sobre')) ?>">Conhecer a TME</a>
        </div>
    </div>
    <div class="hero-visual" aria-hidden="true">
        <div class="visual-topline">
            <span></span><span></span><span></span>
        </div>
        <div class="visual-grid">
            <div>
                <small>Aprendizagem</small>
                <strong>82%</strong>
            </div>
            <div>
                <small>Comunidade</small>
                <strong>+248</strong>
            </div>
            <div>
                <small>XP acadêmico</small>
                <strong>4.920</strong>
            </div>
            <div>
                <small>Eventos</small>
                <strong>12</strong>
            </div>
        </div>
        <div class="signal-line"></div>
    </div>
</section>

<section class="page-section">
    <div class="section-heading">
        <span class="eyebrow">Base inicial</span>
        <h2>Uma arquitetura preparada para crescer</h2>
        <p>A primeira versão organiza autenticação, aprovações, painéis por perfil, instituições e módulos educacionais planejados.</p>
    </div>

    <div class="feature-grid">
        <article class="feature-card">
            <h3>Ensino estruturado</h3>
            <p>Cursos, aulas, módulos, materiais e progresso em uma base pronta para LMS e EAD.</p>
        </article>
        <article class="feature-card">
            <h3>Gestão educacional</h3>
            <p>Perfis para aluno, professor, supervisor, administração, secretaria e financeiro.</p>
        </article>
        <article class="feature-card">
            <h3>Comunidade acadêmica</h3>
            <p>Posts, projetos, comentários e moderação obrigatória desde a modelagem inicial.</p>
        </article>
        <article class="feature-card">
            <h3>IA futura</h3>
            <p>Estrutura reservada para tutor inteligente, correção automática, quizzes e análise de desempenho.</p>
        </article>
    </div>
</section>
