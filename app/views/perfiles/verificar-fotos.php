<?php
/**
 * perfiles/verificar-fotos.php
 * Subida de fotos de verificación para el perfil (sin filtros, sin cubrir rostro).
 */
$yaEnviadas = !empty($fotosVerificacion);
?>

<div class="container py-4" style="max-width:700px">

    <div class="text-center mb-4">
        <h1 class="h4 fw-bold mb-1">
            <i class="bi bi-upload text-primary me-2"></i>Fotos de verificación
        </h1>
        <p class="text-muted mb-0" style="font-size:.875rem">
            Perfil: <strong><?= e($perfil['nombre']) ?></strong>
        </p>
    </div>

    <?php if ($yaEnviadas): ?>
    <div class="alert alert-success mb-4" style="font-size:.875rem">
        <i class="bi bi-check-circle-fill me-2"></i>
        Ya enviaste <?= count($fotosVerificacion) ?> foto(s) de verificación. Puedes subir más si lo deseas.
    </div>
    <?php endif; ?>

    <!-- Referencia: fotos del perfil -->
    <?php if (!empty($fotosGaleria)): ?>
    <div class="card mb-4">
        <div class="card-header">
            <span class="fw-semibold" style="font-size:.85rem">
                <i class="bi bi-collection me-2 text-primary"></i>Tus fotos de perfil — replica estas sin filtros
            </span>
        </div>
        <div class="card-body py-3">
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(80px,1fr));gap:.5rem">
                <?php foreach ($fotosGaleria as $foto): ?>
                <img src="<?= APP_URL ?>/img/<?= e($foto['token']) ?>"
                     style="width:100%;aspect-ratio:1;object-fit:cover;border-radius:6px;border:1px solid var(--color-border)"
                     loading="lazy">
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Formulario de subida -->
    <div class="card">
        <div class="card-header">
            <span class="fw-semibold" style="font-size:.875rem">
                <i class="bi bi-upload me-2 text-primary"></i>
                <?= $yaEnviadas ? 'Subir fotos adicionales' : 'Subir fotos de verificación' ?>
            </span>
        </div>
        <div class="card-body">
            <div class="alert mb-3" style="background:rgba(255,45,117,.07);border:1px solid rgba(255,45,117,.2);font-size:.82rem;color:var(--color-text)">
                <strong style="color:var(--color-primary)">Recuerda:</strong>
                Sin filtros · Sin emojis cubriendo el rostro · Mínimo 2/3 del cuerpo · Al menos 2 fotos de frente
            </div>

            <form method="POST"
                  action="<?= APP_URL ?>/perfil/<?= (int)$perfil['id'] ?>/verificar/fotos"
                  enctype="multipart/form-data">
                <?= $csrfField ?>

                <!-- Zona de arrastrar y soltar / seleccionar -->
                <div id="dropZone"
                     style="border:2px dashed var(--color-border);border-radius:var(--radius-md);padding:2rem;text-align:center;cursor:pointer;transition:border-color .2s;margin-bottom:1rem"
                     onclick="document.getElementById('inputFotos').click()">
                    <i class="bi bi-cloud-upload" style="font-size:2.5rem;color:var(--color-text-muted)"></i>
                    <p class="mt-2 mb-1" style="font-size:.875rem">
                        Arrastra tus fotos aquí o <strong style="color:var(--color-primary)">haz clic para seleccionar</strong>
                    </p>
                    <p class="text-muted mb-0" style="font-size:.75rem">JPG, PNG o WEBP · Máx. 5 MB por foto</p>
                </div>

                <input type="file" id="inputFotos" name="fotos[]"
                       multiple accept="image/jpeg,image/png,image/webp"
                       style="display:none">

                <!-- Previews -->
                <div id="previewsContainer" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(90px,1fr));gap:.5rem;margin-bottom:1rem"></div>

                <button type="submit" id="btnSubir" class="btn btn-primary w-100" disabled>
                    <i class="bi bi-upload me-2"></i>Enviar fotos de verificación
                </button>
            </form>
        </div>
    </div>

    <?php if ($yaEnviadas): ?>
    <!-- Fotos ya enviadas -->
    <div class="card mt-4">
        <div class="card-header">
            <span class="fw-semibold" style="font-size:.875rem">
                <i class="bi bi-check-circle text-success me-2"></i>Fotos ya enviadas
            </span>
        </div>
        <div class="card-body">
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(80px,1fr));gap:.5rem">
                <?php foreach ($fotosVerificacion as $fv): ?>
                <img src="<?= APP_URL ?>/img/<?= e($fv['token']) ?>"
                     style="width:100%;aspect-ratio:1;object-fit:cover;border-radius:6px;border:2px solid rgba(16,185,129,.4)"
                     loading="lazy">
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="text-center mt-4">
        <a href="<?= APP_URL ?>/mis-perfiles" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i>Volver a mis perfiles
        </a>
    </div>

</div>

<?php $extraJs = <<<'JS'
<script>
(function () {
    const input    = document.getElementById('inputFotos');
    const dropZone = document.getElementById('dropZone');
    const previews = document.getElementById('previewsContainer');
    const btnSubir = document.getElementById('btnSubir');
    let   archivos = new DataTransfer();

    function actualizarBoton() {
        btnSubir.disabled = archivos.files.length === 0;
        btnSubir.textContent = archivos.files.length > 0
            ? `Enviar ${archivos.files.length} foto(s) de verificación`
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
                div.innerHTML = `<img src="${e.target.result}"
                    style="width:100%;aspect-ratio:1;object-fit:cover;border-radius:6px;border:1px solid var(--color-border)">`;
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
</script>
JS;
?>
