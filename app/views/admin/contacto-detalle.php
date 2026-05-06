<?php
/** @var array $msg */
$asuntoLabel = ContactoMensajeModel::ASUNTOS_LABELS[$msg['asunto']] ?? $msg['asunto'];
$noLeido = empty($msg['leido']);
?>
<div class="container py-4" style="max-width:760px">

    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <h1 class="h4 fw-bold mb-0">
            <i class="bi bi-envelope-paper-fill text-primary me-2"></i>Mensaje de contacto
        </h1>
        <a href="<?= APP_URL ?>/admin/contactos" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left me-1"></i>Volver al listado
        </a>
    </div>

    <div class="card">
        <div class="card-body">

            <!-- Header -->
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap mb-2">
                        <h2 class="h5 fw-bold mb-0"><?= e($msg['nombre']) ?></h2>
                        <span class="badge bg-primary bg-opacity-10 text-primary"><?= e($asuntoLabel) ?></span>
                        <?php if ($noLeido): ?>
                        <span class="badge bg-warning bg-opacity-25 text-warning border border-warning border-opacity-25">Sin leer</span>
                        <?php endif; ?>
                    </div>
                    <div class="text-muted" style="font-size:.85rem">
                        <a href="mailto:<?= e($msg['email']) ?>"><?= e($msg['email']) ?></a>
                    </div>
                </div>
            </div>

            <!-- Metadatos -->
            <div class="row g-2 mb-3" style="font-size:.82rem">
                <div class="col-md-6">
                    <strong class="text-muted">Recibido:</strong>
                    <?= e(date('d/m/Y H:i:s', strtotime($msg['fecha_creacion']))) ?>
                </div>
                <div class="col-md-6">
                    <strong class="text-muted">IP:</strong>
                    <code><?= e($msg['ip'] ?? '-') ?></code>
                </div>
            </div>

            <hr>

            <!-- Mensaje -->
            <div class="mb-4">
                <div class="fw-semibold mb-2" style="color:var(--color-text-muted);font-size:.78rem;text-transform:uppercase;letter-spacing:.5px">
                    Mensaje
                </div>
                <div style="background:rgba(255,45,117,.04);border-left:3px solid var(--color-primary);padding:14px 18px;border-radius:6px;font-size:.92rem;line-height:1.7;white-space:pre-wrap"><?= e($msg['mensaje']) ?></div>
            </div>

            <!-- Acciones -->
            <div class="d-flex gap-2 flex-wrap">
                <a href="mailto:<?= e($msg['email']) ?>?subject=Re:%20<?= urlencode($asuntoLabel) ?>" class="btn btn-primary">
                    <i class="bi bi-reply me-1"></i>Responder por correo
                </a>
                <form method="POST" action="<?= APP_URL ?>/admin/contactos/<?= (int)$msg['id'] ?>/leido" class="d-inline">
                    <?= $csrfField ?>
                    <input type="hidden" name="leido" value="<?= $noLeido ? '1' : '0' ?>">
                    <button type="submit" class="btn btn-secondary">
                        <i class="bi <?= $noLeido ? 'bi-check2' : 'bi-arrow-counterclockwise' ?> me-1"></i>
                        <?= $noLeido ? 'Marcar como leído' : 'Marcar como no leído' ?>
                    </button>
                </form>
                <form method="POST" action="<?= APP_URL ?>/admin/contactos/<?= (int)$msg['id'] ?>/eliminar" class="d-inline ms-auto"
                      data-confirm-click="¿Eliminar este mensaje permanentemente?">
                    <?= $csrfField ?>
                    <button type="submit" class="btn btn-outline-danger">
                        <i class="bi bi-trash me-1"></i>Eliminar
                    </button>
                </form>
            </div>

        </div>
    </div>

</div>
