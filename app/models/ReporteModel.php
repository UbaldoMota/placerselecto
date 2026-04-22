<?php
/**
 * ReporteModel.php
 * Modelo para la tabla `reportes`.
 * Gestiona reportes de contenido inapropiado.
 */

require_once APP_PATH . '/Model.php';

class ReporteModel extends Model
{
    protected string $table      = 'reportes';
    protected string $primaryKey = 'id';

    /**
     * Crea un nuevo reporte.
     *
     * @param array $data ['id_anuncio', 'motivo', 'descripcion', 'id_usuario', 'ip_reporte']
     * @return int ID del reporte creado
     */
    public function crear(array $data): int
    {
        return $this->insert([
            'id_anuncio'  => $data['id_anuncio'],
            'id_usuario'  => $data['id_usuario'] ?? null,
            'motivo'      => $data['motivo'],
            'descripcion' => $data['descripcion'] ?? null,
            'estado'      => 'pendiente',
            'ip_reporte'  => $data['ip_reporte'] ?? null,
            'fecha'       => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Verifica si una IP ya reportó el mismo anuncio recientemente (anti-spam).
     * Límite: 1 reporte por IP por anuncio en las últimas 24 horas.
     */
    public function yaReporto(int $idAnuncio, string $ip): bool
    {
        $sql  = "SELECT 1 FROM `reportes`
                 WHERE `id_anuncio` = ?
                   AND `ip_reporte` = ?
                   AND `fecha` > DATE_SUB(NOW(), INTERVAL 24 HOUR)
                 LIMIT 1";
        return (bool) $this->raw($sql, [$idAnuncio, $ip])->fetchColumn();
    }

    /**
     * Marca un reporte como resuelto.
     */
    public function resolver(int $id): bool
    {
        return $this->update($id, [
            'estado'            => 'resuelto',
            'fecha_resolucion'  => date('Y-m-d H:i:s'),
        ]) > 0;
    }

    /**
     * Marca un reporte como revisado (en proceso).
     */
    public function revisar(int $id): bool
    {
        return $this->update($id, ['estado' => 'revisado']) > 0;
    }

    /**
     * Listado de reportes para admin (paginado, con datos del anuncio).
     */
    public function listarAdmin(int $page = 1, string $estado = ''): array
    {
        $where  = [];
        $params = [];

        if ($estado !== '') {
            $where[]  = 'r.estado = ?';
            $params[] = $estado;
        }

        $whereSQL = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        $perPage  = ITEMS_PER_PAGE;
        $offset   = (max(1, $page) - 1) * $perPage;

        $total = (int) $this->raw(
            "SELECT COUNT(*) FROM `reportes` r {$whereSQL}",
            $params
        )->fetchColumn();

        $sql = "SELECT
                    r.*,
                    a.titulo          AS anuncio_titulo,
                    a.estado          AS anuncio_estado,
                    p.nombre          AS perfil_nombre,
                    p.estado          AS perfil_estado,
                    p.imagen_token    AS perfil_imagen,
                    p.ciudad          AS perfil_ciudad,
                    cat.nombre        AS perfil_categoria,
                    mun.nombre        AS perfil_municipio,
                    pu.nombre         AS perfil_dueno,
                    u.nombre          AS usuario_nombre,
                    u.email           AS usuario_email
                FROM `reportes` r
                LEFT JOIN `anuncios`   a   ON a.id = r.id_anuncio
                LEFT JOIN `perfiles`   p   ON p.id = r.id_perfil
                LEFT JOIN `categorias` cat ON cat.id = p.id_categoria
                LEFT JOIN `municipios` mun ON mun.id = p.id_municipio
                LEFT JOIN `usuarios`   pu  ON pu.id = p.id_usuario
                LEFT JOIN `usuarios`   u   ON u.id = r.id_usuario
                {$whereSQL}
                ORDER BY
                   CASE r.estado WHEN 'pendiente' THEN 0 WHEN 'revisado' THEN 1 ELSE 2 END,
                   r.fecha DESC
                LIMIT ? OFFSET ?";

        $stmt = $this->raw($sql, array_merge($params, [$perPage, $offset]));

        return [
            'items'   => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'total'   => $total,
            'pages'   => (int) ceil($total / $perPage),
            'current' => (int) $page,
        ];
    }

    /**
     * Cuenta de reportes pendientes (para badge en admin).
     */
    public function contarPendientes(): int
    {
        return $this->count('estado = ?', ['pendiente']);
    }

    /**
     * Estadísticas para dashboard admin.
     */
    public function estadisticas(): array
    {
        $sql = "SELECT
                    COUNT(*)                          AS total,
                    SUM(estado = 'pendiente')         AS pendientes,
                    SUM(estado = 'revisado')          AS revisados,
                    SUM(estado = 'resuelto')          AS resueltos
                FROM `reportes`";

        return $this->raw($sql)->fetch(PDO::FETCH_ASSOC);
    }
}
