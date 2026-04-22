<div class="container py-5" style="max-width:860px">

    <div class="mb-4">
        <h1 class="h3 fw-bold mb-1">Términos y condiciones</h1>
        <p class="text-muted" style="font-size:.85rem">Última actualización: <?= date('d/m/Y') ?></p>
    </div>

    <?php
    $secciones = [
        ['titulo' => '1. Aceptación',
         'cuerpo' => 'Al acceder o usar ' . APP_NAME . ' (el "Sitio"), confirmas tener al menos 18 años de edad y aceptas estos Términos en su totalidad. Si no estás de acuerdo, debes abandonar el Sitio inmediatamente.'],

        ['titulo' => '2. Naturaleza del servicio',
         'cuerpo' => APP_NAME . ' es una plataforma de clasificados publicitarios que actúa exclusivamente como intermediario entre anunciantes adultos. El Sitio no produce, aloja ni distribuye contenido explícito o pornográfico. No ofrece ni facilita servicios sexuales directamente.'],

        ['titulo' => '3. Requisito de mayoría de edad',
         'cuerpo' => 'El uso del Sitio está estrictamente restringido a personas mayores de 18 años (o la mayoría de edad legal en tu jurisdicción, si es mayor). Al registrarte confirmas bajo protesta de decir verdad que cumples este requisito. El Sitio se reserva el derecho de suspender cuentas donde se sospeche que el usuario es menor de edad.'],

        ['titulo' => '4. Conducta del usuario',
         'cuerpo' => 'Está terminantemente prohibido:
• Publicar anuncios que involucren o sugieran la participación de menores de edad.
• Publicar contenido explícito, pornográfico, violento o ilegal.
• Usar el Sitio para actividades de trata de personas, explotación sexual o cualquier otra actividad ilegal.
• Suplantar la identidad de otras personas o proporcionar información falsa.
• Realizar spam, ataques informáticos o cualquier acción que afecte la integridad del Sitio.
El incumplimiento resultará en la suspensión permanente de la cuenta y podrá ser reportado a las autoridades competentes.'],

        ['titulo' => '5. Publicación de anuncios',
         'cuerpo' => 'Los anuncios son revisados por nuestro equipo antes de ser publicados. Nos reservamos el derecho de rechazar, editar o eliminar cualquier anuncio que viole estos Términos, sin previo aviso y sin derecho a reembolso. El usuario es el único responsable del contenido de sus anuncios.'],

        ['titulo' => '6. Planes de destacado y pagos',
         'cuerpo' => 'Los planes de destacado son servicios prepagados no reembolsables. Al completar el pago autorizas el cargo correspondiente. Los planes se activan de forma inmediata y expiran automáticamente al término del período contratado.'],

        ['titulo' => '7. Privacidad',
         'cuerpo' => 'Tratamos tus datos conforme a nuestro Aviso de Privacidad. No vendemos información personal a terceros. Podemos compartir datos con autoridades cuando la ley lo exija.'],

        ['titulo' => '8. Limitación de responsabilidad',
         'cuerpo' => APP_NAME . ' no es responsable del contenido publicado por los usuarios, ni de las transacciones o acuerdos que puedan surgir entre anunciantes y contactos. El Sitio se provee "tal cual", sin garantías de ningún tipo.'],

        ['titulo' => '9. Modificaciones',
         'cuerpo' => 'Podemos modificar estos Términos en cualquier momento. El uso continuado del Sitio tras la publicación de cambios implica tu aceptación de los nuevos términos.'],

        ['titulo' => '10. Ley aplicable',
         'cuerpo' => 'Estos Términos se rigen por las leyes de los Estados Unidos Mexicanos. Cualquier controversia se someterá a los tribunales competentes de la Ciudad de México.'],
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
        <a href="<?= APP_URL ?>/privacidad" class="me-3">Aviso de privacidad</a>
        <a href="<?= APP_URL ?>/mayores-18">Aviso +18</a>
    </div>
</div>
