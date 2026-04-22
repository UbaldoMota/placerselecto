<div class="container py-5" style="max-width:860px">

    <div class="mb-4">
        <h1 class="h3 fw-bold mb-1">Aviso de privacidad</h1>
        <p class="text-muted" style="font-size:.85rem">Última actualización: <?= date('d/m/Y') ?></p>
    </div>

    <?php
    $secciones = [
        ['titulo' => 'Responsable del tratamiento',
         'cuerpo' => APP_NAME . ' es el responsable del tratamiento de tus datos personales, con domicilio en México.'],

        ['titulo' => 'Datos que recopilamos',
         'cuerpo' => 'Recopilamos los siguientes datos personales:
• Nombre o apodo
• Dirección de correo electrónico
• Número de teléfono / WhatsApp (opcional)
• Dirección IP de registro y de acceso
• Imágenes subidas por el usuario
• Información de pago (procesada por terceros — no almacenamos datos de tarjeta)'],

        ['titulo' => 'Finalidad del tratamiento',
         'cuerpo' => 'Tus datos se usan para:
• Crear y gestionar tu cuenta de usuario
• Publicar y moderar anuncios
• Procesar pagos de planes de destacado
• Prevenir fraude, abuso y actividades ilegales
• Cumplir con obligaciones legales
• Enviar comunicaciones relacionadas con el servicio (no publicidad de terceros)'],

        ['titulo' => 'Base legal',
         'cuerpo' => 'El tratamiento se basa en: tu consentimiento explícito al registrarte, la ejecución del contrato de servicios, y el cumplimiento de obligaciones legales.'],

        ['titulo' => 'Conservación de datos',
         'cuerpo' => 'Conservamos tus datos mientras tu cuenta esté activa o sea necesario para cumplir obligaciones legales. Puedes solicitar la eliminación de tu cuenta y datos en cualquier momento.'],

        ['titulo' => 'Transferencia de datos',
         'cuerpo' => 'No vendemos ni cedemos tus datos a terceros con fines comerciales. Podemos compartirlos con:
• Autoridades competentes cuando lo exija la ley
• Procesadores de pago para completar transacciones
• Proveedores de infraestructura técnica bajo contratos de confidencialidad'],

        ['titulo' => 'Cookies y tecnologías de rastreo',
         'cuerpo' => 'Usamos cookies de sesión esenciales para el funcionamiento del Sitio. No usamos cookies de terceros para publicidad. Puedes configurar tu navegador para bloquear cookies, aunque algunas funciones del Sitio podrían verse afectadas.'],

        ['titulo' => 'Tus derechos (ARCO)',
         'cuerpo' => 'Conforme a la Ley Federal de Protección de Datos Personales en Posesión de Particulares (LFPDPPP), tienes derecho a:
• Acceso: conocer qué datos tenemos sobre ti
• Rectificación: corregir datos inexactos
• Cancelación: solicitar la eliminación de tus datos
• Oposición: oponerte al tratamiento de tus datos
Para ejercer estos derechos, contáctanos en: privacidad@' . strtolower(APP_NAME) . '.com'],

        ['titulo' => 'Seguridad',
         'cuerpo' => 'Implementamos medidas técnicas y organizativas para proteger tus datos: cifrado de contraseñas con bcrypt, HTTPS, tokens CSRF, y acceso restringido a datos sensibles. Ningún sistema es 100% seguro; en caso de una brecha de seguridad que afecte tus derechos, te notificaremos conforme a la ley.'],

        ['titulo' => 'Cambios a este aviso',
         'cuerpo' => 'Podemos actualizar este aviso. La versión vigente siempre estará disponible en esta página. El uso continuado del Sitio implica la aceptación de los cambios.'],
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
        <a href="<?= APP_URL ?>/terminos" class="me-3">Términos y condiciones</a>
        <a href="<?= APP_URL ?>/mayores-18">Aviso +18</a>
    </div>
</div>
