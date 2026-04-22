<?php
/**
 * ComentarioController.php
 * Crear/editar/eliminar/reportar comentarios de perfiles.
 * Requiere login (cualquier rol puede comentar).
 */

require_once APP_PATH . '/Controller.php';
require_once APP_PATH . '/Security.php';
require_once APP_PATH . '/models/PerfilModel.php';
require_once APP_PATH . '/models/PerfilComentarioModel.php';
require_once APP_PATH . '/models/NotificacionModel.php';

class ComentarioController extends Controller
{
    private PerfilModel            $perfiles;
    private PerfilComentarioModel  $comentarios;

    public function __construct()
    {
        $this->perfiles    = new PerfilModel();
        $this->comentarios = new PerfilComentarioModel();
    }

    /** POST /perfil/{id}/comentar */
    public function store(array $params = []): void
    {
        $this->requireAuth();

        $idPerfil = (int)($params['id'] ?? 0);
        $user     = $this->currentUser();
        $perfil   = $this->perfiles->find($idPerfil);

        if (!$perfil || $perfil['estado'] !== 'publicado') {
            SessionManager::flash('error', 'Perfil no disponible.');
            $this->redirect('/perfiles');
        }

        if ((int)$perfil['id_usuario'] === (int)$user['id']) {
            SessionManager::flash('error', 'No puedes comentar tu propio perfil.');
            $this->redirect("/perfil/{$idPerfil}");
        }

        // Rate limit: máx 10 comentarios/hora
        if (!Security::checkRateLimit('comentar_' . $user['id'], 10, 3600)) {
            SessionManager::flash('error', 'Demasiados comentarios en poco tiempo. Espera un momento.');
            $this->redirect("/perfil/{$idPerfil}");
        }

        $calif = max(1, min(5, (int)($_POST['calificacion'] ?? 0)));
        $texto = Security::sanitizeText($_POST['comentario'] ?? '');

        if ($calif < 1 || mb_strlen($texto) < 10) {
            SessionManager::flash('error', 'Escribe un comentario de al menos 10 caracteres y una calificación válida.');
            $this->redirect("/perfil/{$idPerfil}");
        }

        // Validar cooldown: 1 comentario por perfil / semana tras aprobación o eliminación
        $check = $this->comentarios->puedeComentar($idPerfil, (int)$user['id']);
        if (!$check['puede']) {
            SessionManager::flash('error', $check['motivo']);
            $this->redirect("/perfil/{$idPerfil}#comentarios");
        }

        $this->comentarios->guardar(
            $idPerfil,
            (int)$user['id'],
            $calif,
            $texto,
            Security::getClientIp()
        );

        (new NotificacionModel())->crearParaAdmins([
            'tipo'    => 'comentario_pendiente',
            'titulo'  => 'Nuevo comentario pendiente',
            'mensaje' => ($user['nombre'] ?? 'Alguien') . ' dejó un comentario de '
                       . str_repeat('★', $calif) . ' en "' . mb_substr($perfil['nombre'], 0, 60) . '".',
            'url'     => '/admin/comentarios?estado=pendiente',
            'icono'   => 'chat-square-text-fill',
            'color'   => 'warning',
        ]);

        SessionManager::flash('success', '¡Gracias! Tu comentario será revisado por el equipo antes de publicarse.');
        $this->redirect("/perfil/{$idPerfil}#comentarios");
    }

    /** POST /comentario/{id}/eliminar — el dueño del comentario lo borra */
    public function delete(array $params = []): void
    {
        $this->requireAuth();
        $id   = (int)($params['id'] ?? 0);
        $user = $this->currentUser();
        $ok   = $this->comentarios->eliminarPropio($id, (int)$user['id']);

        SessionManager::flash($ok ? 'success' : 'error',
            $ok ? 'Comentario eliminado.' : 'No se pudo eliminar.');
        $this->redirect($_SERVER['HTTP_REFERER'] ?? '/perfiles');
    }

    /** POST /comentario/{id}/reportar — cualquier usuario logueado puede reportar */
    public function report(array $params = []): void
    {
        $this->requireAuth();
        $id = (int)($params['id'] ?? 0);
        $c  = $this->comentarios->find($id);
        if (!$c) {
            SessionManager::flash('error', 'Comentario no encontrado.');
            $this->redirect($_SERVER['HTTP_REFERER'] ?? '/perfiles');
        }

        if ($this->comentarios->reportar($id)) {
            // Notificar admins
            (new NotificacionModel())->crearParaAdmins([
                'tipo'    => 'comentario_reportado',
                'titulo'  => 'Comentario reportado',
                'mensaje' => 'Se reportó un comentario en el perfil #' . (int)$c['id_perfil'] . '. Revisa en moderación.',
                'url'     => '/admin/comentarios?estado=reportado',
                'icono'   => 'flag-fill',
                'color'   => 'warning',
            ]);
            SessionManager::flash('success', 'Reporte enviado. El equipo revisará el comentario.');
        } else {
            SessionManager::flash('info', 'Este comentario ya estaba reportado.');
        }

        $this->redirect($_SERVER['HTTP_REFERER'] ?? '/perfiles');
    }
}
