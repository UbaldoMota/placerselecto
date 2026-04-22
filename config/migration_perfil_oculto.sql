-- migration_perfil_oculto.sql
-- - Agrega perfiles.oculta
-- - Agrega reportes.url_referencia (faltaba y rompía el reporte de perfiles)

ALTER TABLE `perfiles`
    ADD COLUMN `oculta` TINYINT(1) NOT NULL DEFAULT 0 AFTER `estado`;

ALTER TABLE `perfiles`
    ADD INDEX `idx_oculta` (`oculta`);

ALTER TABLE `reportes`
    ADD COLUMN `url_referencia` VARCHAR(500) DEFAULT NULL AFTER `descripcion`;
