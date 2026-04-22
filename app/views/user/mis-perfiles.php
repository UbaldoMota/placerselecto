<?php
/**
 * user/mis-perfiles.php — Gestión de perfiles del usuario.
 */
$puedeCrear       = count($perfiles) < $maxPerfiles;
$totalVistas      = $totalVistas ?? 0;
$maxVistas        = $maxVistas   ?? 0;
$tieneDocumento   = !empty($usuario['documento_identidad'] ?? null);
$docEstado        = $usuario['documento_estado'] ?? null;
$documentoVerif   = $docEstado === 'verificado';
$docRechazado     = $docEstado === 'rechazado';
?>

<div class="container py-4">

    <!-- Encabezado -->
    <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h4 fw-bold mb-1">
                <i class="bi bi-person-lines-fill text-primary me-2"></i>Mis perfiles
            </h1>
            <p class="text-muted mb-0" style="font-size:.875rem">
                <?= count($perfiles) ?> / <?= $maxPerfiles ?> perfiles utilizados
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="<?= APP_URL ?>/mis-estadisticas" class="btn btn-sm btn-secondary">
                <i class="bi bi-graph-up me-1"></i>Estadísticas
            </a>
            <a href="<?= APP_URL ?>/dashboard" class="btn btn-sm btn-secondary">
                <i class="bi bi-house me-1"></i>Dashboard
            </a>
        <?php if ($puedeCrear): ?>
            <a href="<?= APP_URL ?>/perfil/nuevo" class="btn btn-sm btn-primary">
                <i class="bi bi-plus-lg me-1"></i>Nuevo perfil
            </a>
        <?php else: ?>
            <span class="btn btn-sm btn-secondary disabled" title="Límite de perfiles alcanzado">
                <i class="bi bi-plus-lg me-1"></i>Nuevo perfil
            </span>
        <?php endif; ?>
        </div>
    </div>

    <?php if (!$puedeCrear): ?>
    <div class="alert py-2 px-3 mb-4"
         style="background:rgba(255,193,7,.08);border:1px solid rgba(255,193,7,.2);font-size:.82rem;color:#ffd44d">
        <i class="bi bi-exclamation-triangle me-2"></i>
        Ya tienes el máximo de <?= $maxPerfiles ?> perfiles permitidos.
        Elimina uno para crear otro.
    </div>
    <?php endif; ?>

    <!-- Resumen de estadísticas -->
    <?php if (!empty($perfiles)): ?>
    <div class="row g-3 mb-4">
        <div class="col-6 col-sm-3">
            <div class="stat-card">
                <div class="stat-card__value"><?= count($perfiles) ?></div>
                <div class="stat-card__label">Mis perfiles</div>
                <i class="bi bi-person-lines-fill stat-card__icon"></i>
            </div>
        </div>
        <div class="col-6 col-sm-3">
            <div class="stat-card">
                <div class="stat-card__value" style="color:#10B981">
                    <?= count(array_filter($perfiles, fn($p) => $p['estado'] === 'publicado')) ?>
                </div>
                <div class="stat-card__label">Publicados</div>
                <i class="bi bi-check-circle stat-card__icon"></i>
            </div>
        </div>
        <div class="col-6 col-sm-3">
            <div class="stat-card">
                <div class="stat-card__value" style="color:var(--color-primary)"><?= number_format($totalVistas) ?></div>
                <div class="stat-card__label">Visitas totales</div>
                <i class="bi bi-graph-up stat-card__icon"></i>
            </div>
        </div>
        <div class="col-6 col-sm-3">
            <div class="stat-card">
                <div class="stat-card__value" style="color:#17a2b8"><?= number_format($maxVistas) ?></div>
                <div class="stat-card__label">Récord de visitas</div>
                <i class="bi bi-trophy stat-card__icon"></i>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Sugerencia / estado de verificación de identidad -->
    <?php if ($docRechazado): ?>
    <div class="mb-4 p-3 rounded d-flex align-items-start gap-3"
         style="background:rgba(220,53,69,.06);border:1px solid rgba(220,53,69,.25)">
        <i class="bi bi-x-circle-fill" style="font-size:1.4rem;color:#dc3545;flex-shrink:0;margin-top:.1rem"></i>
        <div class="flex-fill">
            <div class="fw-semibold mb-1" style="font-size:.88rem;color:#dc3545">
                Tu documento de identidad fue rechazado
            </div>
            <div class="text-muted mb-2" style="font-size:.8rem">
                <?= e($usuario['documento_rechazo_motivo'] ?? 'Revisa los detalles y vuelve a subir tu documento.') ?>
            </div>
            <a href="<?= APP_URL ?>/mi-cuenta/documento" class="btn btn-sm"
               style="background:rgba(220,53,69,.1);border:1px solid rgba(220,53,69,.3);color:#dc3545;font-size:.78rem">
                <i class="bi bi-upload me-1"></i>Volver a subir
            </a>
        </div>
    </div>
    <?php elseif (!$tieneDocumento): ?>
    <div class="mb-4 p-3 rounded d-flex align-items-start gap-3"
         style="background:rgba(255,193,7,.06);border:1px solid rgba(255,193,7,.2)">
        <i class="bi bi-card-checklist" style="font-size:1.5rem;color:#F59E0B;flex-shrink:0;margin-top:.1rem"></i>
        <div class="flex-fill">
            <div class="fw-semibold mb-1" style="font-size:.88rem;color:#ffd44d">
                Verifica tu identidad para aumentar la confianza en tu perfil
            </div>
            <div class="text-muted mb-2" style="font-size:.8rem">
                Sube una foto de tu INE o pasaporte. Es opcional, privado y solo lo revisa el equipo de moderación.
                Aparece el sello <strong>"Identidad verificada"</strong> en tu perfil.
            </div>
            <a href="<?= APP_URL ?>/mi-cuenta/documento" class="btn btn-sm"
               style="background:rgba(255,193,7,.12);border:1px solid rgba(255,193,7,.35);color:#ffd44d;font-size:.78rem">
                <i class="bi bi-upload me-1"></i>Subir documento
            </a>
        </div>
        <a href="<?= APP_URL ?>/mi-cuenta/documento"
           style="color:var(--color-text-muted);font-size:.75rem;white-space:nowrap;flex-shrink:0;align-self:center">
            Ver más
        </a>
    </div>
    <?php elseif ($tieneDocumento && !$documentoVerif): ?>
    <div class="mb-4 p-3 rounded d-flex align-items-center gap-3"
         style="background:rgba(16,185,129,.05);border:1px solid rgba(16,185,129,.15)">
        <i class="bi bi-clock-history" style="font-size:1.3rem;color:#10B981;flex-shrink:0"></i>
        <div style="font-size:.82rem">
            <span class="fw-semibold" style="color:#10B981">Documento en revisión</span>
            <span class="text-muted ms-2">— El equipo revisará tu documento pronto.</span>
        </div>
        <a href="<?= APP_URL ?>/mi-cuenta/documento" class="btn btn-sm ms-auto"
           style="background:rgba(16,185,129,.08);border:1px solid rgba(16,185,129,.2);color:#10B981;font-size:.75rem;white-space:nowrap">
            <i class="bi bi-eye me-1"></i>Ver estado
        </a>
    </div>
    <?php endif; ?>

    <?php if (empty($perfiles)): ?>
    <!-- Estado vacío -->
    <div class="text-center py-5">
        <i class="bi bi-person-x" style="font-size:4rem;color:var(--color-border)"></i>
        <h3 class="h5 mt-3 mb-2">Aún no tienes perfiles</h3>
        <p class="text-muted mb-4">Crea tu primer perfil para aparecer en los resultados.</p>
        <a href="<?= APP_URL ?>/perfil/nuevo" class="btn btn-primary btn-lg">
            <i class="bi bi-plus-lg me-2"></i>Crear perfil
        </a>
    </div>

    <?php else: ?>

    <div class="row g-4">
        <?php foreach ($perfiles as $p):
            $imgUrl = !empty($p['imagen_token'])
                ? APP_URL . '/img/' . $p['imagen_token']
                : null;

            $estadoMap = [
                'pendiente' => ['clase' => 'badge-pendiente',  'icono' => 'bi-clock',        'label' => 'En revisión'],
                'publicado' => ['clase' => 'badge-publicado',  'icono' => 'bi-check-circle',  'label' => 'Publicado'],
                'rechazado' => ['clase' => 'badge-rechazado',  'icono' => 'bi-x-circle',      'label' => 'Rechazado'],
            ];
            $info = $estadoMap[$p['estado']] ?? ['clase'=>'','icono'=>'bi-question','label'=>$p['estado']];
        ?>
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card h-100">

                <!-- Foto -->
                <div style="position:relative;aspect-ratio:3/4;overflow:hidden;background:var(--color-bg-card2);border-radius:var(--radius) var(--radius) 0 0">
                    <?php if ($imgUrl): ?>
                    <img src="<?= e($imgUrl) ?>"
                         alt="<?= e($p['nombre']) ?>"
                         style="width:100%;height:100%;object-fit:cover">
                    <?php else: ?>
                    <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:var(--color-text-muted)">
                        <i class="bi bi-person" style="font-size:4rem"></i>
                    </div>
                    <?php endif; ?>

                    <!-- Badge estado -->
                    <span class="badge-estado <?= $info['clase'] ?>"
                          style="position:absolute;top:8px;left:8px">
                        <i class="bi <?= $info['icono'] ?> me-1"></i><?= $info['label'] ?>
                    </span>

                    <?php if (!empty($p['boost_top'])): ?>
                    <span style="position:absolute;top:8px;right:8px;background:var(--color-primary);color:#fff;font-size:.65rem;font-weight:700;padding:3px 8px;border-radius:4px;box-shadow:var(--shadow-pink)">
                        <i class="bi bi-arrow-up-square-fill me-1"></i>TOP
                    </span>
                    <?php elseif (!empty($p['boost_resaltado'])): ?>
                    <span style="position:absolute;top:8px;right:8px;background:var(--color-warning);color:#fff;font-size:.65rem;font-weight:700;padding:3px 8px;border-radius:4px">
                        <i class="bi bi-stars me-1"></i>RESALT.
                    </span>
                    <?php endif; ?>
                </div>

                <div class="card-body d-flex flex-column">
                    <h3 class="h5 fw-bold mb-1">
                        <?= e($p['nombre']) ?>
                        <?php if (!empty($p['edad'])): ?>
                        <span class="fw-normal text-muted" style="font-size:.8em">, <?= (int)$p['edad'] ?></span>
                        <?php endif; ?>
                    </h3>
                    <p class="text-muted mb-1" style="font-size:.8rem">
                        <i class="bi bi-tag text-primary me-1"></i><?= e($p['categoria_nombre'] ?? '—') ?>
                    </p>
                    <p class="text-muted mb-2" style="font-size:.8rem">
                        <i class="bi bi-geo-alt text-primary me-1"></i>
                        <?= e($p['municipio_nombre'] ?? $p['ciudad'] ?? '—') ?>
                    </p>

                    <!-- Estadística de visitas -->
                    <?php
                    $vistas = (int)$p['vistas'];
                    $pct    = $maxVistas > 0 ? round($vistas / $maxVistas * 100) : 0;
                    ?>
                    <div class="mb-3 p-2 rounded" style="background:rgba(255,45,117,.06);border:1px solid rgba(255,45,117,.12)">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span style="font-size:.75rem;color:var(--color-text-muted)">
                                <i class="bi bi-eye me-1"></i>Visitas
                            </span>
                            <span class="fw-bold" style="font-size:.95rem;color:var(--color-primary)">
                                <?= number_format($vistas) ?>
                            </span>
                        </div>
                        <div style="background:rgba(0,0,0,.06);border-radius:4px;height:4px;overflow:hidden">
                            <div style="width:<?= $pct ?>%;height:100%;background:var(--color-primary);border-radius:4px;transition:width .3s"></div>
                        </div>
                        <?php if ($p['estado'] === 'publicado'): ?>
                        <div class="text-muted mt-1" style="font-size:.7rem">
                            <?= $pct ?>% del total de tus visitas
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Estado de verificación (fotos + video — ambos obligatorios) -->
                    <?php
                    $tieneFotosVer = isset($p['fotos_ver']) ? (int)$p['fotos_ver'] > 0 : false;
                    $tieneVideoVer = !empty($p['video_verificacion']);
                    $ambosListos   = $tieneFotosVer && $tieneVideoVer;
                    ?>

                    <?php if ($p['estado'] === 'publicado'): ?>
                    <div class="perfil-status perfil-status--success mb-3">
                        <div class="perfil-status__icon"><i class="bi bi-check-circle-fill"></i></div>
                        <div class="perfil-status__body">
                            <div class="perfil-status__title">Perfil publicado</div>
                            <div class="perfil-status__sub">Visible para el público en el directorio</div>
                        </div>
                    </div>
                    <?php elseif ($p['estado'] === 'rechazado'): ?>
                    <div class="perfil-status perfil-status--danger mb-3">
                        <div class="perfil-status__icon"><i class="bi bi-x-octagon-fill"></i></div>
                        <div class="perfil-status__body">
                            <div class="perfil-status__title">Perfil rechazado</div>
                            <div class="perfil-status__sub">Edítalo y corrígelo para re-enviarlo a revisión</div>
                        </div>
                    </div>
                    <?php elseif ($p['estado'] === 'pendiente' && $ambosListos): ?>
                    <div class="perfil-status perfil-status--warning mb-3">
                        <div class="perfil-status__icon"><i class="bi bi-clock-history"></i></div>
                        <div class="perfil-status__body">
                            <div class="perfil-status__title">En espera de aprobación</div>
                            <div class="perfil-status__sub">El equipo revisa tu perfil · suele tardar menos de 24 h</div>
                        </div>
                    </div>
                    <?php elseif ($p['estado'] === 'pendiente' && !$ambosListos): ?>
                    <div class="perfil-status perfil-status--action mb-3">
                        <div class="perfil-status__icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
                        <div class="perfil-status__body">
                            <div class="perfil-status__title">Acción requerida</div>
                            <div class="perfil-status__sub">Completa los 2 pasos de verificación para solicitar aprobación</div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="mb-3 p-2 rounded" style="background:rgba(0,0,0,.03);border:1px solid <?= $ambosListos ? 'rgba(16,185,129,.3)' : 'var(--color-border)' ?>">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span style="font-size:.72rem;color:var(--color-text-muted);font-weight:600;text-transform:uppercase;letter-spacing:.04em">
                                Verificación
                            </span>
                            <?php if ($ambosListos): ?>
                            <span style="font-size:.7rem;color:#10B981;font-weight:600">
                                <i class="bi bi-check-circle-fill me-1"></i>Completa
                            </span>
                            <?php else: ?>
                            <span style="font-size:.7rem;color:var(--color-warning);font-weight:600">
                                <i class="bi bi-exclamation-circle me-1"></i><?= ($tieneFotosVer || $tieneVideoVer) ? '1/2' : '0/2' ?> pasos
                            </span>
                            <?php endif; ?>
                        </div>
                        <!-- Paso 1: fotos -->
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span style="font-size:.72rem;color:<?= $tieneFotosVer ? '#10B981' : 'var(--color-text-muted)' ?>">
                                <i class="bi bi-<?= $tieneFotosVer ? 'check-circle-fill' : 'circle' ?> me-1"></i>
                                Fotos de verificación
                            </span>
                            <?php if (!$tieneFotosVer): ?>
                            <a href="<?= APP_URL ?>/perfil/<?= (int)$p['id'] ?>/verificar/fotos"
                               style="font-size:.68rem;color:var(--color-primary)">Subir</a>
                            <?php endif; ?>
                        </div>
                        <!-- Paso 2: video -->
                        <div class="d-flex align-items-center justify-content-between">
                            <span style="font-size:.72rem;color:<?= $tieneVideoVer ? '#10B981' : 'var(--color-text-muted)' ?>">
                                <i class="bi bi-<?= $tieneVideoVer ? 'check-circle-fill' : 'circle' ?> me-1"></i>
                                Video de verificación
                            </span>
                            <?php if (!$tieneVideoVer): ?>
                            <a href="<?= APP_URL ?>/perfil/<?= (int)$p['id'] ?>/verificar/camara"
                               style="font-size:.68rem;color:var(--color-primary)">Grabar</a>
                            <?php endif; ?>
                        </div>
                        <?php if (!$ambosListos): ?>
                        <a href="<?= APP_URL ?>/perfil/<?= (int)$p['id'] ?>/verificar"
                           class="btn btn-sm w-100 mt-2"
                           style="font-size:.72rem;background:rgba(255,45,117,.1);border:1px solid rgba(255,45,117,.3);color:var(--color-primary)">
                            <i class="bi bi-shield-check me-1"></i>Ver verificación
                        </a>
                        <?php endif; ?>
                    </div>

                    <!-- Acciones -->
                    <div class="d-flex gap-2 flex-wrap mt-auto">
                        <?php if ($p['estado'] === 'publicado'): ?>
                        <a href="<?= APP_URL ?>/perfil/<?= (int)$p['id'] ?>"
                           class="btn btn-sm btn-secondary"
                           target="_blank"
                           title="Ver público">
                            <i class="bi bi-eye"></i>
                        </a>
                        <?php endif; ?>

                        <a href="<?= APP_URL ?>/perfil/<?= (int)$p['id'] ?>/editar"
                           class="btn btn-sm btn-secondary flex-fill"
                           title="Editar">
                            <i class="bi bi-pencil me-1"></i>Editar
                        </a>

                        <?php if ($p['estado'] === 'publicado'): ?>
                        <a href="<?= APP_URL ?>/perfil/<?= (int)$p['id'] ?>/destacar"
                           class="btn btn-sm btn-primary"
                           title="Destacar con tokens">
                            <i class="bi bi-stars me-1"></i>Destacar
                        </a>
                        <?php endif; ?>

                        <form method="POST"
                              action="<?= APP_URL ?>/perfil/<?= (int)$p['id'] ?>/eliminar"
                              class="d-inline">
                            <?= $csrfField ?>
                            <button type="submit"
                                    class="btn btn-sm btn-danger"
                                    data-confirm="¿Eliminar el perfil «<?= e($p['nombre']) ?>»? Esta acción no se puede deshacer."
                                    title="Eliminar">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php endif; ?>
</div>
