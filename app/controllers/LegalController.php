<?php
/**
 * LegalController.php
 * Sirve las páginas legales: términos, privacidad y aviso +18.
 */

require_once APP_PATH . '/Controller.php';

class LegalController extends Controller
{
    public function terms(array $params = []): void
    {
        $this->render('legal/terms', ['pageTitle' => 'Términos y condiciones']);
    }

    public function privacy(array $params = []): void
    {
        $this->render('legal/privacy', ['pageTitle' => 'Aviso de privacidad']);
    }

    public function adultNotice(array $params = []): void
    {
        $this->render('legal/adult-notice', ['pageTitle' => 'Aviso para mayores de edad (+18)']);
    }

    public function dmca(array $params = []): void
    {
        $this->render('legal/dmca', ['pageTitle' => 'Política de Derechos de Autor']);
    }

    public function statement2257(array $params = []): void
    {
        $this->render('legal/2257', ['pageTitle' => 'Declaración de verificación de edad (2257)']);
    }

    public function cookies(array $params = []): void
    {
        $this->render('legal/cookies', ['pageTitle' => 'Política de Cookies']);
    }

    public function payments(array $params = []): void
    {
        $this->render('legal/pagos', ['pageTitle' => 'Política de Pagos y Devoluciones']);
    }

    public function parentalControl(array $params = []): void
    {
        $this->render('legal/control-parental', ['pageTitle' => 'Control Parental']);
    }

    public function contact(array $params = []): void
    {
        $this->render('legal/contacto', ['pageTitle' => 'Contacto']);
    }

    public function sendContact(array $params = []): void
    {
        $ip = Security::getClientIp();

        // Rate limit: máximo 3 mensajes por IP en 1 hora (anti-spam)
        if (!Security::checkRateLimit('contact_' . $ip, 3, 3600)) {
            SessionManager::flash('error', 'Has enviado demasiados mensajes recientemente. Intenta de nuevo en una hora.');
            $this->redirect('/contacto');
        }

        // Honeypot: bot detectado si rellenó el campo "website"
        if (!empty($_POST['website'])) {
            SessionManager::flash('success', 'Mensaje enviado. Te responderemos a la brevedad.');
            $this->redirect('/contacto');
        }

        // reCAPTCHA v3: solo se valida si el admin configuro las claves.
        // Si score < 0.5 o falla, rechazamos como bot.
        $siteKey   = function_exists('setting') ? setting('recaptcha_site_key')   : null;
        $secretKey = function_exists('setting') ? setting('recaptcha_secret_key') : null;
        if ($siteKey && $secretKey) {
            $token = (string)($_POST['recaptcha_token'] ?? '');
            if (!$this->verificarRecaptcha($token, $secretKey, 'contact', $ip)) {
                SessionManager::flash('error', 'No pudimos verificar que eres humano. Recarga la página e intenta de nuevo.');
                $this->redirect('/contacto');
            }
        }

        $nombre  = Security::sanitizeString($_POST['nombre']  ?? '');
        $email   = Security::sanitizeEmail($_POST['email']    ?? '');
        $asunto  = trim($_POST['asunto']                       ?? '');
        $mensaje = Security::sanitizeText($_POST['mensaje']   ?? '');

        $tiposValidos = ContactoMensajeModel::ASUNTOS;

        $v = new Validator(compact('nombre', 'email', 'asunto', 'mensaje'));
        $v->required('nombre', 'Nombre')
          ->minLength('nombre', 2, 'Nombre')
          ->maxLength('nombre', 80, 'Nombre')
          ->required('email', 'Email')
          ->email('email')
          ->required('asunto', 'Tipo de consulta')
          ->custom(!in_array($asunto, $tiposValidos, true), 'asunto', 'Tipo de consulta no válido.')
          ->required('mensaje', 'Mensaje')
          ->minLength('mensaje', 20, 'Mensaje')
          ->maxLength('mensaje', 3000, 'Mensaje');

        if ($v->fails()) {
            SessionManager::flash('error', $v->firstGlobalError());
            $this->redirect('/contacto');
        }

        try {
            $modelo = new ContactoMensajeModel();
            $idMsg = $modelo->crear([
                'nombre'  => $nombre,
                'email'   => $email,
                'asunto'  => $asunto,
                'mensaje' => $mensaje,
                'ip'      => $ip,
            ]);

            // Notificar a admins en el panel (campanita)
            (new NotificacionModel())->crearParaAdmins([
                'tipo'    => 'contacto_nuevo',
                'titulo'  => 'Nuevo mensaje de contacto',
                'mensaje' => mb_substr($nombre, 0, 40) . ' — ' . (ContactoMensajeModel::ASUNTOS_LABELS[$asunto] ?? $asunto),
                'url'     => '/admin/contactos/' . (int) $idMsg,
                'icono'   => 'envelope-fill',
                'color'   => 'info',
            ]);
        } catch (Throwable $e) {
            error_log('[ContactForm] DB error: ' . $e->getMessage());
            SessionManager::flash('error', 'Hubo un problema al guardar el mensaje. Intenta de nuevo más tarde.');
            $this->redirect('/contacto');
        }

        SessionManager::flash('success', '¡Gracias! Recibimos tu mensaje. Te responderemos en 1 a 3 días hábiles.');
        $this->redirect('/contacto');
    }

    /**
     * Verifica un token de reCAPTCHA v3 contra la API de Google.
     * Devuelve true solo si la respuesta es valida, action coincide y score >= 0.5.
     */
    private function verificarRecaptcha(string $token, string $secret, string $expectedAction, string $remoteIp = ''): bool
    {
        if ($token === '') return false;

        $ch = curl_init('https://www.google.com/recaptcha/api/siteverify');
        if (!$ch) return false;
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query([
                'secret'   => $secret,
                'response' => $token,
                'remoteip' => $remoteIp,
            ]),
            CURLOPT_TIMEOUT        => 6,
            CURLOPT_CONNECTTIMEOUT => 4,
        ]);
        $resp = curl_exec($ch);
        $err  = curl_error($ch);
        curl_close($ch);

        if (!$resp) {
            error_log('[recaptcha] siteverify fallo curl: ' . $err);
            return false;
        }

        $data = json_decode($resp, true);
        if (!is_array($data) || empty($data['success'])) {
            error_log('[recaptcha] siteverify respuesta no exitosa: ' . substr($resp, 0, 200));
            return false;
        }

        // Verificar que la accion coincida (anti reuso de token de otro form)
        if ($expectedAction !== '' && (($data['action'] ?? '') !== $expectedAction)) {
            error_log('[recaptcha] action mismatch: esperaba ' . $expectedAction . ', recibi ' . ($data['action'] ?? 'null'));
            return false;
        }

        $score = (float)($data['score'] ?? 0);
        if ($score < 0.5) {
            error_log('[recaptcha] score bajo: ' . $score . ' ip=' . $remoteIp);
            return false;
        }

        return true;
    }
}
