<?php
/**
 * dashboard.php — Panel principal del usuario.
 * Layout tipo "home de app": grid de módulos con estado actual.
 */
$estadoVer  = $currentUser['estado_verificacion'];
$verificado = $currentUser['verificado'];
$bloqueada  = in_array($estadoVer, ['rechazado', 'suspendido'], true);

$sinAnticipo = (bool)($usuarioCompleto['sin_anticipo']       ?? false);
$docEstado   = $usuarioCompleto['documento_estado']          ?? null;
$docTiene    = !empty($usuarioCompleto['documento_identidad']);

// Resumen docs
$docInfo = [
    null         => ['label' => 'Subir documento',      'color' => 'var(--color-primary)',  'icon' => 'file-earmark-plus'],
    'pendiente'  => ['label' => 'En revisión',          'color' => '#F59E0B',               'icon' => 'hourglass-split'],
    'verificado' => ['label' => 'Verificado',           'color' => '#10B981',               'icon' => 'patch-check-fill'],
    'rechazado'  => ['label' => 'Rechazado · reenvía',  'color' => '#EF4444',               'icon' => 'x-octagon-fill'],
];
$doc = $docTiene ? ($docInfo[$docEstado] ?? $docInfo['pendiente']) : $docInfo[null];

// Confiabilidad — cuántos activos
$confActivos = count(array_filter($confiabilidad['indicadores'] ?? [], fn($i) => !empty($i['activo'])));
$confTotal   = count($confiabilidad['indicadores'] ?? []);
?>

<!-- HERO compacto -->
<div class="py-4" style="background:linear-gradient(135deg,#FFF5F8 0%,#FFFFFF 60%);border-bottom:1px solid var(--color-border)">
    <div class="container">
        <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3">
            <div>
                <h1 class="h4 fw-bold mb-1">
                    Hola, <?= e($currentUser['nombre']) ?> <span style="font-size:1.1em">👋</span>
                </h1>
                <p class="mb-0" style="font-size:.875rem">
                    <?php if ($bloqueada): ?>
                        <span class="d-inline-flex align-items-center gap-1 px-2 py-1"
                              style="background:rgba(239,68,68,.12);color:#991B1B;border:1px solid rgba(239,68,68,.25);border-radius:20px;font-size:.75rem;font-weight:600">
                            <i class="bi bi-x-circle"></i>Cuenta <?= e($estadoVer === 'suspendido' ? 'suspendida' : 'rechazada') ?>
                        </span>
                    <?php elseif ($estadoVer === 'pendiente'): ?>
                        <span class="d-inline-flex align-items-center gap-1 px-2 py-1"
                              style="background:rgba(245,158,11,.12);color:#92400E;border:1px solid rgba(245,158,11,.25);border-radius:20px;font-size:.75rem;font-weight:600">
                            <i class="bi bi-clock-history"></i>En revisión
                        </span>
                    <?php else: ?>
                        <span class="d-inline-flex align-items-center gap-1 px-2 py-1"
                              style="background:rgba(16,185,129,.12);color:#065F46;border:1px solid rgba(16,185,129,.25);border-radius:20px;font-size:.75rem;font-weight:600">
                            <i class="bi bi-check-circle-fill"></i>Cuenta activa
                        </span>
                    <?php endif; ?>
                </p>
            </div>
            <?php if (!$bloqueada): ?>
            <a href="<?= APP_URL ?>/perfil/nuevo" class="btn btn-primary d-flex align-items-center gap-2">
                <i class="bi bi-plus-lg"></i>Crear nuevo perfil
            </a>
            <?php else: ?>
            <a href="<?= APP_URL ?>/cuenta/reactivar" class="btn btn-danger d-flex align-items-center gap-2">
                <i class="bi bi-envelope-exclamation"></i>Solicitar reactivación
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="container py-4">

    <div class="row g-4">

        <!-- ====== COLUMNA DERECHA (desktop): Mis perfiles ====== -->
        <div class="col-12 col-lg-8 order-lg-2">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <span class="fw-semibold">
                        <i class="bi bi-person-lines-fill text-primary me-2"></i>Mis perfiles recientes
                    </span>
                    <a href="<?= APP_URL ?>/mis-perfiles" class="btn btn-sm btn-secondary">Ver todos</a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($misPerfiles)): ?>
                        <div class="text-center py-5 px-3">
                            <i class="bi bi-person-x" style="font-size:3rem;color:var(--color-border)"></i>
                            <p class="text-muted mt-3 mb-3">Aún no tienes perfiles creados.</p>
                            <a href="<?= APP_URL ?>/perfil/nuevo" class="btn btn-primary btn-sm">
                                <i class="bi bi-plus-lg me-1"></i>Crear mi primer perfil
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead>
                                    <tr>
                                        <th style="width:48px"></th>
                                        <th>Perfil</th>
                                        <th>Estado</th>
                                        <th class="d-none d-md-table-cell">Ubicación</th>
                                        <th>Visitas</th>
                                        <th class="text-end">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($misPerfiles, 0, 5) as $p):
                                        $imgUrl = !empty($p['imagen_token'])
                                            ? APP_URL . '/img/' . $p['imagen_token'] . '?size=thumb'
                                            : null;
                                        $estadoMap = [
                                            'pendiente' => ['badge-pendiente','En revisión'],
                                            'publicado' => ['badge-publicado','Publicado'],
                                            'rechazado' => ['badge-rechazado','Rechazado'],
                                        ];
                                        [$cls, $lbl] = $estadoMap[$p['estado']] ?? ['','?'];
                                    ?>
                                    <tr>
                                        <td>
                                            <?php if ($imgUrl): ?>
                                            <img src="<?= e($imgUrl) ?>" alt=""
                                                 style="width:40px;height:40px;object-fit:cover;border-radius:6px;border:1px solid var(--color-border)">
                                            <?php else: ?>
                                            <div style="width:40px;height:40px;border-radius:6px;background:var(--color-bg-alt);border:1px solid var(--color-border);display:flex;align-items:center;justify-content:center;color:var(--color-text-muted)">
                                                <i class="bi bi-person"></i>
                                            </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="fw-semibold" style="font-size:.88rem">
                                                <?= e($p['nombre']) ?>
                                                <?php if (!empty($p['edad'])): ?><span class="text-muted fw-normal">, <?= (int)$p['edad'] ?></span><?php endif; ?>
                                            </div>
                                            <div class="text-muted" style="font-size:.74rem"><?= e($p['categoria_nombre'] ?? '—') ?></div>
                                        </td>
                                        <td><span class="badge-estado <?= $cls ?>"><?= $lbl ?></span></td>
                                        <td class="d-none d-md-table-cell text-muted" style="font-size:.82rem">
                                            <?= e($p['municipio_nombre'] ?? $p['ciudad'] ?? '—') ?>
                                        </td>
                                        <td style="font-size:.82rem;min-width:80px">
                                            <div class="fw-semibold" style="color:var(--color-primary)">
                                                <i class="bi bi-eye me-1" style="font-size:.75rem"></i><?= number_format((int)$p['vistas']) ?>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex gap-1 justify-content-end">
                                                <?php if ($p['estado'] === 'publicado'): ?>
                                                <a href="<?= APP_URL ?>/perfil/<?= (int)$p['id'] ?>"
                                                   class="btn btn-sm btn-secondary" title="Ver">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <?php endif; ?>
                                                <a href="<?= APP_URL ?>/perfil/<?= (int)$p['id'] ?>/editar"
                                                   class="btn btn-sm btn-secondary" title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div><!-- /col-lg-8 -->

        <!-- ====== COLUMNA IZQUIERDA (desktop): menú + cómo funciona + confiabilidad ====== -->
        <div class="col-12 col-lg-4 order-lg-1 d-flex flex-column gap-4">

            <!-- ====== MENÚ PRINCIPAL (tipo lista) ====== -->
            <div class="dash-menu">

                <!-- Sección: MI CUENTA -->
        <div class="dash-menu__section">
            <div class="dash-menu__heading">Mi cuenta</div>

            <a href="<?= APP_URL ?>/mis-perfiles" class="dash-menu__item">
                <div class="dash-menu__icon" style="color:var(--color-primary);background:rgba(255,45,117,.12)">
                    <i class="bi bi-person-lines-fill"></i>
                </div>
                <div class="dash-menu__body">
                    <div class="dash-menu__title">Mis perfiles</div>
                    <div class="dash-menu__hint">
                        <?php
                        $pub = (int)($statsPerfiles['publicados'] ?? 0);
                        $pen = (int)($statsPerfiles['pendientes'] ?? 0);
                        $tot = (int)($statsPerfiles['total']     ?? 0);
                        if ($tot === 0) echo 'Aún no has creado perfiles';
                        else {
                            $parts = [];
                            if ($pub > 0) $parts[] = $pub . ' publicado(s)';
                            if ($pen > 0) $parts[] = $pen . ' en revisión';
                            echo implode(' · ', $parts) ?: ($tot . ' perfil(es)');
                        }
                        ?>
                    </div>
                </div>
                <span class="dash-menu__value"><?= (int)($statsPerfiles['total'] ?? 0) ?></span>
                <i class="bi bi-chevron-right dash-menu__arrow"></i>
            </a>

            <a href="<?= APP_URL ?>/mis-tokens" class="dash-menu__item">
                <div class="dash-menu__icon" style="color:var(--color-primary);background:rgba(255,45,117,.12)">
                    <i class="bi bi-coin"></i>
                </div>
                <div class="dash-menu__body">
                    <div class="dash-menu__title">Mis tokens</div>
                    <div class="dash-menu__hint">Saldo para destacar tus perfiles</div>
                </div>
                <span class="dash-menu__value"><?= number_format((int)$saldoTokens) ?></span>
                <i class="bi bi-chevron-right dash-menu__arrow"></i>
            </a>

            <a href="<?= APP_URL ?>/mis-estadisticas" class="dash-menu__item">
                <div class="dash-menu__icon" style="color:#3B82F6;background:rgba(59,130,246,.12)">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>
                <div class="dash-menu__body">
                    <div class="dash-menu__title">Estadísticas</div>
                    <div class="dash-menu__hint">Visitas y clics de tus perfiles</div>
                </div>
                <span class="dash-menu__value"><?= number_format((int)($statsPerfiles['total_vistas'] ?? 0)) ?></span>
                <i class="bi bi-chevron-right dash-menu__arrow"></i>
            </a>

            <a href="<?= APP_URL ?>/notificaciones" class="dash-menu__item">
                <div class="dash-menu__icon" style="color:#8B5CF6;background:rgba(139,92,246,.12)">
                    <i class="bi bi-bell-fill"></i>
                </div>
                <div class="dash-menu__body">
                    <div class="dash-menu__title">Notificaciones</div>
                    <div class="dash-menu__hint"><?= $notifCount > 0 ? 'Tienes notificaciones sin leer' : 'Sin notificaciones pendientes' ?></div>
                </div>
                <?php if ($notifCount > 0): ?>
                <span class="dash-menu__value dash-menu__value--danger"><?= (int)$notifCount ?></span>
                <?php else: ?>
                <span class="dash-menu__value dash-menu__value--muted">0</span>
                <?php endif; ?>
                <i class="bi bi-chevron-right dash-menu__arrow"></i>
            </a>
        </div>

        <!-- Sección: VERIFICACIÓN -->
        <div class="dash-menu__section">
            <div class="dash-menu__heading">Verificación y confianza</div>

            <a href="<?= APP_URL ?>/mi-cuenta/documento" class="dash-menu__item">
                <div class="dash-menu__icon" style="color:<?= e($doc['color']) ?>;background:rgba(0,0,0,.04)">
                    <i class="bi bi-<?= e($doc['icon']) ?>"></i>
                </div>
                <div class="dash-menu__body">
                    <div class="dash-menu__title">Documento de identidad</div>
                    <div class="dash-menu__hint">INE, IFE o pasaporte vigente</div>
                </div>
                <span class="dash-menu__value" style="color:<?= e($doc['color']) ?>;font-size:.78rem;font-weight:600"><?= e($doc['label']) ?></span>
                <i class="bi bi-chevron-right dash-menu__arrow"></i>
            </a>

            <a href="#confiabilidad-detalle" class="dash-menu__item">
                <div class="dash-menu__icon" style="color:#10B981;background:rgba(16,185,129,.12)">
                    <i class="bi bi-patch-check-fill"></i>
                </div>
                <div class="dash-menu__body">
                    <div class="dash-menu__title">Mi confiabilidad</div>
                    <div class="dash-menu__hint">Indicadores activos en tu perfil</div>
                </div>
                <span class="dash-menu__value dash-menu__value--muted"><?= (int)$confActivos ?></span>
                <i class="bi bi-chevron-down dash-menu__arrow"></i>
            </a>
        </div>

        <!-- Sección: OTROS -->
        <div class="dash-menu__section">
            <div class="dash-menu__heading">Otros</div>

            <a href="<?= APP_URL ?>/perfiles" class="dash-menu__item">
                <div class="dash-menu__icon" style="color:#3B82F6;background:rgba(59,130,246,.12)">
                    <i class="bi bi-compass-fill"></i>
                </div>
                <div class="dash-menu__body">
                    <div class="dash-menu__title">Explorar perfiles</div>
                    <div class="dash-menu__hint">Ver perfiles públicos del directorio</div>
                </div>
                <i class="bi bi-chevron-right dash-menu__arrow"></i>
            </a>

            <a href="<?= APP_URL ?>/cuenta/reactivar" class="dash-menu__item">
                <div class="dash-menu__icon" style="color:#F59E0B;background:rgba(245,158,11,.14)">
                    <i class="bi bi-envelope-fill"></i>
                </div>
                <div class="dash-menu__body">
                    <div class="dash-menu__title">Soporte</div>
                    <div class="dash-menu__hint">
                        <?= $mensajesAbiertos > 0 ? 'Tienes conversación(es) activa(s)' : 'Contactar al equipo' ?>
                    </div>
                </div>
                <?php if ($mensajesAbiertos > 0): ?>
                <span class="dash-menu__value dash-menu__value--warning"><?= (int)$mensajesAbiertos ?></span>
                <?php endif; ?>
                <i class="bi bi-chevron-right dash-menu__arrow"></i>
            </a>
        </div>

            </div><!-- /menu -->

            <!-- ¿Cómo funciona? -->
            <div class="card">
                <div class="card-header">
                    <span class="fw-semibold">
                        <i class="bi bi-info-circle text-primary me-2"></i>¿Cómo funciona?
                    </span>
                </div>
                <div class="card-body">
                    <?php
                    $tienePublicados = ($statsPerfiles['publicados'] ?? 0) > 0;
                    $tienePerfiles   = ($statsPerfiles['total']      ?? 0) > 0;
                    $pasos = [
                        ['icono' => 'bi-person-check-fill', 'texto' => 'Cuenta activa',             'hecho' => !$bloqueada],
                        ['icono' => 'bi-plus-circle-fill',  'texto' => 'Crear un perfil',           'hecho' => $tienePerfiles],
                        ['icono' => 'bi-hourglass-split',   'texto' => 'Revisión por moderación',   'hecho' => $tienePublicados],
                        ['icono' => 'bi-eye-fill',          'texto' => 'Perfil visible al público', 'hecho' => $tienePublicados],
                    ];
                    ?>
                    <div class="d-flex flex-column gap-2">
                        <?php foreach ($pasos as $paso): ?>
                        <div class="d-flex align-items-center gap-3">
                            <div style="width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;
                                        background:<?= $paso['hecho'] ? 'rgba(16,185,129,.15)' : 'rgba(0,0,0,.04)' ?>;
                                        color:<?= $paso['hecho'] ? '#10B981' : 'var(--color-text-muted)' ?>">
                                <i class="bi <?= e($paso['icono']) ?>" style="font-size:.85rem"></i>
                            </div>
                            <span style="font-size:.85rem;color:<?= $paso['hecho'] ? 'var(--color-text)' : 'var(--color-text-muted)' ?>">
                                <?= e($paso['texto']) ?>
                                <?php if ($paso['hecho']): ?>
                                    <i class="bi bi-check-lg text-success ms-1" style="font-size:.75rem"></i>
                                <?php endif; ?>
                            </span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Mi confiabilidad detalle -->
            <div class="card" id="confiabilidad-detalle">
                <div class="card-header">
                    <span class="fw-semibold">
                        <i class="bi bi-patch-check text-primary me-2"></i>Mi confiabilidad
                    </span>
                </div>
                <div class="card-body" style="padding:.9rem 1rem">
                    <ul class="list-unstyled mb-3" style="display:flex;flex-direction:column;gap:.45rem">
                    <?php foreach ($confiabilidad['indicadores'] as $ind): ?>
                        <li class="d-flex align-items-center gap-2" style="font-size:.78rem">
                            <i class="bi <?= e($ind['icono']) ?>"
                               style="color:<?= $ind['activo'] ? '#10B981' : 'var(--color-text-muted)' ?>;opacity:<?= $ind['activo'] ? '1' : '.35' ?>;font-size:.85rem;flex-shrink:0"></i>
                            <span style="color:<?= $ind['activo'] ? 'var(--color-text)' : 'var(--color-text-muted)' ?>">
                                <?= e($ind['label']) ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                    </ul>

                    <div style="border-top:1px solid var(--color-border);padding-top:.75rem">
                        <p style="font-size:.75rem;color:var(--color-text-muted);margin-bottom:.5rem">
                            <i class="bi bi-shield-check me-1"></i>
                            Declara que no pides depósito anticipado para activar ese indicador de confianza.
                        </p>
                        <form method="POST" action="<?= APP_URL ?>/perfil/sin-anticipo">
                            <?= $csrfField ?>
                            <input type="hidden" name="sin_anticipo" value="<?= $sinAnticipo ? '0' : '1' ?>">
                            <button type="submit" class="btn btn-sm w-100"
                                    style="<?= $sinAnticipo
                                        ? 'background:rgba(239,68,68,.1);color:#991B1B;border:1px solid rgba(239,68,68,.25)'
                                        : 'background:rgba(16,185,129,.1);color:#065F46;border:1px solid rgba(16,185,129,.25)' ?>">
                                <?= $sinAnticipo
                                    ? '<i class="bi bi-x-lg me-1"></i>Desactivar declaración'
                                    : '<i class="bi bi-shield-check me-1"></i>Declarar: no pido anticipo' ?>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </div><!-- /sidebar -->
    </div><!-- /row -->

</div>
