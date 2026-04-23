<?php
/**
 * AuthController.php
 * Gestiona: age gate, registro, login, logout y recuperación de contraseña.
 */

require_once APP_PATH . '/Controller.php';
require_once APP_PATH . '/Security.php';
require_once APP_PATH . '/Validator.php';
require_once APP_PATH . '/models/UsuarioModel.php';
require_once APP_PATH . '/models/LoginLogModel.php';
require_once APP_PATH . '/models/NotificacionModel.php';

class AuthController extends Controller
{
    private UsuarioModel  $usuarios;
    private LoginLogModel $loginLog;

    public function __construct()
    {
        $this->usuarios = new UsuarioModel();
        $this->loginLog = new LoginLogModel();
    }

    // ---------------------------------------------------------
    // AGE GATE — Verificación de mayoría de edad
    // ---------------------------------------------------------

    public function showAgeGate(array $params = []): void
    {
        // El age gate ahora es un modal sobre la página.
        // Si alguien accede directamente a /verificar-edad,
        // se redirige al inicio (donde el modal aparecerá automáticamente).
        $dest = SessionManager::get('age_redirect', '/');
        $this->redirect($dest);
    }

    public function confirmAge(array $params = []): void
    {
        $confirm = $_POST['confirm_age'] ?? '';

        if ($confirm !== '1') {
            // Rechazó — redirigir a Google o página de salida
            header('Location: https://www.google.com');
            exit;
        }

        SessionManager::set('age_verified', true);

        $dest = SessionManager::get('age_redirect', '/');
        SessionManager::delete('age_redirect');

        // Seguridad: nunca redirigir a un recurso/endpoint de fondo
        if (str_starts_with($dest, '/img/')
         || str_starts_with($dest, '/video/')
         || str_starts_with($dest, '/api/')
         || str_starts_with($dest, '/tile/')
         || str_starts_with($dest, '/public/')
         || str_starts_with($dest, '/assets/')
         || str_starts_with($dest, '/sse/')
         || preg_match('/\.(css|js|map|png|jpe?g|webp|gif|svg|ico|woff2?|ttf|eot|json|xml|txt)$/i', $dest)) {
            $dest = '/';
        }

        $this->redirect($dest);
    }

    // ---------------------------------------------------------
    // REGISTRO — nuevo flujo multi-paso
    // ---------------------------------------------------------

    /** Paso 1: selección de tipo de cuenta */
    public function showTipoRegistro(array $params = []): void
    {
        $this->render('auth/registro-tipo', ['pageTitle' => 'Crear cuenta']);
    }

    // ---------------------------------------------------------
    // REGISTRO RÁPIDO — COMENTARISTA (email + password)
    // ---------------------------------------------------------

    public function showRegistroComentarista(array $params = []): void
    {
        $old = SessionManager::get('form_old', []);
        SessionManager::delete('form_old');
        $this->render('auth/registro-comentarista', [
            'pageTitle' => 'Crear cuenta de comentarista',
            'old'       => $old,
        ]);
    }

    /**
     * GET /verificar-email/{token}
     * El usuario hace clic en el enlace del correo para confirmar su cuenta.
     */
    public function verificarEmailLink(array $params = []): void
    {
        $token = $params['token'] ?? '';
        if (!preg_match('/^[a-f0-9]{32,128}$/', $token)) {
            SessionManager::flash('error', 'Enlace de confirmación inválido.');
            $this->redirect('/login');
        }

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT id, email, email_verificado FROM usuarios WHERE email_verify_token = ? LIMIT 1");
        $stmt->execute([$token]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usuario) {
            SessionManager::flash('error', 'Enlace inválido o expirado. Solicita uno nuevo.');
            $this->redirect('/login');
        }

        if ((int)$usuario['email_verificado'] === 1) {
            SessionManager::flash('info', 'Tu correo ya está confirmado. Inicia sesión.');
            $this->redirect('/login');
        }

        // Marcar como verificado e invalidar token
        $db->prepare(
            "UPDATE usuarios SET email_verificado = 1, email_verified_at = NOW(), email_verify_token = NULL WHERE id = ?"
        )->execute([(int)$usuario['id']]);

        SessionManager::flash('success', '¡Correo confirmado! Ya puedes iniciar sesión.');
        $this->redirect('/login');
    }

    /**
     * POST /verificar-email/reenviar
     * Reenvía el correo de confirmación si el usuario pide uno nuevo.
     */
    public function reenviarVerificacionEmail(array $params = []): void
    {
        $email = Security::sanitizeEmail($_POST['email'] ?? '');
        if (!$email) {
            $this->redirect('/login');
        }

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT id, nombre, email_verificado FROM usuarios WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        // Mensaje genérico para no revelar si el email existe
        if (!$usuario || (int)$usuario['email_verificado'] === 1) {
            SessionManager::flash('success', 'Si el correo está registrado y pendiente de confirmar, recibirás un enlace nuevo.');
            $this->redirect('/login');
        }

        // Nuevo token
        $token = bin2hex(random_bytes(32));
        $db->prepare("UPDATE usuarios SET email_verify_token = ? WHERE id = ?")
           ->execute([$token, (int)$usuario['id']]);

        $link = APP_URL . '/verificar-email/' . $token;
        $html = Mailer::render('verificar-email-link', [
            'nombre' => $usuario['nombre'],
            'link'   => $link,
        ]);
        Mailer::send(
            $email,
            'Confirma tu cuenta — ' . APP_NAME,
            $html,
            "Confirma tu correo abriendo este enlace:\n{$link}\n\nEl enlace expira en 24 horas."
        );

        SessionManager::flash('success', 'Reenviamos el correo de confirmación. Revisa tu bandeja de entrada.');
        $this->render('auth/revisar-correo', [
            'email'     => $email,
            'pageTitle' => 'Revisa tu correo',
        ]);
    }

    public function storeRegistroComentarista(array $params = []): void
    {
        $ip     = Security::getClientIp();
        $nombre = Security::sanitizeString($_POST['nombre']           ?? '');
        $email  = Security::sanitizeEmail ($_POST['email']            ?? '');
        $pass   = $_POST['password']         ?? '';
        $conf   = $_POST['password_confirm'] ?? '';
        $acepta = !empty($_POST['acepta_terminos']);

        $v = new Validator([
            'nombre'           => $nombre,
            'email'            => $email,
            'password'         => $pass,
            'password_confirm' => $conf,
        ]);
        $v->required('nombre',  'Nombre')->minLength('nombre', 2)->maxLength('nombre', 120)->noHtml('nombre', 'Nombre')
          ->required('email',   'Email')->email('email')
          ->required('password','Contraseña')->strongPassword('password')
          ->matches('password_confirm', 'password', 'las contraseñas');

        if (!$acepta) {
            SessionManager::flash('error', 'Debes aceptar los términos y la confirmación de mayoría de edad.');
            SessionManager::set('form_old', compact('nombre','email'));
            $this->redirect('/registro/comentarista');
        }

        if ($v->fails()) {
            foreach ($v->allErrors() as $err) SessionManager::flash('error', $err);
            SessionManager::set('form_old', compact('nombre','email'));
            $this->redirect('/registro/comentarista');
        }

        if ($this->usuarios->emailExiste($email)) {
            SessionManager::flash('error', 'Ese correo ya tiene una cuenta. Inicia sesión.');
            $this->redirect('/login');
        }

        // Token único para confirmación de email (64 chars hex)
        $emailToken = bin2hex(random_bytes(32));

        try {
            $idUsuario = $this->usuarios->crear([
                'nombre'             => $nombre,
                'email'              => $email,
                'password'           => $pass,
                'telefono'           => null,
                'rol'                => 'comentarista',
                'ip_registro'        => $ip,
                'email_verificado'   => 0,
                'email_verify_token' => $emailToken,
            ]);
        } catch (\Exception $e) {
            error_log('[AuthController::storeRegistroComentarista] ' . $e->getMessage());
            SessionManager::flash('error', 'Error al crear la cuenta. Intenta de nuevo.');
            $this->redirect('/registro/comentarista');
        }

        // Notificar admins (nuevo usuario)
        (new NotificacionModel())->crearParaAdmins([
            'tipo'    => 'usuario_nuevo',
            'titulo'  => 'Nuevo comentarista registrado',
            'mensaje' => $nombre . ' (' . $email . ') se registró como comentarista.',
            'url'     => '/admin/usuario/' . $idUsuario,
            'icono'   => 'chat-dots-fill',
            'color'   => 'info',
        ]);

        // Correo con enlace de confirmación
        $link = APP_URL . '/verificar-email/' . $emailToken;
        $html = Mailer::render('verificar-email-link', [
            'nombre' => $nombre,
            'link'   => $link,
        ]);
        Mailer::send(
            $email,
            'Confirma tu cuenta — ' . APP_NAME,
            $html,
            "Hola {$nombre},\nConfirma tu correo abriendo este enlace:\n{$link}\n\nEl enlace expira en 24 horas."
        );

        // NO auto-login: debe confirmar el correo primero
        SessionManager::flash('success', 'Cuenta creada. Revisa tu correo para confirmar y poder iniciar sesión.');
        $this->render('auth/revisar-correo', [
            'email' => $email,
            'pageTitle' => 'Revisa tu correo',
        ]);
        return;
    }

    /** Paso 2: formulario de teléfono + email (ruta publicador) */
    public function showContactoPublicador(array $params = []): void
    {
        // Si ya pasó la verificación SMS redirigir al siguiente paso
        $reg = SessionManager::get('reg_pendiente', []);
        if (!empty($reg['sms_verified']) && empty($reg['email_verified'])) {
            $this->redirect('/registro/verificar-email');
        }
        if (!empty($reg['email_verified'])) {
            $this->redirect('/registro/completar'); // paso siguiente (a definir)
        }

        $old = SessionManager::get('form_old', []);
        SessionManager::delete('form_old');
        $this->render('auth/registro-contacto', [
            'pageTitle' => 'Datos de contacto',
            'old'       => $old,
        ]);
    }

    /** Paso 2 POST: valida datos, genera códigos y envía */
    public function storeContacto(array $params = []): void
    {
        $ip       = Security::getClientIp();
        $telefono = Security::sanitizePhone($_POST['telefono'] ?? '');
        $email    = Security::sanitizeEmail($_POST['email']    ?? '');
        $smsOk    = ($_POST['autoriza_sms'] ?? '') === '1';

        // Validaciones
        $v = new Validator(['telefono' => $telefono, 'email' => $email]);
        $v->required('telefono', 'Teléfono')->phone('telefono')
          ->required('email', 'Email')->email('email');

        if ($v->fails()) {
            foreach ($v->allErrors() as $err) SessionManager::flash('error', $err);
            SessionManager::set('form_old', compact('telefono', 'email'));
            $this->redirect('/registro/publicador');
        }
        if (!$smsOk) {
            SessionManager::flash('error', 'Debes autorizar el envío de SMS para continuar.');
            SessionManager::set('form_old', compact('telefono', 'email'));
            $this->redirect('/registro/publicador');
        }

        // Unicidad
        if ($this->usuarios->telefonoExiste($telefono)) {
            SessionManager::flash('error', 'Ese número de teléfono ya está registrado.');
            SessionManager::set('form_old', compact('telefono', 'email'));
            $this->redirect('/registro/publicador');
        }
        if ($this->usuarios->emailExiste($email)) {
            SessionManager::flash('error', 'Ese correo electrónico ya está registrado. ¿<a href="' . APP_URL . '/login">Iniciar sesión</a>?');
            SessionManager::set('form_old', compact('telefono', 'email'));
            $this->redirect('/registro/publicador');
        }

        // Generar códigos
        $smsCodigo   = $this->generarCodigo();
        $emailCodigo = $this->generarCodigo();
        $ahora       = time();

        SessionManager::set('reg_pendiente', [
            'tipo'             => 'publicador',
            'telefono'         => $telefono,
            'email'            => $email,
            'sms_codigo'       => $smsCodigo,
            'sms_sent_at'      => $ahora,
            'sms_intentos'     => 0,
            'sms_verified'     => false,
            'email_codigo'     => $emailCodigo,
            'email_sent_at'    => $ahora,
            'email_intentos'   => 0,
            'email_verified'   => false,
        ]);

        $this->enviarSms($telefono, $smsCodigo);
        $this->enviarCorreoVerificacion($email, $emailCodigo);

        $this->redirect('/registro/verificar-sms');
    }

    /** Paso 3a: mostrar pantalla de verificación SMS */
    public function showVerificarSms(array $params = []): void
    {
        $reg = SessionManager::get('reg_pendiente', []);
        if (empty($reg['telefono'])) {
            $this->redirect('/registro/publicador');
        }
        if (!empty($reg['sms_verified'])) {
            $this->redirect('/registro/verificar-email');
        }
        $segundosRestantes = max(0, 60 - (time() - ($reg['sms_sent_at'] ?? 0)));
        $this->render('auth/registro-verificar-sms', [
            'pageTitle'         => 'Verificar teléfono',
            'telefono'          => $reg['telefono'],
            'segundosRestantes' => $segundosRestantes,
        ]);
    }

    /** Paso 3a POST: valida código SMS */
    public function verificarSms(array $params = []): void
    {
        $reg     = SessionManager::get('reg_pendiente', []);
        $codigo  = trim($_POST['codigo'] ?? '');

        if (empty($reg['telefono']) || !empty($reg['sms_verified'])) {
            $this->redirect('/registro/publicador');
        }

        // Expiración (15 min)
        if ((time() - ($reg['sms_sent_at'] ?? 0)) > 900) {
            SessionManager::flash('error', 'El código ha expirado. Solicita uno nuevo.');
            $this->redirect('/registro/verificar-sms');
        }

        // Intentos máximos
        if (($reg['sms_intentos'] ?? 0) >= 5) {
            SessionManager::delete('reg_pendiente');
            SessionManager::flash('error', 'Demasiados intentos incorrectos. Reinicia el proceso.');
            $this->redirect('/registro/publicador');
        }

        if ($codigo !== $reg['sms_codigo']) {
            $reg['sms_intentos'] = ($reg['sms_intentos'] ?? 0) + 1;
            SessionManager::set('reg_pendiente', $reg);
            $restantes = 5 - $reg['sms_intentos'];
            SessionManager::flash('error', "Código incorrecto. Te quedan {$restantes} intento(s).");
            $this->redirect('/registro/verificar-sms');
        }

        $reg['sms_verified'] = true;
        SessionManager::set('reg_pendiente', $reg);
        $this->redirect('/registro/verificar-email');
    }

    /** Paso 3a: reenviar SMS */
    public function reenviarSms(array $params = []): void
    {
        $reg = SessionManager::get('reg_pendiente', []);
        if (empty($reg['telefono']) || !empty($reg['sms_verified'])) {
            $this->redirect('/registro/publicador');
        }

        if ((time() - ($reg['sms_sent_at'] ?? 0)) < 60) {
            SessionManager::flash('error', 'Espera un momento antes de solicitar un nuevo código.');
            $this->redirect('/registro/verificar-sms');
        }

        $smsCodigo          = $this->generarCodigo();
        $reg['sms_codigo']  = $smsCodigo;
        $reg['sms_sent_at'] = time();
        $reg['sms_intentos'] = 0;
        SessionManager::set('reg_pendiente', $reg);

        $this->enviarSms($reg['telefono'], $smsCodigo);
        SessionManager::flash('success', 'Nuevo código SMS enviado.');
        $this->redirect('/registro/verificar-sms');
    }

    /** Paso 3b: mostrar pantalla de verificación email */
    public function showVerificarEmail(array $params = []): void
    {
        $reg = SessionManager::get('reg_pendiente', []);
        if (empty($reg['sms_verified'])) {
            $this->redirect('/registro/verificar-sms');
        }
        if (!empty($reg['email_verified'])) {
            $this->redirect('/registro/completar');
        }
        $segundosRestantes = max(0, 60 - (time() - ($reg['email_sent_at'] ?? 0)));
        $this->render('auth/registro-verificar-email', [
            'pageTitle'         => 'Verificar correo',
            'email'             => $reg['email'],
            'segundosRestantes' => $segundosRestantes,
        ]);
    }

    /** Paso 3b POST: valida código email */
    public function verificarEmail(array $params = []): void
    {
        $reg    = SessionManager::get('reg_pendiente', []);
        $codigo = trim($_POST['codigo'] ?? '');

        if (empty($reg['sms_verified']) || !empty($reg['email_verified'])) {
            $this->redirect('/registro/publicador');
        }

        if ((time() - ($reg['email_sent_at'] ?? 0)) > 900) {
            SessionManager::flash('error', 'El código ha expirado. Solicita uno nuevo.');
            $this->redirect('/registro/verificar-email');
        }

        if (($reg['email_intentos'] ?? 0) >= 5) {
            SessionManager::delete('reg_pendiente');
            SessionManager::flash('error', 'Demasiados intentos incorrectos. Reinicia el proceso.');
            $this->redirect('/registro/publicador');
        }

        if ($codigo !== $reg['email_codigo']) {
            $reg['email_intentos'] = ($reg['email_intentos'] ?? 0) + 1;
            SessionManager::set('reg_pendiente', $reg);
            $restantes = 5 - $reg['email_intentos'];
            SessionManager::flash('error', "Código incorrecto. Te quedan {$restantes} intento(s).");
            $this->redirect('/registro/verificar-email');
        }

        $reg['email_verified'] = true;
        SessionManager::set('reg_pendiente', $reg);

        // Ambos verificados — continuar al siguiente paso (a definir)
        SessionManager::flash('success', '¡Teléfono y correo verificados! Completa tu registro.');
        $this->redirect('/registro/completar');
    }

    /** Paso 3b: reenviar email */
    public function reenviarEmail(array $params = []): void
    {
        $reg = SessionManager::get('reg_pendiente', []);
        if (empty($reg['sms_verified']) || !empty($reg['email_verified'])) {
            $this->redirect('/registro/publicador');
        }

        if ((time() - ($reg['email_sent_at'] ?? 0)) < 60) {
            SessionManager::flash('error', 'Espera un momento antes de solicitar un nuevo código.');
            $this->redirect('/registro/verificar-email');
        }

        $emailCodigo            = $this->generarCodigo();
        $reg['email_codigo']    = $emailCodigo;
        $reg['email_sent_at']   = time();
        $reg['email_intentos']  = 0;
        SessionManager::set('reg_pendiente', $reg);

        $this->enviarCorreoVerificacion($reg['email'], $emailCodigo);
        SessionManager::flash('success', 'Nuevo código enviado a tu correo.');
        $this->redirect('/registro/verificar-email');
    }

    /** Paso 3b: corregir email y reenviar */
    public function corregirEmail(array $params = []): void
    {
        $reg      = SessionManager::get('reg_pendiente', []);
        $newEmail = Security::sanitizeEmail($_POST['email'] ?? '');

        if (empty($reg['sms_verified']) || !empty($reg['email_verified'])) {
            $this->redirect('/registro/publicador');
        }

        $v = new Validator(['email' => $newEmail]);
        $v->required('email', 'Email')->email('email');
        if ($v->fails()) {
            SessionManager::flash('error', $v->firstGlobalError() ?? 'Email inválido.');
            $this->redirect('/registro/verificar-email');
        }

        if ($this->usuarios->emailExiste($newEmail)) {
            SessionManager::flash('error', 'Ese correo ya está registrado.');
            $this->redirect('/registro/verificar-email');
        }

        $emailCodigo           = $this->generarCodigo();
        $reg['email']          = $newEmail;
        $reg['email_codigo']   = $emailCodigo;
        $reg['email_sent_at']  = time();
        $reg['email_intentos'] = 0;
        SessionManager::set('reg_pendiente', $reg);

        $this->enviarCorreoVerificacion($newEmail, $emailCodigo);
        SessionManager::flash('success', "Código enviado al nuevo correo {$newEmail}.");
        $this->redirect('/registro/verificar-email');
    }

    /** Paso 4: mostrar formulario de nombre + contraseña */
    public function showCompletar(array $params = []): void
    {
        $reg = SessionManager::get('reg_pendiente', []);
        if (empty($reg['email_verified'])) {
            $this->redirect('/registro/verificar-email');
        }
        $this->render('auth/registro-completar', ['pageTitle' => 'Crea tu contraseña']);
    }

    /** Paso 4 POST: crear cuenta y redirigir al dashboard */
    public function completar(array $params = []): void
    {
        $reg = SessionManager::get('reg_pendiente', []);
        if (empty($reg['email_verified'])) {
            $this->redirect('/registro/verificar-email');
        }

        $ip       = Security::getClientIp();
        $nombre   = Security::sanitizeString($_POST['nombre']          ?? '');
        $password = $_POST['password']         ?? '';
        $confirm  = $_POST['password_confirm'] ?? '';

        $v = new Validator([
            'nombre'           => $nombre,
            'password'         => $password,
            'password_confirm' => $confirm,
        ]);
        $v->required('nombre',  'Nombre')->minLength('nombre', 2)->maxLength('nombre', 120)->noHtml('nombre', 'Nombre')
          ->required('password', 'Contraseña')->strongPassword('password')
          ->matches('password_confirm', 'password', 'las contraseñas');

        if ($v->fails()) {
            foreach ($v->allErrors() as $err) SessionManager::flash('error', $err);
            $this->redirect('/registro/completar');
        }

        // Doble verificación de unicidad (por si acaso pasó tiempo)
        if ($this->usuarios->emailExiste($reg['email'])) {
            SessionManager::flash('error', 'Ese correo ya fue registrado. Inicia sesión.');
            SessionManager::delete('reg_pendiente');
            $this->redirect('/login');
        }

        try {
            $idUsuario = $this->usuarios->crear([
                'nombre'      => $nombre,
                'email'       => $reg['email'],
                'password'    => $password,
                'telefono'    => $reg['telefono'],
                'ip_registro' => $ip,
            ]);
        } catch (\Exception $e) {
            error_log('[AuthController::completar] ' . $e->getMessage());
            SessionManager::flash('error', 'Error al crear la cuenta. Intenta de nuevo.');
            $this->redirect('/registro/completar');
        }

        // Limpiar sesión de registro
        SessionManager::delete('reg_pendiente');

        (new NotificacionModel())->crearParaAdmins([
            'tipo'    => 'usuario_nuevo',
            'titulo'  => 'Nuevo usuario registrado',
            'mensaje' => $nombre . ' (' . $reg['email'] . ') completó el registro.',
            'url'     => '/admin/usuario/' . $idUsuario,
            'icono'   => 'person-fill-add',
            'color'   => 'info',
        ]);

        // Login automático
        $usuario = $this->usuarios->find($idUsuario);
        $this->usuarios->guardarEnSesion($usuario);

        SessionManager::flash('success', '¡Bienvenido/a ' . e($nombre) . '! Ya puedes crear y enviar tus perfiles a revisión.');
        $this->redirect('/dashboard');
    }

    // ---------------------------------------------------------
    // REGISTRO — helpers privados
    // ---------------------------------------------------------

    private function generarCodigo(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    private function enviarSms(string $telefono, string $codigo): void
    {
        // TODO: integrar proveedor SMS (Twilio, Vonage, etc.)
        // Por ahora: log + flash visible en desarrollo
        $msg = "[SMS DEV] Teléfono: {$telefono} — Código: {$codigo}";
        error_log($msg);
        SessionManager::flash('info', "🔑 Código SMS (desarrollo): <strong>{$codigo}</strong>");
    }

    private function enviarCorreoVerificacion(string $email, string $codigo, ?string $nombre = null): void
    {
        $html = Mailer::render('codigo-verificacion', [
            'codigo' => $codigo,
            'nombre' => $nombre,
        ]);
        $enviado = Mailer::send(
            $email,
            'Tu código de verificación — ' . APP_NAME,
            $html,
            "Tu código de verificación es: {$codigo}\nTiene validez de 10 minutos."
        );

        if (!$enviado || (defined('SMTP_ENABLED') && !SMTP_ENABLED)) {
            // Fallback dev: mostrar en pantalla
            error_log("[EMAIL DEV] Correo: {$email} — Código: {$codigo}");
            SessionManager::flash('info', "📧 Código Email (desarrollo): <strong>{$codigo}</strong>");
        }
    }

    // ---------------------------------------------------------
    // REGISTRO — flujo legacy (mantenido para compatibilidad)
    // ---------------------------------------------------------

    public function showRegister(array $params = []): void
    {
        $this->render('auth/register', ['pageTitle' => 'Crear cuenta']);
    }

    public function register(array $params = []): void
    {
        $ip = Security::getClientIp();

        // Rate limiting: máx 5 registros por IP en 1 hora
        if (!Security::checkRateLimit('register_' . $ip, 5, 3600)) {
            SessionManager::flash('error', 'Demasiados intentos. Espera antes de intentar de nuevo.');
            $this->redirect('/registro');
        }

        // Sanitizar inputs
        $nombre   = Security::sanitizeString($_POST['nombre']   ?? '');
        $email    = Security::sanitizeEmail($_POST['email']     ?? '');
        $telefono = Security::sanitizePhone($_POST['telefono']  ?? '');
        $password = $_POST['password']          ?? '';
        $confirm  = $_POST['password_confirm']  ?? '';
        $terminos = $_POST['aceptar_terminos']  ?? '';

        // Validar
        $v = new Validator([
            'nombre'           => $nombre,
            'email'            => $email,
            'password'         => $password,
            'password_confirm' => $confirm,
            'telefono'         => $telefono,
            'aceptar_terminos' => $terminos,
        ]);

        $v->required('nombre',  'Nombre')
          ->minLength('nombre', 2, 'Nombre')
          ->maxLength('nombre', 120, 'Nombre')
          ->noHtml('nombre', 'Nombre')
          ->required('email', 'Email')
          ->email('email')
          ->required('password', 'Contraseña')
          ->strongPassword('password')
          ->matches('password_confirm', 'password', 'las contraseñas')
          ->phone('telefono')
          ->custom($terminos !== '1', 'aceptar_terminos', 'Debes aceptar los términos y condiciones.');

        if ($v->fails()) {
            foreach ($v->allErrors() as $err) {
                SessionManager::flash('error', $err);
            }
            // Conservar datos del formulario (excepto passwords)
            SessionManager::set('form_old', compact('nombre', 'email', 'telefono'));
            $this->redirect('/registro');
        }

        // Verificar email único
        if ($this->usuarios->emailExiste($email)) {
            SessionManager::flash('error', 'Ese email ya está registrado. ¿Olvidaste tu contraseña?');
            SessionManager::set('form_old', compact('nombre', 'email', 'telefono'));
            $this->redirect('/registro');
        }

        // Crear usuario
        try {
            $idUsuario = $this->usuarios->crear([
                'nombre'       => $nombre,
                'email'        => $email,
                'password'     => $password,
                'telefono'     => $telefono ?: null,
                'ip_registro'  => $ip,
            ]);
        } catch (\Exception $e) {
            error_log('[AuthController] Error al crear usuario: ' . $e->getMessage());
            SessionManager::flash('error', 'Ocurrió un error al crear tu cuenta. Intenta de nuevo.');
            $this->redirect('/registro');
        }

        // Login automático tras registro
        $usuario = $this->usuarios->find($idUsuario);
        $this->usuarios->guardarEnSesion($usuario);
        Security::resetRateLimit('register_' . $ip);

        SessionManager::flash('success', '¡Cuenta creada! Tu perfil está en revisión. Te avisaremos cuando sea aprobado.');
        $this->redirect('/dashboard');
    }

    // ---------------------------------------------------------
    // LOGIN
    // ---------------------------------------------------------

    public function showLogin(array $params = []): void
    {
        $this->render('auth/login', ['pageTitle' => 'Iniciar sesión']);
    }

    public function login(array $params = []): void
    {
        $ip    = Security::getClientIp();
        $email = Security::sanitizeEmail($_POST['email']    ?? '');
        $pass  = $_POST['password'] ?? '';

        // Rate limiting por IP: máx 10 intentos en 15 min
        if ($this->loginLog->ipBloqueada($ip)) {
            $minutos = (int) ceil(LOGIN_LOCKOUT_TIME / 60);
            SessionManager::flash('error', "Demasiados intentos fallidos. Espera {$minutos} minutos.");
            $this->redirect('/login');
        }

        // Validación básica
        $v = new Validator(['email' => $email, 'password' => $pass]);
        $v->required('email', 'Email')->email('email')
          ->required('password', 'Contraseña');

        if ($v->fails()) {
            SessionManager::flash('error', $v->firstGlobalError());
            SessionManager::set('form_old', ['email' => $email]);
            $this->redirect('/login');
        }

        // Verificar bloqueo temporal del usuario
        if ($this->usuarios->estaBloqueado($email)) {
            $this->loginLog->registrar($ip, $email, false);
            SessionManager::flash('error', 'Tu cuenta está temporalmente bloqueada por múltiples intentos fallidos.');
            $this->redirect('/login');
        }

        // Autenticar
        $usuario = $this->usuarios->autenticar($email, $pass);

        if (!$usuario) {
            $this->usuarios->registrarIntentoFallido($email);
            $this->loginLog->registrar($ip, $email, false);
            SessionManager::flash('error', 'Email o contraseña incorrectos.');
            SessionManager::set('form_old', ['email' => $email]);
            $this->redirect('/login');
        }

        // Bloquear login si el correo no está confirmado (solo aplica a cuentas con email_verificado registrado)
        if (isset($usuario['email_verificado']) && (int)$usuario['email_verificado'] === 0
            && !empty($usuario['email_verify_token'])) {
            SessionManager::flash('error', 'Debes confirmar tu correo antes de iniciar sesión. Revisa tu bandeja.');
            SessionManager::set('form_old', ['email' => $email]);
            $this->render('auth/revisar-correo', [
                'email'     => $email,
                'pageTitle' => 'Confirma tu correo',
            ]);
            return;
        }

        // Login exitoso
        $this->loginLog->registrar($ip, $email, true);
        session_regenerate_id(true); // Prevenir session fixation

        $this->usuarios->guardarEnSesion($usuario);

        // "Mantener sesión activa" — extender cookie a 30 días
        if (!empty($_POST['remember'])) {
            SessionManager::promoteToLongTerm();
        } else {
            SessionManager::forgetLongTerm();
        }

        // Re-hashear si es necesario (upgrade de cost)
        if (Security::needsRehash($usuario['password'])) {
            $this->usuarios->actualizarPassword($usuario['id'], $pass);
        }

        $homePorRol = ($usuario['rol'] ?? 'usuario') === 'comentarista' ? '/' : '/dashboard';
        $destino = SessionManager::get('intended_url', $homePorRol);
        SessionManager::delete('intended_url');

        SessionManager::flash('success', '¡Bienvenido, ' . $usuario['nombre'] . '!');
        $this->redirect($destino);
    }

    // ---------------------------------------------------------
    // LOGOUT
    // ---------------------------------------------------------

    public function logout(array $params = []): void
    {
        $this->usuarios->limpiarSesion();
        SessionManager::forgetLongTerm();
        SessionManager::init(); // Iniciar sesión vacía
        SessionManager::set('age_verified', true); // Mantener age gate
        SessionManager::flash('success', 'Sesión cerrada correctamente.');
        $this->redirect('/login');
    }

    // ---------------------------------------------------------
    // RECUPERACIÓN DE CONTRASEÑA (simulada)
    // ---------------------------------------------------------

    public function showRecover(array $params = []): void
    {
        $this->render('auth/recover', ['pageTitle' => 'Recuperar contraseña']);
    }

    public function recoverPassword(array $params = []): void
    {
        $email = Security::sanitizeEmail($_POST['email'] ?? '');
        $ip    = Security::getClientIp();

        // Rate limiting: máx 3 solicitudes por IP en 1 hora
        if (!Security::checkRateLimit('recover_' . $ip, 3, 3600)) {
            SessionManager::flash('error', 'Demasiadas solicitudes. Intenta en una hora.');
            $this->redirect('/recuperar-password');
        }

        $v = new Validator(['email' => $email]);
        $v->required('email', 'Email')->email('email');

        if ($v->fails()) {
            SessionManager::flash('error', $v->firstGlobalError());
            $this->redirect('/recuperar-password');
        }

        // Siempre mostrar el mismo mensaje (evitar enumeración de usuarios)
        $mensaje = 'Si ese email está registrado, recibirás instrucciones en breve.';
        $usuario = $this->usuarios->buscarPorEmail($email);

        if ($usuario) {
            $token = $this->usuarios->generarTokenRecuperacion($usuario['id']);

            // En producción: enviar email real con el token
            // Por ahora: simulación — mostrar el link en pantalla (solo en desarrollo)
            if (APP_DEBUG) {
                $link = APP_URL . '/recuperar-password?token=' . $token;
                SessionManager::flash('info', "[DEV] Link de recuperación: <a href='{$link}'>{$link}</a>");
            }

            error_log("[AuthController] Token recuperación para {$email}: {$token}");
        }

        SessionManager::flash('success', $mensaje);
        $this->redirect('/recuperar-password');
    }
}
