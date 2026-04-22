-- ============================================================
-- MIGRACIÓN: Galería de fotos por anuncio + proxy seguro
-- ============================================================
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `anuncio_fotos` (
  `id`             INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `id_anuncio`     INT UNSIGNED    NOT NULL,
  `token`          CHAR(40)        NOT NULL,
  `nombre_archivo` VARCHAR(255)    NOT NULL,
  `orden`          TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `created_at`     TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_token` (`token`),
  KEY `idx_anuncio` (`id_anuncio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `anuncios`
  ADD COLUMN `imagen_token` CHAR(40) NULL AFTER `imagen_principal`;
