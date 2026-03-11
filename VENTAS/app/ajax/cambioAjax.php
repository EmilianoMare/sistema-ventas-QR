<?php
// Endpoint deshabilitado: la aplicación opera únicamente en ARS
header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'status' => 'disabled',
    'mensaje' => 'Módulo de tipo de cambio deshabilitado. El sistema opera en ARS.'
]);
exit();

