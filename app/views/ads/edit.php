<?php
/**
 * edit.php — Formulario para editar un anuncio existente.
 */
?>

<div class="container py-4" style="max-width:760px">

    <!-- Encabezado -->
    <div class="mb-4">
        <a href="<?= APP_URL ?>/mis-anuncios" class="btn btn-sm btn-secondary mb-3">
            <i class="bi bi-arrow-left me-1"></i>Mis anuncios
        </a>
        <h1 class="h4 fw-bold mb-1">
            <i class="bi bi-pencil-square text-primary me-2"></i>Editar anuncio
        </h1>
        <p class="text-muted mb-0" style="font-size:.875rem">
            Al guardar, el anuncio volverá a estado <strong>En revisión</strong> y será re-publicado tras aprobación.
        </p>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST"
                  action="<?= APP_URL ?>/anuncio/<?= (int)$anuncio['id'] ?>/editar"
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
                           value="<?= e($anuncio['titulo']) ?>"
                           maxlength="120"
                           required
                           data-validate="required|minLen:5|maxLen:120|noScript">
                </div>

                <!-- Categoría -->
                <div class="mb-4">
                    <label for="id_categoria" class="form-label">
                        Categoría <span class="text-danger">*</span>
                    </label>
                    <select id="id_categoria" name="id_categoria" class="form-select"
                            required data-validate="required">
                        <option value="">— Selecciona —</option>
                        <?php foreach ($categorias as $cat): ?>
                        <option value="<?= (int)$cat['id'] ?>"
                                <?= (int)$cat['id'] === (int)$anuncio['id_categoria'] ? 'selected' : '' ?>>
                            <?= e($cat['nombre']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Estado y Municipio -->
                <?php
                $selEstadoId    = (int)($anuncio['id_estado']    ?? 0);
                $selMunicipioId = (int)($anuncio['id_municipio'] ?? 0);
                ?>
                <div class="row g-3 mb-4">
                    <div class="col-12 col-sm-6">
                        <label for="id_estado" class="form-label">
                            Estado <span class="text-danger">*</span>
                        </label>
                        <select id="id_estado" name="id_estado" class="form-select"
                                required data-validate="required|positiveInt">
                            <option value="">— Selecciona estado —</option>
                            <?php foreach ($estados as $est): ?>
                            <option value="<?= (int)$est['id'] ?>"
                                    <?= (int)$est['id'] === $selEstadoId ? 'selected' : '' ?>>
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
                                required data-validate="required|positiveInt"
                                <?= $selEstadoId ? '' : 'disabled' ?>>
                            <?php if ($selEstadoId && !empty($municipios)): ?>
                                <option value="">— Selecciona municipio —</option>
                                <?php foreach ($municipios as $m): ?>
                                <option value="<?= (int)$m['id'] ?>"
                                        <?= (int)$m['id'] === $selMunicipioId ? 'selected' : '' ?>>
                                    <?= e($m['nombre']) ?>
                                </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="">— Primero selecciona estado —</option>
                            <?php endif; ?>
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
                              required
                              data-validate="required|minLen:20|maxLen:3000"
                              data-maxlength="3000"
                              style="resize:vertical"><?= e($anuncio['descripcion']) ?></textarea>
                </div>

                <!-- WhatsApp -->
                <div class="mb-4">
                    <label for="whatsapp" class="form-label">
                        <i class="bi bi-whatsapp text-success me-1"></i>WhatsApp de contacto
                        <span class="text-muted" style="font-size:.75rem">(opcional)</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text"
                              style="background:var(--color-bg-card2);border-color:var(--color-border);color:var(--color-text-muted)">+</span>
                        <input type="tel"
                               id="whatsapp"
                               name="whatsapp"
                               class="form-control"
                               value="<?= e($anuncio['whatsapp'] ?? '') ?>"
                               maxlength="20"
                               data-validate="phone">
                    </div>
                </div>

                <!-- Galería de fotos -->
                <?php require VIEWS_PATH . '/partials/foto-uploader.php'; ?>

                <!-- Info de estado actual -->
                <div class="rounded-3 p-3 mb-4 d-flex align-items-start gap-2"
                     style="background:rgba(0,0,0,.03);border:1px solid var(--color-border);font-size:.82rem">
                    <i class="bi bi-info-circle text-primary mt-1"></i>
                    <div>
                        <strong class="text-white">Estado actual:</strong>
                        <?php
                        $labels = ['pendiente'=>'En revisión','publicado'=>'Publicado','rechazado'=>'Rechazado','expirado'=>'Expirado'];
                        echo e($labels[$anuncio['estado']] ?? $anuncio['estado']);
                        ?>.
                        Al guardar cambios el anuncio volverá a <strong>En revisión</strong>
                        y será publicado tras nueva aprobación.
                    </div>
                </div>

                <!-- Botones -->
                <div class="d-flex gap-3 flex-column flex-sm-row">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="bi bi-check-lg me-2"></i>Guardar cambios
                    </button>
                    <a href="<?= APP_URL ?>/mis-anuncios" class="btn btn-secondary">
                        Cancelar
                    </a>
                    <!-- Eliminar desde formulario de edición -->
                    <form method="POST"
                          action="<?= APP_URL ?>/anuncio/<?= (int)$anuncio['id'] ?>/eliminar"
                          class="d-inline">
                        <?= $csrfField ?>
                        <button type="submit"
                                class="btn btn-danger"
                                data-confirm="¿Eliminar este anuncio permanentemente?">
                            <i class="bi bi-trash me-1"></i>Eliminar
                        </button>
                    </form>
                </div>

            </form>
        </div>
    </div>
</div>

<?php $extraJs = '<script>
(function () {
    const BASE_URL       = ' . json_encode(APP_URL) . ';
    const selEstado      = document.getElementById("id_estado");
    const selMunicipio   = document.getElementById("id_municipio");
    const loading        = document.getElementById("municipio-loading");
    const preselectedMun = ' . $selMunicipioId . ';

    function cargarMunicipios(idEstado, selectValue) {
        selMunicipio.innerHTML = "<option value=\"\">— Cargando… —</option>";
        selMunicipio.disabled  = true;
        loading.classList.remove("d-none");

        fetch(BASE_URL + "/api/municipios/" + idEstado)
            .then(r => r.json())
            .then(data => {
                loading.classList.add("d-none");
                selMunicipio.innerHTML = "<option value=\"\">— Selecciona municipio —</option>";
                if (data.success && data.municipios.length) {
                    data.municipios.forEach(m => {
                        const opt = document.createElement("option");
                        opt.value       = m.id;
                        opt.textContent = m.nombre;
                        if (m.id == selectValue) opt.selected = true;
                        selMunicipio.appendChild(opt);
                    });
                    selMunicipio.disabled = false;
                } else {
                    selMunicipio.innerHTML = "<option value=\"\">Sin municipios</option>";
                }
            })
            .catch(() => {
                loading.classList.add("d-none");
                selMunicipio.innerHTML = "<option value=\"\">Error al cargar</option>";
            });
    }

    selEstado.addEventListener("change", function () {
        const idEstado = parseInt(this.value, 10);
        if (!idEstado) {
            selMunicipio.innerHTML = "<option value=\"\">— Primero selecciona estado —</option>";
            selMunicipio.disabled  = true;
            return;
        }
        cargarMunicipios(idEstado, 0);
    });
})();
</script>';
?>
