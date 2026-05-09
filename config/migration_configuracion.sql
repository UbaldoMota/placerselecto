-- =============================================================
-- migration_configuracion.sql
-- Tabla key/value para configuracion editable desde panel admin
-- (numeros de WhatsApp de pagos/soporte, emails de notificacion,
-- textos que cambian con el tiempo, etc.).
-- =============================================================

CREATE TABLE IF NOT EXISTS `configuracion` (
    `clave`               VARCHAR(80)   NOT NULL PRIMARY KEY,
    `valor`               TEXT          NULL,
    `descripcion`         VARCHAR(255)  NULL,
    `tipo`                ENUM('texto','telefono','email','url','numero','booleano','textarea') NOT NULL DEFAULT 'texto',
    `fecha_actualizacion` DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed inicial (se inserta solo si no existe ya)
INSERT IGNORE INTO `configuracion` (`clave`, `valor`, `descripcion`, `tipo`) VALUES
('whatsapp_pagos', '', 'Numero de WhatsApp al que se redirige al usuario al hacer clic en Pagar (formato internacional sin signos, ej. 5215555555555).', 'telefono');
