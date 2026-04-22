<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="<?= e(Middleware::generateCsrfToken()) ?>">
    <meta name="app-url" content="<?= e(APP_URL) ?>">

    <title><?= e($pageTitle ?? 'Inicio') ?> | <?= e(APP_NAME) ?></title>
    <meta name="description" content="<?= e($pageDescription ?? 'Plataforma de clasificados para adultos. Solo mayores de 18 años.') ?>">

    <!-- Fuente Inter (self-hosted) -->
    <link rel="stylesheet" href="<?= APP_URL ?>/public/assets/vendor/inter/inter.css">
    <!-- Bootstrap 5.3 -->
    <link rel="stylesheet" href="<?= APP_URL ?>/public/assets/vendor/bootstrap/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="<?= APP_URL ?>/public/assets/vendor/bootstrap-icons/bootstrap-icons.min.css">
    <!-- App CSS -->
    <link rel="stylesheet" href="<?= APP_URL ?>/public/assets/css/app.css">

    <?php if (isset($extraCss)): ?>
        <?= $extraCss ?>
    <?php endif; ?>
</head>
<?php $showAgeGate = $GLOBALS['show_age_gate'] ?? false; ?>
<body>

    <!-- Toasts (flash messages — se cierran solo al hacer clic) -->
    <?php require VIEWS_PATH . '/partials/toasts.php'; ?>

    <!-- Wrapper con blur cuando el age gate está activo -->
    <div id="page-wrapper" class="<?= $showAgeGate ? 'age-gate-active' : '' ?>">

        <!-- Navbar -->
        <?php require VIEWS_PATH . '/partials/navbar.php'; ?>

        <!-- Contenido principal -->
        <main id="main-content">
            <?php require $content; ?>
        </main>

        <!-- Footer -->
        <?php require VIEWS_PATH . '/partials/footer.php'; ?>

    </div><!-- /page-wrapper -->

    <?php if ($showAgeGate): ?>
    <!-- =====================================================
         AGE GATE MODAL OVERLAY
         ===================================================== -->
    <div class="age-gate-overlay" id="ageGateOverlay">
        <div class="age-gate-modal-box">

            <div class="age-gate-icon">
                <i class="bi bi-person-fill-exclamation"></i>
            </div>

            <h1 class="h4 fw-bold mb-2">Contenido para adultos</h1>
            <p class="text-muted mb-1" style="font-size:.9rem">
                Este sitio contiene publicidad de servicios para adultos.
            </p>
            <p class="text-muted mb-4" style="font-size:.9rem">
                Para continuar debes confirmar que eres mayor de <strong style="color:var(--color-text)">18 años</strong>
                y aceptar los <a href="<?= APP_URL ?>/terminos" target="_blank" style="color:var(--color-primary)">términos de uso</a>.
            </p>

            <!-- Aviso legal -->
            <div class="rounded-3 p-3 mb-4 text-start"
                 style="background:var(--color-bg-alt);border:1px solid var(--color-border);font-size:.78rem;color:var(--color-text-muted)">
                <i class="bi bi-info-circle text-primary me-1"></i>
                Este portal funciona como intermediario publicitario.
                <strong style="color:var(--color-text)">No aloja ni vende contenido explícito.</strong>
                El acceso es exclusivo para mayores de edad conforme a la ley aplicable en tu país.
            </div>

            <!-- Confirmar -->
            <form method="POST" action="<?= APP_URL ?>/verificar-edad" class="mb-3">
                <?= Middleware::csrfField() ?>
                <input type="hidden" name="confirm_age" value="1">
                <button type="submit" class="btn btn-primary btn-lg w-100">
                    <i class="bi bi-check-circle me-2"></i>Soy mayor de 18 años — Entrar
                </button>
            </form>

            <!-- Negar -->
            <form method="POST" action="<?= APP_URL ?>/verificar-edad">
                <?= Middleware::csrfField() ?>
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
    </div>
    <?php endif; ?>

    <!-- Modal de confirmación global -->
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title">
                        <i class="bi bi-exclamation-triangle text-warning me-2"></i>Confirmar acción
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="confirmModalBody">
                    ¿Estás seguro de realizar esta acción?
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger btn-sm" id="confirmModalBtn">Confirmar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="<?= APP_URL ?>/public/assets/vendor/bootstrap/bootstrap.bundle.min.js"></script>
    <!-- App JS -->
    <script src="<?= APP_URL ?>/public/assets/js/validation.js"></script>
    <script src="<?= APP_URL ?>/public/assets/js/app.js"></script>
    <?php if ($currentUser): ?>
    <script src="<?= APP_URL ?>/public/assets/js/notifications.js" defer></script>
    <?php endif; ?>

    <?php if (isset($extraJs)): ?>
        <?= $extraJs ?>
    <?php endif; ?>
</body>
</html>
