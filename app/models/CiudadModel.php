<?php
/**
 * CiudadModel.php
 * Modelo para la tabla `ciudades`.
 */

require_once APP_PATH . '/Model.php';

class CiudadModel extends Model
{
    protected string $table      = 'ciudades';
    protected string $primaryKey = 'id';

    /**
     * Retorna todas las ciudades activas ordenadas por nombre.
     *
     * @return array<int, array>
     */
    public function activas(): array
    {
        $sql  = "SELECT * FROM `ciudades` WHERE `activa` = 1 ORDER BY `nombre` ASC";
        return $this->raw($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retorna ciudades con conteo de anuncios publicados.
     *
     * @return array<int, array>
     */
    public function conConteoAnuncios(): array
    {
        $sql = "SELECT
                    ci.nombre,
                    COUNT(a.id) AS total_anuncios
                FROM `ciudades` ci
                LEFT JOIN `anuncios` a
                    ON a.ciudad = ci.nombre AND a.estado = 'publicado'
                WHERE ci.activa = 1
                GROUP BY ci.id
                HAVING total_anuncios > 0
                ORDER BY total_anuncios DESC, ci.nombre ASC";

        return $this->raw($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}
