-- =============================================================
-- schema.sql
-- Esquema completo de la base de datos: clasificados_adultos
-- Motor: MySQL 8+ / MariaDB 10.6+
-- Charset: utf8mb4 (soporte completo Unicode + emojis)
-- Ejecutar como: mysql -u root -p < config/schema.sql
-- =============================================================

-- Crear base de datos si no existe
CREATE DATABASE IF NOT EXISTS `clasificados_adultos`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `clasificados_adultos`;

-- Desactivar verificación FK durante la carga
SET FOREIGN_KEY_CHECKS = 0;
SET sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO';

-- =============================================================
-- TABLA: usuarios
-- =============================================================
DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios` (
    `id`                   INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `nombre`               VARCHAR(120)    NOT NULL,
    `email`                VARCHAR(180)    NOT NULL,
    `password`             VARCHAR(255)    NOT NULL           COMMENT 'Hash bcrypt via password_hash()',
    `telefono`             VARCHAR(20)     DEFAULT NULL,
    `rol`                  ENUM('usuario','admin') NOT NULL DEFAULT 'usuario',
    `verificado`           TINYINT(1)      NOT NULL DEFAULT 0  COMMENT '0=no verificado, 1=verificado',
    `estado_verificacion`  ENUM('pendiente','aprobado','rechazado') NOT NULL DEFAULT 'pendiente',
    `token_recuperacion`   VARCHAR(100)    DEFAULT NULL        COMMENT 'Token para reset de contraseña',
    `token_expiracion`     DATETIME        DEFAULT NULL        COMMENT 'Expiración del token de recuperación',
    `intentos_login`       TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Contador de intentos fallidos',
    `bloqueado_hasta`      DATETIME        DEFAULT NULL        COMMENT 'Bloqueo temporal por intentos fallidos',
    `ultimo_login`         DATETIME        DEFAULT NULL,
    `ip_registro`          VARCHAR(45)     DEFAULT NULL        COMMENT 'IPv4 o IPv6',
    `fecha_creacion`       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `fecha_actualizacion`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_email`       (`email`),
    KEY        `idx_rol`        (`rol`),
    KEY        `idx_verificado` (`verificado`),
    KEY        `idx_estado_ver` (`estado_verificacion`),
    KEY        `idx_fecha`      (`fecha_creacion`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Usuarios del sistema';

-- =============================================================
-- TABLA: categorias
-- =============================================================
DROP TABLE IF EXISTS `categorias`;
CREATE TABLE `categorias` (
    `id`          TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nombre`      VARCHAR(80)      NOT NULL,
    `slug`        VARCHAR(80)      NOT NULL,
    `icono`       VARCHAR(50)      DEFAULT 'bi-person'  COMMENT 'Clase Bootstrap Icons',
    `activa`      TINYINT(1)       NOT NULL DEFAULT 1,
    `orden`       TINYINT UNSIGNED NOT NULL DEFAULT 0,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_slug` (`slug`),
    KEY        `idx_activa` (`activa`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Categorías de anuncios';

-- =============================================================
-- TABLA: ciudades
-- =============================================================
DROP TABLE IF EXISTS `ciudades`;
CREATE TABLE `ciudades` (
    `id`       SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nombre`   VARCHAR(100)      NOT NULL,
    `estado`   VARCHAR(100)      DEFAULT NULL  COMMENT 'Estado/Provincia',
    `pais`     CHAR(2)           NOT NULL DEFAULT 'MX',
    `activa`   TINYINT(1)        NOT NULL DEFAULT 1,

    PRIMARY KEY (`id`),
    KEY `idx_pais`   (`pais`),
    KEY `idx_activa` (`activa`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Ciudades disponibles para anuncios';

-- =============================================================
-- TABLA: anuncios
-- =============================================================
DROP TABLE IF EXISTS `anuncios`;
CREATE TABLE `anuncios` (
    `id`                INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `id_usuario`        INT UNSIGNED     NOT NULL,
    `titulo`            VARCHAR(120)     NOT NULL,
    `descripcion`       TEXT             NOT NULL,
    `ciudad`            VARCHAR(100)     NOT NULL,
    `id_categoria`      TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `imagen_principal`  VARCHAR(255)     DEFAULT NULL  COMMENT 'Nombre de archivo en /uploads/anuncios/',
    `imagenes`          JSON             DEFAULT NULL  COMMENT 'Array JSON de nombres de archivo adicionales',
    `whatsapp`          VARCHAR(20)      DEFAULT NULL  COMMENT 'Número sin formato para link wa.me',
    `estado`            ENUM('pendiente','publicado','rechazado','expirado') NOT NULL DEFAULT 'pendiente',
    `destacado`         TINYINT(1)       NOT NULL DEFAULT 0,
    `fecha_destacado`   DATETIME         DEFAULT NULL  COMMENT 'Inicio del período de destacado',
    `fecha_expiracion_destacado` DATETIME DEFAULT NULL COMMENT 'Fin del período de destacado',
    `vistas`            INT UNSIGNED     NOT NULL DEFAULT 0,
    `fecha_publicacion` DATETIME         DEFAULT NULL,
    `fecha_expiracion`  DATETIME         DEFAULT NULL  COMMENT 'Expiración del anuncio (30 días por defecto)',
    `fecha_creacion`    DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `fecha_actualizacion` DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_usuario`    (`id_usuario`),
    KEY `idx_categoria`  (`id_categoria`),
    KEY `idx_ciudad`     (`ciudad`),
    KEY `idx_estado`     (`estado`),
    KEY `idx_destacado`  (`destacado`),
    -- Índice compuesto para el listado principal (publicados, destacados primero)
    KEY `idx_listado`    (`estado`, `destacado`, `fecha_publicacion`),
    KEY `idx_expiracion` (`fecha_expiracion`),

    CONSTRAINT `fk_anuncios_usuario`
        FOREIGN KEY (`id_usuario`) REFERENCES `usuarios`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,

    CONSTRAINT `fk_anuncios_categoria`
        FOREIGN KEY (`id_categoria`) REFERENCES `categorias`(`id`)
        ON UPDATE CASCADE

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Anuncios clasificados';

-- =============================================================
-- TABLA: pagos
-- =============================================================
DROP TABLE IF EXISTS `pagos`;
CREATE TABLE `pagos` (
    `id`              INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `id_usuario`      INT UNSIGNED     NOT NULL,
    `id_anuncio`      INT UNSIGNED     DEFAULT NULL,
    `monto`           DECIMAL(10,2)    NOT NULL,
    `moneda`          CHAR(3)          NOT NULL DEFAULT 'MXN',
    `tipo_destacado`  TINYINT UNSIGNED NOT NULL COMMENT 'Días del plan: 3, 7 o 15',
    `estado`          ENUM('pendiente','completado','fallido','reembolsado') NOT NULL DEFAULT 'pendiente',
    `metodo_pago`     VARCHAR(50)      DEFAULT NULL  COMMENT 'ccbill | segpay | simulado',
    `referencia_ext`  VARCHAR(100)     DEFAULT NULL  COMMENT 'ID de transacción del procesador',
    `datos_pago`      JSON             DEFAULT NULL  COMMENT 'Respuesta completa del procesador (sanitizada)',
    `ip_pago`         VARCHAR(45)      DEFAULT NULL,
    `fecha_pago`      DATETIME         DEFAULT NULL  COMMENT 'Cuando el pago fue confirmado',
    `fecha_creacion`  DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_usuario`  (`id_usuario`),
    KEY `idx_anuncio`  (`id_anuncio`),
    KEY `idx_estado`   (`estado`),
    KEY `idx_fecha`    (`fecha_pago`),

    CONSTRAINT `fk_pagos_usuario`
        FOREIGN KEY (`id_usuario`) REFERENCES `usuarios`(`id`)
        ON DELETE RESTRICT ON UPDATE CASCADE,

    CONSTRAINT `fk_pagos_anuncio`
        FOREIGN KEY (`id_anuncio`) REFERENCES `anuncios`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Registro de pagos por planes de destacado';

-- =============================================================
-- TABLA: reportes
-- =============================================================
DROP TABLE IF EXISTS `reportes`;
CREATE TABLE `reportes` (
    `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `id_anuncio`  INT UNSIGNED  NOT NULL,
    `id_usuario`  INT UNSIGNED  DEFAULT NULL  COMMENT 'NULL si el reporte es anónimo',
    `motivo`      ENUM(
                    'contenido_ilegal',
                    'spam',
                    'engaño',
                    'menor_de_edad',
                    'datos_falsos',
                    'otro'
                  ) NOT NULL DEFAULT 'otro',
    `descripcion` VARCHAR(500)  DEFAULT NULL  COMMENT 'Detalle adicional opcional',
    `estado`      ENUM('pendiente','revisado','resuelto') NOT NULL DEFAULT 'pendiente',
    `ip_reporte`  VARCHAR(45)   DEFAULT NULL,
    `fecha`       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `fecha_resolucion` DATETIME DEFAULT NULL,

    PRIMARY KEY (`id`),
    KEY `idx_anuncio` (`id_anuncio`),
    KEY `idx_estado`  (`estado`),
    KEY `idx_fecha`   (`fecha`),

    CONSTRAINT `fk_reportes_anuncio`
        FOREIGN KEY (`id_anuncio`) REFERENCES `anuncios`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,

    CONSTRAINT `fk_reportes_usuario`
        FOREIGN KEY (`id_usuario`) REFERENCES `usuarios`(`id`)
        ON DELETE SET NULL ON UPDATE CASCADE

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Reportes de anuncios por usuarios';

-- =============================================================
-- TABLA: sesiones_login (rate limiting)
-- =============================================================
DROP TABLE IF EXISTS `sesiones_login`;
CREATE TABLE `sesiones_login` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `ip`         VARCHAR(45)  NOT NULL,
    `email`      VARCHAR(180) DEFAULT NULL,
    `exitoso`    TINYINT(1)   NOT NULL DEFAULT 0,
    `user_agent` VARCHAR(255) DEFAULT NULL,
    `fecha`      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_ip`    (`ip`),
    KEY `idx_fecha` (`fecha`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Log de intentos de login para rate limiting';

-- =============================================================
-- DATOS INICIALES: categorias
-- =============================================================
INSERT INTO `categorias` (`nombre`, `slug`, `icono`, `activa`, `orden`) VALUES
('Damas',               'damas',               'bi-person-heart',    1, 1),
('Caballeros',          'caballeros',           'bi-person-check',    1, 2),
('Trans',               'trans',                'bi-gender-trans',    1, 3),
('Parejas',             'parejas',              'bi-people-fill',     1, 4),
('Masajes',             'masajes',              'bi-hand-index-thumb',1, 5),
('Webcam',              'webcam',               'bi-camera-video',    1, 6),
('Acompañantes',        'acompanantes',         'bi-stars',           1, 7);

-- =============================================================
-- DATOS INICIALES: ciudades (México)
-- =============================================================
INSERT INTO `ciudades` (`nombre`, `estado`, `pais`) VALUES
('Ciudad de México',  'CDMX',              'MX'),
('Guadalajara',       'Jalisco',           'MX'),
('Monterrey',         'Nuevo León',        'MX'),
('Puebla',            'Puebla',            'MX'),
('Tijuana',           'Baja California',   'MX'),
('León',              'Guanajuato',        'MX'),
('Juárez',            'Chihuahua',         'MX'),
('Mérida',            'Yucatán',           'MX'),
('San Luis Potosí',   'San Luis Potosí',   'MX'),
('Cancún',            'Quintana Roo',      'MX'),
('Querétaro',         'Querétaro',         'MX'),
('Hermosillo',        'Sonora',            'MX'),
('Acapulco',          'Guerrero',          'MX'),
('Veracruz',          'Veracruz',          'MX'),
('Toluca',            'Estado de México',  'MX');

-- =============================================================
-- USUARIO ADMIN INICIAL
-- Password: Admin1234! (cambiar inmediatamente en producción)
-- Hash generado con: password_hash('Admin1234!', PASSWORD_BCRYPT, ['cost'=>12])
-- =============================================================
INSERT INTO `usuarios`
    (`nombre`, `email`, `password`, `rol`, `verificado`, `estado_verificacion`)
VALUES (
    'Administrador',
    'admin@clasificados.local',
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'admin',
    1,
    'aprobado'
);

-- Reactivar verificación FK
SET FOREIGN_KEY_CHECKS = 1;

-- =============================================================
-- EVENTO: expirar destacados automáticamente (requiere event scheduler ON)
-- SET GLOBAL event_scheduler = ON;
-- =============================================================
DROP EVENT IF EXISTS `evt_expirar_destacados`;
DELIMITER $$
CREATE EVENT `evt_expirar_destacados`
ON SCHEDULE EVERY 1 HOUR
STARTS CURRENT_TIMESTAMP
DO
BEGIN
    UPDATE `anuncios`
    SET `destacado` = 0,
        `fecha_destacado` = NULL,
        `fecha_expiracion_destacado` = NULL
    WHERE `destacado` = 1
      AND `fecha_expiracion_destacado` IS NOT NULL
      AND `fecha_expiracion_destacado` < NOW();
END$$
DELIMITER ;

-- =============================================================
-- EVENTO: expirar anuncios (30 días sin renovar)
-- =============================================================
DROP EVENT IF EXISTS `evt_expirar_anuncios`;
DELIMITER $$
CREATE EVENT `evt_expirar_anuncios`
ON SCHEDULE EVERY 1 DAY
STARTS CURRENT_TIMESTAMP
DO
BEGIN
    UPDATE `anuncios`
    SET `estado` = 'expirado'
    WHERE `estado` = 'publicado'
      AND `fecha_expiracion` IS NOT NULL
      AND `fecha_expiracion` < NOW();
END$$
DELIMITER ;

-- =============================================================
-- EVENTO: limpiar log de intentos de login (>30 días)
-- =============================================================
DROP EVENT IF EXISTS `evt_limpiar_login_log`;
DELIMITER $$
CREATE EVENT `evt_limpiar_login_log`
ON SCHEDULE EVERY 1 DAY
STARTS CURRENT_TIMESTAMP
DO
BEGIN
    DELETE FROM `sesiones_login`
    WHERE `fecha` < DATE_SUB(NOW(), INTERVAL 30 DAY);
END$$
DELIMITER ;

-- Confirmar creación
SELECT 'Schema instalado correctamente.' AS resultado;
