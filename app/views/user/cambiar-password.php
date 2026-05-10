<?php /** @var string $csrfField */ ?>
<div class="container py-4" style="max-width:520px">

    <div class="d-flex flex-wrap gap-2 mb-3">
        <a href="<?= APP_URL ?>/dashboard" class="btn btn-sm btn-secondary">
            <i class="bi bi-house me-1"></i>Dashboard
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <h1 class="h5 fw-bold mb-1">
                <i class="bi bi-key-fill text-primary me-2"></i>Cambiar contraseña
            </h1>
            <p class="text-muted mb-4" style="font-size:.88rem">
                Para tu seguridad, te pedimos tu contraseña actual antes del cambio.
            </p>

            <form method="POST" action="<?= APP_URL ?>/mi-cuenta/password" autocomplete="off">
                <?= $csrfField ?>

                <div class="mb-3">
                    <label class="form-label">Contraseña actual</label>
                    <input type="password" name="password_actual" class="form-control"
                           required autocomplete="current-password" autofocus>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nueva contraseña</label>
                    <input type="password" name="password_nueva" class="form-control"
                           required minlength="8" autocomplete="new-password">
                    <div class="form-text" style="font-size:.78rem">
                        Mínimo 8 caracteres, con al menos 1 mayúscula, 1 minúscula y 1 número.
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Confirma la nueva contraseña</label>
                    <input type="password" name="password_confirm" class="form-control"
                           required minlength="8" autocomplete="new-password">
                </div>

                <div class="d-flex gap-2 justify-content-end">
                    <a href="<?= APP_URL ?>/dashboard" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check2-circle me-1"></i>Cambiar contraseña
                    </button>
                </div>
            </form>
        </div>
    </div>

    <p class="text-muted text-center mt-3" style="font-size:.78rem">
        Recibirás un correo confirmando el cambio. Si pierdes tu contraseña,
        puedes <a href="<?= APP_URL ?>/recuperar-password">recuperarla por email</a>.
    </p>

</div>
