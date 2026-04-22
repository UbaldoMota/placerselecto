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

    // ─── Lightbox galería ───
    const lightboxEl = document.getElementById('lightbox');
    const urlsData   = document.getElementById('lightbox-data');
    if (lightboxEl && urlsData) {
        let urls;
        try { urls = JSON.parse(urlsData.textContent || '[]'); } catch (_) { urls = []; }
        const total = urls.length;
        let current = 0;

        function open(idx) {
            current = idx;
            const img = document.getElementById('lightbox-img');
            const ctr = document.getElementById('lightbox-counter');
            img.src = urls[current] || '';
            if (ctr) ctr.textContent = total > 1 ? (current + 1) + ' / ' + total : '';
            lightboxEl.classList.add('is-open');
            document.querySelectorAll('.lightbox__nav').forEach(b => {
                b.style.display = total > 1 ? '' : 'none';
            });
        }
        function close() { lightboxEl.classList.remove('is-open'); }
        function nav(dir, e) {
            if (e) e.stopPropagation();
            current = (current + dir + total) % total;
            document.getElementById('lightbox-img').src = urls[current] || '';
            const ctr = document.getElementById('lightbox-counter');
            if (ctr) ctr.textContent = (current + 1) + ' / ' + total;
        }

        document.addEventListener('click', function(ev){
            const openEl = ev.target.closest('[data-lightbox-open]');
            if (openEl) {
                ev.preventDefault();
                open(parseInt(openEl.dataset.lightboxOpen, 10));
                return;
            }
            if (ev.target === lightboxEl) close();
            if (ev.target.closest('[data-lightbox="close"]')) close();
            const navEl = ev.target.closest('[data-lightbox-nav]');
            if (navEl) nav(parseInt(navEl.dataset.lightboxNav, 10), ev);
        });
        document.addEventListener('keydown', function(e) {
            if (!lightboxEl.classList.contains('is-open')) return;
            if (e.key === 'Escape')     close();
            if (e.key === 'ArrowRight') nav(1);
            if (e.key === 'ArrowLeft')  nav(-1);
        });
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
    }
})();
