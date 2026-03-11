-- Script para crear tabla 'paquetes'
CREATE TABLE IF NOT EXISTS `paquetes` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `direccion` TEXT,
  `referencia` TEXT,
  `barrio` VARCHAR(150),
  `destinatario` VARCHAR(200),
  `creado_en` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
