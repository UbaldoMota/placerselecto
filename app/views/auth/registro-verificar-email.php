<?php /** auth/registro-verificar-email.php — Paso 3b: código email */ ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="<?= e(Middleware::generateCsrfToken()) ?>">
    <title><?= e($pageTitle ?? 'Verificar correo') ?> | <?= e(APP_NAME) ?></title>
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


<?php require VIEWS_PATH . '/partials/global-loader.php'; ?>

<?php require VIEWS_PATH . '/partials/toasts.php'; ?>
<?php require VIEWS_PATH . '/partials/cookie-banner.php'; ?>

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
        <div class="step-dot done"></div>
        <div class="step-dot active"></div>
    </div>

    <!-- Ícono -->
    <div class="text-center mb-3">
        <div style="width:64px;height:64px;border-radius:50%;background:rgba(255,45,117,.1);border:2px solid rgba(255,45,117,.25);
                    display:flex;align-items:center;justify-content:center;margin:0 auto;font-size:1.8rem">
            <i class="bi bi-envelope-fill" style="color:var(--color-primary)"></i>
        </div>
    </div>

    <h1 class="h5 fw-bold mb-1 text-center">Verifica tu correo</h1>
    <p class="text-muted text-center mb-1" style="font-size:.83rem">
        Enviamos un código de 6 dígitos a
    </p>
    <p class="text-center fw-semibold mb-4" style="font-size:.9rem;color:var(--color-text)">
        <?= e($email ?? '') ?>
        <button type="button"
                class="btn btn-sm ms-2"
                style="font-size:.72rem;padding:.1rem .45rem;background:rgba(0,0,0,.04);border:1px solid var(--color-border);color:var(--color-text-muted)"
                data-toggle-display="formCorregirWrap">
            <i class="bi bi-pencil me-1"></i>Corregir
        </button>
    </p>

    <!-- Form corregir email (oculto por defecto) -->
    <div id="formCorregirWrap" style="display:none" class="mb-4">
        <form method="POST" action="<?= APP_URL ?>/registro/corregir-email">
            <?= $csrfField ?>
            <div class="input-group">
                <input type="email"
                       name="email"
                       class="form-control"
                       placeholder="nuevo@correo.com"
                       value="<?= e($email ?? '') ?>"
                       required
                       autocomplete="email">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-send me-1"></i>Enviar
                </button>
            </div>
            <div class="form-text text-muted" style="font-size:.74rem">
                Se enviará un nuevo código al correo que introduzcas.
            </div>
        </form>
    </div>

    <!-- Formulario verificación -->
    <form method="POST" action="<?= APP_URL ?>/registro/verificar-email" novalidate>
        <?= $csrfField ?>

        <div class="mb-4">
            <input type="text"
                   id="codigo" data-number-only data-number-max="6"
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
        <form method="POST" action="<?= APP_URL ?>/registro/reenviar-email" class="d-inline">
            <?= $csrfField ?>
            <button type="submit"
                    class="btn-reenviar"
                    data-countdown-seconds="<?= (int)($segundosRestantes ?? 0) ?>"
                    data-countdown-display="secs"
                    data-countdown-wrap="countdown">
                Reenviar correo
                <span id="countdown"
                      <?= ($segundosRestantes ?? 0) <= 0 ? 'style="display:none"' : '' ?>>
                    (<span id="secs"><?= (int)($segundosRestantes ?? 0) ?></span>s)
                </span>
            </button>
        </form>
    </div>

</div>

<script src="<?= APP_URL ?>/public/assets/vendor/bootstrap/bootstrap.bundle.min.js"></script>
<script src="<?= APP_URL ?>/public/assets/js/app.js"></script>
<script src="<?= APP_URL ?>/public/assets/js/common.js"></script>
<script src="<?= APP_URL ?>/public/assets/js/loader.js"></script>
<script src="<?= APP_URL ?>/public/assets/js/cookie-banner.js"></script>
</body>
</html>
