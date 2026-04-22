<?php
/**
 * PerfilComentarioModel.php
 * Comentarios + calificaciones de comentaristas en perfiles publicados.
 */

require_once APP_PATH . '/Model.php';

class PerfilComentarioModel extends Model
{
    protected string $table      = 'perfil_comentarios';
    protected string $primaryKey = 'id';

    public const COOLDOWN_DIAS = 7;

    /**
     * Revisa si el usuario puede comentar en un perfil.
     * @return array ['puede' => bool, 'motivo' => string|null, 'segundos_restantes' => int]
     */
    public function puedeComentar(int $idPerfil, int $idUsuario): array
    {
        $row = $this->raw(
            "SELECT estado, fecha_cooldown_hasta FROM perfil_comentarios
             WHERE id_perfil = ? AND id_usuario = ? LIMIT 1",
            [$idPerfil, $idUsuario]
        )->fetch(PDO::FETCH_ASSOC);

        if (!$row) return ['puede' => true, 'motivo' => null, 'segundos_restantes' => 0];

        // Ya hay comentario vigente (pendiente/publicado/reportado/oculto)
        if (in_array($row['estado'], ['pendiente','publicado','reportado','oculto'], true)) {
            return [
                'puede'  => false,
                'motivo' => 'Ya tienes un comentario en este perfil. Elimínalo para enviar uno nuevo (tras eliminar deberás esperar 7 días).',
                'segundos_restantes' => 0,
            ];
        }

        // Estado 'eliminado' — revisar cooldown
        if ($row['estado'] === 'eliminado' && !empty($row['fecha_cooldown_hasta'])) {
            $restante = strtotime($row['fecha_cooldown_hasta']) - time();
            if ($restante > 0) {
                return [
                    'puede' => false,
                    'motivo' => 'Debes esperar ' . self::formatearRestante($restante) . ' antes de volver a comentar este perfil.',
                    'segundos_restantes' => $restante,
                ];
            }
        }

        return ['puede' => true, 'motivo' => null, 'segundos_restantes' => 0];
    }

    private static function formatearRestante(int $seg): string
    {
        if ($seg >= 86400) {
            $d = (int)ceil($seg / 86400);
            return $d . ' día' . ($d > 1 ? 's' : '');
        }
        $h = (int)ceil($seg / 3600);
        return $h . ' hora' . ($h > 1 ? 's' : '');
    }

    /** Crea el comentario del usuario. Respeta cooldown y limpia el registro eliminado si aplica. */
    public function guardar(int $idPerfil, int $idUsuario, int $calificacion, string $comentario, ?string $ip = null): int
    {
        $calificacion = max(1, min(5, $calificacion));
        $comentario   = mb_substr(trim($comentario), 0, 2000);

        // Si hay un registro 'eliminado' con cooldown expirado, lo purgamos para insertar uno nuevo
        $this->raw(
            "DELETE FROM perfil_comentarios
             WHERE id_perfil = ? AND id_usuario = ?
               AND estado = 'eliminado'
               AND (fecha_cooldown_hasta IS NULL OR fecha_cooldown_hasta <= NOW())",
            [$idPerfil, $idUsuario]
        );

        return $this->insert([
            'id_perfil'    => $idPerfil,
            'id_usuario'   => $idUsuario,
            'calificacion' => $calificacion,
            'comentario'   => $comentario,
            'ip_autor'     => $ip,
        ]);
    }

    /** Comentario del usuario en el perfil (si existe). */
    public function miComentario(int $idPerfil, int $idUsuario): ?array
    {
        $row = $this->raw(
            "SELECT * FROM perfil_comentarios
             WHERE id_perfil = ? AND id_usuario = ?
             LIMIT 1",
            [$idPerfil, $idUsuario]
        )->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /** Comentarios visibles del perfil, con datos del autor. */
    public function porPerfil(int $idPerfil, int $limit = 20, int $offset = 0): array
    {
        return $this->raw(
            "SELECT c.*, u.nombre AS autor_nombre
             FROM perfil_comentarios c
             INNER JOIN usuarios u ON u.id = c.id_usuario
             WHERE c.id_perfil = ? AND c.estado = 'publicado'
             ORDER BY c.fecha_creacion DESC
             LIMIT ? OFFSET ?",
            [$idPerfil, $limit, $offset]
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function contar(int $idPerfil, string $estado = 'publicado'): int
    {
        return (int)$this->raw(
            "SELECT COUNT(*) FROM perfil_comentarios WHERE id_perfil = ? AND estado = ?",
            [$idPerfil, $estado]
        )->fetchColumn();
    }

    /** Promedio de estrellas para un perfil (0 si sin comentarios). */
    public function promedio(int $idPerfil): array
    {
        $row = $this->raw(
            "SELECT COUNT(*) AS total, AVG(calificacion) AS promedio
             FROM perfil_comentarios
             WHERE id_perfil = ? AND estado = 'publicado'",
            [$idPerfil]
        )->fetch(PDO::FETCH_ASSOC);
        // porPerfil() ya filtra publicados; estadisticas incluye pendientes

        return [
            'total'    => (int)($row['total']    ?? 0),
            'promedio' => round((float)($row['promedio'] ?? 0), 1),
        ];
    }

    /**
     * "Elimina" (soft) el comentario del usuario: marca como 'eliminado' y fija cooldown de 7 días.
     * El registro se conserva hasta que expire el cooldown para bloquear spam.
     */
    public function eliminarPropio(int $id, int $idUsuario): bool
    {
        return $this->raw(
            "UPDATE perfil_comentarios
             SET estado = 'eliminado',
                 fecha_cooldown_hasta = DATE_ADD(NOW(), INTERVAL " . self::COOLDOWN_DIAS . " DAY)
             WHERE id = ? AND id_usuario = ?",
            [$id, $idUsuario]
        )->rowCount() > 0;
    }

    public function reportar(int $id): bool
    {
        return $this->raw(
            "UPDATE perfil_comentarios SET estado = 'reportado' WHERE id = ? AND estado = 'publicado'",
            [$id]
        )->rowCount() > 0;
    }

    // ---------- Admin ----------

    public function listarAdmin(int $page = 1, array $filtros = []): array
    {
        $where = ['1=1']; $params = [];
        if (!empty($filtros['estado'])) {
            $where[]  = "c.estado = ?";
            $params[] = $filtros['estado'];
        }
        if (!empty($filtros['buscar'])) {
            $where[]  = "(c.comentario LIKE ? OR u.email LIKE ? OR p.nombre LIKE ?)";
            $like = '%' . $filtros['buscar'] . '%';
            $params[] = $like; $params[] = $like; $params[] = $like;
        }
        $whereSQL = implode(' AND ', $where);
        $perPage = 25;
        $offset  = (max(1, $page) - 1) * $perPage;

        $total = (int)$this->raw(
            "SELECT COUNT(*) FROM perfil_comentarios c
             INNER JOIN usuarios u ON u.id = c.id_usuario
             INNER JOIN perfiles p ON p.id = c.id_perfil
             WHERE {$whereSQL}",
            $params
        )->fetchColumn();

        $items = $this->raw(
            "SELECT c.*, u.nombre AS autor_nombre, u.email AS autor_email,
                    p.nombre AS perfil_nombre, p.imagen_token AS perfil_imagen,
                    p.ciudad AS perfil_ciudad, p.estado AS perfil_estado,
                    cat.nombre AS perfil_categoria,
                    mun.nombre AS perfil_municipio
             FROM perfil_comentarios c
             INNER JOIN usuarios u ON u.id = c.id_usuario
             INNER JOIN perfiles p ON p.id = c.id_perfil
             LEFT JOIN categorias cat ON cat.id = p.id_categoria
             LEFT JOIN municipios mun ON mun.id = p.id_municipio
             WHERE {$whereSQL}
             ORDER BY
                CASE c.estado WHEN 'pendiente' THEN 0 WHEN 'reportado' THEN 1 WHEN 'publicado' THEN 2 ELSE 3 END,
                c.fecha_creacion DESC
             LIMIT ? OFFSET ?",
            array_merge($params, [$perPage, $offset])
        )->fetchAll(PDO::FETCH_ASSOC);

        return [
            'items'   => $items,
            'total'   => $total,
            'pages'   => (int)ceil($total / $perPage),
            'current' => max(1, $page),
            'perPage' => $perPage,
        ];
    }

    public function setEstado(int $id, string $estado): bool
    {
        if (!in_array($estado, ['pendiente','publicado','oculto','reportado','eliminado'], true)) return false;
        $extra = $estado === 'publicado' ? ', fecha_aprobacion = IFNULL(fecha_aprobacion, NOW())' : '';
        return $this->raw(
            "UPDATE perfil_comentarios SET estado = ? {$extra} WHERE id = ?",
            [$estado, $id]
        )->rowCount() > 0;
    }

    public function eliminar(int $id): bool
    {
        return $this->raw("DELETE FROM perfil_comentarios WHERE id = ?", [$id])->rowCount() > 0;
    }

    public function estadisticas(): array
    {
        $row = $this->raw(
            "SELECT
                COUNT(*) AS total,
                SUM(estado='pendiente') AS pendientes,
                SUM(estado='publicado') AS publicados,
                SUM(estado='reportado') AS reportados,
                SUM(estado='oculto')    AS ocultos
             FROM perfil_comentarios"
        )->fetch(PDO::FETCH_ASSOC);
        return $row ?: [];
    }
}
