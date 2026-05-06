-- =============================================================
-- migration_contactos.sql
-- Tabla para mensajes recibidos a través del formulario público /contacto.
-- A diferencia de soporte_mensajes, NO requiere usuario registrado.
-- Idempotente: seguro correr varias veces.
-- =============================================================

CREATE TABLE IF NOT EXISTS `contacto_mensajes` (
    `id`                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `nombre`              VARCHAR(80)   NOT NULL,
    `email`               VARCHAR(180)  NOT NULL,
    `asunto`              ENUM('soporte','pagos','reporte','legal','otro') NOT NULL DEFAULT 'otro',
    `mensaje`             TEXT          NOT NULL,
    `ip`                  VARCHAR(45)   DEFAULT NULL,
    `leido`               TINYINT(1)    NOT NULL DEFAULT 0,
    `fecha_creacion`      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `fecha_actualizacion` DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    KEY `idx_leido` (`leido`),
    KEY `idx_fecha` (`fecha_creacion`),
    KEY `idx_email` (`email`),
    KEY `idx_asunto` (`asunto`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
