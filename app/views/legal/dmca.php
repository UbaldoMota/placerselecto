<div class="container py-5" style="max-width:860px">

    <div class="mb-4">
        <h1 class="h3 fw-bold mb-1">Política de Derechos de Autor y Contenido</h1>
        <p class="text-muted" style="font-size:.85rem">Última actualización: <?= date('d/m/Y') ?></p>
    </div>

    <div class="alert alert-info" style="font-size:.9rem;line-height:1.7">
        <i class="bi bi-info-circle me-2"></i>
        <strong><?= e(APP_NAME) ?></strong> respeta los derechos de propiedad intelectual y la
        intimidad de las personas. Si encuentras contenido publicado en el Sitio que infringe
        tus derechos, atendemos reportes formales y retiramos el contenido a la brevedad.
    </div>

    <?php
    $secciones = [
        ['titulo' => '1. A quién va dirigida esta política',
         'cuerpo' => 'Esta política aplica a:
• Titulares de derechos de autor que detecten que su obra está siendo utilizada en el Sitio sin autorización.
• Personas cuya imagen, fotografías o videos privados fueron publicados sin su consentimiento (Ley Olimpia y normas equivalentes).
• Personas que aparezcan en contenido que les fue prometido como privado y posteriormente fue compartido en el Sitio.
• Cualquier persona o representante con interés legítimo sobre contenido publicado.'],

        ['titulo' => '2. Cómo presentar un reporte',
         'cuerpo' => 'Envía un correo a legal@placerselecto.com con la siguiente información:

(1) Identificación clara del contenido infractor
    • URL completa del perfil o publicación
    • Descripción del contenido (foto, video, texto)
    • Si se trata de varias publicaciones, lista de todas las URLs

(2) Identificación del titular del derecho
    • Nombre completo o razón social
    • Domicilio para oír y recibir notificaciones
    • Correo electrónico y teléfono de contacto
    • En su caso, documento que acredite la representación legal

(3) Acreditación del derecho infringido
    • Si es derecho de autor: certificado de registro, contrato, capturas con metadatos, fecha de creación, prueba de autoría
    • Si es derecho a la imagen o intimidad: identificación oficial, comparativa con la imagen publicada, descripción del contexto
    • Si es contenido íntimo difundido sin consentimiento: declaración de que el contenido fue captado o entregado bajo expectativa de privacidad y nunca se autorizó su difusión

(4) Declaración bajo protesta de decir verdad
    • Que la información proporcionada es veraz y completa
    • Que actúas como titular del derecho o como su representante autorizado
    • Que comprendes que las declaraciones falsas pueden tener consecuencias legales

(5) Firma del solicitante (autógrafa o electrónica)'],

        ['titulo' => '3. Plazo y procedimiento',
         'cuerpo' => 'Una vez recibido el reporte completo:
• Lo confirmamos por correo dentro de 24 horas hábiles.
• Realizamos una verificación inicial dentro de los 3 días hábiles siguientes.
• Si el reporte cumple con los requisitos, retiramos el contenido y notificamos al usuario que lo publicó.
• Si el reporte requiere información adicional, te lo solicitamos con plazo de 5 días hábiles para complementar.

Para reportes que involucren contenido íntimo no consensuado o sospecha de menores, el retiro es inmediato y la verificación se realiza posteriormente, dentro del mismo procedimiento.'],

        ['titulo' => '4. Contraaviso del usuario afectado',
         'cuerpo' => 'El usuario cuyo contenido fue retirado puede presentar un contraaviso a legal@placerselecto.com aportando:
• Identificación oficial.
• Pruebas que acrediten que es titular o licenciatario del contenido (originales, contratos, registros).
• Declaración bajo protesta de decir verdad de que el retiro fue derivado de un error o reporte improcedente.

' . APP_NAME . ' analizará el contraaviso y, en su caso, restaurará el contenido. Si las partes mantienen el desacuerdo, podrán resolverlo por las vías legales aplicables. El Sitio no es árbitro de disputas de fondo entre titulares de derechos.'],

        ['titulo' => '5. Reincidencia',
         'cuerpo' => 'Las cuentas que reciban reportes verificados de manera reiterada serán suspendidas o canceladas de forma permanente. Cuando proceda, se denunciará al usuario ante la autoridad competente.'],

        ['titulo' => '6. Reportes falsos',
         'cuerpo' => 'La presentación de reportes falsos, fraudulentos o con dolo causa daños al usuario afectado y al Sitio. Quien presente un reporte a sabiendas falso responderá por los daños y perjuicios ocasionados, sin perjuicio de las consecuencias penales que puedan derivarse de la falsedad en declaraciones.'],

        ['titulo' => '7. Conservación de evidencia',
         'cuerpo' => APP_NAME . ' conserva por el tiempo legalmente exigible los registros relacionados con reportes recibidos, contenido retirado y comunicaciones intercambiadas, con el objetivo de cumplir requerimientos de autoridad y de proteger derechos de terceros.'],

        ['titulo' => '8. Contacto',
         'cuerpo' => 'Para presentar reportes, contraavisos o cualquier asunto relacionado con esta política:
legal@placerselecto.com

Te pedimos enviar el reporte por escrito vía correo electrónico para que quede registrado y podamos darle el trámite formal correspondiente. Los reportes verbales o por redes sociales no inician el procedimiento.'],
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
    </div>

    <div class="mt-4 text-center text-muted" style="font-size:.8rem">
        <a href="<?= APP_URL ?>/terminos" class="me-3">Términos y Condiciones</a>
        <a href="<?= APP_URL ?>/privacidad" class="me-3">Aviso de Privacidad</a>
        <a href="<?= APP_URL ?>/mayores-18" class="me-3">Aviso +18</a>
        <a href="<?= APP_URL ?>/2257">Declaración 2257</a>
    </div>
</div>
