<?php
/**
 * NotificacionModel.php
 * Notificaciones in-app (campanita) por usuario.
 */

require_once APP_PATH . '/Model.php';

class NotificacionModel extends Model
{
    protected string $table      = 'notificaciones';
    protected string $primaryKey = 'id';

    public const MAX_DROPDOWN = 10;

    /**
     * Inserta una notificación para un usuario concreto.
     */
    public function crear(int $idUsuario, array $data): int
    {
        return $this->insert([
            'id_usuario' => $idUsuario,
            'tipo'       => $data['tipo']    ?? 'info',
            'titulo'     => mb_substr($data['titulo']  ?? '', 0, 150),
            'mensaje'    => mb_substr($data['mensaje'] ?? '', 0, 500),
            'url'        => !empty($data['url']) ? mb_substr($data['url'], 0, 300) : null,
            'icono'      => $data['icono'] ?? 'bell',
            'color'      => $data['color'] ?? 'primary',
        ]);
    }

    /**
     * Inserta la misma notificación para todos los administradores.
     */
    public function crearParaAdmins(array $data): int
    {
        $ids = $this->raw("SELECT id FROM usuarios WHERE rol = 'admin'")
                    ->fetchAll(PDO::FETCH_COLUMN);

        $n = 0;
        foreach ($ids as $id) {
            $this->crear((int)$id, $data);
            $n++;
        }
        return $n;
    }

    /**
     * Datos ligeros para el polling: count no-leídas + últimas para dropdown + hash.
     * @return array{count:int, items:array, hash:string, last_id:int}
     */
    public function paraPolling(int $idUsuario): array
    {
        $count = (int)$this->raw(
            "SELECT COUNT(*) FROM notificaciones WHERE id_usuario = ? AND leida = 0",
            [$idUsuario]
        )->fetchColumn();

        $items = $this->raw(
            "SELECT id, tipo, titulo, mensaje, url, icono, color, leida, fecha_creacion
             FROM notificaciones
             WHERE id_usuario = ?
             ORDER BY fecha_creacion DESC, id DESC
             LIMIT " . self::MAX_DROPDOWN,
            [$idUsuario]
        )->fetchAll(PDO::FETCH_ASSOC);

        $lastId = $items[0]['id'] ?? 0;
        $hash   = sha1($count . '|' . $lastId . '|' . ($items[0]['leida'] ?? ''));

        return [
            'count'   => $count,
            'items'   => $items,
            'hash'    => $hash,
            'last_id' => (int)$lastId,
        ];
    }

    /**
     * Marca una notificación como leída (solo si pertenece al usuario).
     */
    public function marcarLeida(int $id, int $idUsuario): bool
    {
        $stmt = $this->raw(
            "UPDATE notificaciones
             SET leida = 1, fecha_lectura = NOW()
             WHERE id = ? AND id_usuario = ? AND leida = 0",
            [$id, $idUsuario]
        );
        return $stmt->rowCount() > 0;
    }

    public function marcarTodasLeidas(int $idUsuario): int
    {
        $stmt = $this->raw(
            "UPDATE notificaciones
             SET leida = 1, fecha_lectura = NOW()
             WHERE id_usuario = ? AND leida = 0",
            [$idUsuario]
        );
        return $stmt->rowCount();
    }

    /**
     * Historial paginado del usuario.
     */
    public function historial(int $idUsuario, int $page = 1, int $perPage = 20): array
    {
        $page   = max(1, $page);
        $offset = ($page - 1) * $perPage;

        $total = (int)$this->raw(
            "SELECT COUNT(*) FROM notificaciones WHERE id_usuario = ?",
            [$idUsuario]
        )->fetchColumn();

        $items = $this->raw(
            "SELECT * FROM notificaciones
             WHERE id_usuario = ?
             ORDER BY fecha_creacion DESC, id DESC
             LIMIT ? OFFSET ?",
            [$idUsuario, $perPage, $offset]
        )->fetchAll(PDO::FETCH_ASSOC);

        return [
            'items'   => $items,
            'total'   => $total,
            'pages'   => (int)ceil($total / $perPage),
            'current' => $page,
            'perPage' => $perPage,
        ];
    }

    public function eliminar(int $id, int $idUsuario): bool
    {
        $stmt = $this->raw(
            "DELETE FROM notificaciones WHERE id = ? AND id_usuario = ?",
            [$id, $idUsuario]
        );
        return $stmt->rowCount() > 0;
    }
}
