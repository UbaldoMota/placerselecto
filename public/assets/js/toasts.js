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
})();
