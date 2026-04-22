<?php /** auth/registro-comentarista.php — Registro rápido (email + password) */ ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="<?= e(Middleware::generateCsrfToken()) ?>">
    <title><?= e($pageTitle ?? 'Crear cuenta') ?> | <?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="<?= APP_URL ?>/public/assets/vendor/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/public/assets/vendor/bootstrap-icons/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/public/assets/css/app.css">
    <style>
        body{min-height:100vh;display:flex;align-items:center;justify-content:center;background:var(--color-bg);
             background-image:radial-gradient(ellipse at 90% 80%,rgba(255,45,117,.07) 0%,transparent 50%),
                              radial-gradient(ellipse at 10% 10%,rgba(255,127,168,.12) 0%,transparent 50%);padding:2rem 0}
        .auth-card{background:var(--color-bg-card);border:1px solid var(--color-border);border-radius:var(--radius-lg);
                   padding:2.5rem 2rem;width:100%;max-width:440px;box-shadow:var(--shadow-lg)}
    </style>
</head>
<body>

<a href="<?= APP_URL ?>" class="back-home-btn" title="Volver al inicio">
    <i class="bi bi-arrow-left"></i>
    <span>Inicio</span>
</a>
<style>
.back-home-btn{position:fixed;top:1rem;left:1rem;display:inline-flex;align-items:center;gap:.4rem;
    background:var(--color-bg-card,#fff);border:1px solid var(--color-border,#e5e5e5);
    color:var(--color-text,#1a1a1a);text-decoration:none;font-size:.82rem;font-weight:500;
    padding:.45rem .9rem;border-radius:999px;box-shadow:0 2px 8px rgba(0,0,0,.06);
    transition:transform .15s,box-shadow .15s;z-index:100}
.back-home-btn:hover{transform:translateY(-1px);box-shadow:0 4px 12px rgba(0,0,0,.1);color:var(--color-primary)}
.back-home-btn i{font-size:1rem}
@media(max-width:576px){.back-home-btn span{display:none}.back-home-btn{padding:.5rem .6rem}}
</style>


<?php require VIEWS_PATH . '/partials/toasts.php'; ?>

<div class="auth-card mx-3">

    <div class="text-center mb-4">
        <a href="<?= APP_URL ?>" class="text-decoration-none">
            <div class="fw-black fs-3" style="color:var(--color-primary)">
                <i class="bi bi-heart-fill me-1"></i><?= e(APP_NAME) ?>
            </div>
        </a>
        <div class="d-inline-flex align-items-center gap-2 mt-3 px-3 py-1"
             style="background:rgba(13,110,253,.10);color:#3B82F6;border-radius:999px;font-size:.72rem;font-weight:700">
            <i class="bi bi-chat-dots-fill"></i>Cuenta de comentarios
        </div>
        <h1 class="h5 fw-bold mt-3 mb-1">Crear cuenta</h1>
        <p class="text-muted mb-0" style="font-size:.85rem">
            Para comentar y calificar perfiles. Sin verificación telefónica.
        </p>
    </div>

    <form method="POST" action="<?= APP_URL ?>/registro/comentarista" data-validate-form novalidate>
        <?= Middleware::csrfField() ?>

        <div class="mb-3">
            <label for="nombre" class="form-label">
                <i class="bi bi-person me-1"></i>Nombre o apodo <span class="text-danger">*</span>
            </label>
            <input type="text" id="nombre" name="nombre" class="form-control"
                   maxlength="120" required autocomplete="name"
                   value="<?= e($old['nombre'] ?? '') ?>">
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">
                <i class="bi bi-envelope me-1"></i>Correo electrónico <span class="text-danger">*</span>
            </label>
            <input type="email" id="email" name="email" class="form-control"
                   required autocomplete="email"
                   value="<?= e($old['email'] ?? '') ?>">
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">
                <i class="bi bi-lock me-1"></i>Contraseña <span class="text-danger">*</span>
            </label>
            <input type="password" id="password" name="password" class="form-control"
                   required autocomplete="new-password" minlength="8">
            <small class="text-muted" style="font-size:.74rem">
                Mínimo 8 caracteres, con mayúscula, minúscula y número.
            </small>
        </div>

        <div class="mb-3">
            <label for="password_confirm" class="form-label">
                <i class="bi bi-lock-fill me-1"></i>Confirmar contraseña <span class="text-danger">*</span>
            </label>
            <input type="password" id="password_confirm" name="password_confirm" class="form-control"
                   required autocomplete="new-password">
        </div>

        <div class="mb-4">
            <div class="form-check">
                <input type="checkbox" id="acepta_terminos" name="acepta_terminos" class="form-check-input" value="1" required>
                <label for="acepta_terminos" class="form-check-label" style="font-size:.82rem">
                    Soy mayor de <strong>18 años</strong> y acepto los
                    <a href="<?= APP_URL ?>/terminos" target="_blank">términos</a> y el
                    <a href="<?= APP_URL ?>/privacidad" target="_blank">aviso de privacidad</a>.
                </label>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100">
            <i class="bi bi-check-circle me-1"></i>Crear cuenta
        </button>
    </form>

    <div class="text-center mt-3" style="font-size:.85rem;color:var(--color-text-muted)">
        <a href="<?= APP_URL ?>/registro" class="text-muted">
            <i class="bi bi-arrow-left me-1"></i>Volver a tipo de cuenta
        </a>
        <span class="mx-2">·</span>
        <a href="<?= APP_URL ?>/login" class="fw-semibold">Ya tengo cuenta</a>
    </div>
</div>

<script src="<?= APP_URL ?>/public/assets/vendor/bootstrap/bootstrap.bundle.min.js"></script>
<script src="<?= APP_URL ?>/public/assets/js/validation.js"></script>
<script src="<?= APP_URL ?>/public/assets/js/app.js"></script>
<script src="<?= APP_URL ?>/public/assets/js/common.js"></script>
</body>
</html>
