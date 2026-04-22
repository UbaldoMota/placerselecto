<?php
/**
 * SoporteMensajeModel.php
 * Mensajes del usuario al administrador (reactivación, soporte, dudas).
 */

require_once APP_PATH . '/Model.php';

class SoporteMensajeModel extends Model
{
    protected string $table      = 'soporte_mensajes';
    protected string $primaryKey = 'id';

    public const TIPOS   = ['reactivacion', 'general', 'duda', 'reporte_problema'];
    public const ESTADOS = ['abierto', 'respondido', 'cerrado'];

    public function crear(array $data): int
    {
        return $this->insert([
            'id_usuario' => (int)$data['id_usuario'],
            'tipo'       => in_array($data['tipo'] ?? 'general', self::TIPOS, true) ? $data['tipo'] : 'general',
            'asunto'     => mb_substr($data['asunto'] ?? '', 0, 150),
            'mensaje'    => mb_substr($data['mensaje'] ?? '', 0, 5000),
            'estado'     => 'abierto',
            'ip_envio'   => $data['ip_envio'] ?? null,
        ]);
    }

    public function responder(int $id, int $idAdmin, string $respuesta): bool
    {
        return $this->raw(
            "UPDATE soporte_mensajes
             SET respuesta_admin   = ?,
                 id_admin_respuesta = ?,
                 fecha_respuesta    = NOW(),
                 estado             = 'respondido'
             WHERE id = ?",
            [mb_substr($respuesta, 0, 5000), $idAdmin, $id]
        )->rowCount() > 0;
    }

    public function cerrar(int $id): bool
    {
        return $this->raw(
            "UPDATE soporte_mensajes SET estado = 'cerrado' WHERE id = ?",
            [$id]
        )->rowCount() > 0;
    }

    /** Última solicitud abierta de un usuario para un tipo (evita spam). */
    public function ultimaAbiertaPorTipo(int $idUsuario, string $tipo): ?array
    {
        $row = $this->raw(
            "SELECT * FROM soporte_mensajes
             WHERE id_usuario = ? AND tipo = ? AND estado IN ('abierto','respondido')
             ORDER BY fecha_creacion DESC
             LIMIT 1",
            [$idUsuario, $tipo]
        )->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /** Listado paginado para el admin. */
    public function listarAdmin(int $page = 1, array $filtros = []): array
    {
        $where  = ['1=1'];
        $params = [];

        if (!empty($filtros['estado']) && in_array($filtros['estado'], self::ESTADOS, true)) {
            $where[]  = "m.estado = ?";
            $params[] = $filtros['estado'];
        }
        if (!empty($filtros['tipo']) && in_array($filtros['tipo'], self::TIPOS, true)) {
            $where[]  = "m.tipo = ?";
            $params[] = $filtros['tipo'];
        }
        if (!empty($filtros['buscar'])) {
            $where[]  = "(m.asunto LIKE ? OR m.mensaje LIKE ? OR u.email LIKE ?)";
            $like     = '%' . $filtros['buscar'] . '%';
            $params[] = $like; $params[] = $like; $params[] = $like;
        }

        $whereSQL = implode(' AND ', $where);
        $perPage  = 20;
        $offset   = (max(1, $page) - 1) * $perPage;

        $total = (int)$this->raw(
            "SELECT COUNT(*) FROM soporte_mensajes m
             LEFT JOIN usuarios u ON u.id = m.id_usuario
             WHERE {$whereSQL}",
            $params
        )->fetchColumn();

        $items = $this->raw(
            "SELECT m.*, u.nombre AS usuario_nombre, u.email AS usuario_email,
                    u.estado_verificacion AS usuario_estado
             FROM soporte_mensajes m
             LEFT JOIN usuarios u ON u.id = m.id_usuario
             WHERE {$whereSQL}
             ORDER BY
                CASE m.estado WHEN 'abierto' THEN 0 WHEN 'respondido' THEN 1 ELSE 2 END,
                m.fecha_creacion DESC
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

    public function contarAbiertos(): int
    {
        return (int)$this->raw(
            "SELECT COUNT(*) FROM soporte_mensajes WHERE estado = 'abierto'"
        )->fetchColumn();
    }
}
