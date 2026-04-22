-- =====================================================
-- MIGRATION: sistema de tokens + boosts programables
-- =====================================================

-- 1. Saldo de tokens del usuario
ALTER TABLE `usuarios`
    ADD COLUMN `saldo_tokens` INT UNSIGNED NOT NULL DEFAULT 0
    AFTER `verificado`;

-- 2. Paquetes de recarga (admin-editable)
CREATE TABLE IF NOT EXISTS `token_paquetes` (
    `id`                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `nombre`              VARCHAR(80)   NOT NULL,
    `monto_mxn`           DECIMAL(10,2) NOT NULL,
    `tokens`              INT UNSIGNED  NOT NULL,
    `bonus_pct`           INT UNSIGNED  NOT NULL DEFAULT 0,
    `orden`               INT UNSIGNED  NOT NULL DEFAULT 0,
    `activo`              TINYINT(1)    NOT NULL DEFAULT 1,
    `fecha_creacion`      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `fecha_actualizacion` DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY `idx_activo_orden` (`activo`, `orden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tarifas de consumo (admin-editable, solo 2 filas: top y resaltado)
CREATE TABLE IF NOT EXISTS `token_tarifas` (
    `id`                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tipo`                ENUM('top','resaltado') NOT NULL UNIQUE,
    `tokens_por_hora`     INT UNSIGNED NOT NULL,
    `descripcion`         VARCHAR(200) DEFAULT NULL,
    `fecha_actualizacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Ledger — cada +/- de tokens
CREATE TABLE IF NOT EXISTS `tokens_movimientos` (
    `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `id_usuario`     INT UNSIGNED NOT NULL,
    `tipo`           ENUM('recarga','consumo','reembolso','ajuste_admin') NOT NULL,
    `cantidad`       INT NOT NULL,
    `saldo_despues`  INT UNSIGNED NOT NULL,
    `id_pago`        INT UNSIGNED DEFAULT NULL,
    `id_boost`       INT UNSIGNED DEFAULT NULL,
    `descripcion`    VARCHAR(255) DEFAULT NULL,
    `fecha`          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_user_fecha` (`id_usuario`, `fecha`),
    KEY `idx_tipo_fecha` (`tipo`, `fecha`),
    CONSTRAINT `fk_mov_user` FOREIGN KEY (`id_usuario`)
        REFERENCES `usuarios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Boosts (ventanas de destacado/resaltado por perfil)
CREATE TABLE IF NOT EXISTS `perfil_boost` (
    `id`               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `id_perfil`        INT UNSIGNED NOT NULL,
    `tipo`             ENUM('top','resaltado') NOT NULL,
    `inicio`           DATETIME NOT NULL,
    `fin`              DATETIME NOT NULL,
    `tokens_gastados`  INT UNSIGNED NOT NULL DEFAULT 0,
    `estado`           ENUM('programado','activo','finalizado','cancelado') NOT NULL DEFAULT 'programado',
    `es_legacy`        TINYINT(1) NOT NULL DEFAULT 0,
    `fecha_creacion`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `fecha_cancelacion` DATETIME DEFAULT NULL,
    KEY `idx_active` (`estado`, `inicio`, `fin`),
    KEY `idx_perfil_estado` (`id_perfil`, `estado`),
    CONSTRAINT `fk_boost_perfil` FOREIGN KEY (`id_perfil`)
        REFERENCES `perfiles`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Seed: paquetes iniciales
INSERT INTO `token_paquetes` (`nombre`, `monto_mxn`, `tokens`, `bonus_pct`, `orden`, `activo`) VALUES
    ('Paquete Básico',   99.00, 100,  0, 1, 1),
    ('Paquete Plus',    199.00, 230, 15, 2, 1),
    ('Paquete Pro',     349.00, 450, 28, 3, 1),
    ('Paquete Premium', 599.00, 850, 42, 4, 1)
ON DUPLICATE KEY UPDATE `id` = `id`;

-- 7. Seed: tarifas de consumo
INSERT INTO `token_tarifas` (`tipo`, `tokens_por_hora`, `descripcion`) VALUES
    ('top',       3, 'Aparecer primero en los listados de tu municipio'),
    ('resaltado', 2, 'Resaltar visualmente el perfil sin subir en orden')
ON DUPLICATE KEY UPDATE `tipo` = `tipo`;

-- 8. Migrar perfiles con destacado=1 activo → boost legacy
INSERT INTO `perfil_boost`
    (`id_perfil`, `tipo`, `inicio`, `fin`, `tokens_gastados`, `estado`, `es_legacy`)
SELECT
    `id`,
    'top',
    COALESCE(`fecha_destacado`, NOW()),
    `fecha_expiracion_destacado`,
    0,
    'activo',
    1
FROM `perfiles`
WHERE `destacado` = 1
  AND `fecha_expiracion_destacado` IS NOT NULL
  AND `fecha_expiracion_destacado` > NOW();
