-- =====================================================
-- MIGRATION: mensajes de soporte (solicitud de reactivación, etc.)
-- =====================================================

-- 1. Añadir estado 'suspendido' al enum de verificación
ALTER TABLE `usuarios`
    MODIFY COLUMN `estado_verificacion`
    ENUM('pendiente','aprobado','rechazado','suspendido')
    NOT NULL DEFAULT 'pendiente';

-- 2. Tabla de mensajes del usuario al admin
CREATE TABLE IF NOT EXISTS `soporte_mensajes` (
    `id`                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `id_usuario`          INT UNSIGNED NOT NULL,
    `tipo`                ENUM('reactivacion','general','duda','reporte_problema') NOT NULL DEFAULT 'general',
    `asunto`              VARCHAR(150) NOT NULL,
    `mensaje`             TEXT NOT NULL,
    `estado`              ENUM('abierto','respondido','cerrado') NOT NULL DEFAULT 'abierto',
    `respuesta_admin`     TEXT DEFAULT NULL,
    `id_admin_respuesta`  INT UNSIGNED DEFAULT NULL,
    `fecha_creacion`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `fecha_respuesta`     DATETIME DEFAULT NULL,
    `ip_envio`            VARCHAR(45) DEFAULT NULL,
    KEY `idx_user_fecha`  (`id_usuario`, `fecha_creacion`),
    KEY `idx_estado_fecha`(`estado`, `fecha_creacion`),
    KEY `idx_tipo`        (`tipo`),
    CONSTRAINT `fk_soporte_user` FOREIGN KEY (`id_usuario`)
        REFERENCES `usuarios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
