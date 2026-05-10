<?php
/**
 * cron/hard_delete_cuentas.php
 *
 * Borra de forma definitiva las cuentas que pasaron el grace period (30 días)
 * tras solicitar eliminación. Diseñado para ejecutarse 1x al día via cPanel cron.
 *
 *   0 3 * * * /usr/local/bin/php /home/placerse/public_html/cron/hard_delete_cuentas.php >> /home/placerse/public_html/logs/cron_delete.log 2>&1
 *
 * Acciones por cada cuenta elegible (eliminado_at IS NOT NULL AND eliminacion_programada_para < NOW()):
 *  1. Borrar archivos físicos: fotos (+ variantes thumb/medium/webp), videos, documento de identidad cifrado, video de verificación.
 *  2. Anonimizar pagos (id_usuario = NULL) — retención fiscal.
 *  3. DELETE FROM usuarios — cascadea a perfiles/anuncios/comentarios/notif/soporte/tokens_movimientos via FKs.
 *
 * Idempotente: errores parciales no abortan el sweep, se loguean por cuenta.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Crypto.php';

// ---- Logging ----
$LOG = LOGS_PATH . '/cron_delete.log';
function L(string $msg): void {
    global $LOG;
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $msg . "\n";
    @file_put_contents($LOG, $line, FILE_APPEND);
    if (PHP_SAPI === 'cli') echo $line;
}

L('=== sweep iniciado ===');

// ---- Conexión BD ----
try {
    $db = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $db->exec("SET time_zone = '-06:00'"); // Misma TZ que la app
} catch (\Throwable $e) {
    L('FATAL conexion BD: ' . $e->getMessage());
    exit(1);
}

// ---- Helpers de borrado de archivos ----
function deleteFotoFiles(string $filename): int {
    $base = UPLOADS_PATH . '/anuncios/';
    $filename = basename($filename);
    if (!file_exists($base . $filename)) return 0;

    $info = pathinfo($filename);
    $stem = $info['filename'];
    $ext  = $info['extension'] ?? '';

    $borrados = 0;
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
        if (is_file($p) && @unlink($p)) $borrados++;
    }
    return $borrados;
}

function deleteVideoFile(string $filename, string $subdir = 'videos'): bool {
    $p = UPLOADS_PATH . '/' . $subdir . '/' . basename($filename);
    return is_file($p) && @unlink($p);
}

// ---- Buscar cuentas elegibles ----
$stmt = $db->prepare(
    "SELECT id, email, nombre,
            documento_identidad, video_verificacion
       FROM usuarios
      WHERE eliminado_at IS NOT NULL
        AND eliminacion_programada_para IS NOT NULL
        AND eliminacion_programada_para < NOW()
      ORDER BY eliminacion_programada_para
      LIMIT 50"
);
$stmt->execute();
$cuentas = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$cuentas) {
    L('sin cuentas elegibles. fin.');
    exit(0);
}

L('encontradas ' . count($cuentas) . ' cuentas');

$totalOk = 0; $totalFail = 0;

foreach ($cuentas as $u) {
    $idUser = (int)$u['id'];
    $email  = $u['email'];

    L("--- procesando user_id={$idUser} email={$email}");

    try {
        // 1. Borrar archivos físicos ANTES del DELETE (sino perdemos las refs).
        $fotosBorradas  = 0;
        $videosBorrados = 0;

        $perfilesIds = $db->prepare("SELECT id FROM perfiles WHERE id_usuario = ?");
        $perfilesIds->execute([$idUser]);
        foreach ($perfilesIds->fetchAll(PDO::FETCH_COLUMN) as $idPerfil) {
            // Fotos del perfil + variantes
            $f = $db->prepare("SELECT nombre_archivo FROM perfil_fotos WHERE id_perfil = ?");
            $f->execute([(int)$idPerfil]);
            foreach ($f->fetchAll(PDO::FETCH_COLUMN) as $nombre) {
                $fotosBorradas += deleteFotoFiles((string)$nombre);
            }
            // Videos del perfil
            $v = $db->prepare("SELECT nombre_archivo FROM perfil_videos WHERE id_perfil = ?");
            $v->execute([(int)$idPerfil]);
            foreach ($v->fetchAll(PDO::FETCH_COLUMN) as $nombre) {
                if (deleteVideoFile((string)$nombre)) $videosBorrados++;
            }
        }

        // Documento de identidad cifrado
        if (!empty($u['documento_identidad'])) {
            $p = UPLOADS_PATH . '/verificaciones/documentos/' . basename($u['documento_identidad']);
            if (is_file($p)) @unlink($p);
        }
        // Video de verificación de cara (cuenta-level)
        if (!empty($u['video_verificacion'])) {
            $p = UPLOADS_PATH . '/verificaciones/' . basename($u['video_verificacion']);
            if (is_file($p)) @unlink($p);
        }

        // 2. Anonimizar pagos para retención fiscal (id_usuario -> NULL)
        $stmtPay = $db->prepare("UPDATE pagos SET id_usuario = NULL WHERE id_usuario = ?");
        $stmtPay->execute([$idUser]);
        $pagosAnon = $stmtPay->rowCount();

        // 3. DELETE FROM usuarios — el resto cascadea via FK ON DELETE CASCADE
        //    (perfiles, perfil_fotos, perfil_videos, perfil_boost,
        //     anuncios, perfil_comentarios, notificaciones,
        //     soporte_mensajes, tokens_movimientos)
        $del = $db->prepare("DELETE FROM usuarios WHERE id = ?");
        $del->execute([$idUser]);

        L("OK user_id={$idUser} fotos_archivos={$fotosBorradas} videos={$videosBorrados} pagos_anonimizados={$pagosAnon}");
        $totalOk++;
    } catch (\Throwable $e) {
        L("ERR user_id={$idUser}: " . $e->getMessage());
        $totalFail++;
    }
}

L("=== sweep terminado: ok={$totalOk} fail={$totalFail} ===");
exit($totalFail === 0 ? 0 : 1);
