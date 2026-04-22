(function () {
    const cfgEl = document.getElementById('perfil-edit-config');
    if (!cfgEl) return;

    const urlMeta = document.querySelector('meta[name="app-url"]');
    const BASE_URL = urlMeta ? urlMeta.getAttribute('content') : '';
    const existingHtml = cfgEl.dataset.descripcion || '';

    const selEstado    = document.getElementById('id_estado');
    const selMunicipio = document.getElementById('id_municipio');
    const loading      = document.getElementById('municipio-loading');

    if (selEstado && selMunicipio) {
        selEstado.addEventListener('change', function () {
            const idEstado = parseInt(this.value, 10);
            selMunicipio.innerHTML = '<option value="">— Cargando… —</option>';
            selMunicipio.disabled  = true;
            if (!idEstado) {
                selMunicipio.innerHTML = '<option value="">— Primero selecciona estado —</option>';
                return;
            }
            if (loading) loading.classList.remove('d-none');
            fetch(BASE_URL + '/api/municipios/' + idEstado)
                .then(r => r.json())
                .then(data => {
                    if (loading) loading.classList.add('d-none');
                    selMunicipio.innerHTML = '<option value="">— Selecciona municipio —</option>';
                    if (data.success && data.municipios.length) {
                        data.municipios.forEach(m => {
                            const opt = document.createElement('option');
                            opt.value       = m.id;
                            opt.textContent = m.nombre;
                            selMunicipio.appendChild(opt);
                        });
                        selMunicipio.disabled = false;
                    }
                })
                .catch(() => {
                    if (loading) loading.classList.add('d-none');
                    selMunicipio.innerHTML = '<option value="">Error al cargar</option>';
                });
        });
    }

    /* Quill */
    if (typeof Quill === 'undefined') return;
    const AlignStyle = Quill.import('attributors/style/align');
    Quill.register(AlignStyle, true);

    const quill = new Quill('#descripcion-editor', {
        theme: 'snow',
        modules: {
            toolbar: {
                container: [
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ align: '' }, { align: 'center' }, { align: 'right' }, { align: 'justify' }],
                    [{ list: 'ordered' }, { list: 'bullet' }],
                    ['clean'],
                    ['emoji']
                ],
                handlers: {
                    emoji: function() { toggleEmojiPanel(this.quill); }
                }
            }
        }
    });

    if (existingHtml) {
        const hasHtml = /<[a-z][\s\S]*>/i.test(existingHtml);
        if (hasHtml) quill.root.innerHTML = existingHtml;
        else         quill.setText(existingHtml);
    }

    const EMOJIS = ["😊","😍","🔥","💋","😘","😈","💦","❤️","💕","✨","🌹","💎","👄","🍑","💅","😏","🥰","😻","💓","🌸","🦋","💄","👑","🎀","🌺","😉","🤩","💃","🍒","🫦","🤫","😜","🫶","💞","🥵","😇","🙈","💫","🌙","⭐","🎭","🖤","💜","🤍","🤎","💛","💚","💙","❤️‍🔥"];

    function toggleEmojiPanel(q) {
        let panel = document.getElementById('emoji-panel');
        if (!panel) {
            panel = document.createElement('div');
            panel.id = 'emoji-panel';
            panel.className = 'quill-emoji-panel';
            EMOJIS.forEach(em => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.textContent = em;
                btn.addEventListener('click', () => {
                    const range = q.getSelection(true);
                    q.insertText(range ? range.index : q.getLength() - 1, em);
                    panel.style.display = 'none';
                });
                panel.appendChild(btn);
            });
            document.getElementById('descripcion-editor').appendChild(panel);
            document.addEventListener('click', function(e) {
                if (!panel.contains(e.target) && !e.target.closest('.ql-emoji')) {
                    panel.style.display = 'none';
                }
            });
        }
        panel.style.display = panel.style.display === 'grid' ? 'none' : 'grid';
    }

    const btnEmoji = document.querySelector('.ql-emoji');
    if (btnEmoji) btnEmoji.innerHTML = '😊';

    const form       = document.querySelector('form[data-validate-form]');
    const hiddenIn   = document.getElementById('descripcion');
    const editorWrap = document.getElementById('descripcion-editor');
    if (form && hiddenIn) {
        function syncHidden() {
            const text = quill.getText().trim();
            hiddenIn.value = text === '' ? '' : quill.root.innerHTML;
            hideError();
        }
        function showError(msg) {
            if (editorWrap) editorWrap.style.border = '2px solid var(--color-danger,#dc3545)';
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
        form.addEventListener('submit', function (ev) {
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
})();
