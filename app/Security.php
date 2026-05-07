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
     * Rangos oficiales de Cloudflare. Fuente: https://www.cloudflare.com/ips/
     * Casi nunca cambian; revisar 1-2 veces al año.
     */
    private const CLOUDFLARE_IPV4_RANGES = [
        '173.245.48.0/20',
        '103.21.244.0/22',
        '103.22.200.0/22',
        '103.31.4.0/22',
        '141.101.64.0/18',
        '108.162.192.0/18',
        '190.93.240.0/20',
        '188.114.96.0/20',
        '197.234.240.0/22',
        '198.41.128.0/17',
        '162.158.0.0/15',
        '104.16.0.0/13',
        '104.24.0.0/14',
        '172.64.0.0/13',
        '131.0.72.0/22',
    ];

    private const CLOUDFLARE_IPV6_RANGES = [
        '2400:cb00::/32',
        '2606:4700::/32',
        '2803:f800::/32',
        '2405:b500::/32',
        '2405:8100::/32',
        '2a06:98c0::/29',
        '2c0f:f248::/32',
    ];

    /**
     * Obtiene la IP real del cliente.
     *
     * Estrategia: si REMOTE_ADDR pertenece a un rango de Cloudflare, confiar en
     * el header CF-Connecting-IP. En cualquier otro caso (local, dev, hits directos
     * al origen) se usa REMOTE_ADDR. Esto evita que un atacante que conozca la IP
     * del origen pueda falsificar la IP del cliente con un header forjado para
     * burlar rate limiting o el bloqueo de login.
     */
    public static function getClientIp(): string
    {
        $remote = $_SERVER['REMOTE_ADDR'] ?? '';

        if ($remote !== ''
            && self::isCloudflareIp($remote)
            && !empty($_SERVER['HTTP_CF_CONNECTING_IP'])
        ) {
            $cfIp = trim($_SERVER['HTTP_CF_CONNECTING_IP']);
            if (filter_var($cfIp, FILTER_VALIDATE_IP)) {
                return $cfIp;
            }
        }

        if ($remote !== '' && filter_var($remote, FILTER_VALIDATE_IP)) {
            return $remote;
        }

        return '0.0.0.0';
    }

    /**
     * ¿La IP dada está dentro de algún rango oficial de Cloudflare?
     */
    public static function isCloudflareIp(string $ip): bool
    {
        $packed = @inet_pton($ip);
        if ($packed === false) return false;

        $ranges = strlen($packed) === 4
            ? self::CLOUDFLARE_IPV4_RANGES
            : self::CLOUDFLARE_IPV6_RANGES;

        foreach ($ranges as $cidr) {
            if (self::ipInCidr($packed, $cidr)) return true;
        }
        return false;
    }

    private static function ipInCidr(string $packedIp, string $cidr): bool
    {
        [$subnet, $maskBits] = explode('/', $cidr);
        $packedSubnet = @inet_pton($subnet);
        if ($packedSubnet === false || strlen($packedSubnet) !== strlen($packedIp)) {
            return false;
        }

        $maskBits = (int) $maskBits;
        $bytesFull = intdiv($maskBits, 8);
        $bitsLeft  = $maskBits % 8;

        if ($bytesFull > 0
            && substr($packedIp, 0, $bytesFull) !== substr($packedSubnet, 0, $bytesFull)
        ) {
            return false;
        }
        if ($bitsLeft > 0) {
            $maskByte = (0xFF << (8 - $bitsLeft)) & 0xFF;
            $a = ord($packedIp[$bytesFull])     & $maskByte;
            $b = ord($packedSubnet[$bytesFull]) & $maskByte;
            if ($a !== $b) return false;
        }
        return true;
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
     * URL de Telegram para un perfil. Prioridad:
     *   1. flag telegram_usar_whatsapp + whatsapp → t.me/+{digits}
     *   2. username explícito                    → t.me/{username}
     *   3. solo whatsapp (auto-fallback)         → t.me/+{digits}
     *   4. nada                                  → null
     */
    public static function telegramUrl(array $perfil): ?string
    {
        $usaWa = !empty($perfil['telegram_usar_whatsapp']);
        $wa    = $perfil['whatsapp'] ?? '';
        $tg    = trim((string)($perfil['telegram'] ?? ''));

        if (($usaWa || $tg === '') && $wa !== '') {
            $digits = preg_replace('/\D/', '', $wa);
            return $digits !== '' ? 'https://t.me/+' . $digits : null;
        }
        if ($tg !== '') {
            return 'https://t.me/' . ltrim($tg, '@');
        }
        return null;
    }

    /**
     * Etiqueta legible de Telegram (para mostrar bajo el botón).
     * Devuelve "@usuario" o "+5215512345678" según corresponda.
     */
    public static function telegramHandle(array $perfil): ?string
    {
        $usaWa = !empty($perfil['telegram_usar_whatsapp']);
        $wa    = $perfil['whatsapp'] ?? '';
        $tg    = trim((string)($perfil['telegram'] ?? ''));

        if (($usaWa || $tg === '') && $wa !== '') {
            $digits = preg_replace('/\D/', '', $wa);
            return $digits !== '' ? '+' . $digits : null;
        }
        if ($tg !== '') {
            return '@' . ltrim($tg, '@');
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
