<?php
/**
 * ApiController.php
 * API REST JSON para operaciones AJAX.
 * Todas las respuestas son JSON. Sin layout.
 */

require_once APP_PATH . '/Controller.php';
require_once APP_PATH . '/Security.php';
require_once APP_PATH . '/models/AnuncioModel.php';
require_once APP_PATH . '/models/CategoriaModel.php';
require_once APP_PATH . '/models/CiudadModel.php';
require_once APP_PATH . '/models/MunicipioModel.php';

class ApiController extends Controller
{
    private AnuncioModel    $anuncios;
    private CategoriaModel  $categorias;
    private CiudadModel     $ciudades;
    private MunicipioModel  $municipios;

    public function __construct()
    {
        $this->anuncios   = new AnuncioModel();
        $this->categorias = new CategoriaModel();
        $this->ciudades   = new CiudadModel();
        $this->municipios = new MunicipioModel();
    }

    // ---------------------------------------------------------
    // GET /api/municipios/{id_estado}
    // Retorna municipios en JSON para cascading select
    // ---------------------------------------------------------
    public function getMunicipios(array $params = []): void
    {
        Security::setJsonHeaders();
        Security::setNoCacheHeaders();

        $idEstado = (int) ($params['id_estado'] ?? 0);

        if ($idEstado <= 0) {
            $this->json(['success' => false, 'municipios' => []], 400);
        }

        $lista = $this->municipios->porEstado($idEstado);

        $this->json([
            'success'    => true,
            'municipios' => $lista,
        ]);
    }

    // ---------------------------------------------------------
    // GET /api/anuncios?q=&ciudad=&categoria=&page=
    // Búsqueda en tiempo real con paginación
    // ---------------------------------------------------------
    public function getAds(array $params = []): void
    {
        Security::setJsonHeaders();
        Security::setNoCacheHeaders();

        $page      = max(1, (int) $this->getParam('page', '1'));
        $ciudad    = $this->getParam('ciudad', '');
        $categoria = (int) $this->getParam('categoria', '0');
        $buscar    = $this->getParam('q', '');

        // Limitar longitud de búsqueda
        $buscar = mb_substr($buscar, 0, 100, 'UTF-8');

        $result = $this->anuncios->listarPublicos($page, $ciudad, $categoria, $buscar);

        // Transformar items para el frontend (solo campos necesarios)
        $items = array_map(function ($ad) {
            return [
                'id'              => (int) $ad['id'],
                'titulo'          => $ad['titulo'],
                'ciudad'          => $ad['ciudad'],
                'categoria'       => $ad['categoria_nombre'] ?? '',
                'imagen'          => $ad['imagen_principal']
                                        ? APP_URL . '/uploads/anuncios/' . $ad['imagen_principal']
                                        : null,
                'destacado'       => (bool) $ad['destacado'],
                'vistas'          => (int) $ad['vistas'],
                'tiempo'          => Security::timeAgo($ad['fecha_publicacion'] ?? $ad['fecha_creacion']),
                'url'             => APP_URL . '/anuncio/' . $ad['id'],
            ];
        }, $result['items']);

        $this->json([
            'success'    => true,
            'items'      => $items,
            'total'      => $result['total'],
            'pages'      => $result['pages'],
            'current'    => $result['current'],
            'perPage'    => $result['perPage'],
        ]);
    }

    // ---------------------------------------------------------
    // GET /api/anuncio/{id}
    // Detalle de un anuncio
    // ---------------------------------------------------------
    public function getAd(array $params = []): void
    {
        Security::setJsonHeaders();

        $id      = (int) ($params['id'] ?? 0);
        $anuncio = $this->anuncios->obtenerPublico($id);

        if (!$anuncio) {
            $this->json(['error' => 'Anuncio no encontrado.'], 404);
        }

        $this->json([
            'success' => true,
            'item'    => [
                'id'          => (int) $anuncio['id'],
                'titulo'      => $anuncio['titulo'],
                'descripcion' => Security::truncate($anuncio['descripcion'], 300),
                'ciudad'      => $anuncio['ciudad'],
                'categoria'   => $anuncio['categoria_nombre'] ?? '',
                'imagen'      => $anuncio['imagen_principal']
                                    ? APP_URL . '/uploads/anuncios/' . $anuncio['imagen_principal']
                                    : null,
                'whatsapp'    => $anuncio['whatsapp']
                                    ? 'https://wa.me/' . preg_replace('/\D/', '', $anuncio['whatsapp'])
                                    : null,
                'destacado'   => (bool) $anuncio['destacado'],
                'vistas'      => (int) $anuncio['vistas'],
                'url'         => APP_URL . '/anuncio/' . $anuncio['id'],
            ],
        ]);
    }

    // ---------------------------------------------------------
    // POST /api/anuncio/{id}/like  (para uso futuro)
    // ---------------------------------------------------------
    public function likeAd(array $params = []): void
    {
        Security::setJsonHeaders();
        $this->json(['success' => true, 'message' => 'Función disponible próximamente.']);
    }

    // ---------------------------------------------------------
    // GET /tile/{z}/{x}/{y}  — Proxy de tiles OpenStreetMap
    // Sirve las imágenes desde el propio dominio para cumplir CSP img-src 'self'.
    // ---------------------------------------------------------
    public function tile(array $params = []): void
    {
        $z = (int)($params['z'] ?? -1);
        $x = (int)($params['x'] ?? -1);
        // "y" puede traer extensión ".png" — limpiarla
        $yRaw = (string)($params['y'] ?? '');
        $y = (int)preg_replace('/\.png$/', '', $yRaw);

        // Validar rangos razonables
        if ($z < 0 || $z > 19 || $x < 0 || $y < 0) {
            http_response_code(400);
            exit;
        }

        // Rotación de servidores a, b, c
        $servers = ['a', 'b', 'c'];
        $srv     = $servers[($x + $y) % 3];
        $url     = "https://{$srv}.tile.openstreetmap.org/{$z}/{$x}/{$y}.png";

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_USERAGENT      => 'PlacerSelecto/1.0 (+' . APP_URL . ')',
            CURLOPT_HTTPHEADER     => ['Referer: ' . APP_URL],
        ]);
        $data = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code !== 200 || !$data) {
            http_response_code(502);
            exit;
        }

        header('Content-Type: image/png');
        header('Cache-Control: public, max-age=86400'); // 24h
        header('Content-Length: ' . strlen($data));
        echo $data;
        exit;
    }
}
