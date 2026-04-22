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

        // Configurar cookies de sesión de forma segura
        session_set_cookie_params([
            'lifetime' => SESSION_LIFETIME,
            'path'     => '/',
            'domain'   => '',
            'secure'   => SESSION_SECURE,   // true en HTTPS
            'httponly' => SESSION_HTTPONLY,  // No accesible desde JS
            'samesite' => SESSION_SAMESITE,  // Protección CSRF básica
        ]);

        session_name(SESSION_NAME);
        session_start();

        // Regenerar ID de sesión periódicamente para prevenir session fixation
        if (!isset($_SESSION['_initiated'])) {
            session_regenerate_id(true);
            $_SESSION['_initiated'] = true;
            $_SESSION['_created']   = time();
        }

        // Expirar sesión si superó el lifetime
        if (isset($_SESSION['_created']) && (time() - $_SESSION['_created']) > SESSION_LIFETIME) {
            self::destroy();
            self::init(); // Iniciar sesión nueva
        }
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
