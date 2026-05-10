<?php
/**
 * dashboard.php — Panel principal del usuario.
 * Layout: hero compacto + card hero de tokens (CTA primario) + cards de perfiles
 * + seccion educativa "por que destacar" + sidebar con verificacion/menu/pasos.
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

// Confiabilidad
$confActivos = count(array_filter($confiabilidad['indicadores'] ?? [], fn($i) => !empty($i['activo'])));

// Tarifas (default si vienen vacias)
$tarifaTop       = (int)($tarifaTop       ?? 3);
$tarifaResaltado = (int)($tarifaResaltado ?? 1);
$horasTop        = $tarifaTop       > 0 ? floor($saldoTokens / $tarifaTop)       : 0;
$horasResaltado  = $tarifaResaltado > 0 ? floor($saldoTokens / $tarifaResaltado) : 0;
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

    <!-- Tip de bandeja de spam (cerrable, sessionStorage) -->
    <div id="tipSpam" class="alert py-2 px-3 mb-3 d-flex align-items-start gap-2"
         style="background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.30);color:#92400E;font-size:.83rem;line-height:1.5">
        <i class="bi bi-envelope-exclamation" style="font-size:1.05rem;color:#F59E0B;flex-shrink:0;margin-top:2px"></i>
        <div class="flex-grow-1">
            <strong>Revisa tu carpeta de spam.</strong>
            Las notificaciones del sistema (perfil aprobado, pago confirmado, etc.) podrían llegar ahí.
            Cuando encuentres uno de nuestros correos, márcalo como <em>"No es spam"</em> para que los siguientes lleguen a tu bandeja principal.
        </div>
        <button type="button" class="btn-close" aria-label="Cerrar"
                style="font-size:.7rem"
                onclick="document.getElementById('tipSpam').style.display='none';try{sessionStorage.setItem('dashTipSpamCerrado','1');}catch(e){}">
        </button>
    </div>
    <script>
    (function(){
        try {
            if (sessionStorage.getItem('dashTipSpamCerrado') === '1') {
                var t = document.getElementById('tipSpam');
                if (t) t.style.display = 'none';
            }
        } catch(e) {}
    })();
    </script>

    <!-- ============================================================
         HERO TOKENS — CTA primario de monetizacion
         ============================================================ -->
    <div class="card mb-4 border-0"
         style="background:linear-gradient(135deg,#FFE0EC 0%,#FFF5F8 60%,#FFFFFF 100%);border:1px solid rgba(255,45,117,.22);box-shadow:0 4px 18px rgba(255,45,117,.08)">
        <div class="card-body p-4">
            <div class="row align-items-center g-3">

                <!-- Saldo + tip -->
                <div class="col-12 col-md-7">
                    <div class="text-uppercase fw-bold mb-1"
                         style="font-size:.7rem;letter-spacing:.1em;color:var(--color-primary)">
                        <i class="bi bi-coin me-1"></i>Mi saldo de tokens
                    </div>
                    <div class="fw-black" style="font-size:2.6rem;line-height:1;color:#1A1A1A">
                        <?= number_format($saldoTokens) ?>
                        <small class="fw-semibold text-muted ms-1" style="font-size:.95rem">tokens</small>
                    </div>
                    <?php if ($saldoTokens > 0): ?>
                    <div class="text-muted mt-2" style="font-size:.85rem">
                        Con tu saldo te destacas
                        <strong style="color:var(--color-primary)"><?= number_format($horasTop) ?> h</strong>
                        en TOP o <strong style="color:#F59E0B"><?= number_format($horasResaltado) ?> h</strong> Resaltada
                    </div>
                    <?php else: ?>
                    <div class="text-muted mt-2" style="font-size:.85rem">
                        Aún no tienes tokens. Compra un paquete y empieza a destacarte.
                    </div>
                    <?php endif; ?>
                </div>

                <!-- CTA -->
                <div class="col-12 col-md-5 text-md-end">
                    <a href="<?= APP_URL ?>/tokens/comprar" class="btn btn-primary btn-lg w-100" style="font-size:1rem;font-weight:700">
                        <i class="bi bi-cart-plus me-1"></i>Recargar tokens
                    </a>
                </div>

            </div>
        </div>
    </div>

    <div class="row g-4">

        <!-- ============================================================
             COLUMNA PRINCIPAL — perfiles + educativo
             ============================================================ -->
        <div class="col-12 col-lg-8 order-lg-2">

            <!-- Mis perfiles recientes (cards estilo /mis-perfiles) -->
            <div class="card mb-4">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <span class="fw-semibold">
                        <i class="bi bi-person-lines-fill text-primary me-2"></i>Mis perfiles recientes
                    </span>
                    <a href="<?= APP_URL ?>/mis-perfiles" class="btn btn-sm btn-secondary">Ver todos</a>
                </div>
                <div class="card-body">
                    <?php if (empty($misPerfiles)): ?>
                        <div class="text-center py-5 px-3">
                            <i class="bi bi-person-x" style="font-size:3rem;color:var(--color-border)"></i>
                            <p class="text-muted mt-3 mb-3">Aún no tienes perfiles creados.</p>
                            <a href="<?= APP_URL ?>/perfil/nuevo" class="btn btn-primary btn-sm">
                                <i class="bi bi-plus-lg me-1"></i>Crear mi primer perfil
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="row g-3">
                            <?php foreach (array_slice($misPerfiles, 0, 3) as $p):
                                $imgUrl = !empty($p['imagen_token'])
                                    ? APP_URL . '/img/' . $p['imagen_token'] . '?size=medium'
                                    : null;
                                $estadoMap = [
                                    'pendiente' => ['clase' => 'badge-pendiente', 'icono' => 'bi-clock',         'label' => 'En revisión'],
                                    'publicado' => ['clase' => 'badge-publicado', 'icono' => 'bi-check-circle',  'label' => 'Publicado'],
                                    'rechazado' => ['clase' => 'badge-rechazado', 'icono' => 'bi-x-circle',      'label' => 'Rechazado'],
                                ];
                                $info = $estadoMap[$p['estado']] ?? ['clase'=>'','icono'=>'bi-question','label'=>$p['estado']];
                            ?>
                            <div class="col-12 col-sm-6 col-md-4">
                                <div class="card h-100" style="overflow:hidden">

                                    <!-- Foto -->
                                    <div style="position:relative;aspect-ratio:3/4;overflow:hidden;background:var(--color-bg-card2)">
                                        <?php if ($imgUrl): ?>
                                        <img src="<?= e($imgUrl) ?>"
                                             alt="<?= e($p['nombre']) ?>"
                                             style="width:100%;height:100%;object-fit:cover"
                                             loading="lazy">
                                        <?php else: ?>
                                        <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:var(--color-text-muted)">
                                            <i class="bi bi-person" style="font-size:3rem"></i>
                                        </div>
                                        <?php endif; ?>

                                        <!-- Badge estado -->
                                        <span class="badge-estado <?= $info['clase'] ?>"
                                              style="position:absolute;top:8px;left:8px">
                                            <i class="bi <?= $info['icono'] ?> me-1"></i><?= $info['label'] ?>
                                        </span>

                                        <?php if (!empty($p['boost_top'])): ?>
                                        <span style="position:absolute;top:8px;right:8px;background:var(--color-primary);color:#fff;font-size:.65rem;font-weight:700;padding:3px 8px;border-radius:4px;box-shadow:0 2px 6px rgba(255,45,117,.4)">
                                            <i class="bi bi-arrow-up-square-fill me-1"></i>TOP
                                        </span>
                                        <?php elseif (!empty($p['boost_resaltado'])): ?>
                                        <span style="position:absolute;top:8px;right:8px;background:#F59E0B;color:#fff;font-size:.65rem;font-weight:700;padding:3px 8px;border-radius:4px">
                                            <i class="bi bi-stars me-1"></i>RESALT.
                                        </span>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Body compacto -->
                                    <div class="card-body p-3 d-flex flex-column">
                                        <div class="fw-bold mb-1" style="font-size:.95rem">
                                            <?= e($p['nombre']) ?>
                                            <?php if (!empty($p['edad'])): ?>
                                            <span class="fw-normal text-muted" style="font-size:.85em">, <?= (int)$p['edad'] ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-muted mb-2" style="font-size:.74rem">
                                            <i class="bi bi-tag me-1"></i><?= e($p['categoria_nombre'] ?? '—') ?>
                                            · <i class="bi bi-geo-alt me-1"></i><?= e($p['municipio_nombre'] ?? $p['ciudad'] ?? '—') ?>
                                        </div>

                                        <!-- Estado de boost (badges con horas restantes) -->
                                        <?php if ($p['estado'] === 'publicado'):
                                            $boostBadges = [];
                                            if (!empty($p['boost_top']) && !empty($p['boost_top_fin'])) {
                                                $hRestT = max(0, (int)floor((strtotime($p['boost_top_fin']) - time()) / 3600));
                                                $boostBadges[] = [
                                                    'label' => 'TOP',
                                                    'horas' => $hRestT,
                                                    'bg'    => 'rgba(255,45,117,.12)',
                                                    'color' => 'var(--color-primary)',
                                                    'icon'  => 'bi-arrow-up-square-fill',
                                                ];
                                            }
                                            if (!empty($p['boost_resaltado']) && !empty($p['boost_resaltado_fin'])) {
                                                $hRestR = max(0, (int)floor((strtotime($p['boost_resaltado_fin']) - time()) / 3600));
                                                $boostBadges[] = [
                                                    'label' => 'Resaltado',
                                                    'horas' => $hRestR,
                                                    'bg'    => 'rgba(245,158,11,.12)',
                                                    'color' => '#92400E',
                                                    'icon'  => 'bi-stars',
                                                ];
                                            }
                                        ?>
                                        <?php if (!empty($boostBadges)): ?>
                                        <div class="d-flex flex-wrap gap-1 mb-2">
                                            <?php foreach ($boostBadges as $bb): ?>
                                            <span style="background:<?= $bb['bg'] ?>;color:<?= $bb['color'] ?>;font-size:.65rem;font-weight:700;padding:.15rem .45rem;border-radius:10px;display:inline-flex;align-items:center;gap:.2rem">
                                                <i class="bi <?= $bb['icon'] ?>"></i>
                                                <?= e($bb['label']) ?>
                                                <?php if ($bb['horas'] > 0): ?>
                                                · <?= $bb['horas'] ?>h
                                                <?php endif; ?>
                                            </span>
                                            <?php endforeach; ?>
                                        </div>
                                        <?php else: ?>
                                        <div class="mb-2" style="font-size:.7rem;color:var(--color-text-muted)">
                                            <i class="bi bi-circle me-1"></i>Sin boost activo
                                        </div>
                                        <?php endif; ?>
                                        <?php endif; ?>

                                        <!-- Visitas + acciones secundarias -->
                                        <div class="d-flex align-items-center justify-content-between pt-2 mt-auto"
                                             style="border-top:1px solid var(--color-border)">
                                            <span style="font-size:.78rem;color:var(--color-primary);font-weight:600">
                                                <i class="bi bi-eye me-1"></i><?= number_format((int)$p['vistas']) ?>
                                            </span>
                                            <div class="d-flex gap-1">
                                                <?php if ($p['estado'] === 'publicado'): ?>
                                                <a href="<?= APP_URL ?>/perfil/<?= (int)$p['id'] ?>"
                                                   class="btn btn-sm btn-secondary" style="font-size:.7rem;padding:.2rem .5rem"
                                                   title="Ver" target="_blank">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <?php endif; ?>
                                                <a href="<?= APP_URL ?>/perfil/<?= (int)$p['id'] ?>/editar"
                                                   class="btn btn-sm btn-secondary" style="font-size:.7rem;padding:.2rem .5rem"
                                                   title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <form method="POST"
                                                      action="<?= APP_URL ?>/perfil/<?= (int)$p['id'] ?>/eliminar"
                                                      class="d-inline">
                                                    <?= $csrfField ?>
                                                    <button type="submit"
                                                            class="btn btn-sm btn-danger"
                                                            style="font-size:.7rem;padding:.2rem .5rem"
                                                            data-confirm="¿Eliminar el perfil «<?= e($p['nombre']) ?>»? Esta acción no se puede deshacer."
                                                            title="Eliminar">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>

                                        <!-- CTA: Destacar con tokens (prominente, solo si publicado) -->
                                        <?php if ($p['estado'] === 'publicado'): ?>
                                        <a href="<?= APP_URL ?>/perfil/<?= (int)$p['id'] ?>/destacar"
                                           class="btn btn-primary w-100 mt-2"
                                           style="font-weight:700;font-size:.82rem;padding:.5rem .75rem">
                                            <i class="bi bi-stars me-1"></i>Destacar con tokens
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ¿Por qué destacar tu perfil? — seccion educativa para convencer de comprar tokens -->
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <span class="fw-semibold">
                        <i class="bi bi-lightbulb-fill text-primary me-2"></i>¿Por qué destacar tu perfil?
                    </span>
                    <a href="<?= APP_URL ?>/tokens/comprar" class="btn btn-sm btn-primary">
                        <i class="bi bi-cart-plus me-1"></i>Recargar
                    </a>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-3">

                        <div class="col-12 col-md-4">
                            <div class="d-flex align-items-start gap-2">
                                <div style="width:40px;height:40px;border-radius:8px;background:rgba(255,45,117,.12);color:var(--color-primary);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                                    <i class="bi bi-arrow-up-square-fill" style="font-size:1.2rem"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold" style="font-size:.92rem">Sales en la cabecera de tu zona</div>
                                    <div class="text-muted" style="font-size:.78rem;line-height:1.4">
                                        TOP coloca tu perfil en las primeras posiciones de tu municipio, rotando con otras TOPs cada minuto. Más visibilidad = más contactos.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="d-flex align-items-start gap-2">
                                <div style="width:40px;height:40px;border-radius:8px;background:rgba(245,158,11,.12);color:#F59E0B;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                                    <i class="bi bi-stars" style="font-size:1.2rem"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold" style="font-size:.92rem">Resaltado para llamar la atención</div>
                                    <div class="text-muted" style="font-size:.78rem;line-height:1.4">
                                        Un fondo amarillo distintivo hace que tu tarjeta destaque entre las demás.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="d-flex align-items-start gap-2">
                                <div style="width:40px;height:40px;border-radius:8px;background:rgba(16,185,129,.12);color:#10B981;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                                    <i class="bi bi-clock-history" style="font-size:1.2rem"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold" style="font-size:.92rem">Pagas solo por las horas que TÚ eliges</div>
                                    <div class="text-muted" style="font-size:.78rem;line-height:1.4">
                                        Sin mensualidades. Activas el boost solo en tus mejores horarios (noches, findes).
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div><!-- /col-lg-8 -->

        <!-- ============================================================
             SIDEBAR — verificacion + menu + pasos
             ============================================================ -->
        <div class="col-12 col-lg-4 order-lg-1 d-flex flex-column gap-4">

            <!-- Menu rapido -->
            <div class="dash-menu">

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

                    <a href="<?= APP_URL ?>/mis-tokens" class="dash-menu__item">
                        <div class="dash-menu__icon" style="color:#10B981;background:rgba(16,185,129,.12)">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <div class="dash-menu__body">
                            <div class="dash-menu__title">Historial de tokens</div>
                            <div class="dash-menu__hint">Recargas y consumos pasados</div>
                        </div>
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

                <div class="dash-menu__section">
                    <div class="dash-menu__heading">Mi cuenta</div>

                    <a href="<?= APP_URL ?>/mi-cuenta/password" class="dash-menu__item">
                        <div class="dash-menu__icon" style="color:#6366F1;background:rgba(99,102,241,.12)">
                            <i class="bi bi-key-fill"></i>
                        </div>
                        <div class="dash-menu__body">
                            <div class="dash-menu__title">Cambiar contraseña</div>
                            <div class="dash-menu__hint">Pide tu contraseña actual antes del cambio</div>
                        </div>
                        <i class="bi bi-chevron-right dash-menu__arrow"></i>
                    </a>

                    <a href="<?= APP_URL ?>/mi-cuenta/eliminar" class="dash-menu__item">
                        <div class="dash-menu__icon" style="color:#EF4444;background:rgba(239,68,68,.12)">
                            <i class="bi bi-trash3-fill"></i>
                        </div>
                        <div class="dash-menu__body">
                            <div class="dash-menu__title">Eliminar mi cuenta</div>
                            <div class="dash-menu__hint">30 días de gracia antes del borrado definitivo</div>
                        </div>
                        <i class="bi bi-chevron-right dash-menu__arrow"></i>
                    </a>
                </div>

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
                </div>

            </div>

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
                        ['icono' => 'bi-stars',             'texto' => 'Destacarte con tokens',     'hecho' => $saldoTokens > 0],
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

            <!-- Confiabilidad detalle -->
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
