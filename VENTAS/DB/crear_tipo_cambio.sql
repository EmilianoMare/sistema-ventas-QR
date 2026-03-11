-- =====================================================
-- TABLA DE TIPO DE CAMBIO
-- =====================================================

CREATE TABLE IF NOT EXISTS `tipo_cambio` (
  `cambio_id` INT AUTO_INCREMENT PRIMARY KEY,
  `moneda_origen` VARCHAR(3) NOT NULL DEFAULT 'USD',
  `moneda_destino` VARCHAR(3) NOT NULL DEFAULT 'ARS',
  `valor` DECIMAL(15,4) NOT NULL,
  `fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `fuente` VARCHAR(100) DEFAULT 'Manual',
  UNIQUE KEY `monedas` (`moneda_origen`, `moneda_destino`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

-- Insertar valor inicial
INSERT INTO `tipo_cambio` (`moneda_origen`, `moneda_destino`, `valor`, `fuente`) 
VALUES ('USD', 'ARS', 950.00, 'Manual')
ON DUPLICATE KEY UPDATE `valor` = 950.00;

-- Describir tabla
DESCRIBE `tipo_cambio`;
