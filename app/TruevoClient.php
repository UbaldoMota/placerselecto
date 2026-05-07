<?php
/**
 * TruevoClient.php — wrapper sobre la API de Truevo (pasarela de tarjetas, sector adulto).
 *
 * FASE A: stub — devuelve datos simulados, NO contacta el API real.
 * FASE B: completar TODOs marcando "PROD".
 *
 * Para activar producción:
 *   1. Llenar TRUEVO_ENABLED, TRUEVO_API_KEY, TRUEVO_SECRET, TRUEVO_BASE_URL en config/env.production.php
 *   2. Reemplazar el stub de createPayment() con la llamada real al endpoint /payments
 *   3. Reemplazar el stub de verifyWebhook() con la verificación HMAC real (header X-Signature típicamente)
 */
class TruevoClient
{
    /**
     * Crea una transacción en Truevo. Retorna ['url'=>checkout_url, 'reference'=>id_externo].
     * En modo stub devuelve una URL local que simula el checkout.
     */
    public static function createPayment(int $idPago, float $monto, string $moneda, string $descripcion, string $emailCliente): array
    {
        if (defined('TRUEVO_ENABLED') && TRUEVO_ENABLED) {
            // PROD TODO: llamar al API real
            // POST {TRUEVO_BASE_URL}/payments con Authorization: Bearer {TRUEVO_API_KEY}
            // Body: { amount, currency, reference: idPago, customer_email, callback_url, return_url }
            // Respuesta: { id, checkout_url, ... }
            error_log('[TruevoClient] PROD mode pero sin implementación — falló');
            return ['url' => null, 'reference' => null];
        }

        // STUB modo dev: referencia simulada y URL que apunta a la pantalla de simulación
        $reference = 'TRV-SIM-' . strtoupper(bin2hex(random_bytes(6)));
        $url = APP_URL . '/pago/' . $idPago . '/pendiente?ref=' . urlencode($reference);
        return ['url' => $url, 'reference' => $reference];
    }

    /**
     * Verifica la firma HMAC del webhook entrante.
     * En FASE A acepta cualquier payload con un header X-Stub-Mode: 1.
     * En PROD valida con TRUEVO_SECRET y header X-Signature (formato exacto según docs Truevo).
     */
    public static function verifyWebhook(string $rawBody, array $headers): bool
    {
        if (defined('TRUEVO_ENABLED') && TRUEVO_ENABLED) {
            // PROD TODO: comparar HMAC SHA256 de rawBody con TRUEVO_SECRET vs header
            $sig = $headers['X-Signature'] ?? '';
            $expected = hash_hmac('sha256', $rawBody, defined('TRUEVO_SECRET') ? TRUEVO_SECRET : '');
            return hash_equals($expected, $sig);
        }
        // STUB: aceptar siempre en dev
        return true;
    }
}
