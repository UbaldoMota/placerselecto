-- =====================================================
-- MIGRATION: sistema de notificaciones en tiempo real
-- =====================================================

CREATE TABLE IF NOT EXISTS `notificaciones` (
    `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `id_usuario`      INT UNSIGNED NOT NULL,
    `tipo`            VARCHAR(50)  NOT NULL,
    `titulo`          VARCHAR(150) NOT NULL,
    `mensaje`         VARCHAR(500) NOT NULL,
    `url`             VARCHAR(300) DEFAULT NULL,
    `icono`           VARCHAR(50)  NOT NULL DEFAULT 'bell',
    `color`           VARCHAR(20)  NOT NULL DEFAULT 'primary',
    `leida`           TINYINT(1)   NOT NULL DEFAULT 0,
    `fecha_creacion`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `fecha_lectura`   DATETIME     DEFAULT NULL,
    KEY `idx_user_unread` (`id_usuario`, `leida`, `fecha_creacion`),
    KEY `idx_user_created` (`id_usuario`, `fecha_creacion`),
    CONSTRAINT `fk_notif_usuario` FOREIGN KEY (`id_usuario`)
        REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
