<?php
defined('BASE_PATH') || exit('Acesso direto não permitido.');

$isValid = $certificate && $certificate['validation_status'] === 'valido';
?>

<section class="auth-page">
    <div class="auth-panel validation-panel">
        <span class="eyebrow">Resultado</span>
        <h1><?= $isValid ? 'Certificado valido' : 'Certificado invalido' ?></h1>

        <?php if (! $certificate): ?>
            <p class="muted">Nenhum certificado foi encontrado para o código <?= e($code) ?>.</p>
        <?php else: ?>
            <div class="validation-result <?= $isValid ? 'valid' : 'invalid' ?>">
                <span class="status-badge <?= e($certificate['validation_status']) ?>"><?= e(human_label($certificate['validation_status'])) ?></span>
                <h2><?= e($certificate['student_name']) ?></h2>
                <p><?= e($certificate['course_title'] ?: $certificate['title']) ?></p>
                <div class="course-meta">
                    <span><?= e((int) $certificate['workload_hours']) ?>h</span>
                    <span>Emitido em <?= e(date('d/m/Y', strtotime($certificate['issued_at']))) ?></span>
                    <span><?= e($certificate['code']) ?></span>
                </div>
                <?php if (! $isValid): ?>
                    <p class="danger-text">Motivo: <?= e($certificate['revocation_reason'] ?: 'certificado revogado pela administracao') ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="actions-row">
            <a class="button large" href="<?= e(url('/certificados/validar')) ?>">Nova validação</a>
            <a class="button ghost large" href="<?= e(url('/')) ?>">Voltar</a>
        </div>
    </div>
</section>
