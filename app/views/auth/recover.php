<?php
/**
 * recover.php — Vista de recuperación de contraseña.
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="<?= e(Middleware::generateCsrfToken()) ?>">
    <title><?= e($pageTitle ?? 'Recuperar contraseña') ?> | <?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/public/assets/css/app.css">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--color-bg);
        }
        .auth-card {
            background: var(--color-bg-card);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-lg);
            padding: 2.5rem 2rem;
            width: 100%;
            max-width: 420px;
            box-shadow: var(--shadow-lg);
        }
    </style>
</head>
<body>

<!-- Flash messages -->
<?php require VIEWS_PATH . '/partials/toasts.php'; ?>

<div class="auth-card mx-3">

    <!-- Logo -->
    <div class="text-center mb-4">
        <a href="<?= APP_URL ?>" class="text-decoration-none">
            <div class="fw-black fs-3" style="color:var(--color-primary)">
                <i class="bi bi-heart-fill me-1"></i><?= e(APP_NAME) ?>
            </div>
        </a>
    </div>

    <!-- Icono -->
    <div class="text-center mb-3">
        <div style="width:64px;height:64px;background:rgba(23,162,184,.1);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto;font-size:1.8rem;color:#17a2b8;border:2px solid rgba(23,162,184,.2)">
            <i class="bi bi-key"></i>
        </div>
        <h2 class="h5 fw-bold mt-3 mb-1">Recuperar contraseña</h2>
        <p class="text-muted mb-0" style="font-size:.85rem">
            Ingresa tu email y te enviaremos instrucciones.
        </p>
    </div>

    <form method="POST"
          action="<?= APP_URL ?>/recuperar-password"
          data-validate-form
          novalidate>

        <?= $csrfField ?>

        <div class="mb-4">
            <label for="email" class="form-label">
                <i class="bi bi-envelope me-1"></i>Email registrado
            </label>
            <input type="email"
                   id="email"
                   name="email"
                   class="form-control"
                   placeholder="tu@email.com"
                   autocomplete="email"
                   required
                   data-validate="required|email">
        </div>

        <button type="submit" class="btn btn-primary w-100 mb-3">
            <i class="bi bi-send me-2"></i>Enviar instrucciones
        </button>

        <a href="<?= APP_URL ?>/login" class="btn btn-secondary w-100">
            <i class="bi bi-arrow-left me-2"></i>Volver al login
        </a>

    </form>

    <!-- Aviso sobre el sistema simulado -->
    <?php if (defined('APP_DEBUG') && APP_DEBUG): ?>
    <div class="mt-3 p-2 rounded-2 text-center"
         style="background:rgba(255,193,7,.06);border:1px solid rgba(255,193,7,.15);font-size:.75rem;color:#ffd44d">
        <i class="bi bi-bug me-1"></i>
        <strong>Modo desarrollo:</strong> el link de recuperación aparece en el flash y en el log del servidor.
    </div>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= APP_URL ?>/public/assets/js/validation.js"></script>
<script src="<?= APP_URL ?>/public/assets/js/app.js"></script>
</body>
</html>
