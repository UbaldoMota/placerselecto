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
                    Plataforma de clasificados publicitarios para adultos verificados.
                    Solo para mayores de 18 años. Contenido para adultos.
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
                    <li class="mb-2"><a href="<?= APP_URL ?>/contacto"><i class="bi bi-envelope me-1"></i>Contacto</a></li>
                </ul>
            </div>

            <div class="col-6 col-md-2">
                <div class="text-uppercase fw-semibold mb-3" style="font-size:.7rem;letter-spacing:.8px;color:var(--color-text-muted)">
                    Legal
                </div>
                <ul class="list-unstyled mb-0" style="font-size:.85rem">
                    <li class="mb-2"><a href="<?= APP_URL ?>/terminos"><i class="bi bi-file-text me-1"></i>Términos</a></li>
                    <li class="mb-2"><a href="<?= APP_URL ?>/privacidad"><i class="bi bi-lock me-1"></i>Privacidad</a></li>
                    <li class="mb-2"><a href="<?= APP_URL ?>/cookies"><i class="bi bi-cookie me-1"></i>Cookies</a></li>
                    <li class="mb-2"><a href="<?= APP_URL ?>/pagos"><i class="bi bi-credit-card me-1"></i>Pagos</a></li>
                    <li class="mb-2"><a href="<?= APP_URL ?>/mayores-18"><i class="bi bi-person-badge me-1"></i>Aviso +18</a></li>
                    <li class="mb-2"><a href="<?= APP_URL ?>/control-parental"><i class="bi bi-shield-lock me-1"></i>Control parental</a></li>
                    <li class="mb-2"><a href="<?= APP_URL ?>/dmca"><i class="bi bi-c-circle me-1"></i>Derechos de Autor</a></li>
                    <li class="mb-2"><a href="<?= APP_URL ?>/2257"><i class="bi bi-shield-check me-1"></i>Declaración 2257</a></li>
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
            <div class="d-flex gap-3 flex-wrap justify-content-center">
                <a href="<?= APP_URL ?>/terminos">Términos</a>
                <a href="<?= APP_URL ?>/privacidad">Privacidad</a>
                <a href="<?= APP_URL ?>/cookies">Cookies</a>
                <a href="<?= APP_URL ?>/pagos">Pagos</a>
                <a href="<?= APP_URL ?>/mayores-18">+18</a>
                <a href="<?= APP_URL ?>/control-parental">Control parental</a>
                <a href="<?= APP_URL ?>/dmca">DMCA</a>
                <a href="<?= APP_URL ?>/2257">2257</a>
                <a href="<?= APP_URL ?>/contacto">Contacto</a>
            </div>
        </div>

        <!-- Franja final: declaración de edad + insignias + medios de pago + responsable -->
        <hr style="border-color:var(--color-border);margin:1rem 0">

        <div class="text-center" style="font-size:.78rem;color:var(--color-text-muted)">
            <em>Página de contenido adulto +18 años. Todos los usuarios han reconocido tener mínimo 18 años.</em>
        </div>

        <!-- Insignias de cumplimiento -->
        <div class="d-flex flex-wrap gap-2 justify-content-center align-items-center my-3">
            <!-- Métodos de pago -->
            <span class="badge px-2 py-1 d-inline-flex align-items-center gap-1"
                  style="background:#fff;color:#1a1a1a;border:1px solid #e5e5e5;font-size:.72rem;font-weight:600;letter-spacing:.5px">
                VISA
            </span>
            <span class="badge px-2 py-1 d-inline-flex align-items-center gap-1"
                  style="background:#fff;color:#1a1a1a;border:1px solid #e5e5e5;font-size:.72rem;font-weight:600;letter-spacing:.5px">
                <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#eb001b;margin-right:-3px"></span>
                <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#f79e1b"></span>
                Mastercard
            </span>

            <!-- Badge DMCA -->
            <a href="<?= APP_URL ?>/dmca"
               class="badge px-2 py-1 d-inline-flex align-items-center gap-1 text-decoration-none"
               style="background:#003eaa;color:#fff;font-size:.72rem;font-weight:700;letter-spacing:.4px"
               title="Política de Derechos de Autor">
                <i class="bi bi-c-circle"></i>DMCA Protected
            </a>

            <!-- Badge +18 -->
            <span class="badge px-2 py-1 d-inline-flex align-items-center"
                  style="background:#FF2D75;color:#fff;font-size:.72rem;font-weight:700;letter-spacing:.4px"
                  title="Solo para mayores de 18 años">
                +18
            </span>

            <!-- Badge RTA -->
            <a href="https://www.rtalabel.org" target="_blank" rel="noopener nofollow"
               class="badge px-2 py-1 d-inline-flex align-items-center gap-1 text-decoration-none"
               style="background:#1a1a1a;color:#fff;font-size:.72rem;font-weight:700;letter-spacing:.4px"
               title="Sitio etiquetado RTA — bloqueable por software de control parental">
                <i class="bi bi-shield-fill-check"></i>RTA
            </a>
        </div>

    </div>
</footer>
