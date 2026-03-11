<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/VENTAS/autoload.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/VENTAS/config/server.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/VENTAS/config/app.php');

require_once($_SERVER['DOCUMENT_ROOT'].'/VENTAS/app/pdf/fpdf.php');

// Validar cliente_id
if(!isset($_GET['cliente_id']) || empty($_GET['cliente_id'])){
    die("Cliente no especificado");
}

$cliente_id = intval($_GET['cliente_id']);

// Crear conexión PDO
try {
    $pdo = new \PDO(
        "mysql:host=".DB_SERVER.";dbname=".DB_NAME,
        DB_USER,
        DB_PASS,
        [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
    );
} catch(\PDOException $e) {
    die("Error de conexión a la base de datos: ".$e->getMessage());
}


// Obtener datos del cliente
$stmt = $pdo->prepare("SELECT * FROM cliente WHERE cliente_id = :cliente_id AND cliente_id != 1");
$stmt->execute([':cliente_id' => $cliente_id]);
$cliente = $stmt->fetch(\PDO::FETCH_ASSOC);

if(!$cliente){
    die("Cliente no encontrado");
}

// Obtener movimientos
$stmt = $pdo->prepare("SELECT * FROM cuenta_corriente_movimiento WHERE cliente_id = :cliente_id ORDER BY fecha DESC");
$stmt->execute([':cliente_id' => $cliente_id]);
$movimientos = $stmt->fetchAll(\PDO::FETCH_ASSOC);

// Calcular saldo en tiempo real sumando movimientos
$saldo_usd = 0;
foreach($movimientos as $mov){
    if($mov['tipo'] === 'DEBE'){
        $saldo_usd += floatval($mov['monto']);
    }elseif($mov['tipo'] === 'HABER'){
        $saldo_usd -= floatval($mov['monto']);
    }
}

// Obtener datos de la empresa
$stmt = $pdo->prepare("SELECT * FROM empresa LIMIT 1");
$stmt->execute();
$empresa = $stmt->fetch(\PDO::FETCH_ASSOC);

// Nota: el módulo de tipo de cambio fue deshabilitado; los montos se manejan en ARS

// Crear PDF
$pdf = new FPDF('P','mm','Letter');
$pdf->SetMargins(17,17,17);
$pdf->AddPage();

// Header
$pdf->SetFont('Arial','B',16);
$pdf->SetTextColor(32,100,210);
$pdf->Cell(0,10,iconv("UTF-8", "ISO-8859-1",strtoupper($empresa['empresa_nombre'])),0,1,'L');

$pdf->Ln(5);

$pdf->SetFont('Arial','',10);
$pdf->SetTextColor(39,39,51);
$pdf->Cell(0,6,iconv("UTF-8", "ISO-8859-1",$empresa['empresa_direccion']),0,1,'L');
$pdf->Cell(0,6,iconv("UTF-8", "ISO-8859-1","Teléfono: ".$empresa['empresa_telefono']),0,1,'L');
$pdf->Cell(0,6,iconv("UTF-8", "ISO-8859-1","Email: ".$empresa['empresa_email']),0,1,'L');

$pdf->Ln(8);

// Título
$pdf->SetFont('Arial','B',14);
$pdf->SetTextColor(32,100,210);
$pdf->Cell(0,10,iconv("UTF-8", "ISO-8859-1","RESUMEN DE CUENTA CORRIENTE"),0,1,'C');

$pdf->Ln(5);

// Información del cliente
$pdf->SetFont('Arial','B',11);
$pdf->SetTextColor(39,39,51);
$pdf->Cell(50,7,iconv("UTF-8", "ISO-8859-1","Cliente:"),0,0);
$pdf->SetFont('Arial','',11);
$pdf->Cell(0,7,iconv("UTF-8", "ISO-8859-1",$cliente['cliente_nombre'].' '.$cliente['cliente_apellido']),0,1);

$pdf->SetFont('Arial','B',11);
$pdf->SetTextColor(39,39,51);
$pdf->Cell(50,7,iconv("UTF-8", "ISO-8859-1","Documento:"),0,0);
$pdf->SetFont('Arial','',11);
$pdf->Cell(0,7,iconv("UTF-8", "ISO-8859-1",$cliente['cliente_tipo_documento'].' '.$cliente['cliente_numero_documento']),0,1);

$pdf->SetFont('Arial','B',11);
$pdf->SetTextColor(39,39,51);
$pdf->Cell(50,7,iconv("UTF-8", "ISO-8859-1","Teléfono:"),0,0);
$pdf->SetFont('Arial','',11);
$pdf->Cell(0,7,iconv("UTF-8", "ISO-8859-1",$cliente['cliente_telefono']),0,1);

$pdf->SetFont('Arial','B',11);
$pdf->SetTextColor(39,39,51);
$pdf->Cell(50,7,iconv("UTF-8", "ISO-8859-1","Dirección:"),0,0);
$pdf->SetFont('Arial','',11);
$pdf->Cell(0,7,iconv("UTF-8", "ISO-8859-1",$cliente['cliente_provincia'].', '.$cliente['cliente_ciudad'].', '.$cliente['cliente_direccion']),0,1);

$pdf->Ln(5);

// Información de fecha y saldo
$pdf->SetFont('Arial','B',11);
$pdf->SetTextColor(39,39,51);
$pdf->Cell(50,7,iconv("UTF-8", "ISO-8859-1","Fecha del Resumen:"),0,0);
$pdf->SetFont('Arial','',11);
$pdf->Cell(0,7,iconv("UTF-8", "ISO-8859-1",date('d/m/Y')),0,1);



$pdf->SetFont('Arial','B',11);
$pdf->SetTextColor(39,39,51);
$pdf->Cell(50,7,iconv("UTF-8", "ISO-8859-1","Saldo Actual:"),0,0);
$pdf->SetFont('Arial','',11);
$color = $saldo_usd > 0 ? [220,53,69] : [40,167,69];
$pdf->SetTextColor($color[0],$color[1],$color[2]);
$pdf->Cell(0,7,iconv("UTF-8", "ISO-8859-1",MONEDA_SIMBOLO.number_format($saldo_usd,2).' '.MONEDA_NOMBRE),0,1);


$pdf->Ln(8);

// Sección de productos comprados
$pdf->SetFont('Arial','B',11);
$pdf->SetTextColor(32,100,210);
$pdf->Cell(0,8,iconv("UTF-8", "ISO-8859-1","Productos comprados"),0,1,'L');
$pdf->SetFont('Arial','',9);
$pdf->SetTextColor(39,39,51);

$stmt = $pdo->prepare("SELECT p.producto_nombre, SUM(vd.venta_detalle_cantidad) as cantidad FROM venta_detalle vd INNER JOIN producto p ON vd.producto_id = p.producto_id INNER JOIN venta v ON vd.venta_codigo = v.venta_codigo WHERE v.cliente_id = :cliente_id GROUP BY p.producto_nombre");
$stmt->execute([':cliente_id' => $cliente_id]);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

if(count($productos) > 0){
    foreach($productos as $prod){
        $pdf->Cell(0,6,iconv("UTF-8", "ISO-8859-1",$prod['producto_nombre'].' x '.$prod['cantidad']),0,1,'L');
    }
}else{
    $pdf->Cell(0,6,iconv("UTF-8", "ISO-8859-1","Sin compras registradas."),0,1,'L');
}

// Tabla de movimientos
$pdf->SetFont('Arial','B',10);
$pdf->SetTextColor(255,255,255);
$pdf->SetFillColor(32,100,210);

$pdf->Cell(25,8,iconv("UTF-8", "ISO-8859-1","Fecha"),1,0,'C',true);
$pdf->Cell(20,8,iconv("UTF-8", "ISO-8859-1","Tipo"),1,0,'C',true);
$pdf->Cell(70,8,iconv("UTF-8", "ISO-8859-1","Concepto"),1,0,'L',true);
$pdf->Cell(30,8,iconv("UTF-8", "ISO-8859-1","Monto"),1,0,'R',true);
$pdf->Cell(25,8,iconv("UTF-8", "ISO-8859-1","Método"),1,1,'C',true);

$pdf->SetFont('Arial','',9);
$pdf->SetTextColor(39,39,51);

foreach($movimientos as $mov){
    $tipo = $mov['tipo'] == 'DEBE' ? 'Deuda' : 'Pago';
    $color_tipo = $mov['tipo'] == 'DEBE' ? [220,53,69] : [40,167,69];
    $pdf->Cell(25,7,iconv("UTF-8", "ISO-8859-1",date('d/m/Y', strtotime($mov['fecha']))),1,0,'C');
    $pdf->SetTextColor($color_tipo[0],$color_tipo[1],$color_tipo[2]);
    $pdf->Cell(20,7,iconv("UTF-8", "ISO-8859-1",$tipo),1,0,'C');
    $pdf->SetTextColor(39,39,51);
    $pdf->Cell(70,7,iconv("UTF-8", "ISO-8859-1",substr($mov['detalle'],0,35)),1,0,'L');
    $pdf->Cell(30,7,iconv("UTF-8", "ISO-8859-1",MONEDA_SIMBOLO.number_format($mov['monto'],2)),1,0,'R');
    $metodo = $mov['metodo_pago'] ?? 'N/A';
    $pdf->Cell(25,7,iconv("UTF-8", "ISO-8859-1",$metodo),1,1,'C');
}

$pdf->Ln(10);

// Firma y observaciones
$pdf->SetFont('Arial','I',9);
$pdf->SetTextColor(100,100,100);
$pdf->Cell(0,5,iconv("UTF-8", "ISO-8859-1","Este resumen se genera automáticamente del sistema de cuentas corrientes."),0,1,'C');
$pdf->Cell(0,5,iconv("UTF-8", "ISO-8859-1","Fecha: ".date('d/m/Y H:i:s')),0,1,'C');

// Output
$pdf->Output('D','Resumen_CC_'.$cliente['cliente_nombre'].'_'.$cliente['cliente_apellido'].'_'.date('Ymd').'.pdf');
?>
