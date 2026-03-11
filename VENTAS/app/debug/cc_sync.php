<?php
require_once __DIR__.'/../../config/server.php';

try{
    $pdo = new PDO('mysql:host='.DB_SERVER.';dbname='.DB_NAME, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
}catch(PDOException $e){
    die('DB error: '.$e->getMessage());
}

header('Content-Type: application/json');

// Obtener suma de movimientos por cliente
$stmt = $pdo->query("SELECT cliente_id, IFNULL(SUM(CASE WHEN tipo='DEBE' THEN monto WHEN tipo='HABER' THEN -monto ELSE 0 END),0) as saldo_mov FROM cuenta_corriente_movimiento GROUP BY cliente_id");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$report = ['updated'=>[], 'inserted'=>[], 'skipped'=>[], 'errors'=>[]];

foreach($rows as $r){
    $cliente_id = $r['cliente_id'];
    $saldo_mov = $r['saldo_mov'];

    // Verificar si existe fila en cuenta_corriente
    $check = $pdo->prepare('SELECT cc_id, saldo FROM cuenta_corriente WHERE cliente_id = :cid');
    $check->execute([':cid'=>$cliente_id]);
    $exist = $check->fetch(PDO::FETCH_ASSOC);

    if($exist){
        $old = $exist['saldo'];
        if(number_format($old,2,'.','') != number_format($saldo_mov,2,'.','')){
            $up = $pdo->prepare('UPDATE cuenta_corriente SET saldo = :saldo WHERE cliente_id = :cid');
            $up->execute([':saldo'=>$saldo_mov, ':cid'=>$cliente_id]);
            $report['updated'][] = ['cliente_id'=>$cliente_id,'old'=>$old,'new'=>$saldo_mov];
        }else{
            $report['skipped'][] = ['cliente_id'=>$cliente_id,'saldo'=>$saldo_mov];
        }
    }else{
        // Insertar nueva fila
        $ins = $pdo->prepare('INSERT INTO cuenta_corriente (cliente_id, saldo) VALUES (:cid, :saldo)');
        $ins->execute([':cid'=>$cliente_id, ':saldo'=>$saldo_mov]);
        $report['inserted'][] = ['cliente_id'=>$cliente_id,'saldo'=>$saldo_mov];
    }
}

// Además, detectar clientes con cuenta_corriente pero sin movimientos
$stmt2 = $pdo->query('SELECT cc_id, cliente_id, saldo FROM cuenta_corriente');
$allcc = $stmt2->fetchAll(PDO::FETCH_ASSOC);
foreach($allcc as $cc){
    $cid = $cc['cliente_id'];
    $found = false;
    foreach($rows as $r){ if($r['cliente_id']==$cid){ $found=true; break; }}
    if(!$found){
        // Si no hay movimientos, mantener saldo tal cual (no tocar), reportar
        $report['no_movements'][] = ['cliente_id'=>$cid,'saldo'=>$cc['saldo']];
    }
}

echo json_encode($report, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
