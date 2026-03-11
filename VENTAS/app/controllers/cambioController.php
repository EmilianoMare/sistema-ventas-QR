<?php

namespace app\controllers;
use app\models\mainModel;

class cambioController extends mainModel{

    // Este controlador se ha reducido a funciones neutras porque la aplicación
    // ahora opera exclusivamente en ARS. Las funciones devuelven valores
    // inofensivos para mantener compatibilidad mínima con llamadas existentes.

    public function isApiEnabled(){
        return false;
    }

    public function obtenerTipoCambio(){
        return 1.00; // Sin conversión: factor 1
    }

    public function obtenerInfoTipoCambio(){
        return [
            'valor' => 1.00,
            'fecha_actualizacion' => date('Y-m-d H:i:s'),
            'fuente' => 'Deshabilitado'
        ];
    }

    public function actualizarTipoCambioControlador(){
        return false; // Operación no disponible
    }

    public function obtenerTipoCambioDesdeAPI(){
        return [
            'status' => 'disabled',
            'mensaje' => 'Módulo de tipo de cambio deshabilitado'
        ];
    }

    public function convertirUsdArs($monto_usd){
        // Sin conversión: tratar el monto recibido como ARS
        return floatval($monto_usd);
    }

    public function convertirArsUsd($monto_ars){
        // Sin conversión: devolver 0 para evitar usos incorrectos
        return 0;
    }

}
