<?php /** auth/registro-verificar-sms.php — Paso 3a: código SMS */ ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="<?= e(Middleware::generateCsrfToken()) ?>">
    <title><?= e($pageTitle ?? 'Verificar teléfono') ?> | <?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="<?= APP_URL ?>/public/assets/vendor/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/public/assets/vendor/bootstrap-icons/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/public/assets/css/app.css">
    <style>
        body{min-height:100vh;display:flex;align-items:center;justify-content:center;background:var(--color-bg);
             background-image:radial-gradient(ellipse at 90% 80%,rgba(255,45,117,.07) 0%,transparent 50%),
                              radial-gradient(ellipse at 10% 10%,rgba(255,127,168,.12) 0%,transparent 50%);padding:2rem 0}
        .auth-card{background:var(--color-bg-card);border:1px solid var(--color-border);border-radius:var(--radius-lg);
                   padding:2.5rem 2rem;width:100%;max-width:420px;box-shadow:var(--shadow-lg)}
        .step-dots{display:flex;gap:.5rem;justify-content:center;margin-bottom:1.5rem}
        .step-dot{width:8px;height:8px;border-radius:50%;background:var(--color-border)}
        .step-dot.done{background:var(--color-primary)}
        .step-dot.active{background:var(--color-primary);width:24px;border-radius:4px}
        .code-input{font-size:1.8rem;font-weight:700;letter-spacing:.5rem;text-align:center;
                    background:var(--color-bg-card2)!important;border-color:var(--color-border)!important;
                    color:var(--color-text)!important;padding:.75rem 1rem!important}
        .code-input:focus{border-color:var(--color-primary)!important;box-shadow:0 0 0 3px rgba(255,45,117,.15)!important}
        .btn-reenviar{background:none;border:none;color:var(--color-primary);font-size:.83rem;cursor:pointer;padding:0}
        .btn-reenviar:disabled{color:var(--color-text-muted);cursor:default}
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

    <!-- Pasos -->
    <div class="step-dots">
        <div class="step-dot done"></div>
        <div class="step-dot done"></div>
        <div class="step-dot active"></div>
        <div class="step-dot"></div>
    </div>

    <!-- Ícono -->
    <div class="text-center mb-3">
        <div style="width:64px;height:64px;border-radius:50%;background:rgba(255,45,117,.1);border:2px solid rgba(255,45,117,.25);
                    display:flex;align-items:center;justify-content:center;margin:0 auto;font-size:1.8rem">
            <i class="bi bi-phone-fill" style="color:var(--color-primary)"></i>
        </div>
    </div>

    <h1 class="h5 fw-bold mb-1 text-center">Verifica tu teléfono</h1>
    <p class="text-muted text-center mb-4" style="font-size:.83rem">
        Enviamos un código de 6 dígitos al número<br>
        <strong style="color:var(--color-text)"><?= e($telefono ?? '') ?></strong>
    </p>

    <form method="POST" action="<?= APP_URL ?>/registro/verificar-sms" novalidate>
        <?= $csrfField ?>

        <div class="mb-4">
            <input type="text"
                   id="codigo"
                   name="codigo"
                   class="form-control code-input"
                   placeholder="000000"
                   maxlength="6"
                   inputmode="numeric"
                   pattern="[0-9]{6}"
                   autocomplete="one-time-code"
                   autofocus
                   required>
        </div>

        <button type="submit" class="btn btn-primary w-100 mb-3">
            <i class="bi bi-check-lg me-2"></i>Verificar código
        </button>
    </form>

    <!-- Reenviar -->
    <div class="text-center" style="font-size:.83rem">
        <span class="text-muted">¿No te llegó? </span>
        <form method="POST" action="<?= APP_URL ?>/registro/reenviar-sms" class="d-inline">
            <?= $csrfField ?>
            <button type="submit"
                    class="btn-reenviar"
                    id="btnReenviar"
                    <?= ($segundosRestantes ?? 0) > 0 ? 'disabled' : '' ?>>
                Reenviar SMS
                <span id="countdown"
                      <?= ($segundosRestantes ?? 0) <= 0 ? 'style="display:none"' : '' ?>>
                    (<span id="secs"><?= (int)($segundosRestantes ?? 0) ?></span>s)
                </span>
            </button>
        </form>
    </div>

    <div class="text-center mt-3" style="font-size:.82rem">
        <a href="<?= APP_URL ?>/registro/publicador" class="text-muted">
            <i class="bi bi-arrow-left me-1"></i>Cambiar teléfono o correo
        </a>
    </div>

</div>

<script src="<?= APP_URL ?>/public/assets/vendor/bootstrap/bootstrap.bundle.min.js"></script>
<script src="<?= APP_URL ?>/public/assets/js/app.js"></script>
<script>
(function () {
    // Solo números en el input del código
    const inp = document.getElementById('codigo');
    inp.addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '').slice(0, 6);
    });

    // Countdown reenvío
    let secs = <?= (int)($segundosRestantes ?? 0) ?>;
    const btn       = document.getElementById('btnReenviar');
    const secsEl    = document.getElementById('secs');
    const cdEl      = document.getElementById('countdown');

    if (secs > 0) {
        const timer = setInterval(() => {
            secs--;
            if (secsEl) secsEl.textContent = secs;
            if (secs <= 0) {
                clearInterval(timer);
                if (btn)  btn.disabled = false;
                if (cdEl) cdEl.style.display = 'none';
            }
        }, 1000);
    }
})();
</script>
</body>
</html>
