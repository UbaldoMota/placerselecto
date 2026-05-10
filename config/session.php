<?php
/**
 * session.php
 * Configura e inicializa la sesión de forma segura.
 * Se llama UNA SOLA VEZ desde index.php antes de cualquier output.
 */

class SessionManager
{
    /**
     * Inicializa la sesión con configuración segura.
     * Debe llamarse antes de session_start().
     */
    public static function init(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return; // Ya iniciada
        }

        // Lifetime efectivo: lee del cookie 'remember_me' para saber si es long-term
        $isLong   = !empty($_COOKIE['remember_me']);
        $lifetime = $isLong ? SESSION_LIFETIME_LONG : SESSION_LIFETIME;

        // Storage propio fuera del save_path global de PHP. Crítico en shared
        // hosting porque el GC global borra archivos de sesion mas viejos que el
        // session.gc_maxlifetime del SISTEMA (a veces 1440s/24min), ignorando
        // nuestro ini_set. Con un save_path propio, solo el GC de procesos PHP
        // que corren bajo NUESTRA cuenta toca estos archivos.
        if (defined('ROOT_PATH')) {
            $sessionDir = ROOT_PATH . '/sessions';
            if (!is_dir($sessionDir)) @mkdir($sessionDir, 0700, true);
            if (is_dir($sessionDir) && is_writable($sessionDir)) {
                @ini_set('session.save_path', $sessionDir);
            }
        }

        // Permitir que PHP conserve la sesión server-side hasta el max
        @ini_set('session.gc_maxlifetime', (string) SESSION_LIFETIME_LONG);
        // Bajar la probabilidad de GC en CADA request — el GC corre 1 de cada
        // 1000 requests aprox. (gc_probability/gc_divisor = 1/1000).
        @ini_set('session.gc_probability', '1');
        @ini_set('session.gc_divisor',     '1000');

        // Configurar cookies de sesión de forma segura
        session_set_cookie_params([
            'lifetime' => $lifetime,
            'path'     => '/',
            'domain'   => '',
            'secure'   => SESSION_SECURE,
            'httponly' => SESSION_HTTPONLY,
            'samesite' => SESSION_SAMESITE,
        ]);

        session_name(SESSION_NAME);
        session_start();

        // Regenerar ID de sesión periódicamente para prevenir session fixation
        if (!isset($_SESSION['_initiated'])) {
            session_regenerate_id(true);
            $_SESSION['_initiated'] = true;
            $_SESSION['_created']   = time();
        }

        // Expirar sesión si superó el lifetime efectivo
        if (isset($_SESSION['_created']) && (time() - $_SESSION['_created']) > $lifetime) {
            self::destroy();
            self::init();
        }
    }

    /**
     * Promueve la sesión actual a "long-term" (30 días) — se usa tras login con "recordarme".
     * Debe llamarse antes de cualquier output.
     */
    public static function promoteToLongTerm(): void
    {
        // Cookie accesoria que indica que la sesión es long-term
        setcookie('remember_me', '1', [
            'expires'  => time() + SESSION_LIFETIME_LONG,
            'path'     => '/',
            'secure'   => SESSION_SECURE,
            'httponly' => true,
            'samesite' => SESSION_SAMESITE,
        ]);
        // Re-emitir la cookie de sesión con nueva expiración
        if (session_status() === PHP_SESSION_ACTIVE) {
            setcookie(session_name(), session_id(), [
                'expires'  => time() + SESSION_LIFETIME_LONG,
                'path'     => '/',
                'secure'   => SESSION_SECURE,
                'httponly' => SESSION_HTTPONLY,
                'samesite' => SESSION_SAMESITE,
            ]);
            // Reiniciar el reloj de expiración interno: el chequeo de init()
            // usa _created vs lifetime efectivo. Al promover, queremos los 30
            // días completos desde este momento, no desde la creación inicial.
            $_SESSION['_created']    = time();
            $_SESSION['_long_term']  = true;
        }
    }

    /**
     * Limpia el marcador de "recordarme" (al hacer logout).
     */
    public static function forgetLongTerm(): void
    {
        setcookie('remember_me', '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'secure'   => SESSION_SECURE,
            'httponly' => true,
            'samesite' => SESSION_SAMESITE,
        ]);
    }

    /**
     * Destruye la sesión por completo.
     */
    public static function destroy(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    /**
     * Escribe un valor en sesión.
     */
    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Lee un valor de sesión.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Elimina una clave de sesión.
     */
    public static function delete(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * Verifica si existe una clave en sesión.
     */
    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Guarda un mensaje flash (se muestra una sola vez).
     */
    public static function flash(string $type, string $message): void
    {
        $_SESSION['_flash'][$type][] = $message;
    }

    /**
     * Obtiene y elimina todos los mensajes flash de un tipo.
     *
     * @return array<string>
     */
    public static function getFlash(string $type): array
    {
        $messages = $_SESSION['_flash'][$type] ?? [];
        unset($_SESSION['_flash'][$type]);
        return $messages;
    }

    /**
     * Verifica si hay mensajes flash de cualquier tipo.
     */
    public static function hasFlash(string $type): bool
    {
        return !empty($_SESSION['_flash'][$type]);
    }

    /**
     * Retorna y limpia TODOS los mensajes flash.
     *
     * @return array<string, array<string>>
     */
    public static function getAllFlash(): array
    {
        $all = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
        return $all;
    }
}
