<?php
/**
 * admin/payments.php — Registro de pagos.
 */
$estado = $filtros['estado'] ?? '';
?>

<div class="container-fluid px-4 py-4">

    <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h4 fw-bold mb-1">
                <i class="bi bi-credit-card text-primary me-2"></i>Registro de pagos
            </h1>
            <p class="text-muted mb-0" style="font-size:.875rem">
                <?= number_format((int)($pagination['total'] ?? 0)) ?> transacción(es)
            </p>
        </div>
        <a href="<?= APP_URL ?>/admin" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left me-1"></i>Dashboard
        </a>
    </div>

    <!-- KPIs de pagos -->
    <div class="row g-3 mb-4">
        <?php
        $kpis = [
            ['label' => 'Total recaudado',   'valor' => '$' . number_format((float)($statsP['total_recaudado'] ?? 0), 2),  'color' => '#10B981', 'icono' => 'bi-cash-stack'],
            ['label' => 'Completados',       'valor' => number_format((int)($statsP['completados'] ?? 0)),  'color' => '#10B981', 'icono' => 'bi-check-circle'],
            ['label' => 'Pendientes',        'valor' => number_format((int)($statsP['pendientes']  ?? 0)),  'color' => '#F59E0B', 'icono' => 'bi-hourglass'],
            ['label' => 'Fallidos',          'valor' => number_format((int)($statsP['fallidos']    ?? 0)),  'color' => '#FF2D75', 'icono' => 'bi-x-circle'],
        ];
        foreach ($kpis as $k):
        ?>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-card__value" style="color:<?= $k['color'] ?>;font-size:1.6rem">
                    <?= $k['valor'] ?>
                </div>
                <div class="stat-card__label"><?= e($k['label']) ?></div>
                <i class="bi <?= e($k['icono']) ?> stat-card__icon" style="color:<?= $k['color'] ?>;opacity:.15"></i>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Filtro estado -->
    <div class="d-flex gap-2 mb-4 flex-wrap">
        <?php
        $tabs = [
            ''           => 'Todos',
            'completado' => 'Completados',
            'pendiente'  => 'Pendientes',
            'fallido'    => 'Fallidos',
        ];
        foreach ($tabs as $val => $lbl):
        ?>
        <a href="?estado=<?= e($val) ?>"
           class="btn btn-sm <?= $estado === $val ? 'btn-primary' : 'btn-secondary' ?>">
            <?= $lbl ?>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Tabla -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Usuario</th>
                            <th>Anuncio</th>
                            <th>Plan</th>
                            <th>Monto</th>
                            <th>Estado</th>
                            <th class="d-none d-md-table-cell">Referencia</th>
                            <th class="d-none d-lg-table-cell">Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($pagos)): ?>
                        <tr><td colspan="8" class="text-center text-muted py-5">Sin pagos registrados.</td></tr>
                    <?php else: ?>
                    <?php foreach ($pagos as $p): ?>
                    <tr>
                        <td class="text-muted" style="font-size:.78rem"><?= (int)$p['id'] ?></td>
                        <td>
                            <a href="<?= APP_URL ?>/admin/usuario/<?= (int)$p['id_usuario'] ?>"
                               style="font-size:.83rem;color:var(--color-primary)">
                                <?= e($p['usuario_nombre'] ?? '—') ?>
                            </a>
                            <div class="text-muted" style="font-size:.72rem"><?= e($p['usuario_email'] ?? '') ?></div>
                        </td>
                        <td>
                            <div class="text-truncate" style="max-width:160px;font-size:.83rem">
                                <?= e(Security::truncate($p['anuncio_titulo'] ?? '—', 40)) ?>
                            </div>
                        </td>
                        <td>
                            <span class="badge-estado badge-destacado">
                                <i class="bi bi-star-fill me-1" style="font-size:.7rem"></i>
                                <?= (int)$p['tipo_destacado'] ?> días
                            </span>
                        </td>
                        <td class="fw-bold" style="color:var(--color-primary)">
                            <?= e(Security::formatMoney((float)$p['monto'])) ?>
                        </td>
                        <td>
                            <?php
                            $pMap = [
                                'completado' => ['badge-publicado','Completado'],
                                'pendiente'  => ['badge-pendiente','Pendiente'],
                                'fallido'    => ['badge-rechazado','Fallido'],
                                'reembolsado'=> ['badge-expirado','Reembolsado'],
                            ];
                            [$pc,$pl] = $pMap[$p['estado']] ?? ['','?'];
                            ?>
                            <span class="badge-estado <?= $pc ?>"><?= $pl ?></span>
                        </td>
                        <td class="d-none d-md-table-cell">
                            <?php if ($p['referencia_ext']): ?>
                            <code style="font-size:.72rem;background:var(--color-bg-card2);color:var(--color-text);padding:.15rem .4rem;border-radius:3px">
                                <?= e($p['referencia_ext']) ?>
                            </code>
                            <?php else: ?>
                            <span class="text-muted" style="font-size:.78rem">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="d-none d-lg-table-cell text-muted" style="font-size:.78rem;white-space:nowrap">
                            <?= $p['fecha_pago']
                                ? e(date('d/m/Y H:i', strtotime($p['fecha_pago'])))
                                : e(Security::timeAgo($p['fecha_creacion'])) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Paginación -->
    <?php if (($pagination['pages'] ?? 1) > 1): ?>
    <nav class="mt-4 d-flex justify-content-center">
        <ul class="pagination">
            <li class="page-item <?= $pagination['current'] <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= $pagination['current']-1 ?>&estado=<?= e($estado) ?>">
                    <i class="bi bi-chevron-left"></i>
                </a>
            </li>
            <?php for ($p = 1; $p <= $pagination['pages']; $p++): ?>
            <li class="page-item <?= $p === (int)$pagination['current'] ? 'active':'' ?>">
                <a class="page-link" href="?page=<?= $p ?>&estado=<?= e($estado) ?>"><?= $p ?></a>
            </li>
            <?php endfor; ?>
            <li class="page-item <?= $pagination['current'] >= $pagination['pages'] ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= $pagination['current']+1 ?>&estado=<?= e($estado) ?>">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>

</div>
