<?php
/**
 * perfiles/verificar-instrucciones.php
 * Resumen del proceso de verificación (fotos + video — ambos obligatorios).
 */
$idPerfil    = (int)$perfil['id'];
$ambosListos = $tieneFotosVer && $tieneVideoVer;
?>

<div class="container py-5" style="max-width:700px">

    <div class="text-center mb-4">
        <div style="width:72px;height:72px;border-radius:50%;background:rgba(255,45,117,.1);border:2px solid rgba(255,45,117,.3);display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;font-size:2rem">
            <?php if ($ambosListos): ?>
            <i class="bi bi-shield-check" style="color:#10B981"></i>
            <?php else: ?>
            <i class="bi bi-shield-exclamation" style="color:var(--color-primary)"></i>
            <?php endif; ?>
        </div>
        <h1 class="h4 fw-bold mb-1">
            <?= $ambosListos ? '¡Verificación completada!' : 'Verificación del perfil' ?>
        </h1>
        <p class="text-muted mb-0" style="font-size:.9rem">
            <?php if ($ambosListos): ?>
                <strong><?= e($perfil['nombre']) ?></strong> ha completado los dos pasos.
                El equipo revisará tu perfil en un máximo de 24 horas.
            <?php else: ?>
                Para publicar <strong><?= e($perfil['nombre']) ?></strong> debes completar
                <strong>los dos pasos</strong> de verificación.
            <?php endif; ?>
        </p>
    </div>

    <!-- Progreso visual -->
    <div class="d-flex align-items-center gap-2 mb-4 px-2">
        <div class="flex-fill" style="height:4px;border-radius:4px;background:<?= $tieneFotosVer ? '#10B981' : 'rgba(255,255,255,.12)' ?>"></div>
        <div style="font-size:.72rem;color:var(--color-text-muted);white-space:nowrap">
            <?= ($tieneFotosVer ? 1 : 0) + ($tieneVideoVer ? 1 : 0) ?> / 2 completados
        </div>
        <div class="flex-fill" style="height:4px;border-radius:4px;background:<?= $tieneVideoVer ? '#10B981' : 'rgba(255,255,255,.12)' ?>"></div>
    </div>

    <!-- PASO 1 — Fotos -->
    <div class="card mb-3" style="border:1px solid <?= $tieneFotosVer ? 'rgba(16,185,129,.35)' : 'var(--color-border)' ?>">
        <div class="card-body">
            <div class="d-flex align-items-start gap-3">
                <!-- Ícono estado -->
                <div style="width:44px;height:44px;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:1.3rem;
                     background:<?= $tieneFotosVer ? 'rgba(16,185,129,.15)' : 'rgba(255,45,117,.1)' ?>;
                     border:2px solid <?= $tieneFotosVer ? 'rgba(16,185,129,.35)' : 'rgba(255,45,117,.25)' ?>">
                    <?php if ($tieneFotosVer): ?>
                    <i class="bi bi-check-lg" style="color:#10B981"></i>
                    <?php else: ?>
                    <i class="bi bi-images" style="color:var(--color-primary)"></i>
                    <?php endif; ?>
                </div>
                <div class="flex-fill">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-1">
                        <h2 class="h6 fw-bold mb-0">
                            Paso 1 — Fotos de verificación
                        </h2>
                        <?php if ($tieneFotosVer): ?>
                        <span style="font-size:.75rem;color:#10B981;font-weight:600">
                            <i class="bi bi-check-circle-fill me-1"></i>Completado
                        </span>
                        <?php else: ?>
                        <span style="font-size:.75rem;color:var(--color-warning);font-weight:600">
                            <i class="bi bi-clock me-1"></i>Pendiente
                        </span>
                        <?php endif; ?>
                    </div>
                    <p class="text-muted mb-2" style="font-size:.82rem">
                        Las mismas fotos del perfil <strong>sin filtros y sin cubrir el rostro</strong>,
                        mostrando mínimo 2/3 del cuerpo.
                    </p>
                    <?php if (!$tieneFotosVer): ?>
                    <a href="<?= APP_URL ?>/perfil/<?= $idPerfil ?>/verificar/fotos"
                       class="btn btn-sm btn-primary">
                        <i class="bi bi-upload me-1"></i>Subir fotos
                    </a>
                    <?php else: ?>
                    <a href="<?= APP_URL ?>/perfil/<?= $idPerfil ?>/verificar/fotos"
                       class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-arrow-repeat me-1"></i>Reenviar fotos
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- PASO 2 — Video -->
    <div class="card mb-4" style="border:1px solid <?= $tieneVideoVer ? 'rgba(16,185,129,.35)' : 'var(--color-border)' ?>">
        <div class="card-body">
            <div class="d-flex align-items-start gap-3">
                <!-- Ícono estado -->
                <div style="width:44px;height:44px;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:1.3rem;
                     background:<?= $tieneVideoVer ? 'rgba(16,185,129,.15)' : 'rgba(255,45,117,.1)' ?>;
                     border:2px solid <?= $tieneVideoVer ? 'rgba(16,185,129,.35)' : 'rgba(255,45,117,.25)' ?>">
                    <?php if ($tieneVideoVer): ?>
                    <i class="bi bi-check-lg" style="color:#10B981"></i>
                    <?php else: ?>
                    <i class="bi bi-camera-video" style="color:var(--color-primary)"></i>
                    <?php endif; ?>
                </div>
                <div class="flex-fill">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-1">
                        <h2 class="h6 fw-bold mb-0">
                            Paso 2 — Video de verificación
                        </h2>
                        <?php if ($tieneVideoVer): ?>
                        <span style="font-size:.75rem;color:#10B981;font-weight:600">
                            <i class="bi bi-check-circle-fill me-1"></i>Completado
                            <?php if (!empty($perfil['video_verificacion_at'])): ?>
                            <span class="text-muted fw-normal">· <?= e(date('d/m/Y H:i', strtotime($perfil['video_verificacion_at']))) ?></span>
                            <?php endif; ?>
                        </span>
                        <?php else: ?>
                        <span style="font-size:.75rem;color:var(--color-warning);font-weight:600">
                            <i class="bi bi-clock me-1"></i>Pendiente
                        </span>
                        <?php endif; ?>
                    </div>
                    <p class="text-muted mb-2" style="font-size:.82rem">
                        Un video de 5 segundos sosteniendo un cartel con
                        <strong><?= e(APP_NAME) ?></strong> y el nombre del perfil.
                        Muévete para demostrar que el video es real.
                    </p>
                    <?php if (!$tieneVideoVer): ?>
                    <a href="<?= APP_URL ?>/perfil/<?= $idPerfil ?>/verificar/camara"
                       class="btn btn-sm btn-primary">
                        <i class="bi bi-camera-video me-1"></i>Grabar video
                    </a>
                    <?php else: ?>
                    <a href="<?= APP_URL ?>/perfil/<?= $idPerfil ?>/verificar/camara"
                       class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-arrow-repeat me-1"></i>Regrabar video
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Fotos actuales del perfil (referencia) -->
    <?php if (!empty($fotosGaleria) && !$tieneFotosVer): ?>
    <div class="card mb-4">
        <div class="card-header">
            <span class="fw-semibold" style="font-size:.875rem">
                <i class="bi bi-collection text-primary me-2"></i>Tus fotos de perfil (referencia para el paso 1)
            </span>
        </div>
        <div class="card-body">
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(80px,1fr));gap:.5rem">
                <?php foreach ($fotosGaleria as $foto): ?>
                <img src="<?= APP_URL ?>/img/<?= e($foto["token"]) ?>?size=thumb"
                     style="width:100%;aspect-ratio:1;object-fit:cover;border-radius:6px;border:1px solid var(--color-border)"
                     loading="lazy">
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Aviso o botón final -->
    <?php if ($ambosListos): ?>
    <div class="rounded-3 p-3 mb-4 d-flex align-items-center gap-3"
         style="background:rgba(16,185,129,.07);border:1px solid rgba(16,185,129,.2);font-size:.85rem">
        <i class="bi bi-clock-history text-success fs-4 flex-shrink-0"></i>
        <span style="color:var(--color-text-muted)">
            Tu perfil está <strong>en revisión</strong>. El equipo lo revisará en un máximo de
            <strong>24 horas</strong> y recibirás una notificación cuando sea aprobado.
        </span>
    </div>
    <?php else: ?>
    <div class="rounded-3 p-3 mb-4 d-flex align-items-center gap-3"
         style="background:rgba(255,193,7,.06);border:1px solid rgba(255,193,7,.2);font-size:.83rem">
        <i class="bi bi-exclamation-triangle text-warning fs-5 flex-shrink-0"></i>
        <span style="color:var(--color-text-muted)">
            Tu perfil permanecerá <strong>en revisión</strong> hasta que completes los dos pasos.
            Ambas verificaciones son necesarias para la aprobación.
        </span>
    </div>
    <?php endif; ?>

    <div class="text-center">
        <a href="<?= APP_URL ?>/mis-perfiles" class="btn btn-secondary px-5">
            <i class="bi bi-collection me-2"></i>Mis perfiles
        </a>
    </div>

</div>
