<?php
defined('BASE_PATH') || exit('Acesso direto não permitido.');

$isValid = $certificate['validation_status'] === 'valido';
$issuedAt = $certificate['issued_at'] ? date('d/m/Y', strtotime($certificate['issued_at'])) : date('d/m/Y');
$completedAt = $certificate['enrollment_completed_at'] ? date('d/m/Y', strtotime($certificate['enrollment_completed_at'])) : $issuedAt;
$validationUrl = rtrim((string) config('app.url'), '/') . '/certificados/validar/' . rawurlencode($certificate['code']);
$qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=' . rawurlencode($validationUrl);
?>

<section class="dashboard-shell certificate-view">
    <div class="admin-toolbar no-print">
        <div class="dashboard-heading">
            <span class="eyebrow">Certificado</span>
            <h1><?= e($certificate['code']) ?></h1>
            <p>Visualização HTML pronta para impressão ou salvamento em PDF pelo navegador.</p>
        </div>
        <div class="actions-row">
            <button class="button large" type="button" onclick="window.print()">Imprimir/Salvar PDF</button>
            <a class="button ghost large" href="<?= e(url('/certificados')) ?>">Voltar</a>
        </div>
    </div>

    <?php if (! $isValid): ?>
        <div class="flash warning no-print">
            Este certificado foi revogado e deve aparecer como inválido na validação pública.
        </div>
    <?php endif; ?>

    <article class="certificate-sheet <?= $isValid ? '' : 'revoked' ?>">
        <div class="certificate-border">
            <header>
                <span class="brand-mark">TME</span>
                <div>
                    <strong>Theo Mind Educacional</strong>
                    <small>Tecnologia, ensino e evolução em uma única plataforma.</small>
                </div>
            </header>

            <div class="certificate-body">
                <span class="eyebrow">Certificado de conclusão</span>
                <h2>Certificamos que</h2>
                <h1><?= e($certificate['student_name']) ?></h1>
                <p>concluiu com aproveitamento o curso</p>
                <h3><?= e($certificate['course_title'] ?: $certificate['title']) ?></h3>
                <p>com carga horária de <strong><?= e((int) $certificate['workload_hours']) ?> horas</strong>, finalizado em <?= e($completedAt) ?>.</p>
            </div>

            <footer>
                <div>
                    <span>Emitido em</span>
                    <strong><?= e($issuedAt) ?></strong>
                </div>
                <div>
                    <span>Código único</span>
                    <strong><?= e($certificate['code']) ?></strong>
                </div>
                <div>
                    <span>Status</span>
                    <strong><?= e($isValid ? 'Válido' : 'Revogado') ?></strong>
                </div>
            </footer>

            <div class="certificate-qr">
                <img src="<?= e($qrUrl) ?>" alt="QR Code de validação do certificado">
                <span>Validação pública</span>
                <small><?= e($validationUrl) ?></small>
            </div>
        </div>
    </article>
</section>
