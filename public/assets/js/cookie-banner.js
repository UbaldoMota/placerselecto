(function(){
    const banner = document.getElementById('cookie-banner');
    if (!banner) return;
    const STORAGE_KEY = 'ps_cookie_consent';

    // Si ya aceptó, no mostrar
    try {
        if (localStorage.getItem(STORAGE_KEY) === '1') return;
    } catch (_) { /* storage no disponible */ }

    // Mostrar con delay leve para no saltar al inicio
    setTimeout(() => {
        banner.classList.add('is-visible');
        banner.setAttribute('aria-hidden', 'false');
    }, 500);

    const btn = document.getElementById('cookie-banner-accept');
    if (btn) {
        btn.addEventListener('click', () => {
            try { localStorage.setItem(STORAGE_KEY, '1'); } catch (_) {}
            banner.classList.remove('is-visible');
            banner.setAttribute('aria-hidden', 'true');
            setTimeout(() => banner.remove(), 400);
        });
    }
})();
