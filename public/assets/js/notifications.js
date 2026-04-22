/**
 * notifications.js
 * Polling de la campanita con Page Visibility API + backoff exponencial.
 *
 * - Intervalo base: 30s
 * - Si la pestaña está oculta: pausa
 * - Si no hay cambios (304) varias veces seguidas: aumenta hasta 90s
 * - Al llegar algo nuevo: resetea a base
 */
(function () {
    'use strict';

    const root = document.getElementById('notif-dropdown-wrap');
    if (!root) return; // usuario no autenticado o navbar sin campanita

    const badge     = document.getElementById('notif-badge');
    const list      = document.getElementById('notif-list');
    const markAll   = document.getElementById('notif-mark-all');
    const bellBtn   = document.getElementById('notif-bell-btn');

    const ENDPOINT   = window.APP_URL_JS + '/api/notificaciones/pendientes';
    const CSRF_META  = document.querySelector('meta[name="csrf-token"]');
    const CSRF_TOKEN = CSRF_META ? CSRF_META.getAttribute('content') : '';

    const BASE_INTERVAL = 30000;  // 30s
    const MAX_INTERVAL  = 90000;  // 90s
    const BACKOFF_STEP  = 15000;  // +15s por cada 304

    let currentInterval = BASE_INTERVAL;
    let timerId = null;
    let lastEtag = '';
    let lastHash = '';
    let lastCount = 0;
    let inFlight = false;

    // -----------------------------------------------------------
    // Fetch + render
    // -----------------------------------------------------------
    async function poll(force = false) {
        if (inFlight) return;
        if (document.hidden && !force) return;
        inFlight = true;

        try {
            const res = await fetch(ENDPOINT, {
                method: 'GET',
                headers: lastEtag ? { 'If-None-Match': lastEtag } : {},
                credentials: 'same-origin'
            });

            if (res.status === 304) {
                // sin cambios — aplicar backoff
                currentInterval = Math.min(currentInterval + BACKOFF_STEP, MAX_INTERVAL);
                return;
            }

            if (!res.ok) return;

            const etag = res.headers.get('ETag') || '';
            const data = await res.json();

            if (data.hash && data.hash !== lastHash) {
                // hay cambios — reset a intervalo base
                currentInterval = BASE_INTERVAL;
                render(data);
                lastHash = data.hash;
                lastEtag = etag;

                // Si subió el count y es un count nuevo, notificar audible (opcional)
                if (data.count > lastCount && lastCount >= 0) {
                    pulseBell();
                }
                lastCount = data.count;
            } else {
                lastEtag = etag;
            }
        } catch (_) {
            // red caída — backoff suave
            currentInterval = Math.min(currentInterval + BACKOFF_STEP, MAX_INTERVAL);
        } finally {
            inFlight = false;
        }
    }

    function render(data) {
        // Badge
        if (data.count > 0) {
            badge.textContent = data.count > 99 ? '99+' : String(data.count);
            badge.classList.remove('d-none');
        } else {
            badge.classList.add('d-none');
        }

        // Lista
        if (!data.items || data.items.length === 0) {
            list.innerHTML =
                '<div class="text-center text-muted py-4" style="font-size:.82rem">' +
                '<i class="bi bi-check2-circle"></i> No tienes notificaciones' +
                '</div>';
            return;
        }

        list.innerHTML = data.items.map(itemHtml).join('');
    }

    function itemHtml(n) {
        const unread = !n.leida;
        const icon   = escapeHtml(n.icono || 'bell');
        const color  = escapeHtml(n.color || 'primary');
        const title  = escapeHtml(n.titulo || '');
        const msg    = escapeHtml(n.mensaje || '');
        const when   = escapeHtml(n.tiempo_rel || '');
        const href   = n.url ? window.APP_URL_JS + n.url : '#';

        return `
<a href="${href}" data-notif-id="${n.id}"
   class="notif-item d-flex gap-2 px-3 py-2 text-decoration-none ${unread ? 'notif-unread' : ''}"
   style="border-bottom:1px solid var(--color-border);color:var(--color-text)">
    <div class="notif-icon text-${color}" style="font-size:1.1rem;line-height:1">
        <i class="bi bi-${icon}"></i>
    </div>
    <div class="flex-grow-1" style="min-width:0">
        <div class="d-flex justify-content-between gap-2">
            <strong style="font-size:.82rem">${title}</strong>
            ${unread ? '<span class="badge bg-primary" style="font-size:.55rem;align-self:flex-start">Nuevo</span>' : ''}
        </div>
        <div class="text-muted" style="font-size:.78rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${msg}</div>
        <div class="text-muted" style="font-size:.68rem">${when}</div>
    </div>
</a>`;
    }

    function escapeHtml(s) {
        return String(s)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }

    function pulseBell() {
        bellBtn.classList.add('notif-pulse');
        setTimeout(() => bellBtn.classList.remove('notif-pulse'), 1200);
    }

    // -----------------------------------------------------------
    // Interacciones: click item → marca leída; marcar todas
    // -----------------------------------------------------------
    list.addEventListener('click', async (ev) => {
        const item = ev.target.closest('.notif-item');
        if (!item) return;
        const id = item.getAttribute('data-notif-id');
        if (!id) return;

        // marcar como leída en background (no bloquea la navegación)
        try {
            const fd = new FormData();
            fd.append('_csrf_token', CSRF_TOKEN);
            fetch(window.APP_URL_JS + '/notificacion/' + encodeURIComponent(id) + '/leer', {
                method: 'POST',
                body: fd,
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                keepalive: true
            });
        } catch (_) {}
    });

    markAll.addEventListener('click', async (ev) => {
        ev.preventDefault();
        try {
            const fd = new FormData();
            fd.append('_csrf_token', CSRF_TOKEN);
            const res = await fetch(window.APP_URL_JS + '/notificaciones/leer-todas', {
                method: 'POST',
                body: fd,
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (res.ok) {
                lastHash = ''; // forzar refresh
                poll(true);
            }
        } catch (_) {}
    });

    // -----------------------------------------------------------
    // Page Visibility API: pausar/reanudar
    // -----------------------------------------------------------
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) {
            // al volver, polear inmediato y reiniciar intervalo
            currentInterval = BASE_INTERVAL;
            poll(true);
            scheduleNext();
        }
    });

    // -----------------------------------------------------------
    // Scheduler recursivo (respeta currentInterval mutante)
    // -----------------------------------------------------------
    function scheduleNext() {
        if (timerId) clearTimeout(timerId);
        timerId = setTimeout(async () => {
            await poll();
            scheduleNext();
        }, currentInterval);
    }

    // Primera carga: inmediata (para llenar el dropdown aunque no abran)
    poll(true).then(scheduleNext);
})();
