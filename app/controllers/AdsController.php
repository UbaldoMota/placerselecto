<?php
/**
 * AdsController.php
 * Gestiona el CRUD de anuncios para usuarios autenticados,
 * el listado público y la vista de detalle.
 */

require_once APP_PATH . '/Controller.php';
require_once APP_PATH . '/Security.php';
require_once APP_PATH . '/Validator.php';
require_once APP_PATH . '/Upload.php';
require_once APP_PATH . '/models/AnuncioModel.php';
require_once APP_PATH . '/models/CategoriaModel.php';
require_once APP_PATH . '/models/CiudadModel.php';
require_once APP_PATH . '/models/EstadoModel.php';
require_once APP_PATH . '/models/FotoModel.php';
require_once APP_PATH . '/models/ReporteModel.php';
require_once APP_PATH . '/models/UsuarioModel.php';

class AdsController extends Controller
{
    private AnuncioModel   $anuncios;
    private CategoriaModel $categorias;
    private CiudadModel    $ciudades;
    private EstadoModel    $estados;
    private FotoModel      $fotos;
    private ReporteModel   $reportes;
    private UsuarioModel   $usuarios;

    private const MAX_FOTOS = 10;

    public function __construct()
    {
        $this->anuncios   = new AnuncioModel();
        $this->categorias = new CategoriaModel();
        $this->ciudades   = new CiudadModel();
        $this->estados    = new EstadoModel();
        $this->fotos      = new FotoModel();
        $this->reportes   = new ReporteModel();
        $this->usuarios   = new UsuarioModel();
    }

    // ---------------------------------------------------------
    // LISTADO PÚBLICO
    // ---------------------------------------------------------

    public function index(array $params = []): void
    {
        $page      = max(1, (int) $this->getParam('page', '1'));
        $ciudad    = $this->getParam('ciudad', '');
        $categoria = (int) $this->getParam('categoria', '0');
        $buscar    = $this->getParam('q', '');

        $result     = $this->anuncios->listarPublicos($page, $ciudad, $categoria, $buscar);
        $categorias = $this->categorias->conConteoAnuncios();

        // Pre-selección de estado/municipios cuando hay filtro de ciudad activo
        $filtroEstadoId  = 0;
        $filtroMunicipios = [];
        if ($ciudad !== '') {
            require_once APP_PATH . '/models/MunicipioModel.php';
            $munModel       = new MunicipioModel();
            $filtroEstadoId = $munModel->estadoPorNombre($ciudad) ?? 0;
            if ($filtroEstadoId > 0) {
                $filtroMunicipios = $munModel->porEstado($filtroEstadoId);
            }
        }

        $this->render('ads/index', [
            'pageTitle'        => 'Anuncios',
            'anuncios'         => $result['items'],
            'pagination'       => $result,
            'categorias'       => $categorias,
            'estados'          => $this->estados->activos(),
            'filtroEstadoId'   => $filtroEstadoId,
            'filtroMunicipios' => $filtroMunicipios,
            'filtros'          => compact('ciudad', 'categoria', 'buscar'),
        ]);
    }

    // ---------------------------------------------------------
    // VISTA DE DETALLE
    // ---------------------------------------------------------

    public function show(array $params = []): void
    {
        $id = (int) ($params['id'] ?? 0);
        if ($id <= 0) {
            $this->redirect('/anuncios');
        }

        $anuncio = $this->anuncios->obtenerPublico($id);
        if (!$anuncio) {
            http_response_code(404);
            require VIEWS_PATH . '/partials/404.php';
            exit;
        }

        // Incrementar contador de vistas (solo si no es el dueño)
        $user = $this->currentUser();
        if (!$user || (int) $user['id'] !== (int) $anuncio['id_usuario']) {
            $this->anuncios->incrementarVistas($id);
        }

        // Anuncios relacionados (misma categoría, excluyendo este)
        $db = Database::getInstance()->getConnection();
        $stmtRel = $db->prepare(
            "SELECT a.id, a.titulo, a.ciudad, a.imagen_principal, a.imagen_token,
                    a.destacado, a.id_categoria, c.nombre AS categoria_nombre
             FROM anuncios a
             LEFT JOIN categorias c ON c.id = a.id_categoria
             WHERE a.estado = 'publicado'
               AND a.id_categoria = ?
               AND a.id != ?
             ORDER BY a.destacado DESC, a.fecha_publicacion DESC
             LIMIT 4"
        );
        $stmtRel->execute([$anuncio['id_categoria'], $id]);
        $relacionados = $stmtRel->fetchAll();

        $confiabilidad  = $this->usuarios->confiabilidad((int) $anuncio['id_usuario']);
        $fotosPrincipal = $this->fotos->porAnuncio($id);

        $this->render('ads/show', [
            'pageTitle'      => $anuncio['titulo'],
            'anuncio'        => $anuncio,
            'relacionados'   => $relacionados,
            'confiabilidad'  => $confiabilidad,
            'fotosPrincipal' => $fotosPrincipal,
        ]);
    }

    // ---------------------------------------------------------
    // BÚSQUEDA (redirige al listado con filtros)
    // ---------------------------------------------------------

    public function search(array $params = []): void
    {
        $q = $this->getParam('q', '');
        $this->redirect('/anuncios?q=' . urlencode($q));
    }

    // ---------------------------------------------------------
    // CREAR ANUNCIO
    // ---------------------------------------------------------

    public function create(array $params = []): void
    {
        $this->requireAuth();

        $user = $this->currentUser();

        // Usuarios rechazados no pueden crear
        if ($user['estado_verificacion'] === 'rechazado') {
            SessionManager::flash('error', 'Tu cuenta fue rechazada. No puedes publicar anuncios.');
            $this->redirect('/dashboard');
        }

        $categorias = $this->categorias->activas();
        $estados    = $this->estados->activos();

        $this->render('ads/create', [
            'pageTitle'  => 'Publicar anuncio',
            'categorias' => $categorias,
            'estados'    => $estados,
        ]);
    }

    public function store(array $params = []): void
    {
        $this->requireAuth();

        $user = $this->currentUser();

        if ($user['estado_verificacion'] === 'rechazado') {
            SessionManager::flash('error', 'Tu cuenta fue rechazada.');
            $this->redirect('/dashboard');
        }

        // Sanitizar
        $titulo      = Security::sanitizeString($_POST['titulo']      ?? '');
        $descripcion = Security::sanitizeText($_POST['descripcion']   ?? '');
        $idEstado    = (int) ($_POST['id_estado']    ?? 0);
        $idMunicipio = (int) ($_POST['id_municipio'] ?? 0);
        $idCategoria = (int) ($_POST['id_categoria'] ?? 0);
        $whatsapp    = Security::sanitizePhone($_POST['whatsapp']      ?? '');

        // Obtener nombre del municipio como valor de "ciudad" (compatibilidad)
        $ciudad = '';
        if ($idMunicipio > 0) {
            require_once APP_PATH . '/models/MunicipioModel.php';
            $munModel = new MunicipioModel();
            $mun = $munModel->find($idMunicipio);
            $ciudad = $mun['nombre'] ?? '';
        }

        // Validar
        $categoriasValidas = array_column($this->categorias->activas(), 'id');
        $estadosValidos    = array_column($this->estados->activos(), 'id');

        $v = new Validator([
            'titulo'       => $titulo,
            'descripcion'  => $descripcion,
            'id_estado'    => $idEstado,
            'id_municipio' => $idMunicipio,
            'id_categoria' => $idCategoria,
        ]);

        $v->required('titulo', 'Título')
          ->minLength('titulo', 5, 'Título')
          ->maxLength('titulo', 120, 'Título')
          ->noHtml('titulo', 'Título')
          ->required('descripcion', 'Descripción')
          ->minLength('descripcion', 20, 'Descripción')
          ->maxLength('descripcion', 3000, 'Descripción')
          ->custom($idEstado <= 0, 'id_estado', 'Debes seleccionar un estado.')
          ->custom($idEstado > 0 && !in_array($idEstado, $estadosValidos, true), 'id_estado', 'Estado no válido.')
          ->custom($idMunicipio <= 0, 'id_municipio', 'Debes seleccionar un municipio.')
          ->required('id_categoria', 'Categoría')
          ->custom(!in_array($idCategoria, $categoriasValidas, true), 'id_categoria', 'Categoría no válida.');

        if ($v->fails()) {
            foreach ($v->allErrors() as $err) {
                SessionManager::flash('error', $err);
            }
            $this->redirect('/anuncio/crear');
        }

        // Procesar fotos múltiples (hasta MAX_FOTOS)
        $archivosSubidos = $this->procesarFotos('fotos', '/anuncio/crear');

        // Crear anuncio
        $idAnuncio = $this->anuncios->crear([
            'id_usuario'      => (int) $user['id'],
            'titulo'          => $titulo,
            'descripcion'     => $descripcion,
            'ciudad'          => $ciudad,
            'id_estado'       => $idEstado,
            'id_municipio'    => $idMunicipio,
            'id_categoria'    => $idCategoria,
            'imagen_principal' => $archivosSubidos[0] ?? null,
            'imagen_token'    => null, // se actualiza tras guardar fotos
            'whatsapp'        => $whatsapp ?: null,
        ]);

        // Guardar fotos en anuncio_fotos y actualizar imagen_token
        $primerToken = null;
        foreach ($archivosSubidos as $orden => $filename) {
            $id = $this->fotos->guardar($idAnuncio, $filename, $orden);
            if ($orden === 0) {
                $primerToken = $this->fotos->find($id)['token'] ?? null;
            }
        }
        if ($primerToken) {
            $this->anuncios->update($idAnuncio, ['imagen_token' => $primerToken]);
        }

        // Mensaje según estado de verificación
        if ($user['verificado']) {
            $mensaje = '¡Anuncio enviado! Quedará publicado tras revisión del equipo.';
        } else {
            $mensaje = '¡Anuncio guardado! Se publicará automáticamente cuando tu cuenta sea verificada.';
        }

        SessionManager::flash('success', $mensaje);
        $this->redirect('/mis-anuncios');
    }

    // ---------------------------------------------------------
    // EDITAR ANUNCIO
    // ---------------------------------------------------------

    public function edit(array $params = []): void
    {
        $this->requireAuth();

        $id   = (int) ($params['id'] ?? 0);
        $user = $this->currentUser();

        $anuncio = $this->anuncios->find($id);

        if (!$anuncio || !$this->anuncios->perteneceA($id, (int) $user['id'])) {
            SessionManager::flash('error', 'Anuncio no encontrado.');
            $this->redirect('/mis-anuncios');
        }

        // No editar anuncios expirados o publicados si no son del usuario
        if ($anuncio['estado'] === 'expirado') {
            SessionManager::flash('warning', 'No puedes editar un anuncio expirado.');
            $this->redirect('/mis-anuncios');
        }

        $categorias = $this->categorias->activas();
        $estados    = $this->estados->activos();

        // Cargar municipios del estado actual si existe
        $municipios = [];
        if (!empty($anuncio['id_estado'])) {
            require_once APP_PATH . '/models/MunicipioModel.php';
            $municipios = (new MunicipioModel())->porEstado((int) $anuncio['id_estado']);
        }

        $fotosExistentes = $this->fotos->porAnuncio($id);

        // Migración automática: si el anuncio tiene imagen_principal legacy
        // pero aún no tiene entradas en anuncio_fotos, la migramos al nuevo sistema.
        if (empty($fotosExistentes) && !empty($anuncio['imagen_principal']) && empty($anuncio['imagen_token'])) {
            $nuevoId    = $this->fotos->guardar($id, $anuncio['imagen_principal'], 0);
            $fotoNueva  = $this->fotos->find($nuevoId);
            if ($fotoNueva) {
                $this->anuncios->update($id, ['imagen_token' => $fotoNueva['token']]);
                $anuncio['imagen_token'] = $fotoNueva['token'];
            }
            $fotosExistentes = $this->fotos->porAnuncio($id);
        }

        $this->render('ads/edit', [
            'pageTitle'       => 'Editar anuncio',
            'anuncio'         => $anuncio,
            'categorias'      => $categorias,
            'estados'         => $estados,
            'municipios'      => $municipios,
            'fotosExistentes' => $fotosExistentes,
            'maxFotos'        => self::MAX_FOTOS,
        ]);
    }

    public function update(array $params = []): void
    {
        $this->requireAuth();

        $id   = (int) ($params['id'] ?? 0);
        $user = $this->currentUser();

        $anuncio = $this->anuncios->find($id);

        if (!$anuncio || !$this->anuncios->perteneceA($id, (int) $user['id'])) {
            SessionManager::flash('error', 'Anuncio no encontrado.');
            $this->redirect('/mis-anuncios');
        }

        if ($anuncio['estado'] === 'expirado') {
            SessionManager::flash('warning', 'No puedes editar un anuncio expirado.');
            $this->redirect('/mis-anuncios');
        }

        // Sanitizar
        $titulo      = Security::sanitizeString($_POST['titulo']     ?? '');
        $descripcion = Security::sanitizeText($_POST['descripcion']  ?? '');
        $idEstado    = (int) ($_POST['id_estado']    ?? 0);
        $idMunicipio = (int) ($_POST['id_municipio'] ?? 0);
        $idCategoria = (int) ($_POST['id_categoria'] ?? 0);
        $whatsapp    = Security::sanitizePhone($_POST['whatsapp']     ?? '');

        // Obtener nombre del municipio como ciudad
        $ciudad = '';
        if ($idMunicipio > 0) {
            require_once APP_PATH . '/models/MunicipioModel.php';
            $munModel = new MunicipioModel();
            $mun = $munModel->find($idMunicipio);
            $ciudad = $mun['nombre'] ?? '';
        }

        $categoriasValidas = array_column($this->categorias->activas(), 'id');
        $estadosValidos    = array_column($this->estados->activos(), 'id');

        $v = new Validator([
            'titulo'       => $titulo,
            'descripcion'  => $descripcion,
            'id_estado'    => $idEstado,
            'id_municipio' => $idMunicipio,
            'id_categoria' => $idCategoria,
        ]);

        $v->required('titulo', 'Título')->minLength('titulo', 5)->maxLength('titulo', 120)->noHtml('titulo', 'Título')
          ->required('descripcion', 'Descripción')->minLength('descripcion', 20)->maxLength('descripcion', 3000)
          ->custom($idEstado <= 0, 'id_estado', 'Debes seleccionar un estado.')
          ->custom($idEstado > 0 && !in_array($idEstado, $estadosValidos, true), 'id_estado', 'Estado no válido.')
          ->custom($idMunicipio <= 0, 'id_municipio', 'Debes seleccionar un municipio.')
          ->required('id_categoria', 'Categoría')->custom(!in_array($idCategoria, $categoriasValidas, true), 'id_categoria', 'Categoría no válida.');

        if ($v->fails()) {
            foreach ($v->allErrors() as $err) {
                SessionManager::flash('error', $err);
            }
            $this->redirect("/anuncio/{$id}/editar");
        }

        // Eliminar fotos marcadas para borrar
        $eliminarIds = array_filter(array_map('intval', (array)($_POST['eliminar_foto'] ?? [])));
        foreach ($eliminarIds as $fotoId) {
            $this->fotos->eliminar($fotoId, $id);
        }

        // Agregar fotos nuevas (hasta completar el límite)
        $totalActual  = $this->fotos->contar($id);
        $disponibles  = max(0, self::MAX_FOTOS - $totalActual);
        if ($disponibles > 0 && !empty($_FILES['fotos']['name'][0])) {
            $nuevos = $this->procesarFotos('fotos', "/anuncio/{$id}/editar", $disponibles);
            foreach ($nuevos as $orden => $filename) {
                $this->fotos->guardar($id, $filename, $totalActual + $orden);
            }
        }

        // Reordenar y actualizar imagen_token (respeta la foto principal elegida)
        $fotoPrincipalId = Security::sanitizeInt($_POST['foto_principal_id'] ?? 0) ?: null;
        $primerToken     = $this->fotos->reordenar($id, $fotoPrincipalId);
        $primerFoto      = $this->fotos->porAnuncio($id)[0] ?? null;

        $this->anuncios->editar($id, [
            'titulo'          => $titulo,
            'descripcion'     => $descripcion,
            'ciudad'          => $ciudad,
            'id_estado'       => $idEstado,
            'id_municipio'    => $idMunicipio,
            'id_categoria'    => $idCategoria,
            'whatsapp'        => $whatsapp ?: null,
            'imagen_principal' => $primerFoto['nombre_archivo'] ?? $anuncio['imagen_principal'],
            'imagen_token'    => $primerToken,
        ]);

        SessionManager::flash('success', 'Anuncio actualizado. Quedará en revisión nuevamente.');
        $this->redirect('/mis-anuncios');
    }

    // ---------------------------------------------------------
    // ELIMINAR ANUNCIO
    // ---------------------------------------------------------

    public function delete(array $params = []): void
    {
        $this->requireAuth();

        $id   = (int) ($params['id'] ?? 0);
        $user = $this->currentUser();

        // Admin puede eliminar cualquiera; usuario solo los suyos
        $esAdmin = $user['rol'] === 'admin';

        $anuncio = $this->anuncios->find($id);

        if (!$anuncio) {
            SessionManager::flash('error', 'Anuncio no encontrado.');
            $this->redirect('/mis-anuncios');
        }

        if (!$esAdmin && !$this->anuncios->perteneceA($id, (int) $user['id'])) {
            SessionManager::flash('error', 'No tienes permiso para eliminar este anuncio.');
            $this->redirect('/mis-anuncios');
        }

        // Eliminar todas las fotos del anuncio
        $this->fotos->eliminarPorAnuncio($id);

        $this->anuncios->delete($id);

        SessionManager::flash('success', 'Anuncio eliminado correctamente.');
        $destino = $esAdmin ? '/admin/anuncios' : '/mis-anuncios';
        $this->redirect($destino);
    }

    // ---------------------------------------------------------
    // REPORTAR ANUNCIO
    // ---------------------------------------------------------

    public function report(array $params = []): void
    {
        $id     = (int) ($params['id'] ?? 0);
        $ip     = Security::getClientIp();
        $motivo = Security::sanitizeString($_POST['motivo'] ?? 'otro');
        $detalle = Security::sanitizeText($_POST['descripcion'] ?? '');

        $motivosValidos = ['contenido_ilegal','spam','engaño','menor_de_edad','datos_falsos','otro'];
        if (!in_array($motivo, $motivosValidos, true)) {
            $motivo = 'otro';
        }

        // Anti-spam: 1 reporte por IP por anuncio en 24h
        if ($this->reportes->yaReporto($id, $ip)) {
            if ($this->isAjax()) {
                $this->json(['error' => 'Ya reportaste este anuncio recientemente.'], 429);
            }
            SessionManager::flash('warning', 'Ya reportaste este anuncio recientemente.');
            $this->redirect("/anuncio/{$id}");
        }

        $user = $this->currentUser();

        $this->reportes->crear([
            'id_anuncio'  => $id,
            'id_usuario'  => $user ? (int) $user['id'] : null,
            'motivo'      => $motivo,
            'descripcion' => $detalle ?: null,
            'ip_reporte'  => $ip,
        ]);

        if ($this->isAjax()) {
            $this->json(['success' => true, 'message' => 'Reporte enviado. Gracias por ayudarnos.']);
        }

        SessionManager::flash('success', 'Reporte enviado. Nuestro equipo lo revisará.');
        $this->redirect("/anuncio/{$id}");
    }

    // ---------------------------------------------------------
    // HELPER PRIVADO: procesar array de archivos subidos
    // ---------------------------------------------------------

    /**
     * Valida y guarda los archivos del campo múltiple dado.
     * Retorna array de nombres de archivo guardados.
     *
     * @param string $fieldName  Nombre del input file (con multiple)
     * @param string $redirectOn Ruta a la que redirigir en error
     * @param int    $limit      Máximo de archivos a procesar
     */
    private function procesarFotos(
        string $fieldName,
        string $redirectOn,
        int    $limit = self::MAX_FOTOS
    ): array {
        $archivos = $_FILES[$fieldName] ?? null;

        if (!$archivos || empty($archivos['name'][0])) {
            return [];
        }

        // Normalizar $_FILES multi al formato estándar de $_FILES por archivo
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
        $uploader = new Upload();

        foreach ($lista as $file) {
            if (!$uploader->saveImage($file, 'anuncios')) {
                foreach ($uploader->getErrors() as $err) {
                    SessionManager::flash('error', $err);
                }
                $this->redirect($redirectOn);
            }
            $guardados[] = $uploader->getSavedFilename();
        }

        return $guardados;
    }
}
