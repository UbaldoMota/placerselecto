(function () {
    const input    = document.getElementById('inputFotos');
    const dropZone = document.getElementById('dropZone');
    const previews = document.getElementById('previewsContainer');
    const btnSubir = document.getElementById('btnSubir');
    if (!input || !dropZone || !previews || !btnSubir) return;

    let archivos = new DataTransfer();

    function actualizarBoton() {
        btnSubir.disabled = archivos.files.length === 0;
        btnSubir.textContent = archivos.files.length > 0
            ? 'Enviar ' + archivos.files.length + ' foto(s) de verificación'
            : 'Enviar fotos de verificación';
        if (archivos.files.length > 0) {
            btnSubir.innerHTML = '<i class="bi bi-upload me-2"></i>' + btnSubir.textContent;
        }
    }

    function mostrarPreviews(files) {
        previews.innerHTML = '';
        archivos = new DataTransfer();
        for (const file of files) {
            archivos.items.add(file);
            const reader = new FileReader();
            reader.onload = e => {
                const div = document.createElement('div');
                div.style.cssText = 'position:relative';
                div.innerHTML = '<img src="' + e.target.result + '" style="width:100%;aspect-ratio:1;object-fit:cover;border-radius:6px;border:1px solid var(--color-border)">';
                previews.appendChild(div);
            };
            reader.readAsDataURL(file);
        }
        input.files = archivos.files;
        actualizarBoton();
    }

    input.addEventListener('change', () => mostrarPreviews(input.files));
    dropZone.addEventListener('dragover', e => {
        e.preventDefault();
        dropZone.style.borderColor = 'var(--color-primary)';
    });
    dropZone.addEventListener('dragleave', () => {
        dropZone.style.borderColor = 'var(--color-border)';
    });
    dropZone.addEventListener('drop', e => {
        e.preventDefault();
        dropZone.style.borderColor = 'var(--color-border)';
        mostrarPreviews(e.dataTransfer.files);
    });

    // ─── Barra de progreso al enviar ───
    const form = btnSubir ? btnSubir.closest('form') : document.querySelector('form[enctype="multipart/form-data"]');
    if (form) {
        form.addEventListener('submit', function(ev) {
            if (!input.files || input.files.length === 0) return;
            ev.preventDefault();
            enviarConProgreso(form);
        });
    }

    function enviarConProgreso(form) {
        const wrap = crearBarraProgreso(form);
        btnSubir.disabled = true;
        btnSubir.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enviando…';

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
            btnSubir.disabled = false;
            btnSubir.innerHTML = '<i class="bi bi-upload me-2"></i>Enviar fotos de verificación';
            alert('Error al subir las fotos. Inténtalo de nuevo.');
        });
        xhr.send(fd);
    }

    function crearBarraProgreso(form) {
        let wrap = document.getElementById('upload-progress');
        if (wrap) wrap.remove();
        wrap = document.createElement('div');
        wrap.id = 'upload-progress';
        wrap.style.cssText = 'margin-top:1rem';
        wrap.innerHTML = '<div style="display:flex;justify-content:space-between;font-size:.78rem;margin-bottom:.3rem"><span>Subiendo fotos…</span><span id="up-lbl">0%</span></div>'
            + '<div style="height:8px;background:var(--color-bg-alt,#f1f3f5);border-radius:6px;overflow:hidden"><div id="up-bar" style="height:100%;width:0%;background:var(--color-primary);transition:width .2s"></div></div>';
        form.appendChild(wrap);
        return { wrap, bar: wrap.querySelector('#up-bar'), lbl: wrap.querySelector('#up-lbl') };
    }
})();
