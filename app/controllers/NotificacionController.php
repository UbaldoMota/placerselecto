<?php
/**
 * NotificacionController.php
 * Endpoints para la campanita: polling, marcar leída/s, historial.
 */

require_once APP_PATH . '/Controller.php';
require_once APP_PATH . '/Security.php';
require_once APP_PATH . '/models/NotificacionModel.php';

class NotificacionController extends Controller
{
    private NotificacionModel $notif;

    public function __construct()
    {
        $this->notif = new NotificacionModel();
    }

    /**
     * GET /api/notificaciones/pendientes
     * Endpoint de polling: devuelve count + últimas + hash (ETag).
     * Si el hash no cambió → 304 Not Modified (sin body).
     */
    public function pendientes(array $params = []): void
    {
        Security::setJsonHeaders();

        if (!SessionManager::has('user_id')) {
            $this->json(['count' => 0, 'items' => [], 'hash' => ''], 200);
        }

        $idUsuario = (int) SessionManager::get('user_id');
        $data      = $this->notif->paraPolling($idUsuario);
        $etag      = 'W/"' . $data['hash'] . '"';

        $clientEtag = $_SERVER['HTTP_IF_NONE_MATCH'] ?? '';
        if ($clientEtag !== '' && $clientEtag === $etag) {
            http_response_code(304);
            header('ETag: ' . $etag);
            exit;
        }

        header('ETag: ' . $etag);
        header('Cache-Control: private, no-store');

        // Formatear fecha a ISO y timeAgo legible
        foreach ($data['items'] as &$it) {
            $it['fecha_iso']   = date('c', strtotime($it['fecha_creacion']));
            $it['tiempo_rel']  = Security::timeAgo($it['fecha_creacion']);
            $it['leida']       = (int)$it['leida'];
        }
        unset($it);

        $this->json($data, 200);
    }

    /**
     * POST /notificacion/{id}/leer
     */
    public function marcarLeida(array $params = []): void
    {
        $this->requireAuth();

        $id   = (int)($params['id'] ?? 0);
        $uid  = (int) SessionManager::get('user_id');
        $ok   = $this->notif->marcarLeida($id, $uid);

        if ($this->isAjax()) {
            $this->json(['ok' => $ok]);
        }

        $this->redirect($_SERVER['HTTP_REFERER'] ?? '/notificaciones');
    }

    /**
     * POST /notificaciones/leer-todas
     */
    public function marcarTodasLeidas(array $params = []): void
    {
        $this->requireAuth();

        $uid = (int) SessionManager::get('user_id');
        $n   = $this->notif->marcarTodasLeidas($uid);

        if ($this->isAjax()) {
            $this->json(['ok' => true, 'marcadas' => $n]);
        }

        SessionManager::flash('success', "Se marcaron {$n} notificación(es) como leídas.");
        $this->redirect($_SERVER['HTTP_REFERER'] ?? '/notificaciones');
    }

    /**
     * GET /notificaciones — página de historial paginado.
     */
    public function index(array $params = []): void
    {
        $this->requireAuth();
        Security::setNoCacheHeaders();

        $uid    = (int) SessionManager::get('user_id');
        $page   = max(1, (int)$this->getParam('page', '1'));
        $result = $this->notif->historial($uid, $page);

        $this->render('notificaciones/index', [
            'pageTitle'   => 'Mis notificaciones',
            'notifs'      => $result['items'],
            'pagination'  => $result,
        ]);
    }

    /**
     * POST /notificacion/{id}/eliminar
     */
    public function eliminar(array $params = []): void
    {
        $this->requireAuth();

        $id  = (int)($params['id'] ?? 0);
        $uid = (int) SessionManager::get('user_id');
        $ok  = $this->notif->eliminar($id, $uid);

        if ($this->isAjax()) {
            $this->json(['ok' => $ok]);
        }

        SessionManager::flash($ok ? 'success' : 'error',
            $ok ? 'Notificación eliminada.' : 'No se pudo eliminar.');
        $this->redirect('/notificaciones');
    }
}
