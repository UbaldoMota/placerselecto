<?php
/**
 * admin/perfil-preview.php — Vista previa de un perfil para el administrador.
 * Permite revisar fotos, descripción y tomar acción (publicar / rechazar).
 *
 * Variables: $perfil, $fotosGaleria (todas, incluyendo ocultas), $fotosVer, $confiabilidad
 */

$estadoMap = [
    'pendiente' => ['badge-pendiente', 'En revisión'],
    'publicado' => ['badge-publicado', 'Publicado'],
    'rechazado' => ['badge-rechazado', 'Rechazado'],
];
[$estadoCls, $estadoLbl] = $estadoMap[$perfil['estado']] ?? ['', $perfil['estado']];

// URLs para el lightbox — solo fotos de galería visibles (no ocultas)
$lightboxUrls = [];
$lightboxMap  = []; // foto id => lightbox index
foreach ($fotosGaleria as $f) {
    if (!(bool)$f['oculta']) {
        $lightboxMap[$f['id']] = count($lightboxUrls);
        $lightboxUrls[]        = APP_URL . '/img/' . $f['token'];
    }
}
// Fallback si no hay fotos de galería visibles
if (empty($lightboxUrls) && !empty($perfil['imagen_token'])) {
    $lightboxUrls = [APP_URL . '/img/' . $perfil['imagen_token']];
}

$tieneFotosVer = count($fotosVer) > 0;
$tieneVideoVer = !empty($perfil['video_verificacion']);
$ambosListos   = $tieneFotosVer && $tieneVideoVer;
?>

<div class="container-fluid px-4 py-4" style="max-width:1100px">

    <!-- Encabezado admin -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <a href="<?= APP_URL ?>/admin/perfiles" class="btn btn-sm btn-secondary mb-2">
                <i class="bi bi-arrow-left me-1"></i>Volver al listado
            </a>
            <h1 class="h4 fw-bold mb-1">
                <i class="bi bi-eye text-primary me-2"></i>Previsualizar perfil
            </h1>
            <p class="text-muted mb-0" style="font-size:.85rem">
                Usuario:
                <a href="<?= APP_URL ?>/admin/usuario/<?= (int)$perfil['id_usuario'] ?>"
                   style="color:var(--color-primary)">
                    <?= e($perfil['usuario_nombre'] ?? '—') ?>
                </a>
                &nbsp;·&nbsp;
                <span class="badge-estado <?= $estadoCls ?>"><?= $estadoLbl ?></span>
            </p>
        </div>

        <!-- Botones de acción -->
        <div class="d-flex gap-2 flex-wrap">
            <?php if ($perfil['estado'] !== 'publicado'): ?>
            <form method="POST" action="<?= APP_URL ?>/admin/perfil/<?= (int)$perfil['id'] ?>/publicar">
                <?= $csrfField ?>
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-check-lg me-1"></i>Publicar perfil
                </button>
            </form>
            <?php endif; ?>

            <?php if ($perfil['estado'] !== 'rechazado'): ?>
            <form method="POST" action="<?= APP_URL ?>/admin/perfil/<?= (int)$perfil['id'] ?>/rechazar">
                <?= $csrfField ?>
                <button type="submit" class="btn btn-danger"
                        data-confirm="¿Rechazar el perfil «<?= e($perfil['nombre']) ?>»?">
                    <i class="bi bi-x-lg me-1"></i>Rechazar perfil
                </button>
            </form>
            <?php endif; ?>

            <form method="POST" action="<?= APP_URL ?>/admin/perfil/<?= (int)$perfil['id'] ?>/eliminar">
                <?= $csrfField ?>
                <button type="submit" class="btn btn-secondary"
                        data-confirm="¿Eliminar permanentemente el perfil «<?= e($perfil['nombre']) ?>»?">
                    <i class="bi bi-trash me-1"></i>Eliminar
                </button>
            </form>
        </div>
    </div>

    <div class="row g-4">

        <!-- COLUMNA PRINCIPAL -->
        <div class="col-12 col-lg-8">
            <div class="card">

                <!-- Galería (admin: tamaño natural + botones ocultar/eliminar) -->
                <?php if (!empty($fotosGaleria)):
                    $totalGaleria = count($fotosGaleria);
                ?>
                <?php if ($totalGaleria === 1):
                    $f      = $fotosGaleria[0];
                    $oculta = (bool)$f['oculta'];
                    $lbIdx  = $lightboxMap[$f['id']] ?? null;
                ?>
                <div style="position:relative<?= $oculta ? ';opacity:.55' : '' ?>">
                    <div class="foto-galeria--1">
                        <img src="<?= APP_URL . '/img/' . e($f['token']) ?>"
                             alt="<?= e($perfil['nombre']) ?>"
                             loading="eager"
                             <?= $lbIdx !== null ? 'data-lightbox-open="' . $lbIdx . '" style="cursor:zoom-in"' : '' ?>>
                    </div>
                    <?php if ($oculta): ?>
                    <div style="position:absolute;top:10px;left:10px;background:rgba(220,53,69,.9);color:#fff;font-size:.72rem;font-weight:700;padding:3px 8px;border-radius:5px">
                        <i class="bi bi-eye-slash me-1"></i>Oculta
                    </div>
                    <?php endif; ?>
                    <div style="position:absolute;bottom:10px;right:10px;display:flex;gap:6px">
                        <form method="POST" action="<?= APP_URL ?>/admin/foto/<?= (int)$f['id'] ?>/ocultar" style="margin:0">
                            <?= $csrfField ?>
                            <button type="submit" class="btn btn-sm" title="<?= $oculta ? 'Mostrar' : 'Ocultar' ?>"
                                    style="background:rgba(0,0,0,.55);border:1px solid rgba(255,255,255,.3);color:#fff;backdrop-filter:blur(4px)">
                                <i class="bi bi-eye<?= $oculta ? '' : '-slash' ?>"></i>
                                <?= $oculta ? 'Mostrar' : 'Ocultar' ?>
                            </button>
                        </form>
                        <form method="POST" action="<?= APP_URL ?>/admin/foto/<?= (int)$f['id'] ?>/eliminar" style="margin:0">
                            <?= $csrfField ?>
                            <button type="submit" class="btn btn-sm btn-danger"
                                    data-confirm="¿Eliminar esta foto permanentemente?"
                                    title="Eliminar">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
                <?php else: ?>
                <div class="foto-galeria--multi">
                    <?php foreach ($fotosGaleria as $f):
                        $oculta = (bool)$f['oculta'];
                        $lbIdx  = $lightboxMap[$f['id']] ?? null;
                    ?>
                    <div class="foto-galeria__item"
                         style="position:relative<?= $oculta ? ';opacity:.5' : '' ?>"
                         <?= $lbIdx !== null ? 'data-lightbox-open="' . $lbIdx . '"' : '' ?>>
                        <img src="<?= APP_URL . '/img/' . e($f['token']) ?>"
                             alt="Foto galería"
                             loading="lazy">

                        <?php if ($oculta): ?>
                        <div style="position:absolute;top:5px;left:5px;background:rgba(220,53,69,.9);color:#fff;font-size:.6rem;font-weight:700;padding:2px 5px;border-radius:4px;pointer-events:none">
                            <i class="bi bi-eye-slash"></i>
                        </div>
                        <?php endif; ?>

                        <div style="position:absolute;bottom:0;left:0;right:0;display:flex;gap:4px;justify-content:center;padding:6px 4px;background:linear-gradient(transparent,rgba(0,0,0,.75))"
                             data-stop-propagation>
                            <form method="POST" action="<?= APP_URL ?>/admin/foto/<?= (int)$f['id'] ?>/ocultar" style="margin:0">
                                <?= $csrfField ?>
                                <button type="submit"
                                        title="<?= $oculta ? 'Mostrar' : 'Ocultar' ?>"
                                        style="all:unset;cursor:pointer;background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.35);color:#fff;border-radius:4px;padding:3px 9px;font-size:.75rem">
                                    <i class="bi bi-eye<?= $oculta ? '' : '-slash' ?>"></i>
                                </button>
                            </form>
                            <form method="POST" action="<?= APP_URL ?>/admin/foto/<?= (int)$f['id'] ?>/eliminar" style="margin:0">
                                <?= $csrfField ?>
                                <button type="submit"
                                        data-confirm="¿Eliminar esta foto permanentemente?"
                                        style="all:unset;cursor:pointer;background:rgba(220,53,69,.75);border:1px solid rgba(220,53,69,.9);color:#fff;border-radius:4px;padding:3px 9px;font-size:.75rem">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                <?php else: ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-image" style="font-size:3rem;opacity:.3"></i>
                    <p class="mt-2 mb-0" style="font-size:.85rem">Sin fotos de galería</p>
                </div>
                <?php endif; ?>

                <!-- Lightbox -->
                <div id="lightbox" class="lightbox">
                    <button class="lightbox__close" data-lightbox="close" title="Cerrar">&times;</button>
                    <button class="lightbox__nav lightbox__prev" data-lightbox-nav="-1" title="Anterior">&#8249;</button>
                    <img id="lightbox-img" class="lightbox__img" src="" alt="">
                    <button class="lightbox__nav lightbox__next" data-lightbox-nav="1" title="Siguiente">&#8250;</button>
                    <div class="lightbox__counter" id="lightbox-counter"></div>
                </div>
                <script id="lightbox-data" type="application/json"><?= json_encode(array_values($lightboxUrls)) ?></script>
                <script src="<?= APP_URL ?>/public/assets/js/lightbox.js" defer></script>

                <div class="card-body p-4">
                    <!-- Meta -->
                    <div class="d-flex flex-wrap gap-3 mb-3" style="font-size:.8rem">
                        <span class="text-muted">
                            <i class="bi bi-tag text-primary me-1"></i><?= e($perfil['categoria_nombre'] ?? '—') ?>
                        </span>
                        <span class="text-muted">
                            <i class="bi bi-geo-alt text-primary me-1"></i>
                            <?php
                            $loc = array_filter([$perfil['municipio_nombre'] ?? null, $perfil['estado_nombre'] ?? null]);
                            echo $loc ? e(implode(', ', $loc)) : e($perfil['ciudad'] ?? '—');
                            ?>
                        </span>
                        <?php if (!empty($perfil['edad'])): ?>
                        <span class="text-muted">
                            <i class="bi bi-person text-primary me-1"></i><?= (int)$perfil['edad'] ?> años
                        </span>
                        <?php endif; ?>
                        <?php if (!empty($perfil['whatsapp'])): ?>
                        <span class="text-muted">
                            <i class="bi bi-whatsapp text-success me-1"></i><?= e($perfil['whatsapp']) ?>
                        </span>
                        <?php endif; ?>
                    </div>

                    <h2 class="h4 fw-bold mb-3"><?= e($perfil['nombre']) ?></h2>

                    <div class="perfil-descripcion">
                        <?php
                        $desc = $perfil['descripcion'] ?? '';
                        if (strip_tags($desc) === $desc) {
                            echo nl2br(e($desc));
                        } else {
                            echo $desc;
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- SIDEBAR -->
        <div class="col-12 col-lg-4">

            <!-- Info del usuario -->
            <div class="card mb-4">
                <div class="card-header">
                    <span class="fw-semibold" style="font-size:.875rem">
                        <i class="bi bi-person-circle text-primary me-2"></i>Datos del usuario
                    </span>
                </div>
                <div class="card-body" style="font-size:.83rem">
                    <div class="d-flex justify-content-between py-1" style="border-bottom:1px solid var(--color-border)">
                        <span class="text-muted">Nombre</span>
                        <span><?= e($perfil['usuario_nombre'] ?? '—') ?></span>
                    </div>
                    <div class="d-flex justify-content-between py-1" style="border-bottom:1px solid var(--color-border)">
                        <span class="text-muted">Verificado</span>
                        <span>
                            <?php if ($perfil['verificado']): ?>
                            <i class="bi bi-check-circle-fill text-success"></i> Sí
                            <?php else: ?>
                            <i class="bi bi-x-circle text-danger"></i> No
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="d-flex justify-content-between py-1" style="border-bottom:1px solid var(--color-border)">
                        <span class="text-muted">Fotos galería</span>
                        <span>
                            <?= count($fotosGaleria) ?>
                            <?php $ocultas = count(array_filter($fotosGaleria, fn($f) => (bool)$f['oculta'])); ?>
                            <?php if ($ocultas > 0): ?>
                            <span style="font-size:.72rem;color:#dc3545">(<?= $ocultas ?> oculta<?= $ocultas > 1 ? 's' : '' ?>)</span>
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="d-flex justify-content-between py-1" style="border-bottom:1px solid var(--color-border)">
                        <span class="text-muted">Estado perfil</span>
                        <span class="badge-estado <?= $estadoCls ?>"><?= $estadoLbl ?></span>
                    </div>
                    <div class="d-flex justify-content-between py-1" style="border-bottom:1px solid var(--color-border)">
                        <span class="text-muted">Fotos verif.</span>
                        <span style="font-size:.82rem">
                            <?= $tieneFotosVer
                                ? '<i class="bi bi-check-circle-fill text-success me-1"></i>' . count($fotosVer) . ' foto(s)'
                                : '<i class="bi bi-x-circle text-danger me-1"></i>Pendiente' ?>
                        </span>
                    </div>
                    <div class="d-flex justify-content-between py-1">
                        <span class="text-muted">Video verif.</span>
                        <span style="font-size:.82rem">
                            <?= $tieneVideoVer
                                ? '<i class="bi bi-check-circle-fill text-success me-1"></i>Enviado'
                                : '<i class="bi bi-x-circle text-danger me-1"></i>Pendiente' ?>
                        </span>
                    </div>
                </div>
                <?php if (!$ambosListos): ?>
                <div class="px-3 py-2"
                     style="background:rgba(255,193,7,.07);border-top:1px solid rgba(255,193,7,.2);font-size:.78rem;color:#c8a000">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    Verificación incompleta — faltan: <?= !$tieneFotosVer ? 'fotos' : '' ?><?= !$tieneFotosVer && !$tieneVideoVer ? ' y ' : '' ?><?= !$tieneVideoVer ? 'video' : '' ?>.
                </div>
                <?php endif; ?>
            </div>

            <!-- Video de verificación del perfil -->
            <?php if (!empty($perfil['video_verificacion'])): ?>
            <div class="card mb-4">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <span class="fw-semibold" style="font-size:.875rem">
                        <i class="bi bi-camera-video text-primary me-2"></i>Video de verificación
                    </span>
                    <span class="badge-estado badge-publicado" style="font-size:.7rem">
                        <i class="bi bi-check-circle me-1"></i>Enviado
                    </span>
                </div>
                <div class="card-body">
                    <video controls
                           src="<?= APP_URL ?>/admin/perfil/<?= (int)$perfil['id'] ?>/video"
                           style="width:100%;border-radius:8px;background:#000;max-height:220px"
                           preload="metadata">
                        Tu navegador no soporta la reproducción de video.
                    </video>
                    <?php if (!empty($perfil['video_verificacion_at'])): ?>
                    <p class="text-muted mt-2 mb-0" style="font-size:.75rem">
                        <i class="bi bi-clock me-1"></i>
                        Enviado: <?= e(date('d/m/Y H:i', strtotime($perfil['video_verificacion_at']))) ?>
                    </p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Videos del perfil (moderación) -->
            <?php if (!empty($videos)): ?>
            <div class="card mb-4">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <span class="fw-semibold" style="font-size:.875rem">
                        <i class="bi bi-play-btn-fill text-primary me-2"></i>Videos del perfil (<?= count($videos) ?>)
                    </span>
                    <?php $pend = count(array_filter($videos, fn($v) => $v['estado'] === 'pendiente')); ?>
                    <?php if ($pend > 0): ?>
                    <span class="badge-estado badge-pendiente"><?= $pend ?> pendiente<?= $pend > 1 ? 's' : '' ?></span>
                    <?php endif; ?>
                </div>
                <div class="card-body d-flex flex-column gap-3">
                    <?php foreach ($videos as $v):
                        $eMap = ['pendiente'=>'badge-pendiente','publicado'=>'badge-publicado','rechazado'=>'badge-rechazado'];
                        $eCls = $eMap[$v['estado']] ?? '';
                    ?>
                    <div class="admin-video-row">
                        <div class="admin-video-row__player">
                            <video src="<?= APP_URL . '/video/' . e($v['token']) ?>"
                                   style="width:100%;height:100%;object-fit:contain;background:#000;display:block"
                                   controls preload="metadata" playsinline></video>
                        </div>
                        <div class="admin-video-row__body">
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-2">
                                <span class="badge-estado <?= e($eCls) ?>"><?= e(ucfirst($v['estado'])) ?></span>
                                <small class="text-muted" style="font-size:.75rem">
                                    <?= !empty($v['tamano_bytes']) ? number_format((int)$v['tamano_bytes'] / 1048576, 1) . ' MB' : '' ?>
                                    · subido <?= e(Security::timeAgo($v['created_at'])) ?>
                                </small>
                            </div>
                            <?php if ($v['estado'] === 'rechazado' && !empty($v['motivo_rechazo'])): ?>
                            <div class="mb-2 p-2 rounded" style="background:rgba(239,68,68,.06);border:1px solid rgba(239,68,68,.2);font-size:.78rem;color:#991B1B">
                                <i class="bi bi-info-circle me-1"></i><?= e($v['motivo_rechazo']) ?>
                            </div>
                            <?php endif; ?>
                            <div class="d-flex gap-2 flex-wrap">
                                <?php if ($v['estado'] !== 'publicado'): ?>
                                <form method="POST" action="<?= APP_URL ?>/admin/video/<?= (int)$v['id'] ?>/publicar" class="m-0">
                                    <?= $csrfField ?>
                                    <button type="submit" class="btn btn-sm btn-primary">
                                        <i class="bi bi-check-lg me-1"></i>Aprobar
                                    </button>
                                </form>
                                <?php endif; ?>
                                <?php if ($v['estado'] !== 'rechazado'): ?>
                                <button type="button" class="btn btn-sm btn-secondary"
                                        data-bs-toggle="collapse" data-bs-target="#rv-<?= (int)$v['id'] ?>">
                                    <i class="bi bi-x-circle me-1"></i>Rechazar
                                </button>
                                <?php endif; ?>
                                <form method="POST" action="<?= APP_URL ?>/admin/video/<?= (int)$v['id'] ?>/eliminar" class="m-0 ms-auto"
                                      data-confirm-submit="¿Eliminar este video permanentemente?">
                                    <?= $csrfField ?>
                                    <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                            <?php if ($v['estado'] !== 'rechazado'): ?>
                            <div class="collapse mt-2" id="rv-<?= (int)$v['id'] ?>">
                                <form method="POST" action="<?= APP_URL ?>/admin/video/<?= (int)$v['id'] ?>/rechazar"
                                      class="p-2 rounded" style="background:rgba(239,68,68,.05);border:1px solid rgba(239,68,68,.2)">
                                    <?= $csrfField ?>
                                    <textarea name="motivo" class="form-control form-control-sm mb-2"
                                              rows="2" maxlength="250" placeholder="Motivo (opcional)"></textarea>
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="bi bi-x-circle me-1"></i>Confirmar rechazo
                                    </button>
                                </form>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Fotos de verificación del perfil -->
            <div class="card mb-4">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <span class="fw-semibold" style="font-size:.875rem">
                        <i class="bi bi-images text-primary me-2"></i>Fotos de verificación
                    </span>
                    <?php if (!empty($fotosVer)): ?>
                    <span class="badge-estado badge-publicado" style="font-size:.7rem">
                        <i class="bi bi-check-circle me-1"></i><?= count($fotosVer) ?> foto(s)
                    </span>
                    <?php else: ?>
                    <span class="badge-estado badge-pendiente" style="font-size:.7rem">
                        <i class="bi bi-clock me-1"></i>Pendiente
                    </span>
                    <?php endif; ?>
                </div>
                <div class="card-body" style="font-size:.83rem">
                    <?php if (!empty($fotosVer)): ?>
                        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(90px,1fr));gap:.5rem">
                            <?php foreach ($fotosVer as $fv): ?>
                            <a href="<?= APP_URL ?>/img/<?= e($fv['token']) ?>" target="_blank" rel="noopener">
                                <img src="<?= APP_URL ?>/img/<?= e($fv['token']) ?>"
                                     style="width:100%;aspect-ratio:1;object-fit:cover;border-radius:6px;border:1px solid var(--color-border)"
                                     loading="lazy">
                            </a>
                            <?php endforeach; ?>
                        </div>
                        <p class="text-muted mt-2 mb-0" style="font-size:.74rem">
                            <i class="bi bi-info-circle me-1"></i>Fotos sin filtros ni rostro cubierto enviadas por el usuario.
                        </p>
                    <?php else: ?>
                        <div class="text-center py-3 text-muted">
                            <i class="bi bi-images" style="font-size:2rem;opacity:.4"></i>
                            <p class="mt-2 mb-0" style="font-size:.82rem">
                                El usuario aún no ha enviado fotos de verificación.
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Confiabilidad -->
            <?php
            $score = $confiabilidad['score'];
            $total = $confiabilidad['total'];
            $pct   = $total > 0 ? round($score / $total * 100) : 0;
            $colorScore = $pct >= 75 ? '#10B981' : ($pct >= 40 ? '#F59E0B' : '#FF2D75');
            ?>
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="fw-semibold" style="font-size:.875rem">
                        <i class="bi bi-patch-check text-primary me-2"></i>Confiabilidad
                    </span>
                    <span class="fw-bold" style="color:<?= $colorScore ?>"><?= $score ?>/<?= $total ?></span>
                </div>
                <div class="card-body" style="padding:.75rem 1rem">
                    <div style="background:var(--color-bg-card2);border-radius:20px;height:5px;margin-bottom:.85rem;overflow:hidden">
                        <div style="width:<?= $pct ?>%;height:100%;background:<?= $colorScore ?>;border-radius:20px"></div>
                    </div>
                    <ul class="list-unstyled mb-0" style="display:flex;flex-direction:column;gap:.45rem">
                    <?php foreach ($confiabilidad['indicadores'] as $ind): ?>
                        <li class="d-flex align-items-center gap-2" style="font-size:.78rem"
                            title="<?= e($ind['descripcion']) ?>">
                            <i class="bi <?= e($ind['icono']) ?>"
                               style="color:<?= $ind['activo'] ? '#10B981' : 'var(--color-text-muted)' ?>;opacity:<?= $ind['activo'] ? '1' : '.35' ?>;flex-shrink:0"></i>
                            <span style="color:<?= $ind['activo'] ? 'var(--color-text)' : 'var(--color-text-muted)' ?>">
                                <?= e($ind['label']) ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <!-- Acciones rápidas (repite los botones principales) -->
            <div class="card">
                <div class="card-body d-flex flex-column gap-2">
                    <?php if ($perfil['estado'] !== 'publicado'): ?>
                    <form method="POST" action="<?= APP_URL ?>/admin/perfil/<?= (int)$perfil['id'] ?>/publicar">
                        <?= $csrfField ?>
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-check-lg me-2"></i>Publicar perfil
                        </button>
                    </form>
                    <?php endif; ?>

                    <?php if ($perfil['estado'] !== 'rechazado'): ?>
                    <form method="POST" action="<?= APP_URL ?>/admin/perfil/<?= (int)$perfil['id'] ?>/rechazar">
                        <?= $csrfField ?>
                        <button type="submit" class="btn btn-danger w-100"
                                data-confirm="¿Rechazar «<?= e($perfil['nombre']) ?>»?">
                            <i class="bi bi-x-lg me-2"></i>Rechazar perfil
                        </button>
                    </form>
                    <?php endif; ?>

                    <a href="<?= APP_URL ?>/admin/usuario/<?= (int)$perfil['id_usuario'] ?>"
                       class="btn btn-secondary w-100">
                        <i class="bi bi-person me-2"></i>Ver perfil del usuario
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
