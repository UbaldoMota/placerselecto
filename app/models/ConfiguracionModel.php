<?php
/**
 * ConfiguracionModel.php
 * Key/value simple para settings del admin.
 */

require_once APP_PATH . '/Model.php';

class ConfiguracionModel extends Model
{
    protected string $table      = 'configuracion';
    protected string $primaryKey = 'clave';

    public function get(string $clave, $default = null): ?string
    {
        $row = $this->raw("SELECT valor FROM configuracion WHERE clave = ? LIMIT 1", [$clave])
                    ->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['valor'] : $default;
    }

    public function getInt(string $clave, int $default = 0): int
    {
        $v = $this->get($clave);
        return $v !== null ? (int)$v : $default;
    }

    public function set(string $clave, string $valor, ?string $desc = null): bool
    {
        $sql = "INSERT INTO configuracion (clave, valor, descripcion) VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE valor = VALUES(valor),
                                        descripcion = COALESCE(VALUES(descripcion), descripcion)";
        return $this->raw($sql, [$clave, $valor, $desc])->rowCount() >= 0;
    }
}
