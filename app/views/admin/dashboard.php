<?php
/**
 * admin/dashboard.php — Dashboard principal del panel de administración.
 */
?>

<!-- Header -->
<div class="py-4" style="background:linear-gradient(135deg,var(--color-bg-card2) 0%,rgba(15,52,96,.5) 100%);border-bottom:1px solid var(--color-border)">
    <div class="container-fluid px-4">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h1 class="h4 fw-bold mb-1">
                    <i class="bi bi-shield-check text-primary me-2"></i>Panel de administración
                </h1>
                <p class="text-muted mb-0" style="font-size:.875rem">
                    <?= e(date('l, d \d\e F \d\e Y')) ?>
                </p>
            </div>
            <div class="d-flex gap-2">
                <a href="<?= APP_URL ?>/admin/usuarios?estado=pendiente" class="btn btn-sm btn-warning position-relative">
                    <i class="bi bi-person-exclamation me-1"></i>Verificaciones
                    <?php if ((int)($statsUsuarios['pendientes'] ?? 0) > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:.6rem">
                        <?= (int)$statsUsuarios['pendientes'] ?>
                    </span>
                    <?php endif; ?>
                </a>
                <a href="<?= APP_URL ?>/admin/reportes?estado=pendiente" class="btn btn-sm btn-danger position-relative">
                    <i class="bi bi-flag me-1"></i>Reportes
                    <?php if ((int)($statsReportes['pendientes'] ?? 0) > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning text-dark" style="font-size:.6rem">
                        <?= (int)$statsReportes['pendientes'] ?>
                    </span>
                    <?php endif; ?>
                </a>
                <a href="<?= APP_URL ?>/admin/tokens" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-coin me-1"></i>Tokens
                </a>
                <a href="<?= APP_URL ?>/admin/mensajes" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-envelope me-1"></i>Mensajes
                </a>
                <a href="<?= APP_URL ?>/admin/almacenamiento" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-hdd me-1"></i>Almacenamiento
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid px-4 py-4">

    <!-- KPIs principales -->
    <div class="row g-3 mb-4">
        <?php
        $kpis = [
            ['valor' => number_format((int)($statsUsuarios['total']     ?? 0)), 'label' => 'Usuarios totales',    'icono' => 'bi-people-fill',          'color' => '#17a2b8', 'link' => '/admin/usuarios'],
            ['valor' => number_format((int)($statsUsuarios['pendientes']?? 0)), 'label' => 'Pendientes verif.',   'icono' => 'bi-person-exclamation',   'color' => '#F59E0B', 'link' => '/admin/usuarios?estado=pendiente'],
            ['valor' => number_format((int)($statsPerfiles['total']     ?? 0)), 'label' => 'Perfiles totales',    'icono' => 'bi-person-lines-fill',    'color' => '#6f42c1', 'link' => '/admin/perfiles'],
            ['valor' => number_format((int)($statsPerfiles['publicados']?? 0)), 'label' => 'Publicados',          'icono' => 'bi-check-circle-fill',    'color' => '#10B981', 'link' => '/admin/perfiles?estado=publicado'],
            ['valor' => number_format((int)($statsPerfiles['pendientes']?? 0)), 'label' => 'En revisión',         'icono' => 'bi-hourglass-split',      'color' => '#FF2D75', 'link' => '/admin/perfiles?estado=pendiente'],
            ['valor' => number_format((int)($statsReportes['pendientes']?? 0)), 'label' => 'Reportes pendientes', 'icono' => 'bi-flag-fill',            'color' => '#FF2D75', 'link' => '/admin/reportes'],
        ];
        foreach ($kpis as $k):
        ?>
        <div class="col-6 col-sm-4 col-xl-2">
            <a href="<?= APP_URL . e($k['link']) ?>" class="text-decoration-none">
                <div class="stat-card" style="cursor:pointer">
                    <div class="stat-card__value" style="color:<?= $k['color'] ?>;font-size:1.7rem">
                        <?= $k['valor'] ?>
                    </div>
                    <div class="stat-card__label"><?= e($k['label']) ?></div>
                    <i class="bi <?= e($k['icono']) ?> stat-card__icon" style="color:<?= $k['color'] ?>;opacity:.15"></i>
                </div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="row g-4">

        <!-- Usuarios recientes -->
        <div class="col-12 col-xl-7">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <span class="fw-semibold">
                        <i class="bi bi-people text-primary me-2"></i>Usuarios recientes
                    </span>
                    <a href="<?= APP_URL ?>/admin/usuarios" class="btn btn-sm btn-secondary">Ver todos</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>Usuario</th>
                                    <th>Estado</th>
                                    <th class="d-none d-md-table-cell">Fecha registro</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($ultimosUsuarios)): ?>
                                <tr><td colspan="4" class="text-center text-muted py-4">Sin usuarios aún.</td></tr>
                            <?php else: ?>
                            <?php foreach ($ultimosUsuarios as $u): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div style="width:34px;height:34px;border-radius:50%;background:rgba(255,45,117,.1);border:1px solid rgba(255,45,117,.2);display:flex;align-items:center;justify-content:center;color:var(--color-primary);font-size:.8rem;font-weight:700;flex-shrink:0">
                                            <?= strtoupper(mb_substr($u['nombre'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div class="fw-semibold" style="font-size:.875rem"><?= e($u['nombre']) ?></div>
                                            <div class="text-muted" style="font-size:.72rem"><?= e($u['email']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php
                                    $map = [
                                        'pendiente' => ['badge-pendiente','clock','En revisión'],
                                        'aprobado'  => ['badge-publicado','check-circle','Aprobado'],
                                        'rechazado' => ['badge-rechazado','x-circle','Rechazado'],
                                    ];
                                    [$cls,$ico,$lbl] = $map[$u['estado_verificacion']] ?? ['','question','?'];
                                    ?>
                                    <span class="badge-estado <?= $cls ?>">
                                        <i class="bi bi-<?= $ico ?> me-1"></i><?= $lbl ?>
                                    </span>
                                </td>
                                <td class="d-none d-md-table-cell text-muted" style="font-size:.8rem">
                                    <?= e(Security::timeAgo($u['fecha_creacion'])) ?>
                                </td>
                                <td class="text-end">
                                    <?php if ($u['estado_verificacion'] === 'pendiente'): ?>
                                    <div class="d-flex gap-1 justify-content-end">
                                        <form method="POST" action="<?= APP_URL ?>/admin/usuario/<?= (int)$u['id'] ?>/aprobar">
                                            <?= $csrfField ?>
                                            <button class="btn btn-sm" style="background:rgba(16,185,129,.1);color:#10B981;border:1px solid rgba(16,185,129,.25)" title="Aprobar">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                        </form>
                                        <form method="POST" action="<?= APP_URL ?>/admin/usuario/<?= (int)$u['id'] ?>/rechazar">
                                            <?= $csrfField ?>
                                            <button class="btn btn-sm btn-danger" title="Rechazar"
                                                    data-confirm="¿Rechazar a <?= e($u['nombre']) ?>?">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </form>
                                    </div>
                                    <?php else: ?>
                                    <a href="<?= APP_URL ?>/admin/usuario/<?= (int)$u['id'] ?>" class="btn btn-sm btn-secondary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna derecha -->
        <div class="col-12 col-xl-5 d-flex flex-column gap-4">

            <!-- Perfiles pendientes -->
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <span class="fw-semibold">
                        <i class="bi bi-person-check text-warning me-2"></i>Perfiles en revisión
                        <?php if ((int)($statsPerfiles['pendientes'] ?? 0) > 0): ?>
                        <span class="badge bg-warning text-dark ms-1" style="font-size:.65rem">
                            <?= (int)$statsPerfiles['pendientes'] ?>
                        </span>
                        <?php endif; ?>
                    </span>
                    <a href="<?= APP_URL ?>/admin/perfiles?estado=pendiente" class="btn btn-sm btn-secondary">Ver todos</a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($perfilesPendientes)): ?>
                    <div class="text-center py-4 text-muted" style="font-size:.875rem">
                        <i class="bi bi-check-all fs-3 d-block mb-2 text-success"></i>
                        Sin perfiles pendientes
                    </div>
                    <?php else: ?>
                    <div class="d-flex flex-column" style="gap:0">
                        <?php foreach ($perfilesPendientes as $i => $p): ?>
                        <div class="px-3 py-2 d-flex align-items-center gap-3 <?= $i > 0 ? 'border-top' : '' ?>"
                             style="border-color:var(--color-border)">
                            <div class="flex-grow-1 min-width-0">
                                <div class="fw-semibold text-truncate" style="font-size:.83rem"><?= e($p['nombre']) ?></div>
                                <div class="text-muted" style="font-size:.72rem">
                                    <i class="bi bi-person me-1"></i><?= e($p['usuario_nombre']) ?>
                                    <span class="ms-2"><i class="bi bi-geo-alt me-1"></i><?= e($p['ciudad']) ?></span>
                                </div>
                            </div>
                            <div class="d-flex gap-1 flex-shrink-0">
                                <form method="POST" action="<?= APP_URL ?>/admin/perfil/<?= (int)$p['id'] ?>/publicar">
                                    <?= $csrfField ?>
                                    <button class="btn btn-sm" style="background:rgba(16,185,129,.1);color:#10B981;border:1px solid rgba(16,185,129,.2)" title="Publicar">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                </form>
                                <form method="POST" action="<?= APP_URL ?>/admin/perfil/<?= (int)$p['id'] ?>/rechazar">
                                    <?= $csrfField ?>
                                    <button class="btn btn-sm btn-danger" title="Rechazar"
                                            data-confirm="¿Rechazar el perfil «<?= e($p['nombre']) ?>»?">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Ingresos mensuales -->
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <span class="fw-semibold">
                        <i class="bi bi-graph-up text-success me-2"></i>Ingresos mensuales
                    </span>
                    <a href="<?= APP_URL ?>/admin/pagos" class="btn btn-sm btn-secondary">Ver pagos</a>
                </div>
                <div class="card-body">
                    <?php if (empty($ingresosMes)): ?>
                    <p class="text-muted text-center mb-0" style="font-size:.85rem">Sin pagos registrados aún.</p>
                    <?php else: ?>
                    <div class="d-flex flex-column gap-2">
                        <?php
                        $maxTotal = max(array_column($ingresosMes, 'total')) ?: 1;
                        foreach ($ingresosMes as $ingreso):
                            $pct    = round(($ingreso['total'] / $maxTotal) * 100);
                            $mesLabel = date('M Y', strtotime($ingreso['mes'] . '-01'));
                        ?>
                        <div>
                            <div class="d-flex justify-content-between mb-1" style="font-size:.78rem">
                                <span class="text-muted"><?= e($mesLabel) ?></span>
                                <span class="fw-semibold"><?= e(Security::formatMoney((float)$ingreso['total'])) ?></span>
                            </div>
                            <div class="progress" style="height:5px;background:rgba(0,0,0,.04)">
                                <div class="progress-bar bg-success" style="width:<?= $pct ?>%;border-radius:3px"></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <hr style="border-color:var(--color-border);margin:.75rem 0 .5rem">
                    <div class="d-flex justify-content-between" style="font-size:.82rem">
                        <span class="text-muted">Total acumulado:</span>
                        <span class="fw-black" style="color:var(--color-primary)">
                            <?= e(Security::formatMoney((float)($statsPagos['total_recaudado'] ?? 0))) ?>
                        </span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>
