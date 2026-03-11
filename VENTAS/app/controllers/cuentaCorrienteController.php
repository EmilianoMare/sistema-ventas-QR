<?php

namespace app\controllers;
use app\models\mainModel;

class cuentaCorrienteController extends mainModel{

    public function registrarPagoControlador(){

        // Verificar sesión del usuario
        if(!isset($_SESSION['id'])){
            return json_encode([
                "tipo" => "simple",
                "titulo" => "Error de Sesión",
                "texto" => "Sesión expirada. Por favor inicia sesión de nuevo.",
                "icono" => "error"
            ]);
        }

        // Obtener y limpiar datos
        $cliente_id = $this->limpiarCadena($_POST['cliente_id'] ?? '');
        $monto = $this->limpiarCadena($_POST['monto'] ?? '');
        $fecha_pago_str = $this->limpiarCadena($_POST['fecha_pago'] ?? date('Y-m-d'));
        $metodo_pago = $this->limpiarCadena($_POST['metodo_pago'] ?? 'EFECTIVO');
        $numero_operacion = $this->limpiarCadena($_POST['numero_operacion'] ?? '');
        $detalle = $this->limpiarCadena($_POST['detalle'] ?? '');
        $generar_comprobante = isset($_POST['generar_comprobante']) ? 1 : 0;

        // Validar que la fecha sea válida y no sea futura
        $hoy = date('Y-m-d'); // Fecha de hoy en Buenos Aires
        if($fecha_pago_str > $hoy){
            return json_encode([
                "tipo" => "simple",
                "titulo" => "Error",
                "texto" => "La fecha del pago no puede ser futura",
                "icono" => "error"
            ]);
        }

        // Convertir fecha a DATETIME (agregar hora: 12:00:00)
        $fecha_pago = date('Y-m-d H:i:s', strtotime($fecha_pago_str . ' 12:00:00'));

        // Validaciones básicas
        if($cliente_id == "" || empty($monto) || $monto <= 0){
            return json_encode([
                "tipo" => "simple",
                "titulo" => "Datos Inválidos",
                "texto" => "Cliente y monto son obligatorios. El monto debe ser mayor a 0.",
                "icono" => "error"
            ]);
        }

        // Convertir monto a número
        $monto = floatval($monto);
        $cliente_id = intval($cliente_id);

        // Validar que el cliente exista
        $check_cliente = $this->ejecutarConsulta("SELECT cliente_id FROM cliente WHERE cliente_id='$cliente_id' AND cliente_id!='1'");
        if($check_cliente->rowCount() == 0){
            return json_encode([
                "tipo" => "simple",
                "titulo" => "Cliente No Válido",
                "texto" => "El cliente seleccionado no existe o no es válido",
                "icono" => "error"
            ]);
        }

        // Verificar que existe cuenta corriente
        $check_cc = $this->ejecutarConsulta("SELECT saldo FROM cuenta_corriente WHERE cliente_id='$cliente_id'");
        
        if($check_cc->rowCount() == 0){
            return json_encode([
                "tipo" => "simple",
                "titulo" => "Sin Cuenta Corriente",
                "texto" => "El cliente seleccionado no tiene cuenta corriente. Crear una venta con forma de pago 'Cuenta Corriente' primero.",
                "icono" => "error"
            ]);
        }

        $cc = $check_cc->fetch();
        $deuda_pendiente = floatval($cc['saldo']);
        
        if($monto > $deuda_pendiente){
            return json_encode([
                "tipo" => "simple",
                "titulo" => "Error",
                "texto" => "El monto del pago ($ " . number_format($monto, 2) . ") no puede exceder la deuda pendiente ($ " . number_format($deuda_pendiente, 2) . ")",
                "icono" => "error"
            ]);
        }

        // Validar método de pago y número de operación
        if(($metodo_pago == 'CHEQUE' || $metodo_pago == 'TRANSFERENCIA') && empty($numero_operacion)){
            return json_encode([
                "tipo" => "simple",
                "titulo" => "Error",
                "texto" => "Debes proporcionar el número de cheque o referencia de transferencia",
                "icono" => "error"
            ]);
        }

        // Generar código de comprobante
        $codigo_comprobante = 'PAG-' . date('YmdHis') . '-' . $cliente_id;

        // Construir detalle completo
        $detalle_completo = 'PAGO RECIBIDO - ' . $metodo_pago;
        if($numero_operacion){
            $detalle_completo .= ' (' . $numero_operacion . ')';
        }
        if($detalle){
            $detalle_completo .= ' - ' . $detalle;
        }

        try {
            // Obtener usuario_id de sesión, si no existe usar 1
            $usuario_id = isset($_SESSION['id']) ? $_SESSION['id'] : 1;
            
            // Escapar detalle para evitar problemas con comillas
            $detalle_escapado = str_replace("'", "''", $detalle_completo);
            
            // Validar que la fecha esté en formato correcto
            if(!strtotime($fecha_pago)){
                throw new \Exception("Fecha inválida: " . $fecha_pago);
            }
            
            // Registrar el movimiento incluyendo el usuario que registró el pago
            $insert_query = "
                INSERT INTO cuenta_corriente_movimiento
                (cliente_id, tipo, monto, detalle, fecha, usuario_id)
                VALUES
                ($cliente_id, 'HABER', $monto, '$detalle_escapado', '$fecha_pago', $usuario_id)
            ";

            $this->ejecutarConsulta($insert_query);

            // Actualizar saldo de la cuenta corriente
            $nuevo_saldo = $deuda_pendiente - $monto;
            $update_query = "
                UPDATE cuenta_corriente
                SET saldo = $nuevo_saldo
                WHERE cliente_id = $cliente_id
            ";
            
            $this->ejecutarConsulta($update_query);

            // Preparar respuesta de éxito
            return json_encode([
                "tipo" => "recargar",
                "titulo" => "¡Éxito!",
                "texto" => "Pago de " . MONEDA_SIMBOLO . number_format($monto, 2) . " registrado. Nuevo saldo: " . MONEDA_SIMBOLO . number_format($nuevo_saldo, 2),
                "icono" => "success"
            ]);

        } catch (\Throwable $e) {
            // Capturar cualquier error (Exception o Error)
            return json_encode([
                "tipo" => "simple",
                "titulo" => "Error al Registrar Pago",
                "texto" => "Error: " . $e->getMessage(),
                "icono" => "error"
            ]);
        }
    }

    public function obtenerDeudaCliente($cliente_id){
        $cliente_id = $this->limpiarCadena($cliente_id);
        
        $consulta = "SELECT saldo FROM cuenta_corriente WHERE cliente_id='$cliente_id'";
        $resultado = $this->ejecutarConsulta($consulta);
        
        if($resultado->rowCount() > 0){
            $row = $resultado->fetch();
            return $row['saldo'];
        }
        return 0;
    }

    public function listarMovimientosRecientes($limite = 10){
        $limite = (int)$limite;
        
        $consulta = "
            SELECT 
                ccm.*,
                c.cliente_nombre,
                c.cliente_apellido,
                u.usuario_nombre,
                u.usuario_apellido
            FROM cuenta_corriente_movimiento ccm
            INNER JOIN cliente c ON ccm.cliente_id = c.cliente_id
            INNER JOIN usuario u ON ccm.usuario_id = u.usuario_id
            ORDER BY ccm.fecha DESC
            LIMIT $limite
        ";
        
        return $this->ejecutarConsulta($consulta);
    }

}