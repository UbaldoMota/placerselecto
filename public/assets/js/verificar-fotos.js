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
})();
