<?php /** auth/registro-tipo.php — Paso 1: selección de tipo de cuenta */ ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($pageTitle ?? 'Crear cuenta') ?> | <?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="<?= APP_URL ?>/public/assets/vendor/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/public/assets/vendor/bootstrap-icons/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/public/assets/css/app.css">
    <style>
        body{min-height:100vh;display:flex;align-items:center;justify-content:center;background:var(--color-bg);
             background-image:radial-gradient(ellipse at 90% 80%,rgba(255,45,117,.07) 0%,transparent 50%),
                              radial-gradient(ellipse at 10% 10%,rgba(255,127,168,.12) 0%,transparent 50%);padding:2rem 0}
        .auth-wrap{width:100%;max-width:600px;padding:0 1rem}
        .tipo-card{background:var(--color-bg-card);border:1.5px solid var(--color-border);border-radius:var(--radius-md);
                   padding:2rem 1.5rem;cursor:pointer;transition:border-color .2s,transform .15s;text-decoration:none;display:block;color:inherit}
        .tipo-card:hover{border-color:var(--color-primary);transform:translateY(-2px);color:inherit}
        .tipo-card__icon{width:56px;height:56px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.6rem;margin-bottom:1rem}
        .tipo-card--publicador .tipo-card__icon{background:rgba(255,45,117,.12);color:var(--color-primary)}
        .tipo-card--comentarista .tipo-card__icon{background:rgba(13,110,253,.12);color:#4da3ff}
        .badge-pronto{display:inline-block;background:rgba(0,0,0,.05);color:var(--color-text-muted);
                      border-radius:20px;font-size:.68rem;padding:.1rem .55rem;border:1px solid var(--color-border)}
    </style>
</head>
<body>

<?php require VIEWS_PATH . '/partials/toasts.php'; ?>

<div class="auth-wrap mx-auto">

    <div class="text-center mb-4">
        <a href="<?= APP_URL ?>" class="text-decoration-none">
            <div class="fw-black fs-3" style="color:var(--color-primary)">
                <i class="bi bi-heart-fill me-1"></i><?= e(APP_NAME) ?>
            </div>
        </a>
        <h1 class="h5 fw-bold mt-3 mb-1">¿Qué tipo de cuenta quieres crear?</h1>
        <p class="text-muted mb-0" style="font-size:.85rem">Elige la opción que mejor describe lo que quieres hacer</p>
    </div>

    <div class="d-flex flex-column gap-3">

        <!-- Publicador -->
        <a href="<?= APP_URL ?>/registro/publicador" class="tipo-card tipo-card--publicador">
            <div class="tipo-card__icon">
                <i class="bi bi-person-badge-fill"></i>
            </div>
            <div class="d-flex align-items-start justify-content-between gap-2">
                <div>
                    <div class="fw-bold mb-1">Publicar mi perfil</div>
                    <div class="text-muted" style="font-size:.84rem">
                        Crea y publica tu perfil para ofrecer tus servicios.
                        Requiere verificación de teléfono y correo.
                    </div>
                </div>
                <i class="bi bi-chevron-right text-muted mt-1 flex-shrink-0"></i>
            </div>
        </a>

        <!-- Comentarista -->
        <a href="<?= APP_URL ?>/registro/comentarista" class="tipo-card tipo-card--comentarista">
            <div class="tipo-card__icon">
                <i class="bi bi-chat-dots-fill"></i>
            </div>
            <div class="d-flex align-items-start justify-content-between gap-2">
                <div>
                    <div class="fw-bold mb-1">Dejar comentarios</div>
                    <div class="text-muted" style="font-size:.84rem">
                        Comenta y valora perfiles publicados. Registro rápido con solo email y contraseña.
                    </div>
                </div>
                <i class="bi bi-chevron-right text-muted mt-1 flex-shrink-0"></i>
            </div>
        </a>

    </div>

    <div class="text-center mt-4" style="font-size:.85rem;color:var(--color-text-muted)">
        ¿Ya tienes cuenta?
        <a href="<?= APP_URL ?>/login" class="fw-semibold">Inicia sesión</a>
    </div>

</div>

<script src="<?= APP_URL ?>/public/assets/vendor/bootstrap/bootstrap.bundle.min.js"></script>
<script src="<?= APP_URL ?>/public/assets/js/app.js"></script>
<script src="<?= APP_URL ?>/public/assets/js/common.js"></script>
</body>
</html>
