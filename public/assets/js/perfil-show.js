(function(){
    // ─── Reporte: toggle URL cuando "fotos_de_internet" ───
    const motivoSel = document.getElementById('rp-motivo');
    const urlWrap   = document.getElementById('rp-url-wrap');
    const urlInp    = document.getElementById('rp-url');
    if (motivoSel && urlWrap) {
        function toggleUrl() {
            const needs = motivoSel.value === 'fotos_de_internet';
            urlWrap.classList.toggle('d-none', !needs);
            if (urlInp) urlInp.required = needs;
        }
        motivoSel.addEventListener('change', toggleUrl);
        toggleUrl();
    }

    // ─── Mapa del perfil (solo lectura) ───
    const mapEl = document.getElementById('mapa-show');
    if (!mapEl) return;
    function initShowMap() {
        if (typeof L === 'undefined') { setTimeout(initShowMap, 50); return; }
        renderShowMap();
    }
    initShowMap();
    function renderShowMap() {
        const lat   = parseFloat(mapEl.dataset.lat);
        const lng   = parseFloat(mapEl.dataset.lng);
        const radio = parseInt(mapEl.dataset.radio || '5', 10);
        const m = L.map('mapa-show', {
            zoomControl:false, dragging:false, scrollWheelZoom:false,
            doubleClickZoom:false, touchZoom:false, keyboard:false, attributionControl:false
        }).setView([lat, lng], 12);
        const uM = document.querySelector('meta[name="app-url"]');
        const tb = uM ? uM.getAttribute('content') : '';
        L.tileLayer(tb + '/tile/{z}/{x}/{y}.png').addTo(m);
        L.circle([lat, lng], {
            radius: radio * 1000, color:'#FF2D75',
            fillColor:'#FF2D75', fillOpacity:.2, weight:2
        }).addTo(m);
        const ic = L.divIcon({
            className:'',
            html:'<div style="width:16px;height:16px;background:#FF2D75;border:3px solid #fff;border-radius:50%;box-shadow:0 2px 6px rgba(0,0,0,.4)"></div>',
            iconSize:[16,16], iconAnchor:[8,8]
        });
        L.marker([lat, lng], {icon:ic}).addTo(m);
        setTimeout(() => m.invalidateSize(), 200);
    }
})();
