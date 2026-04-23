<?php
/**
 * Mailer.php — wrapper simple sobre PHPMailer con SMTP autenticado.
 * Config viene de las constantes SMTP_* en config/config.php.
 */

require_once APP_PATH . '/lib/PHPMailer/Exception.php';
require_once APP_PATH . '/lib/PHPMailer/PHPMailer.php';
require_once APP_PATH . '/lib/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;

class Mailer
{
    /**
     * Envía un correo. Devuelve true si se entregó al servidor SMTP.
     * Si SMTP_ENABLED = false, solo loggea (modo dev).
     */
    public static function send(string $to, string $subject, string $htmlBody, ?string $altText = null): bool
    {
        $enabled = defined('SMTP_ENABLED') && SMTP_ENABLED;

        if (!$enabled) {
            error_log("[MAIL-DEV] to={$to} subject=\"{$subject}\"");
            return true; // en dev consideramos éxito
        }

        $mail = new PHPMailer(true);

        try {
            $mail->CharSet  = 'UTF-8';
            $mail->Encoding = PHPMailer::ENCODING_BASE64;

            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->Port       = SMTP_PORT;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USER;
            $mail->Password   = SMTP_PASS;
            $mail->SMTPSecure = SMTP_SECURE; // 'tls' | 'ssl'

            $mail->setFrom(SMTP_FROM, defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : APP_NAME);
            $mail->addAddress($to);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $htmlBody;
            $mail->AltBody = $altText ?? strip_tags($htmlBody);

            $mail->send();
            return true;
        } catch (MailException $e) {
            error_log('[MAIL-ERR] to=' . $to . ' err=' . $mail->ErrorInfo);
            return false;
        }
    }

    /**
     * Renderiza un template simple con reemplazo de tokens {{clave}} por valor.
     * Uso:  Mailer::render('codigo-verificacion', ['nombre' => 'Juan', 'codigo' => '123456']);
     */
    public static function render(string $template, array $vars = []): string
    {
        $file = APP_PATH . '/views/email/' . $template . '.php';
        if (!file_exists($file)) {
            return '';
        }
        extract($vars, EXTR_SKIP);
        ob_start();
        require $file;
        return ob_get_clean();
    }
}
