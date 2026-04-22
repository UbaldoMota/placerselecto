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
})();
