<?php
/**
 * create.php — Formulario para crear un nuevo anuncio.
 */
$verificado = $currentUser['verificado'];
$estadoVer  = $currentUser['estado_verificacion'];
?>

<div class="container py-4" style="max-width:760px">

    <!-- Encabezado -->
    <div class="mb-4">
        <a href="<?= APP_URL ?>/mis-anuncios" class="btn btn-sm btn-secondary mb-3">
            <i class="bi bi-arrow-left me-1"></i>Mis anuncios
        </a>
        <h1 class="h4 fw-bold mb-1">
            <i class="bi bi-megaphone text-primary me-2"></i>Publicar anuncio
        </h1>
        <p class="text-muted mb-0" style="font-size:.875rem">
            Completa todos los campos. Tu anuncio quedará en revisión antes de ser publicado.
        </p>
    </div>

    <!-- Aviso si no está verificado -->
    <?php if (!$verificado && $estadoVer === 'pendiente'): ?>
    <div class="alert py-2 px-3 mb-4"
         style="background:rgba(255,193,7,.08);border:1px solid rgba(255,193,7,.2);border-radius:var(--radius-sm);font-size:.82rem;color:#ffd44d">
        <i class="bi bi-clock me-2"></i>
        <strong>Tu cuenta está pendiente de verificación.</strong>
        El anuncio se guardará y se publicará automáticamente cuando tu cuenta sea aprobada.
    </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="POST"
                  action="<?= APP_URL ?>/anuncio/crear"
                  enctype="multipart/form-data"
                  data-validate-form
                  novalidate>

                <?= $csrfField ?>

                <!-- Título -->
                <div class="mb-4">
                    <label for="titulo" class="form-label">
                        Título del anuncio <span class="text-danger">*</span>
                    </label>
                    <input type="text"
                           id="titulo"
                           name="titulo"
                           class="form-control"
                           placeholder="Ej: Acompañante independiente en CDMX"
                           maxlength="120"
                           required
                           data-validate="required|minLen:5|maxLen:120|noScript">
                    <div class="form-text text-muted" style="font-size:.75rem">
                        Entre 5 y 120 caracteres. Sin teléfonos ni emails en el título.
                    </div>
                </div>

                <!-- Categoría -->
                <div class="mb-4">
                    <label for="id_categoria" class="form-label">
                        Categoría <span class="text-danger">*</span>
                    </label>
                    <select id="id_categoria" name="id_categoria" class="form-select"
                            required data-validate="required|positiveInt">
                        <option value="">— Selecciona —</option>
                        <?php foreach ($categorias as $cat): ?>
                        <option value="<?= (int)$cat['id'] ?>">
                            <?= e($cat['nombre']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Estado y Municipio -->
                <div class="row g-3 mb-4">
                    <div class="col-12 col-sm-6">
                        <label for="id_estado" class="form-label">
                            Estado <span class="text-danger">*</span>
                        </label>
                        <select id="id_estado" name="id_estado" class="form-select"
                                required data-validate="required|positiveInt">
                            <option value="">— Selecciona estado —</option>
                            <?php foreach ($estados as $est): ?>
                            <option value="<?= (int)$est['id'] ?>">
                                <?= e($est['nombre']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label for="id_municipio" class="form-label">
                            Municipio / Alcaldía <span class="text-danger">*</span>
                        </label>
                        <select id="id_municipio" name="id_municipio" class="form-select"
                                required data-validate="required|positiveInt" disabled>
                            <option value="">— Primero selecciona estado —</option>
                        </select>
                        <div id="municipio-loading" class="form-text text-muted d-none" style="font-size:.75rem">
                            <span class="spinner-border spinner-border-sm me-1"></span>Cargando municipios…
                        </div>
                    </div>
                </div>

                <!-- Descripción -->
                <div class="mb-4">
                    <label for="descripcion" class="form-label">
                        Descripción <span class="text-danger">*</span>
                    </label>
                    <textarea id="descripcion"
                              name="descripcion"
                              class="form-control"
                              rows="6"
                              placeholder="Describe tu anuncio con detalle. Sé claro y honesto. No incluyas datos de contacto aquí."
                              required
                              data-validate="required|minLen:20|maxLen:3000"
                              data-maxlength="3000"
                              style="resize:vertical"></textarea>
                    <div class="form-text text-muted" style="font-size:.75rem">
                        Mínimo 20 caracteres. No incluyas links ni contenido explícito.
                    </div>
                </div>

                <!-- WhatsApp -->
                <div class="mb-4">
                    <label for="whatsapp" class="form-label">
                        <i class="bi bi-whatsapp text-success me-1"></i>WhatsApp de contacto
                        <span class="text-muted" style="font-size:.75rem">(opcional)</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text"
                              style="background:var(--color-bg-card2);border-color:var(--color-border);color:var(--color-text-muted)">
                            +
                        </span>
                        <input type="tel"
                               id="whatsapp"
                               name="whatsapp"
                               class="form-control"
                               placeholder="52 55 1234 5678"
                               maxlength="20"
                               data-validate="phone">
                    </div>
                    <div class="form-text text-muted" style="font-size:.75rem">
                        Solo dígitos y espacios. Incluye código de país. Ej: 52 55 1234 5678
                    </div>
                </div>

                <!-- Fotos (hasta 10) -->
                <?php require VIEWS_PATH . '/partials/foto-uploader.php'; ?>

                <!-- Aviso de contenido -->
                <div class="rounded-3 p-3 mb-4"
                     style="background:rgba(0,0,0,.03);border:1px solid var(--color-border);font-size:.78rem;color:var(--color-text-muted)">
                    <i class="bi bi-shield-check text-primary me-2"></i>
                    Al publicar confirmas que eres mayor de 18 años, que el contenido es legal
                    y acepta nuestros <a href="<?= APP_URL ?>/terminos" target="_blank">términos de uso</a>.
                    Prohibido contenido explícito, ilegal o de menores de edad.
                </div>

                <!-- Botones -->
                <div class="d-flex gap-3 flex-column flex-sm-row">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="bi bi-send me-2"></i>Publicar anuncio
                    </button>
                    <a href="<?= APP_URL ?>/mis-anuncios" class="btn btn-secondary">
                        Cancelar
                    </a>
                </div>

            </form>
        </div>
    </div>
</div>

<?php
$extraJs = '<script src="' . APP_URL . '/public/assets/js/municipios-cascada.js" defer></script>';
?>
