-- =====================================================
-- MEJORAR TABLA cuenta_corriente_movimiento (OPCIONAL)
-- =====================================================
-- Este script añade columnas opcionales para métodos de pago
-- Si las columnas ya existen, no causará error

-- Agregar columnas si no existen
ALTER TABLE `cuenta_corriente_movimiento`
ADD COLUMN `usuario_id` INT(7) AFTER `fecha`,
ADD COLUMN `metodo_pago` VARCHAR(50) DEFAULT 'EFECTIVO' AFTER `usuario_id`,
ADD COLUMN `numero_operacion` VARCHAR(100) DEFAULT '' AFTER `metodo_pago`;

-- Si hubo error en la línea anterior (columnas ya existen), ignóralo
-- El sistema seguirá funcionando sin estos campos

-- Verificar estructura final
DESCRIBE `cuenta_corriente_movimiento`;
