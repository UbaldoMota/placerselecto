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

-- Seeds iniciales (se insertan solo si no existen ya)
INSERT IGNORE INTO `configuracion` (`clave`, `valor`, `descripcion`, `tipo`) VALUES
('whatsapp_pagos',   '', 'Numero de WhatsApp para coordinar pagos (formato internacional sin signos, ej. 5215555555555). Aqui se redirige al usuario al hacer clic en Pagar.', 'telefono'),
('whatsapp_soporte', '', 'Numero de WhatsApp para soporte general. Si esta vacio, se usa el de pagos.', 'telefono'),
('email_pagos',      '', 'Email para correos de soporte de pagos. Si esta vacio, se usa admin_notify_email del env.', 'email');
