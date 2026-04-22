<div class="container py-5" style="max-width:760px">

    <!-- Badge +18 -->
    <div class="text-center mb-5">
        <div style="width:88px;height:88px;border-radius:50%;background:rgba(255,45,117,.1);border:3px solid rgba(255,45,117,.25);display:flex;align-items:center;justify-content:center;font-size:1.6rem;font-weight:900;color:var(--color-primary);margin:0 auto 1.25rem">
            +18
        </div>
        <h1 class="h3 fw-bold mb-2">Aviso de contenido para adultos</h1>
        <p class="text-muted" style="max-width:520px;margin:0 auto">
            Este sitio web contiene publicidad y clasificados dirigidos exclusivamente a personas adultas.
        </p>
    </div>

    <!-- Declaración principal -->
    <div class="card mb-4" style="border-color:rgba(255,45,117,.25)">
        <div class="card-body p-4">
            <h2 class="h5 fw-bold mb-3 text-primary">Declaración de contenido</h2>
            <p style="font-size:.9rem;line-height:1.8">
                <?= e(APP_NAME) ?> es una plataforma de <strong>clasificados publicitarios</strong> que actúa como intermediario
                entre anunciantes adultos y usuarios adultos. El Sitio:
            </p>
            <ul style="font-size:.9rem;line-height:2;color:var(--color-text)">
                <li><i class="bi bi-check-circle-fill text-success me-2"></i>Permite publicar anuncios de servicios legales para adultos.</li>
                <li><i class="bi bi-check-circle-fill text-success me-2"></i>Verifica que los usuarios sean mayores de 18 años.</li>
                <li><i class="bi bi-check-circle-fill text-success me-2"></i>Modera el contenido para cumplir con la legislación aplicable.</li>
                <li><i class="bi bi-x-circle-fill text-danger me-2"></i><strong>NO</strong> aloja, produce ni distribuye pornografía.</li>
                <li><i class="bi bi-x-circle-fill text-danger me-2"></i><strong>NO</strong> permite contenido que involucre menores de edad.</li>
                <li><i class="bi bi-x-circle-fill text-danger me-2"></i><strong>NO</strong> facilita ni promueve actividades ilegales.</li>
            </ul>
        </div>
    </div>

    <!-- Acceso y restricciones -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <h3 class="h6 fw-bold mb-3">
                        <i class="bi bi-person-check text-success me-2"></i>Requisitos de acceso
                    </h3>
                    <ul class="list-unstyled mb-0" style="font-size:.85rem;line-height:1.9">
                        <li><i class="bi bi-dot text-primary"></i>Ser mayor de 18 años</li>
                        <li><i class="bi bi-dot text-primary"></i>Aceptar los Términos y condiciones</li>
                        <li><i class="bi bi-dot text-primary"></i>Confirmar haber leído este aviso</li>
                        <li><i class="bi bi-dot text-primary"></i>No ser residente de jurisdicciones donde el acceso sea ilegal</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <h3 class="h6 fw-bold mb-3">
                        <i class="bi bi-shield-exclamation text-warning me-2"></i>Control parental
                    </h3>
                    <p style="font-size:.85rem;line-height:1.8;margin-bottom:.75rem">
                        Si eres padre, madre o tutor, te recomendamos usar software de control parental
                        para restringir el acceso de menores a este tipo de contenido.
                    </p>
                    <p style="font-size:.82rem;color:var(--color-text-muted)">
                        Proveedores de control parental: Net Nanny, Qustodio, Bark, Circle.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Legislación -->
    <div class="card mb-4">
        <div class="card-body">
            <h3 class="h6 fw-bold mb-3">
                <i class="bi bi-file-earmark-text text-primary me-2"></i>Marco legal
            </h3>
            <p style="font-size:.875rem;line-height:1.8;color:var(--color-text)">
                <?= e(APP_NAME) ?> opera conforme a la legislación mexicana aplicable, incluyendo:
            </p>
            <ul style="font-size:.85rem;line-height:1.9;color:var(--color-text)">
                <li>Ley Federal de Protección de Datos Personales en Posesión de Particulares</li>
                <li>Ley Federal de Telecomunicaciones y Radiodifusión</li>
                <li>Código Penal Federal (disposiciones sobre pornografía infantil y trata de personas)</li>
                <li>Ley General para Prevenir, Sancionar y Erradicar los Delitos en Materia de Trata de Personas</li>
            </ul>
            <p style="font-size:.82rem;color:var(--color-text-muted);margin-bottom:0">
                Cualquier contenido que viole las leyes será eliminado y reportado a las autoridades competentes de inmediato.
            </p>
        </div>
    </div>

    <!-- Denuncia -->
    <div class="card mb-4" style="border-color:rgba(255,45,117,.2)">
        <div class="card-body d-flex gap-3">
            <i class="bi bi-megaphone-fill text-danger fs-4 flex-shrink-0 mt-1"></i>
            <div>
                <h3 class="h6 fw-bold mb-2 text-danger">¿Ves contenido inapropiado?</h3>
                <p style="font-size:.85rem;margin-bottom:.5rem">
                    Si encuentras contenido que creas que involucra menores de edad o actividades ilegales,
                    repórtalo inmediatamente usando el botón <strong>"Reportar"</strong> en el anuncio,
                    o contáctanos directamente.
                </p>
                <p style="font-size:.82rem;color:var(--color-text-muted);margin-bottom:0">
                    También puedes reportarlo al CENADEM (Centro Nacional de Denuncia) o a la Policía Cibernética.
                </p>
            </div>
        </div>
    </div>

    <div class="text-center text-muted" style="font-size:.8rem">
        <a href="<?= APP_URL ?>/terminos" class="me-3">Términos y condiciones</a>
        <a href="<?= APP_URL ?>/privacidad">Aviso de privacidad</a>
    </div>

</div>
