<?php
/**
 * SmsClient.php — wrapper sobre la API SaaS propia de SMS (test.alitter-soluciones.com).
 * Config viene de las constantes SMS_* en config/config.php (ver Guia_Integracion_API.md).
 *
 * Si SMS_ENABLED = false, no llama a la API y devuelve null (modo dev).
 * El caller (AuthController) debe interpretar null como fallback (mostrar código en pantalla).
 */
class SmsClient
{
    private const RETRYABLE  = [429, 500, 502, 503, 504];
    private const DELAYS_MS  = [1000, 2000, 4000];
    private const TIMEOUT    = 30;

    /**
     * Envía un SMS. Devuelve el array `data` de la respuesta en éxito, o null si falla.
     * No lanza excepciones — el envío de SMS es secundario en el flujo de registro.
     */
    public static function send(string $destino, string $mensaje, ?string $referencia = null): ?array
    {
        if (!defined('SMS_ENABLED') || !SMS_ENABLED) {
            error_log("[SMS-DEV] to={$destino} ref={$referencia}");
            return null;
        }

        $e164 = self::toE164($destino);
        if ($e164 === null) {
            error_log("[SMS-ERR] destino no normalizable a E.164: {$destino}");
            return null;
        }

        $apiKey  = defined('SMS_API_KEY')  ? SMS_API_KEY  : '';
        $baseUrl = defined('SMS_BASE_URL') ? rtrim(SMS_BASE_URL, '/') : '';
        if ($apiKey === '' || $baseUrl === '') {
            error_log('[SMS-ERR] SMS_API_KEY o SMS_BASE_URL vacíos');
            return null;
        }

        $idem    = bin2hex(random_bytes(16));
        $payload = json_encode([
            'destino'    => $e164,
            'mensaje'    => $mensaje,
            'referencia' => $referencia,
        ], JSON_UNESCAPED_UNICODE);

        for ($attempt = 0; $attempt <= count(self::DELAYS_MS); $attempt++) {
            $ch = curl_init("{$baseUrl}/api/sms/enviar");
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $payload,
                CURLOPT_TIMEOUT        => self::TIMEOUT,
                CURLOPT_HTTPHEADER     => [
                    "Authorization: Bearer {$apiKey}",
                    'Content-Type: application/json',
                    "Idempotency-Key: {$idem}",
                ],
            ]);
            $body   = curl_exec($ch);
            $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $err    = curl_error($ch);
            curl_close($ch);

            if ($body === false) {
                if ($attempt < count(self::DELAYS_MS)) {
                    usleep(self::DELAYS_MS[$attempt] * 1000);
                    continue;
                }
                error_log("[SMS-ERR] network: {$err}");
                return null;
            }

            $data = json_decode($body, true);

            if ($status === 200 && !empty($data['success'])) {
                $d = $data['data'] ?? [];
                error_log(sprintf(
                    '[SMS-OK] id=%s sid=%s costo=%s ref=%s',
                    $d['mensajeId']    ?? '?',
                    $d['sidProveedor'] ?? '?',
                    $d['costoTotal']   ?? '?',
                    $referencia        ?? '-'
                ));
                return $d;
            }

            // Retry solo en 429/5xx
            if (in_array($status, self::RETRYABLE, true) && $attempt < count(self::DELAYS_MS)) {
                usleep(self::DELAYS_MS[$attempt] * 1000);
                continue;
            }

            // Error definitivo (4xx no retryable o agotamos retries)
            error_log(sprintf(
                '[SMS-ERR] http=%d code=%s msg=%s reqId=%s',
                $status,
                $data['errorCode']  ?? '-',
                $data['message']    ?? 'sin mensaje',
                $data['requestId']  ?? '-'
            ));
            return null;
        }

        return null;
    }

    /**
     * Normaliza un teléfono al formato E.164 (`+` + país + número).
     * Asume México (+52) si no se da prefijo de país y el número tiene 10 dígitos.
     * Devuelve null si el formato no es reconocible.
     */
    public static function toE164(string $phone, string $defaultCountry = '52'): ?string
    {
        $digits = preg_replace('/\D+/', '', $phone);
        if ($digits === '' || $digits === null) {
            return null;
        }

        // Si el original empezaba con +, ya tiene prefijo de país
        if (str_starts_with(trim($phone), '+')) {
            $candidate = '+' . $digits;
        } elseif (strlen($digits) === 10) {
            // 10 dígitos sin prefijo → asumir México
            $candidate = '+' . $defaultCountry . $digits;
        } elseif (str_starts_with($digits, '52') && strlen($digits) === 12) {
            // Ya tiene 52 al inicio, agregar +
            $candidate = '+' . $digits;
        } else {
            $candidate = '+' . $digits;
        }

        // Validar contra el regex E.164 oficial
        if (!preg_match('/^\+[1-9]\d{6,14}$/', $candidate)) {
            return null;
        }
        return $candidate;
    }
}
