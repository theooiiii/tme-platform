<?php
defined('BASE_PATH') || exit('Acesso direto não permitido.');

$firstName = explode(' ', trim($user['full_name']))[0] ?: $user['full_name'];
$initials = strtoupper(substr($user['full_name'], 0, 1));
?>

<section class="dashboard-shell profile-shell">
    <div class="profile-hero">
        <div class="profile-avatar"><?= e($initials) ?></div>
        <div>
            <span class="eyebrow">Perfil e configurações</span>
            <h1><?= e($firstName) ?></h1>
            <p>Centralize identidade, aparência, estatísticas, segurança e preferências da sua experiência TME.</p>
        </div>
    </div>

    <div class="profile-layout">
        <aside class="profile-sidebar">
            <a href="#perfil">Perfil</a>
            <a href="#aparencia">Aparência</a>
            <a href="#estatisticas">Estatísticas</a>
            <a href="#seguranca">Segurança</a>
        </aside>

        <div class="profile-content">
            <section id="perfil" class="profile-panel">
                <div class="section-toolbar compact">
                    <div>
                        <span class="eyebrow">Informações</span>
                        <h2>Dados do usuário</h2>
                    </div>
                    <span class="status-badge <?= e($user['status']) ?>"><?= e(human_label($user['status'])) ?></span>
                </div>

                <div class="profile-info-grid">
                    <div>
                        <span>Nome</span>
                        <strong><?= e($user['full_name']) ?></strong>
                    </div>
                    <div>
                        <span>E-mail</span>
                        <strong><?= e($user['email']) ?></strong>
                    </div>
                    <div>
                        <span>Tipo de conta</span>
                        <strong><?= e(role_label($user['role_slug'])) ?></strong>
                    </div>
                    <div>
                        <span>Instituição</span>
                        <strong><?= e($user['institution_name'] ?: 'Independente') ?></strong>
                    </div>
                    <div>
                        <span>Área de interesse</span>
                        <strong><?= e($user['interest_area'] ?: 'Não informada') ?></strong>
                    </div>
                    <div>
                        <span>Foto de perfil</span>
                        <strong>Placeholder preparado</strong>
                    </div>
                    <div>
                        <span>Plano atual</span>
                        <strong><?= e($activeSubscription['plan_name'] ?? 'Sem plano ativo') ?></strong>
                    </div>
                </div>

                <form class="form" action="<?= e(url('/perfil/dados')) ?>" method="post">
                    <?= csrf_field() ?>
                    <label>
                        Biografia curta
                        <textarea name="bio_short" rows="4" maxlength="280" placeholder="Conte em poucas linhas sua trajetória acadêmica."><?= e($user['bio_short'] ?? '') ?></textarea>
                    </label>
                    <button class="button" type="submit">Salvar perfil</button>
                </form>

                <div class="section-toolbar compact">
                    <div>
                        <span class="eyebrow">Comunidade</span>
                        <h2>Meus posts</h2>
                    </div>
                    <a class="button ghost small" href="<?= e(url('/comunidade')) ?>">Abrir feed</a>
                </div>
                <div class="profile-post-list">
                    <?php if (empty($posts)): ?>
                        <p class="muted">Seus posts acadêmicos aparecerão aqui.</p>
                    <?php else: ?>
                        <?php foreach ($posts as $post): ?>
                            <article>
                                <strong><?= e($post['title']) ?></strong>
                                <span class="status-badge <?= e($post['status']) ?>"><?= e(human_label($post['status'])) ?></span>
                            </article>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>

            <section id="aparencia" class="profile-panel">
                <div class="section-toolbar compact">
                    <div>
                        <span class="eyebrow">Personalizacao</span>
                        <h2>Aparência</h2>
                    </div>
                </div>

                <form class="form grid-form" action="<?= e(url('/perfil/aparencia')) ?>" method="post">
                    <?= csrf_field() ?>
                    <label>
                        Tema
                        <select name="theme" data-profile-theme>
                            <option value="light" <?= $settings['theme'] === 'light' ? 'selected' : '' ?>>Claro</option>
                            <option value="dark" <?= $settings['theme'] === 'dark' ? 'selected' : '' ?>>Escuro</option>
                        </select>
                    </label>
                    <label>
                        Cor principal
                        <input type="color" name="primary_color" value="<?= e($settings['primary_color']) ?>" data-profile-color>
                    </label>
                    <div class="profile-preview span-2">
                        <span>Preview</span>
                        <strong>Botao, barras de progresso e destaques usam sua cor principal.</strong>
                        <div class="progress-track"><span style="width: 72%;"></span></div>
                    </div>
                    <button class="button span-2" type="submit">Salvar aparência</button>
                </form>
            </section>

            <section id="estatisticas" class="profile-panel">
                <div class="section-toolbar compact">
                    <div>
                        <span class="eyebrow">Gamificacao</span>
                        <h2>Estatísticas</h2>
                    </div>
                    <a class="button ghost small" href="<?= e(url('/ranking')) ?>">Ver ranking</a>
                </div>

                <div class="metric-grid profile-metrics">
                    <article class="metric"><span>XP</span><strong><?= e((int) $profile['xp_total']) ?></strong></article>
                    <article class="metric"><span>Nivel</span><strong><?= e((int) $profile['level']) ?></strong></article>
                    <article class="metric"><span>Cursos</span><strong><?= e($stats['enrolled_courses']) ?></strong></article>
                    <article class="metric"><span>Concluidos</span><strong><?= e($stats['completed_courses']) ?></strong></article>
                    <article class="metric"><span>Atividades</span><strong><?= e($stats['submitted_activities']) ?></strong></article>
                    <article class="metric"><span>Certificados</span><strong><?= e($stats['certificates']) ?></strong></article>
                    <article class="metric"><span>Moedas</span><strong><?= e((int) ($profile['internal_coins'] ?? 0)) ?></strong></article>
                    <article class="metric"><span>Notificações</span><strong><?= e((int) $unreadNotifications) ?></strong></article>
                </div>

                <div class="badge-strip">
                    <?php if (empty($badges)): ?>
                        <span class="muted">Suas conquistas recentes aparecerão aqui.</span>
                    <?php else: ?>
                        <?php foreach ($badges as $badge): ?>
                            <span class="badge-pill"><?= e($badge['name']) ?></span>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>

            <section id="seguranca" class="profile-panel">
                <div class="section-toolbar compact">
                    <div>
                        <span class="eyebrow">Segurança</span>
                        <h2>Acesso</h2>
                    </div>
                </div>

                <form class="form grid-form" action="<?= e(url('/perfil/senha')) ?>" method="post">
                    <?= csrf_field() ?>
                    <label class="span-2">
                        Senha atual
                        <input type="password" name="current_password" autocomplete="current-password" required>
                    </label>
                    <label>
                        Nova senha
                        <input type="password" name="password" autocomplete="new-password" required minlength="8">
                    </label>
                    <label>
                        Confirmar nova senha
                        <input type="password" name="password_confirmation" autocomplete="new-password" required minlength="8">
                    </label>
                    <button class="button span-2" type="submit">Alterar senha</button>
                </form>

                <div class="security-actions">
                    <div>
                        <strong>Sessoes futuras</strong>
                        <p class="muted">Área reservada para histórico de dispositivos e encerramento remoto.</p>
                    </div>
                    <a class="button ghost" href="<?= e(url('/notificacoes')) ?>">Notificações</a>
                    <a class="button ghost" href="<?= e(url('/financeiro')) ?>">Financeiro</a>
                    <form action="<?= e(url('/logout')) ?>" method="post">
                        <?= csrf_field() ?>
                        <button class="button ghost" type="submit">Sair da conta</button>
                    </form>
                </div>
            </section>
        </div>
    </div>
</section>
