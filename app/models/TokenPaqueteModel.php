<?php
/**
 * TokenPaqueteModel.php
 * Paquetes de recarga de tokens (admin-editables).
 */

require_once APP_PATH . '/Model.php';

class TokenPaqueteModel extends Model
{
    protected string $table      = 'token_paquetes';
    protected string $primaryKey = 'id';

    /** Paquetes activos (para mostrar al usuario al comprar). */
    public function activos(): array
    {
        return $this->raw(
            "SELECT * FROM token_paquetes
             WHERE activo = 1
             ORDER BY orden ASC, monto_mxn ASC"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Todos (para admin, incluye desactivados). */
    public function todos(): array
    {
        return $this->raw(
            "SELECT * FROM token_paquetes
             ORDER BY orden ASC, id ASC"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crear(array $data): int
    {
        return $this->insert([
            'nombre'    => mb_substr($data['nombre'] ?? '', 0, 80),
            'monto_mxn' => (float)($data['monto_mxn'] ?? 0),
            'tokens'    => max(0, (int)($data['tokens'] ?? 0)),
            'bonus_pct' => max(0, min(200, (int)($data['bonus_pct'] ?? 0))),
            'orden'     => (int)($data['orden'] ?? 0),
            'activo'    => !empty($data['activo']) ? 1 : 0,
        ]);
    }

    public function editar(int $id, array $data): bool
    {
        $allowed = ['nombre','monto_mxn','tokens','bonus_pct','orden','activo'];
        $filtered = array_filter(
            $data,
            fn($k) => in_array($k, $allowed, true),
            ARRAY_FILTER_USE_KEY
        );
        if (empty($filtered)) return false;

        if (isset($filtered['tokens']))    $filtered['tokens']    = max(0, (int)$filtered['tokens']);
        if (isset($filtered['bonus_pct'])) $filtered['bonus_pct'] = max(0, min(200, (int)$filtered['bonus_pct']));
        if (isset($filtered['activo']))    $filtered['activo']    = $filtered['activo'] ? 1 : 0;

        return $this->update($id, $filtered) >= 0;
    }

    public function toggleActivo(int $id): bool
    {
        return $this->raw(
            "UPDATE token_paquetes SET activo = 1 - activo WHERE id = ?",
            [$id]
        )->rowCount() > 0;
    }
}
