<?php
/**
 * perfiles/create.php — Formulario para crear un nuevo perfil.
 */
$verificado  = $currentUser['verificado'];
$estadoVer   = $currentUser['estado_verificacion'];
$old         = $oldInput ?? [];
$oldEstado   = (int)($old['id_estado']    ?? 0);
$oldMunicipio= (int)($old['id_municipio'] ?? 0);
$oldCategoria= (int)($old['id_categoria'] ?? 0);
?>
<!-- Quill — CSS y JS en body (el layout no soporta extraCss antes de la vista) -->
<link rel="stylesheet" href="<?= APP_URL ?>/public/assets/vendor/quill/quill.snow.css">
<script src="<?= APP_URL ?>/public/assets/vendor/quill/quill.min.js"></script>
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
            <i class="bi bi-person-plus text-primary me-2"></i>Crear perfil
        </h1>
        <p class="text-muted mb-0" style="font-size:.875rem">
            Completa tu perfil. Quedará en revisión antes de ser publicado.
        </p>
    </div>

    <?php if (!$verificado && $estadoVer === 'pendiente'): ?>
    <div class="alert py-2 px-3 mb-4"
         style="background:rgba(255,193,7,.08);border:1px solid rgba(255,193,7,.2);border-radius:var(--radius-sm);font-size:.82rem;color:#ffd44d">
        <i class="bi bi-clock me-2"></i>
        <strong>Tu cuenta está pendiente de verificación.</strong>
        El perfil se guardará y se publicará automáticamente cuando tu cuenta sea aprobada.
    </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="POST"
                  action="<?= APP_URL ?>/perfil/nuevo"
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
                           placeholder="Ej: Valentina"
                           maxlength="120"
                           required
                           data-validate="required|minLen:2|maxLen:120|noScript"
                           value="<?= e($old['nombre'] ?? '') ?>">
                    <div class="form-text text-muted" style="font-size:.75rem">
                        Tu nombre o nombre artístico. Sin teléfonos ni emails.
                    </div>
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
                           placeholder="Ej: 24"
                           min="18" max="99"
                           required
                           style="max-width:120px"
                           value="<?= e($old['edad'] ?? '') ?>">
                    <div class="form-check mt-2">
                        <input type="checkbox"
                               class="form-check-input"
                               id="edad_publica"
                               name="edad_publica"
                               value="1"
                               <?= !isset($old['edad_publica']) || $old['edad_publica'] ? 'checked' : '' ?>>
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
                            <?= $oldCategoria === (int)$cat['id'] ? 'selected' : '' ?>>
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
                                <?= $oldEstado === (int)$est['id'] ? 'selected' : '' ?>>
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
                    </div>
                </div>

                <!-- Descripción (editor rico) -->
                <div class="mb-4">
                    <label class="form-label">
                        Descripción <span class="text-danger">*</span>
                    </label>
                    <!-- Editor visible -->
                    <div id="descripcion-editor" class="quill-editor-wrap"></div>
                    <!-- Input oculto que se envía con el form -->
                    <input type="hidden" id="descripcion" name="descripcion" style="display:none!important">
                    <div class="form-text text-muted mt-1" style="font-size:.75rem">
                        Usa la barra para <strong>negritas</strong>, <em>cursiva</em>, centrado y más.
                        Para emojis pulsa <kbd>Win + .</kbd> o usa el botón 😊.
                    </div>
                </div>

                <?php
                // Pre-rellenar contacto desde la cuenta del usuario si no hay old input
                $p = [
                    'whatsapp'      => $usuarioData['telefono'] ?? '',
                    'telegram'      => '',
                    'email_contacto'=> $usuarioData['email']    ?? '',
                ];
                require VIEWS_PATH . '/partials/perfil-extra-fields.php';
                ?>

                <!-- Fotos (hasta 10) -->
                <?php if (!empty($old)): ?>
                <div class="alert py-2 px-3 mb-2"
                     style="background:rgba(255,193,7,.08);border:1px solid rgba(255,193,7,.2);border-radius:var(--radius-sm);font-size:.8rem;color:#ffd44d">
                    <i class="bi bi-image me-1"></i>
                    Las fotos no se conservan al corregir errores. Vuelve a seleccionarlas.
                </div>
                <?php endif; ?>
                <?php require VIEWS_PATH . '/partials/foto-uploader.php'; ?>

                <!-- Videos (hasta 3, opcionales) -->
                <div class="mb-4">
                    <label class="form-label d-block">
                        <i class="bi bi-play-btn-fill text-primary me-1"></i>Videos del perfil
                        <span class="text-muted fw-normal" style="font-size:.78rem">(opcional — hasta 3)</span>
                    </label>
                    <input type="file" name="videos[]" class="form-control"
                           accept="video/mp4,video/webm,video/quicktime" multiple>
                    <small class="text-muted" style="font-size:.75rem">
                        MP4, WebM o MOV · máx 50 MB por video · 3 videos por perfil
                    </small>
                </div>

                <!-- Aviso de contenido -->
                <div class="rounded-3 p-3 mb-4"
                     style="background:rgba(0,0,0,.03);border:1px solid var(--color-border);font-size:.78rem;color:var(--color-text-muted)">
                    <i class="bi bi-shield-check text-primary me-2"></i>
                    Al publicar confirmas que eres mayor de 18 años, que el contenido es legal
                    y acepta nuestros <a href="<?= APP_URL ?>/terminos" target="_blank">términos de uso</a>.
                </div>

                <!-- Botones -->
                <div class="d-flex gap-3 flex-column flex-sm-row">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="bi bi-send me-2"></i>Crear perfil
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
$extraJs = '<script>
(function () {
    const BASE_URL      = ' . json_encode(APP_URL) . ';
    const OLD_ESTADO    = ' . $oldEstado . ';
    const OLD_MUNICIPIO = ' . $oldMunicipio . ';
    const OLD_DESC      = ' . json_encode($old['descripcion'] ?? '') . ';

    const selEstado    = document.getElementById("id_estado");
    const selMunicipio = document.getElementById("id_municipio");

    function cargarMunicipios(idEstado, seleccionarId) {
        selMunicipio.innerHTML = "<option value=\"\">— Cargando… —</option>";
        selMunicipio.disabled  = true;

        if (!idEstado) {
            selMunicipio.innerHTML = "<option value=\"\">— Primero selecciona estado —</option>";
            return;
        }

        fetch(BASE_URL + "/api/municipios/" + idEstado)
            .then(r => r.json())
            .then(data => {
                selMunicipio.innerHTML = "<option value=\"\">— Selecciona municipio —</option>";
                if (data.success && data.municipios.length) {
                    data.municipios.forEach(m => {
                        const opt = document.createElement("option");
                        opt.value       = m.id;
                        opt.textContent = m.nombre;
                        if (seleccionarId && parseInt(m.id) === seleccionarId) {
                            opt.selected = true;
                        }
                        selMunicipio.appendChild(opt);
                    });
                    selMunicipio.disabled = false;
                } else {
                    selMunicipio.innerHTML = "<option value=\"\">Sin municipios</option>";
                }
            })
            .catch(() => {
                selMunicipio.innerHTML = "<option value=\"\">Error al cargar</option>";
            });
    }

    selEstado.addEventListener("change", function () {
        cargarMunicipios(parseInt(this.value, 10), null);
    });

    /* Restaurar municipio si venimos de un error de validación */
    if (OLD_ESTADO > 0) {
        cargarMunicipios(OLD_ESTADO, OLD_MUNICIPIO);
    }

    /* ── Quill editor ── */
    const AlignStyle = Quill.import("attributors/style/align");
    Quill.register(AlignStyle, true);

    const quill = new Quill("#descripcion-editor", {
        theme: "snow",
        placeholder: "Cuéntanos sobre ti, tu personalidad, servicios, disponibilidad…",
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

    /* Restaurar contenido de descripción si venimos de error */
    if (OLD_DESC && OLD_DESC.trim() !== "" && OLD_DESC !== "<p><br></p>") {
        quill.root.innerHTML = OLD_DESC;
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

    /* Añadir ícono al botón emoji */
    document.querySelector(".ql-emoji").innerHTML = "😊";

    /* Copiar HTML al input oculto antes de enviar */
    const form     = document.querySelector("form[data-validate-form]");
    const hiddenIn = document.getElementById("descripcion");
    form.addEventListener("submit", function() {
        hiddenIn.value = quill.root.innerHTML;
    });

    /* Si venimos de error de validación, hacer scroll/focus al primer campo vacío/inválido */
    if (OLD_ESTADO > 0 || ' . json_encode(!empty($old)) . ') {
        const flashAlert = document.querySelector(".alert-danger, .flash-error");
        if (flashAlert) {
            flashAlert.scrollIntoView({ behavior: "smooth", block: "center" });
        }
        /* Buscar primer campo vacío o inválido y hacer focus */
        const campos = ["nombre", "id_categoria", "id_estado"];
        for (const id of campos) {
            const el = document.getElementById(id);
            if (el && !el.value) {
                el.focus();
                break;
            }
        }
    }
})();
</script>';
?>
