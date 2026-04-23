<?php
/**
 * UsuarioModel.php
 * Modelo para la tabla `usuarios`.
 * Gestiona registro, autenticación, verificación y rate limiting.
 */

require_once APP_PATH . '/Model.php';

class UsuarioModel extends Model
{
    protected string $table      = 'usuarios';
    protected string $primaryKey = 'id';

    // ---------------------------------------------------------
    // REGISTRO Y AUTENTICACIÓN
    // ---------------------------------------------------------

    /**
     * Crea un nuevo usuario con contraseña hasheada.
     *
     * @param array $data ['nombre', 'email', 'password', 'telefono', 'ip_registro']
     * @return int ID del usuario creado
     */
    public function crear(array $data): int
    {
        return $this->insert([
            'nombre'               => $data['nombre'],
            'email'                => strtolower(trim($data['email'])),
            'password'             => password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]),
            'telefono'             => $data['telefono'] ?? null,
            'telefono_original'    => $data['telefono'] ?? null,
            'rol'                  => $data['rol'] ?? 'usuario',
            'verificado'           => 1,
            'estado_verificacion'  => 'aprobado',
            'email_verificado'     => $data['email_verificado']   ?? 1,
            'email_verify_token'   => $data['email_verify_token'] ?? null,
            'ip_registro'          => $data['ip_registro'] ?? null,
            'fecha_creacion'       => date('Y-m-d H:i:s'),
            'fecha_actualizacion'  => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Busca un usuario por email (case-insensitive).
     */
    public function buscarPorEmail(string $email): ?array
    {
        return $this->findBy('email', strtolower(trim($email)));
    }

    /**
     * Verifica credenciales de login.
     * Retorna el usuario si son correctas, null si no.
     *
     * @return array|null
     */
    public function autenticar(string $email, string $password): ?array
    {
        $usuario = $this->buscarPorEmail($email);

        if (!$usuario) {
            return null;
        }

        if (!password_verify($password, $usuario['password'])) {
            return null;
        }

        // Actualizar último login y resetear intentos
        $this->update($usuario['id'], [
            'ultimo_login'        => date('Y-m-d H:i:s'),
            'intentos_login'      => 0,
            'bloqueado_hasta'     => null,
            'fecha_actualizacion' => date('Y-m-d H:i:s'),
        ]);

        return $usuario;
    }

    /**
     * Verifica si el usuario está bloqueado por intentos fallidos.
     */
    public function estaBloqueado(string $email): bool
    {
        $usuario = $this->buscarPorEmail($email);
        if (!$usuario) {
            return false;
        }

        if (!empty($usuario['bloqueado_hasta'])) {
            return strtotime($usuario['bloqueado_hasta']) > time();
        }

        return false;
    }

    /**
     * Registra un intento de login fallido.
     * Si supera el límite, bloquea temporalmente.
     */
    public function registrarIntentoFallido(string $email): void
    {
        $usuario = $this->buscarPorEmail($email);
        if (!$usuario) {
            return;
        }

        $intentos = (int) $usuario['intentos_login'] + 1;
        $data     = [
            'intentos_login'      => $intentos,
            'fecha_actualizacion' => date('Y-m-d H:i:s'),
        ];

        if ($intentos >= LOGIN_MAX_ATTEMPTS) {
            $data['bloqueado_hasta'] = date('Y-m-d H:i:s', time() + LOGIN_LOCKOUT_TIME);
            $data['intentos_login']  = 0; // Resetear para siguiente ciclo
        }

        $this->update($usuario['id'], $data);
    }

    // ---------------------------------------------------------
    // VERIFICACIÓN
    // ---------------------------------------------------------

    /**
     * Aprueba la verificación de un usuario.
     */
    public function aprobar(int $id): bool
    {
        return $this->update($id, [
            'verificado'           => 1,
            'estado_verificacion'  => 'aprobado',
            'fecha_actualizacion'  => date('Y-m-d H:i:s'),
        ]) > 0;
    }

    /**
     * Rechaza la verificación de un usuario.
     */
    public function rechazar(int $id): bool
    {
        return $this->update($id, [
            'verificado'           => 0,
            'estado_verificacion'  => 'rechazado',
            'fecha_actualizacion'  => date('Y-m-d H:i:s'),
        ]) > 0;
    }

    /**
     * Verifica si el email ya está registrado.
     */
    public function emailExiste(string $email): bool
    {
        return $this->exists('email', strtolower(trim($email)));
    }

    public function telefonoExiste(string $telefono): bool
    {
        $tel = preg_replace('/\D/', '', $telefono);
        if ($tel === '') return false;
        $stmt = $this->raw(
            "SELECT 1 FROM `{$this->table}` WHERE REGEXP_REPLACE(telefono,'[^0-9]','') = ? LIMIT 1",
            [$tel]
        );
        return (bool) $stmt->fetchColumn();
    }

    // ---------------------------------------------------------
    // RECUPERACIÓN DE CONTRASEÑA
    // ---------------------------------------------------------

    /**
     * Genera y guarda un token de recuperación de contraseña.
     *
     * @return string El token generado
     */
    public function generarTokenRecuperacion(int $id): string
    {
        $token = bin2hex(random_bytes(32));
        $this->update($id, [
            'token_recuperacion'  => $token,
            'token_expiracion'    => date('Y-m-d H:i:s', time() + 3600), // 1 hora
            'fecha_actualizacion' => date('Y-m-d H:i:s'),
        ]);
        return $token;
    }

    /**
     * Busca un usuario por token de recuperación válido (no expirado).
     */
    public function buscarPorToken(string $token): ?array
    {
        $sql = "SELECT * FROM `usuarios`
                WHERE `token_recuperacion` = ?
                  AND `token_expiracion` > NOW()
                LIMIT 1";

        $stmt = $this->raw($sql, [$token]);
        $row  = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Actualiza la contraseña y limpia el token de recuperación.
     */
    public function actualizarPassword(int $id, string $nuevaPassword): bool
    {
        return $this->update($id, [
            'password'            => password_hash($nuevaPassword, PASSWORD_BCRYPT, ['cost' => 12]),
            'token_recuperacion'  => null,
            'token_expiracion'    => null,
            'fecha_actualizacion' => date('Y-m-d H:i:s'),
        ]) > 0;
    }

    // ---------------------------------------------------------
    // ADMIN
    // ---------------------------------------------------------

    /**
     * Lista usuarios paginados con filtros opcionales.
     * Incluye estadísticas: conexiones, visitas, perfiles y estado de sesión.
     *
     * @return array{items: array, total: int, pages: int, current: int}
     */
    public function listarPaginado(
        int    $page    = 1,
        string $estado  = '',
        string $buscar  = '',
        int    $perPage = ITEMS_PER_PAGE
    ): array {
        $where  = [];
        $params = [];

        if ($estado !== '') {
            $where[]  = 'u.`estado_verificacion` = ?';
            $params[] = $estado;
        }

        if ($buscar !== '') {
            $where[]  = '(u.`nombre` LIKE ? OR u.`email` LIKE ?)';
            $params[] = '%' . $buscar . '%';
            $params[] = '%' . $buscar . '%';
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $db = Database::getInstance()->getConnection();

        // Contar total
        $countSql  = "SELECT COUNT(*) FROM `usuarios` u {$whereClause}";
        $countStmt = $db->prepare($countSql);
        $countStmt->execute($params);
        $total  = (int) $countStmt->fetchColumn();
        $pages  = max(1, (int) ceil($total / $perPage));
        $page   = max(1, min($page, $pages));
        $offset = ($page - 1) * $perPage;

        // Query principal con estadísticas
        $sql = "
            SELECT
                u.*,
                -- ¿Está en sesión? (último login en los últimos 30 min)
                IF(u.ultimo_login >= DATE_SUB(NOW(), INTERVAL 30 MINUTE), 1, 0) AS en_sesion,
                -- Veces que se conectó exitosamente
                (SELECT COUNT(*) FROM sesiones_login sl
                 WHERE sl.email = u.email AND sl.exitoso = 1) AS conexiones,
                -- Total vistas en todos sus perfiles
                (SELECT COALESCE(SUM(p.vistas), 0) FROM perfiles p
                 WHERE p.id_usuario = u.id) AS vistas_perfiles,
                -- Cantidad de perfiles
                (SELECT COUNT(*) FROM perfiles p
                 WHERE p.id_usuario = u.id) AS total_perfiles,
                -- Perfiles publicados
                (SELECT COUNT(*) FROM perfiles p
                 WHERE p.id_usuario = u.id AND p.estado = 'publicado') AS perfiles_publicados
            FROM `usuarios` u
            {$whereClause}
            ORDER BY u.fecha_creacion DESC
            LIMIT ? OFFSET ?
        ";

        $allParams = array_merge($params, [$perPage, $offset]);
        $stmt      = $db->prepare($sql);
        $stmt->execute($allParams);

        return [
            'items'   => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'total'   => $total,
            'pages'   => $pages,
            'current' => $page,
            'perPage' => $perPage,
        ];
    }

    /**
     * Estadísticas básicas para el dashboard admin.
     *
     * @return array{total: int, pendientes: int, aprobados: int, rechazados: int}
     */
    public function estadisticas(): array
    {
        $sql = "SELECT
                    COUNT(*)                                        AS total,
                    SUM(estado_verificacion = 'pendiente')          AS pendientes,
                    SUM(estado_verificacion = 'aprobado')           AS aprobados,
                    SUM(estado_verificacion = 'rechazado')          AS rechazados
                FROM `usuarios`
                WHERE `rol` = 'usuario'";

        $stmt = $this->raw($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ---------------------------------------------------------
    // CONFIABILIDAD
    // ---------------------------------------------------------

    /**
     * Calcula los indicadores de confiabilidad de un usuario.
     * Retorna array con cada indicador y un score total.
     */
    public function confiabilidad(int $id): array
    {
        $u = $this->find($id);
        if (!$u) {
            return ['score' => 0, 'total' => 0, 'indicadores' => []];
        }

        $db = Database::getInstance()->getConnection();

        // -- Indicador 5/8: años en el portal y reportes aceptados --
        $diasRegistrado = (int) floor((time() - strtotime($u['fecha_creacion'])) / 86400);
        $masDeUnAnio    = $diasRegistrado >= 365;

        $stmtRep = $db->prepare(
            "SELECT COUNT(*) FROM reportes
             WHERE id_anuncio IN (SELECT id FROM anuncios WHERE id_usuario = ?)
               AND estado = 'resuelto'"
        );
        $stmtRep->execute([$id]);
        $reportesResueltos = (int) $stmtRep->fetchColumn();
        $sinReportes = $reportesResueltos === 0;

        // -- Indicador 6: teléfono no cambiado --
        $telefonoSinCambios = ($u['telefono_original'] === null)
            || ($u['telefono'] === $u['telefono_original']);

        // -- Indicador 7: máximo 2 ciudades en últimos 7 días --
        $stmtCiudades = $db->prepare(
            "SELECT COUNT(DISTINCT ciudad) AS ciudades
             FROM anuncios
             WHERE id_usuario = ?
               AND estado = 'publicado'
               AND fecha_publicacion >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
        );
        $stmtCiudades->execute([$id]);
        $ciudadesRecientes = (int) $stmtCiudades->fetchColumn();
        $pocasCiudades = $ciudadesRecientes <= 2;

        // -- Indicador 8: usuario con perfiles publicados + sin reportes --
        $stmtActivo = $db->prepare(
            "SELECT
                (SELECT COUNT(*) FROM perfiles WHERE id_usuario = ? AND estado = 'publicado') +
                (SELECT COUNT(*) FROM anuncios WHERE id_usuario = ? AND estado = 'publicado')"
        );
        $stmtActivo->execute([$id, $id]);
        $tieneContenidoPublicado = (int) $stmtActivo->fetchColumn() > 0;

        $indicadores = [
            [
                'key'         => 'edad_verificada',
                'activo'      => $u['estado_verificacion'] === 'aprobado',
                'icono'       => 'bi-person-check',
                'label'       => 'Mayoría de edad verificada',
                'descripcion' => 'La plataforma revisó y confirmó que el usuario es mayor de edad.',
            ],
            [
                'key'         => 'documento_verificado',
                'activo'      => (bool) $u['documento_verificado'],
                'icono'       => 'bi-card-checklist',
                'label'       => 'Identidad verificada',
                'descripcion' => 'El usuario entregó su documento de identidad y coincide con su foto o video de verificación.',
            ],
            [
                'key'         => 'fotos_verificadas',
                'activo'      => (bool) $u['fotos_verificadas'],
                'icono'       => 'bi-camera-video-fill',
                'label'       => 'Fotos del perfil verificadas',
                'descripcion' => 'Las fotos han sido verificadas. El usuario envió un selfie con un cartel y sus fotos de perfil a cara descubierta.',
            ],
            [
                'key'         => 'sin_anticipo',
                'activo'      => (bool) $u['sin_anticipo'],
                'icono'       => 'bi-shield-check',
                'label'       => 'No pide depósito anticipado',
                'descripcion' => 'El anunciante declaró que no pide depósito anticipado. Si esta opción está marcada, nunca deposite por adelantado.',
            ],
            [
                'key'         => 'sin_reportes',
                'activo'      => $sinReportes,
                'icono'       => 'bi-star-fill',
                'label'       => 'Sin reportes negativos',
                'descripcion' => 'No se ha aceptado ningún reporte negativo en contra de este usuario.',
            ],
            [
                'key'         => 'telefono_estable',
                'activo'      => $telefonoSinCambios,
                'icono'       => 'bi-telephone-fill',
                'label'       => 'Teléfono sin cambios',
                'descripcion' => 'El usuario no ha cambiado su número de teléfono desde el registro.',
            ],
            [
                'key'         => 'pocas_ciudades',
                'activo'      => $pocasCiudades,
                'icono'       => 'bi-geo-alt-fill',
                'label'       => 'Actividad local',
                'descripcion' => 'No ha publicado en más de 2 ciudades en los últimos 7 días. Publicar en muchas ciudades puede indicar perfil falso.',
            ],
            [
                'key'         => 'usuario_activo',
                'activo'      => $tieneContenidoPublicado && $sinReportes,
                'icono'       => 'bi-award-fill',
                'label'       => 'Usuario activo y confiable',
                'descripcion' => 'Tiene al menos un perfil publicado y no se ha aceptado ningún reporte negativo en su contra.',
            ],
        ];

        $score = count(array_filter($indicadores, fn($i) => $i['activo']));

        return [
            'score'       => $score,
            'total'       => count($indicadores),
            'indicadores' => $indicadores,
        ];
    }

    /**
     * Elimina COMPLETAMENTE un usuario y todos sus datos asociados:
     * perfiles + fotos de perfil, anuncios + fotos de anuncio,
     * pagos, reportes, sesiones_login y el propio registro de usuario.
     *
     * @param int $id ID del usuario a eliminar (no debe ser admin)
     * @return array{perfiles: int, anuncios: int, archivos: int} Resumen de lo eliminado
     */
    public function eliminarCompleto(int $id): array
    {
        $db = Database::getInstance()->getConnection();
        $resumen = ['perfiles' => 0, 'anuncios' => 0, 'archivos' => 0];

        $usuario = $this->find($id);
        if (!$usuario || $usuario['rol'] === 'admin') {
            return $resumen;
        }

        // 1. Eliminar fotos físicas y registros de PERFILES
        $stmtPerfiles = $db->prepare("SELECT id, imagen_principal FROM perfiles WHERE id_usuario = ?");
        $stmtPerfiles->execute([$id]);
        $perfiles = $stmtPerfiles->fetchAll(PDO::FETCH_ASSOC);

        foreach ($perfiles as $perfil) {
            $idPerfil = (int) $perfil['id'];

            // Fotos de galería del perfil
            $stmtFotos = $db->prepare("SELECT nombre_archivo FROM perfil_fotos WHERE id_perfil = ?");
            $stmtFotos->execute([$idPerfil]);
            $fotosPerfil = $stmtFotos->fetchAll(PDO::FETCH_ASSOC);

            foreach ($fotosPerfil as $f) {
                $path = UPLOADS_PATH . '/anuncios/' . basename($f['nombre_archivo']);
                if (file_exists($path)) { @unlink($path); $resumen['archivos']++; }
            }
            $db->prepare("DELETE FROM perfil_fotos WHERE id_perfil = ?")->execute([$idPerfil]);

            // Imagen principal del perfil
            if (!empty($perfil['imagen_principal'])) {
                $path = UPLOADS_PATH . '/anuncios/' . basename($perfil['imagen_principal']);
                if (file_exists($path)) { @unlink($path); $resumen['archivos']++; }
            }

            $resumen['perfiles']++;
        }
        $db->prepare("DELETE FROM perfiles WHERE id_usuario = ?")->execute([$id]);

        // 2. Eliminar fotos físicas y registros de ANUNCIOS
        $stmtAnuncios = $db->prepare("SELECT id, imagen_principal FROM anuncios WHERE id_usuario = ?");
        $stmtAnuncios->execute([$id]);
        $anuncios = $stmtAnuncios->fetchAll(PDO::FETCH_ASSOC);

        foreach ($anuncios as $anuncio) {
            $idAnuncio = (int) $anuncio['id'];

            $stmtFotos = $db->prepare("SELECT nombre_archivo FROM anuncio_fotos WHERE id_anuncio = ?");
            $stmtFotos->execute([$idAnuncio]);
            $fotosAn = $stmtFotos->fetchAll(PDO::FETCH_ASSOC);

            foreach ($fotosAn as $f) {
                $path = UPLOADS_PATH . '/anuncios/' . basename($f['nombre_archivo']);
                if (file_exists($path)) { @unlink($path); $resumen['archivos']++; }
            }
            $db->prepare("DELETE FROM anuncio_fotos WHERE id_anuncio = ?")->execute([$idAnuncio]);

            if (!empty($anuncio['imagen_principal'])) {
                $path = UPLOADS_PATH . '/anuncios/' . basename($anuncio['imagen_principal']);
                if (file_exists($path)) { @unlink($path); $resumen['archivos']++; }
            }

            $resumen['anuncios']++;
        }
        $db->prepare("DELETE FROM anuncios WHERE id_usuario = ?")->execute([$id]);

        // 3. Pagos, reportes y sesiones
        $db->prepare("DELETE FROM pagos WHERE id_usuario = ?")->execute([$id]);
        $db->prepare("DELETE FROM reportes WHERE id_usuario = ?")->execute([$id]);
        $db->prepare("DELETE FROM sesiones_login WHERE email = ?")->execute([$usuario['email']]);

        // 4. Eliminar el usuario
        $this->delete($id);

        return $resumen;
    }

    /**
     * Activa/desactiva verificación de documento o fotos (solo admin).
     */
    public function setVerificacion(int $id, string $campo, bool $valor): bool
    {
        $permitidos = ['documento_verificado', 'fotos_verificadas'];
        if (!in_array($campo, $permitidos, true)) {
            return false;
        }
        return $this->update($id, [
            $campo               => $valor ? 1 : 0,
            'fecha_actualizacion' => date('Y-m-d H:i:s'),
        ]) > 0;
    }

    /**
     * El usuario declara que no pide anticipos.
     */
    public function setSinAnticipo(int $id, bool $valor): bool
    {
        return $this->update($id, [
            'sin_anticipo'        => $valor ? 1 : 0,
            'fecha_actualizacion' => date('Y-m-d H:i:s'),
        ]) > 0;
    }

    // ---------------------------------------------------------
    // SESIÓN
    // ---------------------------------------------------------

    /**
     * Guarda los datos del usuario en sesión tras login exitoso.
     */
    public function guardarEnSesion(array $usuario): void
    {
        SessionManager::set('user_id',                   $usuario['id']);
        SessionManager::set('user_nombre',               $usuario['nombre']);
        SessionManager::set('user_email',                $usuario['email']);
        SessionManager::set('user_rol',                  $usuario['rol']);
        SessionManager::set('user_verificado',           (bool) $usuario['verificado']);
        SessionManager::set('user_estado_verificacion',  $usuario['estado_verificacion']);
    }

    /**
     * Limpia los datos de sesión (logout).
     */
    public function limpiarSesion(): void
    {
        SessionManager::destroy();
    }
}
