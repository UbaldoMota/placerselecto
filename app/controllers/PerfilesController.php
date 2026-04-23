<?php
/**
 * PerfilesController.php
 * CRUD de perfiles de anunciantes.
 * Cada usuario puede tener hasta PerfilModel::MAX_POR_USUARIO perfiles (3).
 */

require_once APP_PATH . '/Controller.php';
require_once APP_PATH . '/Security.php';
require_once APP_PATH . '/Upload.php';
require_once APP_PATH . '/Validator.php';
require_once APP_PATH . '/models/PerfilModel.php';
require_once APP_PATH . '/models/PerfilFotoModel.php';
require_once APP_PATH . '/models/PerfilVideoModel.php';
require_once APP_PATH . '/models/PerfilComentarioModel.php';
require_once APP_PATH . '/models/CategoriaModel.php';
require_once APP_PATH . '/models/EstadoModel.php';
require_once APP_PATH . '/models/UsuarioModel.php';
require_once APP_PATH . '/models/NotificacionModel.php';

class PerfilesController extends Controller
{
    private PerfilModel       $perfiles;
    private PerfilFotoModel   $fotos;
    private PerfilVideoModel  $videos;
    private CategoriaModel    $categorias;
    private EstadoModel       $estados;
    private UsuarioModel      $usuarios;

    private const MAX_FOTOS  = 10;
    private const MAX_VIDEOS = 3;

    public function __construct()
    {
        $this->perfiles   = new PerfilModel();
        $this->fotos      = new PerfilFotoModel();
        $this->videos     = new PerfilVideoModel();
        $this->categorias = new CategoriaModel();
        $this->estados    = new EstadoModel();
        $this->usuarios   = new UsuarioModel();
    }

    /**
     * Procesa uploads de videos (input name="videos[]", max N).
     * Valida MIME real, tamaño, y guarda en /uploads/videos/ con nombre único.
     * Retorna ['guardados' => int, 'errores' => array<string>]
     */
    private function procesarVideos(int $idPerfil, string $field, int $maxNuevos): array
    {
        $errores = [];
        $archivos = $_FILES[$field] ?? null;
        if (!$archivos || empty($archivos['name'][0])) {
            return ['guardados' => 0, 'errores' => []];
        }

        $destDir = UPLOADS_PATH . '/videos';
        if (!is_dir($destDir)) mkdir($destDir, 0755, true);

        $guardados = 0;
        foreach ($archivos['name'] as $i => $name) {
            if ($guardados >= $maxNuevos) break;
            if (!empty($archivos['error'][$i]) && $archivos['error'][$i] !== UPLOAD_ERR_OK) continue;

            $tmp   = $archivos['tmp_name'][$i];
            $size  = (int)$archivos['size'][$i];
            $label = basename($archivos['name'][$i]);

            if ($size <= 0 || $size > PerfilVideoModel::MAX_BYTES) {
                $errores[] = "\"{$label}\": supera el límite de 50 MB.";
                continue;
            }

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime  = finfo_file($finfo, $tmp);
            finfo_close($finfo);

            if (!in_array($mime, PerfilVideoModel::ALLOWED_MIME, true)) {
                $errores[] = "\"{$label}\": formato no permitido (MP4, WebM o MOV).";
                continue;
            }

            $ext = match($mime) {
                'video/mp4'        => 'mp4',
                'video/webm'       => 'webm',
                'video/quicktime'  => 'mov',
                default            => 'mp4',
            };
            $filename = 'vid_p' . $idPerfil . '_' . time() . '_' . bin2hex(random_bytes(5)) . '.' . $ext;
            $destPath = $destDir . '/' . $filename;

            if (!move_uploaded_file($tmp, $destPath)) {
                $errores[] = "\"{$label}\": error al guardar el archivo.";
                continue;
            }
            @chmod($destPath, 0644);

            $this->videos->guardar($idPerfil, $filename, $this->videos->contar($idPerfil), $size);
            $guardados++;
        }

        return ['guardados' => $guardados, 'errores' => $errores];
    }

    // ---------------------------------------------------------
    // LISTADO PÚBLICO
    // ---------------------------------------------------------

    public function index(array $params = []): void
    {
        $page    = max(1, (int)$this->getParam('page', '1'));
        $filtros = [
            'buscar'       => Security::sanitizeString($this->getParam('q', '')),
            'id_categoria' => (int)$this->getParam('id_categoria', '0') ?: null,
            'id_estado'    => (int)$this->getParam('id_estado', '0') ?: null,
            'id_municipio' => (int)$this->getParam('id_municipio', '0') ?: null,
        ];

        $result     = $this->perfiles->listarPublicos(array_filter($filtros), $page);
        $categorias = $this->categorias->activas();
        $estados    = $this->estados->activos();

        // Pre-cargar municipios si hay estado seleccionado
        $municipios = [];
        if (!empty($filtros['id_estado'])) {
            require_once APP_PATH . '/models/MunicipioModel.php';
            $municipios = (new MunicipioModel())->porEstado($filtros['id_estado']);
        }

        $this->render('perfiles/index', [
            'pageTitle'  => 'Perfiles',
            'perfiles'   => $result['items'],
            'pagination' => $result,
            'filtros'    => $filtros,
            'categorias' => $categorias,
            'estados'    => $estados,
            'municipios' => $municipios,
        ]);
    }

    // ---------------------------------------------------------
    // DETALLE PÚBLICO
    // ---------------------------------------------------------

    public function show(array $params = []): void
    {
        $id = (int)($params['id'] ?? 0);
        if ($id <= 0) $this->redirect('/perfiles');

        $perfil = $this->perfiles->obtenerPublico($id);
        if (!$perfil) {
            http_response_code(404);
            require VIEWS_PATH . '/partials/404.php';
            exit;
        }

        $user     = $this->currentUser();
        $esAdmin  = ($user['rol'] ?? '') === 'admin';
        $esDuenio = $user && (int)$user['id'] === (int)$perfil['id_usuario'];

        $noVisible = $perfil['estado'] !== 'publicado' || !empty($perfil['oculta']);
        if ($noVisible && !$esDuenio && !$esAdmin) {
            http_response_code(404);
            require VIEWS_PATH . '/partials/404.php';
            exit;
        }

        if (!$esDuenio && !$esAdmin) {
            $this->perfiles->incrementarVistas($id);
        }

        // Perfiles relacionados (misma categoría)
        $db      = Database::getInstance()->getConnection();
        $stmtRel = $db->prepare(
            "SELECT p.id, p.nombre, p.edad, p.edad_publica, p.ciudad, p.imagen_principal, p.imagen_token,
                    p.destacado, p.id_categoria,
                    e.nombre AS estado_nombre, m.nombre AS municipio_nombre
             FROM perfiles p
             LEFT JOIN estados    e ON e.id = p.id_estado
             LEFT JOIN municipios m ON m.id = p.id_municipio
             WHERE p.estado = 'publicado' AND p.oculta = 0
               AND p.id_categoria = ?
               AND p.id != ?
             ORDER BY p.destacado DESC, p.fecha_publicacion DESC
             LIMIT 4"
        );
        $stmtRel->execute([$perfil['id_categoria'], $id]);
        $relacionados = $stmtRel->fetchAll();

        $confiabilidad = $this->usuarios->confiabilidad((int)$perfil['id_usuario']);
        $fotos         = $this->fotos->galeria($id);

        // Migración automática desde imagen_principal legacy
        if (empty($fotos) && !empty($perfil['imagen_principal']) && empty($perfil['imagen_token'])) {
            $nuevoId = $this->fotos->guardar($id, $perfil['imagen_principal'], 0);
            $fotoNueva = $this->fotos->find($nuevoId);
            if ($fotoNueva) {
                $this->perfiles->update($id, ['imagen_token' => $fotoNueva['token']]);
            }
            $fotos = $this->fotos->galeria($id);
        }

        $comModel  = new PerfilComentarioModel();
        $comPromedio = $comModel->promedio($id);
        $comentarios = $comModel->porPerfil($id, 50);
        $miComentario = $user ? $comModel->miComentario($id, (int)$user['id']) : null;

        // Navegación prev/next respecto a los resultados de la búsqueda.
        // Se pasan los filtros vía query string al clickear un perfil del listado.
        $filtrosBusq = [
            'buscar'       => Security::sanitizeString($this->getParam('q', '')),
            'id_categoria' => (int)$this->getParam('id_categoria', '0') ?: null,
            'id_estado'    => (int)$this->getParam('id_estado', '0') ?: null,
            'id_municipio' => (int)$this->getParam('id_municipio', '0') ?: null,
        ];
        $filtrosBusq = array_filter($filtrosBusq);

        $nav = null;
        if (!empty($filtrosBusq) || $this->getParam('nav', '') === '1') {
            // Si viene desde búsqueda o navegación, calcula posición
            $ids    = $this->perfiles->listarIdsPublicos($filtrosBusq);
            $pos    = array_search($id, $ids, true);
            if ($pos !== false) {
                $nav = [
                    'filtros'  => $filtrosBusq,
                    'prev'     => $ids[$pos - 1] ?? null,
                    'next'     => $ids[$pos + 1] ?? null,
                    'posicion' => $pos + 1,
                    'total'    => count($ids),
                ];
            }
        }

        $this->render('perfiles/show', [
            'pageTitle'     => $perfil['nombre'],
            'perfil'        => $perfil,
            'fotos'         => $fotos,
            'videos'        => $this->videos->listar($id),
            'relacionados'  => $relacionados,
            'confiabilidad' => $confiabilidad,
            'esPropio'      => $esDuenio,
            'categorias'    => $this->categorias->activas(),
            'estados'       => $this->estados->activos(),
            'comentarios'   => $comentarios,
            'comPromedio'   => $comPromedio,
            'miComentario'  => $miComentario,
            'nav'           => $nav,
        ]);
    }

    // ---------------------------------------------------------
    // CREAR PERFIL
    // ---------------------------------------------------------

    public function create(array $params = []): void
    {
        $this->requireAuth();
        $user = $this->currentUser();

        // Los comentaristas no pueden crear perfiles
        if (($user['rol'] ?? '') === 'comentarista') {
            SessionManager::flash('error', 'Tu cuenta es de tipo comentarista. No puedes publicar perfiles.');
            $this->redirect('/perfiles');
        }

        // Bloqueo: cuentas rechazadas o suspendidas no pueden crear perfiles
        if (in_array($user['estado_verificacion'], ['rechazado', 'suspendido'], true)) {
            SessionManager::flash('error', 'Tu cuenta está bloqueada. Solicita la reactivación para volver a publicar.');
            $this->redirect('/cuenta/reactivar');
        }

        if ($this->perfiles->contarPorUsuario((int)$user['id']) >= PerfilModel::MAX_POR_USUARIO) {
            SessionManager::flash('error', 'Ya alcanzaste el límite de ' . PerfilModel::MAX_POR_USUARIO . ' perfiles.');
            $this->redirect('/mis-perfiles');
        }

        $oldInput     = SessionManager::get('_old_input', []);
        SessionManager::delete('_old_input');

        // Datos del usuario para pre-rellenar contacto
        $usuarioData  = $this->usuarios->find((int)$user['id']);

        $this->render('perfiles/create', [
            'pageTitle'   => 'Crear perfil',
            'categorias'  => $this->categorias->activas(),
            'estados'     => $this->estados->activos(),
            'oldInput'    => $oldInput,
            'usuarioData' => $usuarioData,
        ]);
    }

    public function store(array $params = []): void
    {
        $this->requireAuth();
        $user = $this->currentUser();

        // Log diagnóstico (ayuda a debug en móvil — se ve en logs/php_errors.log)
        error_log('[PERFIL-STORE] user=' . ($user['id'] ?? '?')
            . ' desc_len=' . mb_strlen($_POST['descripcion'] ?? '')
            . ' nombre="' . mb_substr($_POST['nombre'] ?? '', 0, 40) . '"'
            . ' estado=' . ($_POST['id_estado'] ?? '?')
            . ' mun=' . ($_POST['id_municipio'] ?? '?')
            . ' cat=' . ($_POST['id_categoria'] ?? '?')
            . ' fotos=' . (isset($_FILES['fotos']) ? count($_FILES['fotos']['name'] ?? []) : 0)
            . ' UA=' . mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 100));

        if (($user['rol'] ?? '') === 'comentarista') {
            SessionManager::flash('error', 'Tu cuenta es de tipo comentarista. No puedes publicar perfiles.');
            $this->redirect('/perfiles');
        }

        // Bloqueo: cuentas rechazadas o suspendidas (también en POST por seguridad)
        if (in_array($user['estado_verificacion'], ['rechazado', 'suspendido'], true)) {
            SessionManager::flash('error', 'Tu cuenta está bloqueada. Solicita la reactivación.');
            $this->redirect('/cuenta/reactivar');
        }

        if ($this->perfiles->contarPorUsuario((int)$user['id']) >= PerfilModel::MAX_POR_USUARIO) {
            SessionManager::flash('error', 'Límite de ' . PerfilModel::MAX_POR_USUARIO . ' perfiles alcanzado.');
            $this->redirect('/mis-perfiles');
        }

        $nombre         = Security::sanitizeString($_POST['nombre']         ?? '');
        $descripcion    = Security::sanitizeHtml($_POST['descripcion']      ?? '');
        $edad           = Security::sanitizeInt($_POST['edad']              ?? 0);
        $edadPublica    = isset($_POST['edad_publica']) ? 1 : 0;
        $idEstado       = (int)($_POST['id_estado']    ?? 0);
        $idMunicipio    = (int)($_POST['id_municipio'] ?? 0);
        $idCategoria    = (int)($_POST['id_categoria'] ?? 0);
        $whatsapp       = Security::sanitizePhone($_POST['whatsapp']        ?? '');
        $telegram       = Security::sanitizeString($_POST['telegram']       ?? '');
        $emailContacto  = filter_var($_POST['email_contacto'] ?? '', FILTER_SANITIZE_EMAIL);
        $pideAnticipo   = isset($_POST['pide_anticipo']) ? 1 : 0;
        $zonaLat        = isset($_POST['zona_lat'])  && $_POST['zona_lat']  !== '' ? (float)$_POST['zona_lat']  : null;
        $zonaLng        = isset($_POST['zona_lng'])  && $_POST['zona_lng']  !== '' ? (float)$_POST['zona_lng']  : null;
        $zonaRadio      = isset($_POST['zona_radio']) && (int)$_POST['zona_radio'] > 0 ? (int)$_POST['zona_radio'] : 5;
        $zonaDesc       = Security::sanitizeString($_POST['zona_descripcion'] ?? '');

        // Limpiar telegram: quitar @ inicial si lo escribieron
        if ($telegram) $telegram = ltrim($telegram, '@');
        // Validar email contacto
        if ($emailContacto && !filter_var($emailContacto, FILTER_VALIDATE_EMAIL)) $emailContacto = '';

        // Datos a preservar si hay error (excepto archivos)
        $oldInput = [
            'nombre'           => $nombre,
            'descripcion'      => $descripcion,
            'edad'             => $_POST['edad'] ?? '',
            'edad_publica'     => $edadPublica,
            'id_estado'        => $idEstado,
            'id_municipio'     => $idMunicipio,
            'id_categoria'     => $idCategoria,
            'whatsapp'         => $whatsapp,
            'telegram'         => $telegram,
            'email_contacto'   => $emailContacto,
            'pide_anticipo'    => $pideAnticipo,
            'zona_lat'         => $zonaLat,
            'zona_lng'         => $zonaLng,
            'zona_radio'       => $zonaRadio,
            'zona_descripcion' => $zonaDesc,
        ];

        $ciudad = '';
        if ($idMunicipio > 0) {
            require_once APP_PATH . '/models/MunicipioModel.php';
            $mun    = (new MunicipioModel())->find($idMunicipio);
            $ciudad = $mun['nombre'] ?? '';
        }

        $v = new Validator([
            'nombre'       => $nombre,
            'descripcion'  => strip_tags($descripcion), // valida longitud sin etiquetas HTML
            'id_estado'    => $idEstado,
            'id_municipio' => $idMunicipio,
            'id_categoria' => $idCategoria,
        ]);
        $v->required('nombre',      'Nombre')->minLength('nombre', 2)->maxLength('nombre', 120)->noHtml('nombre', 'Nombre')
          ->required('descripcion', 'Descripción')->minLength('descripcion', 10)->maxLength('descripcion', 3000)
          ->custom($idEstado  <= 0, 'id_estado',    'Debes seleccionar un estado.')
          ->custom($idMunicipio <= 0, 'id_municipio', 'Debes seleccionar un municipio.')
          ->required('id_categoria', 'Categoría')
          ->custom($edad < 18 || $edad > 99, 'edad', 'La edad debe estar entre 18 y 99 años.');

        if ($v->fails()) {
            SessionManager::set('_old_input', $oldInput);
            foreach ($v->allErrors() as $err) SessionManager::flash('error', $err);
            $this->redirect('/perfil/nuevo');
        }

        // Al menos una foto es obligatoria
        $archivos = $this->procesarFotos('fotos', '/perfil/nuevo');
        if (empty($archivos)) {
            SessionManager::set('_old_input', $oldInput);
            SessionManager::flash('error', 'Debes subir al menos una fotografía para crear el perfil.');
            $this->redirect('/perfil/nuevo');
        }

        $idPerfil = $this->perfiles->crear([
            'id_usuario'      => (int)$user['id'],
            'nombre'          => $nombre,
            'descripcion'     => $descripcion,
            'edad'            => $edad,
            'edad_publica'    => $edadPublica,
            'ciudad'          => $ciudad,
            'id_estado'       => $idEstado,
            'id_municipio'    => $idMunicipio,
            'id_categoria'    => $idCategoria,
            'imagen_principal'=> $archivos[0] ?? null,
            'imagen_token'    => null,
            'whatsapp'        => $whatsapp       ?: null,
            'telegram'        => $telegram        ?: null,
            'email_contacto'  => $emailContacto  ?: null,
            'pide_anticipo'   => $pideAnticipo,
            'zona_lat'        => $zonaLat,
            'zona_lng'        => $zonaLng,
            'zona_radio'      => $zonaRadio,
            'zona_descripcion'=> $zonaDesc        ?: null,
        ]);

        $primerToken = null;
        foreach ($archivos as $orden => $filename) {
            $fid = $this->fotos->guardar($idPerfil, $filename, $orden);
            if ($orden === 0) {
                $primerToken = $this->fotos->find($fid)['token'] ?? null;
            }
        }
        if ($primerToken) {
            $this->perfiles->update($idPerfil, ['imagen_token' => $primerToken]);
        }

        // Videos (opcionales, hasta MAX_VIDEOS) — se crean pendientes de moderación
        $resVideos = $this->procesarVideos($idPerfil, 'videos', self::MAX_VIDEOS);
        foreach ($resVideos['errores'] as $err) {
            SessionManager::flash('warning', $err);
        }
        if ($resVideos['guardados'] > 0) {
            (new NotificacionModel())->crearParaAdmins([
                'tipo'    => 'video_pendiente',
                'titulo'  => 'Video(s) pendiente(s) de moderación',
                'mensaje' => $resVideos['guardados'] . ' video(s) subido(s) en el perfil "' . mb_substr($nombre, 0, 60) . '".',
                'url'     => '/admin/perfil/' . $idPerfil,
                'icono'   => 'play-btn-fill',
                'color'   => 'warning',
            ]);
        }

        (new NotificacionModel())->crearParaAdmins([
            'tipo'    => 'perfil_pendiente',
            'titulo'  => 'Nuevo perfil pendiente',
            'mensaje' => e($user['nombre'] ?? '') . ' creó el perfil "' . mb_substr($nombre, 0, 80) . '".',
            'url'     => '/admin/perfil/' . $idPerfil,
            'icono'   => 'person-plus-fill',
            'color'   => 'warning',
        ]);

        // Redirigir al flujo de verificación de video
        $this->redirect("/perfil/{$idPerfil}/verificar");
    }

    // ---------------------------------------------------------
    // VERIFICACIÓN DE FOTOS — Instrucciones (por perfil publicado)
    // ---------------------------------------------------------

    public function showVerificar(array $params = []): void
    {
        $this->requireAuth();
        $id   = (int)($params['id'] ?? 0);
        $user = $this->currentUser();

        $perfil = $this->perfiles->find($id);
        if (!$perfil || !$this->perfiles->perteneceA($id, (int)$user['id'])) {
            SessionManager::flash('error', 'Perfil no encontrado.');
            $this->redirect('/mis-perfiles');
        }

        $fotosGaleria      = $this->fotos->galeria($id);
        $fotosVerificacion = $this->fotos->verificacion($id);

        $this->render('perfiles/verificar-instrucciones', [
            'pageTitle'         => 'Verificar perfil',
            'perfil'            => $perfil,
            'fotosGaleria'      => array_values($fotosGaleria),
            'tieneFotosVer'     => count($fotosVerificacion) > 0,
            'tieneVideoVer'     => !empty($perfil['video_verificacion']),
        ]);
    }

    // ---------------------------------------------------------
    // VERIFICACIÓN DE FOTOS — Formulario de subida
    // ---------------------------------------------------------

    public function showVerificarFotos(array $params = []): void
    {
        $this->requireAuth();
        $id   = (int)($params['id'] ?? 0);
        $user = $this->currentUser();

        $perfil = $this->perfiles->find($id);
        if (!$perfil || !$this->perfiles->perteneceA($id, (int)$user['id'])) {
            SessionManager::flash('error', 'Perfil no encontrado.');
            $this->redirect('/mis-perfiles');
        }

        // Fotos de galería para que el usuario vea cuáles debe replicar
        $fotosGaleria = $this->fotos->galeria($id);

        // Fotos de verificación ya subidas
        $fotosVerificacion = $this->fotos->verificacion($id);

        $this->render('perfiles/verificar-fotos', [
            'pageTitle'         => 'Subir fotos de verificación',
            'perfil'            => $perfil,
            'fotosGaleria'      => $fotosGaleria,
            'fotosVerificacion' => $fotosVerificacion,
        ]);
    }

    // ---------------------------------------------------------
    // VERIFICACIÓN DE FOTOS — Subida POST
    // ---------------------------------------------------------

    public function subirFotosVerificacion(array $params = []): void
    {
        $this->requireAuth();
        $id   = (int)($params['id'] ?? 0);
        $user = $this->currentUser();

        $perfil = $this->perfiles->find($id);
        if (!$perfil || !$this->perfiles->perteneceA($id, (int)$user['id'])) {
            SessionManager::flash('error', 'Perfil no encontrado.');
            $this->redirect('/mis-perfiles');
        }

        // Procesar fotos (mismo helper, mismo subdirectorio 'anuncios')
        $archivos = $this->procesarFotos('fotos', "/perfil/{$id}/verificar/fotos");

        if (empty($archivos)) {
            SessionManager::flash('error', 'Debes subir al menos una foto de verificación.');
            $this->redirect("/perfil/{$id}/verificar/fotos");
        }

        // Guardar con el flag es_verificacion = 1
        $db = Database::getInstance()->getConnection();
        foreach ($archivos as $i => $filename) {
            $token = bin2hex(random_bytes(20));
            $db->prepare(
                "INSERT INTO perfil_fotos (id_perfil, token, nombre_archivo, orden, es_verificacion)
                 VALUES (?, ?, ?, ?, 1)"
            )->execute([$id, $token, $filename, $i]);
        }

        SessionManager::flash('success', 'Paso 1 completado: fotos de verificación enviadas. Ahora graba el video para terminar.');
        $this->redirect("/perfil/{$id}/verificar");
    }

    // ---------------------------------------------------------
    // VERIFICACIÓN DE VIDEO — Vista cámara (por perfil)
    // ---------------------------------------------------------

    public function showVerificarCamara(array $params = []): void
    {
        $this->requireAuth();
        $id   = (int)($params['id'] ?? 0);
        $user = $this->currentUser();

        $perfil = $this->perfiles->find($id);
        if (!$perfil || !$this->perfiles->perteneceA($id, (int)$user['id'])) {
            SessionManager::flash('error', 'Perfil no encontrado.');
            $this->redirect('/mis-perfiles');
        }

        $this->render('perfiles/verificar-camara', [
            'pageTitle' => 'Verificación en video — ' . $perfil['nombre'],
            'perfil'    => $perfil,
        ]);
    }

    // ---------------------------------------------------------
    // VERIFICACIÓN DE VIDEO — Subida POST (por perfil)
    // ---------------------------------------------------------

    public function subirVideoVerificacion(array $params = []): void
    {
        $this->requireAuth();
        $id   = (int)($params['id'] ?? 0);
        $user = $this->currentUser();

        // Sólo acepta XHR
        if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') !== 'XMLHttpRequest') {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Petición no válida.']);
            exit;
        }

        $perfil = $this->perfiles->find($id);
        if (!$perfil || !$this->perfiles->perteneceA($id, (int)$user['id'])) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'error' => 'Perfil no encontrado.']);
            exit;
        }

        $file = $_FILES['video'] ?? null;
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['ok' => false, 'error' => 'No se recibió el video.']);
            exit;
        }

        // Máximo 60 MB
        if ($file['size'] > 60 * 1024 * 1024) {
            echo json_encode(['ok' => false, 'error' => 'El video supera 60 MB.']);
            exit;
        }

        $ext = in_array(strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)), ['mp4', 'webm'])
               ? strtolower(pathinfo($file['name'], PATHINFO_EXTENSION))
               : 'webm';

        $filename = 'perfil_' . $id . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $destDir  = UPLOADS_PATH . '/verificaciones/perfiles/';
        $destPath = $destDir . $filename;

        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            echo json_encode(['ok' => false, 'error' => 'Error al guardar el video.']);
            exit;
        }

        // Borrar video anterior si existe
        if (!empty($perfil['video_verificacion'])) {
            $prevPath = $destDir . basename($perfil['video_verificacion']);
            if (file_exists($prevPath)) @unlink($prevPath);
        }

        // Guardar en DB
        $db = Database::getInstance()->getConnection();
        $db->prepare(
            "UPDATE perfiles SET video_verificacion = ?, video_verificacion_at = NOW() WHERE id = ?"
        )->execute([$filename, $id]);

        echo json_encode(['ok' => true]);
        exit;
    }

    // ---------------------------------------------------------
    // EDITAR PERFIL
    // ---------------------------------------------------------

    public function edit(array $params = []): void
    {
        $this->requireAuth();
        $id   = (int)($params['id'] ?? 0);
        $user = $this->currentUser();

        $perfil = $this->perfiles->find($id);
        if (!$perfil || !$this->perfiles->perteneceA($id, (int)$user['id'])) {
            SessionManager::flash('error', 'Perfil no encontrado.');
            $this->redirect('/mis-perfiles');
        }

        $municipios = [];
        if (!empty($perfil['id_estado'])) {
            require_once APP_PATH . '/models/MunicipioModel.php';
            $municipios = (new MunicipioModel())->porEstado((int)$perfil['id_estado']);
        }

        $fotosExistentes = $this->fotos->galeria($id);

        // Migración automática desde imagen_principal legacy
        if (empty($fotosExistentes) && !empty($perfil['imagen_principal']) && empty($perfil['imagen_token'])) {
            $nuevoId   = $this->fotos->guardar($id, $perfil['imagen_principal'], 0);
            $fotoNueva = $this->fotos->find($nuevoId);
            if ($fotoNueva) {
                $this->perfiles->update($id, ['imagen_token' => $fotoNueva['token']]);
                $perfil['imagen_token'] = $fotoNueva['token'];
            }
            $fotosExistentes = $this->fotos->galeria($id);
        }

        $this->render('perfiles/edit', [
            'pageTitle'        => 'Editar perfil',
            'perfil'           => $perfil,
            'categorias'       => $this->categorias->activas(),
            'estados'          => $this->estados->activos(),
            'municipios'       => $municipios,
            'fotosExistentes'  => $fotosExistentes,
            'videosExistentes' => $this->videos->listar($id),
            'maxFotos'         => self::MAX_FOTOS,
            'maxVideos'        => self::MAX_VIDEOS,
        ]);
    }

    public function update(array $params = []): void
    {
        $this->requireAuth();
        $id   = (int)($params['id'] ?? 0);
        $user = $this->currentUser();

        $perfil = $this->perfiles->find($id);
        if (!$perfil || !$this->perfiles->perteneceA($id, (int)$user['id'])) {
            SessionManager::flash('error', 'Perfil no encontrado.');
            $this->redirect('/mis-perfiles');
        }

        $nombre        = Security::sanitizeString($_POST['nombre']         ?? '');
        $descripcion   = Security::sanitizeHtml($_POST['descripcion']      ?? '');
        $edad          = Security::sanitizeInt($_POST['edad']              ?? 0);
        $edadPublica   = isset($_POST['edad_publica']) ? 1 : 0;
        $idEstado      = (int)($_POST['id_estado']    ?? 0);
        $idMunicipio   = (int)($_POST['id_municipio'] ?? 0);
        $idCategoria   = (int)($_POST['id_categoria'] ?? 0);
        $whatsapp      = Security::sanitizePhone($_POST['whatsapp']        ?? '');
        $telegram      = Security::sanitizeString($_POST['telegram']       ?? '');
        $emailContacto = filter_var($_POST['email_contacto'] ?? '', FILTER_SANITIZE_EMAIL);
        $pideAnticipo  = isset($_POST['pide_anticipo']) ? 1 : 0;
        $zonaLat       = isset($_POST['zona_lat'])  && $_POST['zona_lat']  !== '' ? (float)$_POST['zona_lat']  : null;
        $zonaLng       = isset($_POST['zona_lng'])  && $_POST['zona_lng']  !== '' ? (float)$_POST['zona_lng']  : null;
        $zonaRadio     = isset($_POST['zona_radio']) && (int)$_POST['zona_radio'] > 0 ? (int)$_POST['zona_radio'] : 5;
        $zonaDesc      = Security::sanitizeString($_POST['zona_descripcion'] ?? '');
        if ($telegram) $telegram = ltrim($telegram, '@');
        if ($emailContacto && !filter_var($emailContacto, FILTER_VALIDATE_EMAIL)) $emailContacto = '';

        $ciudad = '';
        if ($idMunicipio > 0) {
            require_once APP_PATH . '/models/MunicipioModel.php';
            $mun    = (new MunicipioModel())->find($idMunicipio);
            $ciudad = $mun['nombre'] ?? '';
        }

        $v = new Validator([
            'nombre'       => $nombre,
            'descripcion'  => strip_tags($descripcion), // valida longitud sin etiquetas HTML
            'id_estado'    => $idEstado,
            'id_municipio' => $idMunicipio,
            'id_categoria' => $idCategoria,
        ]);
        $v->required('nombre', 'Nombre')->minLength('nombre', 2)->maxLength('nombre', 120)->noHtml('nombre', 'Nombre')
          ->required('descripcion', 'Descripción')->minLength('descripcion', 10)->maxLength('descripcion', 3000)
          ->custom($idEstado <= 0,    'id_estado',    'Debes seleccionar un estado.')
          ->custom($idMunicipio <= 0, 'id_municipio', 'Debes seleccionar un municipio.')
          ->required('id_categoria', 'Categoría')
          ->custom($edad < 18 || $edad > 99, 'edad', 'La edad debe estar entre 18 y 99 años.');

        if ($v->fails()) {
            foreach ($v->allErrors() as $err) SessionManager::flash('error', $err);
            $this->redirect("/perfil/{$id}/editar");
        }

        // Eliminar fotos marcadas
        $eliminarIds = array_filter(array_map('intval', (array)($_POST['eliminar_foto'] ?? [])));
        foreach ($eliminarIds as $fotoId) {
            $this->fotos->eliminar($fotoId, $id);
        }

        // Agregar fotos nuevas
        $totalActual = $this->fotos->contar($id);
        $disponibles = max(0, self::MAX_FOTOS - $totalActual);
        if ($disponibles > 0 && !empty($_FILES['fotos']['name'][0])) {
            $nuevos = $this->procesarFotos('fotos', "/perfil/{$id}/editar", $disponibles);
            foreach ($nuevos as $orden => $filename) {
                $this->fotos->guardar($id, $filename, $totalActual + $orden);
            }
        }

        // Videos: eliminar marcados + subir nuevos (máx MAX_VIDEOS en total)
        $eliminarVideoIds = array_filter(array_map('intval', (array)($_POST['eliminar_video'] ?? [])));
        foreach ($eliminarVideoIds as $vidId) {
            $this->videos->eliminar($vidId, $id);
        }
        $videosActuales = $this->videos->contar($id);
        $dispVideos     = max(0, self::MAX_VIDEOS - $videosActuales);
        if ($dispVideos > 0) {
            $resVideos = $this->procesarVideos($id, 'videos', $dispVideos);
            foreach ($resVideos['errores'] as $err) {
                SessionManager::flash('warning', $err);
            }
            if ($resVideos['guardados'] > 0) {
                (new NotificacionModel())->crearParaAdmins([
                    'tipo'    => 'video_pendiente',
                    'titulo'  => 'Video(s) pendiente(s) de moderación',
                    'mensaje' => $resVideos['guardados'] . ' video(s) nuevo(s) en "' . mb_substr($perfil['nombre'] ?? '', 0, 60) . '".',
                    'url'     => '/admin/perfil/' . $id,
                    'icono'   => 'play-btn-fill',
                    'color'   => 'warning',
                ]);
            }
        }

        $fotoPrincipalId = Security::sanitizeInt($_POST['foto_principal_id'] ?? 0) ?: null;
        $primerToken     = $this->fotos->reordenar($id, $fotoPrincipalId);
        $primerFoto      = $this->fotos->galeria($id)[0] ?? null;

        $this->perfiles->editar($id, [
            'nombre'          => $nombre,
            'descripcion'     => $descripcion,
            'edad'            => $edad,
            'edad_publica'    => $edadPublica,
            'ciudad'          => $ciudad,
            'id_estado'       => $idEstado,
            'id_municipio'    => $idMunicipio,
            'id_categoria'    => $idCategoria,
            'whatsapp'        => $whatsapp       ?: null,
            'telegram'        => $telegram        ?: null,
            'email_contacto'  => $emailContacto  ?: null,
            'pide_anticipo'   => $pideAnticipo,
            'zona_lat'        => $zonaLat,
            'zona_lng'        => $zonaLng,
            'zona_radio'      => $zonaRadio,
            'zona_descripcion'=> $zonaDesc        ?: null,
            'imagen_principal'=> $primerFoto['nombre_archivo'] ?? $perfil['imagen_principal'],
            'imagen_token'    => $primerToken,
        ]);

        SessionManager::flash('success', 'Perfil actualizado. Quedará en revisión nuevamente.');
        $this->redirect('/mis-perfiles');
    }

    // ---------------------------------------------------------
    // ELIMINAR PERFIL
    // ---------------------------------------------------------

    // ---------------------------------------------------------
    // WHATSAPP — Redirect con tracking de clic
    // ---------------------------------------------------------

    public function whatsappRedirect(array $params = []): void
    {
        $id     = (int)($params['id'] ?? 0);
        $perfil = $this->perfiles->obtenerPublico($id);

        if (!$perfil || $perfil['estado'] !== 'publicado' || empty($perfil['whatsapp'])) {
            $this->redirect('/perfiles');
        }

        // No contar clics del propio dueño
        $user     = $this->currentUser();
        $esDuenio = $user && (int)$user['id'] === (int)$perfil['id_usuario'];
        if (!$esDuenio) {
            $this->perfiles->registrarClickWhatsapp($id);
        }

        $waUrl = 'https://wa.me/' . preg_replace('/\D/', '', $perfil['whatsapp']);
        header('Location: ' . $waUrl);
        exit;
    }

    public function delete(array $params = []): void
    {
        $this->requireAuth();
        $id   = (int)($params['id'] ?? 0);
        $user = $this->currentUser();

        $esAdmin = ($user['rol'] ?? '') === 'admin';
        $perfil  = $this->perfiles->find($id);

        if (!$perfil || (!$esAdmin && !$this->perfiles->perteneceA($id, (int)$user['id']))) {
            SessionManager::flash('error', 'Perfil no encontrado.');
            $this->redirect('/mis-perfiles');
        }

        $this->fotos->eliminarPorAnuncio($id);
        $this->perfiles->delete($id);

        SessionManager::flash('success', 'Perfil eliminado correctamente.');
        $this->redirect($esAdmin ? '/admin/perfiles' : '/mis-perfiles');
    }

    // ---------------------------------------------------------
    // BUSCAR (redirect a index con filtros)
    // ---------------------------------------------------------

    public function search(array $params = []): void
    {
        $qs = http_build_query(array_filter([
            'q'            => Security::sanitizeString($_GET['q']            ?? ''),
            'id_categoria' => (int)($_GET['id_categoria'] ?? 0) ?: '',
            'id_estado'    => (int)($_GET['id_estado']    ?? 0) ?: '',
            'id_municipio' => (int)($_GET['id_municipio'] ?? 0) ?: '',
        ]));
        $this->redirect('/perfiles' . ($qs ? '?' . $qs : ''));
    }

    // ---------------------------------------------------------
    // REPORTAR PERFIL
    // ---------------------------------------------------------

    public function report(array $params = []): void
    {
        $id     = (int)($params['id'] ?? 0);
        $motivo = Security::sanitizeString($_POST['motivo']        ?? '');
        $desc   = Security::sanitizeText($_POST['descripcion']     ?? '');
        $urlRef = Security::sanitizeString($_POST['url_referencia'] ?? '');

        if ($id <= 0 || empty($motivo)) {
            SessionManager::flash('error', 'Selecciona un motivo para el reporte.');
            $this->redirect("/perfil/{$id}");
        }

        if (mb_strlen($desc) < 10) {
            SessionManager::flash('error', 'Describe el motivo del reporte en al menos 10 caracteres.');
            $this->redirect("/perfil/{$id}");
        }

        $perfil = $this->perfiles->find($id);
        if (!$perfil) {
            $this->redirect('/perfiles');
        }

        // Validar URL si aplica al motivo "fotos_de_internet"
        if ($motivo === 'fotos_de_internet') {
            if (empty($urlRef) || !Security::isSafeUrl($urlRef) || !filter_var($urlRef, FILTER_VALIDATE_URL)) {
                SessionManager::flash('error', 'Incluye la URL donde se encuentran las fotos originales (empieza con http:// o https://).');
                $this->redirect("/perfil/{$id}");
            }
            $urlRef = mb_substr($urlRef, 0, 500);
        } else {
            $urlRef = null;
        }

        $user = $this->currentUser();
        $db   = Database::getInstance()->getConnection();

        // Detectar si existe la columna url_referencia (migración pendiente en algunos entornos)
        $tieneUrlRef = $db->query(
            "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
              WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME   = 'reportes'
                AND COLUMN_NAME  = 'url_referencia'"
        )->fetchColumn() > 0;

        if ($tieneUrlRef) {
            $stmt = $db->prepare(
                "INSERT INTO reportes (id_anuncio, id_perfil, id_usuario, motivo, descripcion, url_referencia, ip_reporte)
                 VALUES (NULL, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([$id, (int)$user['id'], $motivo, $desc, $urlRef, Security::getClientIp()]);
        } else {
            $stmt = $db->prepare(
                "INSERT INTO reportes (id_anuncio, id_perfil, id_usuario, motivo, descripcion, ip_reporte)
                 VALUES (NULL, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([$id, (int)$user['id'], $motivo, $desc, Security::getClientIp()]);
        }

        (new NotificacionModel())->crearParaAdmins([
            'tipo'    => 'reporte_nuevo',
            'titulo'  => 'Nuevo reporte de perfil',
            'mensaje' => 'Motivo: ' . mb_substr($motivo, 0, 80) . ' — perfil "' . mb_substr($perfil['nombre'], 0, 60) . '".',
            'url'     => '/admin/reportes',
            'icono'   => 'flag-fill',
            'color'   => 'danger',
        ]);

        SessionManager::flash('success', 'Reporte enviado. Gracias por ayudarnos a mantener la comunidad segura.');
        $this->redirect("/perfil/{$id}");
    }

    // ---------------------------------------------------------
    // HELPER PRIVADO: procesar fotos subidas
    // ---------------------------------------------------------

    private function procesarFotos(string $fieldName, string $redirectOn, int $limit = self::MAX_FOTOS): array
    {
        $archivos = $_FILES[$fieldName] ?? null;
        if (!$archivos || empty($archivos['name'][0])) return [];

        $lista = [];
        foreach ($archivos['name'] as $i => $name) {
            if ($archivos['error'][$i] === UPLOAD_ERR_NO_FILE) continue;
            $lista[] = [
                'name'     => $name,
                'type'     => $archivos['type'][$i],
                'tmp_name' => $archivos['tmp_name'][$i],
                'error'    => $archivos['error'][$i],
                'size'     => $archivos['size'][$i],
            ];
        }

        $lista    = array_slice($lista, 0, $limit);
        $guardados = [];
        $uploader  = new Upload();

        foreach ($lista as $file) {
            if (!$uploader->saveImage($file, 'anuncios')) {
                foreach ($uploader->getErrors() as $err) SessionManager::flash('error', $err);
                $this->redirect($redirectOn);
            }
            $guardados[] = $uploader->getSavedFilename();
        }

        return $guardados;
    }
}
