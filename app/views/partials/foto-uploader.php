<?php
/**
 * foto-uploader.php
 * Widget reutilizable de subida múltiple de fotos con selección de principal.
 *
 * Variables disponibles del scope padre:
 *   $fotosExistentes  array  (solo en edición) lista de fotos en DB
 *   $maxFotos         int    (default 10)
 */
$fotosExistentes = $fotosExistentes ?? [];
$maxFotos        = $maxFotos        ?? 10;
$yaSubidas       = count($fotosExistentes);
$disponibles     = $maxFotos - $yaSubidas;
?>

<div class="mb-4" id="foto-uploader">

    <div class="d-flex align-items-center justify-content-between mb-2">
        <label class="form-label mb-0">
            <i class="bi bi-images text-primary me-1"></i>Fotos del anuncio
        </label>
        <span class="badge text-muted" id="foto-counter"
              style="background:rgba(0,0,0,.04);font-size:.72rem;font-weight:500">
            <?= $yaSubidas ?> / <?= $maxFotos ?>
        </span>
    </div>

    <!-- Input oculto para foto principal de BD -->
    <input type="hidden" name="foto_principal_id" id="foto-principal-id" value="">

    <!-- Fotos ya guardadas en BD (solo edición) -->
    <?php if (!empty($fotosExistentes)): ?>
    <div class="foto-grid mb-3" id="fotos-db-grid">
        <?php foreach ($fotosExistentes as $i => $f): ?>
        <?php $esPrincipal = (int)$f['orden'] === 0; ?>
        <div class="foto-thumb <?= $esPrincipal ? 'foto-thumb--principal' : '' ?>"
             id="db-foto-<?= (int)$f['id'] ?>">
            <img src="<?= APP_URL ?>/img/<?= e($f['token']) ?>" alt="Foto">

            <!-- Botón eliminar -->
            <button type="button"
                    class="foto-thumb__del"
                    title="Eliminar foto"
                    onclick="eliminarFotoDb(<?= (int)$f['id'] ?>, this)">
                <i class="bi bi-x-lg"></i>
            </button>

            <!-- Botón establecer principal (oculto en la principal actual) -->
            <?php if (!$esPrincipal): ?>
            <button type="button"
                    class="foto-thumb__set-principal"
                    title="Establecer como principal"
                    onclick="establecerPrincipalDb(<?= (int)$f['id'] ?>)">
                <i class="bi bi-star"></i>
            </button>
            <?php endif; ?>

            <!-- Badge principal -->
            <span class="foto-thumb__main" <?= $esPrincipal ? '' : 'style="display:none"' ?>>
                <i class="bi bi-star-fill me-1"></i>Principal
            </span>

            <!-- Input oculto para eliminación -->
            <input type="hidden" name="eliminar_foto[]"
                   id="del-input-<?= (int)$f['id'] ?>"
                   value="" disabled>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Zona de nuevas fotos (preview local) -->
    <div class="foto-grid" id="fotos-new-grid"></div>

    <!-- Zona de arrastre / click -->
    <?php if ($disponibles > 0): ?>
    <div class="foto-dropzone" id="foto-dropzone"
         onclick="document.getElementById('fotos-input').click()">
        <i class="bi bi-cloud-arrow-up fs-3 d-block mb-2 text-primary"></i>
        <div style="font-size:.85rem;font-weight:600;color:var(--color-text)">
            Arrastra fotos aquí o haz clic para seleccionar
        </div>
        <div class="text-muted mt-1" style="font-size:.72rem">
            JPG, PNG, WEBP · Máx. 5 MB por foto · Hasta <?= $disponibles ?> foto<?= $disponibles !== 1 ? 's' : '' ?> más
        </div>
    </div>
    <input type="file"
           id="fotos-input"
           name="fotos[]"
           multiple
           accept="image/jpeg,image/png,image/webp"
           style="display:none">
    <?php else: ?>
    <div class="foto-dropzone foto-dropzone--full">
        <i class="bi bi-check-circle text-success fs-4 d-block mb-1"></i>
        <div style="font-size:.82rem;color:var(--color-text-muted)">
            Límite de <?= $maxFotos ?> fotos alcanzado
        </div>
    </div>
    <?php endif; ?>

    <div class="form-text text-muted mt-2" style="font-size:.72rem">
        <i class="bi bi-shield-lock text-primary me-1"></i>
        Las imágenes se sirven de forma segura y nunca se expone su ruta real.
        <span style="margin-left:.5rem">
            <i class="bi bi-star-fill text-warning me-1"></i>Haz clic en <i class="bi bi-star"></i> para elegir la foto principal.
        </span>
    </div>
</div>

<script>
(function () {
    const MAX_TOTAL  = <?= $maxFotos ?>;
    const input      = document.getElementById('fotos-input');
    const newGrid    = document.getElementById('fotos-new-grid');
    const counter    = document.getElementById('foto-counter');
    const dropzone   = document.getElementById('foto-dropzone');
    const principalInput = document.getElementById('foto-principal-id');

    // Archivos pendientes de subir
    let pendingFiles = [];

    // Cantidad de fotos ya en BD (descontando las marcadas para eliminar)
    let dbCount = <?= $yaSubidas ?>;

    // ¿Hay foto principal en BD que siga vigente?
    let dbHasPrincipal = <?= $yaSubidas > 0 ? 'true' : 'false' ?>;

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
            // Es principal si no hay ninguna en BD activa y es la primera nueva
            const isPrincipal = !dbHasPrincipal && idx === 0;

            const div = document.createElement('div');
            div.className = 'foto-thumb' + (isPrincipal ? ' foto-thumb--principal' : '');

            // Imagen
            const img = document.createElement('img');
            img.src = item.url;
            img.alt = 'Foto ' + (idx + 1);
            div.appendChild(img);

            // Botón eliminar
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

            // Botón "establecer principal" (solo en no-principal)
            if (!isPrincipal) {
                const starBtn = document.createElement('button');
                starBtn.type = 'button';
                starBtn.className = 'foto-thumb__set-principal';
                starBtn.title = 'Establecer como principal';
                starBtn.innerHTML = '<i class="bi bi-star"></i>';
                starBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    // Mover este archivo al inicio
                    const [moved] = pendingFiles.splice(idx, 1);
                    pendingFiles.unshift(moved);
                    // Si había principal en BD, quitarla
                    if (dbHasPrincipal) {
                        dbHasPrincipal = false;
                        // Quitar badge "Principal" del primer thumb de BD si existe
                        const dbPrincipal = document.querySelector('#fotos-db-grid .foto-thumb--principal');
                        if (dbPrincipal) {
                            dbPrincipal.classList.remove('foto-thumb--principal');
                            const badge = dbPrincipal.querySelector('.foto-thumb__main');
                            if (badge) badge.style.display = 'none';
                            // Mostrar botón estrella en la que era principal de BD
                            const oldStar = dbPrincipal.querySelector('.foto-thumb__set-principal');
                            if (oldStar) oldStar.style.display = '';
                        }
                        // Asegurarnos de que foto_principal_id no apunte a ninguna de BD
                        if (principalInput) principalInput.value = '';
                    }
                    syncInput();
                    renderNewPreviews();
                });
                div.appendChild(starBtn);
            }

            // Badge principal
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
            // Limpiar ANTES de syncInput para no borrar el DataTransfer recién asignado
            this.value = '';
            nuevos.forEach(file => {
                pendingFiles.push({ file, url: URL.createObjectURL(file) });
            });
            syncInput();
            renderNewPreviews();
            updateCounter();
        });
    }

    // Drag & drop
    if (dropzone) {
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

    // -------------------------------------------------------
    // Foto de BD: establecer como principal
    // -------------------------------------------------------
    window.establecerPrincipalDb = function (id) {
        // Quitar visual principal a todos los thumbs de BD
        document.querySelectorAll('#fotos-db-grid .foto-thumb').forEach(thumb => {
            thumb.classList.remove('foto-thumb--principal');
            const badge = thumb.querySelector('.foto-thumb__main');
            if (badge) badge.style.display = 'none';
            const starBtn = thumb.querySelector('.foto-thumb__set-principal');
            if (starBtn) starBtn.style.display = '';
        });

        // Marcar el elegido
        const selected = document.getElementById('db-foto-' + id);
        if (selected) {
            selected.classList.add('foto-thumb--principal');
            const badge = selected.querySelector('.foto-thumb__main');
            if (badge) badge.style.display = '';
            const starBtn = selected.querySelector('.foto-thumb__set-principal');
            if (starBtn) starBtn.style.display = 'none';
        }

        // Si había nuevas fotos como principal, quitarles el badge
        dbHasPrincipal = true;
        renderNewPreviews();

        // Actualizar el input oculto
        if (principalInput) principalInput.value = id;
    };

    // -------------------------------------------------------
    // Foto de BD: eliminar / deshacer
    // -------------------------------------------------------
    window.eliminarFotoDb = function (id, btn) {
        const thumb = document.getElementById('db-foto-' + id);
        const inp   = document.getElementById('del-input-' + id);
        if (!thumb || !inp) return;
        inp.value    = id;
        inp.disabled = false;
        thumb.style.opacity = '.35';
        thumb.style.pointerEvents = 'none';
        btn.innerHTML = '<i class="bi bi-arrow-counterclockwise"></i>';
        btn.title = 'Deshacer';
        btn.onclick = function () { deshacerEliminarFotoDb(id, btn); };
        dbCount--;
        // Si era la principal de BD, la principal pasa a ser la primera nueva (si hay)
        if (thumb.classList.contains('foto-thumb--principal')) {
            dbHasPrincipal = false;
            if (principalInput) principalInput.value = '';
            renderNewPreviews();
        }
        updateCounter();
    };

    window.deshacerEliminarFotoDb = function (id, btn) {
        const thumb = document.getElementById('db-foto-' + id);
        const inp   = document.getElementById('del-input-' + id);
        if (!thumb || !inp) return;
        inp.value    = '';
        inp.disabled = true;
        thumb.style.opacity = '';
        thumb.style.pointerEvents = '';
        btn.innerHTML = '<i class="bi bi-x-lg"></i>';
        btn.title = 'Eliminar foto';
        btn.onclick = function () { eliminarFotoDb(id, btn); };
        dbCount++;
        // Si era principal de BD, recuperarla
        if (thumb.classList.contains('foto-thumb--principal')) {
            dbHasPrincipal = true;
            renderNewPreviews();
        }
        updateCounter();
    };

    updateCounter();
})();
</script>
