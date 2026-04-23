<?php
/**
 * env.example.php
 * Plantilla — copia este archivo como:
 *   - env.development.php  (en local)
 *   - env.production.php   (en el servidor)
 * y rellena los valores reales.
 *
 * Estos archivos NUNCA se suben a git (están en .gitignore).
 */

return [
    'app_url' => 'http://localhost/Publicidad',
    'db_host' => 'localhost',
    'db_port' => '3306',
    'db_name' => 'clasificados_adultos',
    'db_user' => 'root',
    'db_pass' => '',

    // SMTP — en local normalmente false, en producción true
    'smtp_enabled'   => false,
    'smtp_host'      => 'mail.placerselecto.com',  // o el host SMTP de cPanel
    'smtp_port'      => 587,
    'smtp_secure'    => 'tls',                     // 'tls' (587) o 'ssl' (465)
    'smtp_user'      => 'noreply@placerselecto.com',
    'smtp_pass'      => 'la-contraseña-del-buzón',
    'smtp_from'      => 'noreply@placerselecto.com',
    'smtp_from_name' => 'PlacerSelecto',
];
