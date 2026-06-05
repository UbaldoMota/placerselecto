<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="<?= e(Middleware::generateCsrfToken()) ?>">
    <meta name="app-url" content="<?= e(APP_URL) ?>">

    <!-- RTA (Restricted To Adults) — los filtros de control parental reconocen este tag y bloquean el sitio para menores -->
    <meta name="rating" content="adult">
    <meta name="rating" content="RTA-5042-1996-1400-1577-RTA">

    <title><?= e($pageTitle ?? 'Inicio') ?> | <?= e(APP_NAME) ?></title>
    <meta name="description" content="<?= e($pageDescription ?? 'Plataforma de clasificados para adultos. Solo mayores de 18 años.') ?>">

    <?php require VIEWS_PATH . '/partials/head-meta.php'; ?>

    <!-- Fuente Inter (self-hosted) -->
    <link rel="stylesheet" href="<?= asset('assets/vendor/inter/inter.css') ?>">
    <!-- Bootstrap 5.3 -->
    <link rel="stylesheet" href="<?= asset('assets/vendor/bootstrap/bootstrap.min.css') ?>">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="<?= asset('assets/vendor/bootstrap-icons/bootstrap-icons.min.css') ?>">
    <!-- App CSS -->
    <link rel="stylesheet" href="<?= asset('assets/css/app.css') ?>">

    <?php if (isset($extraCss)): ?>
        <?= $extraCss ?>
    <?php endif; ?>
</head>
<?php
$showAgeGate = $GLOBALS['show_age_gate'] ?? false;
$showWelcome = $GLOBALS['show_welcome'] ?? false;
?>
<body>

    <!-- Global loader overlay -->
    <?php require VIEWS_PATH . '/partials/global-loader.php'; ?>

    <!-- Cookie consent banner -->
    <?php require VIEWS_PATH . '/partials/cookie-banner.php'; ?>

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

    <?php if ($showWelcome): ?>
    <!-- =====================================================
         MODAL DE BIENVENIDA — etapa de lanzamiento
         Aparece una sola vez, tras confirmar la mayoría de edad.
         ===================================================== -->
    <?php
        $welcomeRol = $currentUser['rol'] ?? '';
        if (!$currentUser) {
            $welcomeCtaUrl  = APP_URL . '/registro';
            $welcomeCtaText = 'Crear mi perfil gratis';
        } elseif ($welcomeRol === 'usuario') {
            $welcomeCtaUrl  = APP_URL . '/perfil/nuevo';
            $welcomeCtaText = 'Crear mi perfil';
        } else {
            $welcomeCtaUrl  = '';
            $welcomeCtaText = '';
        }
    ?>
    <div class="modal fade" id="welcomeModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 text-center" style="border-radius:var(--radius-lg);overflow:hidden">
                <div class="modal-body p-4 p-md-5">
                    <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Cerrar"></button>

                    <div class="age-gate-icon">
                        <i class="bi bi-rocket-takeoff"></i>
                    </div>

                    <h2 class="h4 fw-bold mb-2">¡Estamos arrancando! 🚀</h2>
                    <p class="text-muted mb-3" style="font-size:.95rem">
                        <strong style="color:var(--color-text)"><?= e(APP_NAME) ?></strong> es el directorio de anuncios
                        para adultos en México. Conecta de forma <strong style="color:var(--color-text)">directa, segura
                        y sin intermediarios</strong>.
                    </p>

                    <!-- Puntos de confianza (para anunciantes y visitantes) -->
                    <div class="text-start rounded-3 p-3 mb-3" style="background:rgba(255,45,117,.05);border:1px solid var(--color-border)">
                        <div class="d-flex align-items-start mb-2">
                            <i class="bi bi-chat-dots-fill text-primary me-2" style="margin-top:.15rem"></i>
                            <span style="font-size:.88rem">
                                <strong style="color:var(--color-text)">Contacto directo</strong> por WhatsApp o Telegram.
                                Sin comisiones de por medio.
                            </span>
                        </div>
                        <div class="d-flex align-items-start mb-2">
                            <i class="bi bi-geo-alt-fill text-primary me-2" style="margin-top:.15rem"></i>
                            <span style="font-size:.88rem">
                                Cobertura en <strong style="color:var(--color-text)">todos los estados y municipios</strong>
                                de la República.
                            </span>
                        </div>
                        <div class="d-flex align-items-start">
                            <i class="bi bi-gift-fill text-primary me-2" style="margin-top:.15rem"></i>
                            <span style="font-size:.88rem">
                                <strong style="color:var(--color-text)">Anunciarte es gratis.</strong>
                                Tú decides cuándo destacar tu perfil.
                            </span>
                        </div>
                    </div>

                    <p class="text-muted mb-4" style="font-size:.9rem">
                        Estamos sumando los primeros perfiles —
                        <strong style="color:var(--color-text)">sé de los primeros en aparecer</strong>.
                    </p>

                    <button type="button" class="btn btn-primary btn-lg w-100 mb-2" data-bs-dismiss="modal">
                        <i class="bi bi-compass me-2"></i>Explorar
                    </button>
                    <?php if ($welcomeCtaUrl): ?>
                    <a href="<?= e($welcomeCtaUrl) ?>" class="btn btn-outline-primary w-100">
                        <i class="bi bi-person-plus me-2"></i><?= e($welcomeCtaText) ?>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var el = document.getElementById('welcomeModal');
            if (el && window.bootstrap) {
                // El banner de promo (sticky, z-index alto) se encima al modal:
                // lo ocultamos mientras el modal está abierto y lo restauramos al cerrar.
                el.addEventListener('show.bs.modal', function () {
                    var pb = document.getElementById('promoBanner');
                    if (pb) pb.style.visibility = 'hidden';
                });
                el.addEventListener('hidden.bs.modal', function () {
                    var pb = document.getElementById('promoBanner');
                    if (pb) pb.style.visibility = '';
                });
                new bootstrap.Modal(el).show();
            }
        });
    </script>
    <?php endif; ?>

    <!-- Bootstrap JS -->
    <script src="<?= APP_URL ?>/public/assets/vendor/bootstrap/bootstrap.bundle.min.js"></script>
    <!-- App JS -->
    <script src="<?= APP_URL ?>/public/assets/js/validation.js"></script>
    <script src="<?= APP_URL ?>/public/assets/js/app.js"></script>
    <script src="<?= APP_URL ?>/public/assets/js/common.js"></script>
    <script src="<?= APP_URL ?>/public/assets/js/loader.js"></script>
    <script src="<?= APP_URL ?>/public/assets/js/cookie-banner.js"></script>
    <?php if ($currentUser): ?>
    <script src="<?= APP_URL ?>/public/assets/js/notifications.js" defer></script>
    <?php endif; ?>

    <?php if (isset($extraJs)): ?>
        <?= $extraJs ?>
    <?php endif; ?>
</body>
</html>
