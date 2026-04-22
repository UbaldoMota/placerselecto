<?php
/**
 * ImageController.php
 * Proxy seguro para servir imágenes de anuncios.
 *
 * Las imágenes NUNCA se exponen con su ruta real en disco.
 * Se acceden únicamente mediante un token aleatorio:
 *   GET /img/{token}
 *
 * Medidas de seguridad aplicadas:
 *  - Token de 40 hex (random_bytes) — imposible de adivinar
 *  - Validación de formato del token antes de tocar la BD
 *  - Archivo físico en directorio con Require all denied
 *  - Cabeceras: X-Content-Type-Options, X-Frame-Options, Cache-Control private
 *  - No se expone la ruta real ni el nombre del archivo
 *  - Sólo se sirven imágenes de anuncios publicados
 *    (o del propio usuario / admin si no está publicado)
 */

require_once APP_PATH . '/Controller.php';
require_once APP_PATH . '/models/FotoModel.php';
require_once APP_PATH . '/models/PerfilFotoModel.php';
require_once APP_PATH . '/models/PerfilVideoModel.php';
require_once APP_PATH . '/models/AnuncioModel.php';
require_once APP_PATH . '/models/PerfilModel.php';

class ImageController extends Controller
{
    private FotoModel         $fotos;
    private PerfilFotoModel   $perfilFotos;
    private PerfilVideoModel  $perfilVideos;
    private AnuncioModel      $anuncios;
    private PerfilModel       $perfiles;

    public function __construct()
    {
        $this->fotos        = new FotoModel();
        $this->perfilFotos  = new PerfilFotoModel();
        $this->perfilVideos = new PerfilVideoModel();
        $this->anuncios     = new AnuncioModel();
        $this->perfiles     = new PerfilModel();
    }

    /**
     * GET /img/{token}
     * Sirve la imagen si el acceso está autorizado.
     * Busca en anuncio_fotos primero, luego en perfil_fotos.
     */
    public function serve(array $params = []): void
    {
        $token = $params['token'] ?? '';

        // 1. Validar formato del token (exactamente 40 hex)
        if (!ctype_xdigit($token) || strlen($token) !== 40) {
            $this->abort(404);
        }

        // 2. Buscar en BD — primero anuncios, luego perfiles
        $foto      = $this->fotos->porToken($token);
        $esPerfil  = false;
        if (!$foto) {
            $foto     = $this->perfilFotos->porToken($token);
            $esPerfil = ($foto !== null);
        }
        if (!$foto) {
            $this->abort(404);
        }

        // 3. Verificar acceso según tipo de entidad
        $user = $this->currentUser();
        $esAdmin = ($user['rol'] ?? '') === 'admin';

        if ($esPerfil) {
            $entidad   = $this->perfiles->find((int)$foto['id_perfil']);
            $idDuenio  = $entidad['id_usuario'] ?? null;
            $publicado = ($entidad['estado'] ?? '') === 'publicado';
        } else {
            $entidad   = $this->anuncios->find((int)$foto['id_anuncio']);
            $idDuenio  = $entidad['id_usuario'] ?? null;
            $publicado = ($entidad['estado'] ?? '') === 'publicado';
        }

        if (!$entidad) {
            $this->abort(404);
        }

        $esDuenio = $user && (int)$user['id'] === (int)$idDuenio;

        // Las fotos de verificación de perfil son EXCLUSIVAS para el administrador.
        // Nunca se sirven públicamente ni al propio dueño del perfil.
        if ($esPerfil && !empty($foto['es_verificacion'])) {
            if (!$esAdmin) {
                $this->abort(403);
            }
        }

        if (!$publicado && !$esDuenio && !$esAdmin) {
            $this->abort(403);
        }

        // 4. Construir ruta física (solo basename — anti path-traversal)
        $filename = basename($foto['nombre_archivo']);
        $path     = UPLOADS_PATH . '/anuncios/' . $filename;

        if (!file_exists($path) || !is_file($path)) {
            $this->abort(404);
        }

        // 5. Detectar tipo MIME real
        $finfo    = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $path);
        finfo_close($finfo);

        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($mimeType, $allowed, true)) {
            $this->abort(415);
        }

        // 6. Cabeceras de seguridad
        header('Content-Type: '      . $mimeType);
        header('Content-Length: '    . filesize($path));
        header('Cache-Control: private, max-age=7200');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        // No exponer nombre real — usar nombre genérico
        header('Content-Disposition: inline; filename="foto.jpg"');

        // Evitar que el layout se inyecte
        readfile($path);
        exit;
    }

    /**
     * GET /video/{token}
     * Sirve video público del perfil (proxy seguro).
     */
    public function serveVideo(array $params = []): void
    {
        $token = $params['token'] ?? '';
        if (!ctype_xdigit($token) || strlen($token) !== 40) {
            $this->abort(404);
        }

        $video = $this->perfilVideos->porToken($token);
        if (!$video) {
            $this->abort(404);
        }

        $perfil = $this->perfiles->find((int)$video['id_perfil']);
        if (!$perfil) {
            $this->abort(404);
        }

        $user    = $this->currentUser();
        $esAdmin = ($user['rol'] ?? '') === 'admin';
        $esDuenio = $user && (int)$user['id'] === (int)$perfil['id_usuario'];
        $publicado = $perfil['estado'] === 'publicado';

        if ((!empty($video['oculta']) && !$esAdmin && !$esDuenio) ||
            (!$publicado && !$esDuenio && !$esAdmin)) {
            $this->abort(403);
        }

        $filename = basename($video['nombre_archivo']);
        $path     = UPLOADS_PATH . '/videos/' . $filename;

        if (!file_exists($path) || !is_file($path)) {
            $this->abort(404);
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $path);
        finfo_close($finfo);

        if (!in_array($mime, PerfilVideoModel::ALLOWED_MIME, true)) {
            $this->abort(415);
        }

        $size = filesize($path);

        // Soporte básico de Range requests para <video>
        $start = 0;
        $end   = $size - 1;
        $code  = 200;
        if (!empty($_SERVER['HTTP_RANGE']) && preg_match('/bytes=(\d+)-(\d*)/', $_SERVER['HTTP_RANGE'], $m)) {
            $start = (int)$m[1];
            if ($m[2] !== '') $end = min((int)$m[2], $size - 1);
            $code = 206;
            header('HTTP/1.1 206 Partial Content');
            header("Content-Range: bytes {$start}-{$end}/{$size}");
        }

        header('Content-Type: ' . $mime);
        header('Accept-Ranges: bytes');
        header('Content-Length: ' . ($end - $start + 1));
        header('Cache-Control: private, max-age=3600');
        header('X-Content-Type-Options: nosniff');
        header('Content-Disposition: inline; filename="video.mp4"');

        $fp = fopen($path, 'rb');
        fseek($fp, $start);
        $buffer = 8192;
        $remaining = $end - $start + 1;
        while (!feof($fp) && $remaining > 0 && !connection_aborted()) {
            $read = ($remaining > $buffer) ? $buffer : $remaining;
            echo fread($fp, $read);
            flush();
            $remaining -= $read;
        }
        fclose($fp);
        exit;
    }

    // ---------------------------------------------------------
    // Helpers privados
    // ---------------------------------------------------------

    private function abort(int $code): never
    {
        http_response_code($code);
        exit;
    }
}
