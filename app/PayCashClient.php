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
        $reference = str_pad((string)mt_rand(0, 99999999999999), 14, '0', STR_PAD_LEFT);
        // Barcode = mismo número (en producción sería data SVG/PNG codificado por la pasarela)
        return [
            'reference' => $reference,
            'barcode'   => $reference,
            'expira_en' => $expira,
        ];
    }

    public static function verifyWebhook(string $rawBody, array $headers): bool
    {
        if (defined('PAYCASH_ENABLED') && PAYCASH_ENABLED) {
            $sig = $headers['X-Signature'] ?? '';
            $expected = hash_hmac('sha256', $rawBody, defined('PAYCASH_SECRET') ? PAYCASH_SECRET : '');
            return hash_equals($expected, $sig);
        }
        return true;
    }
}
