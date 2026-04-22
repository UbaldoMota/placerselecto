<?php
/**
 * MunicipioModel.php
 * Modelo para la tabla `municipios`.
 */

require_once APP_PATH . '/Model.php';

class MunicipioModel extends Model
{
    protected string $table      = 'municipios';
    protected string $primaryKey = 'id';

    /**
     * Retorna todos los municipios activos de un estado, ordenados por nombre.
     *
     * @param int $idEstado
     * @return array<int, array{id: int, nombre: string}>
     */
    public function porEstado(int $idEstado): array
    {
        return $this->raw(
            "SELECT `id`, `nombre` FROM `municipios`
             WHERE `id_estado` = ? AND `activo` = 1
             ORDER BY `nombre` ASC",
            [$idEstado]
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca el id_estado de un municipio por nombre exacto.
     * Retorna null si no se encuentra.
     */
    public function estadoPorNombre(string $nombre): ?int
    {
        $row = $this->raw(
            "SELECT `id_estado` FROM `municipios` WHERE `nombre` = ? LIMIT 1",
            [$nombre]
        )->fetch(PDO::FETCH_ASSOC);

        return $row ? (int) $row['id_estado'] : null;
    }
}
