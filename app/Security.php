<?php
/**
 * Security.php
 * Clase central de seguridad de la aplicación.
 * Agrupa: sanitización, detección de IP, rate limiting,
 * validación de contraseñas, protección de headers y utilidades criptográficas.
 */

class Security
{
    // ---------------------------------------------------------
    // SANITIZACIÓN Y ESCAPE
    // ---------------------------------------------------------

    /**
     * Escapa una cadena para salida HTML segura (previene XSS).
     */
    public static function escape(mixed $value): string
    {
        if ($value === null) {
            return '';
        }
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Alias corto de escape() para usar en vistas: e($var)
     */
    public static function e(mixed $value): string
    {
        return self::escape($value);
    }

    /**
     * Sanitiza un string: elimina etiquetas HTML y espacios extremos.
     */
    public static function sanitizeString(string $input): string
    {
        return trim(strip_tags($input));
    }

    /**
     * Sanitiza un email: lowercasea y filtra caracteres inválidos.
     */
    public static function sanitizeEmail(string $email): string
    {
        return strtolower(trim(filter_var($email, FILTER_SANITIZE_EMAIL)));
    }

    /**
     * Sanitiza un número entero.
     */
    public static function sanitizeInt(mixed $value): int
    {
        return (int) filter_var($value, FILTER_SANITIZE_NUMBER_INT);
    }

    /**
     * Sanitiza un número decimal.
     */
    public static function sanitizeFloat(mixed $value): float
    {
        return (float) filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }

    /**
     * Sanitiza un array de $_POST o $_GET de forma recursiva.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public static function sanitizeArray(array $data): array
    {
        $clean = [];
        foreach ($data as $key => $value) {
            $cleanKey = self::sanitizeString((string) $key);
            if (is_array($value)) {
                $clean[$cleanKey] = self::sanitizeArray($value);
            } else {
                $clean[$cleanKey] = self::sanitizeString((string) $value);
            }
        }
        return $clean;
    }

    /**
     * Elimina caracteres de control y normaliza espacios en blanco.
     */
    public static function sanitizeText(string $input): string
    {
        // Eliminar caracteres de control (excepto saltos de línea normales)
        $input = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $input);
        // Normalizar espacios múltiples
        $input = preg_replace('/[ \t]+/', ' ', $input);
        return trim($input);
    }

    /**
     * Sanitiza HTML de formato (editor rico).
     * Permite solo etiquetas de presentación seguras; elimina scripts y atributos peligrosos.
     */
    public static function sanitizeHtml(string $input): string
    {
        // Etiquetas de formato seguras que genera Quill
        $allowed = '<p><br><strong><b><em><i><u><s><span><ul><ol><li><h2><h3><blockquote>';
        $clean   = strip_tags($input, $allowed);
        // Eliminar manejadores de eventos y javascript:
        $clean   = preg_replace('/\s+on\w+\s*=\s*(?:"[^"]*"|\'[^\']*\')/i', '', $clean);
        $clean   = preg_replace('/javascript\s*:/i', 'blocked:', $clean);
        // Eliminar caracteres de control
        $clean   = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $clean);
        return trim($clean);
    }

    /**
     * Sanitiza un número de teléfono: solo dígitos, +, -, espacios y paréntesis.
     */
    public static function sanitizePhone(string $phone): string
    {
        return preg_replace('/[^\d\+\-\s\(\)]/', '', trim($phone));
    }

    // ---------------------------------------------------------
    // VALIDACIÓN
    // ---------------------------------------------------------

    /**
     * Valida formato de email.
     */
    public static function isValidEmail(string $email): bool
    {
        return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Valida fortaleza de contraseña.
     * Requisitos: mínimo 8 chars, al menos 1 mayúscula, 1 minúscula, 1 número.
     *
     * @return array<string> Errores (vacío = válida)
     */
    public static function validatePassword(string $password): array
    {
        $errors = [];

        if (strlen($password) < 8) {
            $errors[] = 'La contraseña debe tener al menos 8 caracteres.';
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'La contraseña debe contener al menos una letra mayúscula.';
        }
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'La contraseña debe contener al menos una letra minúscula.';
        }
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'La contraseña debe contener al menos un número.';
        }

        return $errors;
    }

    /**
     * Valida que un nombre no contenga caracteres peligrosos.
     */
    public static function isValidName(string $name): bool
    {
        $name = trim($name);
        return strlen($name) >= 2
            && strlen($name) <= 120
            && !preg_match('/<|>|&quot;|&#|javascript:/i', $name);
    }

    /**
     * Valida un número de teléfono básico (7-20 dígitos).
     */
    public static function isValidPhone(string $phone): bool
    {
        $digits = preg_replace('/\D/', '', $phone);
        return strlen($digits) >= 7 && strlen($digits) <= 20;
    }

    /**
     * Verifica que un valor sea un entero positivo válido.
     */
    public static function isPositiveInt(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false && (int) $value > 0;
    }

    // ---------------------------------------------------------
    // DETECCIÓN DE IP
    // ---------------------------------------------------------

    /**
     * Obtiene la IP real del cliente, considerando proxies confiables.
     * NOTA: En producción tras un proxy/CDN, ajustar los headers confiables.
     */
    public static function getClientIp(): string
    {
        // Lista de headers a revisar en orden de confianza
        $headers = [
            'HTTP_CF_CONNECTING_IP',    // Cloudflare
            'HTTP_X_FORWARDED_FOR',     // Proxy estándar
            'HTTP_X_REAL_IP',           // Nginx
            'HTTP_CLIENT_IP',
            'REMOTE_ADDR',              // Directo (siempre disponible)
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                // X-Forwarded-For puede contener múltiples IPs separadas por coma
                $ip = trim(explode(',', $_SERVER[$header])[0]);

                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        // Fallback: REMOTE_ADDR (puede ser IP privada en localhost)
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    // ---------------------------------------------------------
    // TOKENS Y HASHES
    // ---------------------------------------------------------

    /**
     * Genera un token criptográficamente seguro.
     *
     * @param int $bytes Longitud en bytes (el token resultante tiene el doble de chars en hex)
     */
    public static function generateToken(int $bytes = 32): string
    {
        return bin2hex(random_bytes($bytes));
    }

    /**
     * Comparación segura de strings (time-safe, previene timing attacks).
     */
    public static function safeCompare(string $known, string $userInput): bool
    {
        return hash_equals($known, $userInput);
    }

    /**
     * Genera un hash de contraseña con bcrypt costo 12.
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    /**
     * Verifica una contraseña contra su hash.
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Verifica si un hash necesita ser re-generado (bcrypt cost update).
     */
    public static function needsRehash(string $hash): bool
    {
        return password_needs_rehash($hash, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    // ---------------------------------------------------------
    // RATE LIMITING (basado en sesión, sin Redis)
    // ---------------------------------------------------------

    /**
     * Verifica y registra un intento de acción con rate limiting por sesión.
     *
     * @param string $action    Identificador de la acción (ej. 'login', 'register')
     * @param int    $maxTries  Máximo de intentos permitidos
     * @param int    $windowSec Ventana de tiempo en segundos
     * @return bool true = permitido, false = bloqueado
     */
    public static function checkRateLimit(string $action, int $maxTries, int $windowSec): bool
    {
        $key    = '_rl_' . $action;
        $now    = time();
        $data   = SessionManager::get($key, ['count' => 0, 'reset_at' => $now + $windowSec]);

        // Si venció la ventana, resetear
        if ($now > $data['reset_at']) {
            $data = ['count' => 0, 'reset_at' => $now + $windowSec];
        }

        $data['count']++;
        SessionManager::set($key, $data);

        return $data['count'] <= $maxTries;
    }

    /**
     * Resetea el contador de rate limiting de una acción.
     */
    public static function resetRateLimit(string $action): void
    {
        SessionManager::delete('_rl_' . $action);
    }

    /**
     * Retorna los segundos restantes para que expire el bloqueo.
     */
    public static function rateLimitTtl(string $action): int
    {
        $key  = '_rl_' . $action;
        $data = SessionManager::get($key);
        if (!$data) {
            return 0;
        }
        return max(0, $data['reset_at'] - time());
    }

    // ---------------------------------------------------------
    // PROTECCIÓN ADICIONAL DE HEADERS
    // ---------------------------------------------------------

    /**
     * Envía cabeceras de seguridad adicionales para respuestas JSON.
     */
    public static function setJsonHeaders(): void
    {
        header('Content-Type: application/json; charset=UTF-8');
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: no-store, no-cache, must-revalidate');
    }

    /**
     * Envía cabeceras para prevenir cacheo de páginas autenticadas.
     */
    public static function setNoCacheHeaders(): void
    {
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
    }

    // ---------------------------------------------------------
    // UTILIDADES
    // ---------------------------------------------------------

    /**
     * Genera un slug URL-amigable a partir de un string.
     */
    public static function slug(string $text): string
    {
        $text = mb_strtolower(trim($text), 'UTF-8');
        // Transliterar caracteres con acento
        $map = [
            'á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u',
            'à'=>'a','è'=>'e','ì'=>'i','ò'=>'o','ù'=>'u',
            'ä'=>'a','ë'=>'e','ï'=>'i','ö'=>'o','ü'=>'u',
            'ñ'=>'n','ç'=>'c','ß'=>'ss',
        ];
        $text = strtr($text, $map);
        $text = preg_replace('/[^a-z0-9\s\-]/', '', $text);
        $text = preg_replace('/[\s\-]+/', '-', $text);
        return trim($text, '-');
    }

    /**
     * Trunca un texto a N caracteres añadiendo "..." si es necesario.
     */
    public static function truncate(string $text, int $max = 150): string
    {
        $text = strip_tags($text);
        if (mb_strlen($text, 'UTF-8') <= $max) {
            return $text;
        }
        return mb_substr($text, 0, $max, 'UTF-8') . '...';
    }

    /**
     * Formatea un número como precio en MXN.
     */
    public static function formatMoney(float $amount, string $currency = 'MXN'): string
    {
        return '$' . number_format($amount, 2) . ' ' . $currency;
    }

    /**
     * Genera la URL segura de una imagen de anuncio.
     * Si tiene imagen_token usa el proxy /img/{token}.
     * Si sólo tiene imagen_principal usa la URL directa (anuncios legacy).
     * Retorna null si no hay imagen.
     *
     * @param array $row  Fila del anuncio (debe tener imagen_token y/o imagen_principal)
     */
    public static function imgUrl(array $row): ?string
    {
        if (!empty($row['imagen_token'])) {
            return APP_URL . '/img/' . $row['imagen_token'];
        }
        if (!empty($row['imagen_principal'])) {
            // Fallback para anuncios anteriores al sistema de tokens
            return APP_URL . '/uploads/anuncios/' . $row['imagen_principal'];
        }
        return null;
    }

    /**
     * Retorna la diferencia de tiempo en formato legible.
     * Ej: "hace 3 horas", "hace 2 días"
     */
    public static function timeAgo(string $datetime): string
    {
        $diff = time() - strtotime($datetime);

        if ($diff < 60)      return 'hace un momento';
        if ($diff < 3600)    return 'hace ' . floor($diff / 60) . ' min';
        if ($diff < 86400)   return 'hace ' . floor($diff / 3600) . ' h';
        if ($diff < 604800)  return 'hace ' . floor($diff / 86400) . ' días';
        if ($diff < 2592000) return 'hace ' . floor($diff / 604800) . ' semanas';

        return date('d/m/Y', strtotime($datetime));
    }

    /**
     * Verifica si una URL es segura (protocolo HTTPS o HTTP relativo).
     */
    public static function isSafeUrl(string $url): bool
    {
        if (str_starts_with($url, '/')) {
            return true; // Relativa interna
        }
        $parsed = parse_url($url);
        return isset($parsed['scheme']) && in_array($parsed['scheme'], ['http', 'https'], true);
    }
}

// Función global de escape para vistas (atajo de Security::e())
function e(mixed $value): string
{
    return Security::e($value);
}
