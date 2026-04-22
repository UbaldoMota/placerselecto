<?php
/**
 * PerfilFotoModel.php
 * Galería de fotos de perfiles (tabla perfil_fotos).
 * Extiende FotoModel cambiando solo la tabla y la FK.
 */

require_once APP_PATH . '/models/FotoModel.php';

class PerfilFotoModel extends FotoModel
{
    protected string $table  = 'perfil_fotos';
    protected string $fkCol  = 'id_perfil';

    /**
     * Fotos públicas del perfil: excluye las de verificación (es_verificacion = 1).
     * Usar en vistas públicas y del usuario. NUNCA mostrar fotos de verificación públicamente.
     */
    public function galeria(int $idPerfil): array
    {
        return $this->raw(
            "SELECT * FROM `perfil_fotos`
             WHERE `id_perfil` = ? AND `es_verificacion` = 0 AND `oculta` = 0
             ORDER BY `orden` ASC, `id` ASC",
            [$idPerfil]
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Todas las fotos de galería, incluyendo ocultas. Solo para el admin.
     */
    public function galeriaAdmin(int $idPerfil): array
    {
        return $this->raw(
            "SELECT * FROM `perfil_fotos`
             WHERE `id_perfil` = ? AND `es_verificacion` = 0
             ORDER BY `orden` ASC, `id` ASC",
            [$idPerfil]
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Alterna el estado oculta (0→1, 1→0). Devuelve true si se modificó.
     */
    public function toggleOculta(int $id): bool
    {
        return $this->raw(
            "UPDATE `perfil_fotos` SET `oculta` = 1 - `oculta` WHERE `id` = ?",
            [$id]
        )->rowCount() > 0;
    }

    /**
     * Fotos de verificación. Solo para uso del administrador.
     */
    public function verificacion(int $idPerfil): array
    {
        return $this->raw(
            "SELECT * FROM `perfil_fotos`
             WHERE `id_perfil` = ? AND `es_verificacion` = 1
             ORDER BY `id` ASC",
            [$idPerfil]
        )->fetchAll(PDO::FETCH_ASSOC);
    }
}
