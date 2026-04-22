<?php
/**
 * TokenTarifaModel.php
 * Tarifas de consumo de tokens por tipo de boost.
 */

require_once APP_PATH . '/Model.php';

class TokenTarifaModel extends Model
{
    protected string $table      = 'token_tarifas';
    protected string $primaryKey = 'id';

    public const TIPOS = ['top', 'resaltado'];

    /** Retorna ['top' => ['tokens_por_hora' => 3, ...], 'resaltado' => [...]]. */
    public function mapa(): array
    {
        $rows = $this->raw("SELECT * FROM token_tarifas")->fetchAll(PDO::FETCH_ASSOC);
        $map  = [];
        foreach ($rows as $r) $map[$r['tipo']] = $r;
        return $map;
    }

    public function tokensPorHora(string $tipo): int
    {
        $row = $this->raw(
            "SELECT tokens_por_hora FROM token_tarifas WHERE tipo = ? LIMIT 1",
            [$tipo]
        )->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['tokens_por_hora'] : 0;
    }

    public function actualizarPorTipo(string $tipo, int $tokensPorHora, ?string $descripcion = null): bool
    {
        if (!in_array($tipo, self::TIPOS, true)) return false;

        $sql = $descripcion !== null
            ? "UPDATE token_tarifas SET tokens_por_hora = ?, descripcion = ? WHERE tipo = ?"
            : "UPDATE token_tarifas SET tokens_por_hora = ? WHERE tipo = ?";
        $params = $descripcion !== null
            ? [$tokensPorHora, $descripcion, $tipo]
            : [$tokensPorHora, $tipo];

        return $this->raw($sql, $params)->rowCount() >= 0;
    }
}
