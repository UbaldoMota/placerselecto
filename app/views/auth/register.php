<?php
/**
 * register.php — Vista de registro de usuario.
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
    <title><?= e($pageTitle ?? 'Registro') ?> | <?= e(APP_NAME) ?></title>
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
                radial-gradient(ellipse at 90% 80%, rgba(255,45,117,.07) 0%, transparent 50%),
                radial-gradient(ellipse at 10% 10%, rgba(255,127,168,.12) 0%, transparent 50%);
            padding: 2rem 0;
        }
        .auth-card {
            background: var(--color-bg-card);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-lg);
            padding: 2.5rem 2rem;
            width: 100%;
            max-width: 480px;
            box-shadow: var(--shadow-lg);
        }
        .step-badge {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            background: rgba(255,45,117,.12);
            color: var(--color-primary);
            border-radius: 20px;
            padding: .2rem .75rem;
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
        }
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
        <p class="text-muted mt-1 mb-0" style="font-size:.85rem">Crea tu cuenta — es gratis</p>
    </div>

    <!-- Aviso de verificación -->
    <div class="alert py-2 px-3 mb-4"
         style="background:rgba(255,193,7,.08);border:1px solid rgba(255,193,7,.2);border-radius:var(--radius-sm);font-size:.8rem;color:#ffd44d">
        <i class="bi bi-info-circle me-2"></i>
        Tu cuenta será revisada antes de poder publicar. Proceso rápido (&lt; 24 h).
    </div>

    <form method="POST"
          action="<?= APP_URL ?>/registro"
          data-validate-form
          novalidate
          autocomplete="on">

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
                   value="<?= e($old['nombre'] ?? '') ?>"
                   maxlength="120"
                   autocomplete="name"
                   required
                   data-validate="required|minLen:2|maxLen:120|noScript">
        </div>

        <!-- Email -->
        <div class="mb-3">
            <label for="email" class="form-label">
                <i class="bi bi-envelope me-1"></i>Email <span class="text-danger">*</span>
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
            <div class="form-text text-muted" style="font-size:.75rem">
                No la compartiremos con nadie.
            </div>
        </div>

        <!-- Teléfono (opcional) -->
        <div class="mb-3">
            <label for="telefono" class="form-label">
                <i class="bi bi-whatsapp me-1"></i>WhatsApp / Teléfono
                <span class="text-muted" style="font-size:.75rem">(opcional)</span>
            </label>
            <input type="tel"
                   id="telefono"
                   name="telefono"
                   class="form-control"
                   placeholder="+52 55 1234 5678"
                   value="<?= e($old['telefono'] ?? '') ?>"
                   autocomplete="tel"
                   data-validate="phone">
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
                <button type="button" class="show-pass-btn" data-toggle-password="password" data-eye-icon="eyeIcon"
                        style="background:var(--color-bg-card2);border:1.5px solid var(--color-border);border-left:none;color:var(--color-text-muted);cursor:pointer;padding:.55rem .75rem;border-radius:0 var(--radius-sm) var(--radius-sm) 0">
                    <i class="bi bi-eye" id="eyeIcon"></i>
                </button>
            </div>
            <!-- Indicador de fortaleza -->
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

        <!-- Términos y condiciones -->
        <div class="mb-4">
            <div class="form-check">
                <input type="checkbox"
                       class="form-check-input"
                       id="aceptar_terminos"
                       name="aceptar_terminos"
                       value="1"
                       required>
                <label class="form-check-label" for="aceptar_terminos" style="font-size:.83rem">
                    Soy mayor de <strong>18 años</strong> y acepto los
                    <a href="<?= APP_URL ?>/terminos" target="_blank">Términos y condiciones</a>
                    y el
                    <a href="<?= APP_URL ?>/privacidad" target="_blank">Aviso de privacidad</a>.
                </label>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 btn-lg">
            <i class="bi bi-person-check me-2"></i>Crear mi cuenta
        </button>

    </form>

    <div class="text-center mt-3" style="font-size:.85rem;color:var(--color-text-muted)">
        ¿Ya tienes cuenta?
        <a href="<?= APP_URL ?>/login" class="fw-semibold">Inicia sesión</a>
    </div>

</div>

<script src="<?= APP_URL ?>/public/assets/vendor/bootstrap/bootstrap.bundle.min.js"></script>
<script src="<?= APP_URL ?>/public/assets/js/validation.js"></script>
<script src="<?= APP_URL ?>/public/assets/js/app.js"></script>
<script src="<?= APP_URL ?>/public/assets/js/common.js"></script>
</body>
</html>
