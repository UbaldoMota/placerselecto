<?php
/**
 * partials/perfil-extra-fields.php
 * Campos adicionales del perfil: medios de contacto, anticipo y zona de trabajo.
 * Variables esperadas del scope padre:
 *   $p (array)  — datos actuales del perfil (en edición) o [] (en creación)
 *   $old (array) — datos previos si hubo error de validación
 */
$p   = $p   ?? [];
$old = $old ?? [];

// Valores actuales (edición tiene prioridad, luego old input, luego vacío)
$vWa    = $old['whatsapp']       ?? $p['whatsapp']       ?? '';
$vTg    = $old['telegram']       ?? $p['telegram']       ?? '';
$vEm    = $old['email_contacto'] ?? $p['email_contacto'] ?? '';
$vAnti  = isset($old['pide_anticipo']) ? (bool)$old['pide_anticipo'] : (bool)($p['pide_anticipo'] ?? false);
$vLat   = $old['zona_lat']        ?? $p['zona_lat']        ?? '';
$vLng   = $old['zona_lng']        ?? $p['zona_lng']        ?? '';
$vRadio = $old['zona_radio']      ?? $p['zona_radio']      ?? 5;
$vZDesc = $old['zona_descripcion'] ?? $p['zona_descripcion'] ?? '';

$tieneWa = $vWa !== '';
$tieneTg = $vTg !== '';
$tieneEm = $vEm !== '';
$tieneZona = $vLat !== '' && $vLng !== '';
?>
<!-- Leaflet CSS -->
<link rel="stylesheet" href="<?= APP_URL ?>/public/assets/vendor/leaflet/leaflet.css">

<style>
.contact-method{border:1px solid var(--color-border);border-radius:var(--radius-md);overflow:hidden;transition:border-color .2s}
.contact-method.activo{border-color:rgba(255,45,117,.4)}
.contact-method__header{display:flex;align-items:center;gap:.75rem;padding:.75rem 1rem;cursor:pointer;user-select:none}
.contact-method__header:hover{background:rgba(0,0,0,.03)}
.contact-method__body{padding:0 1rem .85rem;display:none}
.contact-method__body.open{display:block}
#mapa-zona{height:300px;border-radius:var(--radius-md);border:1px solid var(--color-border);z-index:0}
.leaflet-container{background:#F5F5F5}
.form-check-input:checked{background-color:var(--color-primary);border-color:var(--color-primary)}
</style>

<!-- ══════════════════════════════════════════
     MEDIOS DE CONTACTO
══════════════════════════════════════════ -->
<div class="mb-4">
    <label class="form-label fw-semibold">
        <i class="bi bi-chat-dots text-primary me-1"></i>Medios de contacto
        <span class="text-muted fw-normal" style="font-size:.75rem">(selecciona los que uses)</span>
    </label>
    <div class="d-flex flex-column gap-2">

        <!-- WhatsApp -->
        <div class="contact-method <?= $tieneWa ? 'activo' : '' ?>" id="cm-wa">
            <div class="contact-method__header" data-cm-toggle="wa">
                <i class="bi bi-whatsapp" style="font-size:1.25rem;color:#25d366;flex-shrink:0"></i>
                <span class="flex-fill fw-semibold" style="font-size:.9rem">WhatsApp</span>
                <div class="form-check form-switch mb-0" >
                    <input class="form-check-input" type="checkbox" id="tog-wa"
                           <?= $tieneWa ? 'checked' : '' ?>
                           data-cm-switch="wa">
                </div>
            </div>
            <div class="contact-method__body <?= $tieneWa ? 'open' : '' ?>" id="body-wa">
                <div class="input-group">
                    <span class="input-group-text" style="background:var(--color-bg-card2);border-color:var(--color-border);color:var(--color-text-muted)">+</span>
                    <input type="tel" name="whatsapp" id="inp-wa" class="form-control"
                           placeholder="52 55 1234 5678" maxlength="20"
                           value="<?= e($vWa) ?>">
                </div>
                <div class="form-text" style="font-size:.72rem">Incluye código de país. Ej: 52 55 1234 5678</div>
            </div>
        </div>

        <!-- Telegram -->
        <div class="contact-method <?= $tieneTg ? 'activo' : '' ?>" id="cm-tg">
            <div class="contact-method__header" data-cm-toggle="tg">
                <i class="bi bi-telegram" style="font-size:1.25rem;color:#29b6f6;flex-shrink:0"></i>
                <span class="flex-fill fw-semibold" style="font-size:.9rem">Telegram</span>
                <div class="form-check form-switch mb-0" >
                    <input class="form-check-input" type="checkbox" id="tog-tg"
                           <?= $tieneTg ? 'checked' : '' ?>
                           data-cm-switch="tg">
                </div>
            </div>
            <div class="contact-method__body <?= $tieneTg ? 'open' : '' ?>" id="body-tg">
                <div class="input-group">
                    <span class="input-group-text" style="background:var(--color-bg-card2);border-color:var(--color-border);color:var(--color-text-muted)">@</span>
                    <input type="text" name="telegram" id="inp-tg" class="form-control"
                           placeholder="usuario" maxlength="100"
                           value="<?= e($vTg) ?>">
                </div>
                <div class="form-text" style="font-size:.72rem">Tu @usuario de Telegram (sin el @)</div>
            </div>
        </div>

        <!-- Email -->
        <div class="contact-method <?= $tieneEm ? 'activo' : '' ?>" id="cm-em">
            <div class="contact-method__header" data-cm-toggle="em">
                <i class="bi bi-envelope" style="font-size:1.25rem;color:#F59E0B;flex-shrink:0"></i>
                <span class="flex-fill fw-semibold" style="font-size:.9rem">Correo electrónico</span>
                <div class="form-check form-switch mb-0" >
                    <input class="form-check-input" type="checkbox" id="tog-em"
                           <?= $tieneEm ? 'checked' : '' ?>
                           data-cm-switch="em">
                </div>
            </div>
            <div class="contact-method__body <?= $tieneEm ? 'open' : '' ?>" id="body-em">
                <input type="email" name="email_contacto" id="inp-em" class="form-control"
                       placeholder="contacto@ejemplo.com" maxlength="150"
                       value="<?= e($vEm) ?>">
                <div class="form-text" style="font-size:.72rem">Este email se mostrará en tu perfil público</div>
            </div>
        </div>

    </div>
</div>

<!-- ══════════════════════════════════════════
     ANTICIPO
══════════════════════════════════════════ -->
<div class="mb-4">
    <label class="form-label fw-semibold">
        <i class="bi bi-cash-coin text-primary me-1"></i>Pago anticipado
    </label>
    <div class="d-flex align-items-start gap-3 p-3 rounded"
         style="background:rgba(0,0,0,.02);border:1px solid var(--color-border)">
        <div class="form-check form-switch mt-1 mb-0">
            <input class="form-check-input" type="checkbox" id="pide_anticipo"
                   name="pide_anticipo" value="1" <?= $vAnti ? 'checked' : '' ?>>
        </div>
        <label for="pide_anticipo" style="cursor:pointer">
            <div class="fw-semibold" style="font-size:.875rem">Solicito pago por adelantado</div>
            <div class="text-muted" style="font-size:.76rem;line-height:1.5">
                Activa esta opción si pides anticipo antes de la cita.
                Los clientes verán esta información en tu perfil.
                <span style="color:var(--color-primary)">Desactivarlo mejora tu indicador de confianza.</span>
            </div>
        </label>
    </div>
</div>

<!-- ══════════════════════════════════════════
     ZONA DE TRABAJO (MAPA)
══════════════════════════════════════════ -->
<div class="mb-4">
    <label class="form-label fw-semibold">
        <i class="bi bi-geo-alt text-primary me-1"></i>Zona de trabajo
        <span class="text-muted fw-normal" style="font-size:.75rem">(opcional)</span>
    </label>
    <p class="text-muted mb-2" style="font-size:.78rem">
        Busca tu colonia o zona y ajusta el área de cobertura. Esta información se mostrará en tu perfil.
    </p>

    <!-- Buscador -->
    <div class="input-group mb-2">
        <input type="text" id="mapSearch" class="form-control"
               placeholder="Busca tu colonia, ciudad o zona…"
               style="font-size:.875rem">
        <button type="button" class="btn btn-secondary" id="btnMapSearch">
            <i class="bi bi-search"></i>
        </button>
        <?php if ($tieneZona): ?>
        <button type="button" class="btn btn-secondary" id="btnLimpiarZona" title="Quitar zona">
            <i class="bi bi-x-lg"></i>
        </button>
        <?php endif; ?>
    </div>

    <!-- Mapa -->
    <div id="mapa-zona"></div>

    <!-- Radio -->
    <div class="d-flex align-items-center gap-3 mt-2">
        <span class="text-muted" style="font-size:.78rem;white-space:nowrap">
            <i class="bi bi-arrows-angle-expand me-1"></i>Radio de cobertura:
        </span>
        <input type="range" id="sliderRadio" class="form-range flex-fill"
               min="1" max="50" value="<?= (int)$vRadio ?>">
        <span id="lblRadio" class="fw-semibold" style="font-size:.82rem;min-width:40px;color:var(--color-primary)">
            <?= (int)$vRadio ?> km
        </span>
    </div>

    <!-- Descripción de zona -->
    <input type="text" name="zona_descripcion" id="zona_descripcion" class="form-control mt-2"
           placeholder="Ej: Zona Norte de CDMX, Polanco, Condesa…"
           maxlength="200"
           value="<?= e($vZDesc) ?>"
           style="font-size:.875rem">
    <div class="form-text" style="font-size:.72rem">Descripción opcional de tu zona (aparece en el perfil)</div>

    <!-- Inputs ocultos -->
    <input type="hidden" name="zona_lat"   id="zona_lat"   value="<?= e((string)$vLat) ?>">
    <input type="hidden" name="zona_lng"   id="zona_lng"   value="<?= e((string)$vLng) ?>">
    <input type="hidden" name="zona_radio" id="zona_radio" value="<?= (int)$vRadio ?>">
</div>

<!-- ══════════════════════════════════════════
     JS — Leaflet + lógica de contacto
══════════════════════════════════════════ -->

<!-- Config + JS (CSP-safe: sin inline) -->
<div id="perfil-extra-config"
     data-tiene-zona="<?= $tieneZona ? '1' : '0' ?>"
     data-lat="<?= $tieneZona ? (float)$vLat : '23.6345' ?>"
     data-lng="<?= $tieneZona ? (float)$vLng : '-102.5528' ?>"
     data-zoom="<?= $tieneZona ? 12 : 5 ?>"
     data-radio="<?= (int)$vRadio ?>"
     style="display:none"></div>
<script src="<?= APP_URL ?>/public/assets/vendor/leaflet/leaflet.js" defer></script>
<script src="<?= APP_URL ?>/public/assets/js/perfil-extra-fields.js" defer></script>
