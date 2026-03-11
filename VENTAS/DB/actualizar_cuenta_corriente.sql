-- Actualizar tabla cuenta_corriente_movimiento para agregar nuevos campos
-- Este script agrega soporte para métodos de pago y códigos de comprobante

-- Agregar columnas si no existen
ALTER TABLE `cuenta_corriente_movimiento`
ADD COLUMN `metodo_pago` VARCHAR(50) DEFAULT 'EFECTIVO' AFTER `detalle`,
ADD COLUMN `numero_operacion` VARCHAR(100) DEFAULT '' AFTER `metodo_pago`,
ADD COLUMN `codigo_comprobante` VARCHAR(100) DEFAULT '' AFTER `numero_operacion`;

-- Crear indice para codigo_comprobante
ALTER TABLE `cuenta_corriente_movimiento`
ADD INDEX `idx_codigo_comprobante` (`codigo_comprobante`);

-- Crear indice para búsquedas rápidas
ALTER TABLE `cuenta_corriente_movimiento`
ADD INDEX `idx_cliente_fecha` (`cliente_id`, `fecha`);

-- Verificar la estructura de la tabla
DESCRIBE `cuenta_corriente_movimiento`;
