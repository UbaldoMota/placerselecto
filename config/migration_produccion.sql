-- =============================================================
-- migration_produccion.sql
-- Migración consolidada para sincronizar schema del servidor con
-- los cambios de desarrollo (comentarios, videos, stats, config).
-- Idempotente: seguro correr varias veces.
-- =============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- =========================================================
-- TABLA: perfil_videos (videos por perfil, moderados)
-- =========================================================
CREATE TABLE IF NOT EXISTS `perfil_videos` (
    `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_perfil`       INT UNSIGNED NOT NULL,
    `token`           CHAR(40)     NOT NULL,
    `nombre_archivo`  VARCHAR(255) NOT NULL,
    `orden`           TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `duracion_seg`    SMALLINT UNSIGNED DEFAULT NULL,
    `tamano_bytes`    INT UNSIGNED DEFAULT NULL,
    `oculta`          TINYINT(1)   NOT NULL DEFAULT 0,
    `estado`          ENUM('pendiente','publicado','rechazado') NOT NULL DEFAULT 'pendiente',
    `motivo_rechazo`  VARCHAR(250) DEFAULT NULL,
    `created_at`      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `token` (`token`),
    KEY `idx_perfil_orden` (`id_perfil`,`orden`),
    CONSTRAINT `fk_video_perfil` FOREIGN KEY (`id_perfil`) REFERENCES `perfiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- TABLA: perfil_comentarios (comentarios con rating + moderación)
-- =========================================================
CREATE TABLE IF NOT EXISTS `perfil_comentarios` (
    `id`                   INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_perfil`            INT UNSIGNED NOT NULL,
    `id_usuario`           INT UNSIGNED NOT NULL,
    `calificacion`         TINYINT UNSIGNED NOT NULL DEFAULT 5,
    `comentario`           TEXT         NOT NULL,
    `estado`               ENUM('pendiente','publicado','oculto','reportado','eliminado') NOT NULL DEFAULT 'pendiente',
    `ip_autor`             VARCHAR(45)  DEFAULT NULL,
    `fecha_creacion`       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `fecha_actualizacion`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `fecha_aprobacion`     DATETIME     DEFAULT NULL,
    `fecha_cooldown_hasta` DATETIME     DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_perfil_usuario` (`id_perfil`,`id_usuario`),
    KEY `idx_perfil_fecha` (`id_perfil`,`fecha_creacion`),
    KEY `idx_estado` (`estado`,`fecha_creacion`),
    KEY `fk_com_usuario` (`id_usuario`),
    CONSTRAINT `fk_com_perfil`  FOREIGN KEY (`id_perfil`)  REFERENCES `perfiles` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_com_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- TABLA: perfil_stats (métricas diarias: visitas + clicks WA)
-- =========================================================
CREATE TABLE IF NOT EXISTS `perfil_stats` (
    `id_perfil`       INT UNSIGNED NOT NULL,
    `fecha`           DATE         NOT NULL,
    `visitas`         INT          NOT NULL DEFAULT 0,
    `clicks_whatsapp` INT          NOT NULL DEFAULT 0,
    PRIMARY KEY (`id_perfil`,`fecha`),
    KEY `idx_fecha` (`fecha`),
    CONSTRAINT `fk_pstats_perfil` FOREIGN KEY (`id_perfil`) REFERENCES `perfiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- TABLA: configuracion (clave/valor para ajustes de admin)
-- =========================================================
CREATE TABLE IF NOT EXISTS `configuracion` (
    `clave`               VARCHAR(64) NOT NULL,
    `valor`               TEXT        NOT NULL,
    `descripcion`         VARCHAR(255) DEFAULT NULL,
    `fecha_actualizacion` DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`clave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Config defaults de storage
INSERT IGNORE INTO `configuracion` (`clave`, `valor`, `descripcion`) VALUES
    ('storage_limit_mb',    '5120', 'Límite total de almacenamiento en MB'),
    ('storage_warning_pct', '80',   'Porcentaje de uso para mostrar alerta');

-- =========================================================
-- AMPLIAR: usuarios.rol — agregar 'comentarista'
-- =========================================================
ALTER TABLE `usuarios`
    MODIFY COLUMN `rol` ENUM('usuario','admin','comentarista') NOT NULL DEFAULT 'usuario';

-- =========================================================
-- AMPLIAR: usuarios — columnas de verificación + saldo
-- (Usar IF NOT EXISTS para ser idempotente — MariaDB 10.3+)
-- =========================================================
ALTER TABLE `usuarios`
    ADD COLUMN IF NOT EXISTS `documento_verificado`     TINYINT(1)   NOT NULL DEFAULT 0 AFTER `verificado`,
    ADD COLUMN IF NOT EXISTS `fotos_verificadas`        TINYINT(1)   NOT NULL DEFAULT 0 AFTER `documento_verificado`,
    ADD COLUMN IF NOT EXISTS `video_verificacion`       VARCHAR(255) DEFAULT NULL AFTER `fotos_verificadas`,
    ADD COLUMN IF NOT EXISTS `video_verificacion_at`    DATETIME     DEFAULT NULL AFTER `video_verificacion`,
    ADD COLUMN IF NOT EXISTS `documento_identidad`      VARCHAR(255) DEFAULT NULL AFTER `video_verificacion_at`,
    ADD COLUMN IF NOT EXISTS `documento_identidad_at`   DATETIME     DEFAULT NULL AFTER `documento_identidad`,
    ADD COLUMN IF NOT EXISTS `documento_estado`         VARCHAR(20)  DEFAULT NULL AFTER `documento_identidad_at`,
    ADD COLUMN IF NOT EXISTS `documento_rechazo_motivo` VARCHAR(255) DEFAULT NULL AFTER `documento_estado`,
    ADD COLUMN IF NOT EXISTS `sin_anticipo`             TINYINT(1)   NOT NULL DEFAULT 0 AFTER `documento_rechazo_motivo`,
    ADD COLUMN IF NOT EXISTS `telefono_original`        VARCHAR(20)  DEFAULT NULL AFTER `sin_anticipo`;

-- =========================================================
-- AMPLIAR: perfiles — campos nuevos
-- =========================================================
ALTER TABLE `perfiles`
    ADD COLUMN IF NOT EXISTS `edad`                 TINYINT UNSIGNED NOT NULL DEFAULT 18       AFTER `descripcion`,
    ADD COLUMN IF NOT EXISTS `edad_publica`         TINYINT(1)       NOT NULL DEFAULT 1        AFTER `edad`,
    ADD COLUMN IF NOT EXISTS `telegram`             VARCHAR(100)     DEFAULT NULL              AFTER `whatsapp`,
    ADD COLUMN IF NOT EXISTS `email_contacto`       VARCHAR(150)     DEFAULT NULL              AFTER `telegram`,
    ADD COLUMN IF NOT EXISTS `pide_anticipo`        TINYINT(1)       NOT NULL DEFAULT 0        AFTER `email_contacto`,
    ADD COLUMN IF NOT EXISTS `zona_lat`             DECIMAL(10,7)    DEFAULT NULL              AFTER `pide_anticipo`,
    ADD COLUMN IF NOT EXISTS `zona_lng`             DECIMAL(10,7)    DEFAULT NULL              AFTER `zona_lat`,
    ADD COLUMN IF NOT EXISTS `zona_radio`           SMALLINT         NOT NULL DEFAULT 5        AFTER `zona_lng`,
    ADD COLUMN IF NOT EXISTS `zona_descripcion`     VARCHAR(200)     DEFAULT NULL              AFTER `zona_radio`,
    ADD COLUMN IF NOT EXISTS `video_verificacion`   VARCHAR(255)     DEFAULT NULL              AFTER `zona_descripcion`,
    ADD COLUMN IF NOT EXISTS `video_verificacion_at` DATETIME        DEFAULT NULL              AFTER `video_verificacion`;

-- =========================================================
-- AMPLIAR: reportes — nuevos motivos + estado + acciones admin
-- =========================================================
ALTER TABLE `reportes`
    MODIFY COLUMN `id_anuncio` INT UNSIGNED DEFAULT NULL;

ALTER TABLE `reportes`
    MODIFY COLUMN `motivo` ENUM(
        'verificar_edad','mal_clasificado','difamaciones','fotos_de_internet',
        'fotos_son_mias','usan_mi_telefono','estafa','extorsion','contenido_ilegal',
        'spam','engano','menor_de_edad','datos_falsos','otro'
    ) NOT NULL DEFAULT 'otro';

ALTER TABLE `reportes`
    MODIFY COLUMN `estado` ENUM('pendiente','revisado','resuelto','rechazado') NOT NULL DEFAULT 'pendiente';

ALTER TABLE `reportes`
    ADD COLUMN IF NOT EXISTS `nota_admin`          TEXT         DEFAULT NULL AFTER `fecha_resolucion`,
    ADD COLUMN IF NOT EXISTS `id_admin_resolucion` INT UNSIGNED DEFAULT NULL AFTER `nota_admin`;

-- =========================================================
-- AMPLIAR: perfil_fotos — flag de foto de verificación + oculta
-- =========================================================
ALTER TABLE `perfil_fotos`
    ADD COLUMN IF NOT EXISTS `es_verificacion` TINYINT(1) NOT NULL DEFAULT 0 AFTER `orden`,
    ADD COLUMN IF NOT EXISTS `oculta`          TINYINT(1) NOT NULL DEFAULT 0 AFTER `created_at`;

-- =========================================================
-- AMPLIAR: pagos — columnas para sistema de tokens/paquetes
-- =========================================================
ALTER TABLE `pagos`
    ADD COLUMN IF NOT EXISTS `id_paquete`       INT UNSIGNED DEFAULT NULL AFTER `id_perfil`,
    ADD COLUMN IF NOT EXISTS `tokens_otorgados` INT UNSIGNED DEFAULT NULL AFTER `id_paquete`;

SET FOREIGN_KEY_CHECKS = 1;
