/**
 * app.js
 * JavaScript principal de la aplicación.
 * Funciones globales: flash messages, confirmaciones, AJAX helpers,
 * búsqueda en tiempo real y utilidades de UI.
 */

'use strict';

// ---------------------------------------------------------
// UTILIDADES GLOBALES
// ---------------------------------------------------------
const App = {

    /**
     * Petición fetch con JSON, CSRF token y manejo de errores centralizado.
     *
     * @param {string} url
     * @param {Object} options  Opciones fetch adicionales
     * @returns {Promise<any>}
     */
    async fetchJson(url, options = {}) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

        const defaults = {
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token': csrfToken,
            },
            credentials: 'same-origin',
        };

        const config = { ...defaults, ...options };
        if (options.headers) {
            config.headers = { ...defaults.headers, ...options.headers };
        }

        const response = await fetch(url, config);

        if (!response.ok) {
            const err = await response.json().catch(() => ({ error: 'Error del servidor' }));
            throw new Error(err.error || `HTTP ${response.status}`);
        }

        return response.json();
    },

    /**
     * POST de un formulario vía AJAX.
     *
     * @param {string}      url
     * @param {FormData}    formData
     * @returns {Promise<any>}
     */
    async postForm(url, formData) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token': csrfToken,
            },
            credentials: 'same-origin',
            body: formData,
        });

        return response.json();
    },

    /**
     * Muestra una alerta Bootstrap temporal (auto-cierre).
     *
     * @param {string} message
     * @param {'success'|'danger'|'warning'|'info'} type
     * @param {number} duration  ms antes de auto-cerrar (0 = manual)
     */
    showAlert(message, type = 'info', duration = 4000) {
        const container = document.getElementById('flash-container');
        if (!container) return;

        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-dismissible fade show shadow-sm`;
        alert.setAttribute('role', 'alert');
        alert.innerHTML = `
            ${this.escapeHtml(message)}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        container.appendChild(alert);

        if (duration > 0) {
            setTimeout(() => {
                alert.classList.remove('show');
                setTimeout(() => alert.remove(), 300);
            }, duration);
        }
    },

    /**
     * Diálogo de confirmación antes de ejecutar una acción destructiva.
     * Retorna Promise<boolean>.
     *
     * @param {string} message
     * @returns {Promise<boolean>}
     */
    confirm(message) {
        return new Promise((resolve) => {
            // Usar modal Bootstrap si existe, si no usar confirm nativo
            const modal = document.getElementById('confirmModal');
            if (!modal) {
                resolve(window.confirm(message));
                return;
            }

            const bodyEl   = modal.querySelector('#confirmModalBody');
            const confirmBtn = modal.querySelector('#confirmModalBtn');
            if (bodyEl)    bodyEl.textContent = message;

            const bsModal = bootstrap.Modal.getOrCreateInstance(modal);
            bsModal.show();

            const handler = () => {
                bsModal.hide();
                resolve(true);
                confirmBtn.removeEventListener('click', handler);
            };

            modal.addEventListener('hidden.bs.modal', () => resolve(false), { once: true });
            confirmBtn.addEventListener('click', handler);
        });
    },

    /**
     * Escapa HTML para prevenir XSS en inserciones dinámicas.
     *
     * @param {string} str
     * @returns {string}
     */
    escapeHtml(str) {
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(String(str)));
        return div.innerHTML;
    },

    /**
     * Debounce: retrasa la ejecución de una función hasta que
     * el usuario pare de llamarla.
     *
     * @param {Function} fn
     * @param {number}   delay ms
     * @returns {Function}
     */
    debounce(fn, delay = 350) {
        let timer;
        return (...args) => {
            clearTimeout(timer);
            timer = setTimeout(() => fn(...args), delay);
        };
    },

    /**
     * Formatea un número como precio.
     */
    formatMoney(amount, currency = 'MXN') {
        return '$' + parseFloat(amount).toLocaleString('es-MX', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        }) + ' ' + currency;
    },
};

// ---------------------------------------------------------
// BÚSQUEDA EN TIEMPO REAL
// ---------------------------------------------------------
class LiveSearch {
    /**
     * @param {HTMLInputElement} input     Campo de búsqueda
     * @param {string}           endpoint  URL de la API (ej. /api/anuncios?q=)
     * @param {HTMLElement}      container Donde renderizar resultados
     * @param {Function}         renderer  fn(items) => HTML string
     */
    constructor(input, endpoint, container, renderer) {
        this.input     = input;
        this.endpoint  = endpoint;
        this.container = container;
        this.renderer  = renderer;
        this.minChars  = 2;
        this._bind();
    }

    _bind() {
        const search = App.debounce(async () => {
            const q = this.input.value.trim();
            if (q.length < this.minChars) {
                this.container.innerHTML = '';
                return;
            }

            this.container.innerHTML = `
                <div class="text-center py-3">
                    <div class="spinner-border spinner-border-sm text-primary"></div>
                </div>`;

            try {
                const data = await App.fetchJson(
                    this.endpoint + encodeURIComponent(q)
                );
                this.container.innerHTML = this.renderer(data.items || data);
            } catch (err) {
                this.container.innerHTML = `<p class="text-danger p-2">Error al buscar.</p>`;
            }
        }, 400);

        this.input.addEventListener('input', search);

        // Cerrar al hacer clic fuera
        document.addEventListener('click', (e) => {
            if (!this.container.contains(e.target) && e.target !== this.input) {
                this.container.innerHTML = '';
            }
        });
    }
}

// ---------------------------------------------------------
// VISTA PREVIA DE IMAGEN AL SELECCIONAR ARCHIVO
// ---------------------------------------------------------
function initImagePreview(inputSelector, previewSelector) {
    const input   = document.querySelector(inputSelector);
    const preview = document.querySelector(previewSelector);
    if (!input || !preview) return;

    input.addEventListener('change', () => {
        const file = input.files[0];
        if (!file) {
            preview.src = '';
            preview.closest('.preview-container')?.classList.add('d-none');
            return;
        }

        // Validar tipo en frontend (la validación real es en backend)
        if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
            App.showAlert('Solo se permiten imágenes JPG, PNG o WEBP.', 'warning');
            input.value = '';
            return;
        }

        // Validar tamaño: max 5 MB
        if (file.size > 5 * 1024 * 1024) {
            App.showAlert('La imagen no debe superar 5 MB.', 'warning');
            input.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = (e) => {
            preview.src = e.target.result;
            preview.closest('.preview-container')?.classList.remove('d-none');
        };
        reader.readAsDataURL(file);
    });
}

// ---------------------------------------------------------
// CONFIRMACIÓN ANTES DE ELIMINAR (formularios con data-confirm)
// ---------------------------------------------------------
document.addEventListener('DOMContentLoaded', () => {

    // Botones/formularios con data-confirm
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', async (e) => {
            e.preventDefault();
            const message = el.dataset.confirm || '¿Estás seguro?';
            const ok      = await App.confirm(message);
            if (!ok) return;

            // Si es un botón dentro de un form, submittear ese form
            const form = el.closest('form') || document.getElementById(el.dataset.form);
            if (form) {
                form.submit();
            } else if (el.href) {
                window.location.href = el.href;
            }
        });
    });

    // (Handler de toasts está en partials/toasts.php para funcionar en vistas standalone)

    // Upload forms — XHR con barra de progreso para multipart/form-data pesado
    (function(){
        function ensureOverlay() {
            var o = document.getElementById('upload-overlay');
            if (o) return o;
            o = document.createElement('div');
            o.id = 'upload-overlay';
            o.setAttribute('aria-live', 'assertive');
            o.innerHTML =
                '<div class="upload-modal">' +
                '  <div class="upload-modal__icon"><i class="bi bi-cloud-arrow-up-fill"></i></div>' +
                '  <div class="upload-modal__title">Subiendo archivos…</div>' +
                '  <div class="upload-modal__sub">No cierres ni recargues la página.</div>' +
                '  <div class="upload-modal__bar"><div class="upload-modal__bar-fill"></div></div>' +
                '  <div class="upload-modal__pct">0%</div>' +
                '  <div class="upload-modal__hint">Los archivos grandes pueden tardar unos segundos.</div>' +
                '</div>';
            document.body.appendChild(o);
            return o;
        }
        document.querySelectorAll('form[data-upload-form]').forEach(function(form){
            form.addEventListener('submit', function(ev){
                // Si otro handler (ej. validation.js) ya bloqueó el submit,
                // NO enviar por XHR — el formulario es inválido.
                if (ev.defaultPrevented) return;

                // Solo interceptar si hay archivos (foto o video) seleccionados
                var files = form.querySelectorAll('input[type="file"]');
                var hasFile = false;
                files.forEach(function(f){ if (f.files && f.files.length) hasFile = true; });
                if (!hasFile) return; // dejar submit normal

                ev.preventDefault();

                var overlay = ensureOverlay();
                var bar = overlay.querySelector('.upload-modal__bar-fill');
                var pct = overlay.querySelector('.upload-modal__pct');
                overlay.classList.add('is-on');

                // Deshabilitar botones submit
                form.querySelectorAll('button[type="submit"]').forEach(function(b){ b.disabled = true; });

                var xhr = new XMLHttpRequest();
                xhr.upload.addEventListener('progress', function(e){
                    if (e.lengthComputable) {
                        var p = Math.round(e.loaded / e.total * 100);
                        bar.style.width = p + '%';
                        pct.textContent = p + '%';
                        if (p >= 100) {
                            overlay.querySelector('.upload-modal__sub').textContent = 'Procesando en el servidor…';
                        }
                    }
                });
                xhr.addEventListener('load', function(){
                    // Navegar a la URL final (sigue redirects)
                    if (xhr.status >= 200 && xhr.status < 400) {
                        window.location.href = xhr.responseURL || form.action;
                    } else {
                        overlay.classList.remove('is-on');
                        form.querySelectorAll('button[type="submit"]').forEach(function(b){ b.disabled = false; });
                        alert('Error al subir (HTTP ' + xhr.status + '). Intenta de nuevo.');
                    }
                });
                xhr.addEventListener('error', function(){
                    overlay.classList.remove('is-on');
                    form.querySelectorAll('button[type="submit"]').forEach(function(b){ b.disabled = false; });
                    alert('Error de red al subir. Revisa tu conexión e intenta de nuevo.');
                });
                xhr.addEventListener('abort', function(){
                    overlay.classList.remove('is-on');
                    form.querySelectorAll('button[type="submit"]').forEach(function(b){ b.disabled = false; });
                });

                xhr.open(form.method || 'POST', form.action);
                xhr.send(new FormData(form));
            });
        });
    })();

    // Rating stars (botones clickables con estado y hover visual)
    document.querySelectorAll('.rating-stars').forEach(wrap => {
        const stars = wrap.querySelectorAll('.rating-star');
        const input = wrap.querySelector('input[type="hidden"][name="calificacion"]');
        const paint = (n, cls) => stars.forEach(s => {
            s.classList.toggle(cls, parseInt(s.dataset.v, 10) <= n);
        });
        const selected = () => parseInt(wrap.dataset.selected || '0', 10);

        stars.forEach(s => {
            s.addEventListener('mouseenter', () => {
                stars.forEach(x => x.classList.remove('is-hover'));
                paint(parseInt(s.dataset.v, 10), 'is-hover');
            });
            s.addEventListener('click', () => {
                const v = parseInt(s.dataset.v, 10);
                wrap.dataset.selected = v;
                if (input) input.value = v;
                stars.forEach(x => x.classList.remove('is-active'));
                paint(v, 'is-active');
            });
        });
        wrap.addEventListener('mouseleave', () => {
            stars.forEach(x => x.classList.remove('is-hover'));
            paint(selected(), 'is-active');
        });
    });

    // Inicializar tooltips de Bootstrap
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
        new bootstrap.Tooltip(el);
    });

    // Inicializar popovers de Bootstrap
    document.querySelectorAll('[data-bs-toggle="popover"]').forEach(el => {
        new bootstrap.Popover(el);
    });

    // Imagen preview global
    initImagePreview('#imagen_principal', '#img-preview');
});
