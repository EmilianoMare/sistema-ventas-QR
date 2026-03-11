<?php

	$code=(isset($_GET['code'])) ? $_GET['code'] : 0;

	/*---------- Incluyendo configuraciones ----------*/
    require_once "../../config/app.php";
    require_once "../../autoload.php";

	/*---------- Instancia al controlador venta ----------*/
	use app\controllers\saleController;
	$ins_venta = new saleController();

	$datos_venta=$ins_venta->seleccionarDatos("Normal","venta INNER JOIN cliente ON venta.cliente_id=cliente.cliente_id INNER JOIN usuario ON venta.usuario_id=usuario.usuario_id INNER JOIN caja ON venta.caja_id=caja.caja_id WHERE (venta_codigo='$code')","*",0);

	if($datos_venta->rowCount()==1){
        
		/*---------- Datos de la venta ----------*/
		$datos_venta=$datos_venta->fetch();

		/*---------- Seleccion de datos de la empresa ----------*/
		$datos_empresa=$ins_venta->seleccionarDatos("Normal","empresa LIMIT 1","*",0);
		$datos_empresa=$datos_empresa->fetch();

		require "./fpdf.php";

		$pdf = new FPDF('P','mm','A4');
		$pdf->AddPage();
		$pdf->SetFont('Arial','B',14);
		$pdf->SetTextColor(0,0,0);
		
		// Encabezado
		$pdf->Cell(0,10,iconv("UTF-8", "ISO-8859-1",strtoupper($datos_empresa['empresa_nombre'])),0,1,'C');
		$pdf->SetFont('Arial','',10);
		$pdf->Cell(0,5,iconv("UTF-8", "ISO-8859-1",$datos_empresa['empresa_direccion']),0,1,'C');
		$pdf->Cell(0,5,iconv("UTF-8", "ISO-8859-1","Teléfono: ".$datos_empresa['empresa_telefono']." | Email: ".$datos_empresa['empresa_email']),0,1,'C');

		$pdf->SetFont('Arial','B',12);
		$pdf->Ln(5);
		$pdf->Cell(0,8,iconv("UTF-8", "ISO-8859-1","REMITO DE ENTREGA"),0,1,'C');
		
		// Línea separadora
		$pdf->SetLineWidth(0.5);
		$pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
		$pdf->Ln(3);

		// Datos del remito
		$pdf->SetFont('Arial','',9);
		$pdf->SetFillColor(240,240,240);
		
		$pdf->Cell(80,6,iconv("UTF-8", "ISO-8859-1","Remito Nro: ".$datos_venta['venta_id']),0,0,'L',true);
		$pdf->Cell(0,6,iconv("UTF-8", "ISO-8859-1","Fecha: ".date("d/m/Y", strtotime($datos_venta['venta_fecha']))),0,1,'R',true);

		$pdf->Cell(80,6,iconv("UTF-8", "ISO-8859-1","Código: ".$datos_venta['venta_codigo']),0,0,'L',true);
		$pdf->Cell(0,6,iconv("UTF-8", "ISO-8859-1","Hora: ".$datos_venta['venta_hora']),0,1,'R',true);

		$pdf->Cell(80,6,iconv("UTF-8", "ISO-8859-1","Caja: ".$datos_venta['caja_numero']),0,0,'L',true);
		$pdf->Cell(0,6,iconv("UTF-8", "ISO-8859-1","Vendedor: ".$datos_venta['usuario_nombre']." ".$datos_venta['usuario_apellido']),0,1,'R',true);

		$pdf->Ln(3);

		// Datos del cliente
		$pdf->SetFont('Arial','B',10);
		$pdf->Cell(0,6,iconv("UTF-8", "ISO-8859-1","DATOS DEL CLIENTE"),0,1,'L');
		$pdf->SetFont('Arial','',9);

		if($datos_venta['cliente_id']==1){
			$pdf->Cell(0,5,iconv("UTF-8", "ISO-8859-1","Cliente: N/A"),0,1,'L');
			$pdf->Cell(0,5,iconv("UTF-8", "ISO-8859-1","Documento: N/A"),0,1,'L');
			$pdf->Cell(0,5,iconv("UTF-8", "ISO-8859-1","Teléfono: N/A"),0,1,'L');
		}else{
			$pdf->Cell(0,5,iconv("UTF-8", "ISO-8859-1","Cliente: ".$datos_venta['cliente_nombre']." ".$datos_venta['cliente_apellido']),0,1,'L');
			$pdf->Cell(0,5,iconv("UTF-8", "ISO-8859-1","Documento: ".$datos_venta['cliente_tipo_documento']." ".$datos_venta['cliente_numero_documento']),0,1,'L');
			$pdf->Cell(0,5,iconv("UTF-8", "ISO-8859-1","Teléfono: ".$datos_venta['cliente_telefono']),0,1,'L');
			$pdf->Cell(0,5,iconv("UTF-8", "ISO-8859-1","Dirección: ".$datos_venta['cliente_provincia'].", ".$datos_venta['cliente_ciudad'].", ".$datos_venta['cliente_direccion']),0,1,'L');
		}

		$pdf->Ln(3);

		// Encabezado de tabla de productos
		$pdf->SetFont('Arial','B',9);
		$pdf->SetFillColor(200,200,200);
		$pdf->SetTextColor(0,0,0);
		$pdf->Cell(10,7,iconv("UTF-8", "ISO-8859-1","Nro"),1,0,'C',true);
		$pdf->Cell(80,7,iconv("UTF-8", "ISO-8859-1","Descripción"),1,0,'L',true);
		$pdf->Cell(25,7,iconv("UTF-8", "ISO-8859-1","Cantidad"),1,0,'C',true);
		$pdf->Cell(30,7,iconv("UTF-8", "ISO-8859-1","Precio Unit."),1,0,'R',true);
		$pdf->Cell(35,7,iconv("UTF-8", "ISO-8859-1","Total"),1,1,'R',true);

		// Detalles de la venta
		$pdf->SetFont('Arial','',9);
		$pdf->SetTextColor(0,0,0);
		$pdf->SetFillColor(255,255,255);
		
		$venta_detalle=$ins_venta->seleccionarDatos("Normal","venta_detalle WHERE venta_codigo='".$datos_venta['venta_codigo']."'","*",0);
        $venta_detalle=$venta_detalle->fetchAll();
        
        $contador = 1;
        foreach($venta_detalle as $detalle){
            $pdf->Cell(10,6,iconv("UTF-8", "ISO-8859-1",$contador),1,0,'C');
            $pdf->Cell(80,6,iconv("UTF-8", "ISO-8859-1",$detalle['venta_detalle_descripcion']),1,0,'L');
            $pdf->Cell(25,6,iconv("UTF-8", "ISO-8859-1",$detalle['venta_detalle_cantidad']),1,0,'C');
            $pdf->Cell(30,6,iconv("UTF-8", "ISO-8859-1",MONEDA_SIMBOLO.number_format($detalle['venta_detalle_precio_venta'],MONEDA_DECIMALES,MONEDA_SEPARADOR_DECIMAL,MONEDA_SEPARADOR_MILLAR)),1,0,'R');
            $pdf->Cell(35,6,iconv("UTF-8", "ISO-8859-1",MONEDA_SIMBOLO.number_format($detalle['venta_detalle_total'],MONEDA_DECIMALES,MONEDA_SEPARADOR_DECIMAL,MONEDA_SEPARADOR_MILLAR)),1,1,'R');
            $contador++;
        }

		// Totales
		$pdf->SetFont('Arial','B',10);
		$pdf->SetFillColor(240,240,240);
		$pdf->Cell(145,7,iconv("UTF-8", "ISO-8859-1","TOTAL:"),1,0,'R',true);
		$pdf->Cell(35,7,iconv("UTF-8", "ISO-8859-1",MONEDA_SIMBOLO.number_format($datos_venta['venta_total'],MONEDA_DECIMALES,MONEDA_SEPARADOR_DECIMAL,MONEDA_SEPARADOR_MILLAR).' '.MONEDA_NOMBRE),1,1,'R',true);

		$pdf->Ln(5);

		// Notas finales
		$pdf->SetFont('Arial','',8);
		$pdf->SetTextColor(100,100,100);
		$pdf->MultiCell(0,4,iconv("UTF-8", "ISO-8859-1","Este remito es un comprobante de entrega de mercaderías. Los productos entregados quedan bajo responsabilidad del cliente a partir de esta fecha."),0,'C');

		$pdf->Ln(5);

		// Firmas
		$pdf->SetFont('Arial','',9);
		$pdf->SetTextColor(0,0,0);
		$pdf->Ln(10);
		$pdf->Cell(95,7,iconv("UTF-8", "ISO-8859-1","Firma del Vendedor"),0,0,'C');
		$pdf->Cell(0,7,iconv("UTF-8", "ISO-8859-1","Firma del Cliente"),0,1,'C');
		$pdf->Cell(95,1,"",1,0,'C');
		$pdf->Cell(0,1,"",1,1,'C');

		$pdf->Output("I","Remito_Nro".$datos_venta['venta_id'].".pdf",true);

	}else{
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
	<title><?php echo APP_NAME; ?></title>
	<?php include '../views/inc/head.php'; ?>
</head>
<body>
    <div class="main-container">
        <section class="hero-body">
            <div class="hero-body">
                <p class="has-text-centered has-text-white pb-3">
                    <i class="fas fa-rocket fa-5x"></i>
                </p>
                <p class="title has-text-white">¡Ocurrió un error!</p>
                <p class="subtitle has-text-white">No hemos encontrado datos de la venta</p>
            </div>
        </section>
    </div>
	<?php include '../views/inc/script.php'; ?>
</body>
</html>
<?php } ?>
