<?php
/**
 * BoostModel.php
 * Ventanas de destacado/resaltado por perfil (consumen tokens).
 */

require_once APP_PATH . '/Model.php';

class BoostModel extends Model
{
    protected string $table      = 'perfil_boost';
    protected string $primaryKey = 'id';

    public const TIPOS   = ['top', 'resaltado'];
    public const ESTADOS = ['programado', 'activo', 'finalizado', 'cancelado'];

    /**
     * Crea un boost (sin tocar saldo — el BoostController orquesta con TokenMovimientoModel).
     * Retorna el ID del boost.
     */
    public function crear(array $data): int
    {
        return $this->insert([
            'id_perfil'       => (int)$data['id_perfil'],
            'tipo'            => $data['tipo'],
            'inicio'          => $data['inicio'],
            'fin'             => $data['fin'],
            'tokens_gastados' => max(0, (int)$data['tokens_gastados']),
            'estado'          => $data['estado'] ?? 'programado',
            'es_legacy'       => 0,
        ]);
    }

    /**
     * Actualiza estados "lazy": programado→activo si NOW() >= inicio;
     * activo→finalizado si NOW() > fin.
     * Devuelve filas afectadas.
     */
    public function sincronizarEstados(): int
    {
        $a = $this->raw(
            "UPDATE perfil_boost
             SET estado = 'activo'
             WHERE estado = 'programado' AND inicio <= NOW() AND fin > NOW()"
        )->rowCount();

        $b = $this->raw(
            "UPDATE perfil_boost
             SET estado = 'finalizado'
             WHERE estado IN ('programado','activo') AND fin <= NOW()"
        )->rowCount();

        return $a + $b;
    }

    /**
     * Boost activo actualmente para un perfil (retorna el de mayor prioridad si hay varios).
     * Prioridad: top > resaltado.
     */
    public function activoPorPerfil(int $idPerfil): ?array
    {
        $row = $this->raw(
            "SELECT * FROM perfil_boost
             WHERE id_perfil = ?
               AND estado IN ('programado','activo')
               AND inicio <= NOW() AND fin > NOW()
             ORDER BY FIELD(tipo, 'top', 'resaltado'), fin DESC
             LIMIT 1",
            [$idPerfil]
        )->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    /** Lista los boosts de un perfil (todos los estados) para el panel del usuario. */
    public function porPerfil(int $idPerfil, int $limit = 50): array
    {
        return $this->raw(
            "SELECT * FROM perfil_boost
             WHERE id_perfil = ?
             ORDER BY
                FIELD(estado, 'activo','programado','finalizado','cancelado'),
                inicio DESC
             LIMIT ?",
            [$idPerfil, $limit]
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Boosts del usuario (todos sus perfiles). */
    public function porUsuario(int $idUsuario, string $estadoFiltro = ''): array
    {
        $where  = "p.id_usuario = ?";
        $params = [$idUsuario];

        if ($estadoFiltro !== '' && in_array($estadoFiltro, self::ESTADOS, true)) {
            $where .= " AND b.estado = ?";
            $params[] = $estadoFiltro;
        }

        return $this->raw(
            "SELECT b.*, p.nombre AS perfil_nombre, p.imagen_token
             FROM perfil_boost b
             INNER JOIN perfiles p ON p.id = b.id_perfil
             WHERE {$where}
             ORDER BY b.inicio DESC
             LIMIT 100",
            $params
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cancela un boost.
     * @return array{ok:bool, reembolso_tokens:int, boost:array|null, error?:string}
     */
    public function cancelar(int $id, int $idUsuario): array
    {
        // Validar propiedad
        $row = $this->raw(
            "SELECT b.* FROM perfil_boost b
             INNER JOIN perfiles p ON p.id = b.id_perfil
             WHERE b.id = ? AND p.id_usuario = ?
             LIMIT 1",
            [$id, $idUsuario]
        )->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return ['ok' => false, 'reembolso_tokens' => 0, 'boost' => null, 'error' => 'Boost no encontrado.'];
        }

        if (!in_array($row['estado'], ['programado','activo'], true)) {
            return ['ok' => false, 'reembolso_tokens' => 0, 'boost' => $row, 'error' => 'El boost ya no está vigente.'];
        }

        // Solo se pueden cancelar los programados (según decisión del producto).
        // Los activos no se reembolsan.
        $esProgramado = $row['estado'] === 'programado' && strtotime($row['inicio']) > time();
        $reembolso = $esProgramado ? (int)$row['tokens_gastados'] : 0;

        if (!$esProgramado) {
            return [
                'ok' => false,
                'reembolso_tokens' => 0,
                'boost' => $row,
                'error' => 'Los boosts activos no se pueden cancelar.'
            ];
        }

        $this->raw(
            "UPDATE perfil_boost
             SET estado = 'cancelado', fecha_cancelacion = NOW()
             WHERE id = ?",
            [$id]
        );

        return ['ok' => true, 'reembolso_tokens' => $reembolso, 'boost' => $row];
    }

    /**
     * Verifica si el perfil ya tiene un boost del mismo tipo que solape con el rango propuesto.
     * Usado para prevenir doble-pago por ventanas superpuestas.
     */
    public function haySolapamiento(int $idPerfil, string $tipo, string $inicio, string $fin): bool
    {
        $row = $this->raw(
            "SELECT 1 FROM perfil_boost
             WHERE id_perfil = ?
               AND tipo = ?
               AND estado IN ('programado','activo')
               AND NOT (fin <= ? OR inicio >= ?)
             LIMIT 1",
            [$idPerfil, $tipo, $inicio, $fin]
        )->fetch();
        return (bool)$row;
    }

    /** Estadísticas globales para admin. */
    public function estadisticas(): array
    {
        $row = $this->raw(
            "SELECT
                COUNT(*)                                                AS total,
                SUM(estado = 'activo')                                  AS activos,
                SUM(estado = 'programado')                              AS programados,
                SUM(estado = 'finalizado')                              AS finalizados,
                SUM(estado = 'cancelado')                               AS cancelados,
                COALESCE(SUM(tokens_gastados), 0)                       AS tokens_totales_consumidos
             FROM perfil_boost"
        )->fetch(PDO::FETCH_ASSOC);

        return $row ?: [];
    }

    public function find(int $id): ?array
    {
        return parent::find($id);
    }
}
