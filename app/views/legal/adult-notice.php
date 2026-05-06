<div class="container py-5" style="max-width:760px">

    <!-- Badge +18 -->
    <div class="text-center mb-5">
        <div style="width:88px;height:88px;border-radius:50%;background:rgba(255,45,117,.1);border:3px solid rgba(255,45,117,.25);display:flex;align-items:center;justify-content:center;font-size:1.6rem;font-weight:900;color:var(--color-primary);margin:0 auto 1.25rem">
            +18
        </div>
        <h1 class="h3 fw-bold mb-2">Aviso de contenido para adultos</h1>
        <p class="text-muted" style="max-width:520px;margin:0 auto">
            Este Sitio contiene material para adultos. Su acceso es voluntario y está restringido a personas mayores de 18 años.
        </p>
    </div>

    <!-- Declaración principal -->
    <div class="card mb-4" style="border-color:rgba(255,45,117,.25)">
        <div class="card-body p-4">
            <h2 class="h5 fw-bold mb-3 text-primary">Declaración de contenido</h2>
            <p style="font-size:.9rem;line-height:1.8">
                <?= e(APP_NAME) ?> es una plataforma de <strong>clasificados publicitarios para adultos</strong>.
                Los usuarios que se registran y verifican su identidad pueden publicar perfiles que incluyen
                fotografías, videos y descripciones de servicios para adultos. El contenido publicado puede
                ser sexualmente explícito y está dirigido exclusivamente a un público adulto.
            </p>
            <p style="font-size:.9rem;line-height:1.8;margin-bottom:0">
                El Sitio actúa como intermediario tecnológico y publicitario. No presta servicios sexuales
                ni participa en las transacciones que los usuarios acuerden entre sí.
            </p>
        </div>
    </div>

    <!-- Lo que se permite -->
    <div class="card mb-4">
        <div class="card-body p-4">
            <h3 class="h6 fw-bold mb-3 text-success">
                <i class="bi bi-check-circle-fill me-2"></i>Permitido en el Sitio
            </h3>
            <ul style="font-size:.9rem;line-height:2;color:var(--color-text);margin-bottom:0">
                <li>Publicaciones de personas adultas verificadas (18+) que ofrecen servicios para adultos.</li>
                <li>Contenido visual sexualmente explícito, incluyendo desnudos y representaciones gráficas, siempre que se cumpla con la verificación de edad e identidad.</li>
                <li>Descripciones, tarifas, datos de contacto y zonas de atención de los anunciantes.</li>
                <li>Comentarios y calificaciones moderados por usuarios verificados.</li>
            </ul>
        </div>
    </div>

    <!-- Lo que está prohibido — tolerancia cero -->
    <div class="card mb-4" style="border-color:rgba(220,53,69,.3)">
        <div class="card-body p-4">
            <h3 class="h6 fw-bold mb-3 text-danger">
                <i class="bi bi-x-octagon-fill me-2"></i>Tolerancia cero — prohibido absolutamente
            </h3>
            <ul style="font-size:.9rem;line-height:2;color:var(--color-text);margin-bottom:0">
                <li><strong>Cualquier material que involucre o aparente involucrar a menores de edad</strong>. Toda detección se denuncia a las autoridades competentes y a las redes internacionales de protección.</li>
                <li><strong>Contenido íntimo difundido sin el consentimiento</strong> de las personas que aparecen en él (incluido el llamado "porno de venganza" y la difusión no consensuada de imágenes íntimas).</li>
                <li><strong>Trata de personas, lenocinio o explotación sexual</strong> bajo cualquier forma.</li>
                <li><strong>Bestialidad, necrofilia, snuff, violación o violencia real</strong>.</li>
                <li><strong>Contenido protegido por derechos de autor</strong> sin autorización del titular.</li>
                <li><strong>Suplantación de identidad</strong> o uso de contenido de terceros sin su consentimiento.</li>
            </ul>
        </div>
    </div>

    <!-- Verificación obligatoria -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <h3 class="h6 fw-bold mb-3">
                        <i class="bi bi-shield-check text-success me-2"></i>Verificación obligatoria
                    </h3>
                    <p style="font-size:.85rem;line-height:1.8;margin-bottom:0">
                        Para publicar perfiles con contenido visual, el Sitio exige verificación con
                        documento oficial de identidad, fotografía facial y video de confirmación.
                        Este proceso garantiza que los modelos son adultos y consintieron la publicación.
                    </p>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <h3 class="h6 fw-bold mb-3">
                        <i class="bi bi-shield-exclamation text-warning me-2"></i>Control parental
                    </h3>
                    <p style="font-size:.85rem;line-height:1.8;margin-bottom:.5rem">
                        Si eres padre, madre o tutor, te recomendamos usar software de control parental
                        para restringir el acceso de menores a este tipo de contenido.
                    </p>
                    <p style="font-size:.82rem;color:var(--color-text-muted);margin-bottom:0">
                        Proveedores: Net Nanny, Qustodio, Bark, Circle.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Marco legal -->
    <div class="card mb-4">
        <div class="card-body">
            <h3 class="h6 fw-bold mb-3">
                <i class="bi bi-file-earmark-text text-primary me-2"></i>Marco legal aplicable
            </h3>
            <p style="font-size:.875rem;line-height:1.8;color:var(--color-text)">
                <?= e(APP_NAME) ?> opera conforme a la legislación mexicana aplicable a la actividad
                publicitaria, al comercio electrónico, a la protección de datos personales y a la
                prevención de delitos sexuales y de trata.
            </p>
            <p style="font-size:.82rem;color:var(--color-text-muted);margin-bottom:0">
                Cualquier contenido que viole la ley o estas políticas será retirado de inmediato
                y reportado a las autoridades competentes.
            </p>
        </div>
    </div>

    <!-- Reportes -->
    <div class="card mb-4" style="border-color:rgba(255,45,117,.2)">
        <div class="card-body d-flex gap-3">
            <i class="bi bi-megaphone-fill text-danger fs-4 flex-shrink-0 mt-1"></i>
            <div>
                <h3 class="h6 fw-bold mb-2 text-danger">¿Detectaste contenido inapropiado?</h3>
                <p style="font-size:.85rem;margin-bottom:.5rem">
                    Si encuentras contenido que pudiera involucrar menores de edad, falta de
                    consentimiento o cualquier actividad ilícita, repórtalo de inmediato con el
                    botón <strong>"Reportar"</strong> en el perfil correspondiente o escribe a
                    <a href="mailto:legal@placerselecto.com">legal@placerselecto.com</a>.
                </p>
                <p style="font-size:.82rem;color:var(--color-text-muted);margin-bottom:0">
                    También puedes reportarlo al CENADEM (Centro Nacional de Denuncia) o a la Policía Cibernética de tu entidad federativa.
                </p>
            </div>
        </div>
    </div>

    <div class="text-center text-muted" style="font-size:.8rem">
        <a href="<?= APP_URL ?>/terminos" class="me-3">Términos y Condiciones</a>
        <a href="<?= APP_URL ?>/privacidad" class="me-3">Aviso de Privacidad</a>
        <a href="<?= APP_URL ?>/dmca" class="me-3">Derechos de Autor</a>
        <a href="<?= APP_URL ?>/2257">Declaración 2257</a>
    </div>

</div>
