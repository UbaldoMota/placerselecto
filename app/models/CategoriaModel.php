<?php
/**
 * CategoriaModel.php
 * Modelo para la tabla `categorias`.
 */

require_once APP_PATH . '/Model.php';

class CategoriaModel extends Model
{
    protected string $table      = 'categorias';
    protected string $primaryKey = 'id';

    /**
     * Retorna todas las categorías activas ordenadas.
     *
     * @return array<int, array>
     */
    public function activas(): array
    {
        $sql  = "SELECT * FROM `categorias` WHERE `activa` = 1 ORDER BY `orden` ASC, `nombre` ASC";
        $stmt = $this->raw($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca una categoría por slug.
     */
    public function porSlug(string $slug): ?array
    {
        return $this->findBy('slug', $slug);
    }

    /**
     * Retorna categorías con el conteo de anuncios publicados.
     *
     * @return array<int, array>
     */
    public function conConteoAnuncios(): array
    {
        $sql = "SELECT
                    c.*,
                    COUNT(a.id) AS total_anuncios
                FROM `categorias` c
                LEFT JOIN `anuncios` a
                    ON a.id_categoria = c.id AND a.estado = 'publicado'
                WHERE c.activa = 1
                GROUP BY c.id
                ORDER BY c.orden ASC";

        return $this->raw($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retorna categorías con el conteo de perfiles publicados.
     *
     * @return array<int, array>
     */
    public function conConteoPerfiles(): array
    {
        $sql = "SELECT
                    c.*,
                    COUNT(p.id) AS total_perfiles
                FROM `categorias` c
                LEFT JOIN `perfiles` p
                    ON p.id_categoria = c.id AND p.estado = 'publicado'
                WHERE c.activa = 1
                GROUP BY c.id
                ORDER BY c.orden ASC";

        return $this->raw($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}
