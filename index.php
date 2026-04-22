<?php
/**
 * index.php
 * Punto de entrada único (Front Controller) de la aplicación.
 * Toda petición HTTP pasa por aquí gracias al .htaccess.
 *
 * Orden de arranque:
 *   1. Configuración global
 *   2. Autoload de clases base
 *   3. Sesión segura
 *   4. Conexión a BD (singleton, se instancia cuando el modelo lo necesite)
 *   5. Router + Middlewares
 *   6. Dispatch
 */

declare(strict_types=1);

// ---------------------------------------------------------
// 1. CONFIGURACIÓN
// ---------------------------------------------------------
require_once __DIR__ . '/config/config.php';

// ---------------------------------------------------------
// 2. CLASES BASE CRÍTICAS (carga explícita antes del autoloader)
//    Security debe cargarse aquí para que la función global e() esté disponible
// ---------------------------------------------------------
require_once __DIR__ . '/app/Security.php';

// ---------------------------------------------------------
// 3. AUTOLOAD MANUAL DEL RESTO DE CLASES
// ---------------------------------------------------------
spl_autoload_register(function (string $class): void {
    $map = [
        'Database'       => CONFIG_PATH . '/database.php',
        'SessionManager' => CONFIG_PATH . '/session.php',
        'Router'         => APP_PATH    . '/Router.php',
        'Middleware'     => APP_PATH    . '/Middleware.php',
        'Controller'     => APP_PATH    . '/Controller.php',
        'Model'          => APP_PATH    . '/Model.php',
        'Security'       => APP_PATH    . '/Security.php',
        'Validator'      => APP_PATH    . '/Validator.php',
        'Upload'         => APP_PATH    . '/Upload.php',
    ];

    if (isset($map[$class])) {
        require_once $map[$class];
        return;
    }

    // Buscar automáticamente en controllers/ y models/
    $paths = [
        APP_PATH . '/controllers/' . $class . '.php',
        APP_PATH . '/models/'      . $class . '.php',
    ];

    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// ---------------------------------------------------------
// 4. SESIÓN SEGURA
// ---------------------------------------------------------
SessionManager::init();

// Refrescar datos volátiles del usuario desde BD (evita sesión stale
// cuando el admin modifica estado_verificacion / verificado / rol).
// Es una query indexada por PK — barata. Solo corre si hay sesión.
if (SessionManager::has('user_id') && !str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/img/')) {
    try {
        $db   = Database::getInstance()->getConnection();
        $stmt = $db->prepare(
            "SELECT rol, verificado, estado_verificacion
             FROM usuarios WHERE id = ? LIMIT 1"
        );
        $stmt->execute([(int) SessionManager::get('user_id')]);
        $u = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($u) {
            SessionManager::set('user_rol',                 $u['rol']);
            SessionManager::set('user_verificado',          (bool) $u['verificado']);
            SessionManager::set('user_estado_verificacion', $u['estado_verificacion']);
        } else {
            // Usuario eliminado en BD → cerrar sesión
            SessionManager::destroy();
            SessionManager::init();
            SessionManager::set('age_verified', true);
        }
    } catch (\Throwable $e) {
        error_log('[index] refresh user session failed: ' . $e->getMessage());
    }
}

// ---------------------------------------------------------
// 5. CABECERAS DE SEGURIDAD GLOBALES
// ---------------------------------------------------------
// Remover CSP inyectado por el hosting (LiteSpeed/cPanel)
header_remove('Content-Security-Policy');
header_remove('Content-Security-Policy-Report-Only');
header("Content-Security-Policy: default-src 'self' https: data: blob:; script-src 'self' https: 'unsafe-inline' 'unsafe-eval'; style-src 'self' https: 'unsafe-inline'; img-src 'self' https: data: blob:; font-src 'self' https: data:; connect-src 'self' https:; frame-src 'self' https:;");

header('Content-Type: text/html; charset=UTF-8');
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
// Permitir cámara solo en las rutas de verificación por video
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$necesitaCamara = preg_match('#/verificar/(camara|video)$#', $uri)
               || $uri === '/verificacion/camara';
header('Permissions-Policy: geolocation=(), microphone=()' . ($necesitaCamara ? ', camera=self' : ', camera=()'));

// Content Security Policy permisivo (el hosting puede aplicar otro encima)
// Ya no definimos uno restrictivo aquí; confiamos en el del hosting o en el proxy de tiles.


// Remover cabeceras que revelan tecnología
header_remove('X-Powered-By');

// ---------------------------------------------------------
// 6. VERIFICACIÓN DE MAYORÍA DE EDAD (Age Gate)
//    Si el usuario no confirmó su edad → redirigir (excepto rutas exentas)
// ---------------------------------------------------------
$exemptAgeRoutes = ['/verificar-edad', '/terminos', '/privacidad', '/mayores-18'];
$currentUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$basePath   = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
if ($basePath) {
    $currentUri = substr($currentUri, strlen($basePath));
}
$currentUri = '/' . ltrim($currentUri ?: '/', '/');

if (!SessionManager::get('age_verified') && !in_array($currentUri, $exemptAgeRoutes, true)) {
    // Guardar la URL de destino para redirigir tras confirmar edad.
    // Excluir recursos/endpoints de fondo y archivos estáticos — no son páginas navegables
    // y sobreescribirían el destino real al hacerse en background (polling, preloads, etc.).
    $esRecurso = str_starts_with($currentUri, '/img/')
              || str_starts_with($currentUri, '/video/')
              || str_starts_with($currentUri, '/api/')
              || str_starts_with($currentUri, '/tile/')
              || str_starts_with($currentUri, '/public/')
              || str_starts_with($currentUri, '/assets/')
              || str_starts_with($currentUri, '/sse/')
              || preg_match('/\.(css|js|map|png|jpe?g|webp|gif|svg|ico|woff2?|ttf|eot|json|xml|txt)$/i', $currentUri);

    if (!$esRecurso) {
        SessionManager::set('age_redirect', $currentUri);
    } elseif (!SessionManager::has('age_redirect')) {
        SessionManager::set('age_redirect', '/');
    }
    // Mostrar modal sobre la página en vez de redirigir
    $GLOBALS['show_age_gate'] = true;
}

// ---------------------------------------------------------
// 7. ROUTER
// ---------------------------------------------------------
$router = new Router();
$router->loadRoutes(ROOT_PATH . '/routes/web.php');
Middleware::register($router);

// ---------------------------------------------------------
// 8. DISPATCH
// ---------------------------------------------------------
try {
    $router->dispatch();
} catch (RuntimeException $e) {
    http_response_code(500);
    error_log('[APP] RuntimeException: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());

    if (APP_DEBUG) {
        echo '<pre style="background:#FEF2F2;color:#991B1B;border:1px solid #EF4444;padding:20px;font-family:monospace">';
        echo '<strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . "\n\n";
        echo '<strong>Archivo:</strong> ' . $e->getFile() . ' línea ' . $e->getLine() . "\n\n";
        echo '<strong>Stack trace:</strong>' . "\n" . htmlspecialchars($e->getTraceAsString());
        echo '</pre>';
    } else {
        require_once VIEWS_PATH . '/partials/500.php';
    }
} catch (Throwable $e) {
    http_response_code(500);
    error_log('[APP] Throwable: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());

    if (APP_DEBUG) {
        echo '<pre style="background:#FEF2F2;color:#991B1B;border:1px solid #EF4444;padding:20px;font-family:monospace">';
        echo '<strong>Fatal Error:</strong> ' . htmlspecialchars($e->getMessage()) . "\n\n";
        echo htmlspecialchars($e->getTraceAsString());
        echo '</pre>';
    } else {
        require_once VIEWS_PATH . '/partials/500.php';
    }
}
