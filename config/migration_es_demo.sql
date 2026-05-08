-- =============================================================
-- migration_es_demo.sql
-- Agrega columna `es_demo` a `perfiles` (idempotente) y marca
-- los 20 perfiles más recientes como demo.
-- Ejecutar: mysql -u root clasificados_adultos < migration_es_demo.sql
-- En prod se aplica via script one-shot (ver reference_deploy.md).
-- =============================================================

-- Idempotente: solo agrega si no existe (MySQL 8.0.29+)
ALTER TABLE `perfiles`
  ADD COLUMN IF NOT EXISTS `es_demo` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0
  COMMENT 'Perfil seed/demostrativo: muestra badge "Perfil de muestra" en UI'
  AFTER `estado`;

-- Marcar los 20 más recientes (por id, que es AUTO_INCREMENT = orden de creación).
-- Reset previo solo en filas afectadas para mantener idempotencia si se vuelve a correr.
UPDATE `perfiles`
   SET `es_demo` = 1
 WHERE `id` IN (
   SELECT `id` FROM (
     SELECT `id` FROM `perfiles` ORDER BY `id` DESC LIMIT 20
   ) AS sub
 );
