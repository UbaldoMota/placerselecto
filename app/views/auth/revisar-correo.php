<?php /** auth/revisar-correo.php — pantalla tras registrarse pidiendo revisar el email */ ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Revisa tu correo | <?= e(APP_NAME) ?></title>
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

<?php require VIEWS_PATH . '/partials/global-loader.php'; ?>
<?php require VIEWS_PATH . '/partials/toasts.php'; ?>
<?php require VIEWS_PATH . '/partials/cookie-banner.php'; ?>

<div class="auth-card mx-3 text-center">

    <div class="mb-3">
        <a href="<?= APP_URL ?>" class="text-decoration-none">
            <div class="fw-black fs-3" style="color:var(--color-primary)">
                <i class="bi bi-heart-fill me-1"></i><?= e(APP_NAME) ?>
            </div>
        </a>
    </div>

    <div class="mb-3">
        <div style="width:72px;height:72px;border-radius:50%;background:rgba(255,45,117,.1);border:2px solid rgba(255,45,117,.25);
                    display:flex;align-items:center;justify-content:center;margin:0 auto;font-size:2rem">
            <i class="bi bi-envelope-check-fill" style="color:var(--color-primary)"></i>
        </div>
    </div>

    <h1 class="h5 fw-bold mb-2">¡Revisa tu correo!</h1>
    <p class="text-muted mb-1" style="font-size:.9rem">
        Te enviamos un enlace de confirmación a
    </p>
    <p class="fw-bold mb-4" style="font-size:.95rem;color:var(--color-text)">
        <?= e($email ?? '') ?>
    </p>

    <div class="alert py-2 px-3 mb-3 text-start" style="background:rgba(13,110,253,.08);border:1px solid rgba(13,110,253,.2);font-size:.82rem;color:#3B82F6">
        <i class="bi bi-info-circle-fill me-2"></i>
        Debes hacer clic en el enlace del correo antes de poder iniciar sesión.
    </div>

    <p class="text-muted mb-4" style="font-size:.78rem">
        <strong>¿No lo ves?</strong> Revisa tu carpeta de <em>spam</em> o <em>correo no deseado</em>.
        El enlace expira en 24 horas.
    </p>

    <form method="POST" action="<?= APP_URL ?>/verificar-email/reenviar">
        <?= $csrfField ?>
        <input type="hidden" name="email" value="<?= e($email ?? '') ?>">
        <button type="submit" class="btn btn-secondary w-100 mb-2">
            <i class="bi bi-arrow-clockwise me-1"></i>Reenviar correo
        </button>
    </form>

    <a href="<?= APP_URL ?>/login" class="btn btn-link text-muted" style="font-size:.85rem">
        <i class="bi bi-arrow-left me-1"></i>Volver al login
    </a>

</div>

<script src="<?= APP_URL ?>/public/assets/vendor/bootstrap/bootstrap.bundle.min.js"></script>
<script src="<?= APP_URL ?>/public/assets/js/app.js"></script>
<script src="<?= APP_URL ?>/public/assets/js/common.js"></script>
<script src="<?= APP_URL ?>/public/assets/js/loader.js"></script>
<script src="<?= APP_URL ?>/public/assets/js/cookie-banner.js"></script>
</body>
</html>
