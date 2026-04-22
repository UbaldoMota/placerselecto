<?php /** auth/registro-contacto.php — Paso 2: teléfono + email */ ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="<?= e(Middleware::generateCsrfToken()) ?>">
    <title><?= e($pageTitle ?? 'Datos de contacto') ?> | <?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="<?= APP_URL ?>/public/assets/vendor/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/public/assets/vendor/bootstrap-icons/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/public/assets/css/app.css">
    <style>
        body{min-height:100vh;display:flex;align-items:center;justify-content:center;background:var(--color-bg);
             background-image:radial-gradient(ellipse at 90% 80%,rgba(255,45,117,.07) 0%,transparent 50%),
                              radial-gradient(ellipse at 10% 10%,rgba(255,127,168,.12) 0%,transparent 50%);padding:2rem 0}
        .auth-card{background:var(--color-bg-card);border:1px solid var(--color-border);border-radius:var(--radius-lg);
                   padding:2.5rem 2rem;width:100%;max-width:480px;box-shadow:var(--shadow-lg)}
        .step-dots{display:flex;gap:.5rem;justify-content:center;margin-bottom:1.5rem}
        .step-dot{width:8px;height:8px;border-radius:50%;background:var(--color-border)}
        .step-dot.active{background:var(--color-primary);width:24px;border-radius:4px}
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

    <!-- Logo -->
    <div class="text-center mb-3">
        <a href="<?= APP_URL ?>" class="text-decoration-none">
            <div class="fw-black fs-4" style="color:var(--color-primary)">
                <i class="bi bi-heart-fill me-1"></i><?= e(APP_NAME) ?>
            </div>
        </a>
    </div>

    <!-- Pasos -->
    <div class="step-dots">
        <div class="step-dot"></div>
        <div class="step-dot active"></div>
        <div class="step-dot"></div>
        <div class="step-dot"></div>
    </div>

    <h1 class="h5 fw-bold mb-1 text-center">Datos de contacto</h1>
    <p class="text-muted text-center mb-4" style="font-size:.83rem">
        Verificaremos tu teléfono y correo para activar tu cuenta
    </p>

    <form method="POST" action="<?= APP_URL ?>/registro/publicador"
          data-validate-form novalidate>
        <?= $csrfField ?>

        <!-- Teléfono -->
        <div class="mb-3">
            <label for="telefono" class="form-label">
                <i class="bi bi-phone me-1"></i>Número de teléfono <span class="text-danger">*</span>
            </label>
            <div class="input-group">
                <span class="input-group-text"
                      style="background:var(--color-bg-card2);border-color:var(--color-border);color:var(--color-text-muted)">
                    +
                </span>
                <input type="tel"
                       id="telefono"
                       name="telefono"
                       class="form-control"
                       placeholder="52 55 1234 5678"
                       value="<?= e($old['telefono'] ?? '') ?>"
                       autocomplete="tel"
                       required
                       data-validate="required|phone">
            </div>
            <div class="form-text text-muted" style="font-size:.74rem">
                Incluye código de país. Ej: 52 55 1234 5678. Te enviaremos un SMS de verificación.
            </div>
        </div>

        <!-- Correo -->
        <div class="mb-3">
            <label for="email" class="form-label">
                <i class="bi bi-envelope me-1"></i>Correo electrónico <span class="text-danger">*</span>
            </label>
            <input type="email"
                   id="email"
                   name="email"
                   class="form-control"
                   placeholder="tu@correo.com"
                   value="<?= e($old['email'] ?? '') ?>"
                   autocomplete="email"
                   required
                   data-validate="required|email">
        </div>

        <!-- Consentimiento SMS -->
        <div class="mb-4">
            <div class="rounded-3 p-3"
                 style="background:rgba(255,45,117,.05);border:1px solid rgba(255,45,117,.2)">
                <div class="form-check mb-0">
                    <input type="checkbox"
                           class="form-check-input"
                           id="autoriza_sms"
                           name="autoriza_sms"
                           value="1"
                           required>
                    <label class="form-check-label" for="autoriza_sms" style="font-size:.82rem">
                        Autorizo el envío de un SMS de verificación a mi teléfono.
                        Los datos no serán compartidos con terceros.
                        Consulta nuestro <a href="<?= APP_URL ?>/privacidad" target="_blank">aviso de privacidad</a>.
                    </label>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100">
            <i class="bi bi-send me-2"></i>Enviar códigos de verificación
        </button>
    </form>

    <div class="text-center mt-3" style="font-size:.83rem;color:var(--color-text-muted)">
        <a href="<?= APP_URL ?>/registro" class="text-muted">
            <i class="bi bi-arrow-left me-1"></i>Cambiar tipo de cuenta
        </a>
    </div>

</div>

<script src="<?= APP_URL ?>/public/assets/vendor/bootstrap/bootstrap.bundle.min.js"></script>
<script src="<?= APP_URL ?>/public/assets/js/validation.js"></script>
<script src="<?= APP_URL ?>/public/assets/js/app.js"></script>
<script src="<?= APP_URL ?>/public/assets/js/common.js"></script>
</body>
</html>
