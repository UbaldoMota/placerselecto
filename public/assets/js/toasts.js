(function(){
    if (window.__toastsBound) return;
    window.__toastsBound = true;

    var DUR = { success: 5000, info: 5000, warning: 7000, danger: 9000 };

    function closeToast(t) {
        if (!t || t.__closed) return;
        t.__closed = true;
        t.classList.add('is-leaving');
        setTimeout(function(){ t.remove(); }, 260);
    }

    function scheduleDismiss(t) {
        var variant = ['success','info','warning','danger'].find(function(v){ return t.classList.contains('toast-' + v); }) || 'info';
        var ms = DUR[variant];
        var remaining = ms;
        var startedAt;

        function start() {
            startedAt = Date.now();
            t.__timer = setTimeout(function(){ closeToast(t); }, remaining);
            var bar = t.querySelector('.toast-progress');
            if (bar) {
                bar.style.transition = 'none';
                bar.style.transform  = 'scaleX(1)';
                bar.offsetWidth;
                bar.style.transition = 'transform ' + remaining + 'ms linear';
                bar.style.transform  = 'scaleX(0)';
            }
        }
        function pause() {
            if (!t.__timer) return;
            clearTimeout(t.__timer);
            t.__timer = null;
            remaining -= (Date.now() - startedAt);
            if (remaining < 800) remaining = 800;
            var bar = t.querySelector('.toast-progress');
            if (bar) {
                var cs = getComputedStyle(bar).transform;
                bar.style.transition = 'none';
                bar.style.transform  = cs;
            }
        }

        t.addEventListener('mouseenter', pause);
        t.addEventListener('focusin',    pause);
        t.addEventListener('mouseleave', start);
        t.addEventListener('focusout',   start);
        start();
    }

    document.addEventListener('click', function(ev){
        var btn = ev.target.closest('.toast-close');
        if (!btn) return;
        closeToast(btn.closest('.toast-item'));
    });

    document.querySelectorAll('#toast-container .toast-item').forEach(scheduleDismiss);

    /**
     * API pública: crear toast dinámico desde cualquier JS.
     *   window.showToast('success' | 'info' | 'warning' | 'danger' | 'error', 'mensaje');
     * En vistas standalone donde el #toast-container no existe, lo crea.
     */
    var META = {
        success: { icon: 'check-circle-fill',        title: 'Éxito' },
        info:    { icon: 'info-circle-fill',         title: 'Información' },
        warning: { icon: 'exclamation-triangle-fill',title: 'Aviso' },
        danger:  { icon: 'x-octagon-fill',           title: 'Error' },
        error:   { icon: 'x-octagon-fill',           title: 'Error' }
    };

    function ensureContainer() {
        var c = document.getElementById('toast-container');
        if (c) return c;
        c = document.createElement('div');
        c.id = 'toast-container';
        c.setAttribute('aria-live', 'polite');
        c.setAttribute('aria-atomic', 'true');
        document.body.appendChild(c);
        return c;
    }

    function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, function(ch){
            return { '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;' }[ch];
        });
    }

    window.showToast = function(variant, message, title) {
        if (!variant || !message) return;
        var v = (variant === 'error') ? 'danger' : variant;
        var meta = META[v] || META.info;
        var container = ensureContainer();

        var t = document.createElement('div');
        t.className = 'toast-item toast-' + v;
        t.setAttribute('role', 'alert');
        t.innerHTML =
            '<div class="toast-icon"><i class="bi bi-' + meta.icon + '"></i></div>' +
            '<div class="toast-body">' +
                '<div class="toast-title">' + escapeHtml(title || meta.title) + '</div>' +
                '<div class="toast-message">' + escapeHtml(message) + '</div>' +
            '</div>' +
            '<button type="button" class="toast-close" aria-label="Cerrar">' +
                '<i class="bi bi-x-lg"></i>' +
            '</button>' +
            '<div class="toast-progress" aria-hidden="true"></div>';
        container.appendChild(t);
        scheduleDismiss(t);
        return t;
    };
})();
