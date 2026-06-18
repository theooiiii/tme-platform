<?php defined('BASE_PATH') || exit('Acesso direto não permitido.'); ?>

<section class="dashboard-shell">
    <div class="dashboard-heading">
        <span class="eyebrow">Notas</span>
        <h1>Boletim</h1>
        <p>Resumo simples das atividades corrigidas por curso.</p>
    </div>

    <?php if (empty($rows)): ?>
        <div class="empty-state">
            <h2>Sem notas ainda</h2>
            <p>Quando suas entregas forem corrigidas, o boletim aparecera aqui.</p>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Curso</th>
                        <th>Atividades</th>
                        <th>Corrigidas</th>
                        <th>Média</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <td><strong><?= e($row['course_title']) ?></strong></td>
                            <td><?= e((int) $row['activities_count']) ?></td>
                            <td><?= e((int) $row['graded_count']) ?></td>
                            <td><?= e($row['average_score'] !== null ? number_format((float) $row['average_score'], 2, ',', '.') : '-') ?></td>
                            <td><?= e($row['total_score'] !== null ? number_format((float) $row['total_score'], 2, ',', '.') : '0,00') ?> / <?= e($row['max_total'] !== null ? number_format((float) $row['max_total'], 2, ',', '.') : '0,00') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
