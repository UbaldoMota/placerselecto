<?php
/** @var array  $usuario */
/** @var ?array $pendiente */

$estado   = $usuario['estado_verificacion'] ?? 'pendiente';
$bloqueada = in_array($estado, ['rechazado', 'suspendido'], true);

$meta = [
    'rechazado'  => ['titulo' => 'Cuenta rechazada',  'color' => 'danger',  'icono' => 'x-octagon-fill'],
    'suspendido' => ['titulo' => 'Cuenta suspendida', 'color' => 'warning', 'icono' => 'slash-circle-fill'],
    'pendiente'  => ['titulo' => 'Cuenta en revisión','color' => 'info',    'icono' => 'clock-history'],
    'aprobado'   => ['titulo' => 'Cuenta activa',     'color' => 'success', 'icono' => 'patch-check-fill'],
][$estado] ?? ['titulo' => 'Estado desconocido', 'color' => 'secondary', 'icono' => 'question-circle'];
?>
<div class="container py-4" style="max-width:720px">

    <div class="d-flex flex-wrap gap-2 mb-3">
        <a href="<?= APP_URL ?>/mis-perfiles" class="btn btn-sm btn-secondary">
            <i class="bi bi-person-lines-fill me-1"></i>Mis perfiles
        </a>
        <a href="<?= APP_URL ?>/dashboard" class="btn btn-sm btn-secondary">
            <i class="bi bi-house me-1"></i>Dashboard
        </a>
    </div>

    <!-- Estado actual -->
    <div class="card mb-4">
        <div class="card-body d-flex align-items-center gap-3">
            <div style="width:54px;height:54px;border-radius:50%;background:rgba(255,45,117,.08);display:flex;align-items:center;justify-content:center;color:var(--color-<?= e($meta['color']) ?>);font-size:1.7rem;flex-shrink:0">
                <i class="bi bi-<?= e($meta['icono']) ?>"></i>
            </div>
            <div>
                <h1 class="h5 fw-bold mb-1"><?= e($meta['titulo']) ?></h1>
                <p class="text-muted mb-0" style="font-size:.88rem">
                    <?php if ($bloqueada): ?>
                        Tu cuenta está bloqueada. No puedes crear nuevos perfiles mientras esté en este estado.
                    <?php elseif ($estado === 'aprobado'): ?>
                        Tu cuenta está activa. Ya puedes publicar perfiles normalmente.
                    <?php else: ?>
                        Tu cuenta está siendo revisada por nuestro equipo. Pronto recibirás una notificación.
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>

    <?php if ($pendiente): ?>
    <!-- Ya hay solicitud abierta/respondida -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong><i class="bi bi-envelope-fill text-primary me-1"></i>Tu última solicitud</strong>
            <span class="badge-estado <?= $pendiente['estado'] === 'respondido' ? 'badge-publicado' : 'badge-pendiente' ?>">
                <?= $pendiente['estado'] === 'respondido' ? 'Respondida' : 'En espera' ?>
            </span>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <div class="text-muted" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.05em">Asunto</div>
                <strong><?= e($pendiente['asunto']) ?></strong>
            </div>
            <div class="mb-3">
                <div class="text-muted" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.05em">Tu mensaje</div>
                <p class="mb-0" style="white-space:pre-wrap"><?= nl2br(e($pendiente['mensaje'])) ?></p>
                <small class="text-muted">Enviado: <?= e(date('d/m/Y H:i', strtotime($pendiente['fecha_creacion']))) ?></small>
            </div>
            <?php if (!empty($pendiente['respuesta_admin'])): ?>
            <div class="p-3 rounded" style="background:var(--color-bg-alt);border-left:3px solid var(--color-primary)">
                <div class="text-muted mb-1" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.05em">
                    <i class="bi bi-shield-check me-1"></i>Respuesta del equipo
                </div>
                <p class="mb-1" style="white-space:pre-wrap"><?= nl2br(e($pendiente['respuesta_admin'])) ?></p>
                <small class="text-muted">Respondido: <?= e(date('d/m/Y H:i', strtotime($pendiente['fecha_respuesta']))) ?></small>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($bloqueada): ?>
    <!-- Formulario nueva solicitud -->
    <div class="card">
        <div class="card-header">
            <strong><i class="bi bi-send text-primary me-1"></i>Enviar solicitud al administrador</strong>
        </div>
        <div class="card-body">
            <p class="text-muted mb-3" style="font-size:.88rem">
                Escribe un mensaje explicando por qué tu cuenta debería reactivarse. Sé claro y directo — el equipo revisará tu caso.
            </p>

            <form method="POST" action="<?= APP_URL ?>/cuenta/reactivar">
                <?= $csrfField ?>

                <div class="mb-3">
                    <label class="form-label">Asunto *</label>
                    <input type="text" name="asunto" class="form-control" required
                           minlength="4" maxlength="150"
                           placeholder="Ej. Solicitud de reactivación de cuenta"
                           value="<?= e($_POST['asunto'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Mensaje *</label>
                    <textarea name="mensaje" class="form-control" rows="6" required
                              minlength="20" maxlength="5000"
                              placeholder="Explica por qué debería reactivarse tu cuenta. Incluye cualquier información relevante."><?= e($_POST['mensaje'] ?? '') ?></textarea>
                    <small class="text-muted">Mínimo 20 caracteres, máximo 5000.</small>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-send me-1"></i>Enviar solicitud
                </button>
                <small class="text-muted d-block mt-2" style="font-size:.78rem">
                    Máximo 2 solicitudes por día. Recibirás una notificación cuando el admin responda.
                </small>
            </form>
        </div>
    </div>
    <?php endif; ?>

</div>
