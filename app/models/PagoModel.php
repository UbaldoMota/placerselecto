<?php
/**
 * PagoModel.php
 * Modelo para la tabla `pagos`.
 * Gestiona registro de transacciones y planes de destacado.
 */

require_once APP_PATH . '/Model.php';

class PagoModel extends Model
{
    protected string $table      = 'pagos';
    protected string $primaryKey = 'id';

    /**
     * Crea un registro de pago en estado 'pendiente'.
     *
     * @return int ID del pago creado
     */
    public function iniciarPago(array $data): int
    {
        return $this->insert([
            'id_usuario'       => $data['id_usuario'],
            'id_anuncio'       => $data['id_anuncio']       ?? null,
            'id_perfil'        => $data['id_perfil']        ?? null,
            'id_paquete'       => $data['id_paquete']       ?? null,
            'tokens_otorgados' => $data['tokens_otorgados'] ?? null,
            'monto'            => $data['monto'],
            'moneda'           => $data['moneda']           ?? 'MXN',
            'tipo_destacado'   => $data['tipo_destacado']   ?? null,
            'estado'           => 'pendiente',
            'metodo_pago'      => $data['metodo_pago']      ?? 'simulado',
            'ip_pago'          => $data['ip_pago']          ?? null,
            'fecha_creacion'   => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Marca el pago como completado y registra la fecha.
     */
    public function completar(int $id, ?string $referenciaExt = null): bool
    {
        return $this->update($id, [
            'estado'          => 'completado',
            'referencia_ext'  => $referenciaExt,
            'fecha_pago'      => date('Y-m-d H:i:s'),
        ]) > 0;
    }

    /**
     * Marca el pago como fallido.
     */
    public function fallar(int $id): bool
    {
        return $this->update($id, ['estado' => 'fallido']) > 0;
    }

    /**
     * Busca un pago pendiente de un usuario para un anuncio específico.
     */
    public function buscarPendiente(int $idUsuario, int $idAnuncio): ?array
    {
        $sql  = "SELECT * FROM `pagos`
                 WHERE `id_usuario` = ? AND `id_anuncio` = ? AND `estado` = 'pendiente'
                 ORDER BY `fecha_creacion` DESC LIMIT 1";
        $stmt = $this->raw($sql, [$idUsuario, $idAnuncio]);
        $row  = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Historial de pagos de un usuario.
     */
    public function historialUsuario(int $idUsuario, int $page = 1): array
    {
        $perPage = ITEMS_PER_PAGE;
        $offset  = (max(1, $page) - 1) * $perPage;

        $total = $this->count('id_usuario = ?', [$idUsuario]);

        $sql = "SELECT
                    p.*,
                    a.titulo AS anuncio_titulo
                FROM `pagos` p
                LEFT JOIN `anuncios` a ON a.id = p.id_anuncio
                WHERE p.id_usuario = ?
                ORDER BY p.fecha_creacion DESC
                LIMIT ? OFFSET ?";

        $stmt = $this->raw($sql, [$idUsuario, $perPage, $offset]);

        return [
            'items'   => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'total'   => $total,
            'pages'   => (int) ceil($total / $perPage),
            'current' => (int) $page,
        ];
    }

    /**
     * Listado de pagos para el panel admin.
     */
    public function listarAdmin(int $page = 1, string $estado = ''): array
    {
        $where  = [];
        $params = [];

        if ($estado !== '') {
            $where[]  = 'p.estado = ?';
            $params[] = $estado;
        }

        $whereSQL = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        $perPage  = ITEMS_PER_PAGE;
        $offset   = (max(1, $page) - 1) * $perPage;

        $total = (int) $this->raw(
            "SELECT COUNT(*) FROM `pagos` p {$whereSQL}",
            $params
        )->fetchColumn();

        $sql = "SELECT
                    p.*,
                    u.nombre  AS usuario_nombre,
                    u.email   AS usuario_email,
                    a.titulo  AS anuncio_titulo
                FROM `pagos` p
                LEFT JOIN `usuarios` u ON u.id = p.id_usuario
                LEFT JOIN `anuncios` a ON a.id = p.id_anuncio
                {$whereSQL}
                ORDER BY p.fecha_creacion DESC
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
     * Total recaudado (pagos completados).
     */
    public function totalRecaudado(): float
    {
        $sql = "SELECT COALESCE(SUM(monto), 0) FROM `pagos` WHERE `estado` = 'completado'";
        return (float) $this->raw($sql)->fetchColumn();
    }

    /**
     * Estadísticas para dashboard admin.
     */
    public function estadisticas(): array
    {
        $sql = "SELECT
                    COUNT(*)                              AS total,
                    SUM(estado = 'completado')            AS completados,
                    SUM(estado = 'pendiente')             AS pendientes,
                    SUM(estado = 'fallido')               AS fallidos,
                    COALESCE(SUM(CASE WHEN estado = 'completado' THEN monto END), 0) AS total_recaudado
                FROM `pagos`";

        return $this->raw($sql)->fetch(PDO::FETCH_ASSOC);
    }
}
