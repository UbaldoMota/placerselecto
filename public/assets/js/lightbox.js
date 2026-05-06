/**
 * lightbox.js - Galería lightbox genérica.
 * Se activa si existe #lightbox y #lightbox-data (script type=application/json).
 * Triggers: data-lightbox-open="idx"
 * Controles: data-lightbox="close", data-lightbox-nav="-1|1"
 */
(function(){
    const lightboxEl = document.getElementById('lightbox');
    const urlsData   = document.getElementById('lightbox-data');
    if (!lightboxEl || !urlsData) return;

    // Reubicar el lightbox a <body> para escapar stacking contexts creados por
    // ancestros con transform/filter/backdrop-filter (p. ej. .card:hover con translateY).
    // Sin esto, el "position: fixed" queda confinado al card en lugar del viewport.
    if (lightboxEl.parentElement !== document.body) {
        document.body.appendChild(lightboxEl);
    }

    let urls;
    try { urls = JSON.parse(urlsData.textContent || '[]'); } catch (_) { urls = []; }
    const total = urls.length;
    let current = 0;

    function open(idx) {
        current = idx;
        const img = document.getElementById('lightbox-img');
        const ctr = document.getElementById('lightbox-counter');
        if (img) img.src = urls[current] || '';
        if (ctr) ctr.textContent = total > 1 ? (current + 1) + ' / ' + total : '';
        lightboxEl.classList.add('is-open');
        document.body.classList.add('lightbox-open');
        document.querySelectorAll('.lightbox__nav').forEach(b => {
            b.style.display = total > 1 ? '' : 'none';
        });
    }
    function close() {
        lightboxEl.classList.remove('is-open');
        document.body.classList.remove('lightbox-open');
    }
    function nav(dir, e) {
        if (e) e.stopPropagation();
        current = (current + dir + total) % total;
        const img = document.getElementById('lightbox-img');
        const ctr = document.getElementById('lightbox-counter');
        if (img) img.src = urls[current] || '';
        if (ctr) ctr.textContent = (current + 1) + ' / ' + total;
    }

    // Listener SOLO en los triggers específicos (no global) — evita conflictos con otros scripts
    document.querySelectorAll('[data-lightbox-open]').forEach(el => {
        el.addEventListener('click', function(ev){
            const interactive = ev.target.closest('button, a, form, input, label, select, [data-stop-propagation]');
            if (interactive && el.contains(interactive)) return;
            ev.preventDefault();
            ev.stopPropagation();
            open(parseInt(el.dataset.lightboxOpen, 10));
        });
    });

    // Controles dentro del lightbox
    lightboxEl.addEventListener('click', function(ev){
        ev.stopPropagation();
        if (ev.target.closest('[data-lightbox="close"]')) { close(); return; }
        const navEl = ev.target.closest('[data-lightbox-nav]');
        if (navEl) { nav(parseInt(navEl.dataset.lightboxNav, 10), ev); return; }
        if (ev.target === lightboxEl) { close(); return; }
    });
    document.addEventListener('keydown', function(e) {
        if (!lightboxEl.classList.contains('is-open')) return;
        if (e.key === 'Escape')     close();
        if (e.key === 'ArrowRight') nav(1);
        if (e.key === 'ArrowLeft')  nav(-1);
    });

    // -----------------------------------------------------------
    // Swipe táctil en pantallas móviles
    // -----------------------------------------------------------
    var SWIPE_MIN = 50;          // px mínimo horizontal para considerar swipe
    var SWIPE_MAX_VERTICAL = 60; // px máximo vertical permitido (evita confundir con scroll)
    var touchStartX = 0, touchStartY = 0, touchActive = false;

    lightboxEl.addEventListener('touchstart', function(ev){
        if (total < 2) return;
        if (ev.touches.length !== 1) { touchActive = false; return; }
        touchActive = true;
        touchStartX = ev.touches[0].clientX;
        touchStartY = ev.touches[0].clientY;
    }, { passive: true });

    lightboxEl.addEventListener('touchend', function(ev){
        if (!touchActive || total < 2) return;
        touchActive = false;
        var t  = ev.changedTouches[0];
        var dx = t.clientX - touchStartX;
        var dy = t.clientY - touchStartY;
        if (Math.abs(dx) < SWIPE_MIN || Math.abs(dy) > SWIPE_MAX_VERTICAL) return;
        // Swipe izquierda → siguiente; derecha → anterior
        nav(dx < 0 ? 1 : -1);
    }, { passive: true });

    lightboxEl.addEventListener('touchcancel', function(){
        touchActive = false;
    }, { passive: true });
})();
