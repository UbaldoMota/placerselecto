<?php
/**
 * EstadoModel.php
 * Modelo para la tabla `estados`.
 */

require_once APP_PATH . '/Model.php';

class EstadoModel extends Model
{
    protected string $table      = 'estados';
    protected string $primaryKey = 'id';

    /** Retorna todos los estados activos ordenados por nombre. */
    public function activos(): array
    {
        return $this->raw(
            "SELECT `id`, `nombre` FROM `estados` WHERE `activo` = 1 ORDER BY `nombre` ASC"
        )->fetchAll(PDO::FETCH_ASSOC);
    }
}
