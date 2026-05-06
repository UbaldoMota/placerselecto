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
}
