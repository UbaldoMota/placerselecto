<div class="container py-5" style="max-width:860px">

    <div class="mb-4">
        <h1 class="h3 fw-bold mb-1">Aviso de Privacidad</h1>
        <p class="text-muted" style="font-size:.85rem">Última actualización: <?= date('d/m/Y') ?></p>
    </div>

    <?php
    $secciones = [
        ['titulo' => '1. Responsable del tratamiento',
         'cuerpo' => 'El responsable del tratamiento de tus datos personales es Alberto Figueroa, operador de ' . APP_NAME . ' (en adelante, el "Sitio"), con sede de operación en Estado de México, México.

Para cualquier asunto relacionado con la protección de tus datos personales, escribe a:
legal@placerselecto.com'],

        ['titulo' => '2. Tipología de usuarios',
         'cuerpo' => 'El presente Aviso aplica a tres tipos de usuarios del Sitio:

(a) Visitantes: personas que acceden al Sitio sin registrarse, únicamente para navegar.
(b) Publicadores: usuarios registrados que crean perfiles publicitarios y publican fotografías, videos y datos de contacto.
(c) Comentaristas y reportadores: usuarios registrados o no que escriben comentarios, valoraciones, denuncias o usan formularios de contacto.

Cada tipo de usuario implica un tratamiento de datos diferenciado, descrito a continuación.'],

        ['titulo' => '3. Datos que recopilamos',
         'cuerpo' => 'De los Visitantes:
• No recopilamos datos personales identificables. Solamente datos técnicos esenciales (IP, hora de conexión y cookies de sesión y de seguridad) durante la navegación.

De los Publicadores:
• Datos de cuenta: nombre o apodo, correo electrónico, contraseña cifrada, número de teléfono.
• Datos del perfil: descripción, edad, ubicación, fotografías, videos.
• Documento oficial de identidad y video de verificación, exigidos para validar la mayoría de edad y la identidad del titular del perfil.
• Datos de pago (cuando contratas servicios premium): nombre, IP de la transacción y, en su caso, los últimos cuatro dígitos de la tarjeta. NO almacenamos el número completo de tarjeta, ni el CVC, ni la fecha de vencimiento; el procesamiento de pagos lo realizan proveedores externos sujetos a sus propias políticas.
• Datos técnicos: IP, hora de conexión, agente de usuario (navegador y dispositivo).

De los Comentaristas y reportadores:
• Apodo, correo electrónico, en su caso teléfono.
• El contenido escrito en comentarios, denuncias o formularios.
• IP y hora de conexión.'],

        ['titulo' => '4. Datos públicos vs. privados',
         'cuerpo' => 'De los datos asociados a un Publicador, los siguientes serán PÚBLICOS dentro del Sitio para cumplir con la finalidad del servicio:
• Apodo o nombre artístico.
• Descripción del perfil, edad declarada, categoría, estado, municipio y zona aproximada.
• Fotografías y videos publicados (excluido el material de verificación).
• Forma o formas de contacto que el usuario decida exponer (por ejemplo, WhatsApp).

Los siguientes serán siempre PRIVADOS y solo se usan internamente o se revelan ante requerimiento legal:
• Documento oficial de identidad y video de verificación.
• Contraseña, IP, hora de conexión, agente de usuario.
• Correo electrónico y formas de contacto que el usuario decida no exponer.
• Datos de pago.
• Cualquier dato de los Comentaristas y reportadores.'],

        ['titulo' => '5. Finalidades del tratamiento',
         'cuerpo' => 'Tus datos se utilizan para:
• Crear y gestionar tu cuenta y tus perfiles.
• Verificar tu mayoría de edad e identidad.
• Moderar el contenido publicado en el Sitio.
• Procesar pagos de servicios premium.
• Prevenir fraude, abuso y actividades ilegales.
• Atender denuncias, reportes, solicitudes y quejas.
• Cumplir con obligaciones legales y atender requerimientos de autoridades competentes.
• Enviar comunicaciones relacionadas con el servicio (alertas de moderación, recuperación de contraseña, avisos de cambios en políticas). NO enviamos publicidad de terceros.'],

        ['titulo' => '6. Conservación de datos',
         'cuerpo' => 'La información personal se conserva mientras tu cuenta esté activa o sea necesaria para las finalidades descritas.

Cuando solicitas la eliminación de tu cuenta o cuando una cuenta permanece sin actividad por más de 24 meses consecutivos, los datos públicos se retiran del Sitio. La información mínima imprescindible para acreditar el origen de los contenidos y la fecha en que se prestó el servicio se conserva por el plazo legalmente exigible para atender posibles requerimientos de autoridad o reclamaciones.

Las cuentas marcadas por fraude, denuncia o incumplimiento de las normas del Sitio podrán conservarse por el plazo necesario para concluir la investigación o atender procedimientos abiertos.'],

        ['titulo' => '7. Datos públicos copiados por terceros',
         'cuerpo' => 'Los datos publicados con carácter público (apodos, fotografías, descripciones, datos de contacto que el usuario decida exponer) son visibles para todos los visitantes del Sitio. ' . APP_NAME . ' no controla ni se hace responsable de copias no autorizadas que terceros realicen mediante captura, descarga, "scraping" u otros medios.

Si detectas que tu contenido ha sido reproducido en otro sitio sin tu autorización, repórtalo a legal@placerselecto.com y haremos los esfuerzos razonables para colaborar en su retiro. Sin embargo, ' . APP_NAME . ' no es responsable de la conducta de terceros sobre datos que ya eran de carácter público en el Sitio. Sí somos plenamente responsables de los datos privados que custodiamos.'],

        ['titulo' => '8. Transferencia de datos',
         'cuerpo' => 'No vendemos ni cedemos tus datos a terceros con fines comerciales. Podemos compartirlos con:
• Autoridades competentes cuando lo exija la ley.
• Procesadores de pago para completar transacciones.
• Proveedores de infraestructura técnica bajo contrato de confidencialidad, en particular:
  - Cloudflare, Inc. (Estados Unidos): actúa como CDN y firewall, recibiendo IP y cabeceras HTTP con la finalidad de proteger al Sitio contra ataques.
  - Hosting cPanel operado en infraestructura ubicada en territorio europeo: aloja la base de datos y archivos del Sitio.
  - Proveedor de envío de SMS: recibe el número de teléfono únicamente para entregar el código de verificación.
  - Proveedor de envío de correo electrónico (SMTP): recibe la dirección destinataria para entregar mensajes de verificación, recuperación y notificaciones.

Cuando estos proveedores se ubican fuera de México, su tratamiento de datos se realiza bajo contrato y aplicando estándares equivalentes a los exigidos por la legislación mexicana.'],

        ['titulo' => '9. Cookies y tecnologías de rastreo',
         'cuerpo' => 'Usamos cookies de sesión esenciales para el funcionamiento del Sitio (CLASIF_SESS) y una cookie de seguridad de Cloudflare (__cf_bm) que ayuda a distinguir tráfico humano de bots. NO usamos cookies de terceros para publicidad ni rastreo cross-site. Puedes configurar tu navegador para bloquear cookies, aunque ciertas funciones del Sitio podrían verse afectadas.'],

        ['titulo' => '10. Tus derechos (ARCO)',
         'cuerpo' => 'Conforme a la Ley Federal de Protección de Datos Personales en Posesión de los Particulares (LFPDPPP), tienes derecho a:

• ACCESO: conocer qué datos personales tenemos sobre ti, su origen, las finalidades de su tratamiento y a quién se han transferido.
• RECTIFICACIÓN: corregir datos inexactos, incompletos o desactualizados.
• CANCELACIÓN: solicitar la eliminación de tus datos cuando ya no sean necesarios o cuando consideres que su tratamiento no se ajusta a la ley.
• OPOSICIÓN: oponerte al tratamiento de tus datos para finalidades específicas o, en general, por causa legítima.

Adicionalmente puedes:
• REVOCAR tu consentimiento en cualquier momento.
• Solicitar la PORTABILIDAD de los datos que nos has proporcionado en formato estructurado y de uso común.
• PRESENTAR una queja ante el Instituto Nacional de Transparencia, Acceso a la Información y Protección de Datos Personales (INAI) si consideras que tu derecho ha sido vulnerado.

Para ejercer estos derechos escribe a legal@placerselecto.com indicando:
(a) tu nombre completo y un medio de contacto válido,
(b) los documentos que acrediten tu identidad,
(c) la descripción clara y precisa del derecho que ejerces y, en su caso, los datos a los que se refiere.

Atenderemos tu solicitud en el plazo legal aplicable, que normalmente es de 20 días hábiles. La cancelación o supresión queda condicionada a las obligaciones legales de retención previstas en la sección 6.'],

        ['titulo' => '11. Comunicaciones',
         'cuerpo' => '' . APP_NAME . ' no envía publicidad ni promociones de terceros por correo electrónico, SMS u otro medio.

Las comunicaciones que sí podemos enviarte son exclusivamente operativas y necesarias para el servicio:
• Códigos de verificación al registrarte o recuperar contraseña.
• Avisos de moderación (perfil aprobado, rechazado, comentario respondido).
• Notificaciones de seguridad (intento de acceso sospechoso, brecha de seguridad).
• Comunicación de cambios sustanciales en estos avisos legales.

Si en el futuro implementáramos comunicaciones promocionales propias, las pediremos con consentimiento expreso adicional y siempre con opción de baja en cada mensaje.'],

        ['titulo' => '12. Seguridad',
         'cuerpo' => 'Implementamos medidas técnicas y organizativas razonables para proteger tus datos: cifrado de contraseñas con bcrypt, conexiones HTTPS, tokens CSRF en formularios, acceso restringido a datos sensibles, y un firewall de aplicación (Cloudflare WAF) con detección de bots y mitigación de denegación de servicio.

Ningún sistema es 100% seguro y pueden existir actuaciones dolosas de terceros que estén fuera de nuestro control. Nos comprometemos a actuar con diligencia ante cualquier incidente y, cuando una eventual brecha pueda afectar significativamente tus derechos, a notificarte conforme a la ley aplicable.'],

        ['titulo' => '13. Cambios a este aviso',
         'cuerpo' => 'Podemos actualizar este Aviso de Privacidad en cualquier momento, especialmente cuando se produzcan modificaciones legislativas o cambios en el servicio. La versión vigente se publicará siempre en esta página con la fecha de la última actualización.

Cuando los cambios sean sustanciales y afecten directamente al tratamiento de tus datos, los notificaremos con al menos 30 días naturales de antelación a través de un aviso destacado en el Sitio o por correo electrónico a tu cuenta registrada.'],

        ['titulo' => '14. Contacto',
         'cuerpo' => 'Para cualquier asunto relacionado con la protección de tus datos personales, ejercicio de derechos o quejas:
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
        <a href="<?= APP_URL ?>/terminos" class="me-3">Términos y Condiciones</a>
        <a href="<?= APP_URL ?>/mayores-18" class="me-3">Aviso +18</a>
        <a href="<?= APP_URL ?>/dmca" class="me-3">Derechos de Autor</a>
        <a href="<?= APP_URL ?>/2257">Declaración 2257</a>
    </div>
</div>
