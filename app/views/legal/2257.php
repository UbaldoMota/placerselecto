<div class="container py-5" style="max-width:860px">

    <div class="mb-4">
        <h1 class="h3 fw-bold mb-1">Declaración de cumplimiento de verificación de edad</h1>
        <p class="text-muted" style="font-size:.85rem">
            Equivalente a la sección 2257 (United States Code, Title 18, Section 2257)<br>
            Última actualización: <?= date('d/m/Y') ?>
        </p>
    </div>

    <div class="alert alert-info" style="font-size:.9rem;line-height:1.7">
        <i class="bi bi-shield-check me-2"></i>
        Todos los modelos, intérpretes, autores o personas que aparecen en cualquier
        representación visual de conducta sexualmente explícita publicada en
        <strong><?= e(APP_NAME) ?></strong> tenían 18 años de edad cumplidos al momento
        de la captura del material.
    </div>

    <?php
    $secciones = [
        ['titulo' => '1. Naturaleza de esta declaración',
         'cuerpo' => APP_NAME . ' es una plataforma operada bajo la legislación de los Estados Unidos Mexicanos. Si bien la sección 2257 del título 18 del Código de los Estados Unidos no aplica directamente a operadores fuera de su jurisdicción, esta declaración se publica como buena práctica internacional para acreditar el cumplimiento de los estándares más exigentes de verificación de edad y consentimiento de los modelos cuyo material se aloja en el Sitio.'],

        ['titulo' => '2. Alcance del contenido cubierto',
         'cuerpo' => 'Esta declaración cubre todas las representaciones visuales (fotografías, videos, capturas, miniaturas) de conducta sexualmente explícita o desnudos publicados en el Sitio por usuarios verificados a partir de la fecha de su entrada en operación. Las publicaciones pasan por verificación previa antes de ser visibles al público.'],

        ['titulo' => '3. Sistema de verificación de edad',
         'cuerpo' => 'Antes de poder publicar contenido visual, todo usuario debe completar el proceso de verificación obligatorio del Sitio, que incluye:

(1) Documento oficial de identidad vigente
    • Credencial para votar (INE), pasaporte u otro documento oficial mexicano que contenga fotografía, fecha de nacimiento y firma.

(2) Foto de verificación facial
    • Selfie en tiempo real que permita comparar al usuario con la fotografía del documento oficial.

(3) Video corto de verificación (mínimo 5 segundos)
    • Grabado en el momento por la cámara del dispositivo del usuario, con el rostro completo visible y siguiendo instrucciones específicas que confirman que es la persona real (no una imagen estática ni un deepfake).

(4) Aceptación expresa de los Términos y Condiciones, el Aviso de Privacidad y la Política de Contenido.

El proceso de verificación queda registrado con marca de tiempo, dirección IP y dispositivo, y debe ser aprobado manualmente por un moderador del Sitio antes de habilitar la publicación de contenido.'],

        ['titulo' => '4. Custodio de los registros',
         'cuerpo' => 'Los registros de verificación se conservan en sistemas seguros, con acceso restringido y cifrado, durante el tiempo legalmente exigible. El custodio designado para fines de esta declaración y para atender requerimientos de autoridad es:

' . e(APP_NAME) . '
Correo electrónico: legal@placerselecto.com

Las solicitudes de información sobre verificación de edad de un modelo específico deben presentarse por escrito a través del correo indicado, acompañadas del fundamento legal del requerimiento y de la identificación del solicitante o de la autoridad requirente.'],

        ['titulo' => '5. Compromiso de tolerancia cero ante material con menores',
         'cuerpo' => APP_NAME . ' aplica una política de tolerancia cero contra cualquier material que involucre o sugiera la participación de menores de edad. Cualquier detección, tanto durante la moderación previa como derivada de un reporte posterior, conduce a:
• Retiro inmediato del contenido y suspensión de la cuenta.
• Conservación de la evidencia para entrega a las autoridades competentes.
• Denuncia ante las autoridades mexicanas correspondientes y, cuando proceda, a través de los canales internacionales de protección.

Adicionalmente, el Sitio utiliza tecnologías automatizadas de detección de imágenes coincidentes con bases de datos internacionales de material de abuso sexual infantil.'],

        ['titulo' => '6. Consentimiento de los modelos',
         'cuerpo' => 'Toda persona cuyo material se publica en el Sitio ha:
• Comprobado ser mayor de 18 años a través del proceso descrito.
• Aceptado expresamente los Términos y Condiciones del Sitio.
• Declarado bajo protesta de decir verdad ser titular de los derechos sobre el contenido o contar con el consentimiento de cada persona identificable en él.
• Otorgado al Sitio una licencia limitada para almacenar y mostrar el contenido conforme a lo descrito en los Términos.

El consentimiento puede ser revocado en cualquier momento solicitando la eliminación del contenido conforme al procedimiento descrito en el Aviso de Privacidad y en los Términos y Condiciones.'],

        ['titulo' => '7. Contenido no producido por el Sitio',
         'cuerpo' => APP_NAME . ' no produce contenido sexual explícito. Todo el material visual es generado y publicado por usuarios independientes. El Sitio actúa como intermediario tecnológico que aloja y distribuye el contenido aportado por sus usuarios, dentro del marco descrito en estos documentos legales.'],

        ['titulo' => '8. Contacto para esta declaración',
         'cuerpo' => 'Para cualquier asunto relacionado con verificación de edad, requerimientos sobre identidad de modelos, o auditorías de cumplimiento, contacta a:
legal@placerselecto.com

Las solicitudes deben presentarse por escrito y acompañar el fundamento legal correspondiente.'],
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
        <a href="<?= APP_URL ?>/dmca">Derechos de Autor</a>
    </div>
</div>
