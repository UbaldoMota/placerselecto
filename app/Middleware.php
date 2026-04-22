<?php
/**
 * Middleware.php
 * Registro y lógica de todos los middlewares del sistema.
 * Se registran en index.php sobre el Router.
 */

class Middleware
{
    /**
     * Registra todos los middlewares sobre el router dado.
     *
     * @param Router $router
     */
    public static function register(Router $router): void
    {
        // ---- Middleware: auth ----------------------------------------
        // Verifica que el usuario esté autenticado.
        $router->registerMiddleware('auth', function (callable $next): void {
            if (!SessionManager::has('user_id')) {
                SessionManager::flash('error', 'Debes iniciar sesión para acceder.');
                header('Location: ' . APP_URL . '/login');
                exit;
            }
            $next();
        });

        // ---- Middleware: guest ----------------------------------------
        // Bloquea acceso a usuarios ya autenticados (ej. login/registro).
        $router->registerMiddleware('guest', function (callable $next): void {
            if (SessionManager::has('user_id')) {
                $dest = SessionManager::get('user_rol') === 'comentarista' ? '/' : '/dashboard';
                header('Location: ' . APP_URL . $dest);
                exit;
            }
            $next();
        });

        // ---- Middleware: admin ----------------------------------------
        // Verifica que el usuario tenga rol 'admin'.
        $router->registerMiddleware('admin', function (callable $next): void {
            $rol = SessionManager::get('user_rol');
            if ($rol !== 'admin') {
                http_response_code(403);
                require VIEWS_PATH . '/partials/403.php';
                exit;
            }
            $next();
        });

        // ---- Middleware: csrf -----------------------------------------
        // Valida el token CSRF en peticiones POST/PUT/DELETE.
        $router->registerMiddleware('csrf', function (callable $next): void {
            $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

            if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
                $token  = $_POST[CSRF_TOKEN_NAME] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
                $stored = SessionManager::get('csrf_token');
                $expiry = SessionManager::get('csrf_token_expiry', 0);

                if (
                    empty($token)
                    || empty($stored)
                    || !hash_equals($stored, $token)
                    || time() > $expiry
                ) {
                    http_response_code(419);
                    SessionManager::flash('error', 'Token de seguridad inválido. Por favor recarga la página.');
                    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? APP_URL));
                    exit;
                }

                // Rotar token después de cada request POST exitoso
                self::regenerateCsrfToken();
            }

            $next();
        });
    }

    /**
     * Genera (o regenera) el token CSRF y lo guarda en sesión.
     * Debe llamarse al inicio de cada request GET para que las views lo tengan disponible.
     */
    public static function generateCsrfToken(): string
    {
        // Solo generar si no existe o está por vencer
        $expiry  = SessionManager::get('csrf_token_expiry', 0);
        $current = SessionManager::get('csrf_token', '');

        if (empty($current) || time() > ($expiry - 60)) {
            return self::regenerateCsrfToken();
        }

        return $current;
    }

    /**
     * Fuerza la regeneración del token CSRF.
     */
    public static function regenerateCsrfToken(): string
    {
        $token = bin2hex(random_bytes(32));
        SessionManager::set('csrf_token', $token);
        SessionManager::set('csrf_token_expiry', time() + CSRF_TOKEN_TTL);
        return $token;
    }

    /**
     * Retorna el campo HTML input oculto con el token CSRF.
     * Para usar dentro de cualquier <form>.
     */
    public static function csrfField(): string
    {
        $token = self::generateCsrfToken();
        return sprintf(
            '<input type="hidden" name="%s" value="%s">',
            htmlspecialchars(CSRF_TOKEN_NAME, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($token, ENT_QUOTES, 'UTF-8')
        );
    }
}
