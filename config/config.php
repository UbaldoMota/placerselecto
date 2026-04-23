<?php
/**
 * config.php
 * Detecta automáticamente el entorno (local vs producción)
 * y carga las credenciales correspondientes.
 *
 * Las credenciales sensibles van en `config/env.local.php` (gitignored).
 * En producción, va en `config/env.production.php` (también gitignored).
 */

// Detectar entorno por hostname
$host = $_SERVER['HTTP_HOST'] ?? 'cli';
$isLocal = (
    $host === 'localhost'
    || str_starts_with($host, 'localhost:')
    || str_starts_with($host, '127.0.0.1')
    || str_ends_with($host, '.local')
    || str_ends_with($host, '.test')
);

define('APP_ENV', $isLocal ? 'development' : 'production');
define('APP_DEBUG', APP_ENV === 'development');

// Cargar credenciales del entorno (no se versionan en git)
$envFile = __DIR__ . '/env.' . APP_ENV . '.php';
if (!file_exists($envFile)) {
    // Fallback: si no existe el env, usar el opuesto (útil al desplegar la primera vez)
    $envFile = __DIR__ . '/env.' . (APP_ENV === 'production' ? 'development' : 'production') . '.php';
}
if (file_exists($envFile)) {
    $env = require $envFile;
} else {
    die('Falta config/env.' . APP_ENV . '.php — copia env.example.php y rellénalo.');
}

// Constantes públicas (iguales en ambos entornos)
define('APP_NAME', 'PlacerSelecto');
define('APP_VERSION', '1.0.0');

// Constantes derivadas del env
define('APP_URL',    rtrim($env['app_url'], '/'));
define('DB_HOST',    $env['db_host']    ?? 'localhost');
define('DB_PORT',    $env['db_port']    ?? '3306');
define('DB_NAME',    $env['db_name']);
define('DB_USER',    $env['db_user']);
define('DB_PASS',    $env['db_pass']);
define('DB_CHARSET', 'utf8mb4');

// Rutas de sistema
define('ROOT_PATH',    dirname(__DIR__));
define('APP_PATH',     ROOT_PATH . '/app');
define('CONFIG_PATH',  ROOT_PATH . '/config');
define('PUBLIC_PATH',  ROOT_PATH . '/public');
define('UPLOADS_PATH', ROOT_PATH . '/uploads');
define('LOGS_PATH',    ROOT_PATH . '/logs');
define('VIEWS_PATH',   APP_PATH  . '/views');

// Sesiones
define('SESSION_NAME',     'CLASIF_SESS');
define('SESSION_LIFETIME', 7200);
define('SESSION_SECURE',   !$isLocal);  // HTTPS sólo en producción
define('SESSION_HTTPONLY', true);
define('SESSION_SAMESITE', 'Lax');

// Seguridad
define('CSRF_TOKEN_NAME', '_csrf_token');
define('CSRF_TOKEN_TTL',  3600);
define('LOGIN_MAX_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME',  900);

// Uploads
define('UPLOAD_MAX_SIZE',    15 * 1024 * 1024); // 15 MB (fotos de iPhone suelen ser 3-8 MB)
define('UPLOAD_ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/webp']);
define('UPLOAD_ALLOWED_EXT',   ['jpg', 'jpeg', 'png', 'webp']);

define('ITEMS_PER_PAGE', 20);

define('PLANES_DESTACADO', [
    3  => ['precio' => 99.00,  'label' => '3 días'],
    7  => ['precio' => 199.00, 'label' => '7 días'],
    15 => ['precio' => 349.00, 'label' => '15 días'],
]);

define('WATERMARK_ENABLED', true);
define('WATERMARK_TEXT',    'PlacerSelecto.com');
define('WATERMARK_OPACITY', 75);

date_default_timezone_set('America/Mexico_City');

if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', LOGS_PATH . '/php_errors.log');
}
