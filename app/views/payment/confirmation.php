<?php
/**
 * payment/confirmation.php — Confirmación de pago exitoso.
 */
$planLabel = PLANES_DESTACADO[(int)$pago['tipo_destacado']]['label'] ?? $pago['tipo_destacado'] . ' días';
$expira    = $anuncio['fecha_expiracion_destacado'] ?? null;
?>

<div class="container py-5" style="max-width:600px">

    <!-- Icono de éxito animado -->
    <div class="text-center mb-5">
        <div style="width:96px;height:96px;border-radius:50%;background:rgba(16,185,129,.12);border:3px solid rgba(16,185,129,.3);display:flex;align-items:center;justify-content:center;font-size:2.8rem;color:#10B981;margin:0 auto;animation:popIn .4s ease">
            <i class="bi bi-check-lg"></i>
        </div>
        <h1 class="h3 fw-black mt-4 mb-2">¡Pago completado!</h1>
        <p class="text-muted">Tu anuncio ya está destacado y aparece primero en el listado.</p>
    </div>

    <!-- Tarjeta resumen -->
    <div class="card mb-4">
        <div class="card-header d-flex align-items-center gap-2">
            <i class="bi bi-receipt text-primary"></i>
            <span class="fw-semibold">Comprobante de pago</span>
        </div>
        <div class="card-body">
            <div class="d-flex flex-column gap-3">

                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted">Referencia:</span>
                    <code style="color:var(--color-text);background:var(--color-bg-card2);padding:.2rem .5rem;border-radius:4px;font-size:.82rem">
                        <?= e($pago['referencia_ext'] ?? '—') ?>
                    </code>
                </div>

                <div class="d-flex justify-content-between">
                    <span class="text-muted">Anuncio:</span>
                    <span class="fw-semibold text-end" style="max-width:240px">
                        <?= e(Security::truncate($anuncio['titulo'] ?? '—', 50)) ?>
                    </span>
                </div>

                <div class="d-flex justify-content-between">
                    <span class="text-muted">Plan contratado:</span>
                    <span class="fw-semibold">
                        <i class="bi bi-star-fill text-warning me-1"></i><?= e($planLabel) ?>
                    </span>
                </div>

                <?php if ($expira): ?>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Destacado hasta:</span>
                    <span class="fw-semibold">
                        <?= e(date('d/m/Y \a \l\a\s H:i', strtotime($expira))) ?>
                    </span>
                </div>
                <?php endif; ?>

                <div class="d-flex justify-content-between">
                    <span class="text-muted">Método:</span>
                    <span class="text-capitalize"><?= e($pago['metodo_pago'] ?? 'simulado') ?></span>
                </div>

                <div class="d-flex justify-content-between">
                    <span class="text-muted">Fecha:</span>
                    <span><?= e(date('d/m/Y H:i', strtotime($pago['fecha_pago'] ?? $pago['fecha_creacion']))) ?></span>
                </div>

                <hr style="border-color:var(--color-border);margin:.25rem 0">

                <div class="d-flex justify-content-between align-items-center">
                    <span class="fw-bold fs-6">Total pagado:</span>
                    <span class="fw-black" style="font-size:1.4rem;color:var(--color-primary)">
                        <?= e(Security::formatMoney((float)$pago['monto'])) ?>
                    </span>
                </div>

            </div>
        </div>
    </div>

    <!-- Qué sigue -->
    <div class="card mb-4">
        <div class="card-body">
            <h6 class="fw-bold mb-3">
                <i class="bi bi-lightning-charge-fill text-warning me-2"></i>¿Qué pasa ahora?
            </h6>
            <div class="d-flex flex-column gap-2" style="font-size:.875rem">
                <div class="d-flex gap-2">
                    <i class="bi bi-check-circle-fill text-success mt-1 flex-shrink-0"></i>
                    <span>Tu anuncio aparece en las primeras posiciones del listado.</span>
                </div>
                <div class="d-flex gap-2">
                    <i class="bi bi-check-circle-fill text-success mt-1 flex-shrink-0"></i>
                    <span>Tiene el badge <strong>TOP</strong> visible para todos los usuarios.</span>
                </div>
                <div class="d-flex gap-2">
                    <i class="bi bi-check-circle-fill text-success mt-1 flex-shrink-0"></i>
                    <span>El destacado se desactiva automáticamente al vencer el plazo.</span>
                </div>
                <?php if ($expira): ?>
                <div class="d-flex gap-2">
                    <i class="bi bi-calendar-check text-primary mt-1 flex-shrink-0"></i>
                    <span>Activo hasta <strong><?= e(date('d/m/Y', strtotime($expira))) ?></strong>.</span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Acciones -->
    <div class="d-flex flex-column flex-sm-row gap-3">
        <a href="<?= APP_URL ?>/anuncio/<?= (int)$anuncio['id'] ?>"
           class="btn btn-primary flex-fill"
           target="_blank">
            <i class="bi bi-eye me-2"></i>Ver mi anuncio
        </a>
        <a href="<?= APP_URL ?>/mis-anuncios" class="btn btn-secondary flex-fill">
            <i class="bi bi-collection me-2"></i>Mis anuncios
        </a>
    </div>

</div>

<style>
@keyframes popIn {
    0%   { transform: scale(.5); opacity: 0; }
    80%  { transform: scale(1.1); }
    100% { transform: scale(1);   opacity: 1; }
}
</style>
