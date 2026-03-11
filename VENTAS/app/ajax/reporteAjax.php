<?php

    require_once "../../config/app.php";
    require_once "../views/inc/session_start.php";
    require_once "../../autoload.php";

    use app\controllers\reportController;

    if(isset($_POST['modulo_reporte'])){

        // Forzamos tipo de respuesta JSON para las peticiones AJAX
        header('Content-Type: application/json; charset=utf-8');

        $insReporte = new reportController();

        $mod = $_POST['modulo_reporte'];
        switch($mod){
            case 'topProducts': echo $insReporte->topProductsControlador(); break;
            case 'topClients': echo $insReporte->topClientsControlador(); break;
            case 'totals': echo $insReporte->totalsControlador(); break;
            case 'salesByProduct': echo $insReporte->salesByProductControlador(); break;
            case 'salesByCategory': echo $insReporte->salesByCategoryControlador(); break;
            case 'clientHistory': echo $insReporte->clientHistoryControlador(); break;
            case 'totalPerClient': echo $insReporte->totalPerClientControlador(); break;
            case 'lowStock': echo $insReporte->lowStockControlador(); break;
            case 'listProducts': echo $insReporte->listProductsControlador(); break;
            case 'listCategories': echo $insReporte->listCategoriesControlador(); break;
            case 'listClients': echo $insReporte->listClientsControlador(); break;
            default: echo json_encode([]);
        }

    }else{
        session_destroy();
        header("Location: ".APP_URL."login/");
    }
