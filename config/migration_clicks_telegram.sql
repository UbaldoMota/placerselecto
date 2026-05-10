-- =============================================================
-- Migration: perfil_stats — agregar columna clicks_telegram
-- Permite trackear clics al botón de Telegram igual que WhatsApp.
-- Idempotente.
-- =============================================================

SET @col_existe := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME   = 'perfil_stats'
      AND COLUMN_NAME  = 'clicks_telegram'
);

SET @sql := IF(@col_existe = 0,
    'ALTER TABLE `perfil_stats`
        ADD COLUMN `clicks_telegram` INT NOT NULL DEFAULT 0 AFTER `clicks_whatsapp`',
    'SELECT "migration_clicks_telegram: ya aplicada" AS info'
);

PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
