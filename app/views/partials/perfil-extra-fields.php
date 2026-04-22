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
            <div class="contact-method__header" onclick="toggleCM('wa')">
                <i class="bi bi-whatsapp" style="font-size:1.25rem;color:#25d366;flex-shrink:0"></i>
                <span class="flex-fill fw-semibold" style="font-size:.9rem">WhatsApp</span>
                <div class="form-check form-switch mb-0" onclick="event.stopPropagation()">
                    <input class="form-check-input" type="checkbox" id="tog-wa"
                           <?= $tieneWa ? 'checked' : '' ?>
                           onchange="toggleCM('wa',this.checked)">
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
            <div class="contact-method__header" onclick="toggleCM('tg')">
                <i class="bi bi-telegram" style="font-size:1.25rem;color:#29b6f6;flex-shrink:0"></i>
                <span class="flex-fill fw-semibold" style="font-size:.9rem">Telegram</span>
                <div class="form-check form-switch mb-0" onclick="event.stopPropagation()">
                    <input class="form-check-input" type="checkbox" id="tog-tg"
                           <?= $tieneTg ? 'checked' : '' ?>
                           onchange="toggleCM('tg',this.checked)">
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
            <div class="contact-method__header" onclick="toggleCM('em')">
                <i class="bi bi-envelope" style="font-size:1.25rem;color:#F59E0B;flex-shrink:0"></i>
                <span class="flex-fill fw-semibold" style="font-size:.9rem">Correo electrónico</span>
                <div class="form-check form-switch mb-0" onclick="event.stopPropagation()">
                    <input class="form-check-input" type="checkbox" id="tog-em"
                           <?= $tieneEm ? 'checked' : '' ?>
                           onchange="toggleCM('em',this.checked)">
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
<script src="<?= APP_URL ?>/public/assets/vendor/leaflet/leaflet.js"></script>
<script>
(function(){
    /* ── Contact method toggles ── */
    function toggleCM(key, force) {
        const tog  = document.getElementById('tog-' + key);
        const body = document.getElementById('body-' + key);
        const card = document.getElementById('cm-' + key);
        const open = force !== undefined ? force : !tog.checked;
        tog.checked = open;
        body.classList.toggle('open', open);
        card.classList.toggle('activo', open);
        if (!open) {
            const inp = document.getElementById('inp-' + key);
            if (inp) inp.value = '';
        }
    }
    window.toggleCM = toggleCM;

    /* ── Mapa Leaflet ── */
    const initLat = <?= $tieneZona ? (float)$vLat : 23.6345 ?>;
    const initLng = <?= $tieneZona ? (float)$vLng : -102.5528 ?>;
    const initZoom = <?= $tieneZona ? 12 : 5 ?>;
    let radioKm = <?= (int)$vRadio ?>;

    const mapa = L.map('mapa-zona').setView([initLat, initLng], initZoom);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://openstreetmap.org">OpenStreetMap</a>',
        maxZoom: 18
    }).addTo(mapa);

    /* Marcador e icono personalizado */
    const icono = L.divIcon({
        className: '',
        html: '<div style="width:22px;height:22px;background:var(--color-primary);border:3px solid #fff;border-radius:50%;box-shadow:0 2px 8px rgba(0,0,0,.5)"></div>',
        iconSize: [22, 22], iconAnchor: [11, 11]
    });

    <?php if ($tieneZona): ?>
    let marker = L.marker([initLat, initLng], {draggable:true, icon: icono}).addTo(mapa);
    let circle = L.circle([initLat, initLng], {radius: radioKm * 1000, color:'#FF2D75', fillColor:'#FF2D75', fillOpacity:.15, weight:2}).addTo(mapa);
    <?php else: ?>
    let marker = null;
    let circle = null;
    <?php endif; ?>

    function setZona(lat, lng) {
        document.getElementById('zona_lat').value   = lat;
        document.getElementById('zona_lng').value   = lng;
        document.getElementById('zona_radio').value = radioKm;

        if (!marker) {
            marker = L.marker([lat, lng], {draggable:true, icon: icono}).addTo(mapa);
            marker.on('dragend', function(e){
                const pos = e.target.getLatLng();
                setZona(pos.lat.toFixed(7), pos.lng.toFixed(7));
                circle.setLatLng(pos);
            });
        } else {
            marker.setLatLng([lat, lng]);
        }

        if (!circle) {
            circle = L.circle([lat, lng], {radius: radioKm * 1000, color:'#FF2D75', fillColor:'#FF2D75', fillOpacity:.15, weight:2}).addTo(mapa);
        } else {
            circle.setLatLng([lat, lng]).setRadius(radioKm * 1000);
        }
    }

    <?php if ($tieneZona): ?>
    /* Drag listener para datos ya existentes */
    marker.on('dragend', function(e){
        const pos = e.target.getLatLng();
        setZona(pos.lat.toFixed(7), pos.lng.toFixed(7));
        circle.setLatLng(pos);
    });
    <?php endif; ?>

    /* Click en mapa → colocar marcador */
    mapa.on('click', function(e){
        setZona(e.latlng.lat.toFixed(7), e.latlng.lng.toFixed(7));
        mapa.setView(e.latlng);
    });

    /* Slider de radio */
    const slider   = document.getElementById('sliderRadio');
    const lblRadio = document.getElementById('lblRadio');
    const inpRadio = document.getElementById('zona_radio');
    slider.addEventListener('input', function(){
        radioKm = parseInt(this.value);
        lblRadio.textContent = radioKm + ' km';
        inpRadio.value = radioKm;
        if (circle) circle.setRadius(radioKm * 1000);
    });

    /* Búsqueda con Nominatim */
    function buscarZona() {
        const q = document.getElementById('mapSearch').value.trim();
        if (!q) return;
        fetch('https://nominatim.openstreetmap.org/search?q=' + encodeURIComponent(q) + '&format=json&limit=1&countrycodes=mx,us,co,ar,es,pe,ve,cl', {
            headers: {'Accept-Language': 'es'}
        })
        .then(r => r.json())
        .then(data => {
            if (data && data.length) {
                const lat = parseFloat(data[0].lat).toFixed(7);
                const lng = parseFloat(data[0].lon).toFixed(7);
                setZona(lat, lng);
                mapa.setView([lat, lng], 13);
                if (!document.getElementById('zona_descripcion').value) {
                    document.getElementById('zona_descripcion').value = data[0].display_name.split(',').slice(0,3).join(',');
                }
            } else {
                alert('No se encontró esa ubicación. Prueba con otro término.');
            }
        })
        .catch(() => alert('Error al buscar. Verifica tu conexión.'));
    }

    document.getElementById('btnMapSearch').addEventListener('click', buscarZona);
    document.getElementById('mapSearch').addEventListener('keydown', function(e){
        if (e.key === 'Enter') { e.preventDefault(); buscarZona(); }
    });

    /* Limpiar zona */
    const btnLimpiar = document.getElementById('btnLimpiarZona');
    if (btnLimpiar) {
        btnLimpiar.addEventListener('click', function(){
            document.getElementById('zona_lat').value   = '';
            document.getElementById('zona_lng').value   = '';
            document.getElementById('zona_descripcion').value = '';
            if (marker) { mapa.removeLayer(marker); marker = null; }
            if (circle) { mapa.removeLayer(circle); circle = null; }
        });
    }

    /* Invalidar tamaño del mapa cuando la página termina de cargar */
    setTimeout(() => mapa.invalidateSize(), 300);
})();
</script>
