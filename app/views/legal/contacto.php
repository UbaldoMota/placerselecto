<div class="container py-5" style="max-width:760px">

    <div class="text-center mb-4">
        <div style="width:80px;height:80px;border-radius:50%;background:rgba(255,45,117,.1);border:3px solid rgba(255,45,117,.25);display:flex;align-items:center;justify-content:center;font-size:1.8rem;color:var(--color-primary);margin:0 auto 1.25rem">
            <i class="bi bi-envelope-fill"></i>
        </div>
        <h1 class="h3 fw-bold mb-2">Contacto</h1>
        <p class="text-muted" style="font-size:.9rem;max-width:520px;margin:0 auto">
            ¿Tienes una duda, quieres reportar algo o necesitas soporte? Estamos para escucharte.
        </p>
    </div>

    <!-- Tipos de consulta — orientación al usuario -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h6 fw-bold mb-2 text-primary">
                        <i class="bi bi-question-circle me-1"></i>Soporte general
                    </h2>
                    <p style="font-size:.83rem;line-height:1.6;margin-bottom:0">
                        Dudas sobre cómo usar el Sitio, recuperación de cuenta, problemas técnicos.
                    </p>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h6 fw-bold mb-2 text-primary">
                        <i class="bi bi-credit-card me-1"></i>Pagos y facturación
                    </h2>
                    <p style="font-size:.83rem;line-height:1.6;margin-bottom:0">
                        Problemas con un cargo, solicitud de factura, devoluciones o disputas.
                    </p>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h6 fw-bold mb-2 text-primary">
                        <i class="bi bi-shield-exclamation me-1"></i>Reportes y denuncias
                    </h2>
                    <p style="font-size:.83rem;line-height:1.6;margin-bottom:0">
                        Contenido inapropiado, derechos de autor, datos personales, contenido no consensual.
                    </p>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h6 fw-bold mb-2 text-primary">
                        <i class="bi bi-bank me-1"></i>Asuntos legales y autoridades
                    </h2>
                    <p style="font-size:.83rem;line-height:1.6;margin-bottom:0">
                        Requerimientos de autoridad, ejercicio de derechos ARCO, asesoría legal interna.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulario -->
    <div class="card">
        <div class="card-body p-4">
            <h2 class="h5 fw-bold mb-3">
                <i class="bi bi-pencil-square text-primary me-2"></i>Envíanos un mensaje
            </h2>

            <form method="POST" action="<?= APP_URL ?>/contacto" data-validate-form novalidate id="form-contacto">
                <?= $csrfField ?>

                <!-- Honeypot anti-spam -->
                <input type="text" name="website" tabindex="-1" autocomplete="off"
                       style="position:absolute;left:-10000px;width:1px;height:1px;overflow:hidden">

                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label for="contacto_nombre" class="form-label">Nombre o apodo</label>
                        <input type="text"
                               id="contacto_nombre"
                               name="nombre"
                               class="form-control"
                               value="<?= e($_GET['nombre'] ?? '') ?>"
                               placeholder="¿Cómo te llamas?"
                               required
                               maxlength="80"
                               data-validate="required|minLength:2|maxLength:80">
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="contacto_email" class="form-label">Correo electrónico</label>
                        <input type="email"
                               id="contacto_email"
                               name="email"
                               class="form-control"
                               placeholder="tu@email.com"
                               required
                               maxlength="180"
                               data-validate="required|email">
                    </div>
                </div>

                <div class="mb-3 mt-3">
                    <label for="contacto_asunto" class="form-label">Tipo de consulta</label>
                    <select id="contacto_asunto"
                            name="asunto"
                            class="form-select"
                            required
                            data-validate="required">
                        <option value="">Selecciona una opción…</option>
                        <option value="soporte">Soporte general</option>
                        <option value="pagos">Pagos y facturación</option>
                        <option value="reporte">Reporte o denuncia</option>
                        <option value="legal">Asuntos legales / ARCO</option>
                        <option value="otro">Otro</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="contacto_mensaje" class="form-label">Mensaje</label>
                    <textarea id="contacto_mensaje"
                              name="mensaje"
                              class="form-control"
                              rows="6"
                              required
                              minlength="20"
                              maxlength="3000"
                              placeholder="Cuéntanos con detalle. Si es sobre un perfil, comentario o pago, incluye URL o ID de transacción."
                              data-validate="required|minLength:20|maxLength:3000"></textarea>
                    <div class="form-text" style="font-size:.78rem">
                        Mínimo 20 caracteres, máximo 3000. Incluye toda la información que pueda ayudarnos a resolver tu solicitud.
                    </div>
                </div>

                <div class="form-check mb-3">
                    <input type="checkbox"
                           id="contacto_acepta"
                           class="form-check-input"
                           required
                           data-validate="required">
                    <label for="contacto_acepta" class="form-check-label" style="font-size:.83rem">
                        He leído el <a href="<?= APP_URL ?>/privacidad" target="_blank">Aviso de Privacidad</a>
                        y autorizo el tratamiento de mis datos para responder a esta consulta.
                    </label>
                </div>

                <!-- Verificación humana (math captcha local, sin servicios externos) -->
                <div class="mb-3 p-3 rounded" style="background:rgba(255,45,117,.05);border:1px solid rgba(255,45,117,.2)">
                    <label for="contacto_captcha" class="form-label fw-semibold" style="font-size:.88rem">
                        <i class="bi bi-shield-check text-primary me-1"></i>
                        Verificación: ¿cuánto es <strong><?= (int)($captcha_a ?? 0) ?> + <?= (int)($captcha_b ?? 0) ?></strong>?
                    </label>
                    <input type="number"
                           id="contacto_captcha"
                           name="captcha"
                           class="form-control"
                           style="max-width:140px"
                           inputmode="numeric"
                           min="0" max="20"
                           required
                           autocomplete="off"
                           data-validate="required">
                    <div class="form-text" style="font-size:.74rem">
                        Pequeño paso anti-bot. Esta verificación se hace en nuestro servidor — no usamos servicios externos.
                    </div>
                </div>

                <div class="d-flex gap-2 flex-column flex-sm-row">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="bi bi-send me-2"></i>Enviar mensaje
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Email directo y datos -->
    <div class="card mt-3">
        <div class="card-body" style="font-size:.85rem;line-height:1.7">
            <strong><i class="bi bi-info-circle text-primary me-1"></i> ¿Prefieres escribir directamente?</strong><br>
            Envía tu mensaje a <a href="mailto:legal@placerselecto.com">legal@placerselecto.com</a>.
            Atendemos consultas en español. Tiempo de respuesta habitual: 1 a 3 días hábiles.
        </div>
    </div>

    <div class="mt-4 text-center text-muted" style="font-size:.8rem">
        <a href="<?= APP_URL ?>/terminos" class="me-3">Términos y Condiciones</a>
        <a href="<?= APP_URL ?>/privacidad" class="me-3">Aviso de Privacidad</a>
        <a href="<?= APP_URL ?>/dmca" class="me-3">Derechos de Autor</a>
        <a href="<?= APP_URL ?>/pagos">Pagos</a>
    </div>
</div>
