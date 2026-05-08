-- =============================================================
-- migration_token_destacado.sql
-- Agrega columna `destacado` a `token_paquetes` (idempotente).
-- Marca el paquete "Activa" (549 MXN) como destacado para mostrar
-- el badge "MAS POPULAR" en la pagina de compra.
-- =============================================================

ALTER TABLE `token_paquetes`
  ADD COLUMN IF NOT EXISTS `destacado` TINYINT(1) NOT NULL DEFAULT 0
  COMMENT 'Marca el paquete como MAS POPULAR en la UI'
  AFTER `bonus_pct`;

-- Reset previo (idempotente)
UPDATE `token_paquetes` SET `destacado` = 0 WHERE `activo` = 1;

-- Marcar el paquete "Activa" (segundo nivel, 549 MXN) como destacado
UPDATE `token_paquetes` SET `destacado` = 1 WHERE `nombre` = 'Activa' AND `activo` = 1;
