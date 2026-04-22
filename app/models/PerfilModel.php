<?php
/**
 * PerfilModel.php
 * Gestiona los perfiles de anunciantes (tabla perfiles).
 * Cada usuario puede tener hasta MAX_PERFILES_POR_USUARIO perfiles.
 */

require_once APP_PATH . '/Model.php';

class PerfilModel extends Model
{
    protected string $table      = 'perfiles';
    protected string $primaryKey = 'id';

    public const MAX_POR_USUARIO = 3;

    // ---------------------------------------------------------
    // ESCRITURA
    // ---------------------------------------------------------

    public function crear(array $data): int
    {
        return $this->insert($data);
    }

    public function editar(int $id, array $data): bool
    {
        $allowed = [
            'nombre','descripcion','edad','edad_publica','ciudad',
            'id_estado','id_municipio','id_categoria',
            'imagen_principal','imagen_token',
            'whatsapp','telegram','email_contacto','pide_anticipo',
            'zona_lat','zona_lng','zona_radio','zona_descripcion',
        ];
        $filtered = array_filter(
            $data,
            fn($k) => in_array($k, $allowed, true),
            ARRAY_FILTER_USE_KEY
        );
        if (empty($filtered)) return false;

        $filtered['estado']            = 'pendiente'; // vuelve a revisión al editar
        $filtered['fecha_publicacion'] = null;

        return $this->update($id, $filtered) >= 0;
    }

    public function publicar(int $id): bool
    {
        return $this->update($id, [
            'estado'            => 'publicado',
            'fecha_publicacion' => date('Y-m-d H:i:s'),
        ]) > 0;
    }

    public function rechazar(int $id): bool
    {
        return $this->update($id, ['estado' => 'rechazado']) > 0;
    }

    public function activarDestacado(int $id, int $diasPlan): bool
    {
        $hasta = date('Y-m-d H:i:s', strtotime("+{$diasPlan} days"));
        return $this->update($id, [
            'destacado'                  => 1,
            'fecha_destacado'            => date('Y-m-d H:i:s'),
            'fecha_expiracion_destacado' => $hasta,
        ]) > 0;
    }

    public function desactivarDestacado(int $id): bool
    {
        return $this->update($id, [
            'destacado'                  => 0,
            'fecha_expiracion_destacado' => null,
        ]) > 0;
    }

    public function incrementarVistas(int $id): void
    {
        $this->raw("UPDATE `perfiles` SET `vistas` = `vistas` + 1 WHERE `id` = ?", [$id]);
        $this->raw(
            "INSERT INTO `perfil_stats` (`id_perfil`, `fecha`, `visitas`)
             VALUES (?, CURDATE(), 1)
             ON DUPLICATE KEY UPDATE `visitas` = `visitas` + 1",
            [$id]
        );
    }

    public function registrarClickWhatsapp(int $id): void
    {
        $this->raw(
            "INSERT INTO `perfil_stats` (`id_perfil`, `fecha`, `clicks_whatsapp`)
             VALUES (?, CURDATE(), 1)
             ON DUPLICATE KEY UPDATE `clicks_whatsapp` = `clicks_whatsapp` + 1",
            [$id]
        );
    }

    /** Totales globales del usuario (visitas históricas + stats desde perfil_stats). */
    public function totalStatsUsuario(int $idUsuario): array
    {
        $row = $this->raw(
            "SELECT
                COALESCE(SUM(p.vistas), 0)                                                            AS total_visitas_hist,
                COALESCE(SUM(ps_all.visitas), 0)                                                      AS total_visitas,
                COALESCE(SUM(ps_all.clicks_whatsapp), 0)                                              AS total_whatsapp,
                COALESCE(SUM(CASE WHEN ps_all.fecha = CURDATE() THEN ps_all.visitas          ELSE 0 END), 0) AS visitas_hoy,
                COALESCE(SUM(CASE WHEN ps_all.fecha = CURDATE() THEN ps_all.clicks_whatsapp  ELSE 0 END), 0) AS whatsapp_hoy,
                COALESCE(SUM(CASE WHEN ps_all.fecha >= CURDATE() - INTERVAL 6 DAY THEN ps_all.visitas         ELSE 0 END), 0) AS visitas_semana,
                COALESCE(SUM(CASE WHEN ps_all.fecha >= CURDATE() - INTERVAL 6 DAY THEN ps_all.clicks_whatsapp ELSE 0 END), 0) AS whatsapp_semana
             FROM perfiles p
             LEFT JOIN perfil_stats ps_all ON ps_all.id_perfil = p.id
             WHERE p.id_usuario = ?",
            [$idUsuario]
        )->fetch(PDO::FETCH_ASSOC);

        return $row ?: [
            'total_visitas_hist' => 0, 'total_visitas' => 0, 'total_whatsapp' => 0,
            'visitas_hoy' => 0, 'whatsapp_hoy' => 0,
            'visitas_semana' => 0, 'whatsapp_semana' => 0,
        ];
    }

    /** Estadísticas por perfil individual del usuario. */
    public function statsPerPerfil(int $idUsuario): array
    {
        return $this->raw(
            "SELECT p.id, p.nombre, p.imagen_token, p.estado, p.vistas AS total_vistas_hist,
                    COALESCE(SUM(ps.visitas), 0)                                                              AS total_visitas,
                    COALESCE(SUM(ps.clicks_whatsapp), 0)                                                      AS total_whatsapp,
                    COALESCE(SUM(CASE WHEN ps.fecha = CURDATE() THEN ps.visitas          ELSE 0 END), 0)       AS visitas_hoy,
                    COALESCE(SUM(CASE WHEN ps.fecha = CURDATE() THEN ps.clicks_whatsapp  ELSE 0 END), 0)       AS whatsapp_hoy,
                    COALESCE(SUM(CASE WHEN ps.fecha >= CURDATE() - INTERVAL 6 DAY THEN ps.visitas ELSE 0 END), 0) AS visitas_semana
             FROM perfiles p
             LEFT JOIN perfil_stats ps ON ps.id_perfil = p.id
             WHERE p.id_usuario = ?
             GROUP BY p.id
             ORDER BY total_visitas DESC",
            [$idUsuario]
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Serie de días para gráfica (rellena días sin datos con 0). */
    public function seriesDias(int $idUsuario, int $idPerfil = 0, int $dias = 7): array
    {
        if ($idPerfil > 0) {
            $rows = $this->raw(
                "SELECT fecha, visitas, clicks_whatsapp
                 FROM `perfil_stats`
                 WHERE `id_perfil` = ? AND `fecha` >= CURDATE() - INTERVAL ? DAY
                 ORDER BY fecha ASC",
                [$idPerfil, $dias - 1]
            )->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $rows = $this->raw(
                "SELECT ps.fecha,
                        COALESCE(SUM(ps.visitas), 0)         AS visitas,
                        COALESCE(SUM(ps.clicks_whatsapp), 0) AS clicks_whatsapp
                 FROM `perfil_stats` ps
                 INNER JOIN `perfiles` p ON p.id = ps.id_perfil
                 WHERE p.id_usuario = ? AND ps.fecha >= CURDATE() - INTERVAL ? DAY
                 GROUP BY ps.fecha ORDER BY ps.fecha ASC",
                [$idUsuario, $dias - 1]
            )->fetchAll(PDO::FETCH_ASSOC);
        }

        $map = [];
        foreach ($rows as $r) $map[$r['fecha']] = $r;

        $serie = [];
        for ($i = $dias - 1; $i >= 0; $i--) {
            $f = date('Y-m-d', strtotime("-{$i} days"));
            $serie[] = $map[$f] ?? ['fecha' => $f, 'visitas' => 0, 'clicks_whatsapp' => 0];
        }
        return $serie;
    }

    // ---------------------------------------------------------
    // LECTURA
    // ---------------------------------------------------------

    public function perteneceA(int $idPerfil, int $idUsuario): bool
    {
        $row = $this->raw(
            "SELECT 1 FROM `perfiles` WHERE `id` = ? AND `id_usuario` = ? LIMIT 1",
            [$idPerfil, $idUsuario]
        )->fetch();
        return (bool) $row;
    }

    public function contarPorUsuario(int $idUsuario): int
    {
        return (int) $this->raw(
            "SELECT COUNT(*) FROM `perfiles` WHERE `id_usuario` = ?",
            [$idUsuario]
        )->fetchColumn();
    }

    /**
     * Perfil completo con JOINs (para detalle público).
     */
    public function obtenerPublico(int $id): ?array
    {
        $row = $this->raw(
            "SELECT p.*,
                    c.nombre  AS categoria_nombre,
                    e.nombre  AS estado_nombre,
                    m.nombre  AS municipio_nombre,
                    u.nombre  AS usuario_nombre,
                    u.verificado,
                    u.estado_verificacion
             FROM perfiles p
             LEFT JOIN categorias c ON c.id = p.id_categoria
             LEFT JOIN estados    e ON e.id = p.id_estado
             LEFT JOIN municipios m ON m.id = p.id_municipio
             LEFT JOIN usuarios   u ON u.id = p.id_usuario
             WHERE p.id = ?
             LIMIT 1",
            [$id]
        )->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    /**
     * Listado público paginado con filtros.
     */
    /**
     * Devuelve los IDs de perfiles que coinciden con los filtros, en el mismo orden
     * que listarPublicos (top > resaltado > fecha). Capado a $limit para evitar cargas grandes.
     * Útil para calcular prev/next en la vista de detalle.
     *
     * @return int[]
     */
    public function listarIdsPublicos(array $filtros = [], int $limit = 500): array
    {
        $where  = ["p.estado = 'publicado'", "p.oculta = 0"];
        $params = [];

        if (!empty($filtros['id_categoria'])) {
            $where[]  = "p.id_categoria = ?";
            $params[] = (int)$filtros['id_categoria'];
        }
        if (!empty($filtros['id_estado'])) {
            $where[]  = "p.id_estado = ?";
            $params[] = (int)$filtros['id_estado'];
        }
        if (!empty($filtros['id_municipio'])) {
            $where[]  = "p.id_municipio = ?";
            $params[] = (int)$filtros['id_municipio'];
        }
        if (!empty($filtros['buscar'])) {
            $where[]  = "(p.nombre LIKE ? OR p.descripcion LIKE ?)";
            $like     = '%' . $filtros['buscar'] . '%';
            $params[] = $like;
            $params[] = $like;
        }

        $whereSQL = implode(' AND ', $where);

        $rows = $this->raw(
            "SELECT p.id
             FROM perfiles p
             LEFT JOIN (
                 SELECT id_perfil,
                        MAX(IF(tipo='top',       1, 0)) AS has_top,
                        MAX(IF(tipo='resaltado', 1, 0)) AS has_resaltado
                 FROM perfil_boost
                 WHERE estado IN ('programado','activo')
                   AND inicio <= NOW() AND fin > NOW()
                 GROUP BY id_perfil
             ) b ON b.id_perfil = p.id
             WHERE {$whereSQL}
             ORDER BY
                IFNULL(b.has_top, 0)       DESC,
                IFNULL(b.has_resaltado, 0) DESC,
                p.fecha_publicacion DESC
             LIMIT ?",
            array_merge($params, [$limit])
        )->fetchAll(PDO::FETCH_COLUMN);

        return array_map('intval', $rows ?: []);
    }

    public function listarPublicos(array $filtros = [], int $page = 1): array
    {
        $where  = ["p.estado = 'publicado'", "p.oculta = 0"];
        $params = [];

        if (!empty($filtros['id_categoria'])) {
            $where[]  = "p.id_categoria = ?";
            $params[] = (int)$filtros['id_categoria'];
        }
        if (!empty($filtros['id_estado'])) {
            $where[]  = "p.id_estado = ?";
            $params[] = (int)$filtros['id_estado'];
        }
        if (!empty($filtros['id_municipio'])) {
            $where[]  = "p.id_municipio = ?";
            $params[] = (int)$filtros['id_municipio'];
        }
        if (!empty($filtros['buscar'])) {
            $where[]  = "(p.nombre LIKE ? OR p.descripcion LIKE ?)";
            $like     = '%' . $filtros['buscar'] . '%';
            $params[] = $like;
            $params[] = $like;
        }

        $whereSQL = implode(' AND ', $where);
        $perPage  = ITEMS_PER_PAGE;
        $offset   = ($page - 1) * $perPage;

        $total = (int)$this->raw(
            "SELECT COUNT(*) FROM `perfiles` p WHERE {$whereSQL}",
            $params
        )->fetchColumn();

        $rows = $this->raw(
            "SELECT p.*, c.nombre AS categoria_nombre,
                    e.nombre AS estado_nombre,
                    m.nombre AS municipio_nombre,
                    b.has_top       AS boost_top,
                    b.has_resaltado AS boost_resaltado,
                    (SELECT COUNT(*) FROM perfil_fotos  pf WHERE pf.id_perfil = p.id AND pf.es_verificacion = 0 AND pf.oculta = 0) AS fotos_count,
                    (SELECT COUNT(*) FROM perfil_videos pv WHERE pv.id_perfil = p.id AND pv.oculta = 0 AND pv.estado = 'publicado') AS videos_count,
                    (p.zona_lat IS NOT NULL AND p.zona_lng IS NOT NULL) AS tiene_mapa,
                    (SELECT COUNT(*) FROM perfil_comentarios cm WHERE cm.id_perfil = p.id AND cm.estado = 'publicado') AS com_count,
                    (SELECT AVG(calificacion) FROM perfil_comentarios cm WHERE cm.id_perfil = p.id AND cm.estado = 'publicado') AS com_promedio
             FROM perfiles p
             LEFT JOIN (
                 SELECT id_perfil,
                        MAX(IF(tipo='top',       1, 0)) AS has_top,
                        MAX(IF(tipo='resaltado', 1, 0)) AS has_resaltado
                 FROM perfil_boost
                 WHERE estado IN ('programado','activo')
                   AND inicio <= NOW() AND fin > NOW()
                 GROUP BY id_perfil
             ) b ON b.id_perfil = p.id
             LEFT JOIN categorias c ON c.id = p.id_categoria
             LEFT JOIN estados    e ON e.id = p.id_estado
             LEFT JOIN municipios m ON m.id = p.id_municipio
             WHERE {$whereSQL}
             ORDER BY
                IFNULL(b.has_top, 0)       DESC,
                IFNULL(b.has_resaltado, 0) DESC,
                p.fecha_publicacion DESC
             LIMIT ? OFFSET ?",
            array_merge($params, [$perPage, $offset])
        )->fetchAll(PDO::FETCH_ASSOC);

        return [
            'items'   => $rows,
            'total'   => $total,
            'pages'   => (int)ceil($total / $perPage),
            'current' => $page,
            'perPage' => $perPage,
        ];
    }

    /**
     * Perfiles de un usuario (panel propio).
     */
    public function misPerfiles(int $idUsuario): array
    {
        return $this->raw(
            "SELECT p.*, c.nombre AS categoria_nombre,
                    e.nombre AS estado_nombre,
                    m.nombre AS municipio_nombre,
                    (SELECT COUNT(*) FROM perfil_fotos pf WHERE pf.id_perfil = p.id AND pf.es_verificacion = 1) AS fotos_ver,
                    b.has_top       AS boost_top,
                    b.has_resaltado AS boost_resaltado
             FROM perfiles p
             LEFT JOIN (
                 SELECT id_perfil,
                        MAX(IF(tipo='top',       1, 0)) AS has_top,
                        MAX(IF(tipo='resaltado', 1, 0)) AS has_resaltado
                 FROM perfil_boost
                 WHERE estado IN ('programado','activo')
                   AND inicio <= NOW() AND fin > NOW()
                 GROUP BY id_perfil
             ) b ON b.id_perfil = p.id
             LEFT JOIN categorias c ON c.id = p.id_categoria
             LEFT JOIN estados    e ON e.id = p.id_estado
             LEFT JOIN municipios m ON m.id = p.id_municipio
             WHERE p.id_usuario = ?
             ORDER BY p.fecha_creacion DESC",
            [$idUsuario]
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Listado para el admin con filtros y paginación.
     */
    public function listarAdmin(array $filtros = [], int $page = 1): array
    {
        $where  = ['1=1'];
        $params = [];

        if (!empty($filtros['estado'])) {
            $where[]  = "p.estado = ?";
            $params[] = $filtros['estado'];
        }
        if (!empty($filtros['buscar'])) {
            $where[]  = "(p.nombre LIKE ? OR u.email LIKE ?)";
            $like     = '%' . $filtros['buscar'] . '%';
            $params[] = $like;
            $params[] = $like;
        }

        $whereSQL = implode(' AND ', $where);
        $perPage  = 25;
        $offset   = ($page - 1) * $perPage;

        $total = (int)$this->raw(
            "SELECT COUNT(*) FROM `perfiles` p
             LEFT JOIN usuarios u ON u.id = p.id_usuario
             WHERE {$whereSQL}",
            $params
        )->fetchColumn();

        $rows = $this->raw(
            "SELECT p.*, c.nombre AS categoria_nombre,
                    u.nombre AS usuario_nombre, u.email AS usuario_email,
                    e.nombre AS estado_nombre,
                    m.nombre AS municipio_nombre,
                    (SELECT COUNT(*) FROM perfil_fotos pf WHERE pf.id_perfil = p.id AND pf.es_verificacion = 1) AS fotos_ver
             FROM perfiles p
             LEFT JOIN categorias c ON c.id = p.id_categoria
             LEFT JOIN usuarios   u ON u.id = p.id_usuario
             LEFT JOIN estados    e ON e.id = p.id_estado
             LEFT JOIN municipios m ON m.id = p.id_municipio
             WHERE {$whereSQL}
             ORDER BY
               CASE p.estado WHEN 'pendiente' THEN 0 ELSE 1 END,
               p.fecha_creacion DESC
             LIMIT ? OFFSET ?",
            array_merge($params, [$perPage, $offset])
        )->fetchAll(PDO::FETCH_ASSOC);

        return [
            'items'   => $rows,
            'total'   => $total,
            'pages'   => (int)ceil($total / $perPage),
            'current' => $page,
            'perPage' => $perPage,
        ];
    }

    public function estadisticas(): array
    {
        $row = $this->raw(
            "SELECT
                COUNT(*)                           AS total,
                SUM(estado = 'publicado')           AS publicados,
                SUM(estado = 'pendiente')           AS pendientes,
                SUM(estado = 'rechazado')           AS rechazados,
                SUM(destacado = 1)                  AS destacados
             FROM perfiles"
        )->fetch(PDO::FETCH_ASSOC);

        return $row ?: ['total'=>0,'publicados'=>0,'pendientes'=>0,'rechazados'=>0,'destacados'=>0];
    }

    // Desactivar destacados expirados
    public function expirarDestacados(): int
    {
        $stmt = $this->raw(
            "UPDATE `perfiles`
             SET destacado = 0, fecha_expiracion_destacado = NULL
             WHERE destacado = 1
               AND fecha_expiracion_destacado IS NOT NULL
               AND fecha_expiracion_destacado < NOW()"
        );
        return $stmt->rowCount();
    }
}
