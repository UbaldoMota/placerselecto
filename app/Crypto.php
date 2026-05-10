<?php
/**
 * Cifrado at-rest para archivos sensibles (documentos de identidad).
 * Usa AES-256-GCM (openssl_encrypt) — autenticado, FIPS-friendly, disponible
 * en todos los PHP con OpenSSL (siempre).
 *
 * Formato del archivo cifrado:
 *   [8 bytes "PSCRYPT1"][12 bytes nonce][16 bytes tag][N bytes ciphertext]
 *
 * Si el archivo no empieza con MAGIC, se asume legacy plaintext (compat de
 * transición). La migración convierte los legacy a cifrados.
 */
class Crypto
{
    private const MAGIC       = "PSCRYPT1";
    private const MAGIC_BYTES = 8;
    private const CIPHER      = 'aes-256-gcm';
    private const KEY_BYTES   = 32;
    private const NONCE_BYTES = 12;   // GCM standard IV size
    private const TAG_BYTES   = 16;

    private static function key(): string
    {
        $b64 = defined('DOC_ENCRYPTION_KEY') ? DOC_ENCRYPTION_KEY : '';
        if ($b64 === '') {
            throw new RuntimeException('DOC_ENCRYPTION_KEY no configurada en env');
        }
        $key = base64_decode($b64, true);
        if ($key === false || strlen($key) !== self::KEY_BYTES) {
            throw new RuntimeException('DOC_ENCRYPTION_KEY inválida (debe ser 32 bytes base64)');
        }
        return $key;
    }

    /**
     * Cifra un archivo en disco. Sobrescribe el original.
     * Si ya estaba cifrado, no doble-cifra.
     */
    public static function encryptFile(string $path): bool
    {
        if (!is_file($path)) return false;

        $plaintext = file_get_contents($path);
        if ($plaintext === false) return false;

        if (strncmp($plaintext, self::MAGIC, self::MAGIC_BYTES) === 0) {
            return true; // ya cifrado
        }

        $nonce = random_bytes(self::NONCE_BYTES);
        $tag   = '';
        $ciphertext = openssl_encrypt(
            $plaintext, self::CIPHER, self::key(),
            OPENSSL_RAW_DATA, $nonce, $tag
        );
        if ($ciphertext === false) return false;

        $blob = self::MAGIC . $nonce . $tag . $ciphertext;
        $ok   = file_put_contents($path, $blob, LOCK_EX) !== false;
        if ($ok) @chmod($path, 0644);
        return $ok;
    }

    /**
     * Lee y descifra. Si el archivo es legacy (sin MAGIC), retorna el contenido tal cual.
     * Devuelve null si el archivo no existe o el descifrado falla (tampered/wrong key).
     */
    public static function decryptFile(string $path): ?string
    {
        if (!is_file($path)) return null;

        $blob = file_get_contents($path);
        if ($blob === false) return null;

        if (strncmp($blob, self::MAGIC, self::MAGIC_BYTES) !== 0) {
            return $blob; // legacy plaintext
        }

        $offNonce = self::MAGIC_BYTES;
        $offTag   = $offNonce + self::NONCE_BYTES;
        $offCt    = $offTag   + self::TAG_BYTES;

        $nonce      = substr($blob, $offNonce, self::NONCE_BYTES);
        $tag        = substr($blob, $offTag,   self::TAG_BYTES);
        $ciphertext = substr($blob, $offCt);

        $plaintext = openssl_decrypt(
            $ciphertext, self::CIPHER, self::key(),
            OPENSSL_RAW_DATA, $nonce, $tag
        );
        return $plaintext === false ? null : $plaintext;
    }

    /**
     * ¿El archivo en disco ya está cifrado? (lee solo los primeros 8 bytes).
     */
    public static function isEncrypted(string $path): bool
    {
        $fp = @fopen($path, 'rb');
        if (!$fp) return false;
        $magic = fread($fp, self::MAGIC_BYTES);
        fclose($fp);
        return $magic === self::MAGIC;
    }
}
