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
    }, 700);

    function dismiss(save) {
        if (save) {
            try { localStorage.setItem(STORAGE_KEY, '1'); } catch (_) {}
        }
        banner.classList.remove('is-visible');
        banner.classList.add('is-leaving');
        banner.setAttribute('aria-hidden', 'true');
        setTimeout(() => banner.remove(), 400);
    }

    document.getElementById('cookie-banner-accept')
        ?.addEventListener('click', () => dismiss(true));

    document.getElementById('cookie-banner-close')
        ?.addEventListener('click', () => dismiss(true));
})();
