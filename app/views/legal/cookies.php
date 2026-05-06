<div class="container py-5" style="max-width:860px">

    <div class="mb-4">
        <h1 class="h3 fw-bold mb-1">Política de Cookies</h1>
        <p class="text-muted" style="font-size:.85rem">Última actualización: <?= date('d/m/Y') ?></p>
    </div>

    <div class="alert alert-info" style="font-size:.9rem;line-height:1.7">
        <i class="bi bi-info-circle me-2"></i>
        En <strong><?= e(APP_NAME) ?></strong> usamos únicamente cookies <strong>técnicas y de seguridad</strong>
        necesarias para el funcionamiento del Sitio. <strong>No usamos cookies de publicidad, rastreo o
        análisis comercial.</strong>
    </div>

    <?php
    $secciones = [
        ['titulo' => '1. ¿Qué son las cookies?',
         'cuerpo' => 'Las cookies son pequeños archivos de texto que un sitio web descarga en tu dispositivo cuando lo visitas. Permiten al sitio recordar tus preferencias y reconocerte en visitas posteriores, lo que es indispensable para servicios como mantener la sesión iniciada o aplicar configuraciones de usuario.

Adicionalmente, este Sitio utiliza una tecnología similar llamada localStorage del navegador, que cumple una función parecida pero los datos no se envían al servidor en cada petición.'],

        ['titulo' => '2. ¿Qué tipos de cookies existen?',
         'cuerpo' => 'Por su origen:
• Cookies propias: gestionadas por el editor del sitio (en este caso ' . APP_NAME . ').
• Cookies de terceros: gestionadas por proveedores externos (en nuestro caso, únicamente Cloudflare con fines de seguridad).

Por su duración:
• Cookies de sesión: se eliminan automáticamente al cerrar el navegador.
• Cookies persistentes: se conservan durante un periodo definido (de minutos a años).

Por su finalidad:
• Cookies técnicas: estrictamente necesarias para que el sitio funcione (sesión, autenticación, seguridad).
• Cookies de personalización: recuerdan preferencias del usuario.
• Cookies de análisis: miden tráfico y comportamiento (NO usadas en este Sitio).
• Cookies publicitarias: muestran anuncios personalizados (NO usadas en este Sitio).
• Cookies de redes sociales: integran widgets de redes sociales (NO usadas en este Sitio).'],

        ['titulo' => '3. Cookies que utilizamos',
         'cuerpo' => 'Solamente las siguientes cookies son utilizadas por ' . APP_NAME . '. Todas son técnicas o de seguridad. Ninguna realiza rastreo publicitario.'],
    ];
    ?>

    <div class="d-flex flex-column gap-4">
        <?php foreach ($secciones as $s): ?>
        <div class="card">
            <div class="card-body">
                <h2 class="h6 fw-bold mb-2 text-primary"><?= e($s['titulo']) ?></h2>
                <p class="mb-0" style="font-size:.875rem;line-height:1.8;color:var(--color-text);white-space:pre-line">
                    <?= e($s['cuerpo']) ?>
                </p>
            </div>
        </div>
        <?php endforeach; ?>

        <!-- Tabla cookies propias -->
        <div class="card">
            <div class="card-body">
                <h2 class="h6 fw-bold mb-3 text-primary">Cookies propias (técnicas)</h2>
                <div class="table-responsive">
                    <table class="table table-sm" style="font-size:.83rem">
                        <thead>
                            <tr style="background:rgba(255,45,117,.08)">
                                <th>Nombre</th>
                                <th>Función</th>
                                <th>Caducidad</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>CLASIF_SESS</code></td>
                                <td>Identifica tu sesión activa en el Sitio. Sin ella no es posible iniciar sesión ni mantenerla.</td>
                                <td>24 horas (o 30 días si activas "mantener sesión")</td>
                            </tr>
                            <tr>
                                <td><code>remember_me</code></td>
                                <td>Indica al servidor que tu sesión es de larga duración cuando marcaste "Mantener sesión activa" al iniciar sesión.</td>
                                <td>30 días</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tabla cookies de terceros -->
        <div class="card">
            <div class="card-body">
                <h2 class="h6 fw-bold mb-3 text-primary">Cookies de terceros (seguridad)</h2>
                <p style="font-size:.875rem;line-height:1.7;color:var(--color-text)">
                    Utilizamos los servicios de <strong>Cloudflare</strong> como CDN y firewall de aplicación
                    para proteger al Sitio contra ataques y abuso automatizado. Cloudflare establece las
                    siguientes cookies para distinguir tráfico humano de tráfico automatizado.
                </p>
                <div class="table-responsive">
                    <table class="table table-sm" style="font-size:.83rem">
                        <thead>
                            <tr style="background:rgba(255,45,117,.08)">
                                <th>Nombre</th>
                                <th>Función</th>
                                <th>Caducidad</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>__cf_bm</code></td>
                                <td>Cloudflare Bot Management. Distingue tráfico humano de bots para proteger al Sitio.</td>
                                <td>30 minutos (renovable)</td>
                            </tr>
                            <tr>
                                <td><code>cf_clearance</code></td>
                                <td>Cloudflare Challenge. Solo se establece si Cloudflare detecta tráfico sospechoso y necesita verificar al usuario.</td>
                                <td>30 minutos</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p style="font-size:.82rem;color:var(--color-text-muted);margin-bottom:0">
                    Más información sobre las cookies de Cloudflare:
                    <a href="https://www.cloudflare.com/cookie-policy/" target="_blank" rel="noopener nofollow">https://www.cloudflare.com/cookie-policy/</a>
                </p>
            </div>
        </div>

        <!-- Tabla almacenamiento local -->
        <div class="card">
            <div class="card-body">
                <h2 class="h6 fw-bold mb-3 text-primary">Almacenamiento local (localStorage)</h2>
                <p style="font-size:.875rem;line-height:1.7;color:var(--color-text)">
                    Adicionalmente usamos el almacenamiento local del navegador (localStorage) para
                    recordar pequeñas decisiones del usuario. A diferencia de las cookies, el localStorage
                    no se envía al servidor y permanece exclusivamente en tu navegador.
                </p>
                <div class="table-responsive">
                    <table class="table table-sm" style="font-size:.83rem">
                        <thead>
                            <tr style="background:rgba(255,45,117,.08)">
                                <th>Clave</th>
                                <th>Función</th>
                                <th>Persistencia</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>ps_cookie_consent</code></td>
                                <td>Recuerda que ya viste el aviso de cookies y no volverlo a mostrar.</td>
                                <td>Hasta que limpies el almacenamiento del navegador</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Cómo gestionar cookies -->
        <div class="card">
            <div class="card-body">
                <h2 class="h6 fw-bold mb-2 text-primary">4. Cómo controlar o eliminar las cookies</h2>
                <p style="font-size:.875rem;line-height:1.7;color:var(--color-text)">
                    Puedes permitir, bloquear o eliminar las cookies instaladas en tu equipo desde la
                    configuración de tu navegador. Cada navegador ofrece instrucciones específicas:
                </p>
                <ul style="font-size:.85rem;line-height:1.9;margin-bottom:.5rem">
                    <li><strong>Google Chrome:</strong> Configuración → Privacidad y seguridad → Cookies y otros datos del sitio</li>
                    <li><strong>Mozilla Firefox:</strong> Opciones → Privacidad y seguridad → Cookies y datos del sitio</li>
                    <li><strong>Safari:</strong> Preferencias → Privacidad → Administrar datos del sitio web</li>
                    <li><strong>Microsoft Edge:</strong> Configuración → Cookies y permisos del sitio</li>
                </ul>
                <p style="font-size:.82rem;color:var(--color-text-muted);margin-bottom:0">
                    <strong>Importante:</strong> al desactivar las cookies técnicas el Sitio no podrá
                    mantener tu sesión iniciada y algunas funciones dejarán de operar correctamente.
                </p>
            </div>
        </div>

        <!-- Cambios -->
        <div class="card">
            <div class="card-body">
                <h2 class="h6 fw-bold mb-2 text-primary">5. Cambios a esta política</h2>
                <p style="font-size:.875rem;line-height:1.7;color:var(--color-text);margin-bottom:0">
                    ' . APP_NAME . ' puede actualizar esta Política de Cookies cuando se introduzcan nuevas
                    funcionalidades o se modifique la legislación aplicable. La versión vigente se publicará
                    siempre en esta página con la fecha de su última actualización.
                </p>
            </div>
        </div>

        <!-- Contacto -->
        <div class="card">
            <div class="card-body">
                <h2 class="h6 fw-bold mb-2 text-primary">6. Contacto</h2>
                <p style="font-size:.875rem;line-height:1.7;color:var(--color-text);margin-bottom:0">
                    Para cualquier duda sobre esta Política de Cookies escribe a
                    <a href="mailto:legal@placerselecto.com">legal@placerselecto.com</a>.
                </p>
            </div>
        </div>
    </div>

    <div class="mt-4 text-center text-muted" style="font-size:.8rem">
        <a href="<?= APP_URL ?>/terminos" class="me-3">Términos y Condiciones</a>
        <a href="<?= APP_URL ?>/privacidad" class="me-3">Aviso de Privacidad</a>
        <a href="<?= APP_URL ?>/mayores-18" class="me-3">Aviso +18</a>
        <a href="<?= APP_URL ?>/dmca" class="me-3">Derechos de Autor</a>
        <a href="<?= APP_URL ?>/2257">Declaración 2257</a>
    </div>
</div>
