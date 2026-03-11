<?php
// Script temporal para probar autocomplete_producto desde CLI (llama directamente al controlador)
chdir(__DIR__);
require_once __DIR__.'/config/app.php';
require_once __DIR__.'/autoload.php';
require_once __DIR__.'/app/views/inc/session_start.php';

use app\controllers\saleController;

$ins = new saleController();
// simular búsqueda
$_POST['term'] = 'Faro';
$result = $ins->autocompleteProductoControlador();
header('Content-Type: application/json; charset=utf-8');
echo $result;
