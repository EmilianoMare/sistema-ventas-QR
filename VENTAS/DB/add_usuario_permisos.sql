-- Agregar columna usuario_permisos para control de accesos por usuario
ALTER TABLE `usuario` ADD COLUMN `usuario_permisos` TEXT NOT NULL AFTER `caja_id`;