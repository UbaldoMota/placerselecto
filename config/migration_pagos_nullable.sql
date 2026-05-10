-- =============================================================
-- Migration: pagos.id_usuario -> NULLABLE con ON DELETE SET NULL
-- Permite anonimizar pagos al hard-delete del usuario (retención fiscal).
-- Idempotente.
-- =============================================================

-- 1. Permitir NULL en pagos.id_usuario si todavía es NOT NULL
SET @es_nullable := (
    SELECT IS_NULLABLE FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME   = 'pagos'
      AND COLUMN_NAME  = 'id_usuario'
);
SET @sql := IF(@es_nullable = 'NO',
    'ALTER TABLE `pagos` MODIFY `id_usuario` INT UNSIGNED NULL DEFAULT NULL',
    'SELECT "pagos.id_usuario ya nullable" AS info'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 2. Cambiar FK de RESTRICT a SET NULL
SET @fk_existe := (
    SELECT COUNT(*) FROM information_schema.REFERENTIAL_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = DATABASE()
      AND TABLE_NAME        = 'pagos'
      AND CONSTRAINT_NAME   = 'fk_pagos_usuario'
);
SET @sql := IF(@fk_existe > 0,
    'ALTER TABLE `pagos` DROP FOREIGN KEY `fk_pagos_usuario`',
    'SELECT "fk_pagos_usuario no existe" AS info'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

ALTER TABLE `pagos`
    ADD CONSTRAINT `fk_pagos_usuario`
        FOREIGN KEY (`id_usuario`) REFERENCES `usuarios`(`id`)
        ON DELETE SET NULL ON UPDATE CASCADE;
