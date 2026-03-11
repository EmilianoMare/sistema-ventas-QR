-- =====================================================
-- CREAR TABLAS PARA CUENTA CORRIENTE
-- =====================================================
-- Este script crea las tablas necesarias para el sistema de cuenta corriente

-- ① Crear tabla de cuentas corrientes
CREATE TABLE IF NOT EXISTS `cuenta_corriente` (
  `cuenta_id` int(11) NOT NULL AUTO_INCREMENT,
  `cliente_id` int(10) NOT NULL,
  `saldo` decimal(30,2) NOT NULL DEFAULT 0.00,
  `fecha_creacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`cuenta_id`),
  UNIQUE KEY `cliente_id` (`cliente_id`),
  FOREIGN KEY (`cliente_id`) REFERENCES `cliente`(`cliente_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

-- ② Crear tabla de movimientos de cuenta corriente
CREATE TABLE IF NOT EXISTS `cuenta_corriente_movimiento` (
  `movimiento_id` int(11) NOT NULL AUTO_INCREMENT,
  `cliente_id` int(10) NOT NULL,
  `tipo` enum('DEBE','HABER') NOT NULL,
  `monto` decimal(30,2) NOT NULL,
  `detalle` varchar(500) COLLATE utf8_spanish2_ci NOT NULL,
  `fecha` date NOT NULL,
  `usuario_id` int(7) NOT NULL,
  `metodo_pago` varchar(50) COLLATE utf8_spanish2_ci DEFAULT 'EFECTIVO',
  `numero_operacion` varchar(100) COLLATE utf8_spanish2_ci DEFAULT '',
  `codigo_comprobante` varchar(100) COLLATE utf8_spanish2_ci DEFAULT '',
  `fecha_registro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`movimiento_id`),
  KEY `cliente_id` (`cliente_id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `idx_cliente_fecha` (`cliente_id`, `fecha`),
  KEY `idx_codigo_comprobante` (`codigo_comprobante`),
  FOREIGN KEY (`cliente_id`) REFERENCES `cliente`(`cliente_id`),
  FOREIGN KEY (`usuario_id`) REFERENCES `usuario`(`usuario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

-- =====================================================
-- CONSULTAS PARA VERIFICAR
-- =====================================================
-- Verificar estructura de las tablas creadas
DESCRIBE `cuenta_corriente`;
DESCRIBE `cuenta_corriente_movimiento`;

-- Mostrar tablas relacionadas
SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES 
WHERE TABLE_SCHEMA = 'ventas' AND TABLE_NAME LIKE 'cuenta%';
