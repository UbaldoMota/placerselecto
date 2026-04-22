<?php
/**
 * login.php — Vista de inicio de sesión.
 * Layout: standalone (sin navbar) para UX limpia.
 */
$old = SessionManager::get('form_old', []);
SessionManager::delete('form_old');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="<?= e(Middleware::generateCsrfToken()) ?>">
    <title><?= e($pageTitle ?? 'Login') ?> | <?= e(APP_NAME) ?></title>
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
            background-image:
                radial-gradient(ellipse at 10% 80%, rgba(255,45,117,.08) 0%, transparent 50%),
                radial-gradient(ellipse at 90% 10%, rgba(255,127,168,.12) 0%, transparent 50%);
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
        .auth-divider {
            display: flex;
            align-items: center;
            gap: .75rem;
            color: var(--color-text-muted);
            font-size: .78rem;
            margin: 1.25rem 0;
        }
        .auth-divider::before,
        .auth-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--color-border);
        }
        .show-pass-btn {
            background: var(--color-bg-card2);
            border: 1.5px solid var(--color-border);
            border-left: none;
            color: var(--color-text-muted);
            cursor: pointer;
            border-radius: 0 var(--radius-sm) var(--radius-sm) 0;
            padding: .55rem .75rem;
            transition: color var(--transition);
        }
        .show-pass-btn:hover { color: var(--color-primary); }
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
        <p class="text-muted mt-1 mb-0" style="font-size:.85rem">Inicia sesión en tu cuenta</p>
    </div>

    <!-- Formulario -->
    <form method="POST"
          action="<?= APP_URL ?>/login"
          data-validate-form
          novalidate
          autocomplete="on">

        <?= $csrfField ?>

        <!-- Email -->
        <div class="mb-3">
            <label for="email" class="form-label">
                <i class="bi bi-envelope me-1"></i>Email
            </label>
            <input type="email"
                   id="email"
                   name="email"
                   class="form-control"
                   placeholder="tu@email.com"
                   value="<?= e($old['email'] ?? '') ?>"
                   autocomplete="email"
                   required
                   data-validate="required|email">
        </div>

        <!-- Contraseña -->
        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center">
                <label for="password" class="form-label mb-0">
                    <i class="bi bi-lock me-1"></i>Contraseña
                </label>
                <a href="<?= APP_URL ?>/recuperar-password"
                   style="font-size:.78rem">¿Olvidaste tu contraseña?</a>
            </div>
            <div class="input-group mt-1">
                <input type="password"
                       id="password"
                       name="password"
                       class="form-control"
                       placeholder="Tu contraseña"
                       autocomplete="current-password"
                       required
                       data-validate="required|minLen:8">
                <button type="button" class="show-pass-btn" id="togglePass" title="Mostrar/ocultar">
                    <i class="bi bi-eye" id="eyeIcon"></i>
                </button>
            </div>
        </div>

        <!-- Recordarme -->
        <div class="mb-4 form-check">
            <input type="checkbox" class="form-check-input" id="remember" name="remember" value="1">
            <label class="form-check-label text-muted" for="remember" style="font-size:.85rem">
                Mantener sesión activa
            </label>
        </div>

        <button type="submit" class="btn btn-primary w-100 btn-lg">
            <i class="bi bi-box-arrow-in-right me-2"></i>Iniciar sesión
        </button>

    </form>

    <div class="auth-divider">¿No tienes cuenta?</div>

    <a href="<?= APP_URL ?>/registro" class="btn btn-secondary w-100">
        <i class="bi bi-person-plus me-2"></i>Crear cuenta gratis
    </a>

    <div class="text-center mt-4" style="font-size:.75rem;color:var(--color-text-muted)">
        <a href="<?= APP_URL ?>/terminos"   class="me-2">Términos</a>
        <a href="<?= APP_URL ?>/privacidad" class="me-2">Privacidad</a>
        <a href="<?= APP_URL ?>">Inicio</a>
    </div>

</div>

<script src="<?= APP_URL ?>/public/assets/vendor/bootstrap/bootstrap.bundle.min.js"></script>
<script src="<?= APP_URL ?>/public/assets/js/validation.js"></script>
<script src="<?= APP_URL ?>/public/assets/js/app.js"></script>
<script>
    // Toggle mostrar/ocultar contraseña
    document.getElementById('togglePass')?.addEventListener('click', function () {
        const input   = document.getElementById('password');
        const icon    = document.getElementById('eyeIcon');
        const visible = input.type === 'text';
        input.type    = visible ? 'password' : 'text';
        icon.className = visible ? 'bi bi-eye' : 'bi bi-eye-slash';
    });
</script>
</body>
</html>
