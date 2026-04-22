<?php
/**
 * UserController.php
 * Panel de usuario: dashboard y listado de mis anuncios.
 */

require_once APP_PATH . '/Controller.php';
require_once APP_PATH . '/models/UsuarioModel.php';
require_once APP_PATH . '/models/AnuncioModel.php';
require_once APP_PATH . '/models/PerfilModel.php';
require_once APP_PATH . '/models/PagoModel.php';
require_once APP_PATH . '/models/NotificacionModel.php';
require_once APP_PATH . '/models/TokenMovimientoModel.php';
require_once APP_PATH . '/models/BoostModel.php';
require_once APP_PATH . '/models/SoporteMensajeModel.php';

class UserController extends Controller
{
    private UsuarioModel $usuarios;
    private AnuncioModel $anuncios;
    private PerfilModel  $perfiles;
    private PagoModel    $pagos;

    public function __construct()
    {
        $this->usuarios = new UsuarioModel();
        $this->anuncios = new AnuncioModel();
        $this->perfiles = new PerfilModel();
        $this->pagos    = new PagoModel();
    }

    // ---------------------------------------------------------
    // DASHBOARD
    // ---------------------------------------------------------

    public function dashboard(array $params = []): void
    {
        $this->requireAuth();
        Security::setNoCacheHeaders();

        $user   = $this->currentUser();
        $idUser = (int) $user['id'];

        // Los comentaristas no tienen dashboard — se redirigen a la página principal
        if (($user['rol'] ?? '') === 'comentarista') {
            $this->redirect('/');
        }

        // Estadísticas de perfiles del usuario
        $db = Database::getInstance()->getConnection();

        $stmtPerfiles = $db->prepare(
            "SELECT
                COUNT(*)                        AS total,
                SUM(estado = 'publicado')        AS publicados,
                SUM(estado = 'pendiente')        AS pendientes,
                SUM(estado = 'rechazado')        AS rechazados,
                SUM(destacado = 1)               AS destacados,
                COALESCE(SUM(vistas), 0)         AS total_vistas,
                COALESCE(MAX(vistas), 0)         AS max_vistas
             FROM perfiles WHERE id_usuario = ?"
        );
        $stmtPerfiles->execute([$idUser]);
        $statsPerfiles = $stmtPerfiles->fetch();

        // Perfiles del usuario
        $misPerfiles = $this->perfiles->misPerfiles($idUser);

        $usuarioCompleto = $this->usuarios->find($idUser);
        $confiabilidad   = $this->usuarios->confiabilidad($idUser);

        // Datos agregados para los módulos del dashboard
        $saldoTokens = (new TokenMovimientoModel())->saldo($idUser);

        $s = $db->prepare("SELECT COUNT(*) FROM notificaciones WHERE id_usuario = ? AND leida = 0");
        $s->execute([$idUser]);
        $notifCount = (int)$s->fetchColumn();

        $s = $db->prepare("SELECT COUNT(*) FROM soporte_mensajes WHERE id_usuario = ? AND estado IN ('abierto','respondido')");
        $s->execute([$idUser]);
        $mensajesAbiertos = (int)$s->fetchColumn();

        $this->render('user/dashboard', [
            'pageTitle'       => 'Mi panel',
            'statsPerfiles'   => $statsPerfiles,
            'misPerfiles'     => $misPerfiles,
            'usuarioCompleto' => $usuarioCompleto,
            'confiabilidad'   => $confiabilidad,
            'saldoTokens'     => $saldoTokens,
            'notifCount'      => $notifCount,
            'mensajesAbiertos'=> $mensajesAbiertos,
        ]);
    }

    // ---------------------------------------------------------
    // SIN ANTICIPO
    // ---------------------------------------------------------

    public function toggleSinAnticipo(array $params = []): void
    {
        $this->requireAuth();

        $user  = $this->currentUser();
        $valor = (bool)(int) $this->postParam('sin_anticipo', '0');

        $this->usuarios->setSinAnticipo((int) $user['id'], $valor);

        $msg = $valor
            ? 'Declaración activada: no pides depósito anticipado.'
            : 'Declaración desactivada.';
        SessionManager::flash('success', $msg);
        $this->redirect('/dashboard');
    }

    // ---------------------------------------------------------
    // MIS ANUNCIOS
    // ---------------------------------------------------------

    public function myAds(array $params = []): void
    {
        $this->requireAuth();

        $user   = $this->currentUser();
        $page   = max(1, (int) ($this->getParam('page', '1')));
        $result = $this->anuncios->misAnuncios((int) $user['id'], $page);

        $this->render('user/my-ads', [
            'pageTitle'  => 'Mis anuncios',
            'anuncios'   => $result['items'],
            'pagination' => $result,
        ]);
    }

    // ---------------------------------------------------------
    // MIS PERFILES
    // ---------------------------------------------------------
    // ESTADÍSTICAS
    // ---------------------------------------------------------

    public function estadisticas(array $params = []): void
    {
        $this->requireAuth();
        Security::setNoCacheHeaders();

        $user   = $this->currentUser();
        $idUser = (int)$user['id'];

        $totalStats  = $this->perfiles->totalStatsUsuario($idUser);
        $diasGrafico = $this->perfiles->seriesDias($idUser, 0, 7);
        $porPerfil   = $this->perfiles->statsPerPerfil($idUser);

        // Serie de 7 días por cada perfil
        $seriesPerfil = [];
        foreach ($porPerfil as $p) {
            $seriesPerfil[(int)$p['id']] = $this->perfiles->seriesDias($idUser, (int)$p['id'], 7);
        }

        $this->render('user/estadisticas', [
            'pageTitle'    => 'Mis estadísticas',
            'totalStats'   => $totalStats,
            'diasGrafico'  => $diasGrafico,
            'porPerfil'    => $porPerfil,
            'seriesPerfil' => $seriesPerfil,
        ]);
    }

    // ---------------------------------------------------------

    public function misPerfiles(array $params = []): void
    {
        $this->requireAuth();

        $user     = $this->currentUser();
        $idUser   = (int)$user['id'];
        $perfiles = $this->perfiles->misPerfiles($idUser);
        $usuario  = $this->usuarios->find($idUser);

        $totalVistas = (int)array_sum(array_column($perfiles, 'vistas'));
        $maxVistas   = !empty($perfiles) ? (int)max(array_column($perfiles, 'vistas')) : 0;

        $this->render('user/mis-perfiles', [
            'pageTitle'   => 'Mis perfiles',
            'perfiles'    => $perfiles,
            'usuario'     => $usuario,
            'maxPerfiles' => PerfilModel::MAX_POR_USUARIO,
            'totalVistas' => $totalVistas,
            'maxVistas'   => $maxVistas,
        ]);
    }

    // ---------------------------------------------------------
    // TOKENS — panel del usuario
    // ---------------------------------------------------------

    public function misTokens(array $params = []): void
    {
        $this->requireAuth();

        $user   = $this->currentUser();
        $idUser = (int)$user['id'];
        $mov    = new TokenMovimientoModel();
        $boosts = new BoostModel();

        $page   = max(1, (int)$this->getParam('page', '1'));
        $result = $mov->historial($idUser, $page, 20);

        $this->render('user/mis-tokens', [
            'pageTitle'    => 'Mis tokens',
            'saldo'        => $mov->saldo($idUser),
            'historial'    => $result['items'],
            'pagination'   => $result,
            'boostsActivos'=> $boosts->porUsuario($idUser, 'activo'),
            'boostsProg'   => $boosts->porUsuario($idUser, 'programado'),
        ]);
    }

    // ---------------------------------------------------------
    // REACTIVACIÓN DE CUENTA (para cuentas rechazadas/suspendidas)
    // ---------------------------------------------------------

    public function showReactivacion(array $params = []): void
    {
        $this->requireAuth();

        $user    = $this->currentUser();
        $idUser  = (int)$user['id'];
        $usuario = $this->usuarios->find($idUser);

        $soporte = new SoporteMensajeModel();
        $pendiente = $soporte->ultimaAbiertaPorTipo($idUser, 'reactivacion');

        $this->render('user/solicitar-reactivacion', [
            'pageTitle'  => 'Solicitar reactivación',
            'usuario'    => $usuario,
            'pendiente'  => $pendiente,
        ]);
    }

    public function enviarReactivacion(array $params = []): void
    {
        $this->requireAuth();

        $user   = $this->currentUser();
        $idUser = (int)$user['id'];

        $asunto  = Security::sanitizeString($_POST['asunto']  ?? '');
        $mensaje = Security::sanitizeText($_POST['mensaje']   ?? '');

        if (mb_strlen($asunto) < 4 || mb_strlen($mensaje) < 20) {
            SessionManager::flash('error', 'Escribe un asunto de al menos 4 caracteres y un mensaje de al menos 20.');
            $this->redirect('/cuenta/reactivar');
        }

        // Rate limiting: máx 2 solicitudes de reactivación por 24h
        if (!Security::checkRateLimit('reactivacion_' . $idUser, 2, 86400)) {
            SessionManager::flash('error', 'Ya enviaste varias solicitudes recientemente. Espera la revisión de un administrador.');
            $this->redirect('/cuenta/reactivar');
        }

        $soporte = new SoporteMensajeModel();
        $idMsg   = $soporte->crear([
            'id_usuario' => $idUser,
            'tipo'       => 'reactivacion',
            'asunto'     => $asunto,
            'mensaje'    => $mensaje,
            'ip_envio'   => Security::getClientIp(),
        ]);

        // Notificar a todos los admins
        (new NotificacionModel())->crearParaAdmins([
            'tipo'    => 'mensaje_soporte',
            'titulo'  => 'Nueva solicitud de reactivación',
            'mensaje' => ($user['nombre'] ?? 'Usuario') . ': ' . mb_substr($asunto, 0, 100),
            'url'     => '/admin/mensajes?tipo=reactivacion',
            'icono'   => 'envelope-exclamation-fill',
            'color'   => 'warning',
        ]);

        SessionManager::flash('success', 'Tu solicitud fue enviada. Un administrador la revisará pronto.');
        $this->redirect('/cuenta/reactivar');
    }

    // ---------------------------------------------------------
    // DOCUMENTO DE IDENTIDAD (una vez por cuenta, opcional)
    // ---------------------------------------------------------

    public function showSubirDocumento(array $params = []): void
    {
        $this->requireAuth();
        $usuario = $this->usuarios->find((int)$this->currentUser()['id']);

        $this->render('user/subir-documento', [
            'pageTitle' => 'Verificar identidad',
            'usuario'   => $usuario,
        ]);
    }

    public function subirDocumento(array $params = []): void
    {
        $this->requireAuth();

        $user    = $this->currentUser();
        $idUser  = (int)$user['id'];
        $usuario = $this->usuarios->find($idUser);

        $file = $_FILES['documento'] ?? null;
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            SessionManager::flash('error', 'No se recibió el archivo correctamente.');
            $this->redirect('/mi-cuenta/documento');
        }

        if ($file['size'] > UPLOAD_MAX_SIZE) {
            SessionManager::flash('error', 'El archivo no debe superar ' . (UPLOAD_MAX_SIZE / 1024 / 1024) . ' MB.');
            $this->redirect('/mi-cuenta/documento');
        }

        // Validar MIME real
        $finfo    = finfo_open(FILEINFO_MIME_TYPE);
        $mimeReal = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeReal, UPLOAD_ALLOWED_TYPES, true)) {
            SessionManager::flash('error', 'Formato no permitido. Solo se aceptan JPG, PNG o WEBP.');
            $this->redirect('/mi-cuenta/documento');
        }

        // Borrar documento anterior si existe
        if (!empty($usuario['documento_identidad'])) {
            $old = UPLOADS_PATH . '/verificaciones/documentos/' . basename($usuario['documento_identidad']);
            if (file_exists($old)) @unlink($old);
        }

        $ext      = match($mimeReal) {
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            default      => 'jpg',
        };
        $filename = 'doc_u' . $idUser . '_' . time() . '_' . bin2hex(random_bytes(5)) . '.' . $ext;
        $destDir  = UPLOADS_PATH . '/verificaciones/documentos';
        $destPath = $destDir . '/' . $filename;

        if (!is_dir($destDir)) mkdir($destDir, 0755, true);

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            SessionManager::flash('error', 'No se pudo guardar el documento.');
            $this->redirect('/mi-cuenta/documento');
        }

        chmod($destPath, 0644);

        // Al resubir, resetear verificación y poner en revisión
        $this->usuarios->update($idUser, [
            'documento_identidad'      => $filename,
            'documento_identidad_at'   => date('Y-m-d H:i:s'),
            'documento_estado'         => 'pendiente',
            'documento_rechazo_motivo' => null,
            'documento_verificado'     => 0,
            'fecha_actualizacion'      => date('Y-m-d H:i:s'),
        ]);

        (new NotificacionModel())->crearParaAdmins([
            'tipo'    => 'documento_pendiente',
            'titulo'  => 'Nuevo documento por revisar',
            'mensaje' => ($usuario['nombre'] ?? 'Usuario') . ' envió su documento de identidad.',
            'url'     => '/admin/usuario/' . $idUser,
            'icono'   => 'file-earmark-person-fill',
            'color'   => 'warning',
        ]);

        SessionManager::flash('success', 'Documento enviado correctamente. El equipo lo revisará en breve.');
        $this->redirect('/mi-cuenta/documento');
    }

    // ---------------------------------------------------------
    // VERIFICACIÓN DE CUENTA — Cámara (una vez por usuario)
    // ---------------------------------------------------------

    public function showVerificacionCamara(array $params = []): void
    {
        $this->requireAuth();

        $user    = $this->currentUser();
        $usuario = $this->usuarios->find((int)$user['id']);

        $this->render('user/verificar-camara', [
            'pageTitle' => 'Verificar mi cuenta',
            'usuario'   => $usuario,
        ]);
    }

    public function subirVideoVerificacion(array $params = []): void
    {
        $this->requireAuth();

        $user    = $this->currentUser();
        $idUser  = (int)$user['id'];
        $usuario = $this->usuarios->find($idUser);

        if (!$usuario) {
            $this->json(['ok' => false, 'error' => 'Usuario no encontrado.'], 403);
        }

        $video = $_FILES['video'] ?? null;
        if (!$video || $video['error'] !== UPLOAD_ERR_OK) {
            $this->json(['ok' => false, 'error' => 'No se recibió el video correctamente.'], 400);
        }

        if ($video['size'] > 60 * 1024 * 1024) {
            $this->json(['ok' => false, 'error' => 'El video supera el tamaño máximo (60 MB).'], 400);
        }

        $finfo    = finfo_open(FILEINFO_MIME_TYPE);
        $mimeReal = finfo_file($finfo, $video['tmp_name']);
        finfo_close($finfo);

        $mimesPermitidos = ['video/webm', 'video/mp4', 'video/x-matroska'];
        if (!in_array($mimeReal, $mimesPermitidos, true)) {
            $this->json(['ok' => false, 'error' => 'Formato de video no permitido.'], 400);
        }

        // Si ya tenía un video anterior, borrar el archivo físico
        if (!empty($usuario['video_verificacion'])) {
            $old = UPLOADS_PATH . '/verificaciones/' . basename($usuario['video_verificacion']);
            if (file_exists($old)) @unlink($old);
        }

        $ext      = str_contains($mimeReal, 'mp4') ? 'mp4' : 'webm';
        $filename = time() . '_u' . $idUser . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        $destDir  = UPLOADS_PATH . '/verificaciones';
        $destPath = $destDir . '/' . $filename;

        if (!is_dir($destDir)) mkdir($destDir, 0755, true);

        if (!move_uploaded_file($video['tmp_name'], $destPath)) {
            $this->json(['ok' => false, 'error' => 'No se pudo guardar el video.'], 500);
        }

        chmod($destPath, 0644);

        $this->usuarios->update($idUser, [
            'video_verificacion'    => $filename,
            'video_verificacion_at' => date('Y-m-d H:i:s'),
            'fecha_actualizacion'   => date('Y-m-d H:i:s'),
        ]);

        $this->json(['ok' => true]);
    }
}
