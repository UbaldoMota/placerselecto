<?php
/**
 * FotoModel.php
 * Gestiona la galería de fotos (tabla configurable via $table / $fkCol).
 * Por defecto trabaja con anuncio_fotos / id_anuncio.
 * PerfilFotoModel extiende esta clase cambiando esas dos propiedades.
 */

require_once APP_PATH . '/Model.php';

class FotoModel extends Model
{
    protected string $table      = 'anuncio_fotos';
    protected string $primaryKey = 'id';
    protected string $fkCol      = 'id_anuncio';   // columna FK en la tabla

    // ---------------------------------------------------------
    // LECTURA
    // ---------------------------------------------------------

    /** Todas las fotos de una entidad ordenadas por `orden`. */
    public function porAnuncio(int $idEntidad): array
    {
        return $this->raw(
            "SELECT * FROM `{$this->table}`
             WHERE `{$this->fkCol}` = ?
             ORDER BY `orden` ASC, `id` ASC",
            [$idEntidad]
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Busca una foto por su token (para el proxy). Retorna null si no existe. */
    public function porToken(string $token): ?array
    {
        if (!ctype_xdigit($token) || strlen($token) !== 40) {
            return null;
        }
        $row = $this->raw(
            "SELECT * FROM `{$this->table}` WHERE `token` = ? LIMIT 1",
            [$token]
        )->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    // ---------------------------------------------------------
    // ESCRITURA
    // ---------------------------------------------------------

    /**
     * Guarda una foto y retorna su ID.
     * Genera un token criptográficamente seguro.
     */
    public function guardar(int $idEntidad, string $nombreArchivo, int $orden = 0): int
    {
        $token = bin2hex(random_bytes(20)); // 40 hex chars

        return $this->insert([
            $this->fkCol     => $idEntidad,
            'token'          => $token,
            'nombre_archivo' => $nombreArchivo,
            'orden'          => $orden,
        ]);
    }

    /**
     * Elimina una foto verificando que pertenece a la entidad.
     * También borra el archivo físico.
     */
    public function eliminar(int $id, int $idEntidad): ?string
    {
        $foto = $this->raw(
            "SELECT * FROM `{$this->table}`
             WHERE `id` = ? AND `{$this->fkCol}` = ? LIMIT 1",
            [$id, $idEntidad]
        )->fetch(PDO::FETCH_ASSOC);

        if (!$foto) return null;

        $path = UPLOADS_PATH . '/anuncios/' . basename($foto['nombre_archivo']);
        if (file_exists($path) && is_file($path)) {
            @unlink($path);
        }

        $this->delete($id);
        return $foto['token'];
    }

    /** Elimina todas las fotos de una entidad (al borrarla). */
    public function eliminarPorAnuncio(int $idEntidad): void
    {
        $fotos = $this->porAnuncio($idEntidad);
        foreach ($fotos as $f) {
            $path = UPLOADS_PATH . '/anuncios/' . basename($f['nombre_archivo']);
            if (file_exists($path) && is_file($path)) {
                @unlink($path);
            }
        }
        $this->raw(
            "DELETE FROM `{$this->table}` WHERE `{$this->fkCol}` = ?",
            [$idEntidad]
        );
    }

    /**
     * Renumera el orden (0, 1, 2…).
     * Si se indica $principalId esa foto pasa a orden 0.
     * Retorna el token de la primera foto.
     */
    public function reordenar(int $idEntidad, ?int $principalId = null): ?string
    {
        $fotos = $this->porAnuncio($idEntidad);

        if ($principalId !== null) {
            usort($fotos, fn($a, $b) =>
                ((int)$a['id'] === $principalId ? -1 : 0) -
                ((int)$b['id'] === $principalId ? -1 : 0)
            );
        }

        foreach ($fotos as $i => $f) {
            $this->update((int)$f['id'], ['orden' => $i]);
        }
        return $fotos[0]['token'] ?? null;
    }

    /** Total de fotos de una entidad. */
    public function contar(int $idEntidad): int
    {
        return (int) $this->raw(
            "SELECT COUNT(*) FROM `{$this->table}` WHERE `{$this->fkCol}` = ?",
            [$idEntidad]
        )->fetchColumn();
    }
}
