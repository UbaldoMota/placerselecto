<?php
/**
 * navbar.php
 * Barra de navegación principal.
 * Usa $currentUser inyectado por el Controller base.
 */
$isAdmin        = ($currentUser['rol'] ?? '') === 'admin';
$isComentarista = ($currentUser['rol'] ?? '') === 'comentarista';
?>
<nav class="navbar-main">
    <div class="container">
        <div class="d-flex align-items-center justify-content-between w-100 gap-3">

            <!-- Logo -->
            <a class="navbar-brand d-flex align-items-center gap-2 text-decoration-none" href="<?= APP_URL ?>">
                <i class="bi bi-heart-fill"></i>
                <span class="brand-text"><?= e(APP_NAME) ?></span>
            </a>

            <!-- Acciones derecha -->
            <div class="d-flex align-items-center gap-2">

                <?php if ($currentUser): ?>
                    <!-- Usuario autenticado -->

                    <?php if ($isAdmin): ?>
                        <a href="<?= APP_URL ?>/admin"
                           class="btn btn-sm btn-outline-primary d-none d-sm-inline-flex align-items-center gap-1">
                            <i class="bi bi-shield-check"></i>
                            <span class="d-none d-lg-inline">Admin</span>
                        </a>
                    <?php endif; ?>

                    <?php if (!$isComentarista): ?>
                    <a href="<?= APP_URL ?>/perfil/nuevo"
                       class="btn btn-sm btn-primary d-flex align-items-center gap-1">
                        <i class="bi bi-plus-lg"></i>
                        <span class="d-none d-sm-inline">Nuevo perfil</span>
                    </a>
                    <?php else: ?>
                    <a href="<?= APP_URL ?>/perfiles"
                       class="btn btn-sm btn-primary d-flex align-items-center gap-1">
                        <i class="bi bi-compass"></i>
                        <span class="d-none d-sm-inline">Explorar</span>
                    </a>
                    <?php endif; ?>

                    <!-- Campanita de notificaciones -->
                    <div class="dropdown" id="notif-dropdown-wrap">
                        <button class="btn btn-sm btn-secondary position-relative notif-bell"
                                type="button" data-bs-toggle="dropdown" aria-expanded="false"
                                aria-label="Notificaciones" id="notif-bell-btn">
                            <i class="bi bi-bell-fill"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger notif-badge d-none"
                                  id="notif-badge" style="font-size:.62rem">0</span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end notif-dropdown p-0"
                             style="background:var(--color-bg-card);border-color:var(--color-border);width:360px;max-width:92vw">
                            <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom"
                                 style="border-color:var(--color-border) !important">
                                <strong style="font-size:.85rem">Notificaciones</strong>
                                <button type="button" class="btn btn-sm btn-link p-0 text-primary"
                                        id="notif-mark-all" style="font-size:.75rem;text-decoration:none">
                                    Marcar todas leídas
                                </button>
                            </div>
                            <div id="notif-list" class="notif-list" style="max-height:380px;overflow-y:auto">
                                <div class="text-center text-muted py-4" style="font-size:.82rem">
                                    <i class="bi bi-hourglass-split"></i> Cargando…
                                </div>
                            </div>
                            <div class="border-top text-center py-2"
                                 style="border-color:var(--color-border) !important">
                                <a href="<?= APP_URL ?>/notificaciones" class="text-primary"
                                   style="font-size:.8rem;text-decoration:none">
                                    Ver todas <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Dropdown usuario -->
                    <div class="dropdown">
                        <button class="btn btn-sm btn-secondary d-flex align-items-center gap-2"
                                type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle"></i>
                            <span class="d-none d-md-inline"><?= e($currentUser['nombre']) ?></span>
                            <i class="bi bi-chevron-down" style="font-size:.65rem"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end"
                            style="background:var(--color-bg-card);border-color:var(--color-border)">
                            <li>
                                <span class="dropdown-item-text text-muted" style="font-size:.75rem">
                                    <?= e($currentUser['email']) ?>
                                </span>
                            </li>
                            <li><hr class="dropdown-divider" style="border-color:var(--color-border)"></li>
                            <?php if ($isComentarista): ?>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2"
                                   href="<?= APP_URL ?>/perfiles"
                                   style="color:var(--color-text)">
                                    <i class="bi bi-compass text-primary"></i> Explorar perfiles
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2"
                                   href="<?= APP_URL ?>/notificaciones"
                                   style="color:var(--color-text)">
                                    <i class="bi bi-bell text-primary"></i> Notificaciones
                                </a>
                            </li>
                            <?php else: ?>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2"
                                   href="<?= APP_URL ?>/dashboard"
                                   style="color:var(--color-text)">
                                    <i class="bi bi-grid text-primary"></i> Mi panel
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2"
                                   href="<?= APP_URL ?>/mis-perfiles"
                                   style="color:var(--color-text)">
                                    <i class="bi bi-person-lines-fill text-primary"></i> Mis perfiles
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2"
                                   href="<?= APP_URL ?>/mis-tokens"
                                   style="color:var(--color-text)">
                                    <i class="bi bi-coin text-primary"></i> Mis tokens
                                </a>
                            </li>
                            <?php endif; ?>
                            <?php if ($isAdmin): ?>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2"
                                   href="<?= APP_URL ?>/admin"
                                   style="color:var(--color-text)">
                                    <i class="bi bi-shield-check text-warning"></i> Panel admin
                                </a>
                            </li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider" style="border-color:var(--color-border)"></li>
                            <li>
                                <form method="POST" action="<?= APP_URL ?>/logout" class="m-0">
                                    <?= $csrfField ?>
                                    <button type="submit"
                                            class="dropdown-item d-flex align-items-center gap-2"
                                            style="color:#FF2D75">
                                        <i class="bi bi-box-arrow-right"></i> Cerrar sesión
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>

                <?php else: ?>
                    <!-- Usuario no autenticado -->
                    <a href="<?= APP_URL ?>/login"
                       class="btn btn-sm btn-secondary">
                        <i class="bi bi-person me-1"></i>Entrar
                    </a>
                    <a href="<?= APP_URL ?>/registro"
                       class="btn btn-sm btn-primary">
                        <i class="bi bi-plus-circle me-1"></i>Registrarse
                    </a>
                <?php endif; ?>

            </div><!-- /acciones -->
        </div><!-- /flex row -->

    </div><!-- /container -->
</nav>

<?php
// Banner de estado de verificación (solo para usuarios normales, no admin)
if ($currentUser && !$isAdmin):
    $estadoVer = $currentUser['estado_verificacion'];
    $verificado = $currentUser['verificado'];
?>
<?php if (!$verificado && $estadoVer === 'pendiente'): ?>
<div class="container mt-3">
    <div class="verification-banner pendiente">
        <i class="bi bi-clock-history text-warning"></i>
        <div>
            <strong>Cuenta en revisión.</strong>
            Tu perfil está siendo verificado por nuestro equipo.
            Podrás publicar anuncios una vez aprobado. Este proceso tarda menos de 24 horas.
        </div>
    </div>
</div>
<?php elseif ($estadoVer === 'rechazado' || $estadoVer === 'suspendido'): ?>
<div class="container mt-3">
    <div class="verification-banner rechazado">
        <i class="bi bi-x-octagon-fill text-danger"></i>
        <div class="flex-grow-1">
            <strong>Cuenta <?= $estadoVer === 'rechazado' ? 'rechazada' : 'suspendida' ?>.</strong>
            No puedes crear perfiles mientras esté bloqueada.
            <a href="<?= APP_URL ?>/cuenta/reactivar" class="fw-bold text-danger">
                Solicitar reactivación →
            </a>
        </div>
    </div>
</div>
<?php endif; ?>
<?php endif; ?>
