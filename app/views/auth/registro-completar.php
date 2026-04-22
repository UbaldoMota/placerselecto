<?php /** auth/registro-completar.php — Paso 4: nombre + contraseña */ ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="<?= e(Middleware::generateCsrfToken()) ?>">
    <title><?= e($pageTitle ?? 'Crea tu contraseña') ?> | <?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="<?= APP_URL ?>/public/assets/vendor/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/public/assets/vendor/bootstrap-icons/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/public/assets/css/app.css">
    <style>
        body{min-height:100vh;display:flex;align-items:center;justify-content:center;background:var(--color-bg);
             background-image:radial-gradient(ellipse at 90% 80%,rgba(255,45,117,.07) 0%,transparent 50%),
                              radial-gradient(ellipse at 10% 10%,rgba(255,127,168,.12) 0%,transparent 50%);padding:2rem 0}
        .auth-card{background:var(--color-bg-card);border:1px solid var(--color-border);border-radius:var(--radius-lg);
                   padding:2.5rem 2rem;width:100%;max-width:440px;box-shadow:var(--shadow-lg)}
        .step-dots{display:flex;gap:.5rem;justify-content:center;margin-bottom:1.5rem}
        .step-dot{width:8px;height:8px;border-radius:50%;background:var(--color-border)}
        .step-dot.done{background:var(--color-primary)}
        .step-dot.active{background:var(--color-primary);width:24px;border-radius:4px}
    </style>
</head>
<body>

<?php require VIEWS_PATH . '/partials/toasts.php'; ?>

<div class="auth-card mx-3">

    <div class="text-center mb-3">
        <a href="<?= APP_URL ?>" class="text-decoration-none">
            <div class="fw-black fs-4" style="color:var(--color-primary)">
                <i class="bi bi-heart-fill me-1"></i><?= e(APP_NAME) ?>
            </div>
        </a>
    </div>

    <!-- Indicador de pasos -->
    <div class="step-dots">
        <div class="step-dot done"></div>
        <div class="step-dot done"></div>
        <div class="step-dot done"></div>
        <div class="step-dot done"></div>
        <div class="step-dot active"></div>
    </div>

    <!-- Ícono -->
    <div class="text-center mb-3">
        <div style="width:64px;height:64px;border-radius:50%;background:rgba(255,45,117,.1);border:2px solid rgba(255,45,117,.25);
                    display:flex;align-items:center;justify-content:center;margin:0 auto;font-size:1.8rem">
            <i class="bi bi-shield-lock-fill" style="color:var(--color-primary)"></i>
        </div>
    </div>

    <h1 class="h5 fw-bold mb-1 text-center">¡Casi listo!</h1>
    <p class="text-muted text-center mb-4" style="font-size:.83rem">
        Elige un nombre y crea tu contraseña para completar el registro
    </p>

    <form method="POST" action="<?= APP_URL ?>/registro/completar"
          data-validate-form novalidate autocomplete="on">
        <?= $csrfField ?>

        <!-- Nombre -->
        <div class="mb-3">
            <label for="nombre" class="form-label">
                <i class="bi bi-person me-1"></i>Nombre o apodo <span class="text-danger">*</span>
            </label>
            <input type="text"
                   id="nombre"
                   name="nombre"
                   class="form-control"
                   placeholder="Cómo quieres que te llamen"
                   maxlength="120"
                   autocomplete="nickname"
                   autofocus
                   required
                   data-validate="required|minLen:2|maxLen:120|noScript">
            <div class="form-text text-muted" style="font-size:.74rem">
                Puede ser tu nombre real, artístico o un apodo.
            </div>
        </div>

        <!-- Contraseña -->
        <div class="mb-3">
            <label for="password" class="form-label">
                <i class="bi bi-lock me-1"></i>Contraseña <span class="text-danger">*</span>
            </label>
            <div class="input-group">
                <input type="password"
                       id="password"
                       name="password"
                       class="form-control"
                       placeholder="Mínimo 8 caracteres"
                       autocomplete="new-password"
                       required
                       data-validate="required|strongPassword"
                       data-password-strength="pass-strength">
                <button type="button"
                        id="togglePass"
                        style="background:var(--color-bg-card2);border:1.5px solid var(--color-border);border-left:none;
                               color:var(--color-text-muted);cursor:pointer;padding:.55rem .75rem;
                               border-radius:0 var(--radius-sm) var(--radius-sm) 0">
                    <i class="bi bi-eye" id="eyeIcon"></i>
                </button>
            </div>
            <div id="pass-strength"></div>
        </div>

        <!-- Confirmar contraseña -->
        <div class="mb-4">
            <label for="password_confirm" class="form-label">
                <i class="bi bi-lock-fill me-1"></i>Confirmar contraseña <span class="text-danger">*</span>
            </label>
            <input type="password"
                   id="password_confirm"
                   name="password_confirm"
                   class="form-control"
                   placeholder="Repite tu contraseña"
                   autocomplete="new-password"
                   required
                   data-validate="required"
                   data-match="password">
        </div>

        <button type="submit" class="btn btn-primary w-100 btn-lg">
            <i class="bi bi-person-check me-2"></i>Crear mi cuenta
        </button>
    </form>

</div>

<script src="<?= APP_URL ?>/public/assets/vendor/bootstrap/bootstrap.bundle.min.js"></script>
<script src="<?= APP_URL ?>/public/assets/js/validation.js"></script>
<script src="<?= APP_URL ?>/public/assets/js/app.js"></script>
<script>
    document.getElementById('togglePass')?.addEventListener('click', function () {
        const input = document.getElementById('password');
        const icon  = document.getElementById('eyeIcon');
        input.type  = input.type === 'text' ? 'password' : 'text';
        icon.className = input.type === 'text' ? 'bi bi-eye-slash' : 'bi bi-eye';
    });
</script>
</body>
</html>
