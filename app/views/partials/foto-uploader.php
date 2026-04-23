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
            <img src="<?= APP_URL ?>/img/<?= e($f["token"]) ?>?size=thumb" alt="Foto">

            <!-- Botón eliminar -->
            <button type="button"
                    class="foto-thumb__del"
                    title="Eliminar foto"
                    data-action="eliminar-db"
                    data-foto-id="<?= (int)$f['id'] ?>">
                <i class="bi bi-x-lg"></i>
            </button>

            <!-- Botón establecer principal (oculto en la principal actual) -->
            <?php if (!$esPrincipal): ?>
            <button type="button"
                    class="foto-thumb__set-principal"
                    title="Establecer como principal"
                    data-action="set-principal-db"
                    data-foto-id="<?= (int)$f['id'] ?>">
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
    <div class="foto-dropzone" id="foto-dropzone" data-trigger="fotos-input">
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

<div id="foto-uploader-config"
     data-max-fotos="<?= (int)$maxFotos ?>"
     data-ya-subidas="<?= (int)$yaSubidas ?>"
     data-has-principal="<?= $yaSubidas > 0 ? '1' : '0' ?>"
     style="display:none"></div>
<script src="<?= APP_URL ?>/public/assets/js/foto-uploader.js" defer></script>
