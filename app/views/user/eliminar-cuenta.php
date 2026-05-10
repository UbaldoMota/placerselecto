<?php
/** @var array  $usuario */
/** @var string $csrfField */

$tienePerfiles  = !empty($usuario['n_perfiles'] ?? 0);
?>
<div class="container py-4" style="max-width:560px">

    <div class="d-flex flex-wrap gap-2 mb-3">
        <a href="<?= APP_URL ?>/dashboard" class="btn btn-sm btn-secondary">
            <i class="bi bi-house me-1"></i>Dashboard
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <h1 class="h5 fw-bold mb-1 text-danger">
                <i class="bi bi-exclamation-octagon-fill me-2"></i>Eliminar mi cuenta
            </h1>
            <p class="text-muted mb-4" style="font-size:.88rem">
                Esta acción no es inmediata. Tu cuenta queda en periodo de gracia de 30 días.
            </p>

            <div class="alert alert-warning mb-4" style="font-size:.88rem">
                <strong><i class="bi bi-info-circle me-1"></i>Qué pasa cuando eliminas tu cuenta:</strong>
                <ul class="mb-0 mt-2" style="padding-left:1.2rem">
                    <li>Tu sesión se cierra inmediatamente.</li>
                    <li>Tus perfiles se ocultan del directorio.</li>
                    <li>No puedes iniciar sesión normalmente.</li>
                    <li>Recibes un correo con un link para revertir si fue un error.</li>
                    <li>A los <strong>30 días</strong> se borran de forma permanente: perfiles, fotos, videos y documento de identidad.</li>
                    <li>El saldo de tokens <strong>no se reembolsa</strong> (los tokens son virtuales, no canjeables).</li>
                </ul>
            </div>

            <form method="POST" action="<?= APP_URL ?>/mi-cuenta/eliminar" autocomplete="off"
                  data-confirm-submit="¿Estás segura? Tu cuenta se eliminará en 30 días.">
                <?= $csrfField ?>

                <div class="mb-3">
                    <label class="form-label">Tu contraseña actual</label>
                    <input type="password" name="password_actual" class="form-control"
                           required autocomplete="current-password">
                </div>

                <div class="mb-4">
                    <label class="form-label">Para confirmar, escribe <strong>ELIMINAR</strong></label>
                    <input type="text" name="confirmacion" class="form-control"
                           placeholder="ELIMINAR" required pattern="^ELIMINAR$"
                           autocomplete="off" spellcheck="false"
                           style="text-transform:uppercase">
                    <div class="form-text" style="font-size:.78rem">
                        Debes escribir exactamente la palabra ELIMINAR (en mayúsculas, sin espacios).
                    </div>
                </div>

                <div class="d-flex gap-2 justify-content-end">
                    <a href="<?= APP_URL ?>/dashboard" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash3-fill me-1"></i>Eliminar mi cuenta
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
