<?php
/**
 * AnuncioModel.php
 * Modelo para la tabla `anuncios`.
 * Gestiona CRUD, publicación, destacados, búsqueda y paginación.
 */

require_once APP_PATH . '/Model.php';

class AnuncioModel extends Model
{
    protected string $table      = 'anuncios';
    protected string $primaryKey = 'id';

    /** Días de vigencia de un anuncio publicado */
    private const VIGENCIA_DIAS = 30;

    // ---------------------------------------------------------
    // CREACIÓN Y EDICIÓN
    // ---------------------------------------------------------

    /**
     * Crea un nuevo anuncio.
     * Estado inicial: 'pendiente' (requiere aprobación de admin).
     *
     * @param array $data
     * @return int ID del anuncio creado
     */
    public function crear(array $data): int
    {
        return $this->insert([
            'id_usuario'      => $data['id_usuario'],
            'titulo'          => $data['titulo'],
            'descripcion'     => $data['descripcion'],
            'ciudad'          => $data['ciudad'],
            'id_estado'       => $data['id_estado']       ?? null,
            'id_municipio'    => $data['id_municipio']    ?? null,
            'id_categoria'    => $data['id_categoria'],
            'imagen_principal' => $data['imagen_principal'] ?? null,
            'imagen_token'    => $data['imagen_token']    ?? null,
            'whatsapp'        => $data['whatsapp']        ?? null,
            'estado'          => 'pendiente',
            'destacado'       => 0,
            'vistas'          => 0,
            'fecha_creacion'  => date('Y-m-d H:i:s'),
            'fecha_actualizacion' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Actualiza los campos editables de un anuncio.
     * Al editar, vuelve a estado 'pendiente' para re-aprobación.
     */
    public function editar(int $id, array $data): bool
    {
        $campos = [
            'titulo'          => $data['titulo'],
            'descripcion'     => $data['descripcion'],
            'ciudad'          => $data['ciudad'],
            'id_estado'       => $data['id_estado']    ?? null,
            'id_municipio'    => $data['id_municipio'] ?? null,
            'id_categoria'    => $data['id_categoria'],
            'whatsapp'        => $data['whatsapp'] ?? null,
            'estado'          => 'pendiente',
            'fecha_actualizacion' => date('Y-m-d H:i:s'),
        ];

        if (!empty($data['imagen_principal'])) {
            $campos['imagen_principal'] = $data['imagen_principal'];
        }
        if (array_key_exists('imagen_token', $data)) {
            $campos['imagen_token'] = $data['imagen_token'];
        }

        return $this->update($id, $campos) > 0;
    }

    /**
     * Verifica que el anuncio pertenezca al usuario dado.
     */
    public function perteneceA(int $idAnuncio, int $idUsuario): bool
    {
        $sql  = "SELECT 1 FROM `anuncios` WHERE `id` = ? AND `id_usuario` = ? LIMIT 1";
        $stmt = $this->raw($sql, [$idAnuncio, $idUsuario]);
        return (bool) $stmt->fetchColumn();
    }

    // ---------------------------------------------------------
    // PUBLICACIÓN Y MODERACIÓN (admin)
    // ---------------------------------------------------------

    /**
     * Publica un anuncio: cambia estado a 'publicado' y setea fechas.
     */
    public function publicar(int $id): bool
    {
        $expiracion = date('Y-m-d H:i:s', strtotime('+' . self::VIGENCIA_DIAS . ' days'));
        return $this->update($id, [
            'estado'            => 'publicado',
            'fecha_publicacion' => date('Y-m-d H:i:s'),
            'fecha_expiracion'  => $expiracion,
            'fecha_actualizacion' => date('Y-m-d H:i:s'),
        ]) > 0;
    }

    /**
     * Rechaza un anuncio desde admin.
     */
    public function rechazar(int $id): bool
    {
        return $this->update($id, [
            'estado'              => 'rechazado',
            'fecha_actualizacion' => date('Y-m-d H:i:s'),
        ]) > 0;
    }

    // ---------------------------------------------------------
    // DESTACADOS
    // ---------------------------------------------------------

    /**
     * Activa el plan de destacado para un anuncio.
     *
     * @param int $id        ID del anuncio
     * @param int $diasPlan  3, 7 o 15
     */
    public function activarDestacado(int $id, int $diasPlan): bool
    {
        $inicio     = date('Y-m-d H:i:s');
        $expiracion = date('Y-m-d H:i:s', strtotime("+{$diasPlan} days"));

        return $this->update($id, [
            'destacado'                  => 1,
            'fecha_destacado'            => $inicio,
            'fecha_expiracion_destacado' => $expiracion,
            'fecha_actualizacion'        => $inicio,
        ]) > 0;
    }

    /**
     * Desactiva destacado manualmente o por expiración.
     */
    public function desactivarDestacado(int $id): bool
    {
        return $this->update($id, [
            'destacado'                  => 0,
            'fecha_destacado'            => null,
            'fecha_expiracion_destacado' => null,
            'fecha_actualizacion'        => date('Y-m-d H:i:s'),
        ]) > 0;
    }

    /**
     * Procesa expiración de destacados (alternativa al evento MySQL).
     * Llamar desde un cron job o desde el listado público.
     */
    public function expirarDestacados(): int
    {
        $sql  = "UPDATE `anuncios`
                 SET `destacado` = 0,
                     `fecha_destacado` = NULL,
                     `fecha_expiracion_destacado` = NULL,
                     `fecha_actualizacion` = NOW()
                 WHERE `destacado` = 1
                   AND `fecha_expiracion_destacado` IS NOT NULL
                   AND `fecha_expiracion_destacado` < NOW()";
        $stmt = $this->raw($sql);
        return $stmt->rowCount();
    }

    // ---------------------------------------------------------
    // CONTADOR DE VISTAS
    // ---------------------------------------------------------

    /**
     * Incrementa el contador de vistas de un anuncio.
     * Se usa SQL directo para evitar race conditions.
     */
    public function incrementarVistas(int $id): void
    {
        $this->raw(
            "UPDATE `anuncios` SET `vistas` = `vistas` + 1 WHERE `id` = ?",
            [$id]
        );
    }

    // ---------------------------------------------------------
    // LISTADOS PÚBLICOS
    // ---------------------------------------------------------

    /**
     * Listado público paginado: solo anuncios publicados.
     * Orden: destacados primero, luego por fecha de publicación DESC.
     *
     * @param int    $page
     * @param string $ciudad     Filtro por ciudad ('' = todas)
     * @param int    $categoria  Filtro por categoría (0 = todas)
     * @param string $buscar     Texto a buscar en título y descripción
     * @return array{items: array, total: int, pages: int, current: int}
     */
    public function listarPublicos(
        int    $page      = 1,
        string $ciudad    = '',
        int    $categoria = 0,
        string $buscar    = ''
    ): array {
        // Expirar destacados vencidos antes de mostrar
        $this->expirarDestacados();

        $where  = ['a.estado = ?'];
        $params = ['publicado'];

        if ($ciudad !== '') {
            $where[]  = 'a.ciudad = ?';
            $params[] = $ciudad;
        }

        if ($categoria > 0) {
            $where[]  = 'a.id_categoria = ?';
            $params[] = $categoria;
        }

        if ($buscar !== '') {
            $where[]  = '(a.titulo LIKE ? OR a.descripcion LIKE ?)';
            $params[] = '%' . $buscar . '%';
            $params[] = '%' . $buscar . '%';
        }

        $whereSQL = implode(' AND ', $where);
        $perPage  = ITEMS_PER_PAGE;
        $offset   = (max(1, $page) - 1) * $perPage;

        // Total
        $countSQL  = "SELECT COUNT(*) FROM `anuncios` a WHERE {$whereSQL}";
        $countStmt = $this->raw($countSQL, $params);
        $total     = (int) $countStmt->fetchColumn();

        // Datos con JOIN a categorias, estados y municipios
        $sql = "SELECT
                    a.*,
                    c.nombre  AS categoria_nombre,
                    c.slug    AS categoria_slug,
                    c.icono   AS categoria_icono,
                    e.nombre  AS estado_nombre,
                    m.nombre  AS municipio_nombre
                FROM `anuncios` a
                LEFT JOIN `categorias` c  ON c.id = a.id_categoria
                LEFT JOIN `estados`    e  ON e.id = a.id_estado
                LEFT JOIN `municipios` m  ON m.id = a.id_municipio
                WHERE {$whereSQL}
                ORDER BY a.destacado DESC, a.fecha_publicacion DESC
                LIMIT ? OFFSET ?";

        $allParams = array_merge($params, [$perPage, $offset]);
        $stmt      = $this->raw($sql, $allParams);

        return [
            'items'   => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'total'   => $total,
            'pages'   => (int) ceil($total / $perPage),
            'current' => (int) $page,
            'perPage' => $perPage,
        ];
    }

    /**
     * Obtiene el detalle completo de un anuncio publicado (con datos de usuario).
     */
    public function obtenerPublico(int $id): ?array
    {
        $sql = "SELECT
                    a.*,
                    u.nombre   AS usuario_nombre,
                    c.nombre   AS categoria_nombre,
                    c.slug     AS categoria_slug,
                    e.nombre   AS estado_nombre,
                    m.nombre   AS municipio_nombre
                FROM `anuncios` a
                LEFT JOIN `usuarios`   u ON u.id = a.id_usuario
                LEFT JOIN `categorias` c ON c.id = a.id_categoria
                LEFT JOIN `estados`    e ON e.id = a.id_estado
                LEFT JOIN `municipios` m ON m.id = a.id_municipio
                WHERE a.id = ?
                  AND a.estado = 'publicado'
                LIMIT 1";

        $stmt = $this->raw($sql, [$id]);
        $row  = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    // ---------------------------------------------------------
    // PANEL DE USUARIO
    // ---------------------------------------------------------

    /**
     * Anuncios de un usuario específico (paginados).
     */
    public function misAnuncios(int $idUsuario, int $page = 1): array
    {
        $sql = "SELECT
                    a.*,
                    c.nombre AS categoria_nombre
                FROM `anuncios` a
                LEFT JOIN `categorias` c ON c.id = a.id_categoria
                WHERE a.id_usuario = ?
                ORDER BY a.fecha_creacion DESC
                LIMIT ? OFFSET ?";

        $perPage = ITEMS_PER_PAGE;
        $offset  = (max(1, $page) - 1) * $perPage;

        $total = $this->count('id_usuario = ?', [$idUsuario]);
        $stmt  = $this->raw($sql, [$idUsuario, $perPage, $offset]);

        return [
            'items'   => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'total'   => $total,
            'pages'   => (int) ceil($total / $perPage),
            'current' => (int) $page,
            'perPage' => $perPage,
        ];
    }

    // ---------------------------------------------------------
    // ADMIN
    // ---------------------------------------------------------

    /**
     * Listado admin paginado con filtros.
     */
    public function listarAdmin(
        int    $page   = 1,
        string $estado = '',
        string $buscar = ''
    ): array {
        $where  = [];
        $params = [];

        if ($estado !== '') {
            $where[]  = 'a.estado = ?';
            $params[] = $estado;
        }

        if ($buscar !== '') {
            $where[]  = '(a.titulo LIKE ? OR u.nombre LIKE ?)';
            $params[] = '%' . $buscar . '%';
            $params[] = '%' . $buscar . '%';
        }

        $whereSQL = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        $perPage  = ITEMS_PER_PAGE;
        $offset   = (max(1, $page) - 1) * $perPage;

        $countSQL = "SELECT COUNT(*) FROM `anuncios` a
                     LEFT JOIN `usuarios` u ON u.id = a.id_usuario
                     {$whereSQL}";
        $total    = (int) $this->raw($countSQL, $params)->fetchColumn();

        $sql = "SELECT
                    a.*,
                    u.nombre AS usuario_nombre,
                    u.email  AS usuario_email,
                    c.nombre AS categoria_nombre
                FROM `anuncios` a
                LEFT JOIN `usuarios`   u ON u.id = a.id_usuario
                LEFT JOIN `categorias` c ON c.id = a.id_categoria
                {$whereSQL}
                ORDER BY a.fecha_creacion DESC
                LIMIT ? OFFSET ?";

        $stmt = $this->raw($sql, array_merge($params, [$perPage, $offset]));

        return [
            'items'   => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'total'   => $total,
            'pages'   => (int) ceil($total / $perPage),
            'current' => (int) $page,
            'perPage' => $perPage,
        ];
    }

    /**
     * Estadísticas de anuncios para dashboard admin.
     */
    public function estadisticas(): array
    {
        $sql = "SELECT
                    COUNT(*)                          AS total,
                    SUM(estado = 'pendiente')         AS pendientes,
                    SUM(estado = 'publicado')         AS publicados,
                    SUM(estado = 'rechazado')         AS rechazados,
                    SUM(estado = 'expirado')          AS expirados,
                    SUM(destacado = 1)                AS destacados
                FROM `anuncios`";

        return $this->raw($sql)->fetch(PDO::FETCH_ASSOC);
    }
}
