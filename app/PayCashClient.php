<?php
/**
 * PayCashClient.php — wrapper sobre PayCash (pago en efectivo: Walmart, 7Eleven, Soriana, Santander).
 *
 * FASE A: stub — genera referencia simulada localmente.
 * FASE B: conectar al API real (probable agregador: MULTIVA, Openpay o equivalente).
 *
 * Para activar producción:
 *   1. Llenar PAYCASH_ENABLED, PAYCASH_API_KEY, PAYCASH_SECRET, PAYCASH_BASE_URL en config/env.production.php
 *   2. Reemplazar createPayment() con POST al endpoint del proveedor que genera la referencia
 *   3. Reemplazar verifyWebhook() con la verificación HMAC real
 */
class PayCashClient
{
    public const VIGENCIA_DIAS = 7;

    /**
     * Genera una referencia de pago en efectivo. Retorna:
     *   ['reference' => string, 'barcode' => string, 'expira_en' => 'YYYY-MM-DD HH:MM:SS']
     */
    public static function createPayment(int $idPago, float $monto, string $emailCliente): array
    {
        $expira = date('Y-m-d H:i:s', strtotime('+' . self::VIGENCIA_DIAS . ' days'));

        if (defined('PAYCASH_ENABLED') && PAYCASH_ENABLED) {
            // PROD TODO: POST {PAYCASH_BASE_URL}/payments
            // Body: { amount, reference: idPago, customer_email, expires_at }
            // Respuesta: { reference_code, barcode_data, expires_at }
            error_log('[PayCashClient] PROD mode pero sin implementación — falló');
            return ['reference' => null, 'barcode' => null, 'expira_en' => $expira];
        }

        // STUB: referencia numérica de 14 dígitos (formato típico de tienda de conveniencia)
        $reference = str_pad((string)random_int(0, 99999999999999), 14, '0', STR_PAD_LEFT);
        // Barcode = mismo número (en producción sería data SVG/PNG codificado por la pasarela)
        return [
            'reference' => $reference,
            'barcode'   => $reference,
            'expira_en' => $expira,
        ];
    }

    public static function verifyWebhook(string $rawBody, array $headers): bool
    {
        // Si la pasarela no está habilitada, rechazar TODO webhook entrante.
        // De lo contrario, cualquiera podría POSTear a /webhook/paycash y acreditar tokens.
        if (!defined('PAYCASH_ENABLED') || !PAYCASH_ENABLED) {
            error_log('[PayCashClient] webhook recibido con PAYCASH_ENABLED=false — rechazado');
            return false;
        }
        // Header lookup case-insensitive (Apache normaliza a X-Signature, otros entornos pueden variar)
        $sig = $headers['X-Signature'] ?? $headers['x-signature'] ?? $headers['X-SIGNATURE'] ?? '';
        if ($sig === '') return false;
        $secret = defined('PAYCASH_SECRET') ? PAYCASH_SECRET : '';
        if ($secret === '') {
            error_log('[PayCashClient] PAYCASH_SECRET vacío con PAYCASH_ENABLED=true — config inválida');
            return false;
        }
        $expected = hash_hmac('sha256', $rawBody, $secret);
        return hash_equals($expected, $sig);
    }
}
