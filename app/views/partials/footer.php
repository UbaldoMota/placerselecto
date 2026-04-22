<footer class="site-footer">
    <div class="container">
        <div class="row gy-4">

            <!-- Logo + descripción -->
            <div class="col-12 col-md-4">
                <div class="fw-bold fs-5 mb-2" style="color:var(--color-text)">
                    <i class="bi bi-heart-fill text-primary me-1"></i>
                    <?= e(APP_NAME) ?>
                </div>
                <p class="mb-3" style="font-size:.82rem">
                    Plataforma de clasificados publicitarios para adultos.
                    Solo para mayores de 18 años. Publicidad legal, sin contenido explícito.
                </p>
                <div class="d-flex gap-3">
                    <span class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-25 px-2 py-1" style="font-size:.7rem">
                        <i class="bi bi-person-fill-exclamation me-1"></i>+18
                    </span>
                    <span class="badge px-2 py-1"
                          style="font-size:.7rem;background:rgba(0,0,0,.04);color:var(--color-text-muted)">
                        <i class="bi bi-shield-check me-1"></i>Sitio seguro
                    </span>
                </div>
            </div>

            <!-- Links -->
            <div class="col-6 col-md-2 offset-md-2">
                <div class="text-uppercase fw-semibold mb-3" style="font-size:.7rem;letter-spacing:.8px;color:var(--color-text-muted)">
                    Navegar
                </div>
                <ul class="list-unstyled mb-0" style="font-size:.85rem">
                    <li class="mb-2"><a href="<?= APP_URL ?>"><i class="bi bi-house me-1"></i>Inicio</a></li>
                    <li class="mb-2"><a href="<?= APP_URL ?>/anuncios"><i class="bi bi-grid me-1"></i>Anuncios</a></li>
                    <li class="mb-2"><a href="<?= APP_URL ?>/registro"><i class="bi bi-person-plus me-1"></i>Registro</a></li>
                    <li class="mb-2"><a href="<?= APP_URL ?>/login"><i class="bi bi-box-arrow-in-right me-1"></i>Login</a></li>
                </ul>
            </div>

            <div class="col-6 col-md-2">
                <div class="text-uppercase fw-semibold mb-3" style="font-size:.7rem;letter-spacing:.8px;color:var(--color-text-muted)">
                    Legal
                </div>
                <ul class="list-unstyled mb-0" style="font-size:.85rem">
                    <li class="mb-2"><a href="<?= APP_URL ?>/terminos"><i class="bi bi-file-text me-1"></i>Términos</a></li>
                    <li class="mb-2"><a href="<?= APP_URL ?>/privacidad"><i class="bi bi-lock me-1"></i>Privacidad</a></li>
                    <li class="mb-2"><a href="<?= APP_URL ?>/mayores-18"><i class="bi bi-person-badge me-1"></i>Aviso +18</a></li>
                </ul>
            </div>

            <!-- Advertencia legal -->
            <div class="col-12 col-md-2">
                <div class="text-uppercase fw-semibold mb-3" style="font-size:.7rem;letter-spacing:.8px;color:var(--color-text-muted)">
                    Aviso
                </div>
                <p style="font-size:.75rem;line-height:1.5">
                    Este sitio contiene publicidad para adultos. Al acceder confirmas ser mayor de 18 años y aceptar nuestros términos.
                </p>
            </div>

        </div><!-- /row -->

        <hr style="border-color:var(--color-border);margin:1.5rem 0 1rem">

        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2" style="font-size:.78rem">
            <span>&copy; <?= date('Y') ?> <?= e(APP_NAME) ?>. Todos los derechos reservados.</span>
            <div class="d-flex gap-3">
                <a href="<?= APP_URL ?>/terminos">Términos</a>
                <a href="<?= APP_URL ?>/privacidad">Privacidad</a>
                <a href="<?= APP_URL ?>/mayores-18">+18</a>
            </div>
        </div>

    </div>
</footer>
