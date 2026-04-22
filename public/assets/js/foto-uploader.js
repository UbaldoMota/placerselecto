(function () {
    const cfgEl = document.getElementById('foto-uploader-config');
    if (!cfgEl) return;

    const MAX_TOTAL = parseInt(cfgEl.dataset.maxFotos || '10', 10);
    let dbCount     = parseInt(cfgEl.dataset.yaSubidas || '0', 10);
    let dbHasPrincipal = cfgEl.dataset.hasPrincipal === '1';

    const input          = document.getElementById('fotos-input');
    const newGrid        = document.getElementById('fotos-new-grid');
    const counter        = document.getElementById('foto-counter');
    const dropzone       = document.getElementById('foto-dropzone');
    const principalInput = document.getElementById('foto-principal-id');

    let pendingFiles = [];

    function updateCounter() {
        const total = dbCount + pendingFiles.length;
        if (counter) counter.textContent = total + ' / ' + MAX_TOTAL;
        if (dropzone) {
            const restante = MAX_TOTAL - total;
            if (restante <= 0) {
                dropzone.style.display = 'none';
            } else {
                dropzone.style.display = '';
                const txt = dropzone.querySelector('div:last-of-type');
                if (txt) txt.textContent =
                    'JPG, PNG, WEBP · Máx. 5 MB por foto · Hasta ' + restante +
                    ' foto' + (restante !== 1 ? 's' : '') + ' más';
            }
        }
    }

    function renderNewPreviews() {
        if (!newGrid) return;
        newGrid.innerHTML = '';

        pendingFiles.forEach((item, idx) => {
            const isPrincipal = !dbHasPrincipal && idx === 0;
            const div = document.createElement('div');
            div.className = 'foto-thumb' + (isPrincipal ? ' foto-thumb--principal' : '');

            const img = document.createElement('img');
            img.src = item.url;
            img.alt = 'Foto ' + (idx + 1);
            div.appendChild(img);

            const delBtn = document.createElement('button');
            delBtn.type = 'button';
            delBtn.className = 'foto-thumb__del';
            delBtn.title = 'Quitar';
            delBtn.innerHTML = '<i class="bi bi-x-lg"></i>';
            delBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                URL.revokeObjectURL(item.url);
                pendingFiles.splice(idx, 1);
                syncInput();
                renderNewPreviews();
                updateCounter();
            });
            div.appendChild(delBtn);

            if (!isPrincipal) {
                const starBtn = document.createElement('button');
                starBtn.type = 'button';
                starBtn.className = 'foto-thumb__set-principal';
                starBtn.title = 'Establecer como principal';
                starBtn.innerHTML = '<i class="bi bi-star"></i>';
                starBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const [moved] = pendingFiles.splice(idx, 1);
                    pendingFiles.unshift(moved);
                    if (dbHasPrincipal) {
                        dbHasPrincipal = false;
                        const dbPrincipal = document.querySelector('#fotos-db-grid .foto-thumb--principal');
                        if (dbPrincipal) {
                            dbPrincipal.classList.remove('foto-thumb--principal');
                            const badge = dbPrincipal.querySelector('.foto-thumb__main');
                            if (badge) badge.style.display = 'none';
                            const oldStar = dbPrincipal.querySelector('.foto-thumb__set-principal');
                            if (oldStar) oldStar.style.display = '';
                        }
                        if (principalInput) principalInput.value = '';
                    }
                    syncInput();
                    renderNewPreviews();
                });
                div.appendChild(starBtn);
            }

            const badge = document.createElement('span');
            badge.className = 'foto-thumb__main';
            badge.innerHTML = '<i class="bi bi-star-fill me-1"></i>Principal';
            if (!isPrincipal) badge.style.display = 'none';
            div.appendChild(badge);

            newGrid.appendChild(div);
        });
    }

    function syncInput() {
        if (!input) return;
        const dt = new DataTransfer();
        pendingFiles.forEach(f => dt.items.add(f.file));
        input.files = dt.files;
    }

    if (input) {
        input.addEventListener('change', function () {
            const libre = MAX_TOTAL - dbCount - pendingFiles.length;
            const nuevos = Array.from(this.files).slice(0, libre);
            this.value = '';
            nuevos.forEach(file => {
                pendingFiles.push({ file, url: URL.createObjectURL(file) });
            });
            syncInput();
            renderNewPreviews();
            updateCounter();
        });
    }

    if (dropzone) {
        // Click abre el file input
        dropzone.addEventListener('click', () => {
            const triggerId = dropzone.dataset.trigger;
            const fileInput = triggerId ? document.getElementById(triggerId) : input;
            if (fileInput) fileInput.click();
        });
        dropzone.addEventListener('dragover',  e => { e.preventDefault(); dropzone.classList.add('foto-dropzone--over'); });
        dropzone.addEventListener('dragleave', () => dropzone.classList.remove('foto-dropzone--over'));
        dropzone.addEventListener('drop', e => {
            e.preventDefault();
            dropzone.classList.remove('foto-dropzone--over');
            const libre = MAX_TOTAL - dbCount - pendingFiles.length;
            const files = Array.from(e.dataTransfer.files)
                              .filter(f => f.type.startsWith('image/'))
                              .slice(0, libre);
            files.forEach(file => pendingFiles.push({ file, url: URL.createObjectURL(file) }));
            syncInput();
            renderNewPreviews();
            updateCounter();
        });
    }

    // Delegar clicks en DB grid (para botones que antes tenían onclick inline)
    const dbGrid = document.getElementById('fotos-db-grid');
    if (dbGrid) {
        dbGrid.addEventListener('click', function (e) {
            const btnPrincipal = e.target.closest('[data-action="set-principal-db"]');
            const btnEliminar  = e.target.closest('[data-action="eliminar-db"]');
            const btnDeshacer  = e.target.closest('[data-action="deshacer-db"]');
            if (btnPrincipal) {
                e.preventDefault();
                establecerPrincipalDb(btnPrincipal.dataset.fotoId);
            } else if (btnEliminar) {
                e.preventDefault();
                eliminarFotoDb(btnEliminar.dataset.fotoId, btnEliminar);
            } else if (btnDeshacer) {
                e.preventDefault();
                deshacerEliminarFotoDb(btnDeshacer.dataset.fotoId, btnDeshacer);
            }
        });
    }

    function establecerPrincipalDb(id) {
        document.querySelectorAll('#fotos-db-grid .foto-thumb').forEach(thumb => {
            thumb.classList.remove('foto-thumb--principal');
            const badge = thumb.querySelector('.foto-thumb__main');
            if (badge) badge.style.display = 'none';
            const starBtn = thumb.querySelector('.foto-thumb__set-principal');
            if (starBtn) starBtn.style.display = '';
        });

        const selected = document.getElementById('db-foto-' + id);
        if (selected) {
            selected.classList.add('foto-thumb--principal');
            const badge = selected.querySelector('.foto-thumb__main');
            if (badge) badge.style.display = '';
            const starBtn = selected.querySelector('.foto-thumb__set-principal');
            if (starBtn) starBtn.style.display = 'none';
        }

        dbHasPrincipal = true;
        renderNewPreviews();
        if (principalInput) principalInput.value = id;
    }

    function eliminarFotoDb(id, btn) {
        const thumb = document.getElementById('db-foto-' + id);
        const inp   = document.getElementById('del-input-' + id);
        if (!thumb || !inp) return;
        inp.value    = id;
        inp.disabled = false;
        thumb.style.opacity = '.35';
        thumb.style.pointerEvents = 'none';
        btn.innerHTML = '<i class="bi bi-arrow-counterclockwise"></i>';
        btn.title = 'Deshacer';
        btn.dataset.action = 'deshacer-db';
        dbCount--;
        if (thumb.classList.contains('foto-thumb--principal')) {
            dbHasPrincipal = false;
            if (principalInput) principalInput.value = '';
            renderNewPreviews();
        }
        updateCounter();
    }

    function deshacerEliminarFotoDb(id, btn) {
        const thumb = document.getElementById('db-foto-' + id);
        const inp   = document.getElementById('del-input-' + id);
        if (!thumb || !inp) return;
        inp.value    = '';
        inp.disabled = true;
        thumb.style.opacity = '';
        thumb.style.pointerEvents = '';
        btn.innerHTML = '<i class="bi bi-x-lg"></i>';
        btn.title = 'Eliminar foto';
        btn.dataset.action = 'eliminar-db';
        dbCount++;
        if (thumb.classList.contains('foto-thumb--principal')) {
            dbHasPrincipal = true;
            renderNewPreviews();
        }
        updateCounter();
    }

    updateCounter();
})();
