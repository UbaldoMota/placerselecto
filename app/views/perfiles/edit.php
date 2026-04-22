<?php
/**
 * perfiles/edit.php — Formulario para editar un perfil existente.
 */
$selEstadoId    = (int)($perfil['id_estado']    ?? 0);
$selMunicipioId = (int)($perfil['id_municipio'] ?? 0);
?>
<!-- Quill — CSS y JS en body (el layout no soporta extraCss antes de la vista) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css">
<script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
<style>
.ql-toolbar.ql-snow{background:#F5F5F5!important;border:1px solid #E5E5E5!important;border-bottom:none!important;padding:.45rem .6rem!important;border-radius:8px 8px 0 0!important}
.ql-toolbar.ql-snow button{background:transparent!important;border:none!important}
.ql-toolbar.ql-snow button:hover{background:rgba(255,45,117,.08)!important;border-radius:4px!important}
.ql-container.ql-snow{background:#FFFFFF!important;border:1px solid #E5E5E5!important;font-family:inherit!important;font-size:.93rem!important;border-radius:0 0 8px 8px!important}
.ql-editor{color:#1A1A1A!important;min-height:160px!important;line-height:1.8!important;padding:.85rem 1rem!important}
.ql-editor.ql-blank::before{color:#9AA0A6!important;font-style:normal!important;left:1rem!important;right:1rem!important}
.ql-snow .ql-stroke{stroke:#666666!important}
.ql-snow .ql-fill{fill:#666666!important}
.ql-snow .ql-picker-label{color:#666666!important;background:transparent!important;border-color:#E5E5E5!important}
.ql-snow .ql-picker-options{background:#FFFFFF!important;border-color:#E5E5E5!important;box-shadow:0 4px 12px rgba(0,0,0,.08)}
.ql-snow .ql-picker-item{color:#1A1A1A!important}
.ql-snow button:hover .ql-stroke,.ql-snow button.ql-active .ql-stroke{stroke:#FF2D75!important}
.ql-snow button:hover .ql-fill,.ql-snow button.ql-active .ql-fill{fill:#FF2D75!important}
.ql-snow .ql-picker-label:hover .ql-stroke{stroke:#FF2D75!important}
.ql-toolbar.ql-snow .ql-formats{margin-right:.5rem}
.ql-emoji{font-size:1.05rem!important;padding:0 3px!important;line-height:1!important;height:24px!important;width:auto!important}
</style>

<div class="container py-4" style="max-width:760px">

    <div class="d-flex flex-wrap gap-2 justify-content-end mb-3">
        <a href="<?= APP_URL ?>/mis-perfiles" class="btn btn-sm btn-secondary">
            <i class="bi bi-person-lines-fill me-1"></i>Mis perfiles
        </a>
        <a href="<?= APP_URL ?>/dashboard" class="btn btn-sm btn-secondary">
            <i class="bi bi-house me-1"></i>Dashboard
        </a>
    </div>

    <div class="mb-4">
        <h1 class="h4 fw-bold mb-1">
            <i class="bi bi-pencil-square text-primary me-2"></i>Editar perfil
        </h1>
        <p class="text-muted mb-0" style="font-size:.875rem">
            Al guardar, el perfil volverá a estado <strong>En revisión</strong> y será re-publicado tras aprobación.
        </p>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST"
                  action="<?= APP_URL ?>/perfil/<?= (int)$perfil['id'] ?>/editar"
                  enctype="multipart/form-data"
                  data-validate-form
                  data-upload-form
                  novalidate>

                <?= $csrfField ?>

                <!-- Nombre -->
                <div class="mb-4">
                    <label for="nombre" class="form-label">
                        Nombre del perfil <span class="text-danger">*</span>
                    </label>
                    <input type="text"
                           id="nombre"
                           name="nombre"
                           class="form-control"
                           value="<?= e($perfil['nombre']) ?>"
                           maxlength="120"
                           required
                           data-validate="required|minLen:2|maxLen:120|noScript">
                </div>

                <!-- Edad -->
                <div class="mb-4">
                    <label for="edad" class="form-label">
                        Edad <span class="text-danger">*</span>
                    </label>
                    <input type="number"
                           id="edad"
                           name="edad"
                           class="form-control"
                           value="<?= !empty($perfil['edad']) ? (int)$perfil['edad'] : '' ?>"
                           min="18" max="99"
                           required
                           style="max-width:120px">
                    <div class="form-check mt-2">
                        <input type="checkbox"
                               class="form-check-input"
                               id="edad_publica"
                               name="edad_publica"
                               value="1"
                               <?= (int)($perfil['edad_publica'] ?? 1) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="edad_publica" style="font-size:.85rem">
                            Mostrar edad públicamente en el perfil
                        </label>
                        <div class="text-muted" style="font-size:.75rem">
                            Si lo desactivas, la edad queda registrada pero no se muestra en tu perfil público.
                        </div>
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
                        <option value="<?= (int)$cat['id'] ?>"
                                <?= (int)$cat['id'] === (int)$perfil['id_categoria'] ? 'selected' : '' ?>>
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

                <!-- Descripción (editor rico) -->
                <div class="mb-4">
                    <label class="form-label">
                        Descripción <span class="text-danger">*</span>
                    </label>
                    <div id="descripcion-editor" class="quill-editor-wrap"></div>
                    <input type="hidden" id="descripcion" name="descripcion" style="display:none!important"
                           value="<?= e($perfil['descripcion']) ?>">
                    <div class="form-text text-muted mt-1" style="font-size:.75rem">
                        Usa la barra para <strong>negritas</strong>, <em>cursiva</em>, centrado y más.
                        Para emojis pulsa <kbd>Win + .</kbd> o usa el botón 😊.
                    </div>
                </div>

                <?php $p = $perfil; $old = []; require VIEWS_PATH . '/partials/perfil-extra-fields.php'; ?>

                <!-- Galería de fotos -->
                <?php require VIEWS_PATH . '/partials/foto-uploader.php'; ?>

                <!-- Videos del perfil -->
                <div class="mb-4">
                    <label class="form-label d-block">
                        <i class="bi bi-play-btn-fill text-primary me-1"></i>Videos del perfil
                        <span class="text-muted fw-normal" style="font-size:.78rem">
                            (<?= count($videosExistentes ?? []) ?> / <?= (int)$maxVideos ?>)
                        </span>
                    </label>

                    <?php if (!empty($videosExistentes)): ?>
                    <div class="row g-2 mb-2">
                        <?php foreach ($videosExistentes as $v): ?>
                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="p-2 rounded border" style="background:var(--color-bg-alt)">
                                <video src="<?= APP_URL . '/video/' . e($v['token']) ?>"
                                       style="width:100%;border-radius:6px;background:#000"
                                       controls preload="metadata" playsinline></video>
                                <label class="form-check mt-2 d-flex gap-1 align-items-center" style="font-size:.75rem">
                                    <input type="checkbox" class="form-check-input" name="eliminar_video[]" value="<?= (int)$v['id'] ?>">
                                    <span>Eliminar este video</span>
                                </label>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <?php $disp = max(0, (int)$maxVideos - count($videosExistentes ?? [])); ?>
                    <?php if ($disp > 0): ?>
                    <input type="file" name="videos[]" class="form-control"
                           accept="video/mp4,video/webm,video/quicktime" multiple>
                    <small class="text-muted" style="font-size:.75rem">
                        Puedes agregar <?= $disp ?> video(s) más · MP4/WebM/MOV · máx 50 MB
                    </small>
                    <?php else: ?>
                    <small class="text-muted" style="font-size:.75rem">
                        Alcanzaste el límite. Elimina un video existente para subir otro.
                    </small>
                    <?php endif; ?>
                </div>

                <!-- Info estado -->
                <div class="rounded-3 p-3 mb-4 d-flex align-items-start gap-2"
                     style="background:rgba(0,0,0,.03);border:1px solid var(--color-border);font-size:.82rem">
                    <i class="bi bi-info-circle text-primary mt-1"></i>
                    <span style="color:var(--color-text-muted)">
                        Estado actual: <strong>
                        <?php
                        $labels = ['pendiente'=>'En revisión','publicado'=>'Publicado','rechazado'=>'Rechazado'];
                        echo $labels[$perfil['estado']] ?? $perfil['estado'];
                        ?>
                        </strong>.
                        Al guardar volverá a <strong>En revisión</strong>.
                    </span>
                </div>

                <!-- Botones -->
                <div class="d-flex gap-3 flex-column flex-sm-row">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="bi bi-save me-2"></i>Guardar cambios
                    </button>
                    <a href="<?= APP_URL ?>/mis-perfiles" class="btn btn-secondary">
                        Cancelar
                    </a>
                </div>

            </form>
        </div>
    </div>
</div>

<?php
$descripcionJson = json_encode($perfil['descripcion'] ?? '');
$extraJs  = '<script>
(function () {
    const BASE_URL     = ' . json_encode(APP_URL) . ';
    const selEstado    = document.getElementById("id_estado");
    const selMunicipio = document.getElementById("id_municipio");
    const loading      = document.getElementById("municipio-loading");

    selEstado.addEventListener("change", function () {
        const idEstado = parseInt(this.value, 10);
        selMunicipio.innerHTML = "<option value=\"\">— Cargando… —</option>";
        selMunicipio.disabled  = true;
        if (!idEstado) {
            selMunicipio.innerHTML = "<option value=\"\">— Primero selecciona estado —</option>";
            return;
        }
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
                        selMunicipio.appendChild(opt);
                    });
                    selMunicipio.disabled = false;
                }
            })
            .catch(() => {
                loading.classList.add("d-none");
                selMunicipio.innerHTML = "<option value=\"\">Error al cargar</option>";
            });
    });

    /* ── Quill editor ── */
    const AlignStyle = Quill.import("attributors/style/align");
    Quill.register(AlignStyle, true);

    const quill = new Quill("#descripcion-editor", {
        theme: "snow",
        modules: {
            toolbar: {
                container: [
                    ["bold", "italic", "underline", "strike"],
                    [{ align: "" }, { align: "center" }, { align: "right" }, { align: "justify" }],
                    [{ list: "ordered" }, { list: "bullet" }],
                    ["clean"],
                    ["emoji"]
                ],
                handlers: {
                    emoji: function() { toggleEmojiPanel(this.quill); }
                }
            }
        }
    });

    /* Cargar contenido existente */
    const existingHtml = ' . $descripcionJson . ';
    if (existingHtml) {
        const hasHtml = /<[a-z][\s\S]*>/i.test(existingHtml);
        if (hasHtml) {
            quill.root.innerHTML = existingHtml;
        } else {
            quill.setText(existingHtml);
        }
    }

    /* Emoji panel */
    const EMOJIS = ["😊","😍","🔥","💋","😘","😈","💦","❤️","💕","✨","🌹","💎","👄","🍑","💅","😏","🥰","😻","💓","🌸","🦋","💄","👑","🎀","🌺","😉","🤩","💃","🍒","🫦","🤫","😜","🫶","💞","🥵","😇","🙈","💫","🌙","⭐","🎭","🖤","💜","🤍","🤎","💛","💚","💙","❤️‍🔥"];

    function toggleEmojiPanel(q) {
        let panel = document.getElementById("emoji-panel");
        if (!panel) {
            panel = document.createElement("div");
            panel.id = "emoji-panel";
            panel.className = "quill-emoji-panel";
            EMOJIS.forEach(em => {
                const btn = document.createElement("button");
                btn.type = "button";
                btn.textContent = em;
                btn.onclick = () => {
                    const range = q.getSelection(true);
                    q.insertText(range ? range.index : q.getLength() - 1, em);
                    panel.style.display = "none";
                };
                panel.appendChild(btn);
            });
            const wrap = document.getElementById("descripcion-editor");
            wrap.appendChild(panel);
            document.addEventListener("click", function(e) {
                if (!panel.contains(e.target) && !e.target.closest(".ql-emoji")) {
                    panel.style.display = "none";
                }
            });
        }
        panel.style.display = panel.style.display === "grid" ? "none" : "grid";
    }

    document.querySelector(".ql-emoji").innerHTML = "😊";

    /* Copiar HTML al input oculto antes de enviar */
    const form     = document.querySelector("form[data-validate-form]");
    const hiddenIn = document.getElementById("descripcion");
    form.addEventListener("submit", function() {
        hiddenIn.value = quill.root.innerHTML;
    });
})();
</script>'; ?>
