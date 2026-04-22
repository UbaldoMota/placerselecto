# ClasificadosAdultos — Documentación del Proyecto

## ¿Qué es?

Directorio de clasificados para adultos (México). Los usuarios se registran, crean anuncios de servicios, y pueden destacarlos pagando un plan. Todo el contenido pasa por revisión de un administrador antes de publicarse.

**Stack:** PHP 8+ sin frameworks, MySQL 8.4, Bootstrap 5 (tema oscuro), Bootstrap Icons. Sin Composer — autoload manual vía `spl_autoload_register`.

**Entorno local:** Laragon en Windows. URL local: `http://localhost/Publicidad`. DB: `clasificados_adultos`.

---

## Arquitectura

```
/
├── index.php              # Front controller — arranca el router
├── routes/web.php         # Tabla de rutas [METHOD, URI, Controller, método, middlewares]
├── config/
│   ├── config.php         # Constantes globales (APP_URL, DB_*, UPLOAD_*, etc.)
│   ├── database.php       # Singleton PDO
│   └── session.php        # SessionManager (get/set/flash/delete)
├── app/
│   ├── Router.php         # Resuelve rutas, aplica middlewares, despacha
│   ├── Controller.php     # Base: render(), redirect(), currentUser(), requireAuth()
│   ├── Model.php          # Base CRUD: find(), insert(), update(), delete(), paginate()
│   ├── Security.php       # Sanitización, CSRF, rate limiting, hashing, imgUrl()
│   ├── Upload.php         # Valida y guarda imágenes (MIME real, dimensiones, rename)
│   ├── Validator.php      # Reglas encadenables: required, minLength, noHtml, etc.
│   ├── Middleware.php     # Handlers: auth, guest, admin, csrf
│   ├── controllers/       # Un controller por dominio
│   ├── models/            # Un model por tabla principal
│   └── views/             # PHP templates organizados por sección
├── public/
│   └── assets/
│       ├── css/app.css    # CSS global (variables, dark theme, componentes)
│       └── js/app.js      # JS global (validación, confirmaciones, etc.)
└── uploads/
    └── anuncios/          # Imágenes físicas (.htaccess: Require all denied)
```

**Flujo de una request:**
`index.php` → `Router` → aplica middlewares → instancia Controller → llama método → `render()` inyecta variables en la vista → `layout.php` envuelve el HTML.

---

## Base de Datos

**DB name:** `clasificados_adultos`

| Tabla | Descripción |
|---|---|
| `usuarios` | Cuentas de usuario y admin |
| `anuncios` | Anuncios con estado, destacado, vistas |
| `anuncio_fotos` | Galería multi-foto por anuncio (tokens seguros) |
| `categorias` | Categorías de anuncios (ej. Masajes, Escorts) |
| `estados` | 32 estados de México |
| `municipios` | ~2,400 municipios ligados a estado |
| `ciudades` | Tabla legacy (no se usa en flujo nuevo) |
| `pagos` | Registro de pagos por plan de destacado |
| `reportes` | Reportes de usuarios sobre anuncios |
| `sesiones_login` | Log de inicios de sesión |

### Columnas clave de `anuncios`

- `estado`: `pendiente | publicado | rechazado | expirado`
- `imagen_principal`: nombre de archivo legacy (varchar)
- `imagen_token`: token de la foto principal (char 40) — nuevo sistema
- `destacado`: boolean + `fecha_expiracion_destacado`
- `id_estado` / `id_municipio`: FK al nuevo sistema geográfico

### Sistema de fotos (`anuncio_fotos`)

Cada foto tiene un `token` de 40 hex chars (`bin2hex(random_bytes(20))`). Las imágenes **nunca se sirven directamente** — solo a través del proxy `/img/{token}`. El directorio `uploads/anuncios/` tiene `Require all denied` en `.htaccess`.

Anuncios legacy (solo tienen `imagen_principal`): se migran automáticamente a `anuncio_fotos` la primera vez que se abre el formulario de edición.

---

## Controllers

| Controller | Responsabilidad |
|---|---|
| `HomeController` | Página principal: últimos anuncios, categorías con conteo |
| `AdsController` | CRUD de anuncios + búsqueda/filtros + reportes |
| `AuthController` | Registro, login, logout, recuperación de contraseña, age-gate |
| `UserController` | Dashboard de usuario, "Mis anuncios", toggle sin-anticipo |
| `AdminController` | Panel admin: moderar usuarios/anuncios/reportes/pagos |
| `PaymentController` | Flujo de destacado: planes → pago simulado → activación |
| `ImageController` | Proxy seguro de imágenes: valida token → sirve archivo con headers de seguridad |
| `ApiController` | Endpoints JSON: municipios por estado, listado y detalle de anuncios |
| `LegalController` | Páginas estáticas: términos, privacidad, aviso de mayores |

---

## Models

| Model | Métodos destacados |
|---|---|
| `AnuncioModel` | `crear()`, `editar()`, `listarPublicos()` (con JOINs de estado/municipio/categoría), `listarAdmin()`, `publicar()`, `activarDestacado()` |
| `UsuarioModel` | `crear()`, `findByEmail()`, `confiabilidad()` (score del perfil) |
| `FotoModel` | `porAnuncio()`, `porToken()`, `guardar()` (genera token), `eliminar()`, `reordenar()` |
| `CategoriaModel` | `activas()`, `conConteo()` (para homepage) |
| `EstadoModel` | `activos()` |
| `MunicipioModel` | `porEstado()`, `estadoPorNombre()` (reverse lookup) |
| `PagoModel` | `registrar()`, `porUsuario()`, `porAnuncio()` |
| `ReporteModel` | `crear()`, `pendientes()`, `resolver()` |

---

## Vistas

```
views/
├── partials/
│   ├── layout.php          # Shell HTML completo (head, navbar, footer, CSS/JS)
│   ├── navbar.php          # Barra de navegación responsive
│   ├── footer.php
│   ├── foto-uploader.php   # Widget reutilizable de carga múltiple de fotos (hasta 10)
│   └── 403/404/500.php
├── home/index.php          # Hero compacto + barra de búsqueda + categorías + anuncios recientes
├── ads/
│   ├── index.php           # Listado con sidebar de filtros + barra de búsqueda superior
│   ├── show.php            # Detalle: galería de fotos, descripción, contacto WA, relacionados
│   ├── create.php          # Formulario nuevo anuncio (cascada Estado→Municipio)
│   └── edit.php            # Formulario edición (pre-carga estado/municipio/fotos existentes)
├── auth/                   # login, register, recover, age-gate
├── user/
│   ├── dashboard.php       # Score de confiabilidad, estadísticas, acciones rápidas
│   └── my-ads.php          # Tabla de anuncios del usuario con acciones
├── admin/                  # dashboard, users, user-detail, ads, reports, payments
└── payment/plans.php       # Selección de plan de destacado
```

---

## Seguridad (clase `Security`)

- **XSS**: `Security::escape()` / función global `e()` en todas las vistas
- **CSRF**: token en sesión, validado por middleware `csrf` en todos los POST
- **Contraseñas**: bcrypt cost 12, `password_hash/verify/needs_rehash`
- **Rate limiting**: por sesión, configurable por acción (`checkRateLimit`)
- **Imágenes**: proxy `/img/{token}` — sin exposición de rutas. Headers: `X-Content-Type-Options`, `X-Frame-Options: DENY`, `Cache-Control: private`
- **Login**: bloqueo tras 5 intentos fallidos (15 min), log de IPs
- **Uploads**: validación MIME real con `finfo`, dimensiones mínimas 200x200, máx 5 MB

---

## Flujo de usuario típico

1. Visita `/` → ve age-gate si no ha confirmado edad
2. Explora anuncios con filtros (estado, municipio, categoría, búsqueda)
3. Se registra → cuenta queda en estado `pendiente` hasta aprobación admin
4. Crea anuncio → estado `pendiente` → admin lo revisa y publica
5. Puede destacar su anuncio pagando plan (3/7/15 días a $99/$199/$349 MXN)
6. Panel admin: `/admin` — moderar usuarios, anuncios, reportes y pagos

---

## Indicadores de confiabilidad del perfil

Calculados en `UsuarioModel::confiabilidad()`. Cada indicador da puntos:
- Correo verificado (cuenta aprobada)
- Documento de identidad verificado
- Fotos verificadas
- Política sin anticipo
- Teléfono de contacto registrado

Se muestran en la página de detalle del anuncio y en el dashboard del usuario.

---

## Variables de configuración importantes (`config/config.php`)

```php
APP_URL       = 'http://localhost/Publicidad'   // Cambiar en producción
APP_ENV       = 'development'                   // 'production' en prod
DB_NAME       = 'clasificados_adultos'
UPLOAD_MAX_SIZE = 5MB
ITEMS_PER_PAGE  = 20
PLANES_DESTACADO = [3=>$99, 7=>$199, 15=>$349]  // MXN
SESSION_LIFETIME = 7200 (2h)
LOGIN_MAX_ATTEMPTS = 5 (bloqueo 15 min)
```

---

## Pendientes / deuda técnica conocida

- **Pagos**: el flujo de pago es simulado (no hay pasarela real integrada)
- **Email**: la recuperación de contraseña no envía correo real (solo genera token)
- **columna `imagenes` (JSON)**: existe en `anuncios` pero no se usa — herencia de diseño anterior
- **`ciudades`**: tabla legacy que quedó de antes del sistema estado/municipio, no se usa en el flujo nuevo
- **`APP_URL` hardcodeada**: hay que cambiarla a la URL real al desplegar
- **`DB_USER/DB_PASS`**: credenciales root sin contraseña — solo para desarrollo local
