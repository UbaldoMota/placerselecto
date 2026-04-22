<?php
/**
 * AdminController.php
 * Panel de administración: usuarios, anuncios, reportes y pagos.
 * Todas las rutas requieren middleware 'auth' + 'admin'.
 */

require_once APP_PATH . '/Controller.php';
require_once APP_PATH . '/Security.php';
require_once APP_PATH . '/models/UsuarioModel.php';
require_once APP_PATH . '/models/AnuncioModel.php';
require_once APP_PATH . '/models/PerfilModel.php';
require_once APP_PATH . '/models/PerfilFotoModel.php';
require_once APP_PATH . '/models/PerfilVideoModel.php';
require_once APP_PATH . '/models/ConfiguracionModel.php';
require_once APP_PATH . '/models/StorageScannerModel.php';
require_once APP_PATH . '/models/PagoModel.php';
require_once APP_PATH . '/models/ReporteModel.php';
require_once APP_PATH . '/models/NotificacionModel.php';
require_once APP_PATH . '/models/TokenPaqueteModel.php';
require_once APP_PATH . '/models/TokenTarifaModel.php';
require_once APP_PATH . '/models/TokenMovimientoModel.php';
require_once APP_PATH . '/models/BoostModel.php';
require_once APP_PATH . '/models/SoporteMensajeModel.php';
require_once APP_PATH . '/models/PerfilComentarioModel.php';

class AdminController extends Controller
{
    private UsuarioModel          $usuarios;
    private AnuncioModel          $anuncios;
    private PerfilModel           $perfiles;
    private PerfilFotoModel       $perfilFotos;
    private PerfilVideoModel      $perfilVideos;
    private PagoModel             $pagos;
    private ReporteModel          $reportes;
    private NotificacionModel     $notifs;
    private TokenPaqueteModel     $paquetes;
    private TokenTarifaModel      $tarifas;
    private TokenMovimientoModel  $movimientos;
    private BoostModel            $boosts;

    public function __construct()
    {
        $this->usuarios    = new UsuarioModel();
        $this->anuncios    = new AnuncioModel();
        $this->perfiles    = new PerfilModel();
        $this->perfilFotos = new PerfilFotoModel();
        $this->perfilVideos= new PerfilVideoModel();
        $this->pagos       = new PagoModel();
        $this->reportes    = new ReporteModel();
        $this->notifs      = new NotificacionModel();
        $this->paquetes    = new TokenPaqueteModel();
        $this->tarifas     = new TokenTarifaModel();
        $this->movimientos = new TokenMovimientoModel();
        $this->boosts      = new BoostModel();
    }

    // ---------------------------------------------------------
    // DASHBOARD
    // ---------------------------------------------------------

    public function dashboard(array $params = []): void
    {
        $this->requireAdmin();
        Security::setNoCacheHeaders();

        $statsUsuarios = $this->usuarios->estadisticas();
        $statsAnuncios = $this->anuncios->estadisticas();
        $statsPerfiles = $this->perfiles->estadisticas();
        $statsPagos    = $this->pagos->estadisticas();
        $statsReportes = $this->reportes->estadisticas();

        // Últimos 8 usuarios registrados
        $db = Database::getInstance()->getConnection();

        $stmtUsuarios = $db->prepare(
            "SELECT id, nombre, email, rol, verificado, estado_verificacion, fecha_creacion
             FROM usuarios ORDER BY fecha_creacion DESC LIMIT 8"
        );
        $stmtUsuarios->execute();
        $ultimosUsuarios = $stmtUsuarios->fetchAll();

        // Perfiles pendientes de aprobación
        $stmtPendientes = $db->prepare(
            "SELECT p.id, p.nombre, p.ciudad, p.fecha_creacion, u.nombre AS usuario_nombre
             FROM perfiles p
             LEFT JOIN usuarios u ON u.id = p.id_usuario
             WHERE p.estado = 'pendiente'
             ORDER BY p.fecha_creacion ASC
             LIMIT 6"
        );
        $stmtPendientes->execute();
        $perfilesPendientes = $stmtPendientes->fetchAll();

        // Ingresos por mes (últimos 6 meses)
        $stmtIngresos = $db->prepare(
            "SELECT DATE_FORMAT(fecha_pago, '%Y-%m') AS mes,
                    SUM(monto) AS total,
                    COUNT(*) AS transacciones
             FROM pagos
             WHERE estado = 'completado'
               AND fecha_pago >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
             GROUP BY mes
             ORDER BY mes ASC"
        );
        $stmtIngresos->execute();
        $ingresosMes = $stmtIngresos->fetchAll();

        $this->render('admin/dashboard', [
            'pageTitle'           => 'Panel de administración',
            'statsUsuarios'       => $statsUsuarios,
            'statsAnuncios'       => $statsAnuncios,
            'statsPerfiles'       => $statsPerfiles,
            'statsPagos'          => $statsPagos,
            'statsReportes'       => $statsReportes,
            'ultimosUsuarios'     => $ultimosUsuarios,
            'perfilesPendientes'  => $perfilesPendientes,
            'ingresosMes'         => $ingresosMes,
        ]);
    }

    // ---------------------------------------------------------
    // USUARIOS
    // ---------------------------------------------------------

    public function users(array $params = []): void
    {
        $this->requireAdmin();

        $page   = max(1, (int) $this->getParam('page', '1'));
        $estado = $this->getParam('estado', '');
        $buscar = $this->getParam('q', '');

        $result = $this->usuarios->listarPaginado($page, $estado, $buscar);

        $this->render('admin/users', [
            'pageTitle'  => 'Gestión de usuarios',
            'usuarios'   => $result['items'],
            'pagination' => $result,
            'filtros'    => compact('estado', 'buscar'),
        ]);
    }

    public function userDetail(array $params = []): void
    {
        $this->requireAdmin();

        $id      = (int) ($params['id'] ?? 0);
        $usuario = $this->usuarios->find($id);

        if (!$usuario) {
            SessionManager::flash('error', 'Usuario no encontrado.');
            $this->redirect('/admin/usuarios');
        }

        // Anuncios del usuario
        $db = Database::getInstance()->getConnection();
        $stmtAds = $db->prepare(
            "SELECT a.*, c.nombre AS categoria_nombre
             FROM anuncios a
             LEFT JOIN categorias c ON c.id = a.id_categoria
             WHERE a.id_usuario = ?
             ORDER BY a.fecha_creacion DESC
             LIMIT 20"
        );
        $stmtAds->execute([$id]);
        $anuncios = $stmtAds->fetchAll();

        // Pagos del usuario
        $pagos = $this->pagos->historialUsuario($id);

        // Perfiles del usuario con fotos de verificación
        $stmtPerfiles = $db->prepare(
            "SELECT p.id, p.nombre, p.estado,
                    (SELECT COUNT(*) FROM perfil_fotos pf WHERE pf.id_perfil = p.id AND pf.es_verificacion = 1) AS fotos_ver
             FROM perfiles p WHERE p.id_usuario = ? ORDER BY p.fecha_creacion DESC"
        );
        $stmtPerfiles->execute([$id]);
        $perfilesUsuario = $stmtPerfiles->fetchAll();

        $this->render('admin/user-detail', [
            'pageTitle'       => 'Detalle de usuario',
            'usuario'         => $usuario,
            'anuncios'        => $anuncios,
            'pagos'           => $pagos['items'],
            'perfilesUsuario' => $perfilesUsuario,
        ]);
    }

    public function approveUser(array $params = []): void
    {
        $this->requireAdmin();

        $id      = (int) ($params['id'] ?? 0);
        $usuario = $this->usuarios->find($id);

        if (!$usuario) {
            SessionManager::flash('error', 'Usuario no encontrado.');
            $this->redirect('/admin/usuarios');
        }

        if ($usuario['rol'] === 'admin') {
            SessionManager::flash('error', 'No puedes modificar otros administradores.');
            $this->redirect('/admin/usuarios');
        }

        $this->usuarios->aprobar($id);

        $db = Database::getInstance()->getConnection();

        // Publicar anuncios pendientes de este usuario que esperaban verificación
        $stmtAds = $db->prepare(
            "UPDATE anuncios
             SET estado = 'publicado',
                 fecha_publicacion = NOW(),
                 fecha_expiracion  = DATE_ADD(NOW(), INTERVAL 30 DAY)
             WHERE id_usuario = ?
               AND estado = 'pendiente'"
        );
        $stmtAds->execute([$id]);
        $publicadosAds = $stmtAds->rowCount();

        // Publicar perfiles pendientes de este usuario
        $stmtPerfiles = $db->prepare(
            "UPDATE perfiles
             SET estado = 'publicado', fecha_publicacion = NOW()
             WHERE id_usuario = ? AND estado = 'pendiente'"
        );
        $stmtPerfiles->execute([$id]);
        $publicadosPerfiles = $stmtPerfiles->rowCount();

        $msg = "Usuario aprobado correctamente.";
        if ($publicadosAds > 0) {
            $msg .= " Se publicaron {$publicadosAds} anuncio(s) pendiente(s).";
        }
        if ($publicadosPerfiles > 0) {
            $msg .= " Se publicaron {$publicadosPerfiles} perfil(es) pendiente(s).";
        }
        $publicados = $publicadosAds; // keep var alive for existing message check

        $this->notifs->crear($id, [
            'tipo'    => 'cuenta_aprobada',
            'titulo'  => '¡Tu cuenta fue aprobada!',
            'mensaje' => 'Ya puedes publicar perfiles y destacarlos. Bienvenido.',
            'url'     => '/dashboard',
            'icono'   => 'patch-check-fill',
            'color'   => 'success',
        ]);

        SessionManager::flash('success', $msg);

        $referer = $_SERVER['HTTP_REFERER'] ?? '/admin/usuarios';
        $this->redirect(str_contains($referer, 'detalle') ? "/admin/usuario/{$id}" : '/admin/usuarios');
    }

    public function deleteUser(array $params = []): void
    {
        $this->requireAdmin();

        $id      = (int) ($params['id'] ?? 0);
        $usuario = $this->usuarios->find($id);

        if (!$usuario) {
            SessionManager::flash('error', 'Usuario no encontrado.');
            $this->redirect('/admin/usuarios');
        }

        if ($usuario['rol'] === 'admin') {
            SessionManager::flash('error', 'No puedes eliminar administradores.');
            $this->redirect('/admin/usuarios');
        }

        // Confirmación extra por POST
        $confirmar = $_POST['confirmar'] ?? '';
        if ($confirmar !== 'SI_ELIMINAR') {
            SessionManager::flash('error', 'Confirmación incorrecta. El usuario no fue eliminado.');
            $this->redirect("/admin/usuario/{$id}");
        }

        $resumen = $this->usuarios->eliminarCompleto($id);

        $msg = "Usuario <strong>" . e($usuario['nombre']) . "</strong> eliminado correctamente.";
        if ($resumen['perfiles'] > 0) $msg .= " Perfiles eliminados: {$resumen['perfiles']}.";
        if ($resumen['anuncios'] > 0) $msg .= " Anuncios eliminados: {$resumen['anuncios']}.";
        if ($resumen['archivos'] > 0) $msg .= " Archivos de imagen eliminados: {$resumen['archivos']}.";

        SessionManager::flash('success', $msg);
        $this->redirect('/admin/usuarios');
    }

    public function rejectUser(array $params = []): void
    {
        $this->requireAdmin();

        $id      = (int) ($params['id'] ?? 0);
        $usuario = $this->usuarios->find($id);

        if (!$usuario) {
            SessionManager::flash('error', 'Usuario no encontrado.');
            $this->redirect('/admin/usuarios');
        }

        if ($usuario['rol'] === 'admin') {
            SessionManager::flash('error', 'No puedes modificar otros administradores.');
            $this->redirect('/admin/usuarios');
        }

        $this->usuarios->rechazar($id);

        $this->notifs->crear($id, [
            'tipo'    => 'cuenta_rechazada',
            'titulo'  => 'Tu cuenta fue rechazada',
            'mensaje' => 'Contacta a soporte si crees que es un error.',
            'url'     => '/dashboard',
            'icono'   => 'x-octagon-fill',
            'color'   => 'danger',
        ]);

        SessionManager::flash('success', 'Usuario rechazado. Sus anuncios no serán visibles.');

        $referer = $_SERVER['HTTP_REFERER'] ?? '/admin/usuarios';
        $this->redirect(str_contains($referer, 'detalle') ? "/admin/usuario/{$id}" : '/admin/usuarios');
    }

    // ---------------------------------------------------------
    // CONFIABILIDAD — toggle documento_verificado / fotos_verificadas
    // ---------------------------------------------------------

    public function toggleVerificacion(array $params = []): void
    {
        $this->requireAdmin();

        $id     = (int) ($params['id'] ?? 0);
        $campo  = $this->postParam('campo', '');
        $valor  = (bool) (int) $this->postParam('valor', '0');

        $usuario = $this->usuarios->find($id);
        if (!$usuario) {
            SessionManager::flash('error', 'Usuario no encontrado.');
            $this->redirect('/admin/usuarios');
        }

        $ok = $this->usuarios->setVerificacion($id, $campo, $valor);

        // Sincronizar documento_estado cuando se verifica/revoca el documento
        if ($ok && $campo === 'documento_verificado') {
            $this->usuarios->update($id, [
                'documento_estado'         => $valor ? 'verificado' : 'pendiente',
                'documento_rechazo_motivo' => null,
                'fecha_actualizacion'      => date('Y-m-d H:i:s'),
            ]);
        }

        if ($ok) {
            if ($campo === 'documento_verificado' && $valor) {
                $this->notifs->crear($id, [
                    'tipo'    => 'documento_verificado',
                    'titulo'  => 'Documento verificado',
                    'mensaje' => 'Tu documento de identidad fue aprobado. Gracias por verificarte.',
                    'url'     => '/mi-cuenta/documento',
                    'icono'   => 'patch-check-fill',
                    'color'   => 'success',
                ]);
            }
            if ($campo === 'fotos_verificadas' && $valor) {
                $this->notifs->crear($id, [
                    'tipo'    => 'fotos_verificadas',
                    'titulo'  => 'Fotos verificadas',
                    'mensaje' => 'Tus fotos fueron aprobadas como verificadas.',
                    'url'     => '/mis-perfiles',
                    'icono'   => 'camera-fill',
                    'color'   => 'success',
                ]);
            }
            $labels = [
                'documento_verificado' => $valor ? 'Documento marcado como verificado.' : 'Verificación de documento revocada.',
                'fotos_verificadas'    => $valor ? 'Fotos verificadas activadas.'       : 'Fotos verificadas desactivadas.',
            ];
            SessionManager::flash('success', $labels[$campo] ?? 'Actualizado.');
        } else {
            SessionManager::flash('error', 'No se pudo actualizar.');
        }

        $this->redirect("/admin/usuario/{$id}");
    }

    // ---------------------------------------------------------
    // ANUNCIOS
    // ---------------------------------------------------------

    public function ads(array $params = []): void
    {
        $this->requireAdmin();

        $page   = max(1, (int) $this->getParam('page', '1'));
        $estado = $this->getParam('estado', '');
        $buscar = $this->getParam('q', '');

        $result = $this->anuncios->listarAdmin($page, $estado, $buscar);

        $this->render('admin/ads', [
            'pageTitle'  => 'Gestión de anuncios',
            'anuncios'   => $result['items'],
            'pagination' => $result,
            'filtros'    => compact('estado', 'buscar'),
        ]);
    }

    public function publishAd(array $params = []): void
    {
        $this->requireAdmin();

        $id      = (int) ($params['id'] ?? 0);
        $anuncio = $this->anuncios->find($id);

        if (!$anuncio) {
            SessionManager::flash('error', 'Anuncio no encontrado.');
            $this->redirect('/admin/anuncios');
        }

        // Verificar que el dueño del anuncio esté verificado
        $usuario = $this->usuarios->find((int) $anuncio['id_usuario']);
        if (!$usuario || !$usuario['verificado']) {
            SessionManager::flash('warning', 'El usuario de este anuncio no está verificado aún.');
            $this->redirect('/admin/anuncios');
        }

        $this->anuncios->publicar($id);

        $this->notifs->crear((int)$anuncio['id_usuario'], [
            'tipo'    => 'anuncio_publicado',
            'titulo'  => 'Tu anuncio fue publicado',
            'mensaje' => '"' . mb_substr($anuncio['titulo'] ?? 'Tu anuncio', 0, 80) . '" ya es visible para los usuarios.',
            'url'     => '/anuncio/' . $id,
            'icono'   => 'check-circle-fill',
            'color'   => 'success',
        ]);

        SessionManager::flash('success', 'Anuncio publicado correctamente.');
        $this->redirect('/admin/anuncios' . ($this->getParam('page') ? '?page=' . $this->getParam('page') : ''));
    }

    public function deleteAd(array $params = []): void
    {
        $this->requireAdmin();

        $id      = (int) ($params['id'] ?? 0);
        $anuncio = $this->anuncios->find($id);

        if (!$anuncio) {
            SessionManager::flash('error', 'Anuncio no encontrado.');
            $this->redirect('/admin/anuncios');
        }

        $idUsuario = (int)$anuncio['id_usuario'];
        $titulo    = $anuncio['titulo'] ?? 'Tu anuncio';

        if ($anuncio['imagen_principal']) {
            (new \Upload())->delete($anuncio['imagen_principal'], 'anuncios');
        }

        $this->anuncios->delete($id);

        $this->notifs->crear($idUsuario, [
            'tipo'    => 'anuncio_eliminado',
            'titulo'  => 'Tu anuncio fue eliminado',
            'mensaje' => '"' . mb_substr($titulo, 0, 80) . '" fue eliminado por un administrador. Contacta a soporte si crees que es un error.',
            'url'     => '/mis-anuncios',
            'icono'   => 'trash-fill',
            'color'   => 'danger',
        ]);

        SessionManager::flash('success', 'Anuncio eliminado. Se notificó al usuario.');
        $this->redirect('/admin/anuncios');
    }

    // ---------------------------------------------------------
    // REPORTES
    // ---------------------------------------------------------

    public function reports(array $params = []): void
    {
        $this->requireAdmin();

        $page   = max(1, (int) $this->getParam('page', '1'));
        $estado = $this->getParam('estado', '');

        $result = $this->reportes->listarAdmin($page, $estado);

        $this->render('admin/reports', [
            'pageTitle'  => 'Reportes de contenido',
            'reportes'   => $result['items'],
            'pagination' => $result,
            'filtros'    => compact('estado'),
        ]);
    }

    /** Utilidad: registra fecha_resolucion + id_admin_resolucion en un reporte. */
    private function marcarResolucion(int $idReporte, string $estado, ?string $nota = null): void
    {
        $admin = $this->currentUser();
        $db    = Database::getInstance()->getConnection();
        $sets  = ['estado = ?', 'fecha_resolucion = NOW()', 'id_admin_resolucion = ?'];
        $vals  = [$estado, (int)$admin['id']];
        if ($nota !== null) {
            $sets[] = 'nota_admin = ?';
            $vals[] = mb_substr($nota, 0, 1000);
        }
        $vals[] = $idReporte;
        $db->prepare('UPDATE reportes SET ' . implode(', ', $sets) . ' WHERE id = ?')
           ->execute($vals);
    }

    public function resolveReport(array $params = []): void
    {
        $this->requireAdmin();

        $id     = (int) ($params['id'] ?? 0);
        $accion = Security::sanitizeString($_POST['accion'] ?? 'resolver');

        $reporte = $this->reportes->find($id);
        if (!$reporte) {
            SessionManager::flash('error', 'Reporte no encontrado.');
            $this->redirect('/admin/reportes');
        }

        $seEliminoContenido = false;

        if ($accion === 'eliminar_anuncio') {
            $anuncio = $this->anuncios->find((int) $reporte['id_anuncio']);
            if ($anuncio) {
                if ($anuncio['imagen_principal']) {
                    (new \Upload())->delete($anuncio['imagen_principal'], 'anuncios');
                }
                $this->anuncios->delete((int) $reporte['id_anuncio']);

                $this->notifs->crear((int)$anuncio['id_usuario'], [
                    'tipo'    => 'anuncio_eliminado',
                    'titulo'  => 'Tu anuncio fue eliminado por un reporte',
                    'mensaje' => '"' . mb_substr($anuncio['titulo'] ?? 'Tu anuncio', 0, 80) . '" fue retirado tras la resolución de un reporte.',
                    'url'     => '/mis-anuncios',
                    'icono'   => 'trash-fill',
                    'color'   => 'danger',
                ]);

                $seEliminoContenido = true;
            }
            $this->reportes->resolver($id);
            SessionManager::flash('success', 'Reporte resuelto y anuncio eliminado.');
        } else {
            $this->reportes->resolver($id);
            SessionManager::flash('success', 'Reporte marcado como resuelto.');
        }

        // Notificar al reportero (si estaba logueado al reportar)
        if (!empty($reporte['id_usuario'])) {
            $this->notifs->crear((int)$reporte['id_usuario'], [
                'tipo'    => 'reporte_resuelto',
                'titulo'  => $seEliminoContenido
                    ? '¡Tu reporte llevó a acción!'
                    : 'Tu reporte fue revisado',
                'mensaje' => $seEliminoContenido
                    ? 'Gracias por ayudarnos a mantener la comunidad segura. El contenido reportado fue eliminado.'
                    : 'Revisamos tu reporte. En este caso el contenido no infringió nuestras políticas, pero agradecemos tu colaboración.',
                'url'     => '/dashboard',
                'icono'   => $seEliminoContenido ? 'patch-check-fill' : 'flag-fill',
                'color'   => $seEliminoContenido ? 'success' : 'info',
            ]);
        }

        $this->redirect('/admin/reportes');
    }

    // ---------------------------------------------------------
    // PAGOS
    // ---------------------------------------------------------

    public function payments(array $params = []): void
    {
        $this->requireAdmin();

        $page   = max(1, (int) $this->getParam('page', '1'));
        $estado = $this->getParam('estado', '');

        $result = $this->pagos->listarAdmin($page, $estado);

        $this->render('admin/payments', [
            'pageTitle'  => 'Registro de pagos',
            'pagos'      => $result['items'],
            'pagination' => $result,
            'filtros'    => compact('estado'),
            'statsP'     => $this->pagos->estadisticas(),
        ]);
    }

    // ---------------------------------------------------------
    // PERFILES
    // ---------------------------------------------------------

    public function perfiles(array $params = []): void
    {
        $this->requireAdmin();

        $page   = max(1, (int) $this->getParam('page', '1'));
        $estado = $this->getParam('estado', '');
        $buscar = $this->getParam('q', '');

        $result = $this->perfiles->listarAdmin(
            array_filter(compact('estado', 'buscar')),
            $page
        );

        $this->render('admin/perfiles', [
            'pageTitle'  => 'Gestión de perfiles',
            'perfiles'   => $result['items'],
            'pagination' => $result,
            'filtros'    => compact('estado', 'buscar'),
            'stats'      => $this->perfiles->estadisticas(),
        ]);
    }

    public function previewProfile(array $params = []): void
    {
        $this->requireAdmin();

        $id     = (int)($params['id'] ?? 0);
        $perfil = $this->perfiles->obtenerPublico($id);

        if (!$perfil) {
            SessionManager::flash('error', 'Perfil no encontrado.');
            $this->redirect('/admin/perfiles');
        }

        // galería (incluyendo ocultas) + verificación — separados para la vista
        $fotosGaleria  = $this->perfilFotos->galeriaAdmin($id);
        $fotosVer      = $this->perfilFotos->verificacion($id);
        $confiabilidad = $this->usuarios->confiabilidad((int)$perfil['id_usuario']);

        $this->render('admin/perfil-preview', [
            'pageTitle'     => 'Previsualizar perfil — ' . $perfil['nombre'],
            'perfil'        => $perfil,
            'fotosGaleria'  => $fotosGaleria,
            'fotosVer'      => $fotosVer,
            'videos'        => $this->perfilVideos->listarAdmin($id),
            'confiabilidad' => $confiabilidad,
        ]);
    }

    public function toggleHidePhoto(array $params = []): void
    {
        $this->requireAdmin();

        $id   = (int)($params['id'] ?? 0);
        $foto = $this->perfilFotos->find($id);

        if (!$foto) {
            SessionManager::flash('error', 'Foto no encontrada.');
            $this->redirect('/admin/perfiles');
        }

        $this->perfilFotos->toggleOculta($id);

        $ahoraOculta = !(bool)$foto['oculta'];

        // Notificar solo al ocultar (no al restaurar — sería muy ruidoso con toggles)
        if ($ahoraOculta) {
            $perfil = $this->perfiles->find((int)$foto['id_perfil']);
            if ($perfil) {
                $this->notifs->crear((int)$perfil['id_usuario'], [
                    'tipo'    => 'foto_ocultada',
                    'titulo'  => 'Una foto fue ocultada',
                    'mensaje' => 'Un administrador ocultó una foto de tu perfil "' . mb_substr($perfil['nombre'], 0, 60) . '". Puedes reemplazarla editando el perfil.',
                    'url'     => '/perfil/' . (int)$foto['id_perfil'] . '/editar',
                    'icono'   => 'eye-slash-fill',
                    'color'   => 'warning',
                ]);
            }
        }

        SessionManager::flash('success', $ahoraOculta
            ? 'Foto ocultada del perfil público.'
            : 'Foto restaurada al perfil público.');

        $this->redirect('/admin/perfil/' . (int)$foto['id_perfil']);
    }

    public function deletePhoto(array $params = []): void
    {
        $this->requireAdmin();

        $id   = (int)($params['id'] ?? 0);
        $foto = $this->perfilFotos->find($id);

        if (!$foto) {
            SessionManager::flash('error', 'Foto no encontrada.');
            $this->redirect('/admin/perfiles');
        }

        $idPerfil = (int)$foto['id_perfil'];
        $perfil   = $this->perfiles->find($idPerfil);

        // Borrar archivo físico
        $path = UPLOADS_PATH . '/anuncios/' . basename($foto['nombre_archivo']);
        if (file_exists($path) && is_file($path)) {
            @unlink($path);
        }

        $this->perfilFotos->delete($id);

        if ($perfil) {
            $this->notifs->crear((int)$perfil['id_usuario'], [
                'tipo'    => 'foto_eliminada',
                'titulo'  => 'Se eliminó una foto de tu perfil',
                'mensaje' => 'Un administrador eliminó una foto del perfil "' . mb_substr($perfil['nombre'], 0, 60) . '". Puedes subir otra editando el perfil.',
                'url'     => '/perfil/' . $idPerfil . '/editar',
                'icono'   => 'image-alt',
                'color'   => 'warning',
            ]);
        }

        SessionManager::flash('success', 'Foto eliminada permanentemente. Se notificó al usuario.');
        $this->redirect('/admin/perfil/' . $idPerfil);
    }

    public function publishProfile(array $params = []): void
    {
        $this->requireAdmin();

        $id     = (int)($params['id'] ?? 0);
        $perfil = $this->perfiles->find($id);

        if (!$perfil) {
            SessionManager::flash('error', 'Perfil no encontrado.');
            $this->redirect('/admin/perfiles');
        }

        $usuario = $this->usuarios->find((int)$perfil['id_usuario']);
        if (!$usuario || !$usuario['verificado']) {
            SessionManager::flash('warning', 'El usuario de este perfil no está verificado aún.');
            $this->redirect('/admin/perfiles');
        }

        $this->perfiles->publicar($id);

        // Publicar un perfil implica que el admin revisó las fotos.
        // Si el perfil tiene fotos de verificación, marcar al usuario como "fotos verificadas".
        $fotosVer        = $this->perfilFotos->verificacion($id);
        $fotosYaMarcadas = (bool)($usuario['fotos_verificadas'] ?? 0);

        if (!empty($fotosVer) && !$fotosYaMarcadas) {
            $this->usuarios->setVerificacion((int)$perfil['id_usuario'], 'fotos_verificadas', true);
        }

        $this->notifs->crear((int)$perfil['id_usuario'], [
            'tipo'    => 'perfil_publicado',
            'titulo'  => 'Tu perfil fue publicado',
            'mensaje' => '"' . mb_substr($perfil['nombre'], 0, 80) . '" ya es visible para los usuarios.',
            'url'     => '/perfil/' . $id,
            'icono'   => 'check-circle-fill',
            'color'   => 'success',
        ]);

        SessionManager::flash('success', 'Perfil publicado correctamente.');
        $this->redirect('/admin/perfiles');
    }

    public function rejectProfile(array $params = []): void
    {
        $this->requireAdmin();

        $id     = (int)($params['id'] ?? 0);
        $perfil = $this->perfiles->find($id);

        if (!$perfil) {
            SessionManager::flash('error', 'Perfil no encontrado.');
            $this->redirect('/admin/perfiles');
        }

        $this->perfiles->rechazar($id);

        $this->notifs->crear((int)$perfil['id_usuario'], [
            'tipo'    => 'perfil_rechazado',
            'titulo'  => 'Tu perfil fue rechazado',
            'mensaje' => '"' . mb_substr($perfil['nombre'], 0, 80) . '" no pasó la revisión. Puedes editarlo y reenviarlo.',
            'url'     => '/perfil/' . $id . '/editar',
            'icono'   => 'exclamation-octagon-fill',
            'color'   => 'danger',
        ]);

        SessionManager::flash('success', 'Perfil rechazado.');
        $this->redirect('/admin/perfiles');
    }

    /**
     * POST /admin/perfil/{id}/ocultar
     * Toggle de visibilidad: oculta o muestra un perfil sin cambiar su estado.
     */
    public function togglePerfilOculto(array $params = []): void
    {
        $this->requireAdmin();

        $id     = (int)($params['id'] ?? 0);
        $perfil = $this->perfiles->find($id);

        if (!$perfil) {
            SessionManager::flash('error', 'Perfil no encontrado.');
            $this->redirect('/admin/perfiles');
        }

        $nuevaOcul = empty($perfil['oculta']) ? 1 : 0;
        $this->perfiles->update($id, ['oculta' => $nuevaOcul]);

        if ($nuevaOcul === 1) {
            $this->notifs->crear((int)$perfil['id_usuario'], [
                'tipo'    => 'perfil_oculto',
                'titulo'  => 'Tu perfil fue ocultado',
                'mensaje' => '"' . mb_substr($perfil['nombre'], 0, 80) . '" ya no aparece en los listados públicos.',
                'url'     => '/perfil/' . $id,
                'icono'   => 'eye-slash-fill',
                'color'   => 'warning',
            ]);
            SessionManager::flash('success', 'Perfil ocultado. Ya no aparece en listados públicos.');
        } else {
            $this->notifs->crear((int)$perfil['id_usuario'], [
                'tipo'    => 'perfil_visible',
                'titulo'  => 'Tu perfil es visible de nuevo',
                'mensaje' => '"' . mb_substr($perfil['nombre'], 0, 80) . '" volvió a los listados públicos.',
                'url'     => '/perfil/' . $id,
                'icono'   => 'eye-fill',
                'color'   => 'success',
            ]);
            SessionManager::flash('success', 'Perfil visible de nuevo.');
        }
        $this->redirect('/admin/perfiles');
    }

    public function rejectDocument(array $params = []): void
    {
        $this->requireAdmin();

        $id      = (int)($params['id'] ?? 0);
        $usuario = $this->usuarios->find($id);

        if (!$usuario || empty($usuario['documento_identidad'])) {
            SessionManager::flash('error', 'No hay documento para rechazar.');
            $this->redirect('/admin/usuarios');
        }

        $motivosPermitidos = [
            'ilegible'       => 'Imagen ilegible o borrosa',
            'incompleto'     => 'Documento recortado o incompleto',
            'no_coincide'    => 'El nombre no coincide con el de la cuenta',
            'vencido'        => 'Documento vencido o expirado',
            'tipo_invalido'  => 'Tipo de documento no aceptado (solo INE, IFE o pasaporte)',
            'manipulado'     => 'Documento manipulado o editado digitalmente',
            'sin_rostro'     => 'No se distingue el rostro claramente',
            'otro'           => 'Otro motivo',
        ];

        $motivoKey = Security::sanitizeString($_POST['motivo'] ?? '');
        if (!isset($motivosPermitidos[$motivoKey])) {
            SessionManager::flash('error', 'Selecciona un motivo de rechazo válido.');
            $this->redirect("/admin/usuario/{$id}");
        }

        $motivoTexto = $motivosPermitidos[$motivoKey];
        if ($motivoKey === 'otro') {
            $detalle = Security::sanitizeString($_POST['detalle'] ?? '');
            if ($detalle !== '') {
                $motivoTexto .= ': ' . mb_substr($detalle, 0, 120);
            }
        }

        $this->usuarios->update($id, [
            'documento_estado'         => 'rechazado',
            'documento_verificado'     => 0,
            'documento_rechazo_motivo' => $motivoTexto,
            'fecha_actualizacion'      => date('Y-m-d H:i:s'),
        ]);

        $this->notifs->crear($id, [
            'tipo'    => 'documento_rechazado',
            'titulo'  => 'Tu documento fue rechazado',
            'mensaje' => 'Motivo: ' . mb_substr($motivoTexto, 0, 180) . '. Sube uno nuevo.',
            'url'     => '/mi-cuenta/documento',
            'icono'   => 'file-earmark-x-fill',
            'color'   => 'danger',
        ]);

        SessionManager::flash('success', 'Documento rechazado. El usuario verá el motivo en su panel.');
        $this->redirect("/admin/usuario/{$id}");
    }

    public function serveUserDocumento(array $params = []): void
    {
        $this->requireAdmin();

        $id      = (int)($params['id'] ?? 0);
        $usuario = $this->usuarios->find($id);

        if (!$usuario || empty($usuario['documento_identidad'])) {
            http_response_code(404);
            exit;
        }

        $filename = basename($usuario['documento_identidad']);
        $path     = UPLOADS_PATH . '/verificaciones/documentos/' . $filename;

        if (!file_exists($path) || !is_file($path)) {
            http_response_code(404);
            exit;
        }

        $ext  = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $mime = match($ext) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png'         => 'image/png',
            'webp'        => 'image/webp',
            default       => 'application/octet-stream',
        };

        header('Content-Type: '    . $mime);
        header('Content-Length: '  . filesize($path));
        header('Content-Disposition: inline; filename="doc_u' . $id . '.' . $ext . '"');
        header('Cache-Control: private, no-store');
        header('X-Content-Type-Options: nosniff');

        readfile($path);
        exit;
    }

    public function serveUserVideo(array $params = []): void
    {
        $this->requireAdmin();

        $id      = (int)($params['id'] ?? 0);
        $usuario = $this->usuarios->find($id);

        if (!$usuario || empty($usuario['video_verificacion'])) {
            http_response_code(404);
            exit;
        }

        $filename = basename($usuario['video_verificacion']);
        $path     = UPLOADS_PATH . '/verificaciones/' . $filename;

        if (!file_exists($path) || !is_file($path)) {
            http_response_code(404);
            exit;
        }

        $ext      = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $mime     = $ext === 'mp4' ? 'video/mp4' : 'video/webm';
        $filesize = filesize($path);

        header('Content-Type: '    . $mime);
        header('Content-Length: '  . $filesize);
        header('Content-Disposition: inline; filename="verificacion_u' . $id . '.' . $ext . '"');
        header('Cache-Control: private, no-store');
        header('X-Content-Type-Options: nosniff');

        readfile($path);
        exit;
    }

    public function serveProfileVideo(array $params = []): void
    {
        $this->requireAdmin();

        $id     = (int)($params['id'] ?? 0);
        $perfil = $this->perfiles->find($id);

        if (!$perfil || empty($perfil['video_verificacion'])) {
            http_response_code(404);
            exit;
        }

        $filename = basename($perfil['video_verificacion']);
        $path     = UPLOADS_PATH . '/verificaciones/perfiles/' . $filename;

        if (!file_exists($path) || !is_file($path)) {
            http_response_code(404);
            exit;
        }

        $ext      = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $mime     = $ext === 'mp4' ? 'video/mp4' : 'video/webm';
        $filesize = filesize($path);

        header('Content-Type: '    . $mime);
        header('Content-Length: '  . $filesize);
        header('Content-Disposition: inline; filename="verificacion_p' . $id . '.' . $ext . '"');
        header('Cache-Control: private, no-store');
        header('X-Content-Type-Options: nosniff');

        readfile($path);
        exit;
    }

    public function deleteProfile(array $params = []): void
    {
        $this->requireAdmin();

        $id     = (int)($params['id'] ?? 0);
        $perfil = $this->perfiles->find($id);

        if (!$perfil) {
            SessionManager::flash('error', 'Perfil no encontrado.');
            $this->redirect('/admin/perfiles');
        }

        $idUsuario     = (int)$perfil['id_usuario'];
        $nombrePerfil  = $perfil['nombre'];

        $this->perfilFotos->eliminarPorAnuncio($id);
        $this->perfiles->delete($id);

        $this->notifs->crear($idUsuario, [
            'tipo'    => 'perfil_eliminado',
            'titulo'  => 'Tu perfil fue eliminado',
            'mensaje' => 'El perfil "' . mb_substr($nombrePerfil, 0, 80) . '" fue eliminado por un administrador. Contacta a soporte si crees que es un error.',
            'url'     => '/mis-perfiles',
            'icono'   => 'trash-fill',
            'color'   => 'danger',
        ]);

        SessionManager::flash('success', 'Perfil eliminado. Se notificó al usuario.');
        $this->redirect('/admin/perfiles');
    }

    // =========================================================
    // TOKENS — dashboard, paquetes, tarifas, movimientos
    // =========================================================

    public function tokensIndex(array $params = []): void
    {
        $this->requireAdmin();
        Security::setNoCacheHeaders();

        $this->render('admin/tokens-index', [
            'pageTitle' => 'Sistema de tokens',
            'stats'     => $this->movimientos->estadisticas(),
            'statsBoost'=> $this->boosts->estadisticas(),
            'paquetes'  => $this->paquetes->todos(),
            'tarifas'   => $this->tarifas->mapa(),
        ]);
    }

    public function tokensPaquetes(array $params = []): void
    {
        $this->requireAdmin();
        $this->render('admin/tokens-paquetes', [
            'pageTitle' => 'Paquetes de tokens',
            'paquetes'  => $this->paquetes->todos(),
        ]);
    }

    public function tokensPaqueteCrear(array $params = []): void
    {
        $this->requireAdmin();
        $id = $this->paquetes->crear([
            'nombre'    => $this->postParam('nombre'),
            'monto_mxn' => (float)($_POST['monto_mxn'] ?? 0),
            'tokens'    => (int)($_POST['tokens'] ?? 0),
            'bonus_pct' => (int)($_POST['bonus_pct'] ?? 0),
            'orden'     => (int)($_POST['orden'] ?? 0),
            'activo'    => isset($_POST['activo']) ? 1 : 0,
        ]);
        SessionManager::flash('success', "Paquete creado (ID {$id}).");
        $this->redirect('/admin/tokens/paquetes');
    }

    public function tokensPaqueteEditar(array $params = []): void
    {
        $this->requireAdmin();
        $id = (int)($params['id'] ?? 0);
        $this->paquetes->editar($id, [
            'nombre'    => $this->postParam('nombre'),
            'monto_mxn' => (float)($_POST['monto_mxn'] ?? 0),
            'tokens'    => (int)($_POST['tokens'] ?? 0),
            'bonus_pct' => (int)($_POST['bonus_pct'] ?? 0),
            'orden'     => (int)($_POST['orden'] ?? 0),
            'activo'    => isset($_POST['activo']) ? 1 : 0,
        ]);
        SessionManager::flash('success', 'Paquete actualizado.');
        $this->redirect('/admin/tokens/paquetes');
    }

    public function tokensPaqueteToggle(array $params = []): void
    {
        $this->requireAdmin();
        $id = (int)($params['id'] ?? 0);
        $this->paquetes->toggleActivo($id);
        SessionManager::flash('success', 'Estado del paquete cambiado.');
        $this->redirect('/admin/tokens/paquetes');
    }

    public function tokensPaqueteEliminar(array $params = []): void
    {
        $this->requireAdmin();
        $id = (int)($params['id'] ?? 0);
        $this->paquetes->delete($id);
        SessionManager::flash('success', 'Paquete eliminado.');
        $this->redirect('/admin/tokens/paquetes');
    }

    public function tokensTarifas(array $params = []): void
    {
        $this->requireAdmin();
        $this->render('admin/tokens-tarifas', [
            'pageTitle' => 'Tarifas de consumo',
            'tarifas'   => $this->tarifas->mapa(),
        ]);
    }

    public function tokensTarifaActualizar(array $params = []): void
    {
        $this->requireAdmin();

        $tipo  = $params['tipo'] ?? '';
        $tph   = max(1, (int)($_POST['tokens_por_hora'] ?? 0));
        $desc  = Security::sanitizeString($_POST['descripcion'] ?? '');

        if (!in_array($tipo, TokenTarifaModel::TIPOS, true)) {
            SessionManager::flash('error', 'Tipo de tarifa inválido.');
            $this->redirect('/admin/tokens/tarifas');
        }

        $this->tarifas->actualizarPorTipo($tipo, $tph, $desc ?: null);
        SessionManager::flash('success', "Tarifa [{$tipo}] actualizada a {$tph} tokens/hora.");
        $this->redirect('/admin/tokens/tarifas');
    }

    public function tokensMovimientos(array $params = []): void
    {
        $this->requireAdmin();

        $page    = max(1, (int)$this->getParam('page', '1'));
        $filtros = [
            'tipo'  => $this->getParam('tipo', ''),
            'email' => $this->getParam('email', ''),
        ];
        $result = $this->movimientos->historialAdmin($page, 30, $filtros);

        $this->render('admin/tokens-movimientos', [
            'pageTitle'  => 'Movimientos de tokens',
            'items'      => $result['items'],
            'pagination' => $result,
            'filtros'    => $filtros,
        ]);
    }

    // =========================================================
    // SALDO DE USUARIO — ajuste manual por admin
    // =========================================================

    public function ajustarSaldo(array $params = []): void
    {
        $this->requireAdmin();

        $id       = (int)($params['id'] ?? 0);
        $accion   = Security::sanitizeString($_POST['accion'] ?? '');   // 'sumar' | 'restar'
        $cantidad = max(0, (int)($_POST['cantidad'] ?? 0));
        $motivo   = Security::sanitizeString($_POST['motivo'] ?? '');

        if ($cantidad <= 0) {
            SessionManager::flash('error', 'La cantidad debe ser mayor a cero.');
            $this->redirect("/admin/usuario/{$id}");
        }
        if ($motivo === '') {
            SessionManager::flash('error', 'Debes indicar el motivo del ajuste.');
            $this->redirect("/admin/usuario/{$id}");
        }

        $usuario = $this->usuarios->find($id);
        if (!$usuario) {
            SessionManager::flash('error', 'Usuario no encontrado.');
            $this->redirect('/admin/usuarios');
        }

        $signo   = $accion === 'restar' ? -1 : 1;
        $result  = $this->movimientos->aplicar(
            $id, 'ajuste_admin', $signo * $cantidad, null, null,
            "Ajuste admin: {$motivo}"
        );

        if (!$result['ok']) {
            SessionManager::flash('error', $result['error'] ?? 'No se pudo aplicar el ajuste.');
            $this->redirect("/admin/usuario/{$id}");
        }

        // Notificar al usuario
        $this->notifs->crear($id, [
            'tipo'    => 'saldo_ajustado',
            'titulo'  => $signo > 0 ? 'Se agregaron tokens a tu cuenta' : 'Se restaron tokens de tu cuenta',
            'mensaje' => ($signo > 0 ? '+' : '-') . $cantidad . " tokens. Motivo: " . mb_substr($motivo, 0, 140),
            'url'     => '/mis-tokens',
            'icono'   => 'coin',
            'color'   => $signo > 0 ? 'success' : 'warning',
        ]);

        SessionManager::flash('success',
            "Saldo actualizado: " . ($signo > 0 ? '+' : '-') . "{$cantidad} tokens. Nuevo saldo: {$result['saldo_despues']}.");
        $this->redirect("/admin/usuario/{$id}");
    }

    // =========================================================
    // MENSAJES DE SOPORTE (reactivación, dudas, etc.)
    // =========================================================

    public function mensajes(array $params = []): void
    {
        $this->requireAdmin();
        Security::setNoCacheHeaders();

        $soporte = new SoporteMensajeModel();
        $page    = max(1, (int)$this->getParam('page', '1'));
        $filtros = [
            'estado' => $this->getParam('estado', ''),
            'tipo'   => $this->getParam('tipo', ''),
            'buscar' => $this->getParam('q', ''),
        ];
        $result = $soporte->listarAdmin($page, $filtros);

        $this->render('admin/mensajes', [
            'pageTitle'  => 'Mensajes de soporte',
            'mensajes'   => $result['items'],
            'pagination' => $result,
            'filtros'    => $filtros,
            'abiertos'   => $soporte->contarAbiertos(),
        ]);
    }

    public function responderMensaje(array $params = []): void
    {
        $this->requireAdmin();

        $id         = (int)($params['id'] ?? 0);
        $respuesta  = Security::sanitizeText($_POST['respuesta'] ?? '');
        $aprobar    = !empty($_POST['aprobar_reactivacion']);

        if (mb_strlen($respuesta) < 5) {
            SessionManager::flash('error', 'La respuesta debe tener al menos 5 caracteres.');
            $this->redirect('/admin/mensajes');
        }

        $soporte = new SoporteMensajeModel();
        $msg     = $soporte->find($id);
        if (!$msg) {
            SessionManager::flash('error', 'Mensaje no encontrado.');
            $this->redirect('/admin/mensajes');
        }

        $admin   = $this->currentUser();
        $soporte->responder($id, (int)$admin['id'], $respuesta);

        // Si es reactivación y el admin marcó "aprobar", también reactivamos la cuenta
        $cuentaReactivada = false;
        if ($aprobar && $msg['tipo'] === 'reactivacion') {
            $this->usuarios->update((int)$msg['id_usuario'], [
                'estado_verificacion' => 'aprobado',
                'verificado'          => 1,
                'fecha_actualizacion' => date('Y-m-d H:i:s'),
            ]);
            $cuentaReactivada = true;
        }

        // Notificar al usuario con la respuesta
        $this->notifs->crear((int)$msg['id_usuario'], [
            'tipo'    => 'mensaje_respondido',
            'titulo'  => $cuentaReactivada
                ? '¡Tu cuenta fue reactivada!'
                : 'Respuesta a tu solicitud',
            'mensaje' => $cuentaReactivada
                ? 'Tu cuenta está activa de nuevo. Ya puedes crear perfiles. Respuesta: ' . mb_substr($respuesta, 0, 120)
                : mb_substr($respuesta, 0, 200),
            'url'     => $msg['tipo'] === 'reactivacion' ? '/cuenta/reactivar' : '/dashboard',
            'icono'   => $cuentaReactivada ? 'patch-check-fill' : 'envelope-check-fill',
            'color'   => $cuentaReactivada ? 'success' : 'primary',
        ]);

        SessionManager::flash('success',
            $cuentaReactivada
                ? 'Respuesta enviada y cuenta reactivada.'
                : 'Respuesta enviada al usuario.');
        $this->redirect('/admin/mensajes');
    }

    public function cerrarMensaje(array $params = []): void
    {
        $this->requireAdmin();

        $id      = (int)($params['id'] ?? 0);
        $soporte = new SoporteMensajeModel();
        $soporte->cerrar($id);

        SessionManager::flash('success', 'Mensaje cerrado.');
        $this->redirect('/admin/mensajes');
    }

    // =========================================================
    // COMENTARIOS — moderación
    // =========================================================

    public function comentarios(array $params = []): void
    {
        $this->requireAdmin();
        Security::setNoCacheHeaders();

        $m        = new PerfilComentarioModel();
        $page     = max(1, (int)$this->getParam('page', '1'));
        $filtros  = [
            'estado' => $this->getParam('estado', ''),
            'buscar' => $this->getParam('q', ''),
        ];
        $result   = $m->listarAdmin($page, $filtros);

        $this->render('admin/comentarios', [
            'pageTitle'  => 'Moderación de comentarios',
            'items'      => $result['items'],
            'pagination' => $result,
            'filtros'    => $filtros,
            'stats'      => $m->estadisticas(),
        ]);
    }

    public function comentarioOcultar(array $params = []): void
    {
        $this->requireAdmin();

        $id  = (int)($params['id'] ?? 0);
        $com = new PerfilComentarioModel();
        $c   = $com->find($id);

        if ($c && $com->setEstado($id, 'oculto')) {
            $this->notifs->crear((int)$c['id_usuario'], [
                'tipo'    => 'comentario_oculto',
                'titulo'  => 'Tu comentario fue ocultado',
                'mensaje' => 'Un administrador ocultó tu comentario. Contacta a soporte si crees que es un error.',
                'url'     => '/perfil/' . (int)$c['id_perfil'] . '#comentarios',
                'icono'   => 'eye-slash-fill',
                'color'   => 'warning',
            ]);
        }

        SessionManager::flash('success', 'Comentario ocultado.');
        $this->redirect('/admin/comentarios');
    }

    public function comentarioPublicar(array $params = []): void
    {
        $this->requireAdmin();

        $id  = (int)($params['id'] ?? 0);
        $com = new PerfilComentarioModel();
        $c   = $com->find($id);

        if (!$c) {
            SessionManager::flash('error', 'Comentario no encontrado.');
            $this->redirect('/admin/comentarios');
        }

        $eraPendiente = $c['estado'] === 'pendiente';
        $com->setEstado($id, 'publicado');

        // Notificar al autor del comentario: aprobado y publicado
        $this->notifs->crear((int)$c['id_usuario'], [
            'tipo'    => 'comentario_aprobado',
            'titulo'  => '¡Tu comentario fue aprobado!',
            'mensaje' => 'Tu comentario ya es visible en el perfil.',
            'url'     => '/perfil/' . (int)$c['id_perfil'] . '#comentarios',
            'icono'   => 'patch-check-fill',
            'color'   => 'success',
        ]);

        // Si pasó de pendiente a publicado, también notificar al dueño del perfil
        if ($eraPendiente) {
            $perfil = $this->perfiles->find((int)$c['id_perfil']);
            if ($perfil && (int)$perfil['id_usuario'] !== (int)$c['id_usuario']) {
                $this->notifs->crear((int)$perfil['id_usuario'], [
                    'tipo'    => 'comentario_nuevo',
                    'titulo'  => 'Nuevo comentario en tu perfil',
                    'mensaje' => 'Alguien dejó un comentario con '
                               . str_repeat('★', (int)$c['calificacion'])
                               . ' en "' . mb_substr($perfil['nombre'], 0, 60) . '".',
                    'url'     => '/perfil/' . (int)$c['id_perfil'] . '#comentarios',
                    'icono'   => 'chat-square-text-fill',
                    'color'   => 'info',
                ]);
            }
        }

        SessionManager::flash('success', $eraPendiente ? 'Comentario aprobado y publicado.' : 'Comentario restaurado.');
        $this->redirect('/admin/comentarios');
    }

    public function comentarioEliminar(array $params = []): void
    {
        $this->requireAdmin();

        $id  = (int)($params['id'] ?? 0);
        $com = new PerfilComentarioModel();
        $c   = $com->find($id);

        if ($c) {
            $com->eliminar($id);
            $this->notifs->crear((int)$c['id_usuario'], [
                'tipo'    => 'comentario_eliminado',
                'titulo'  => 'Tu comentario fue eliminado',
                'mensaje' => 'Un administrador eliminó tu comentario. Contacta a soporte si crees que es un error.',
                'url'     => '/perfil/' . (int)$c['id_perfil'] . '#comentarios',
                'icono'   => 'trash-fill',
                'color'   => 'danger',
            ]);
        }

        SessionManager::flash('success', 'Comentario eliminado permanentemente.');
        $this->redirect('/admin/comentarios');
    }

    // =========================================================
    // REPORTES — acciones extendidas
    // =========================================================

    /** Rechazar reporte (sin acción contra el denunciado) + motivo opcional. */
    public function rejectReport(array $params = []): void
    {
        $this->requireAdmin();
        $id      = (int)($params['id'] ?? 0);
        $motivo  = Security::sanitizeText($_POST['motivo_rechazo'] ?? '');
        $reporte = $this->reportes->find($id);
        if (!$reporte) {
            SessionManager::flash('error', 'Reporte no encontrado.');
            $this->redirect('/admin/reportes');
        }

        $nota = $motivo !== '' ? ("Rechazado: " . $motivo) : 'Rechazado por el administrador.';
        $this->marcarResolucion($id, 'rechazado', $nota);

        // Notificar al reportero
        if (!empty($reporte['id_usuario'])) {
            $this->notifs->crear((int)$reporte['id_usuario'], [
                'tipo'    => 'reporte_rechazado',
                'titulo'  => 'Tu reporte fue revisado',
                'mensaje' => 'El equipo revisó tu reporte y determinó que no procede. '
                           . ($motivo !== '' ? 'Motivo: ' . mb_substr($motivo, 0, 140) : ''),
                'url'     => '/dashboard',
                'icono'   => 'info-circle-fill',
                'color'   => 'info',
            ]);
        }

        SessionManager::flash('success', 'Reporte rechazado.');
        $this->redirect('/admin/reportes');
    }

    /** Pedir más información al reportero (notificación — no cambia estado de resolucion). */
    public function askInfoReport(array $params = []): void
    {
        $this->requireAdmin();
        $id      = (int)($params['id'] ?? 0);
        $pregunta= Security::sanitizeText($_POST['pregunta'] ?? '');
        $reporte = $this->reportes->find($id);
        if (!$reporte) {
            SessionManager::flash('error', 'Reporte no encontrado.');
            $this->redirect('/admin/reportes');
        }
        if (mb_strlen($pregunta) < 10) {
            SessionManager::flash('error', 'Escribe al menos 10 caracteres en la pregunta.');
            $this->redirect('/admin/reportes');
        }
        if (empty($reporte['id_usuario'])) {
            SessionManager::flash('warning', 'Este reporte es anónimo; no hay a quién pedir información.');
            $this->redirect('/admin/reportes');
        }

        // Pasa a 'revisado' y añade la pregunta como nota
        $this->marcarResolucion($id, 'revisado', "Info solicitada: " . $pregunta);

        $this->notifs->crear((int)$reporte['id_usuario'], [
            'tipo'    => 'reporte_info_pedida',
            'titulo'  => 'Necesitamos más información sobre tu reporte',
            'mensaje' => mb_substr($pregunta, 0, 220),
            'url'     => '/cuenta/reactivar',
            'icono'   => 'question-circle-fill',
            'color'   => 'warning',
        ]);

        SessionManager::flash('success', 'Se envió la solicitud de información al reportero.');
        $this->redirect('/admin/reportes');
    }

    /** Marcar un reporte como "revisado" (en revisión) sin resolverlo todavía. */
    public function markReviewed(array $params = []): void
    {
        $this->requireAdmin();
        $id = (int)($params['id'] ?? 0);
        if (!$this->reportes->find($id)) {
            SessionManager::flash('error', 'Reporte no encontrado.');
            $this->redirect('/admin/reportes');
        }
        $this->marcarResolucion($id, 'revisado');
        SessionManager::flash('success', 'Reporte marcado en revisión.');
        $this->redirect('/admin/reportes');
    }

    /** Guardar/actualizar nota interna del admin sobre un reporte. */
    public function saveNotaReport(array $params = []): void
    {
        $this->requireAdmin();
        $id   = (int)($params['id'] ?? 0);
        $nota = Security::sanitizeText($_POST['nota'] ?? '');

        $this->reportes->update($id, ['nota_admin' => mb_substr($nota, 0, 1000)]);
        SessionManager::flash('success', 'Nota guardada.');
        $this->redirect('/admin/reportes');
    }

    /** Eliminar el perfil denunciado y marcar el reporte como resuelto. */
    public function deletePerfilFromReport(array $params = []): void
    {
        $this->requireAdmin();
        $id      = (int)($params['id'] ?? 0);
        $reporte = $this->reportes->find($id);
        if (!$reporte || empty($reporte['id_perfil'])) {
            SessionManager::flash('error', 'Reporte o perfil no encontrado.');
            $this->redirect('/admin/reportes');
        }

        $perfil = $this->perfiles->find((int)$reporte['id_perfil']);
        if ($perfil) {
            $idUsuario = (int)$perfil['id_usuario'];
            $nombre    = $perfil['nombre'];

            $this->perfilFotos->eliminarPorAnuncio((int)$perfil['id']);
            $this->perfiles->delete((int)$perfil['id']);

            $this->notifs->crear($idUsuario, [
                'tipo'    => 'perfil_eliminado',
                'titulo'  => 'Tu perfil fue eliminado por un reporte',
                'mensaje' => '"' . mb_substr($nombre, 0, 80) . '" fue retirado tras la resolución de un reporte.',
                'url'     => '/mis-perfiles',
                'icono'   => 'trash-fill',
                'color'   => 'danger',
            ]);
        }

        $this->marcarResolucion($id, 'resuelto', 'Perfil eliminado por resolución del reporte.');

        // Avisar al reportero que su reporte funcionó
        if (!empty($reporte['id_usuario'])) {
            $this->notifs->crear((int)$reporte['id_usuario'], [
                'tipo'    => 'reporte_resuelto',
                'titulo'  => '¡Tu reporte llevó a acción!',
                'mensaje' => 'El perfil reportado fue eliminado tras la revisión.',
                'url'     => '/dashboard',
                'icono'   => 'patch-check-fill',
                'color'   => 'success',
            ]);
        }

        SessionManager::flash('success', 'Perfil eliminado y reporte resuelto.');
        $this->redirect('/admin/reportes');
    }

    /** Suspender al dueño del perfil denunciado y marcar reporte como resuelto. */
    public function suspendUserFromReport(array $params = []): void
    {
        $this->requireAdmin();
        $id      = (int)($params['id'] ?? 0);
        $reporte = $this->reportes->find($id);
        if (!$reporte || empty($reporte['id_perfil'])) {
            SessionManager::flash('error', 'Reporte o perfil no encontrado.');
            $this->redirect('/admin/reportes');
        }

        $perfil = $this->perfiles->find((int)$reporte['id_perfil']);
        if (!$perfil) {
            SessionManager::flash('error', 'El perfil ya no existe.');
            $this->redirect('/admin/reportes');
        }

        $idUsuario = (int)$perfil['id_usuario'];
        $this->usuarios->update($idUsuario, [
            'estado_verificacion' => 'suspendido',
            'verificado'          => 0,
            'fecha_actualizacion' => date('Y-m-d H:i:s'),
        ]);

        $this->notifs->crear($idUsuario, [
            'tipo'    => 'cuenta_suspendida',
            'titulo'  => 'Tu cuenta fue suspendida',
            'mensaje' => 'Tu cuenta fue suspendida tras la revisión de un reporte. Puedes solicitar la reactivación contactando a soporte.',
            'url'     => '/cuenta/reactivar',
            'icono'   => 'slash-circle-fill',
            'color'   => 'danger',
        ]);

        $this->marcarResolucion($id, 'resuelto', 'Cuenta del denunciado suspendida.');
        SessionManager::flash('success', 'Cuenta del denunciado suspendida y reporte resuelto.');
        $this->redirect('/admin/reportes');
    }

    // =========================================================
    // VIDEOS DE PERFIL — moderación
    // =========================================================

    public function videoPublicar(array $params = []): void
    {
        $this->requireAdmin();
        $id    = (int)($params['id'] ?? 0);
        $video = $this->perfilVideos->find($id);
        if (!$video) {
            SessionManager::flash('error', 'Video no encontrado.');
            $this->redirect($_SERVER['HTTP_REFERER'] ?? '/admin/perfiles');
        }

        $this->perfilVideos->setEstado($id, 'publicado');

        $perfil = $this->perfiles->find((int)$video['id_perfil']);
        if ($perfil) {
            $this->notifs->crear((int)$perfil['id_usuario'], [
                'tipo'    => 'video_aprobado',
                'titulo'  => 'Un video de tu perfil fue aprobado',
                'mensaje' => 'El video ya es visible en "' . mb_substr($perfil['nombre'], 0, 60) . '".',
                'url'     => '/perfil/' . (int)$perfil['id'],
                'icono'   => 'play-btn-fill',
                'color'   => 'success',
            ]);
        }

        SessionManager::flash('success', 'Video aprobado y publicado.');
        $this->redirect($_SERVER['HTTP_REFERER'] ?? '/admin/perfil/' . (int)$video['id_perfil']);
    }

    public function videoRechazar(array $params = []): void
    {
        $this->requireAdmin();
        $id     = (int)($params['id'] ?? 0);
        $motivo = Security::sanitizeText($_POST['motivo'] ?? '');
        $video  = $this->perfilVideos->find($id);
        if (!$video) {
            SessionManager::flash('error', 'Video no encontrado.');
            $this->redirect($_SERVER['HTTP_REFERER'] ?? '/admin/perfiles');
        }

        $this->perfilVideos->setEstado($id, 'rechazado', $motivo ?: null);

        $perfil = $this->perfiles->find((int)$video['id_perfil']);
        if ($perfil) {
            $this->notifs->crear((int)$perfil['id_usuario'], [
                'tipo'    => 'video_rechazado',
                'titulo'  => 'Un video de tu perfil fue rechazado',
                'mensaje' => ($motivo !== '' ? 'Motivo: ' . mb_substr($motivo, 0, 180) : 'El video no pasó la revisión.') . ' Puedes subir otro editando el perfil.',
                'url'     => '/perfil/' . (int)$perfil['id'] . '/editar',
                'icono'   => 'x-octagon-fill',
                'color'   => 'danger',
            ]);
        }

        SessionManager::flash('success', 'Video rechazado.');
        $this->redirect($_SERVER['HTTP_REFERER'] ?? '/admin/perfil/' . (int)$video['id_perfil']);
    }

    public function videoEliminar(array $params = []): void
    {
        $this->requireAdmin();
        $id    = (int)($params['id'] ?? 0);
        $video = $this->perfilVideos->find($id);
        if (!$video) {
            SessionManager::flash('error', 'Video no encontrado.');
            $this->redirect($_SERVER['HTTP_REFERER'] ?? '/admin/perfiles');
        }

        $this->perfilVideos->eliminar($id, (int)$video['id_perfil']);

        $perfil = $this->perfiles->find((int)$video['id_perfil']);
        if ($perfil) {
            $this->notifs->crear((int)$perfil['id_usuario'], [
                'tipo'    => 'video_eliminado',
                'titulo'  => 'Un video de tu perfil fue eliminado',
                'mensaje' => 'Un administrador eliminó un video del perfil "' . mb_substr($perfil['nombre'], 0, 60) . '".',
                'url'     => '/perfil/' . (int)$perfil['id'] . '/editar',
                'icono'   => 'trash-fill',
                'color'   => 'warning',
            ]);
        }

        SessionManager::flash('success', 'Video eliminado.');
        $this->redirect($_SERVER['HTTP_REFERER'] ?? '/admin/perfil/' . (int)$video['id_perfil']);
    }

    // =========================================================
    // ALMACENAMIENTO — escaneo de uploads + límite configurable
    // =========================================================

    public function almacenamiento(array $params = []): void
    {
        $this->requireAdmin();
        Security::setNoCacheHeaders();

        $scanner = new StorageScannerModel();
        $config  = new ConfiguracionModel();

        $resumen   = $scanner->resumen();
        $total     = $scanner->total();
        $top       = $scanner->topArchivos(20);

        $limitMb   = $config->getInt('storage_limit_mb',    5120);
        $warnPct   = max(1, min(100, $config->getInt('storage_warning_pct', 80)));
        $limitB    = $limitMb * 1048576;
        $usedPct   = $limitB > 0 ? round($total['bytes'] / $limitB * 100, 1) : 0;
        $isWarn    = $usedPct >= $warnPct;
        $isOver    = $total['bytes'] >= $limitB;

        $categoriaFilter = $this->getParam('cat', '');
        $vista   = $this->getParam('vista', 'lista'); // lista | galeria
        $archivos = [];
        if ($categoriaFilter && isset(StorageScannerModel::CATEGORIAS[$categoriaFilter])) {
            $archivos = $scanner->listar($categoriaFilter, 300);
        }

        $this->render('admin/almacenamiento', [
            'pageTitle' => 'Almacenamiento',
            'resumen'   => $resumen,
            'total'     => $total,
            'top'       => $top,
            'limitMb'   => $limitMb,
            'warnPct'   => $warnPct,
            'limitB'    => $limitB,
            'usedPct'   => $usedPct,
            'isWarn'    => $isWarn,
            'isOver'    => $isOver,
            'catFilter' => $categoriaFilter,
            'vista'     => $vista,
            'archivos'  => $archivos,
        ]);
    }

    public function almacenamientoConfig(array $params = []): void
    {
        $this->requireAdmin();

        $limitMb = max(100, (int)($_POST['storage_limit_mb']   ?? 5120));
        $warnPct = max(1, min(100, (int)($_POST['storage_warning_pct'] ?? 80)));

        $config = new ConfiguracionModel();
        $config->set('storage_limit_mb',    (string)$limitMb, 'Límite total de almacenamiento en MB');
        $config->set('storage_warning_pct', (string)$warnPct, 'Porcentaje a partir del cual se alerta (0-100)');

        SessionManager::flash('success', "Configuración guardada: {$limitMb} MB · alerta al {$warnPct}%.");
        $this->redirect('/admin/almacenamiento');
    }

    /** GET /admin/archivo?cat=X&f=Y — sirve archivo del directorio de uploads (solo admin). */
    public function serveArchivo(array $params = []): void
    {
        $this->requireAdmin();

        $cat = $_GET['cat'] ?? '';
        $f   = basename($_GET['f'] ?? ''); // anti path-traversal

        if (!isset(StorageScannerModel::CATEGORIAS[$cat]) || $f === '' || !preg_match('/^[A-Za-z0-9._\-]+$/', $f)) {
            http_response_code(400); exit;
        }

        $dir  = UPLOADS_PATH . StorageScannerModel::CATEGORIAS[$cat]['dir'];
        $path = $dir . '/' . $f;
        if (!is_file($path)) { http_response_code(404); exit; }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $path);
        finfo_close($finfo);

        $allowed = ['image/jpeg','image/png','image/webp','image/gif','video/mp4','video/webm','video/quicktime','application/pdf'];
        if (!in_array($mime, $allowed, true)) { http_response_code(415); exit; }

        $size = filesize($path);

        // Range para videos
        $start = 0; $end = $size - 1;
        if (str_starts_with($mime, 'video/') && !empty($_SERVER['HTTP_RANGE'])
            && preg_match('/bytes=(\d+)-(\d*)/', $_SERVER['HTTP_RANGE'], $m)) {
            $start = (int)$m[1];
            if ($m[2] !== '') $end = min((int)$m[2], $size - 1);
            header('HTTP/1.1 206 Partial Content');
            header("Content-Range: bytes {$start}-{$end}/{$size}");
        }

        header('Content-Type: ' . $mime);
        header('Accept-Ranges: bytes');
        header('Content-Length: ' . ($end - $start + 1));
        header('Cache-Control: private, no-store');
        header('X-Content-Type-Options: nosniff');

        $fp = fopen($path, 'rb');
        fseek($fp, $start);
        $buf = 8192; $rem = $end - $start + 1;
        while (!feof($fp) && $rem > 0 && !connection_aborted()) {
            $read = $rem > $buf ? $buf : $rem;
            echo fread($fp, $read);
            flush();
            $rem -= $read;
        }
        fclose($fp);
        exit;
    }
}
