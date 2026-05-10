<?php
/**
 * cron/hard_delete_cuentas.php
 *
 * Wrapper CLI sobre CuentaEliminacionService::ejecutarPendientes().
 * Mantenido para uso opcional via cPanel cron, pero la ejecución manual
 * desde el panel admin es el flujo preferido.
 *
 *   0 3 * * * /usr/local/bin/php /home/placerse/public_html/cron/hard_delete_cuentas.php >> /home/placerse/public_html/logs/cron_delete.log 2>&1
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/CuentaEliminacionService.php';

$LOG = LOGS_PATH . '/cron_delete.log';
$line = '[' . date('Y-m-d H:i:s') . '] ';

try {
    $svc = new CuentaEliminacionService();
    $r   = $svc->ejecutarPendientes(50);
    $msg = $line . "sweep ok={$r['ok']} fail={$r['fail']} procesadas={$r['procesadas']}\n";
    @file_put_contents($LOG, $msg, FILE_APPEND);
    if (PHP_SAPI === 'cli') echo $msg;
    foreach ($r['errores'] as $e) {
        $em = $line . "ERR " . $e . "\n";
        @file_put_contents($LOG, $em, FILE_APPEND);
        if (PHP_SAPI === 'cli') echo $em;
    }
    exit($r['fail'] === 0 ? 0 : 1);
} catch (\Throwable $e) {
    $em = $line . 'FATAL ' . $e->getMessage() . "\n";
    @file_put_contents($LOG, $em, FILE_APPEND);
    if (PHP_SAPI === 'cli') echo $em;
    exit(1);
}
