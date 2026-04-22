<?php
/**
 * LoginLogModel.php
 * Modelo para `sesiones_login`.
 * Gestiona el rate limiting de intentos de login por IP.
 */

require_once APP_PATH . '/Model.php';

class LoginLogModel extends Model
{
    protected string $table      = 'sesiones_login';
    protected string $primaryKey = 'id';

    /**
     * Registra un intento de login (exitoso o fallido).
     */
    public function registrar(string $ip, ?string $email, bool $exitoso): void
    {
        $this->insert([
            'ip'         => $ip,
            'email'      => $email,
            'exitoso'    => (int) $exitoso,
            'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
            'fecha'      => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Cuenta intentos fallidos de una IP en los últimos N minutos.
     */
    public function intentosFallidos(string $ip, int $minutos = 15): int
    {
        $sql  = "SELECT COUNT(*) FROM `sesiones_login`
                 WHERE `ip` = ?
                   AND `exitoso` = 0
                   AND `fecha` > DATE_SUB(NOW(), INTERVAL ? MINUTE)";
        return (int) $this->raw($sql, [$ip, $minutos])->fetchColumn();
    }

    /**
     * Verifica si una IP está bajo rate limiting.
     * Límite: LOGIN_MAX_ATTEMPTS intentos fallidos en LOGIN_LOCKOUT_TIME segundos.
     */
    public function ipBloqueada(string $ip): bool
    {
        $minutos = (int) ceil(LOGIN_LOCKOUT_TIME / 60);
        return $this->intentosFallidos($ip, $minutos) >= LOGIN_MAX_ATTEMPTS;
    }
}
