<?php
/**
 * user/subir-documento.php — Subida de documento de identidad (INE / Pasaporte).
 * Una vez por cuenta. Opcional pero aumenta la confiabilidad del perfil.
 */
$tieneDocumento  = !empty($usuario['documento_identidad']);
$docEstado       = $usuario['documento_estado'] ?? null;
$documentoVerif  = $docEstado === 'verificado';
$docRechazado    = $docEstado === 'rechazado';
$docMotivo       = $usuario['documento_rechazo_motivo'] ?? null;
?>

<div class="container py-4" style="max-width:640px">

    <div class="d-flex flex-wrap gap-2 mb-4">
        <a href="<?= APP_URL ?>/mis-perfiles" class="btn btn-sm btn-secondary">
            <i class="bi bi-person-lines-fill me-1"></i>Mis perfiles
        </a>
        <a href="<?= APP_URL ?>/dashboard" class="btn btn-sm btn-secondary">
            <i class="bi bi-house me-1"></i>Dashboard
        </a>
    </div>

    <div class="d-flex align-items-center gap-3 mb-4">
        <div style="width:52px;height:52px;border-radius:50%;background:rgba(255,45,117,.12);border:2px solid rgba(255,45,117,.25);display:flex;align-items:center;justify-content:center;flex-shrink:0">
            <i class="bi bi-card-checklist" style="font-size:1.4rem;color:var(--color-primary)"></i>
        </div>
        <div>
            <h1 class="h4 fw-bold mb-0">Verificación de identidad</h1>
            <p class="text-muted mb-0" style="font-size:.85rem">
                Sube una foto de tu INE o pasaporte para aumentar la confianza en tu perfil
            </p>
        </div>
    </div>

    <!-- Estado actual -->
    <?php if ($tieneDocumento): ?>
    <?php if ($documentoVerif): ?>
    <div class="card mb-4" style="border:1px solid rgba(16,185,129,.3)">
        <div class="card-body d-flex align-items-center gap-3 py-3">
            <i class="bi bi-patch-check-fill" style="font-size:1.8rem;color:#10B981;flex-shrink:0"></i>
            <div>
                <div class="fw-semibold" style="color:#10B981">Identidad verificada</div>
                <div class="text-muted" style="font-size:.8rem">
                    El equipo revisó y aprobó tu documento.
                    <?php if (!empty($usuario['documento_identidad_at'])): ?>
                    Enviado el <?= e(date('d/m/Y', strtotime($usuario['documento_identidad_at']))) ?>.
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php elseif ($docRechazado): ?>
    <div class="card mb-4" style="border:1px solid rgba(220,53,69,.35)">
        <div class="card-body py-3">
            <div class="d-flex align-items-start gap-3">
                <i class="bi bi-x-circle-fill" style="font-size:1.8rem;color:#dc3545;flex-shrink:0;margin-top:.1rem"></i>
                <div>
                    <div class="fw-semibold mb-1" style="color:#dc3545">Documento rechazado</div>
                    <?php if ($docMotivo): ?>
                    <div class="mb-2" style="font-size:.82rem;color:var(--color-text-muted)">
                        <strong>Motivo:</strong> <?= e($docMotivo) ?>
                    </div>
                    <?php endif; ?>
                    <div class="text-muted" style="font-size:.8rem">
                        Por favor corrige el problema y vuelve a subir tu documento usando el formulario de abajo.
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="card mb-4" style="border:1px solid rgba(255,193,7,.25)">
        <div class="card-body d-flex align-items-center gap-3 py-3">
            <i class="bi bi-clock-history" style="font-size:1.8rem;color:#F59E0B;flex-shrink:0"></i>
            <div>
                <div class="fw-semibold" style="color:#F59E0B">Documento en revisión</div>
                <div class="text-muted" style="font-size:.8rem">
                    Recibimos tu documento y lo estamos revisando.
                    <?php if (!empty($usuario['documento_identidad_at'])): ?>
                    Enviado el <?= e(date('d/m/Y', strtotime($usuario['documento_identidad_at']))) ?>.
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>

    <!-- Beneficios -->
    <div class="card mb-4" style="background:rgba(255,45,117,.04);border:1px solid rgba(255,45,117,.12)">
        <div class="card-body py-3">
            <p class="mb-2 fw-semibold" style="font-size:.85rem;color:var(--color-primary)">
                <i class="bi bi-shield-check me-1"></i>¿Por qué verificar mi identidad?
            </p>
            <ul class="mb-0" style="font-size:.82rem;color:var(--color-text-muted);padding-left:1.2rem;line-height:1.8">
                <li>Aparece el sello <strong>"Identidad verificada"</strong> en tu perfil</li>
                <li>Aumenta la confianza de los usuarios que te contactan</li>
                <li>Mejora tu puntuación de confiabilidad</li>
                <li>Es completamente opcional — tu documento nunca es público</li>
            </ul>
        </div>
    </div>

    <!-- Formulario -->
    <div class="card">
        <div class="card-header">
            <span class="fw-semibold" style="font-size:.875rem">
                <i class="bi bi-upload text-primary me-2"></i>
                <?= $tieneDocumento ? 'Actualizar documento' : 'Subir documento' ?>
            </span>
        </div>
        <div class="card-body">

            <!-- Instrucciones -->
            <div class="mb-4 p-3 rounded" style="background:rgba(0,0,0,.03);border:1px solid var(--color-border);font-size:.82rem">
                <p class="mb-2 fw-semibold">Qué documentos se aceptan:</p>
                <div class="d-flex gap-4 flex-wrap mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-credit-card-2-front" style="color:var(--color-primary);font-size:1.1rem"></i>
                        <span>INE / IFE (frente y vuelta en una sola imagen)</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-book" style="color:var(--color-primary);font-size:1.1rem"></i>
                        <span>Pasaporte (página con foto)</span>
                    </div>
                </div>
                <ul class="mb-0 text-muted" style="padding-left:1.1rem;line-height:1.8">
                    <li>Foto clara y legible, sin recortes ni dedos tapando datos</li>
                    <li>Formatos aceptados: JPG, PNG o WEBP — máximo 5 MB</li>
                    <li>Tu documento es privado: solo lo revisa el equipo de moderación</li>
                </ul>
            </div>

            <form method="POST"
                  action="<?= APP_URL ?>/mi-cuenta/documento"
                  enctype="multipart/form-data"
                  id="formDocumento">
                <?= $csrfField ?>

                <!-- Zona de drop -->
                <div id="dropZone"
                     style="border:2px dashed var(--color-border);border-radius:10px;padding:2rem;text-align:center;cursor:pointer;transition:border-color .2s,background .2s;margin-bottom:1rem"
                     data-trigger-file="inputDocumento">
                    <i class="bi bi-cloud-arrow-up" id="dropIcon"
                       style="font-size:2.5rem;color:var(--color-text-muted);display:block;margin-bottom:.5rem"></i>
                    <p class="mb-1 fw-semibold" id="dropText">Haz clic o arrastra tu documento aquí</p>
                    <p class="text-muted mb-0" style="font-size:.78rem">JPG, PNG o WEBP — máx. 5 MB</p>
                    <!-- Previsualización -->
                    <img id="previewImg" src="" alt=""
                         style="display:none;max-width:100%;max-height:260px;border-radius:8px;margin-top:1rem;border:1px solid var(--color-border)">
                </div>

                <input type="file"
                       id="inputDocumento"
                       name="documento"
                       accept="image/jpeg,image/png,image/webp"
                       required
                       style="display:none">

                <button type="submit" class="btn btn-primary w-100" id="btnEnviar" disabled>
                    <i class="bi bi-send me-2"></i>Enviar para revisión
                </button>
            </form>
        </div>
    </div>
</div>

<script src="<?= APP_URL ?>/public/assets/js/subir-documento.js" defer></script>
