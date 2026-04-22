-- =============================================================
-- migration_perfiles.sql
-- Sistema de perfiles (reemplaza anuncios en el flujo principal)
-- Ejecutar: mysql -u root clasificados_adultos < migration_perfiles.sql --force
-- =============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- -------------------------------------------------------------
-- Tabla principal de perfiles
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `perfiles` (
  `id`                        INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `id_usuario`                INT UNSIGNED    NOT NULL,
  `nombre`                    VARCHAR(120)    NOT NULL,
  `descripcion`               TEXT            NOT NULL,
  `edad`                      TINYINT UNSIGNED NULL,
  `ciudad`                    VARCHAR(100)    NOT NULL DEFAULT '',
  `id_estado`                 TINYINT UNSIGNED NULL,
  `id_municipio`              SMALLINT UNSIGNED NULL,
  `id_categoria`              TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `imagen_principal`          VARCHAR(255)    NULL,
  `imagen_token`              CHAR(40)        NULL,
  `whatsapp`                  VARCHAR(20)     NULL,
  `estado`                    ENUM('pendiente','publicado','rechazado') NOT NULL DEFAULT 'pendiente',
  `destacado`                 TINYINT(1)      NOT NULL DEFAULT 0,
  `fecha_destacado`           DATETIME        NULL,
  `fecha_expiracion_destacado` DATETIME       NULL,
  `vistas`                    INT UNSIGNED    NOT NULL DEFAULT 0,
  `fecha_publicacion`         DATETIME        NULL,
  `fecha_creacion`            DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion`       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_usuario`   (`id_usuario`),
  INDEX `idx_estado`    (`estado`),
  INDEX `idx_categoria` (`id_categoria`),
  INDEX `idx_destacado` (`destacado`),
  INDEX `idx_municipio` (`id_municipio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- Fotos de perfiles (idéntica estructura a anuncio_fotos)
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `perfil_fotos` (
  `id`             INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `id_perfil`      INT UNSIGNED  NOT NULL,
  `token`          CHAR(40)      NOT NULL,
  `nombre_archivo` VARCHAR(255)  NOT NULL,
  `orden`          TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `created_at`     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_token`   (`token`),
  INDEX  `idx_perfil`     (`id_perfil`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- Agregar id_perfil a pagos y reportes (nullable, para el futuro)
-- -------------------------------------------------------------
ALTER TABLE `pagos`    ADD COLUMN `id_perfil` INT UNSIGNED NULL AFTER `id_anuncio`;
ALTER TABLE `reportes` ADD COLUMN `id_perfil` INT UNSIGNED NULL AFTER `id_anuncio`;

SET FOREIGN_KEY_CHECKS = 1;
