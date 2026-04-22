<?php
/**
 * PerfilVideoModel.php
 * Videos públicos del perfil (hasta 3 por perfil).
 * Los archivos se sirven por proxy /video/{token} — nunca directo.
 */

require_once APP_PATH . '/Model.php';

class PerfilVideoModel extends Model
{
    protected string $table      = 'perfil_videos';
    protected string $primaryKey = 'id';

    public const MAX_POR_PERFIL = 3;
    public const MAX_BYTES      = 52428800; // 50 MB
    public const ALLOWED_MIME   = ['video/mp4', 'video/webm', 'video/quicktime'];

    public function contar(int $idPerfil): int
    {
        return (int)$this->raw(
            "SELECT COUNT(*) FROM perfil_videos WHERE id_perfil = ?",
            [$idPerfil]
        )->fetchColumn();
    }

    /** Videos públicos (aprobados y no ocultos). */
    public function listar(int $idPerfil): array
    {
        return $this->raw(
            "SELECT * FROM perfil_videos
             WHERE id_perfil = ? AND oculta = 0 AND estado = 'publicado'
             ORDER BY orden ASC, id ASC",
            [$idPerfil]
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Todos los videos del perfil para vista admin (cualquier estado). */
    public function listarAdmin(int $idPerfil): array
    {
        return $this->raw(
            "SELECT * FROM perfil_videos
             WHERE id_perfil = ?
             ORDER BY estado = 'pendiente' DESC, orden ASC, id ASC",
            [$idPerfil]
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Cambia estado del video. Si 'rechazado', puede guardar motivo. */
    public function setEstado(int $id, string $estado, ?string $motivo = null): bool
    {
        if (!in_array($estado, ['pendiente','publicado','rechazado'], true)) return false;
        return $this->raw(
            "UPDATE perfil_videos SET estado = ?, motivo_rechazo = ? WHERE id = ?",
            [$estado, $estado === 'rechazado' ? $motivo : null, $id]
        )->rowCount() > 0;
    }

    public function find(int $id): ?array
    {
        return parent::find($id);
    }

    public function estadisticas(): array
    {
        $row = $this->raw(
            "SELECT
                COUNT(*) AS total,
                SUM(estado='pendiente') AS pendientes,
                SUM(estado='publicado') AS publicados,
                SUM(estado='rechazado') AS rechazados
             FROM perfil_videos"
        )->fetch(PDO::FETCH_ASSOC);
        return $row ?: [];
    }

    public function porToken(string $token): ?array
    {
        $row = $this->raw(
            "SELECT v.*, p.id_usuario
             FROM perfil_videos v
             INNER JOIN perfiles p ON p.id = v.id_perfil
             WHERE v.token = ?
             LIMIT 1",
            [$token]
        )->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function guardar(int $idPerfil, string $filename, int $orden = 0, ?int $size = null): int
    {
        $token = bin2hex(random_bytes(20));
        return $this->insert([
            'id_perfil'      => $idPerfil,
            'token'          => $token,
            'nombre_archivo' => $filename,
            'orden'          => $orden,
            'tamano_bytes'   => $size,
        ]);
    }

    public function eliminar(int $idVideo, int $idPerfil): bool
    {
        $row = $this->raw(
            "SELECT nombre_archivo FROM perfil_videos WHERE id = ? AND id_perfil = ? LIMIT 1",
            [$idVideo, $idPerfil]
        )->fetch(PDO::FETCH_ASSOC);

        if (!$row) return false;

        $path = UPLOADS_PATH . '/videos/' . basename($row['nombre_archivo']);
        if (file_exists($path) && is_file($path)) @unlink($path);

        return $this->raw(
            "DELETE FROM perfil_videos WHERE id = ? AND id_perfil = ?",
            [$idVideo, $idPerfil]
        )->rowCount() > 0;
    }

    public function eliminarTodos(int $idPerfil): int
    {
        $rows = $this->listar($idPerfil);
        foreach ($rows as $r) {
            $path = UPLOADS_PATH . '/videos/' . basename($r['nombre_archivo']);
            if (file_exists($path) && is_file($path)) @unlink($path);
        }
        return $this->raw(
            "DELETE FROM perfil_videos WHERE id_perfil = ?",
            [$idPerfil]
        )->rowCount();
    }
}
