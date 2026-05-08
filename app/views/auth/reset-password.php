<?php
/**
 * reset-password.php — Form para escribir la nueva contraseña usando el token recibido por email.
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="<?= e(Middleware::generateCsrfToken()) ?>">
    <title><?= e($pageTitle ?? 'Nueva contraseña') ?> | <?= e(APP_NAME) ?></title>
    <?php require VIEWS_PATH . '/partials/head-meta.php'; ?>
    <link rel="stylesheet" href="<?= APP_URL ?>/public/assets/vendor/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/public/assets/vendor/bootstrap-icons/bootstrap-icons.min.css">
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
        .password-toggle{position:absolute;right:.75rem;top:50%;transform:translateY(-50%);
            background:none;border:0;color:var(--color-text-muted,#888);cursor:pointer;padding:.25rem}
        .password-toggle:hover{color:var(--color-primary)}
        .back-home-btn{position:fixed;top:1rem;left:1rem;display:inline-flex;align-items:center;gap:.4rem;
            background:var(--color-bg-card,#fff);border:1px solid var(--color-border,#e5e5e5);
            color:var(--color-text,#1a1a1a);text-decoration:none;font-size:.82rem;font-weight:500;
            padding:.45rem .9rem;border-radius:999px;box-shadow:0 2px 8px rgba(0,0,0,.06);
            transition:transform .15s,box-shadow .15s;z-index:100}
        .back-home-btn:hover{transform:translateY(-1px);box-shadow:0 4px 12px rgba(0,0,0,.1);color:var(--color-primary)}
        .back-home-btn i{font-size:1rem}
        @media(max-width:576px){.back-home-btn span{display:none}.back-home-btn{padding:.5rem .6rem}}
    </style>
</head>
<body>

<a href="<?= APP_URL ?>" class="back-home-btn" title="Volver al inicio">
    <i class="bi bi-arrow-left"></i>
    <span>Inicio</span>
</a>

<?php require VIEWS_PATH . '/partials/global-loader.php'; ?>
<?php require VIEWS_PATH . '/partials/toasts.php'; ?>
<?php require VIEWS_PATH . '/partials/cookie-banner.php'; ?>

<div class="auth-card mx-3">

    <div class="text-center mb-4">
        <a href="<?= APP_URL ?>" class="text-decoration-none">
            <div class="fw-black fs-3" style="color:var(--color-primary)">
                <i class="bi bi-heart-fill me-1"></i><?= e(APP_NAME) ?>
            </div>
        </a>
    </div>

    <div class="text-center mb-3">
        <div style="width:64px;height:64px;background:rgba(255,45,117,.08);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto;font-size:1.8rem;color:var(--color-primary);border:2px solid rgba(255,45,117,.2)">
            <i class="bi bi-shield-lock"></i>
        </div>
        <h2 class="h5 fw-bold mt-3 mb-1">Crea tu nueva contraseña</h2>
        <?php if (!empty($nombre)): ?>
            <p class="text-muted mb-0" style="font-size:.85rem">Hola <?= e($nombre) ?>, escribe tu nueva contraseña.</p>
        <?php else: ?>
            <p class="text-muted mb-0" style="font-size:.85rem">Escribe tu nueva contraseña dos veces para confirmarla.</p>
        <?php endif; ?>
    </div>

    <form method="POST"
          action="<?= APP_URL ?>/reset-password"
          data-validate-form
          novalidate>

        <?= $csrfField ?>
        <input type="hidden" name="token" value="<?= e($token) ?>">

        <div class="mb-3 position-relative">
            <label for="password" class="form-label">
                <i class="bi bi-key me-1"></i>Nueva contraseña
            </label>
            <input type="password"
                   id="password"
                   name="password"
                   class="form-control"
                   placeholder="Mínimo 8 caracteres"
                   autocomplete="new-password"
                   required
                   data-validate="required|strongPassword">
            <button type="button" class="password-toggle" data-toggle-password="password" aria-label="Mostrar/ocultar">
                <i class="bi bi-eye"></i>
            </button>
        </div>

        <div class="mb-4 position-relative">
            <label for="password_confirm" class="form-label">
                <i class="bi bi-key-fill me-1"></i>Confirmar contraseña
            </label>
            <input type="password"
                   id="password_confirm"
                   name="password_confirm"
                   class="form-control"
                   placeholder="Repite la contraseña"
                   autocomplete="new-password"
                   required
                   data-validate="required|matches:password">
            <button type="button" class="password-toggle" data-toggle-password="password_confirm" aria-label="Mostrar/ocultar">
                <i class="bi bi-eye"></i>
            </button>
        </div>

        <button type="submit" class="btn btn-primary w-100 mb-3">
            <i class="bi bi-check2-circle me-2"></i>Guardar nueva contraseña
        </button>

        <a href="<?= APP_URL ?>/login" class="btn btn-secondary w-100">
            <i class="bi bi-arrow-left me-2"></i>Volver al login
        </a>

    </form>

</div>

<script src="<?= APP_URL ?>/public/assets/vendor/bootstrap/bootstrap.bundle.min.js"></script>
<script src="<?= APP_URL ?>/public/assets/js/validation.js"></script>
<script src="<?= APP_URL ?>/public/assets/js/app.js"></script>
<script src="<?= APP_URL ?>/public/assets/js/common.js"></script>
<script src="<?= APP_URL ?>/public/assets/js/loader.js"></script>
<script src="<?= APP_URL ?>/public/assets/js/cookie-banner.js"></script>
</body>
</html>
