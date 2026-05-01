# Guía de diseño e interacción — PlacerSelecto

Documento de referencia para replicar el look-and-feel y los patrones de interacción de esta app en otros proyectos. Incluye tokens de diseño, librerías, componentes, animaciones y los trucos de UX que funcionan bien.

> **Premisa:** todo es **self-hosted** (Bootstrap, íconos, fuentes, Leaflet, Quill). Nada de CDN — evita fallas por CSP estrictos o privacidad.

---

## 1. Stack visual (librerías)

| Librería | Uso | Notas |
|---|---|---|
| **Bootstrap 5.3** | Grid, utilities, componentes base (botones, cards, navbar, dropdowns) | Solo CSS + JS mínimo; se sobrescriben los componentes con tema propio en `app.css` |
| **Bootstrap Icons** | Íconos (heart, person, camera, check-lg, etc.) | `<i class="bi bi-nombre"></i>` |
| **Inter** (self-hosted) | Tipografía principal | Fallback: Poppins, system-ui, Segoe UI, Roboto |
| **Leaflet** | Mapas con OSM + Nominatim | Sin API key; tiles opcionalmente proxied por `/tile/{z}/{x}/{y}` para privacidad |
| **Quill snow** | Editor rich-text para descripciones | Toolbar mínima + panel de emojis custom |

Estructura:
```
public/assets/
├── vendor/
│   ├── bootstrap/bootstrap.min.css + bootstrap.bundle.min.js
│   ├── bootstrap-icons/bootstrap-icons.min.css
│   ├── inter/inter.css (+ fuentes locales)
│   ├── leaflet/leaflet.css + leaflet.js + images/
│   └── quill/quill.min.js + quill.snow.css
├── css/app.css        ← tema y componentes propios (~2800 líneas)
└── js/*.js             ← módulos IIFE sin dependencias entre sí
```

---

## 2. Design tokens (CSS variables)

```css
:root {
    /* Paleta — tema claro con rosa acento */
    --color-primary:      #FF2D75;   /* rosa CTA */
    --color-primary-d:    #e6245f;   /* hover intenso */
    --color-primary-l:    #FF7FA8;   /* hover suave, bordes */

    /* Fondos */
    --color-bg:           #FFFFFF;
    --color-bg-alt:       #F5F5F5;   /* secciones alternas / inputs */
    --color-bg-card:      #FFFFFF;

    /* Textos */
    --color-text:         #1A1A1A;   /* negro suave, no #000 */
    --color-text-muted:   #666666;
    --color-text-light:   #9AA0A6;   /* placeholders */

    /* Bordes y semánticos */
    --color-border:       #E5E5E5;
    --color-success:      #10B981;
    --color-warning:      #F59E0B;
    --color-danger:       #EF4444;
    --color-info:         #3B82F6;

    /* Geometría (radios 8–12 px — nada de cuadrado, nada de píldora) */
    --radius-sm: 8px;
    --radius-md: 10px;
    --radius-lg: 12px;

    /* Sombras suaves con poca opacidad + una "pink" para CTAs destacados */
    --shadow-sm:   0 1px 2px  rgba(0,0,0,.04);
    --shadow-md:   0 2px 8px  rgba(0,0,0,.06);
    --shadow-lg:   0 6px 20px rgba(0,0,0,.08);
    --shadow-pink: 0 4px 16px rgba(255,45,117,.22);

    --transition: .2s ease;
    --font-base:  'Inter', 'Poppins', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
}
```

**Regla de oro:** nunca colores hard-coded en markup o JS — siempre `var(--color-xxx)`. Permite re-temar cambiando solo `:root`.

### Tipografía
- **Base 15px, line-height 1.6** en body
- Encabezados: `font-weight: 700`, `line-height: 1.25`, `letter-spacing: -0.01em` (más tight)
- Anti-aliasing activado: `-webkit-font-smoothing: antialiased`
- `.text-gradient` con `linear-gradient(135deg, primary, primary-l)` + `background-clip: text` para títulos destacados

---

## 3. Loader global (overlay en cada navegación)

Spinner fullscreen con blur, se dispara **automáticamente** al navegar entre páginas o enviar un form.

**Markup** (`partials/global-loader.php`):
```html
<div id="global-loader" aria-hidden="true">
    <div class="global-loader__box">
        <div class="global-loader__spinner"></div>
        <div class="global-loader__text">Cargando…</div>
    </div>
</div>
```

**CSS:**
```css
#global-loader {
    position: fixed; inset: 0;
    background: rgba(255,255,255,.85);
    backdrop-filter: blur(2px);
    display: flex; align-items: center; justify-content: center;
    z-index: 10500;
    opacity: 0; visibility: hidden;
    transition: opacity .2s, visibility .2s;
    pointer-events: none;
}
#global-loader.is-visible { opacity: 1; visibility: visible; pointer-events: auto; }
.global-loader__box {
    background: #fff; border-radius: 12px;
    padding: 1.5rem 2rem;
    box-shadow: 0 10px 40px rgba(0,0,0,.15);
    display: flex; flex-direction: column; align-items: center; gap: .85rem;
}
.global-loader__spinner {
    width: 48px; height: 48px;
    border: 4px solid rgba(255,45,117,.15);
    border-top-color: var(--color-primary);
    border-radius: 50%;
    animation: placer-spin .8s linear infinite;
}
@keyframes placer-spin { to { transform: rotate(360deg); } }
```

**JS (`loader.js`):** intercepta clicks de `<a>` y submits, muestra el loader. Opt-out con `data-no-loader`. Filtra anclas, `mailto:`, `tel:`, `_blank`, ctrl/meta+click. Se oculta en `pageshow` (volver desde bfcache). API pública: `window.PlacerLoader.show('Texto...')` / `.hide()`.

Clave: `setTimeout(() => { if (!ev.defaultPrevented) show() }, 0)` — si otro handler bloquea, no se muestra el loader innecesariamente.

---

## 4. Toasts (flash messages con progress bar)

Diseño moderno: tarjeta blanca, barra de acento lateral coloreada, ícono circular, auto-dismiss con barra de progreso que se encoge, **pausa en hover**, cerrable manualmente.

**Variantes** via `.toast-success | .toast-danger | .toast-warning | .toast-info` — cada una define `--toast-accent` y `--toast-accent-soft`.

**Durations:** success/info 5s, warning 7s, danger 9s.

**Markup:**
```html
<div id="toast-container" aria-live="polite">
  <div class="toast-item toast-success" role="alert">
    <div class="toast-icon"><i class="bi bi-check-circle-fill"></i></div>
    <div class="toast-body">
      <div class="toast-title">Éxito</div>
      <div class="toast-message">Operación completada.</div>
    </div>
    <button type="button" class="toast-close"><i class="bi bi-x-lg"></i></button>
    <div class="toast-progress"></div>
  </div>
</div>
```

**CSS clave:**
```css
#toast-container {
    position: fixed; top: 84px; right: 1rem;
    z-index: 9998;
    width: min(380px, calc(100vw - 2rem));
    display: flex; flex-direction: column; gap: .6rem;
    pointer-events: none;
}
.toast-item {
    pointer-events: auto;
    display: grid; grid-template-columns: 42px minmax(0,1fr) auto; /* minmax(0,1fr) evita overflow en móvil */
    background: #fff; border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0,0,0,.08);
    animation: toast-in .35s cubic-bezier(.22,.68,0,1.2);
    position: relative; overflow: hidden;
}
.toast-item::before {
    content: ''; position: absolute; left: 0; top: 0; bottom: 0;
    width: 4px; background: var(--toast-accent);
}
.toast-progress {
    position: absolute; left: 0; right: 0; bottom: 0;
    height: 3px; background: var(--toast-accent); opacity: .55;
    transform: scaleX(1); transform-origin: left center;
}
@keyframes toast-in {
    from { opacity: 0; transform: translateX(40px) scale(.98); }
    to   { opacity: 1; transform: translateX(0) scale(1); }
}
```

**JS truco:** al pausar, lee `getComputedStyle(bar).transform` y lo vuelve a aplicar sin transition — así congela la barra visualmente. Al resumir, aplica nueva transition con el tiempo restante.

**Responsive:** bajo 768px `left: .5rem; right: .5rem` full-width en vez de 380px fijo.

---

## 5. Cookie banner (tarjeta flotante)

Pop-up en esquina inferior izquierda (no banner horizontal tradicional). Se almacena aceptación en `localStorage`. Entrada con slide-up + scale.

```css
.cookie-banner {
    position: fixed; left: 1.25rem; bottom: 1.25rem;
    z-index: 10200; max-width: 340px; width: calc(100vw - 2.5rem);
    opacity: 0; visibility: hidden;
    transform: translateY(28px) scale(.96);
    transition: opacity .35s, transform .4s cubic-bezier(.22,1,.36,1), visibility .35s;
}
.cookie-banner.is-visible { opacity: 1; transform: translateY(0) scale(1); }
.cookie-banner__card {
    background: #fff; border-radius: 16px;
    padding: 1.1rem 1.25rem;
    box-shadow: 0 12px 40px rgba(255,45,117,.18), 0 4px 14px rgba(0,0,0,.08);
    border: 1px solid rgba(255,45,117,.15);
}
.cookie-banner__card::before {  /* glow sutil rosa en esquina */
    content: ''; position: absolute; inset: 0;
    background: linear-gradient(135deg, rgba(255,45,117,.04) 0%, transparent 60%);
}
.cookie-banner__icon {  /* emoji 🍪 con wiggle */
    font-size: 2.2rem;
    animation: cookie-wiggle 2.4s ease-in-out .6s;
}
```

JS muestra con delay de 700ms (evita salto al cargar), guarda `localStorage.setItem('ps_cookie_consent', '1')` al aceptar.

---

## 6. Age gate (modal bloqueante sobre página desenfocada)

La página de fondo se renderiza **y se blurrea** con CSS; encima un modal centrado. No redirige — deja la página cargada para que sea visible tras consentir (mejor SEO y UX que redirect).

```css
#page-wrapper.age-gate-active {
    filter: blur(6px) brightness(.92);
    pointer-events: none;
    user-select: none;
    overflow: hidden;
    max-height: 100vh;
}
.age-gate-overlay {
    position: fixed; inset: 0; z-index: 9999;
    background: rgba(26,26,26,.35);
    backdrop-filter: blur(3px);
    animation: ageFadeIn .35s ease;
}
@keyframes ageFadeIn { from { opacity: 0; transform: scale(.97); } to { opacity: 1; transform: scale(1); } }
@keyframes ageSlideUp { from { opacity: 0; transform: translateY(24px); } to { opacity: 1; transform: translateY(0); } }
```

El modal entra con `ageSlideUp` (cubic-bezier overshoot para sensación de "pop"). El icono: círculo 80x80 con bg rosa al 10%.

---

## 7. Lightbox de galería

Componente reutilizable para ver imágenes/videos en pantalla completa con navegación. Escucha click en `[data-lightbox-open="idx"]`.

**Markup:**
```html
<div id="lightbox" class="lightbox">
    <button class="lightbox__close" data-lightbox="close">&times;</button>
    <button class="lightbox__nav lightbox__prev" data-lightbox-nav="-1">&#8249;</button>
    <img id="lightbox-img" class="lightbox__img">
    <button class="lightbox__nav lightbox__next" data-lightbox-nav="1">&#8250;</button>
    <div class="lightbox__counter" id="lightbox-counter"></div>
</div>
<script id="lightbox-data" type="application/json">["url1","url2",...]</script>
```

**JS crítico — reubicar a body al init:**
```js
if (lightboxEl.parentElement !== document.body) {
    document.body.appendChild(lightboxEl);
}
```
⚠️ Cualquier ancestro con `transform`, `filter`, `backdrop-filter`, `will-change` crea un **nuevo containing block** y atrapa a `position: fixed`. Es la causa #1 de "el modal no cubre toda la pantalla". Moverlo a `<body>` elimina el problema.

**CSS:**
```css
.lightbox {
    display: flex; position: fixed; inset: 0;
    background: rgba(0,0,0,.92);
    align-items: center; justify-content: center;
    padding: 1rem;
    visibility: hidden; opacity: 0; pointer-events: none;
    transition: opacity .18s, visibility .18s;
}
.lightbox.is-open { visibility: visible; opacity: 1; pointer-events: auto; z-index: 10400; }
body.lightbox-open { overflow: hidden; }
```

**Selector de triggers inteligente** — ignora clicks en elementos interactivos:
```js
el.addEventListener('click', function(ev){
    const interactive = ev.target.closest('button, a, form, input, label, select, [data-stop-propagation]');
    if (interactive && el.contains(interactive)) return;
    ev.preventDefault(); ev.stopPropagation();
    open(parseInt(el.dataset.lightboxOpen, 10));
});
```

Teclado: Escape cierra, ←/→ navega.

---

## 8. Upload modal (progress bar XHR)

Al subir archivos grandes, se intercepta el submit y se sube por `XMLHttpRequest` con barra de progreso. `<form data-upload-form>` activa el comportamiento.

```css
.upload-modal__icon {
    width: 64px; height: 64px; border-radius: 50%;
    background: rgba(255,45,117,.12); color: var(--color-primary);
    display: flex; align-items: center; justify-content: center;
    font-size: 2rem; margin: 0 auto 1rem;
    animation: uploadPulse 1.5s ease-in-out infinite;
}
@keyframes uploadPulse { 0%,100% { transform: scale(1) } 50% { transform: scale(1.08) } }

.upload-modal__bar { height: 10px; background: var(--color-bg-alt); border-radius: 10px; overflow: hidden; }
.upload-modal__bar-fill {
    height: 100%; width: 0%;
    background: linear-gradient(90deg, var(--color-primary), var(--color-primary-l));
    transition: width .2s ease;
}
```

**JS clave:**
```js
form.addEventListener('submit', function(ev){
    if (ev.defaultPrevented) return;  // ⚠️ respetar validators previos

    const files = form.querySelectorAll('input[type="file"]');
    let hasFile = false;
    files.forEach(f => { if (f.files && f.files.length) hasFile = true; });
    if (!hasFile) return;

    ev.preventDefault();
    const xhr = new XMLHttpRequest();
    xhr.upload.addEventListener('progress', e => {
        if (e.lengthComputable) {
            const p = Math.round(e.loaded / e.total * 100);
            bar.style.width = p + '%';
            if (p >= 100) sub.textContent = 'Procesando en el servidor…';
        }
    });
    xhr.addEventListener('load', () => {
        if (xhr.status >= 200 && xhr.status < 400) {
            window.location.href = xhr.responseURL || form.action;
        }
    });
    xhr.open(form.method, form.action);
    xhr.send(new FormData(form));
});
```

El `if (ev.defaultPrevented) return` es **crítico** — si un validator bloqueó el submit, no subas el form inválido.

---

## 9. Foto uploader con drag-drop + preview grid

Widget reutilizable: zona de drag-drop, preview de miniaturas con eliminar, elegir principal (estrella).

Patrón clave: usa `DataTransfer` para reconstruir `input.files` sin resetear después de `this.value = ''`:
```js
function syncInput() {
    const dt = new DataTransfer();
    pendingFiles.forEach(f => dt.items.add(f.file));
    input.files = dt.files;  // ← así FormData recoge los archivos correctamente
}
```

**CSS de dropzone:**
```css
.foto-dropzone {
    border: 2px dashed var(--color-border);
    border-radius: var(--radius-md);
    padding: 1.5rem;
    text-align: center;
    transition: border-color var(--transition), background var(--transition);
    cursor: pointer;
}
.foto-dropzone:hover, .foto-dropzone--over {
    border-color: var(--color-primary);
    background: rgba(255,45,117,.03);
}
.foto-thumb {
    position: relative;
    width: 90px; height: 90px;
    border-radius: var(--radius-sm);
    overflow: hidden; border: 1.5px solid var(--color-border);
}
.foto-thumb__del {  /* botón X en esquina */
    position: absolute; top: 4px; right: 4px;
    width: 22px; height: 22px; border-radius: 50%;
    background: rgba(0,0,0,.65); color: #fff;
}
.foto-thumb--principal { border-color: var(--color-primary); box-shadow: 0 0 0 2px rgba(255,45,117,.15); }
```

`URL.createObjectURL(file)` para previews locales — acuérdate de `URL.revokeObjectURL()` al eliminar.

---

## 10. Grabación de video fullscreen (cámara)

Para grabación importante (verificación de identidad), al presionar "Grabar" el video ocupa toda la pantalla durante los N segundos y vuelve al tamaño normal al terminar.

**Flujo:**
1. `getUserMedia({ video: { facingMode: { ideal: 'user' }, width:1280, height:720 } })`
2. Al iniciar grabación: **mover el contenedor a `<body>`** (escapa cualquier ancestro con transform) + agregar clase `.is-recording-fullscreen`
3. `MediaRecorder` con `video/webm;codecs=vp9` (fallback: `video/webm` → `video/mp4`)
4. Countdown visible top-right, indicador "● GRABANDO" top-left
5. Al terminar: quitar clase, restaurar al parent original, mostrar pantalla de revisión con `<video controls>`

```css
#videoWrap.is-recording-fullscreen {
    position: fixed !important;   /* ⚠️ !important porque los estilos inline
                                     del contenedor (aspect-ratio, position:relative)
                                     ganan en especificidad sobre clases */
    inset: 0 !important;
    z-index: 10500 !important;
    width: 100vw !important;
    height: 100vh !important;
    height: 100dvh !important;    /* dvh en móvil para contar con address bar */
    aspect-ratio: auto !important;
    border-radius: 0 !important;
}
#videoWrap.is-recording-fullscreen video {
    width: 100% !important; height: 100% !important;
    object-fit: contain !important;
}
#videoWrap.is-recording-fullscreen #countdown {
    top: max(20px, env(safe-area-inset-top, 20px));
    right: max(20px, env(safe-area-inset-right, 20px));
    font-size: 2rem; padding: .4rem 1rem;
}
body.is-recording-lock { overflow: hidden; }
```

Usar **`100dvh`** (dynamic viewport height) + fallback `100vh` para cubrir bien en iOS/Android con barras dinámicas.

---

## 11. Notificación bell (polling inteligente)

Campanita con badge rojo que se actualiza en tiempo real. Optimizaciones:
- **Page Visibility API:** pausa polling cuando la pestaña está oculta
- **ETag + 304:** solo recibe JSON si cambió algo
- **Backoff exponencial:** cada 304 consecutivo sube el intervalo (30s base → 90s máx, +15s por cada 304)
- **Reset al ver cambios:** vuelve a 30s si llega data nueva
- **Shake animation:** al recibir nueva notificación, la campana vibra

```css
@keyframes notif-shake {
    0%, 100% { transform: rotate(0); }
    15% { transform: rotate(-12deg); }
    30% { transform: rotate(10deg); }
    45% { transform: rotate(-8deg); }
    60% { transform: rotate(6deg); }
    75% { transform: rotate(-4deg); }
    90% { transform: rotate(2deg); }
}
.notif-bell.is-pulsing { animation: notif-shake .6s ease; }
```

Endpoint `/api/notificaciones/pendientes` devuelve `{ hash, count, items }` — el hash permite comparar sin re-renderizar si no cambió.

---

## 12. Mapas con Leaflet (solo lectura y editable)

**Lectura (solo mostrar zona):**
```js
const m = L.map('mapa-show', {
    zoomControl: false, dragging: false,
    scrollWheelZoom: false, doubleClickZoom: false,
    touchZoom: false, keyboard: false, attributionControl: false
}).setView([lat, lng], 12);

L.tileLayer(APP_URL + '/tile/{z}/{x}/{y}.png').addTo(m);

// Círculo de radio
L.circle([lat, lng], {
    radius: radio * 1000,   // km → metros
    color: '#FF2D75',
    fillColor: '#FF2D75',
    fillOpacity: .2,
    weight: 2
}).addTo(m);

// Marker custom (rosa con borde blanco) via divIcon
const ic = L.divIcon({
    className: '',
    html: '<div style="width:16px;height:16px;background:#FF2D75;border:3px solid #fff;border-radius:50%;box-shadow:0 2px 6px rgba(0,0,0,.4)"></div>',
    iconSize: [16, 16], iconAnchor: [8, 8]
});
L.marker([lat, lng], { icon: ic }).addTo(m);

setTimeout(() => m.invalidateSize(), 200);  // corrige render si el card cargó oculto
```

**Editable (drag marker + slider de radio):**
- `L.marker(..., { draggable: true })` + `marker.on('dragend', ...)`
- Click en mapa: `mapa.on('click', e => setZona(e.latlng.lat, e.latlng.lng))`
- Slider HTML `<input type="range">` actualiza `circle.setRadius(km * 1000)` en vivo

Ruta `/tile/{z}/{x}/{y}.png` en el backend hace proxy a OSM (o Carto) — evita dependencia directa del cliente en tiles externos y cumple con CSP estricto.

---

## 13. Rich text con Quill (snow theme + emojis)

```js
const AlignStyle = Quill.import("attributors/style/align");
Quill.register(AlignStyle, true);

const quill = new Quill("#descripcion-editor", {
    theme: "snow",
    placeholder: "Cuéntanos…",
    modules: {
        toolbar: {
            container: [
                ["bold", "italic", "underline", "strike"],
                [{ align: "" }, { align: "center" }, { align: "right" }, { align: "justify" }],
                [{ list: "ordered" }, { list: "bullet" }],
                ["clean"],
                ["emoji"]      // ← botón custom
            ],
            handlers: { emoji: function(){ toggleEmojiPanel(this.quill); } }
        }
    }
});

// Sincronizar a <input hidden> para que el form lo envíe
quill.on('text-change', () => {
    hiddenInput.value = quill.getText().trim() === '' ? '' : quill.root.innerHTML;
});
```

Panel de emojis custom: grid de botones al final del editor, se abre/cierra en click del botón "😊". Lista de emojis relevante al dominio (~50 items).

**iOS Safari fix:** a veces no dispara `text-change` al blur — agregar listener explícito:
```js
qlEditor.addEventListener('blur', syncHidden);
qlEditor.addEventListener('input', syncHidden);
```

---

## 14. Rating stars (click + hover preview)

```html
<div class="rating-stars" data-selected="0">
    <input type="hidden" name="calificacion" value="0">
    <button class="rating-star" data-v="1"><i class="bi bi-star"></i></button>
    <button class="rating-star" data-v="2"><i class="bi bi-star"></i></button>
    ...5 stars
</div>
```

Hover muestra relleno dorado temporal; click fija la selección. Ver `app.js` sección "Rating stars".

---

## 15. Verification banner (3 estados)

```css
.verification-banner {
    display: flex; align-items: start; gap: .75rem;
    padding: .75rem 1rem; border-radius: var(--radius-sm);
    font-size: .88rem;
}
.verification-banner.pendiente { background: #FFFBEB; color: #92400E; border-left: 3px solid var(--color-warning); }
.verification-banner.aprobado  { background: #ECFDF5; color: #065F46; border-left: 3px solid var(--color-success); }
.verification-banner.rechazado { background: #FEF2F2; color: #991B1B; border-left: 3px solid var(--color-danger); }
```

Patrón de "soft semantic colors" — bg y texto del mismo matiz para evitar ruido visual.

---

## 16. List item "perfil-row" (card horizontal)

Listas en vez de cards 2×N. Item tiene: foto (120px), body con nombre+meta+descripción, flecha deslizante en hover.

```css
.perfil-row {
    display: grid; grid-template-columns: 120px 1fr auto;
    gap: 1rem; padding: 1rem;
    background: var(--color-bg-card);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-md);
    transition: transform var(--transition), box-shadow var(--transition);
    text-decoration: none; color: var(--color-text);
}
.perfil-row:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}
.perfil-row__photo img { transition: transform .25s; }
.perfil-row:hover .perfil-row__photo img { transform: scale(1.04); }

/* Variantes destacadas */
.perfil-row--top        { border-color: var(--color-primary); box-shadow: 0 0 0 2px rgba(255,45,117,.1); }
.perfil-row--resaltado  { border-left: 3px solid var(--color-primary); }
```

---

## 17. Storage gallery + lightbox (admin)

Grid CSS con `auto-fill, minmax(180px, 1fr)`. Miniaturas de imágenes Y videos (videos con overlay de play).

```css
.storage-gallery {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 1rem;
}
.storage-gallery__media {
    position: relative; aspect-ratio: 1/1; background: #000; overflow: hidden;
}
.storage-gallery__media img, .storage-gallery__media video {
    width: 100%; height: 100%; object-fit: cover;
}
```

Lightbox dedicado (`storage-lb`) que soporta imagen Y video — detecta por `tipo` en el JSON de data.

---

## 18. Patrones de handlers data-attribute (CSP-safe)

Para proyectos con CSP estricto (sin `unsafe-inline`), evita `onclick=""` y centraliza handlers en `common.js`:

| Atributo | Comportamiento |
|---|---|
| `data-toggle-password="inpId"` | Alterna type entre password/text, cambia ícono |
| `data-number-only [data-number-max="6"]` | Permite solo dígitos, limita largo |
| `data-confirm-submit="mensaje"` | `confirm()` antes del submit |
| `data-confirm-click="mensaje"` | `confirm()` antes del click |
| `data-countdown-seconds="60" data-countdown-display="spanId"` | Disable hasta que termine countdown |
| `data-trigger-file="fileInputId"` | Click triggerea `fileInput.click()` (estiliza tu dropzone) |
| `data-auto-submit` | En select/input, submittea el form al cambiar |
| `data-toggle-display="targetId"` | Muestra/oculta elemento |
| `data-no-loader` | Exime del loader global |
| `data-stop-propagation` | El lightbox/parent handlers lo ignoran |
| `data-validate="required\|email\|minLen:8"` | Validación frontend (ver `validation.js`) |
| `data-validate-form` | Activa FormValidator en el form |
| `data-upload-form` | Activa submit XHR con modal de progreso |

Todos se registran una vez con delegation desde `document`. Nada inline, todo auto-detect.

---

## 19. Form validation client-side (sin librerías)

`validation.js` expone `FormValidator` que lee `data-validate="regla1|regla2:arg"` por campo:

```js
const RULES = {
    required:       val => val.trim() !== '',
    email:          val => /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/i.test(val),
    minLen:         (val, n) => val.length >= parseInt(n),
    maxLen:         (val, n) => val.length <= parseInt(n),
    phone:          val => /^[\d+\-\s()]{7,20}$/.test(val),
    strongPassword: val => val.length >= 8 && /[A-Z]/.test(val) && /[a-z]/.test(val) && /[0-9]/.test(val),
    noScript:       val => !/<script|javascript:|on\w+=/i.test(val),
};
```

- Valida on blur + on submit
- `.is-invalid` / `.is-valid` en el input (Bootstrap)
- Error en `.invalid-feedback` que se inserta tras el campo
- Si falla, hace scroll suave al primer error y `focus()`

`PasswordStrength` componente separado con barra progresiva (0-4): Muy débil → Débil → Aceptable → Fuerte.

---

## 20. Cascada estado → municipio (select dependiente)

Patrón reusable — `perfil-create.js`, `perfil-edit.js`, `perfil-search.js`, `home.js`, `ads-index.js` lo implementan:

```js
fetch(BASE_URL + '/api/municipios/' + idEstado)
    .then(r => r.json())
    .then(data => {
        selMunicipio.innerHTML = '<option value="">— Selecciona municipio —</option>';
        if (data.success && data.municipios.length) {
            data.municipios.forEach(m => {
                const opt = new Option(m.nombre, m.id);
                if (seleccionarId && parseInt(m.id) === seleccionarId) opt.selected = true;
                selMunicipio.appendChild(opt);
            });
            selMunicipio.disabled = false;
        }
    });
```

Al inicio el municipio está `disabled` con placeholder "— Primero selecciona estado —". Si viene `oldMunicipio` (repopular tras validation error), auto-carga y selecciona.

---

## 21. Animaciones / keyframes catálogo

```css
/* Spinners */
@keyframes placer-spin { to { transform: rotate(360deg); } }

/* Toast entrada */
@keyframes toast-in {
    from { opacity: 0; transform: translateX(40px) scale(.98); }
    to   { opacity: 1; transform: translateX(0) scale(1); }
}

/* Age gate */
@keyframes ageFadeIn  { from { opacity: 0; transform: scale(.97); } to { opacity: 1; transform: scale(1); } }
@keyframes ageSlideUp { from { opacity: 0; transform: translateY(24px); } to { opacity: 1; transform: translateY(0); } }

/* Upload icon pulse */
@keyframes uploadPulse { 0%,100% { transform: scale(1); } 50% { transform: scale(1.08); } }

/* Campana shake al recibir notif */
@keyframes notif-shake { 0%,100% { transform: rotate(0); } 15% { transform: rotate(-12deg); } ... }

/* Cookie emoji wiggle */
@keyframes cookie-wiggle { 0%,100% { transform: rotate(0); } 25% { transform: rotate(-8deg); } ... }

/* Status banner pulse (acción requerida) */
@keyframes statusPulse { 0%,100% { box-shadow: 0 0 0 0 rgba(255,45,117,.5); } 50% { box-shadow: 0 0 0 10px rgba(255,45,117,0); } }

/* REC indicator blink */
@keyframes blink { 0%,100% { opacity: 1; } 50% { opacity: .2; } }
```

Easings preferidos: `ease` (default), `cubic-bezier(.22,.68,0,1.2)` (overshoot suave para pops/modales), `cubic-bezier(.22,1,.36,1)` (ease-out más natural que lineal).

---

## 22. Patrones de interacción recurrentes

### a) Botón con spinner durante acción
```js
btn.disabled = true;
btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Procesando…';
```

### b) Confirmación destructiva
Usar `data-confirm-click="¿Eliminar permanentemente?"` en vez de modales — menos fricción para admin, más explícito.

### c) Propagar filtros al detail
Al abrir un item desde un listado filtrado, pasar los filtros como query string:
```php
$qsNav = http_build_query(array_filter(['q'=>$q, 'id_estado'=>$idEstado, 'nav'=>'1']));
```
En el detalle se calcula "prev/next" basado en la lista filtrada original.

### d) Navbar con avatar dropdown
- Click abre Bootstrap dropdown
- Primer item: email en texto muted (read-only)
- Luego items de navegación por rol
- Separador `<hr>`
- Logout como form POST (CSRF) en vez de link GET

### e) Paginación compacta
First · Prev · ventana de 5 centrada en actual · Next · Last. Con contador "mostrando X–Y de Z".

### f) Badges de estado (enum)
```html
<span class="badge-estado badge-pendiente">Pendiente</span>
<span class="badge-estado badge-publicado">Publicado</span>
```
Bg suave + texto saturado + padding consistente.

### g) Skeletons / placeholders — **no se usan**
En su lugar, el loader global cubre navegación. Los listados renderizan directo (no hay estados intermedios "cargando…" dentro de la página — SSR completo).

### h) Dropzones con pseudo-estados
`.foto-dropzone` + `.foto-dropzone--over` (drag hover) + `.foto-dropzone--full` (límite alcanzado).

---

## 23. Accesibilidad / detalles finos

- **`aria-hidden`** en overlays cerrados; se alterna con JS al abrir
- **`role="alert"`** en toasts
- **`aria-live="polite"`** en contenedor de toasts
- **`role="dialog"`** + `aria-labelledby` en modales
- **Keyboard nav** en lightbox (Escape/←/→)
- **`focus-visible`** — outline rosa al navegar por tab, invisible en click de mouse
- **`prefers-reduced-motion`**: deshabilitar animaciones decorativas (no implementado en este proyecto, considerar)
- **Contrast check:** `#666` sobre `#fff` cumple AA para 15px; `#9AA0A6` solo para placeholders

---

## 24. Responsive breakpoints pragmáticos

Se usan los de Bootstrap (`sm=576`, `md=768`, `lg=992`, `xl=1200`) pero con tweaks:
- **Toasts full-width ≤768** (no 576) — mejor en tablets portrait
- **Navbar brand pequeño ≤575** con ellipsis para marcas largas
- **Cards con hover transform** solo en no-touch — podría añadirse `@media (hover: hover)` pero en la práctica no molesta en touch

Mobile-first: estilos base para móvil, `@media (min-width: X)` para tablet/desktop.

---

## 25. Performance patterns

- **Imágenes con variantes:** al subir se generan `_thumb` (300px), `_medium` (800px), `.webp` de cada una. Se sirven según `?size=` + `Accept: image/webp`. Cache 1 año `immutable` (tokens únicos por archivo). Reduce transferencia ~70%.
- **ETag + 304** en endpoints de polling (notificaciones) y en imágenes
- **`loading="lazy"`** en imágenes fuera del fold; `loading="eager"` en hero/admin views
- **`defer`** en todos los `<script>` de módulos
- **`font-display: swap`** en Inter (self-hosted)
- **No animations al primer render** — la página se muestra estática, animaciones solo al interactuar

---

## 26. Checklist para clonar el tema a otro proyecto

1. Copiar `public/assets/vendor/` completo (Bootstrap, icons, fuentes, Leaflet, Quill)
2. Copiar `public/assets/css/app.css` — renombrar/reemplazar los colores del token si cambia la marca
3. Copiar los JS que necesites — son IIFEs sin dependencias entre sí (excepto `verificar-camara.js` que usa `PlacerLoader` opcionalmente)
4. Copiar los partials HTML (`layout.php`, `navbar.php`, `toasts.php`, `global-loader.php`, `cookie-banner.php`, `foto-uploader.php`)
5. Adaptar el router/controller para inyectar variables como `$currentUser`, `$csrfField`, `$flashMessages`
6. Sustituir endpoints `/api/municipios/*` por los de tu backend
7. Proxy `/tile/{z}/{x}/{y}` si quieres evitar dependencia directa del cliente en OSM

---

## 27. Reglas no escritas que hacen que "se vea caro"

1. **Sombras sutiles** (no más de `rgba(0,0,0,.1)` de opacidad). Nunca `box-shadow` con `rgba(0,0,0,.5)` — se ve viejo.
2. **Bordes de 1px** siempre — ni 2px ni más, salvo estado activo/destacado
3. **Radios intermedios** (8–12px). Nada de `border-radius: 0` (brutalismo) ni `border-radius: 20px` (burbujas infantiles).
4. **Hover sutiles**: `translateY(-2px)` + sombra apenas más grande. Nunca escalar grande.
5. **Transiciones cortas** (0.15–0.25s). Sobre 0.4s parece lento.
6. **Texto muted al 40% del contraste** para meta info (metadatos, fechas, secundarios). No gris pálido.
7. **Íconos siempre con color accent** cuando son decorativos (`text-primary` en headers de cards, etc.) — da cohesión.
8. **Spacing en múltiplos de 4 o 8** (Bootstrap usa .25rem steps). No valores arbitrarios como `13px`.
9. **Letter-spacing negativo en encabezados** (-0.01em a -0.02em). Se ve más tight y premium.
10. **Gradientes sutiles solo para acentos** (botón CTA, barra de progreso, títulos "text-gradient"). Nunca fondos enteros en gradiente.

---

## 28. Lecciones aprendidas dolorosas (no repetir)

- **`.card:hover { transform }`** crea containing block → rompe modales `position: fixed` dentro del card → mover siempre overlays a `<body>` desde JS
- **Inline styles ganan en especificidad** — si sobrescribes con clase, usa `!important` o mueve el estilo base a CSS
- **XHR uploads** deben verificar `ev.defaultPrevented` o suben el form aunque otro validator lo haya bloqueado
- **Lazy-load + columnas CSS** = layout reflow constante = "parpadeo". Usar `aspect-ratio` o `loading="eager"` en admin views
- **`input.value = ''` borra files** — si reseteas, tienes que re-asignar con `DataTransfer`
- **iOS Safari + Quill** → text-change no dispara al blur, agregar `blur`/`input` listeners explícitos
- **iOS Safari + 100vh** → no cubre toda la pantalla (address bar). Usar `100dvh` con fallback
- **`disabled` en select** → el campo no se envía en el form. Si activas con JS, recuerda que no tenía value antes
- **Position: fixed + ancestros con `filter`/`transform`/`backdrop-filter`/`will-change`** = containing block nuevo. Bug clásico.
