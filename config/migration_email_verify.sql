-- migration_email_verify.sql
-- Campos para verificación de email por link al registrarse

ALTER TABLE `usuarios`
    ADD COLUMN `email_verificado` TINYINT(1) NOT NULL DEFAULT 0 AFTER `verificado`;

ALTER TABLE `usuarios`
    ADD COLUMN `email_verify_token` VARCHAR(64) DEFAULT NULL AFTER `email_verificado`;

ALTER TABLE `usuarios`
    ADD COLUMN `email_verified_at` DATETIME DEFAULT NULL AFTER `email_verify_token`;

ALTER TABLE `usuarios`
    ADD INDEX `idx_email_verify_token` (`email_verify_token`);
