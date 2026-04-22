-- migration_perfil_oculto.sql
-- Agregar columna "oculta" a perfiles para ocultar sin eliminar.
-- Compatible MySQL 8 y MariaDB 10.3+.

-- En MySQL 8 no existe ADD COLUMN IF NOT EXISTS — se ignora con try/catch en PHP.
ALTER TABLE `perfiles`
    ADD COLUMN `oculta` TINYINT(1) NOT NULL DEFAULT 0 AFTER `estado`;

ALTER TABLE `perfiles`
    ADD INDEX `idx_oculta` (`oculta`);
