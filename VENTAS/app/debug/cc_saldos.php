<?php
require_once __DIR__.'/../../config/server.php';
try{
    $pdo = new PDO('mysql:host='.DB_SERVER.';dbname='.DB_NAME, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
}catch(PDOException $e){
    die('DB error: '.$e->getMessage());
}

// Mostrar filas de cuenta_corriente
$stmt = $pdo->query("SELECT * FROM cuenta_corriente");
$cc = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Mostrar sumatorios por movimientos
$stmt2 = $pdo->query("SELECT cliente_id, IFNULL(SUM(CASE WHEN tipo='DEBE' THEN monto WHEN tipo='HABER' THEN -monto ELSE 0 END),0) AS saldo_mov FROM cuenta_corriente_movimiento GROUP BY cliente_id");
$mov = $stmt2->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode(['cuenta_corriente'=>$cc, 'saldos_por_movimientos'=>$mov], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
