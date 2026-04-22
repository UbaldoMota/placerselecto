(function () {
    const input   = document.getElementById('inputDocumento');
    const drop    = document.getElementById('dropZone');
    const preview = document.getElementById('previewImg');
    const icon    = document.getElementById('dropIcon');
    const text    = document.getElementById('dropText');
    const btn     = document.getElementById('btnEnviar');
    if (!input || !drop) return;

    function cargarArchivo(file) {
        if (!file || !file.type.startsWith('image/')) return;
        if (file.size > 5 * 1024 * 1024) {
            alert('El archivo supera 5 MB. Elige una imagen más pequeña.');
            return;
        }
        const reader = new FileReader();
        reader.onload = e => {
            if (preview) { preview.src = e.target.result; preview.style.display = 'block'; }
            if (icon) icon.style.display = 'none';
            if (text) text.textContent   = file.name;
            if (btn)  btn.disabled       = false;
            drop.style.borderColor = 'var(--color-primary)';
            drop.style.background  = 'rgba(255,45,117,.04)';
        };
        reader.readAsDataURL(file);
    }

    input.addEventListener('change', () => cargarArchivo(input.files[0]));

    drop.addEventListener('dragover', e => {
        e.preventDefault();
        drop.style.borderColor = 'var(--color-primary)';
    });
    drop.addEventListener('dragleave', () => {
        drop.style.borderColor = 'var(--color-border)';
    });
    drop.addEventListener('drop', e => {
        e.preventDefault();
        drop.style.borderColor = 'var(--color-border)';
        const file = e.dataTransfer.files[0];
        if (file) {
            const dt = new DataTransfer();
            dt.items.add(file);
            input.files = dt.files;
            cargarArchivo(file);
        }
    });

    // ─── Barra de progreso al enviar ───
    const form = btn ? btn.closest('form') : document.getElementById('formDocumento');
    if (form) {
        form.addEventListener('submit', function(ev) {
            if (!input.files || !input.files[0]) return;
            ev.preventDefault();
            enviarConProgreso(form);
        });
    }

    function enviarConProgreso(form) {
        const wrap = crearBarraProgreso(form);
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enviando…';
        }

        const fd  = new FormData(form);
        const xhr = new XMLHttpRequest();
        xhr.open(form.method || 'POST', form.action);
        xhr.upload.addEventListener('progress', function(e) {
            if (e.lengthComputable) {
                const pct = Math.round((e.loaded / e.total) * 100);
                wrap.bar.style.width = pct + '%';
                wrap.lbl.textContent = pct + '%';
            }
        });
        xhr.addEventListener('load', function() {
            wrap.bar.style.width = '100%';
            wrap.lbl.textContent = '100%';
            window.location.href = xhr.responseURL || form.action;
        });
        xhr.addEventListener('error', function() {
            wrap.wrap.remove();
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-send me-2"></i>Enviar para revisión';
            }
            alert('Error al subir el documento. Inténtalo de nuevo.');
        });
        xhr.send(fd);
    }

    function crearBarraProgreso(form) {
        let wrap = document.getElementById('upload-progress');
        if (wrap) wrap.remove();
        wrap = document.createElement('div');
        wrap.id = 'upload-progress';
        wrap.style.cssText = 'margin-top:1rem';
        wrap.innerHTML = '<div style="display:flex;justify-content:space-between;font-size:.78rem;margin-bottom:.3rem"><span>Subiendo documento…</span><span id="up-lbl">0%</span></div>'
            + '<div style="height:8px;background:var(--color-bg-alt,#f1f3f5);border-radius:6px;overflow:hidden"><div id="up-bar" style="height:100%;width:0%;background:var(--color-primary);transition:width .2s"></div></div>';
        form.appendChild(wrap);
        return { wrap, bar: wrap.querySelector('#up-bar'), lbl: wrap.querySelector('#up-lbl') };
    }
})();
