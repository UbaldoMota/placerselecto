<div class="container py-5" style="max-width:860px">

    <div class="mb-4">
        <h1 class="h3 fw-bold mb-1">Política de Pagos y Devoluciones</h1>
        <p class="text-muted" style="font-size:.85rem">Última actualización: <?= date('d/m/Y') ?></p>
    </div>

    <div class="alert alert-info" style="font-size:.9rem;line-height:1.7">
        <i class="bi bi-info-circle me-2"></i>
        <strong><?= e(APP_NAME) ?></strong> permite el uso gratuito de las funciones básicas
        del Sitio. Los servicios premium son opcionales y se contratan voluntariamente
        para destacar tu perfil.
    </div>

    <?php
    $secciones = [
        ['titulo' => '1. Servicios disponibles',
         'cuerpo' => 'Funciones gratuitas:
• Crear y gestionar tu cuenta de usuario.
• Publicar hasta tres (3) perfiles por cuenta tras el proceso de verificación de identidad.
• Subir fotografías y videos a tus perfiles dentro de los límites del Sitio.
• Recibir contactos y comentarios moderados.
• Estadísticas de visitas y clics de tus perfiles.

Funciones premium (opcionales, de pago con tokens):
• Tokens: saldo virtual que se adquiere mediante paquetes y se consume al activar boosts. Los tokens son la única moneda interna del Sitio. No tienen valor monetario fuera del Sitio, no son canjeables por dinero, ni transferibles a otra cuenta.
• Boost TOP: hace que tu perfil aparezca en las primeras posiciones del listado de tu municipio durante el tiempo que elijas. Cuando varias usuarias tienen TOP activo simultáneamente en el mismo municipio, el orden entre ellas rota automáticamente cada minuto para garantizar visibilidad equitativa. Tarifa por hora medida en tokens.
• Boost RESALTADO: cambia únicamente la apariencia visual de tu tarjeta en los listados (fondo amarillo distintivo) para llamar la atención. No altera la posición de tu perfil, ni la rotación entre los TOP. Tarifa por hora medida en tokens, generalmente menor que la del TOP.

El contenido de cada paquete (cantidad de tokens y precio en MXN) y la tarifa de cada tipo de boost se muestran en el panel de usuario y pueden actualizarse periódicamente. El precio y la tarifa aplicables son siempre los que se muestran al momento de confirmar la operación.'],

        ['titulo' => '2. Métodos de pago',
         'cuerpo' => 'Los pagos para adquirir paquetes de tokens se procesan a través de proveedores externos autorizados. Los métodos disponibles pueden incluir:
• Tarjeta de crédito o débito (Visa, MasterCard).
• Transferencia bancaria (SPEI).
• Efectivo en tiendas de conveniencia.
• Otros métodos electrónicos disponibles según el proveedor de pagos vigente.

Los métodos exactos se muestran en el panel de usuario al momento de la compra. Los pagos con tarjeta se procesan con cumplimiento PCI-DSS por proveedores externos. ' . APP_NAME . ' nunca recibe ni almacena el número completo de tu tarjeta, el código CVC ni la fecha de vencimiento. De cada transacción conservamos únicamente el identificador, el monto, los últimos cuatro dígitos de la tarjeta cuando aplique, y los datos mínimos para la emisión del comprobante.

En cuanto el proveedor de pagos confirma la recepción del importe acordado, los tokens se acreditan al saldo de tu cuenta de forma automática.'],

        ['titulo' => '3. Activación del servicio',
         'cuerpo' => 'Pagos con tarjeta: el servicio premium se activa automáticamente al confirmarse el pago, normalmente en menos de un minuto. Si el pago se procesa pero el servicio no se activa, escribe a legal@placerselecto.com indicando el ID de la transacción y revisaremos manualmente.

Pagos por otros métodos (transferencia, efectivo): se activan tras la confirmación manual de la recepción del pago. El plazo habitual de activación es de 30 minutos a 4 horas en horario hábil.'],

        ['titulo' => '4. Pagos recurrentes',
         'cuerpo' => '' . APP_NAME . ' NO realiza cargos recurrentes ni cargos automáticos. Tampoco suscribimos a los usuarios a planes que se renueven solos.

Cada vez que quieras adquirir un servicio premium, debes ingresar nuevamente los datos de tu medio de pago a través de la pasarela del proveedor externo. ' . APP_NAME . ' no almacena tu información bancaria.'],

        ['titulo' => '5. Política de devoluciones',
         'cuerpo' => 'Distinguimos dos tipos de reembolso porque los tokens son la moneda interna del Sitio y los boosts son consumos de esos tokens.

A) Reembolso del paquete de tokens al medio de pago original
Si solicitas la cancelación de la compra de un paquete dentro de las primeras veinticuatro (24) horas posteriores al pago Y los tokens del paquete no han sido consumidos (ni siquiera parcialmente en un boost), se reembolsa el cien por ciento (100%) del importe pagado al medio de pago original (tarjeta o método de origen). Una vez consumido cualquier token del paquete, no procede el reembolso al medio de pago original.

B) Cancelación de un boost (reembolso al saldo de tokens)
Cuando ya tienes tokens en tu saldo y los gastas en un boost, las reglas son:
• Boost programado (aún no ha iniciado): puedes cancelarlo en cualquier momento desde el panel del perfil. Se devuelve el cien por ciento (100%) de los tokens gastados al saldo de tu cuenta. No se reembolsa al medio de pago original.
• Boost activo (corriendo): no es cancelable. Los tokens están consumiéndose en visibilidad efectiva en el listado, por lo que no procede reembolso.
• Boost finalizado: no aplica reembolso.

Forma y plazo del reembolso al medio de pago:
El reembolso del tipo A se procesa por la misma vía utilizada para el pago original. Una vez aprobado, el reflejo en tu cuenta o tarjeta depende del banco emisor y del proveedor de pagos, lo cual puede tardar entre siete (7) y treinta (30) días hábiles. ' . APP_NAME . ' notifica por correo cuando el reembolso ha sido emitido. La cancelación de boosts del tipo B se aplica al saldo de tokens de forma inmediata, sin intervención del banco.

Casos excluidos del reembolso:
No procede reembolso, total ni parcial, cuando la cuenta o el contenido sean retirados por:
• Fraude o intento de fraude.
• Extorsión, amenaza, suplantación o publicación de datos de terceros.
• Uso de fotografías o videos sin derechos sobre ellos.
• Verificación de identidad fallida o negativa a entregar identificación cuando se solicite.
• Cualquier otro incumplimiento grave de los Términos y Condiciones.

Estas exclusiones no aplican cuando el contenido del usuario sea retirado por moderación discrecional del Sitio sin que medie incumplimiento. En ese caso aplica la regla A si el paquete está dentro de las 24 horas y sin tokens consumidos.

Límite anti-abuso:
Para prevenir el uso fraudulento de la política de devoluciones (contratar paquetes con la única intención de cancelarlos), nos reservamos el derecho de limitar a dos (2) devoluciones del tipo A por usuario en un periodo de doce (12) meses. La cancelación de boosts programados (tipo B) no tiene límite de uso, dado que solo reembolsa al saldo interno de tokens y no implica movimiento bancario.'],

        ['titulo' => '6. Disputas',
         'cuerpo' => 'Las disputas relacionadas con cargos deben presentarse a legal@placerselecto.com dentro de los treinta (30) días naturales siguientes al cargo, indicando:
• ID de la transacción o referencia del cargo.
• Monto y fecha del cargo.
• Motivo detallado de la disputa.
• Cualquier evidencia que respalde tu reclamación (capturas, correos, etc.).

Atenderemos tu solicitud lo antes posible. Si la disputa no puede resolverse de mutuo acuerdo, podrá someterse a las vías legales correspondientes en territorio mexicano.'],

        ['titulo' => '7. Política de fraude en pagos',
         'cuerpo' => '' . APP_NAME . ' aplica tolerancia cero al fraude en pagos. La detección de cualquiera de las siguientes conductas implica la suspensión inmediata e indefinida de la cuenta del usuario, la cancelación de servicios contratados sin reembolso y, en su caso, la denuncia ante autoridades:
• Uso de tarjetas de crédito o débito ajenas, robadas o sin autorización del titular.
• Pagos provenientes de cuentas o tarjetas con historial de contracargos abusivos.
• Pago de perfiles que no son del titular de la cuenta de pago (cada usuario debe pagar su propio perfil).
• Cualquier intento de manipulación de la pasarela de pagos o del Sitio para obtener servicios sin pago efectivo.

' . APP_NAME . ' colabora con los proveedores de pago en investigaciones de fraude y en la respuesta a contracargos.'],

        ['titulo' => '8. Comprobantes',
         'cuerpo' => 'Por cada pago confirmado se emite un comprobante simplificado en el panel de usuario que incluye fecha, monto, producto contratado y referencia de la transacción.

Si requieres factura fiscal con datos completos para deducción, escribe a legal@placerselecto.com indicando: nombre o razón social, RFC, dirección fiscal, uso del CFDI y la referencia del pago. Atenderemos las solicitudes dentro del mes calendario en que se realizó el pago, conforme a las disposiciones fiscales aplicables.'],

        ['titulo' => '9. Cambios a esta política',
         'cuerpo' => '' . APP_NAME . ' puede actualizar esta política cuando introduzca nuevos productos, modifique tarifas o cuando lo exijan las disposiciones legales. La versión vigente se publicará siempre en esta página con la fecha de la última actualización.

Los cambios sustanciales en condiciones de pago o devolución se notificarán con al menos quince (15) días naturales de antelación a través de aviso destacado en el Sitio o por correo electrónico.'],

        ['titulo' => '10. Contacto',
         'cuerpo' => 'Para cualquier asunto relacionado con pagos, devoluciones o disputas:
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
        <a href="<?= APP_URL ?>/privacidad" class="me-3">Aviso de Privacidad</a>
        <a href="<?= APP_URL ?>/cookies" class="me-3">Cookies</a>
        <a href="<?= APP_URL ?>/mayores-18" class="me-3">Aviso +18</a>
        <a href="<?= APP_URL ?>/dmca" class="me-3">Derechos de Autor</a>
        <a href="<?= APP_URL ?>/2257">Declaración 2257</a>
    </div>
</div>
