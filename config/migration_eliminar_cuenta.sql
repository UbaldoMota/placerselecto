-- =============================================================
-- Migration: cuenta — soft delete + grace period 30 días
-- Idempotente: usa IF NOT EXISTS sentinel.
-- =============================================================

-- Solo aplica si las columnas no existen aún
SET @col_existe := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME   = 'usuarios'
      AND COLUMN_NAME  = 'eliminado_at'
);

SET @sql := IF(@col_existe = 0,
    'ALTER TABLE `usuarios`
        ADD COLUMN `eliminado_at`                 DATETIME NULL DEFAULT NULL AFTER `fecha_actualizacion`,
        ADD COLUMN `eliminacion_programada_para`  DATETIME NULL DEFAULT NULL AFTER `eliminado_at`,
        ADD COLUMN `eliminacion_token`            VARCHAR(100) NULL DEFAULT NULL AFTER `eliminacion_programada_para`,
        ADD KEY `idx_eliminacion` (`eliminado_at`, `eliminacion_programada_para`)',
    'SELECT "migration_eliminar_cuenta: ya aplicada" AS info'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
