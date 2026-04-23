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
})();
