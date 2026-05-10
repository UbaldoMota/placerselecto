<?php
/**
 * CuentaEliminacionService
 *
 * Lógica reutilizable para el hard-delete de cuentas que pasaron el grace
 * period de 30 días. Usado por:
 *  - cron/hard_delete_cuentas.php (CLI, opcional)
 *  - AdminController (botón manual en panel admin)
 *
 * Acciones por cuenta elegible:
 *  1. Borra archivos físicos: fotos (+ variantes thumb/medium/webp), videos,
 *     documento de identidad cifrado, video de verificación.
 *  2. Anonimiza pagos (id_usuario = NULL) para retención fiscal.
 *  3. DELETE FROM usuarios — cascadea a perfiles/anuncios/comentarios/notif/
 *     soporte/tokens_movimientos via FK ON DELETE CASCADE.
 */
class CuentaEliminacionService
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /** Cuenta cuántas cuentas ya pasaron el grace period y deben hard-deletarse. */
    public function contarPendientes(): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM usuarios
              WHERE eliminado_at IS NOT NULL
                AND eliminacion_programada_para IS NOT NULL
                AND eliminacion_programada_para < NOW()"
        );
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    /** Lista las cuentas elegibles (id, email, fecha programada). */
    public function listarPendientes(int $limit = 50): array
    {
        $limit = max(1, min(100, $limit));
        $stmt = $this->db->prepare(
            "SELECT id, email, nombre, eliminado_at, eliminacion_programada_para
               FROM usuarios
              WHERE eliminado_at IS NOT NULL
                AND eliminacion_programada_para IS NOT NULL
                AND eliminacion_programada_para < NOW()
              ORDER BY eliminacion_programada_para
              LIMIT {$limit}"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Ejecuta el hard-delete de las cuentas elegibles. Idempotente: errores
     * parciales no abortan el sweep, se acumulan en 'errores'.
     *
     * @return array{ok:int,fail:int,procesadas:int,errores:array<int,string>}
     */
    public function ejecutarPendientes(int $limit = 50): array
    {
        $resultado = ['ok' => 0, 'fail' => 0, 'procesadas' => 0, 'errores' => []];

        $stmt = $this->db->prepare(
            "SELECT id, email, documento_identidad, video_verificacion
               FROM usuarios
              WHERE eliminado_at IS NOT NULL
                AND eliminacion_programada_para IS NOT NULL
                AND eliminacion_programada_para < NOW()
              ORDER BY eliminacion_programada_para
              LIMIT " . max(1, min(100, $limit))
        );
        $stmt->execute();
        $cuentas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($cuentas as $u) {
            $resultado['procesadas']++;
            $idUser = (int)$u['id'];

            try {
                $this->eliminarUsuario($idUser, $u);
                error_log("[CuentaEliminacion] OK user_id={$idUser} email={$u['email']}");
                $resultado['ok']++;
            } catch (\Throwable $e) {
                $msg = "user_id={$idUser}: " . $e->getMessage();
                error_log("[CuentaEliminacion] ERR {$msg}");
                $resultado['fail']++;
                $resultado['errores'][] = $msg;
            }
        }

        return $resultado;
    }

    /** Borra los archivos físicos y registros de un usuario específico. */
    private function eliminarUsuario(int $idUser, array $userRow): void
    {
        // 1. Archivos físicos: recorrer perfiles del usuario
        $perfilesIds = $this->db->prepare("SELECT id FROM perfiles WHERE id_usuario = ?");
        $perfilesIds->execute([$idUser]);
        foreach ($perfilesIds->fetchAll(PDO::FETCH_COLUMN) as $idPerfil) {
            // Fotos del perfil + variantes
            $f = $this->db->prepare("SELECT nombre_archivo FROM perfil_fotos WHERE id_perfil = ?");
            $f->execute([(int)$idPerfil]);
            foreach ($f->fetchAll(PDO::FETCH_COLUMN) as $nombre) {
                $this->borrarFotoConVariantes((string)$nombre);
            }
            // Videos del perfil
            $v = $this->db->prepare("SELECT nombre_archivo FROM perfil_videos WHERE id_perfil = ?");
            $v->execute([(int)$idPerfil]);
            foreach ($v->fetchAll(PDO::FETCH_COLUMN) as $nombre) {
                $this->borrarVideo((string)$nombre);
            }
        }

        // Documento de identidad cifrado
        if (!empty($userRow['documento_identidad'])) {
            $p = UPLOADS_PATH . '/verificaciones/documentos/' . basename($userRow['documento_identidad']);
            if (is_file($p)) @unlink($p);
        }
        // Video de verificación de cara (cuenta-level)
        if (!empty($userRow['video_verificacion'])) {
            $p = UPLOADS_PATH . '/verificaciones/' . basename($userRow['video_verificacion']);
            if (is_file($p)) @unlink($p);
        }

        // 2. Anonimizar pagos (id_usuario = NULL) — retención fiscal
        $this->db->prepare("UPDATE pagos SET id_usuario = NULL WHERE id_usuario = ?")
                 ->execute([$idUser]);

        // 3. DELETE FROM usuarios — cascadea via FKs ON DELETE CASCADE
        $this->db->prepare("DELETE FROM usuarios WHERE id = ?")->execute([$idUser]);
    }

    private function borrarFotoConVariantes(string $filename): void
    {
        $base = UPLOADS_PATH . '/anuncios/';
        $filename = basename($filename);
        if (!file_exists($base . $filename)) return;

        $info = pathinfo($filename);
        $stem = $info['filename'];
        $ext  = $info['extension'] ?? '';

        $candidatos = [
            $filename,
            $stem . '_thumb.'  . $ext,
            $stem . '_thumb.webp',
            $stem . '_medium.' . $ext,
            $stem . '_medium.webp',
            $stem . '.webp',
        ];
        foreach ($candidatos as $f) {
            $p = $base . $f;
            if (is_file($p)) @unlink($p);
        }
    }

    private function borrarVideo(string $filename): void
    {
        $p = UPLOADS_PATH . '/videos/' . basename($filename);
        if (is_file($p)) @unlink($p);
    }
}
