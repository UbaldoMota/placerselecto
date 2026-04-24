# Changelog — ClasificadosAdultos

## [2026-04-23] — Optimización de imágenes, gates de verificación, UX y fixes críticos

### 🖼️ Optimización de imágenes (thumbnails + WebP + cache)
- **`Upload::generateVariants()`** genera automáticamente tras `saveImage`:
  - `{stem}_thumb.{ext}` (~300px lado mayor) + `{stem}_thumb.webp`
  - `{stem}_medium.{ext}` (~800px lado mayor) + `{stem}_medium.webp`
  - `{stem}.webp` (full en WebP del original — si no era ya webp)
  - 5 archivos por imagen. Cada original de ~2.5MB ahora genera ~300KB totales en variantes
- **`ImageController::serve`** acepta `?size=thumb|medium|full` (default `full`) + content negotiation por `Accept: image/webp`:
  - Cascada de fallback: webp del tamaño pedido → original del tamaño pedido → webp full → original full
  - `Cache-Control: public, max-age=31536000, immutable` + `ETag` (con 304 Not Modified)
- **Aplicado en todas las vistas:**
  - Listados (`/`, `/perfiles`, cards en dashboard/mis-perfiles/estadisticas): `?size=thumb` o `?size=medium`
  - Admin (perfiles, comentarios, reports, perfil-preview, verificar-fotos): tamaño según uso
  - `ads/show.php` y `perfiles/show.php` separan `$gridUrls` (medium) del `$lightboxUrls` (full)
  - `foto-uploader.php`: `?size=thumb` en previews
- **Conteo de almacenamiento** ya incluye variantes — `StorageScannerModel` lee todos los archivos del directorio sin filtrar por sufijo

### 📄 Paginación en almacenamiento admin
- `/admin/almacenamiento?cat=fotos&vista=galeria` ahora pagina: **60 por página en galería, 100 en lista**
- `StorageScannerModel::listar($cat, $limit, $offset)` soporta offset; nuevo `contarCategoria()`
- Partial reutilizable `app/views/admin/_storage-pagination.php` (First · Prev · ventana ±2 · Next · Last)
- Header muestra "mostrando X–Y de Z"

### 🚫 Gate de publicación: requiere foto + video de verificación
- `AdminController::publishProfile` bloquea con flash error si:
  - No hay fotos en `perfil_fotos WHERE es_verificacion=1` (foto con rostro)
  - `perfiles.video_verificacion` está vacío (video de 5s)
- En `admin/perfil-preview.php` el botón "Publicar perfil" queda `disabled` con tooltip y banner amarillo explicando qué falta
- Si admin intenta publicar desde el listado `/admin/perfiles` igual se bloquea server-side y redirige al detalle con el banner visible

### 🔔 Notificaciones admin por envío de verificación
- `PerfilesController::subirFotosVerificacion` → notif tipo `verificacion_fotos` a todos los admins
- `PerfilesController::subirVideoVerificacion` → notif tipo `verificacion_video`
- Ambas apuntan a `/admin/perfil/{id}` con icono y color warning

### 🖼️ Foto oculta deja de ser la principal
- `AdminController::toggleHidePhoto` y `deletePhoto`: si la foto ocultada/eliminada era la principal del perfil (coincide con `perfiles.imagen_token`), se promueve automáticamente la primera foto visible de la galería como nueva principal
- Si no queda ninguna visible, se limpian `imagen_token` y `imagen_principal` (antes los listados seguían mostrando la foto oculta)

### ✨ Lightbox: reubicación automática a `<body>`
- `lightbox.js`: al inicializar mueve `#lightbox` a `document.body` para escapar cualquier stacking context creado por ancestros con `transform`/`filter`/`backdrop-filter`
- **Bug original:** `.card:hover { transform: translateY(-2px) }` convertía al card en containing block → el lightbox `position:fixed` quedaba confinado al card en vez de cubrir el viewport → parpadeaba al entrar/salir el hover
- Arregla el mismo bug en `perfiles/show.php`, `ads/show.php` y `admin/perfil-preview.php` sin tocar las vistas

### 🐛 `app.js`: handler XHR respeta `preventDefault`
- **Bug:** el handler `data-upload-form` subía por XHR aunque otro handler (p. ej. `validation.js`) hubiese bloqueado con `preventDefault()` → el usuario veía "la página recarga" tras fallar validación client-side
- **Fix:** `if (ev.defaultPrevented) return;` al inicio del handler
- Afectaba creación/edición de perfil, verificación de fotos

### 📱 UX mobile
- **Navbar:** botón "Registrarse" ahora muestra texto en móvil (antes solo el ícono `+`)
- **Checkbox SMS** en `/registro/publicador`: el contenedor rosa completo es ahora un `<label for="autoriza_sms">` — toda el área es tappable, no solo el cuadradito. El link "aviso de privacidad" usa `event.stopPropagation()` para no togglear el check al navegarse
- **Video de verificación en pantalla completa mientras graba:**
  - Al presionar "GRABAR (5 seg)", el `#videoWrap` se mueve a `<body>` y toma `position:fixed; inset:0; z-index:10500; 100vh/100dvh`
  - `object-fit: contain` para que la imagen completa sea visible
  - Indicador REC y countdown agrandados con `safe-area-inset` (iPhone con notch)
  - `body { overflow: hidden }` durante grabación
  - Todos los overrides con `!important` porque los estilos inline del contenedor tenían mayor especificidad
  - Al terminar los 5s: el elemento vuelve a su lugar original y aparece la pantalla de revisión

### 🐛 Fix crítico: compra de tokens fallaba con integrity constraint
- **Síntoma:** Flash "Error al procesar la compra. Intenta de nuevo." al intentar cualquier paquete
- **Log:** `SQLSTATE[23000]: Integrity constraint violation: 1048 Column 'id_anuncio' cannot be null`
- **Causa:** drift de schema — en prod `pagos.id_anuncio` estaba `NOT NULL`, pero al comprar tokens no hay anuncio asociado (se pasa `null`). Local tenía la columna como NULL (alguna migración nunca se aplicó a prod)
- **Fix aplicado en prod vía script one-shot (subido/ejecutado/auto-eliminado):** `ALTER TABLE pagos MODIFY id_anuncio INT UNSIGNED NULL`
- `config/schema.sql` actualizado a `DEFAULT NULL` para instalaciones nuevas

### Archivos modificados (destacados)
| Archivo | Cambio |
|---|---|
| `app/Upload.php` | `generateVariants()` tras `saveImage` |
| `app/controllers/ImageController.php` | `?size=`, content negotiation WebP, ETag + cache 1 año, `resolveImageVariant()` |
| `app/controllers/AdminController.php` | Gate de publicación, notifs verif., promover foto principal, paginación almacenamiento |
| `app/controllers/PerfilesController.php` | Notifs admin en subir fotos/video verificación |
| `app/models/StorageScannerModel.php` | `listar($cat, $limit, $offset)`, `contarCategoria()` |
| `app/views/admin/_storage-pagination.php` | **nuevo** — paginador reutilizable |
| `app/views/admin/perfil-preview.php` | Gate visual, `$gridUrls/$lightboxUrls`, eager loading |
| `app/views/perfiles/verificar-camara.php` | Fullscreen CSS con `!important` y `100dvh` |
| `public/assets/js/lightbox.js` | Reubica `#lightbox` a `<body>` al init |
| `public/assets/js/verificar-camara.js` | Mueve `#videoWrap` a body al grabar, lo restaura al terminar |
| `public/assets/js/app.js` | `if (ev.defaultPrevented) return` en upload-form handler |
| `app/views/partials/navbar.php` | "Registrarse" siempre visible |
| `app/views/auth/registro-contacto.php` | `<label>` envuelve todo el contenedor SMS |
| `config/schema.sql` | `pagos.id_anuncio` ahora `DEFAULT NULL` |

---

## [2026-04-20] — Sesión de cambios grandes (UX, moderación, comentarios, videos)

### 💬 Sistema de comentarios con rol comentarista
- Nuevo valor en `usuarios.rol` enum: `'comentarista'`
- Registro rápido `/registro/comentarista` (solo nombre + email + contraseña, sin SMS, sin teléfono)
- Comentaristas **no pueden crear perfiles** (bloqueado en `PerfilesController::create/store`)
- Home del comentarista = `/` (no tiene dashboard). Login/guest middleware redirige por rol
- Navbar muestra menú recortado para comentaristas: solo "Explorar perfiles" y "Notificaciones"
- Tabla nueva `perfil_comentarios` con UNIQUE(id_perfil, id_usuario) — **1 comentario por perfil por usuario**
- Estados: `pendiente|publicado|oculto|reportado|eliminado`
- **Todos los comentarios requieren aprobación admin** antes de publicarse (default `pendiente`)
- **Comentarios inmutables** una vez enviados — no hay edit. Solo eliminar + escribir uno nuevo
- **Cooldown de 7 días** tras eliminación (soft-delete con `fecha_cooldown_hasta`) — previene spam
- Form con estrellas clickables (JS controla state + hover preview) y textarea (10–2000 chars)
- Rating promedio visible en `/perfiles` (listado) y `/perfil/{id}` (card completo con conteo)
- Moderación en `/admin/comentarios`: lista con perfil destinatario destacado (thumbnail + nombre + link admin/público), filtros por estado, acciones aprobar/ocultar/restaurar/eliminar
- Notificaciones automáticas: nuevo comentario → admins; aprobado → autor + dueño del perfil; ocultado/eliminado → autor

### 🎬 Videos en perfiles (hasta 3 por perfil)
- Nueva tabla `perfil_videos` (id, id_perfil, token, nombre_archivo, orden, duracion_seg, tamano_bytes, oculta)
- Proxy seguro `GET /video/{token}` con soporte **HTTP Range requests** (206 Partial Content) para streaming en `<video>`
- Validación MIME real + 50MB máx por video (MP4/WebM/MOV); directorio `uploads/videos/` con `Require all denied`
- Formularios `create.php` y `edit.php` aceptan `videos[]` multiple; edit permite eliminar existentes y agregar hasta completar el tope
- Vista pública muestra grid de videos reproducibles en `/perfil/{id}`

### 🚩 Rediseño del formulario de denuncia + panel de reportes
- **Nuevas categorías de reporte** (enum `reportes.motivo` ampliado): verificar_edad, mal_clasificado, difamaciones, fotos_de_internet, fotos_son_mias, usan_mi_telefono, estafa, extorsion (+ los antiguos)
- Columna nueva `reportes.url_referencia` — sólo visible/required cuando motivo = `fotos_de_internet` (validación client + server)
- **Reportes solo para usuarios logueados** (antes permitía anónimo)
- Nuevo estado en reportes: `rechazado` (antes solo pendiente/revisado/resuelto)
- Columnas nuevas: `nota_admin` TEXT, `id_admin_resolucion` INT
- Panel `/admin/reportes` **rediseñado de tabla a cards** con acciones ricas:
  - Header con thumbnail + nombre del perfil reportado + dueño + badges del estado del perfil
  - Vista admin / ver público inline
  - **7 acciones nuevas:** Marcar en revisión · Pedir más información (notifica al reportero) · Rechazar con motivo · Resolver · Suspender cuenta del denunciado · Eliminar perfil · Guardar nota interna
  - Collapse expandibles para acciones que requieren input
- Tabs con 5 estados + paginación compacta (first/prev/window5/next/last)

### 📋 Listado de perfiles tipo "fila rica"
- `/perfiles` cambia de cards 2×N a **filas horizontales** con mucha más info visible:
  - Foto grande, badge TOP/RESALTADO, nombre+edad, tiempo relativo
  - Categoría + ubicación (municipio, estado)
  - Descripción truncada a 2 líneas (strip_tags)
  - Iconos de contactos disponibles (WA/Telegram/Email en círculos coloreados)
  - Badge "No pide anticipo" si aplica
  - Pills con contadores: N fotos, N videos, "Mapa" si tiene lat/lng
  - **Promedio de estrellas + conteo** si hay comentarios publicados
  - Flecha que se desliza en hover
- Query `listarPublicos` expandida con subqueries para fotos_count, videos_count, tiene_mapa, com_count, com_promedio

### 🏠 Dashboard del usuario rediseñado (tipo menú iOS/Settings)
- **Grid de módulos de bloques** → **menú de lista** en sidebar **izquierdo**
- 3 secciones colapsables:
  - **Mi cuenta:** Mis perfiles · Mis tokens · Estadísticas · Notificaciones (con badge rojo)
  - **Verificación y confianza:** Documento · Confiabilidad (con ancla al detalle)
  - **Otros:** Explorar · Soporte
- Eliminada card "Verificación de cuenta" (se hace por perfil)
- Confiabilidad: quitado contador X/Y y barra de progreso, solo indicadores
- Orden sidebar: Acciones rápidas → Cómo funciona → Confiabilidad
- Fix del badge "Cuenta activa" (antes verde sobre verde, ilegible)
- Hero con gradiente rosa-blanco coherente con el tema claro

### ✨ Otros cambios importantes
- **Edad obligatoria + toggle `edad_publica`** en perfiles — se guarda siempre, opcional mostrarla públicamente
- **Nav unificada** en todos los módulos de usuario: botones "Mis perfiles" + "Dashboard" (partial `back-nav.php`)
- **Toasts** (flash) rediseñados: auto-dismiss con barra de progreso, pausa en hover, cierre manual, estilos consistentes entre layout y vistas standalone (partial `toasts.php`)
- **Barra de búsqueda persistente** — al abrir un perfil el form de búsqueda sigue visible (partial `perfil-search.php`)
- **Sesión auto-refresh** — en cada request autenticado `index.php` relee `rol`/`verificado`/`estado_verificacion` de BD (previene sesión stale tras cambios admin)
- **Notifs admin → usuario** ampliadas: eliminar perfil/anuncio/foto, ocultar foto, resolver reporte, ajustar saldo tokens
- **Comentaristas → admin:** nueva entrada en el inbox de moderación al recibir comentario pendiente o reporte
- **Categoría "Acompañantes"** — fix de mojibake en BD (encoding corrupto)
- **Estados geográficos:** Coahuila de Zaragoza → Coahuila, Michoacán de Ocampo → Michoacán, Veracruz de Ignacio de la Llave → Veracruz, México → **Estado de México**
- **Hero home:** contadores numéricos → pills con propuestas de valor (Perfiles verificados · Contacto directo · Discreto y seguro)
- **Category tiles:** bloques con gradientes saturados → **cuadrados blancos minimalistas** con icono rosa + nombre
- **Status banner prominente** en "Mis perfiles": Publicado ✅ / En espera ⏳ / Acción requerida ⚠️ (pulsa) / Rechazado ❌
- **Confiabilidad:** indicador "sin reportes negativos" (sin el requisito de 1 año) · "Usuario activo y confiable" ahora cuenta perfiles publicados en vez de anuncios
- **Fotos verificadas automáticas** cuando admin publica un perfil que tiene fotos de verificación
- **Post-subida de fotos de verificación** ahora redirige a `/perfil/{id}/verificar` (antes a /mis-perfiles perdía el flujo)
- **Paginación compacta** en `/notificaciones` y `/admin/reportes`: First · Prev · ventana de 5 · Next · Last, con contador "mostrando X–Y de Z"
- **Fix mobile navbar**: brand visible pero más pequeño en ≤575px con `max-width + ellipsis`; toasts con `minmax(0, 1fr)` en grid y breakpoint subido a 768px
- **Checkboxes:** fix del estado `:checked` invisible (inline styles sobrescribían Bootstrap) en login, register, registro-contacto
- **Age-gate fix** — excluir `/api/*` del guardado de `age_redirect` (el polling de notificaciones rompía el flujo)
- **Error pages 403/404/500** convertidas al tema claro

### Archivos nuevos destacados
| Archivo | Propósito |
|---|---|
| `app/models/PerfilComentarioModel.php` | Comentarios con rating, cooldown, moderación |
| `app/models/PerfilVideoModel.php` | Videos públicos por perfil |
| `app/controllers/ComentarioController.php` | Crear/eliminar/reportar comentarios |
| `app/views/perfiles/index.php` (reescrita) | Filas ricas con contactos/assets/rating |
| `app/views/admin/reports.php` (reescrita) | Cards con 7 acciones de moderación |
| `app/views/admin/comentarios.php` | Moderación de comentarios con perfil destacado |
| `app/views/auth/registro-comentarista.php` | Registro rápido email + password |
| `app/views/partials/perfil-search.php` | Barra de búsqueda reutilizable |
| `app/views/partials/back-nav.php` | Botones volver Mis perfiles/Dashboard |
| `app/views/partials/toasts.php` | Toasts self-contained con auto-dismiss |
| `config/migration_soporte.sql` | estado suspendido + tabla soporte_mensajes |

---

## [2026-04-19] — Bloqueo de cuentas rechazadas/suspendidas + solicitud de reactivación

### Nuevas funcionalidades
- Nuevo estado `suspendido` en `usuarios.estado_verificacion` (enum ahora incluye pendiente/aprobado/rechazado/suspendido)
- Cuentas `rechazado` o `suspendido` **no pueden crear perfiles** — tanto en `GET /perfil/nuevo` como en `POST`, se redirige a `/cuenta/reactivar`
- Nueva ruta `GET/POST /cuenta/reactivar` — formulario para que el usuario envíe un mensaje directo al admin solicitando reactivación (rate-limit: 2/día)
- Nueva tabla `soporte_mensajes` — tipos: `reactivacion`, `general`, `duda`, `reporte_problema`; estados: `abierto`, `respondido`, `cerrado`
- Nueva ruta admin `/admin/mensajes` — inbox con filtros (estado/tipo/búsqueda) y respuesta inline
- Al responder un mensaje de reactivación, el admin puede **marcar "Aprobar reactivación"** → la cuenta pasa a `aprobado` automáticamente y el usuario es notificado
- Banner actualizado en navbar: cuando la cuenta está rechazada/suspendida, muestra link directo a "Solicitar reactivación"
- Notificaciones: al enviar solicitud → admins; al responder/reactivar → usuario

### Archivos nuevos
| Archivo | Propósito |
|---|---|
| `config/migration_soporte.sql` | Enum + tabla `soporte_mensajes` |
| `app/models/SoporteMensajeModel.php` | CRUD + `ultimaAbiertaPorTipo`, `listarAdmin`, `responder`, `cerrar` |
| `app/views/user/solicitar-reactivacion.php` | Form + visualización de última solicitud + respuesta admin |
| `app/views/admin/mensajes.php` | Inbox con filtros + respuesta inline + checkbox "aprobar reactivación" |

### Archivos modificados
| Archivo | Cambio |
|---|---|
| `routes/web.php` | +5 rutas (cuenta/reactivar GET/POST; admin/mensajes + responder/cerrar) |
| `app/controllers/PerfilesController.php` | `create` y `store` redirigen a `/cuenta/reactivar` si cuenta bloqueada |
| `app/controllers/UserController.php` | +`showReactivacion`, `enviarReactivacion` |
| `app/controllers/AdminController.php` | +`mensajes`, `responderMensaje` (con opción aprobar reactivación), `cerrarMensaje` |
| `app/views/partials/navbar.php` | Banner unificado para rechazado/suspendido con link de reactivación |
| `app/views/admin/dashboard.php` | +botón Mensajes |
| DB `usuarios.estado_verificacion` | +`suspendido` al enum |

---

## [2026-04-19] — Sistema de tokens + boosts programables

### Monetización por tokens (reemplaza planes fijos)
- **Flujo nuevo:** usuario compra paquete → recibe tokens → los gasta en boosts de sus perfiles
- **Tipos de boost:** `top` (primero en su municipio) o `resaltado` (destaque visual sin subir posición)
- **Programación:** boost puede iniciar ahora o programarse para una fecha futura, con duración 1–168h
- **Cancelación:** boosts programados (no iniciados) se cancelan con reembolso 100%; activos no
- **Activación lazy:** las queries de listado filtran por `inicio <= NOW() AND fin > NOW()`, sin cron
- **Listados ordenan por:** boost top > boost resaltado > fecha_publicación

### Schema nuevo
- `usuarios.saldo_tokens INT UNSIGNED DEFAULT 0`
- `token_paquetes` — paquetes editables por admin (nombre, monto_mxn, tokens, bonus_pct, orden, activo)
- `token_tarifas` — tarifas por tipo de boost (tokens_por_hora), editables por admin
- `tokens_movimientos` — ledger completo (recarga, consumo, reembolso, ajuste_admin) con `saldo_despues` snapshot
- `perfil_boost` — ventanas programadas/activas por perfil, con `es_legacy` para destacados migrados
- `pagos` — columnas nuevas: `id_paquete`, `tokens_otorgados`; `id_anuncio` y `tipo_destacado` ahora nullable

### Panel admin `/admin/tokens`
- Tab **Resumen** — stats globales (recargado, consumido, reembolsado, saldo circulante, boosts activos)
- Tab **Paquetes** — CRUD completo con validación (nombre, monto, tokens, bonus%, orden, toggle activo)
- Tab **Tarifas** — editar `tokens_por_hora` para top/resaltado + descripción visible al usuario
- Tab **Movimientos** — ledger global con filtros (tipo, email)
- En `/admin/usuario/{id}` — card nuevo "Ajustar saldo" (sumar/restar con motivo → queda en ledger como `ajuste_admin` + notifica al usuario)

### Panel usuario
- `/tokens/comprar` — paquetes activos en tarjetas con cálculo de $/token
- `/tokens/confirmacion/{id}` — confirmación post-compra con saldo nuevo
- `/mis-tokens` — saldo + boosts activos/programados (cancelables) + historial de movimientos paginado
- `/perfil/{id}/destacar` — formulario con selección de tipo, duración (presets 1h/1d/7d), inicio (ahora/programado), **cálculo de costo en vivo** con JS + warning de saldo insuficiente + historial del perfil
- Link "Mis tokens" en dropdown de usuario del navbar

### Archivos nuevos
| Archivo | Propósito |
|---|---|
| `config/migration_tokens.sql` | Schema + seed + migración legacy de destacados |
| `app/models/TokenPaqueteModel.php` | CRUD paquetes |
| `app/models/TokenTarifaModel.php` | Get/update tarifas |
| `app/models/TokenMovimientoModel.php` | `aplicar()` atómico con SELECT FOR UPDATE, historial, stats |
| `app/models/BoostModel.php` | `crear`, `cancelar`, `sincronizarEstados`, `haySolapamiento`, `activoPorPerfil` |
| `app/controllers/BoostController.php` | `show`, `create`, `cancel` |
| `app/views/admin/tokens-{index,paquetes,tarifas,movimientos}.php` | 4 vistas admin |
| `app/views/payment/token-packages.php` | Comprar paquete |
| `app/views/payment/token-confirmation.php` | Post-compra |
| `app/views/user/mis-tokens.php` | Panel usuario |
| `app/views/boost/create.php` | Destacar perfil con tokens |

### Archivos modificados
| Archivo | Cambio |
|---|---|
| `routes/web.php` | +15 rutas (admin tokens/paquetes/tarifas/movimientos/saldo + user tokens/mis-tokens + boosts) |
| `app/controllers/AdminController.php` | +11 métodos (tokens admin + ajustarSaldo) |
| `app/controllers/PaymentController.php` | +3 métodos (showPackages, buyPackage, tokenPurchaseConfirmation) |
| `app/controllers/UserController.php` | +método misTokens |
| `app/controllers/HomeController.php` | Simplificado: listarPublicos ya ordena por boost |
| `app/models/PerfilModel.php` | `listarPublicos` + `misPerfiles` JOIN con boosts activos, nuevas cols `boost_top`/`boost_resaltado` |
| `app/models/PagoModel.php` | `iniciarPago` acepta id_perfil, id_paquete, tokens_otorgados |
| `app/views/partials/navbar.php` | +link "Mis tokens" |
| `app/views/admin/dashboard.php` | +botón Tokens |
| `app/views/admin/user-detail.php` | +card saldo + ajuste manual |
| `app/views/home/index.php`, `perfiles/index.php`, `user/mis-perfiles.php` | Badges TOP/RESALTADO basados en boost_top/boost_resaltado |
| `public/assets/css/app.css` | +estilos `.ad-card--resaltado`, `.perfil-resaltado` |

### Decisiones de producto aplicadas
- **Scope limitado a top municipio** (no estado/nacional) — simplifica UI y schema
- **Lazy check** en queries (sin cron) — más simple en Laragon
- **Cancelación**: programado = reembolso 100%; activo = no cancelable
- **Legacy**: destacados vigentes migrados a `perfil_boost` con `es_legacy=1`, `tokens_gastados=0`

---

## [2026-04-19] — Fix: age-gate redirigía al endpoint JSON de notificaciones

- El polling cada 30s de la campanita (`GET /api/notificaciones/pendientes`) pasaba por `index.php`, que guardaba cada URI como `age_redirect`. Como sólo se excluía `/img/`, los requests de fondo de la API sobreescribían el destino → al confirmar edad, el usuario terminaba viendo el JSON en crudo.
- **Fix:** excluir también `/api/*` del guardado de `age_redirect` + guardia defensiva en `AuthController::confirmAge` que desvía a `/` si el destino apunta a un recurso/endpoint.

---

## [2026-04-19] — Rediseño visual: tema claro (blanco + rosa + negro suave)

### Cambios de paleta
- **Primary** `#e94560` coral → `#FF2D75` rosa vibrante
- **Primary claro** nuevo: `#FF7FA8` (bordes, hover secundario)
- **Background** `#0f0f1a` negro-azulado → `#FFFFFF` blanco
- **Card bg** `#16213e` azul → `#FFFFFF` (cards blancas con sombra suave)
- **Text** `#e0e0e0` gris claro → `#1A1A1A` negro suave
- **Muted** `#8892a4` → `#666666`
- **Border** `#2a2a4a` púrpura → `#E5E5E5` gris claro
- **Semánticos** actualizados a tonos Tailwind-ish (success #10B981, warning #F59E0B, danger #EF4444, info #3B82F6)

### Cambios de estilo
- Fuente principal **Inter** (400–800) vía Google Fonts
- Sombras suaves (`shadow-sm/md/lg` + `shadow-pink` para CTAs)
- Radios 8–12 px (tarjetas 10 px, modales 12 px)
- Navbar blanco con sombra sutil, logo negro+rosa, hover rosa
- Botones primary rosa con hover a rosa claro + lift
- Cards blancas con lift (+sombra) en hover
- Formularios blancos con focus rosa y placeholder gris claro
- Hero con gradiente rosa-blanco muy sutil
- Lightbox conservado en oscuro (para visualizar imágenes)

### Archivos modificados
| Archivo | Cambio |
|---|---|
| `public/assets/css/app.css` | **Reescritura completa** (1200+ líneas): tokens, base, componentes, responsive |
| `app/views/partials/layout.php` | +Google Fonts Inter, age-gate text colors |
| `app/views/partials/403/404/500.php` | Tema claro |
| `app/views/auth/login.php` | Gradientes rosa-tinte, hover correcto |
| `app/views/auth/*.php` | Gradientes rosa en lugar de azul-marino |
| `app/views/perfiles/create.php` + `edit.php` | Quill editor a tema claro |
| 17 vistas con inline styles | sed global: coral→rosa, amber viejo→warning nuevo, white-translucent→black-translucent |
| `index.php` | Pre de error en tema claro |

---

## [2026-04-19] — Notificaciones en tiempo real (campanita)

### Nuevas funcionalidades

#### Sistema de notificaciones in-app
- Tabla nueva `notificaciones` con índice `(id_usuario, leida, fecha_creacion)`
- Campanita en navbar con badge rojo de no-leídas + dropdown de últimas 10
- Página completa `/notificaciones` con historial paginado + acciones (marcar leída / eliminar)
- Polling AJAX cada 30s con **Page Visibility API** (pausa si la pestaña está oculta)
- **Backoff exponencial**: si el server responde 304 (sin cambios), sube hasta 90s; al llegar algo nuevo resetea a 30s
- Respuesta 304 Not Modified con ETag para ahorrar ancho de banda
- Animación shake en la campanita al llegar una notificación nueva

#### Eventos que disparan notificación
**Admin → Usuario:**
- Cuenta aprobada / rechazada
- Documento verificado / rechazado (con motivo)
- Fotos verificadas
- Perfil publicado / rechazado

**Usuario → Admin(s):**
- Nuevo usuario registrado (al completar registro)
- Nuevo perfil pendiente
- Nuevo documento subido
- Nuevo reporte de perfil
- Nuevo pago registrado

### Archivos nuevos
| Archivo | Propósito |
|---|---|
| `config/migration_notificaciones.sql` | Schema de la tabla |
| `app/models/NotificacionModel.php` | `crear()`, `crearParaAdmins()`, `paraPolling()`, `marcarLeida()`, `historial()` |
| `app/controllers/NotificacionController.php` | Endpoints polling, marcar leída/s, historial, eliminar |
| `app/views/notificaciones/index.php` | Página de historial |
| `public/assets/js/notifications.js` | Cliente: polling + Page Visibility + backoff |

### Archivos modificados
| Archivo | Cambio |
|---|---|
| `routes/web.php` | +5 rutas: `/api/notificaciones/pendientes`, `/notificaciones`, `/notificaciones/leer-todas`, `/notificacion/{id}/leer`, `/notificacion/{id}/eliminar` |
| `app/views/partials/navbar.php` | Campanita con badge + dropdown |
| `app/views/partials/layout.php` | `window.APP_URL_JS` + carga de `notifications.js` solo si hay user |
| `public/assets/css/app.css` | Estilos `.notif-*` (badge, dropdown, lista, pulse) |
| `app/controllers/AdminController.php` | Hooks en aprobar/rechazar usuario, verificar/rechazar doc, publicar/rechazar perfil |
| `app/controllers/PerfilesController.php` | Hook en crear perfil + reportar perfil |
| `app/controllers/UserController.php` | Hook en subir documento |
| `app/controllers/AuthController.php` | Hook al completar registro |
| `app/controllers/PaymentController.php` | Hook al completar pago |
| DB `notificaciones` | Tabla nueva |

---

## [2026-04-19] — Verificación de identidad por documento (INE / Pasaporte)

### Nuevas funcionalidades

#### Usuario — subida de documento de identidad
- Nueva ruta `GET /mi-cuenta/documento` → vista con drag-and-drop, previsualización y estado actual
- Nueva ruta `POST /mi-cuenta/documento` → sube imagen (JPG/PNG/WEBP, máx 5 MB), valida MIME real
- Al resubir un nuevo documento: borra el anterior físicamente y resetea `documento_verificado = 0`
- Guardado en `UPLOADS_PATH/verificaciones/documentos/` con nombre `doc_u{id}_{time}_{rand}.ext`
- Columnas añadidas a `usuarios`: `documento_identidad VARCHAR(255)`, `documento_identidad_at DATETIME`

#### Sugerencia en "Mis perfiles"
- Banner amarillo si el usuario no ha enviado documento: invita a subirlo con enlace directo
- Banner verde si el documento está en revisión (pendiente de aprobación admin)
- No muestra nada si ya está verificado

#### Admin — visualización y aprobación del documento
- Nueva ruta `GET /admin/usuario/{id}/documento` → sirve la imagen de forma segura (solo admin)
- Card en `admin/user-detail.php`: muestra la imagen + fecha de envío + botón "Marcar como verificado" / "Revocar"
- El botón de toggle usa el mismo endpoint ya existente (`/admin/usuario/{id}/verificacion`) con `campo=documento_verificado`

### Archivos modificados
| Archivo | Cambio |
|---|---|
| `routes/web.php` | +`/mi-cuenta/documento` GET/POST, +`/admin/usuario/{id}/documento` GET |
| `app/controllers/UserController.php` | +`showSubirDocumento()`, +`subirDocumento()`, `misPerfiles()` pasa `$usuario` |
| `app/controllers/AdminController.php` | +`serveUserDocumento()` |
| `app/views/user/subir-documento.php` | Vista nueva: drag-drop, previsualización, estado actual, instrucciones |
| `app/views/user/mis-perfiles.php` | Banner de sugerencia cuando falta documento |
| `app/views/admin/user-detail.php` | Card de documento con imagen, fecha y toggle verificar/revocar |
| DB `usuarios` | +`documento_identidad VARCHAR(255) NULL`, +`documento_identidad_at DATETIME NULL` |
| `uploads/verificaciones/documentos/` | Nuevo directorio para documentos |

---

## [2026-04-18] — Gestión de fotos por el admin durante revisión

### Nuevas funcionalidades

#### Admin puede ocultar o eliminar fotos de galería
- Nueva ruta `POST /admin/foto/{id}/ocultar` → alterna visibilidad pública de la foto
- Nueva ruta `POST /admin/foto/{id}/eliminar` → borra foto de DB y archivo físico
- Galería en `perfil-preview.php` rediseñada: grid con botones `<👁️ Ocultar>` / `<🗑 Eliminar>` sobre cada foto
- Fotos ocultas se muestran con borde rojo + badge "Oculta" + opacidad reducida; botón cambia a `<👁 Mostrar>`
- Lightbox solo incluye fotos visibles (no ocultas)
- Sidebar muestra conteo de fotos ocultas en rojo si las hay

#### Columna `oculta` en `perfil_fotos`
- `ALTER TABLE perfil_fotos ADD COLUMN oculta TINYINT(1) NOT NULL DEFAULT 0`
- `PerfilFotoModel::galeria()` ahora excluye `oculta = 1` (invisible al público)
- `PerfilFotoModel::galeriaAdmin()` devuelve todas, incluyendo ocultas (para el admin)
- `PerfilFotoModel::toggleOculta(int $id)` alterna el estado con una sola query

### Archivos modificados
| Archivo | Cambio |
|---|---|
| `routes/web.php` | +2 rutas: admin/foto/{id}/ocultar (POST), admin/foto/{id}/eliminar (POST) |
| `app/controllers/AdminController.php` | +`toggleHidePhoto()`, +`deletePhoto()`, `previewProfile()` pasa `fotosGaleria`/`fotosVer` por separado |
| `app/models/PerfilFotoModel.php` | `galeria()` excluye ocultas, +`galeriaAdmin()`, +`toggleOculta()` |
| `app/views/admin/perfil-preview.php` | Galería con botones por foto, lightbox solo visibles, sidebar con conteo ocultas |
| DB `perfil_fotos` | +`oculta TINYINT(1) NOT NULL DEFAULT 0` |

---

## [2026-04-18] — Verificación doble de perfiles (fotos + video)

### Nuevas funcionalidades

#### Verificación por video por perfil
- Nueva ruta `GET /perfil/{id}/verificar/camara` → vista de grabación con cámara
- Nueva ruta `POST /perfil/{id}/verificar/video` → subida XHR (max 60 MB)
- Nueva ruta `GET /admin/perfil/{id}/video` → sirve el video al admin de forma segura
- Videos guardados en `UPLOADS_PATH/verificaciones/perfiles/`
- Columnas añadidas a `perfiles`: `video_verificacion VARCHAR(255)`, `video_verificacion_at DATETIME`

#### Flujo de cámara con revisión previa
- El usuario activa la cámara con un botón explícito (no automático al cargar)
- Graba 5 segundos con countdown visual
- Ve el video grabado y elige "Volver a grabar" o "Enviar video"
- El video nunca se sube sin confirmación del usuario

#### Verificación doble obligatoria
- Ambos pasos (fotos + video) son necesarios para solicitar aprobación
- `verificar-instrucciones.php` rediseñada como página de progreso "X/2 completados"
- Cada paso muestra su estado individual (✅ completado / ⏳ pendiente) con botón de acción directo
- Mensaje contextual en `mis-perfiles.php`:
  - Verificación incompleta → "Completa los dos pasos de verificación para solicitar la aprobación."
  - Ambos completos → "En espera de aprobación del equipo."

#### Admin — listado de perfiles mejorado
- Columna de estado ahora muestra badge de verificación con tres niveles:
  - 🟢 "Verificación completa" — tiene fotos Y video
  - 🟡 "Ver. incompleta (falta fotos/video)" — solo un paso completado
  - 🔴 "Sin verificación" — ningún paso completado
- `perfil-preview.php`: filas de estado por cada verificación + aviso si falta alguna + `<video>` reproducible

### Correcciones
- `Permissions-Policy` en `index.php`: la cámara ahora se permite solo en rutas `/verificar/camara` y `/verificacion/camara`; bloqueada en el resto del sitio
- Al hacer "Volver a grabar": se restauran correctamente `disabled` e `innerHTML` del botón "Activar cámara" (antes quedaba trabado en "Activando…")

### Archivos modificados
| Archivo | Cambio |
|---|---|
| `routes/web.php` | +3 rutas: verificar/camara (GET), verificar/video (POST), admin/perfil/{id}/video (GET) |
| `app/controllers/PerfilesController.php` | +`showVerificarCamara()`, +`subirVideoVerificacion()`, `showVerificar()` pasa `tieneFotosVer`/`tieneVideoVer` |
| `app/controllers/AdminController.php` | +`serveProfileVideo()` |
| `app/models/PerfilModel.php` | `misPerfiles()` añade subquery `fotos_ver` |
| `app/views/perfiles/verificar-camara.php` | Reescrita: flujo 4 pasos, botón manual, revisión previa |
| `app/views/perfiles/verificar-instrucciones.php` | Reescrita: progreso 2/2, tarjetas por paso |
| `app/views/user/verificar-camara.php` | Igual que perfil: botón manual + revisión previa |
| `app/views/user/mis-perfiles.php` | Mensajes contextuales según verificación, bloque de estado por paso |
| `app/views/admin/perfiles.php` | Badge de verificación con 3 niveles |
| `app/views/admin/perfil-preview.php` | Card video, filas de estado, aviso incompleto |
| `index.php` | `Permissions-Policy` condicional por ruta |
| DB `perfiles` | +`video_verificacion`, +`video_verificacion_at` |
| `public/uploads/verificaciones/perfiles/` | Nuevo directorio para videos de perfiles |

---

## [2026-04-17] — Campos adicionales de perfiles, estadísticas y WhatsApp tracking

### Nuevas funcionalidades
- Campos de contacto: `telegram`, `email_contacto` con toggle-cards
- Switch `pide_anticipo`
- Mapa de zona con Leaflet.js + Nominatim (sin API key)
- Pre-relleno de contacto desde datos de cuenta (`usuarios.telefono` → whatsapp, `usuarios.email` → email_contacto)
- Tracking de clics WhatsApp vía redirect `/perfil/{id}/whatsapp`
- Página `/mis-estadisticas` con gráficas de 7 días (visitas + clics WA)
- Tabla `perfil_stats` para estadísticas diarias por perfil
- Fotos de verificación protegidas: `ImageController` devuelve 403 para no-admins

### Archivos nuevos
- `app/views/partials/perfil-extra-fields.php`
- `app/views/user/estadisticas.php`
- `app/views/perfiles/verificar-fotos.php`
- `app/views/perfiles/verificar-instrucciones.php`

---

## [2026-04-16] — Sistema de perfiles (núcleo)

- CRUD completo de perfiles con hasta 3 por usuario
- Galería de fotos (hasta 10, con lightbox)
- Flujo de verificación de fotos (`es_verificacion = 1`)
- Verificación de cuenta por video (`UserController::subirVideoVerificacion`)
- Dashboard con estadísticas de visitas
- Panel admin: listado, preview, publicar/rechazar/eliminar
