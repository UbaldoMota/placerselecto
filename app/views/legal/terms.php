<div class="container py-5" style="max-width:860px">

    <div class="mb-4">
        <h1 class="h3 fw-bold mb-1">Términos y Condiciones</h1>
        <p class="text-muted" style="font-size:.85rem">Última actualización: <?= date('d/m/Y') ?></p>
    </div>

    <?php
    $secciones = [
        ['titulo' => '1. Aceptación de los Términos',
         'cuerpo' => 'Al acceder, registrarte o utilizar ' . APP_NAME . ' (el "Sitio"), declaras bajo protesta de decir verdad que:
• Eres mayor de 18 años de edad.
• Aceptas estos Términos y Condiciones en su totalidad, así como nuestro Aviso de Privacidad y nuestra Política de Contenido para Adultos.
• Comprendes que el Sitio contiene material para adultos y que su acceso es voluntario.
Si no cumples cualquiera de los requisitos anteriores o no estás de acuerdo con estos Términos, debes abandonar el Sitio inmediatamente y abstenerte de utilizarlo.'],

        ['titulo' => '2. Naturaleza del Servicio',
         'cuerpo' => APP_NAME . ' es una plataforma tecnológica que actúa como intermediario publicitario entre personas adultas que ofrecen servicios de compañía y entretenimiento para adultos, y personas adultas interesadas en dichos servicios.

El Sitio:
• Permite a usuarios verificados publicar perfiles y contenido visual (incluido contenido sexualmente explícito) dirigido a un público adulto.
• Cobra por servicios publicitarios opcionales (destacados, posicionamiento, promociones).
• NO presta servicios sexuales, NO actúa como agencia, NO cobra comisión sobre transacciones realizadas entre los usuarios.
• NO interviene en los acuerdos, citas o transacciones que realicen los usuarios fuera del Sitio.

Los usuarios anunciantes son operadores independientes y son los únicos responsables de los servicios que ofrezcan, así como del cumplimiento de las obligaciones fiscales que les correspondan.'],

        ['titulo' => '3. Edad mínima y verificación',
         'cuerpo' => 'El uso del Sitio está estrictamente reservado a personas mayores de 18 años. Cualquier persona que aparezca en fotografías, videos o cualquier otro material publicado en perfiles también deberá tener 18 años cumplidos al momento de la captura del material.

Para publicar perfiles con contenido visual, el Sitio exige verificación obligatoria que incluye:
• Documento oficial de identidad vigente con fotografía (INE, pasaporte u otro).
• Foto de verificación facial y video corto que confirmen identidad y consentimiento.

' . APP_NAME . ' conserva los registros de verificación durante el tiempo legalmente exigible y los pone a disposición de las autoridades competentes ante requerimiento debidamente fundado.'],

        ['titulo' => '4. Reglas de contenido',
         'cuerpo' => 'Está expresamente PERMITIDO publicar:
• Fotografías y videos de personas adultas verificadas, incluido contenido sexualmente explícito o desnudos.
• Descripciones, tarifas y datos de contacto de los servicios ofrecidos por el anunciante adulto.

Está absolutamente PROHIBIDO y será motivo de eliminación inmediata, suspensión de cuenta y, cuando proceda, denuncia ante autoridades:
• Cualquier material que involucre o sugiera la participación de menores de edad (tolerancia cero).
• Contenido obtenido o difundido sin el consentimiento expreso y libre de las personas que aparecen en él, incluido el llamado "porno de venganza" y la difusión no consensuada de imágenes íntimas.
• Promoción de trata de personas, lenocinio, explotación sexual o cualquier forma de coerción.
• Bestialidad, necrofilia, violación, snuff, violencia real o cualquier otra conducta tipificada como delito.
• Contenido protegido por derechos de autor del que no se posean derechos o autorización para publicar.
• Suplantación de identidad o uso de fotografías o videos de terceros sin su consentimiento.
• Datos personales de terceros, mensajes de odio, amenazas, acoso, fraude, spam o cualquier forma de actividad ilícita.

' . APP_NAME . ' modera el contenido antes de publicarlo, pero no garantiza que la moderación sea infalible. La detección de cualquier contenido prohibido posterior a la publicación dará lugar a su retiro inmediato.'],

        ['titulo' => '5. Declaraciones del usuario al publicar contenido',
         'cuerpo' => 'Al subir cualquier fotografía, video, texto o cualquier otro contenido al Sitio, declaras bajo protesta de decir verdad que:
• Eres mayor de 18 años, así como toda persona que aparezca en el contenido.
• Eres titular de los derechos sobre el contenido o cuentas con las autorizaciones necesarias para publicarlo.
• Cuentas con el consentimiento libre, expreso e informado de cada persona identificable en el contenido para que sea publicado en el Sitio con fines comerciales y publicitarios.
• El contenido no infringe derechos de autor, marca, imagen o intimidad de terceros.
• El contenido no es producto de coerción, engaño, trata de personas o cualquier delito.

Cualquier declaración falsa expone al usuario a la responsabilidad civil, penal y administrativa que corresponda, y faculta al Sitio para retener el contenido para fines probatorios.'],

        ['titulo' => '6. Licencia limitada otorgada al Sitio',
         'cuerpo' => 'Al publicar contenido conservas la titularidad de tus derechos. Sin embargo, otorgas a ' . APP_NAME . ' una licencia mundial, no exclusiva, gratuita, sub-licenciable y limitada para almacenar, reproducir, transformar (redimensionar, comprimir, generar miniaturas), comunicar públicamente y mostrar el contenido dentro del Sitio y de sus servicios asociados, con la única finalidad de operar la plataforma.

La licencia termina cuando eliminas el contenido o cuando el Sitio retira el contenido por violar estos Términos. Las copias de respaldo y los registros legales podrán conservarse por el tiempo legalmente exigible.'],

        ['titulo' => '7. Indemnización',
         'cuerpo' => 'El usuario libera de responsabilidad e indemnizará a ' . APP_NAME . ', a sus desarrolladores, operadores, empleados y proveedores frente a cualquier reclamación, demanda, sanción o pérdida (incluidos honorarios de abogados) que se derive de:
• El contenido publicado por el usuario.
• La violación de estos Términos por el usuario.
• La infracción de derechos de terceros causada por el usuario.
• Las transacciones, acuerdos o relaciones que el usuario establezca con otras personas a partir del Sitio.

Esta obligación de indemnización subsiste tras la terminación de la cuenta del usuario.'],

        ['titulo' => '8. Reportes y procesos de retiro',
         'cuerpo' => APP_NAME . ' opera con procesos formales para reportar y retirar contenido ilícito o no autorizado:
• Contenido que involucre o aparente involucrar a menores: se reporta de inmediato a las autoridades competentes y a las redes internacionales de protección.
• Contenido íntimo difundido sin consentimiento: se retira en el menor tiempo posible tras recibir un reporte verificable de la persona afectada o su representante.
• Contenido que infrinja derechos de autor o de imagen: se atiende conforme a la Política de Derechos de Autor disponible en /dmca.

Cualquier persona puede reportar contenido desde el botón "Reportar" disponible en cada perfil o escribiendo a legal@placerselecto.com.'],

        ['titulo' => '9. Suspensión y terminación',
         'cuerpo' => APP_NAME . ' se reserva el derecho de suspender, restringir o cancelar cuentas, así como de retirar contenido, sin necesidad de previo aviso y sin obligación de reembolso, en los siguientes casos:
• Violación de estos Términos o de la Política de Contenido.
• Sospecha fundada de actividad ilícita.
• Falsedad en las declaraciones del usuario o en la verificación de identidad.
• Requerimiento de autoridad competente.
• Conducta abusiva contra otros usuarios, moderadores o el Sitio.

El usuario puede solicitar la cancelación de su cuenta y la eliminación de su contenido en cualquier momento, conforme al procedimiento de derechos ARCO descrito en el Aviso de Privacidad.'],

        ['titulo' => '10. Pagos y servicios premium',
         'cuerpo' => 'Los servicios de pago dentro del Sitio (paquetes de tokens, destacados, promociones) son prepagados y no reembolsables, salvo cuando la ley aplicable lo exija. Los pagos se procesan a través de proveedores externos sujetos a sus propias políticas. ' . APP_NAME . ' no almacena datos completos de tarjeta de crédito o débito.

Los servicios premium se activan automáticamente al confirmarse el pago y se consumen conforme al plan adquirido. Las disputas relacionadas con cargos deben presentarse a legal@placerselecto.com dentro de los 30 días naturales siguientes al cargo.'],

        ['titulo' => '11. Privacidad y datos personales',
         'cuerpo' => 'El tratamiento de los datos personales se realiza conforme a nuestro Aviso de Privacidad, disponible en /privacidad. Al usar el Sitio reconoces haberlo leído y consentir el tratamiento de tus datos en los términos ahí descritos.'],

        ['titulo' => '12. Limitación de responsabilidad',
         'cuerpo' => APP_NAME . ' se ofrece "tal cual" y "según disponibilidad". El Sitio no es responsable de:
• El contenido publicado por los usuarios, ni de su veracidad, calidad, legalidad o exactitud.
• Los acuerdos, transacciones o consecuencias de la interacción entre usuarios fuera del Sitio.
• Daños indirectos, lucro cesante, pérdida de datos o interrupciones del servicio derivados de fallos técnicos, ataques de terceros o causas de fuerza mayor.

La responsabilidad máxima de ' . APP_NAME . ', en cualquier caso, queda limitada al monto efectivamente pagado por el usuario al Sitio durante los doce meses anteriores al hecho que origine la reclamación.'],

        ['titulo' => '13. Modificaciones',
         'cuerpo' => APP_NAME . ' podrá modificar estos Términos en cualquier momento. La versión vigente se publicará en esta misma página con la fecha de su última actualización. El uso continuado del Sitio después de la publicación de los cambios implica su aceptación. Cuando los cambios sean sustanciales, el Sitio realizará un esfuerzo razonable por notificarlos a los usuarios registrados.'],

        ['titulo' => '14. Ley aplicable',
         'cuerpo' => 'Estos Términos se rigen por las leyes de los Estados Unidos Mexicanos. Cualquier controversia que no pueda resolverse mediante diálogo se someterá a los tribunales mexicanos competentes conforme a la legislación procesal aplicable.'],

        ['titulo' => '15. Contacto',
         'cuerpo' => 'Para cualquier asunto relacionado con estos Términos, denuncias, requerimientos de autoridad o ejercicio de derechos, escribe a:
legal@placerselecto.com'],
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
        <a href="<?= APP_URL ?>/privacidad" class="me-3">Aviso de Privacidad</a>
        <a href="<?= APP_URL ?>/mayores-18" class="me-3">Aviso +18</a>
        <a href="<?= APP_URL ?>/dmca" class="me-3">Derechos de Autor</a>
        <a href="<?= APP_URL ?>/2257">Declaración 2257</a>
    </div>
</div>
