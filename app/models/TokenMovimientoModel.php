<?php
/**
 * TokenMovimientoModel.php
 * Ledger de movimientos de tokens y operaciones atómicas sobre saldo_tokens.
 *
 * IMPORTANTE: toda operación que cambie el saldo debe hacerse por aquí
 * para mantener ledger y saldo sincronizados.
 */

require_once APP_PATH . '/Model.php';

class TokenMovimientoModel extends Model
{
    protected string $table      = 'tokens_movimientos';
    protected string $primaryKey = 'id';

    public const TIPOS = ['recarga', 'consumo', 'reembolso', 'ajuste_admin'];

    /**
     * Aplica un movimiento atómico: actualiza saldo del usuario e inserta ledger.
     * Usa SELECT ... FOR UPDATE para prevenir race conditions.
     *
     * @param string $tipo       'recarga'|'consumo'|'reembolso'|'ajuste_admin'
     * @param int    $cantidad   signed: positivo para recarga/reembolso/ajuste+, negativo para consumo/ajuste-
     * @return array{ok:bool, saldo_despues:int, error?:string, movimiento_id?:int}
     */
    public function aplicar(
        int    $idUsuario,
        string $tipo,
        int    $cantidad,
        ?int   $idPago = null,
        ?int   $idBoost = null,
        ?string $descripcion = null
    ): array {
        if (!in_array($tipo, self::TIPOS, true)) {
            return ['ok' => false, 'saldo_despues' => 0, 'error' => 'Tipo de movimiento inválido.'];
        }
        if ($cantidad === 0) {
            return ['ok' => false, 'saldo_despues' => 0, 'error' => 'La cantidad no puede ser cero.'];
        }

        $db = $this->db;
        $transaction = !$db->inTransaction();
        if ($transaction) $db->beginTransaction();

        try {
            $stmt = $db->prepare("SELECT saldo_tokens FROM usuarios WHERE id = ? FOR UPDATE");
            $stmt->execute([$idUsuario]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                if ($transaction) $db->rollBack();
                return ['ok' => false, 'saldo_despues' => 0, 'error' => 'Usuario no encontrado.'];
            }

            $saldoActual = (int)$row['saldo_tokens'];
            $nuevoSaldo  = $saldoActual + $cantidad;

            if ($nuevoSaldo < 0) {
                if ($transaction) $db->rollBack();
                return ['ok' => false, 'saldo_despues' => $saldoActual, 'error' => 'Saldo insuficiente.'];
            }

            // Actualizar saldo
            $db->prepare("UPDATE usuarios SET saldo_tokens = ? WHERE id = ?")
               ->execute([$nuevoSaldo, $idUsuario]);

            // Registrar en ledger
            $ins = $db->prepare(
                "INSERT INTO tokens_movimientos
                    (id_usuario, tipo, cantidad, saldo_despues, id_pago, id_boost, descripcion)
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            $ins->execute([
                $idUsuario, $tipo, $cantidad, $nuevoSaldo,
                $idPago, $idBoost, $descripcion ? mb_substr($descripcion, 0, 255) : null,
            ]);
            $movId = (int)$db->lastInsertId();

            if ($transaction) $db->commit();

            return ['ok' => true, 'saldo_despues' => $nuevoSaldo, 'movimiento_id' => $movId];

        } catch (\Throwable $e) {
            if ($transaction && $db->inTransaction()) $db->rollBack();
            error_log('[TokenMovimiento::aplicar] ' . $e->getMessage());
            return ['ok' => false, 'saldo_despues' => 0, 'error' => 'Error interno.'];
        }
    }

    /** Saldo actual de un usuario. */
    public function saldo(int $idUsuario): int
    {
        return (int)$this->raw(
            "SELECT saldo_tokens FROM usuarios WHERE id = ?",
            [$idUsuario]
        )->fetchColumn();
    }

    /** Historial paginado del usuario. */
    public function historial(int $idUsuario, int $page = 1, int $perPage = 20): array
    {
        $page   = max(1, $page);
        $offset = ($page - 1) * $perPage;

        $total = (int)$this->raw(
            "SELECT COUNT(*) FROM tokens_movimientos WHERE id_usuario = ?",
            [$idUsuario]
        )->fetchColumn();

        $items = $this->raw(
            "SELECT * FROM tokens_movimientos
             WHERE id_usuario = ?
             ORDER BY fecha DESC, id DESC
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

    /** Para admin: historial global con filtros. */
    public function historialAdmin(int $page = 1, int $perPage = 30, array $filtros = []): array
    {
        $where  = ['1=1'];
        $params = [];

        if (!empty($filtros['id_usuario'])) {
            $where[]  = "m.id_usuario = ?";
            $params[] = (int)$filtros['id_usuario'];
        }
        if (!empty($filtros['tipo']) && in_array($filtros['tipo'], self::TIPOS, true)) {
            $where[]  = "m.tipo = ?";
            $params[] = $filtros['tipo'];
        }
        if (!empty($filtros['email'])) {
            $where[]  = "u.email LIKE ?";
            $params[] = '%' . $filtros['email'] . '%';
        }

        $whereSQL = implode(' AND ', $where);
        $page     = max(1, $page);
        $offset   = ($page - 1) * $perPage;

        $total = (int)$this->raw(
            "SELECT COUNT(*) FROM tokens_movimientos m
             LEFT JOIN usuarios u ON u.id = m.id_usuario
             WHERE {$whereSQL}",
            $params
        )->fetchColumn();

        $items = $this->raw(
            "SELECT m.*, u.email AS usuario_email, u.nombre AS usuario_nombre
             FROM tokens_movimientos m
             LEFT JOIN usuarios u ON u.id = m.id_usuario
             WHERE {$whereSQL}
             ORDER BY m.fecha DESC, m.id DESC
             LIMIT ? OFFSET ?",
            array_merge($params, [$perPage, $offset])
        )->fetchAll(PDO::FETCH_ASSOC);

        return [
            'items'   => $items,
            'total'   => $total,
            'pages'   => (int)ceil($total / $perPage),
            'current' => $page,
            'perPage' => $perPage,
        ];
    }

    /** Resumen global para dashboard admin. */
    public function estadisticas(): array
    {
        $row = $this->raw(
            "SELECT
                COALESCE(SUM(CASE WHEN tipo = 'recarga'     THEN cantidad ELSE 0 END), 0) AS total_recargado,
                COALESCE(SUM(CASE WHEN tipo = 'consumo'     THEN -cantidad ELSE 0 END), 0) AS total_consumido,
                COALESCE(SUM(CASE WHEN tipo = 'reembolso'   THEN cantidad ELSE 0 END), 0) AS total_reembolsado,
                COUNT(DISTINCT CASE WHEN tipo = 'recarga' THEN id_usuario END)            AS usuarios_con_recarga
             FROM tokens_movimientos"
        )->fetch(PDO::FETCH_ASSOC);

        $saldo = (int)$this->raw(
            "SELECT COALESCE(SUM(saldo_tokens), 0) FROM usuarios"
        )->fetchColumn();

        return array_merge($row ?: [], ['saldo_total_circulante' => $saldo]);
    }
}
