<div class="container py-5" style="max-width:860px">

    <div class="text-center mb-4">
        <div style="width:88px;height:88px;border-radius:50%;background:rgba(255,45,117,.1);border:3px solid rgba(255,45,117,.25);display:flex;align-items:center;justify-content:center;font-size:2rem;color:var(--color-primary);margin:0 auto 1.25rem">
            <i class="bi bi-shield-lock-fill"></i>
        </div>
        <h1 class="h3 fw-bold mb-2">Control Parental</h1>
        <p class="text-muted" style="font-size:.9rem;max-width:540px;margin:0 auto">
            Herramientas y recursos para que padres, madres y tutores protejan a los menores
            del acceso a contenido para adultos.
        </p>
    </div>

    <!-- Declaración del Sitio -->
    <div class="card mb-4" style="border-color:rgba(255,45,117,.25)">
        <div class="card-body p-4">
            <h2 class="h6 fw-bold mb-2 text-primary">
                <i class="bi bi-info-circle me-2"></i>Sobre <?= e(APP_NAME) ?>
            </h2>
            <p style="font-size:.9rem;line-height:1.7;margin-bottom:.5rem">
                Este Sitio es de contenido para adultos y su acceso está estrictamente
                limitado a personas mayores de 18 años. Está marcado con la
                <strong>etiqueta RTA (Restricted To Adults)</strong> para que cualquier
                software de control parental lo identifique automáticamente y lo bloquee.
            </p>
            <p style="font-size:.85rem;color:var(--color-text-muted);margin-bottom:0">
                Como padre, madre o tutor, usted es el responsable principal de proteger a
                los menores a su cargo. A continuación encontrará herramientas y recursos
                para hacerlo efectivamente.
            </p>
        </div>
    </div>

    <!-- Búsqueda segura -->
    <div class="card mb-4">
        <div class="card-body">
            <h2 class="h6 fw-bold mb-3 text-primary">
                <i class="bi bi-search me-2"></i>1. Búsqueda segura en motores
            </h2>
            <p style="font-size:.875rem;line-height:1.7">
                Active la búsqueda segura en los motores que usan los menores. Estas funciones
                bloquean el contenido para adultos en los resultados de búsqueda:
            </p>
            <ul style="font-size:.85rem;line-height:2;margin-bottom:0">
                <li><strong>Google SafeSearch</strong> — Activar en <code>google.com/preferences</code> o desde la app de Google. Compatible con cuentas Family Link.</li>
                <li><strong>Microsoft SafeSearch (Bing)</strong> — Configurar en <code>bing.com/account/general</code></li>
                <li><strong>Yahoo SafeSearch</strong> — Disponible en la configuración de búsqueda de Yahoo</li>
                <li><strong>DuckDuckGo Safe Search</strong> — Activar desde su configuración</li>
            </ul>
        </div>
    </div>

    <!-- Buscadores para niños -->
    <div class="card mb-4">
        <div class="card-body">
            <h2 class="h6 fw-bold mb-3 text-primary">
                <i class="bi bi-emoji-smile me-2"></i>2. Buscadores diseñados para niños
            </h2>
            <p style="font-size:.875rem;line-height:1.7">
                Estos buscadores filtran activamente el contenido y solo muestran resultados
                aptos para niños. Son ideales para los más pequeños:
            </p>
            <ul style="font-size:.85rem;line-height:2;margin-bottom:0">
                <li><strong>Kiddle</strong> — <a href="https://www.kiddle.co" target="_blank" rel="noopener nofollow">kiddle.co</a></li>
                <li><strong>KidRex</strong> — <a href="https://www.kidrex.org" target="_blank" rel="noopener nofollow">kidrex.org</a></li>
                <li><strong>Kido'z</strong> — navegador web orientado a infancia</li>
            </ul>
        </div>
    </div>

    <!-- Controles por sistema operativo -->
    <div class="card mb-4">
        <div class="card-body">
            <h2 class="h6 fw-bold mb-3 text-primary">
                <i class="bi bi-pc-display me-2"></i>3. Controles parentales del sistema operativo
            </h2>
            <p style="font-size:.875rem;line-height:1.7">
                Todos los sistemas operativos modernos incluyen herramientas integradas para
                proteger a los menores. No requieren instalar software adicional.
            </p>

            <div class="mt-3">
                <h3 class="fw-semibold mb-2" style="font-size:.95rem">
                    <i class="bi bi-apple me-1"></i> Apple (iOS, iPadOS, macOS)
                </h3>
                <p style="font-size:.85rem;line-height:1.7;margin-bottom:0">
                    "Tiempo en pantalla" en Ajustes → Restricciones de contenido → Contenido web.
                    Permite limitar contenido adulto, definir lista blanca de sitios y monitorear
                    tiempo de uso. En el iPhone/iPad del menor se configura un código exclusivo
                    del adulto.
                </p>
            </div>

            <div class="mt-3">
                <h3 class="fw-semibold mb-2" style="font-size:.95rem">
                    <i class="bi bi-android me-1"></i> Android (Google Family Link)
                </h3>
                <p style="font-size:.85rem;line-height:1.7;margin-bottom:0">
                    Family Link permite vincular el dispositivo del menor con la cuenta del
                    adulto, controlar qué apps puede usar, aprobar descargas y aplicar
                    SafeSearch automáticamente. Disponible gratis en Play Store.
                </p>
            </div>

            <div class="mt-3">
                <h3 class="fw-semibold mb-2" style="font-size:.95rem">
                    <i class="bi bi-windows me-1"></i> Windows (Microsoft Family Safety)
                </h3>
                <p style="font-size:.85rem;line-height:1.7;margin-bottom:0">
                    En Windows 10/11, busque "Opciones familiares" en el menú inicio.
                    Permite filtrar contenido web, limitar tiempo y revisar la actividad del
                    menor. La cuenta familiar se vincula con dispositivos Xbox también.
                </p>
            </div>

            <div class="mt-3">
                <h3 class="fw-semibold mb-2" style="font-size:.95rem">
                    <i class="bi bi-amazon me-1"></i> Amazon Kids+
                </h3>
                <p style="font-size:.85rem;line-height:1.7;margin-bottom:0">
                    Para tabletas Fire y Echo Show. Filtra contenido por edad, limita tiempo
                    de pantalla y proporciona contenido educativo curado.
                </p>
            </div>
        </div>
    </div>

    <!-- Software dedicado -->
    <div class="card mb-4">
        <div class="card-body">
            <h2 class="h6 fw-bold mb-3 text-primary">
                <i class="bi bi-shield-check me-2"></i>4. Software de control parental dedicado
            </h2>
            <p style="font-size:.875rem;line-height:1.7">
                Existen aplicaciones especializadas que ofrecen monitoreo y filtrado más
                avanzados. Son compatibles con la etiqueta RTA y bloquean automáticamente
                este Sitio. Algunas de las más usadas:
            </p>
            <ul style="font-size:.85rem;line-height:2;margin-bottom:0">
                <li><strong>Qustodio</strong> — multiplataforma, plan gratuito y planes premium</li>
                <li><strong>Net Nanny</strong> — uno de los más establecidos, multiplataforma</li>
                <li><strong>Norton Family</strong> — incluido con suscripción Norton 360</li>
                <li><strong>Mobicip</strong> — fuerte en iOS</li>
                <li><strong>Bark</strong> — enfocado en monitoreo de redes sociales y mensajería</li>
                <li><strong>SentryPC</strong> — más enfocado a Windows</li>
                <li><strong>Kaspersky Safe Kids</strong> — opción con plan gratuito</li>
            </ul>
        </div>
    </div>

    <!-- Recursos -->
    <div class="card mb-4">
        <div class="card-body">
            <h2 class="h6 fw-bold mb-3 text-primary">
                <i class="bi bi-book me-2"></i>5. Recursos sobre crianza digital
            </h2>
            <p style="font-size:.875rem;line-height:1.7">
                Cuanto más informados estén los adultos sobre los riesgos digitales, mejor
                podrán acompañar a los menores. Estos sitios ofrecen guías, talleres y
                contenido útil:
            </p>
            <ul style="font-size:.85rem;line-height:2;margin-bottom:0">
                <li><a href="https://www.fosi.org/" target="_blank" rel="noopener nofollow"><strong>FOSI</strong></a> — Family Online Safety Institute</li>
                <li><a href="https://www.connectsafely.org/" target="_blank" rel="noopener nofollow"><strong>ConnectSafely</strong></a> — guías para padres en español e inglés</li>
                <li><a href="https://www.internetmatters.org/" target="_blank" rel="noopener nofollow"><strong>Internet Matters</strong></a> — recursos por edades</li>
                <li><a href="https://www.gob.mx/policiacibernetica" target="_blank" rel="noopener nofollow"><strong>Policía Cibernética México</strong></a> — denuncia y prevención</li>
                <li><a href="https://www.unicef.org/lac/" target="_blank" rel="noopener nofollow"><strong>UNICEF Latinoamérica</strong></a> — protección infantil online</li>
            </ul>
        </div>
    </div>

    <!-- Reportes -->
    <div class="card mb-4" style="border-color:rgba(220,53,69,.3)">
        <div class="card-body">
            <h2 class="h6 fw-bold mb-2 text-danger">
                <i class="bi bi-megaphone-fill me-2"></i>¿Detectaste contenido inapropiado en el Sitio?
            </h2>
            <p style="font-size:.875rem;line-height:1.7;margin-bottom:.5rem">
                Si encuentras en este Sitio contenido que pudiera involucrar a menores de
                edad o cualquier actividad ilícita, repórtalo de inmediato:
            </p>
            <ul style="font-size:.85rem;line-height:1.9;margin-bottom:.5rem">
                <li>Botón <strong>"Reportar"</strong> disponible en cada perfil del Sitio.</li>
                <li>Correo directo a <a href="mailto:legal@placerselecto.com">legal@placerselecto.com</a></li>
                <li>Denuncia ante la <strong>Policía Cibernética</strong> de tu entidad federativa.</li>
                <li>Centro Nacional de Denuncia (CENADEM).</li>
            </ul>
            <p style="font-size:.82rem;color:var(--color-text-muted);margin-bottom:0">
                ' . APP_NAME . ' aplica tolerancia cero al contenido que involucre o aparente
                involucrar a menores de edad y reporta los casos detectados a las autoridades
                competentes y a las redes internacionales de protección.
            </p>
        </div>
    </div>

    <div class="text-center text-muted" style="font-size:.8rem">
        <a href="<?= APP_URL ?>/terminos" class="me-3">Términos y Condiciones</a>
        <a href="<?= APP_URL ?>/privacidad" class="me-3">Aviso de Privacidad</a>
        <a href="<?= APP_URL ?>/mayores-18" class="me-3">Aviso +18</a>
        <a href="<?= APP_URL ?>/dmca" class="me-3">Derechos de Autor</a>
        <a href="<?= APP_URL ?>/2257">Declaración 2257</a>
    </div>
</div>
