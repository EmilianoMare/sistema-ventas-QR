<?php
$pagina = explode("/", $_GET['views'] ?? '');
$id = $pagina[1] ?? 0;

if($id <= 0){
    echo '<div class="notification is-danger">Cliente inválido</div>';
    exit;
}
?>

<div class="container is-fluid mb-6">
    <h1 class="title">Movimientos de Cuenta Corriente</h1>
</div>

<div class="container pb-6 pt-6">
    <div class="table-responsive"><table class="table is-bordered is-striped is-fullwidth">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Detalle</th>
                <th>Debe</th>
                <th>Haber</th>
            </tr>
        </thead>
        <tbody>
        <?php
use app\controllers\clientController;

$insCliente = new clientController();

$mov = $insCliente->listarMovimientosCuentaCorriente($id);

while($row=$mov->fetch()){
    $monto = floatval($row['monto']);
    echo '
    <tr>
        <td>'.$row['fecha'].'</td>
        <td>'.$row['detalle'].'</td>
        <td>'.($row['tipo']=="DEBE"?'<strong>ARS:</strong> $'.number_format($monto,2):'<span style="opacity:0.5">-</span>').'</td>
        <td>'.($row['tipo']=="HABER"?'<strong>ARS:</strong> $'.number_format($monto,2):'<span style="opacity:0.5">-</span>').'</td>
    </tr>';
}
?>
        </tbody>
    </table></div>
</div>