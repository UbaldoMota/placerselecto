<?php
/**
 * ConfiguracionModel.php
 * Configuracion key/value editable desde panel admin (numeros de
 * WhatsApp, emails, textos, limites de almacenamiento, etc.).
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
        $v = $row ? $row['valor'] : null;
        return ($v === null || $v === '') ? $default : $v;
    }

    public function getInt(string $clave, int $default = 0): int
    {
        $v = $this->get($clave);
        return $v !== null ? (int)$v : $default;
    }

    public function set(string $clave, ?string $valor, ?string $desc = null): bool
    {
        $sql = "INSERT INTO configuracion (clave, valor, descripcion) VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE valor = VALUES(valor),
                                        descripcion = COALESCE(VALUES(descripcion), descripcion)";
        return $this->raw($sql, [$clave, $valor, $desc])->rowCount() >= 0;
    }

    /** Lista todos los settings con metadatos (para panel admin). */
    public function getAll(): array
    {
        // Si la columna 'tipo' no existe (migracion vieja), seleccionamos sin ella.
        try {
            return $this->raw(
                "SELECT clave, valor, descripcion, tipo, fecha_actualizacion
                 FROM configuracion ORDER BY clave"
            )->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            $rows = $this->raw(
                "SELECT clave, valor, descripcion, fecha_actualizacion
                 FROM configuracion ORDER BY clave"
            )->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as &$r) { $r['tipo'] = 'texto'; }
            return $rows;
        }
    }
}
