<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Verificación de edad | <?= e(APP_NAME) ?></title>
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
            background-image: radial-gradient(ellipse at 20% 50%, rgba(255,45,117,.08) 0%, transparent 50%),
                              radial-gradient(ellipse at 80% 20%, rgba(255,127,168,.15) 0%, transparent 50%);
        }
        .age-gate-card {
            background: var(--color-bg-card);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-lg);
            padding: 2.5rem 2rem;
            max-width: 460px;
            width: 100%;
            text-align: center;
            box-shadow: var(--shadow-lg);
        }
        .age-icon {
            width: 80px;
            height: 80px;
            background: rgba(255,45,117,.12);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
            color: var(--color-primary);
            margin: 0 auto 1.5rem;
            border: 2px solid rgba(255,45,117,.25);
        }
    </style>
</head>
<body>

<div class="age-gate-card mx-3">

    <div class="age-icon">
        <i class="bi bi-person-fill-exclamation"></i>
    </div>

    <h1 class="h4 fw-bold mb-2">Contenido para adultos</h1>
    <p class="text-muted mb-1" style="font-size:.9rem">
        Este sitio contiene publicidad de servicios para adultos.
    </p>
    <p class="text-muted mb-4" style="font-size:.9rem">
        Para continuar debes confirmar que eres mayor de <strong class="text-white">18 años</strong>
        y aceptar los <a href="<?= APP_URL ?>/terminos" target="_blank">términos de uso</a>.
    </p>

    <!-- Aviso legal compacto -->
    <div class="rounded-3 p-3 mb-4 text-start" style="background:rgba(0,0,0,.03);border:1px solid var(--color-border);font-size:.78rem;color:var(--color-text-muted)">
        <i class="bi bi-info-circle text-primary me-1"></i>
        Este portal funciona como intermediario publicitario. <strong class="text-white">No aloja ni vende contenido explícito.</strong>
        El acceso es exclusivo para mayores de edad conforme a la ley aplicable en tu país.
    </div>

    <form method="POST" action="<?= APP_URL ?>/verificar-edad">
        <?= $csrfField ?? '' ?>
        <input type="hidden" name="confirm_age" value="1">
        <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">
            <i class="bi bi-check-circle me-2"></i>Soy mayor de 18 años — Entrar
        </button>
    </form>

    <form method="POST" action="<?= APP_URL ?>/verificar-edad">
        <?= $csrfField ?? '' ?>
        <input type="hidden" name="confirm_age" value="0">
        <button type="submit" class="btn btn-secondary w-100" style="font-size:.85rem">
            <i class="bi bi-x-circle me-2"></i>Soy menor de edad — Salir
        </button>
    </form>

    <div class="mt-4 d-flex justify-content-center gap-3" style="font-size:.75rem">
        <a href="<?= APP_URL ?>/terminos"   target="_blank">Términos y condiciones</a>
        <a href="<?= APP_URL ?>/privacidad" target="_blank">Aviso de privacidad</a>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
