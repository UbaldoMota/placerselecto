(function () {
    const cfgEl = document.getElementById('perfil-create-config');
    if (!cfgEl) return;

    const BASE_URL      = cfgEl.dataset.baseUrl || '';
    const OLD_ESTADO    = parseInt(cfgEl.dataset.oldEstado || '0', 10);
    const OLD_MUNICIPIO = parseInt(cfgEl.dataset.oldMunicipio || '0', 10);
    const OLD_DESC      = cfgEl.dataset.oldDesc || '';
    const HAS_OLD       = cfgEl.dataset.hasOld === '1';

    const selEstado    = document.getElementById("id_estado");
    const selMunicipio = document.getElementById("id_municipio");

    function cargarMunicipios(idEstado, seleccionarId) {
        selMunicipio.innerHTML = '<option value="">— Cargando… —</option>';
        selMunicipio.disabled  = true;

        if (!idEstado) {
            selMunicipio.innerHTML = '<option value="">— Primero selecciona estado —</option>';
            return;
        }

        fetch(BASE_URL + "/api/municipios/" + idEstado)
            .then(r => r.json())
            .then(data => {
                selMunicipio.innerHTML = '<option value="">— Selecciona municipio —</option>';
                if (data.success && data.municipios.length) {
                    data.municipios.forEach(m => {
                        const opt = document.createElement("option");
                        opt.value       = m.id;
                        opt.textContent = m.nombre;
                        if (seleccionarId && parseInt(m.id, 10) === seleccionarId) {
                            opt.selected = true;
                        }
                        selMunicipio.appendChild(opt);
                    });
                    selMunicipio.disabled = false;
                } else {
                    selMunicipio.innerHTML = '<option value="">Sin municipios</option>';
                }
            })
            .catch(() => {
                selMunicipio.innerHTML = '<option value="">Error al cargar</option>';
            });
    }

    if (selEstado) {
        selEstado.addEventListener("change", function () {
            cargarMunicipios(parseInt(this.value, 10), null);
        });
    }

    if (OLD_ESTADO > 0) {
        cargarMunicipios(OLD_ESTADO, OLD_MUNICIPIO);
    }

    /* Quill editor */
    if (typeof Quill !== "undefined") {
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
                        emoji: function () { toggleEmojiPanel(this.quill); }
                    }
                }
            }
        });

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
                    btn.addEventListener("click", () => {
                        const range = q.getSelection(true);
                        q.insertText(range ? range.index : q.getLength() - 1, em);
                        panel.style.display = "none";
                    });
                    panel.appendChild(btn);
                });
                const wrap = document.getElementById("descripcion-editor");
                wrap.appendChild(panel);
                document.addEventListener("click", function (e) {
                    if (!panel.contains(e.target) && !e.target.closest(".ql-emoji")) {
                        panel.style.display = "none";
                    }
                });
            }
            panel.style.display = panel.style.display === "grid" ? "none" : "grid";
        }

        const btnEmoji = document.querySelector(".ql-emoji");
        if (btnEmoji) btnEmoji.innerHTML = "😊";

        const form       = document.querySelector("form[data-validate-form]");
        const hiddenIn   = document.getElementById("descripcion");
        const editorWrap = document.getElementById("descripcion-editor");
        if (form && hiddenIn) {
            // Sincronizar en tiempo real (cualquier cambio en Quill se refleja en el input)
            function syncHidden() {
                const text = quill.getText().trim();
                hiddenIn.value = text === '' ? '' : quill.root.innerHTML;
                hideError();
            }
            function showError(msg) {
                if (editorWrap) editorWrap.style.border = '2px solid var(--color-danger, #dc3545)';
                let err = document.getElementById('descripcion-error');
                if (!err) {
                    err = document.createElement('div');
                    err.id = 'descripcion-error';
                    err.style.cssText = 'color:var(--color-danger,#dc3545);font-size:.78rem;margin-top:.35rem';
                    editorWrap?.parentElement?.insertBefore(err, editorWrap.nextSibling);
                }
                err.textContent = msg;
            }
            function hideError() {
                if (editorWrap) editorWrap.style.border = '';
                const err = document.getElementById('descripcion-error');
                if (err) err.remove();
            }
            quill.on('text-change', syncHidden);
            // Validación propia antes del submit
            form.addEventListener("submit", function (ev) {
                syncHidden();
                const text = quill.getText().trim();
                if (text.length < 10) {
                    ev.preventDefault();
                    ev.stopImmediatePropagation();
                    showError(text.length === 0
                        ? 'La descripción es obligatoria.'
                        : 'La descripción debe tener al menos 10 caracteres.');
                    editorWrap?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }, { capture: true });
            syncHidden();
        }
    }

    if (OLD_ESTADO > 0 || HAS_OLD) {
        const flashAlert = document.querySelector(".alert-danger, .flash-error");
        if (flashAlert) {
            flashAlert.scrollIntoView({ behavior: "smooth", block: "center" });
        }
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
