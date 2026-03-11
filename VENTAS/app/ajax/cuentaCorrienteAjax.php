<?php

use app\controllers\cuentaCorrienteController;

// ¡IMPORTANTE! Este debe ser el PRIMER código - sin espacios en blanco antes
ob_start();
header('Content-Type: application/json; charset=utf-8');

try {
    require_once "../../config/app.php";
    require_once "../../autoload.php";
    require_once "../views/inc/session_start.php";

    // Limpiar cualquier salida anterior
    ob_end_clean();

    $insCC = new cuentaCorrienteController();

    if(isset($_POST['modulo_cc'])){

        if($_POST['modulo_cc'] == "registrar_pago"){
            echo $insCC->registrarPagoControlador();
        } else {
            echo json_encode([
                "tipo" => "simple",
                "titulo" => "Módulo Inválido",
                "texto" => "El módulo especificado no existe",
                "icono" => "error"
            ]);
        }
    } else {
        echo json_encode([
            "tipo" => "simple",
            "titulo" => "Parámetro Faltante",
            "texto" => "No se especificó el módulo a ejecutar",
            "icono" => "error"
        ]);
    }

} catch (\Throwable $e) {
    // Captura Exception, Error, ParseError, etc.
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        "tipo" => "simple",
        "titulo" => "Error en Servidor",
        "texto" => $e->getMessage(),
        "icono" => "error"
    ]);
} catch (\Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        "tipo" => "simple",
        "titulo" => "Error",
        "texto" => $e->getMessage(),
        "icono" => "error"
    ]);
}