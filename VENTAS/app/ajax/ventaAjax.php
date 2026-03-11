<?php
	
	require_once "../../config/app.php";
	require_once "../views/inc/session_start.php";
	require_once "../../autoload.php";
	
	use app\controllers\saleController;

	// Iniciar buffer de salida y registrar función de cierre para loguear la respuesta completa
	ob_start();

	if(isset($_POST['modulo_venta'])){

		// Forzamos tipo de respuesta JSON para las peticiones AJAX
		header('Content-Type: application/json; charset=utf-8');

		$insVenta = new saleController();

register_shutdown_function(function(){
	$content = ob_get_contents();
	if($content===false) $content = '';
	// Intentar serializar POST de forma segura
	$post_data = isset($_POST) ? json_encode($_POST, JSON_UNESCAPED_UNICODE) : '{}';
	$log = date('Y-m-d H:i:s')." | AJAX venta request: " . ($_SERVER['REQUEST_METHOD'] ?? 'CLI') . " " . ($_SERVER['REQUEST_URI'] ?? '') . " | POST: " . $post_data . "\nRESPONSE:\n" . $content . "\n\n";
	@file_put_contents(__DIR__.'/../../logs/ajax_venta_raw.log', $log, FILE_APPEND);
	// Dejar que el buffer se envíe normalmente
	ob_end_flush();
});
		/*--------- Buscar producto por codigo (modal) ---------*/
		if($_POST['modulo_venta']=="buscar_codigo"){
			echo $insVenta->buscarCodigoVentaControlador();
		}

		/*--------- Autocomplete: recomendaciones en tiempo real ---------*/
		if($_POST['modulo_venta']=="autocomplete_producto"){
			echo $insVenta->autocompleteProductoControlador();
		}

		/*--------- Agregar producto a carrito ---------*/
		if($_POST['modulo_venta']=="agregar_producto"){
			echo $insVenta->agregarProductoCarritoControlador();
        }

        /*--------- Remover producto de carrito ---------*/
		if($_POST['modulo_venta']=="remover_producto"){
			echo $insVenta->removerProductoCarritoControlador();
		}

		/*--------- Actualizar producto de carrito ---------*/
		if($_POST['modulo_venta']=="actualizar_producto"){
			echo $insVenta->actualizarProductoCarritoControlador();
		}

		/*--------- Buscar cliente ---------*/
		if($_POST['modulo_venta']=="buscar_cliente"){
			echo $insVenta->buscarClienteVentaControlador();
		}

		/*--------- Agregar cliente a carrito ---------*/
		if($_POST['modulo_venta']=="agregar_cliente"){
			echo $insVenta->agregarClienteVentaControlador();
		}

		/*--------- Remover cliente de carrito ---------*/
		if($_POST['modulo_venta']=="remover_cliente"){
			echo $insVenta->removerClienteVentaControlador();
		}

		/*--------- Registrar venta ---------*/
		if($_POST['modulo_venta']=="registrar_venta"){
			try{
				echo $insVenta->registrarVentaControlador();
			}catch(\Throwable $e){
				// Log the error and return JSON-safe message
				error_log("Venta AJAX error: " . $e->getMessage());
				http_response_code(500);
				echo json_encode([
					"tipo"=>"simple",
					"titulo"=>"Error interno",
					"texto"=>"Ocurrió un error al procesar la venta",
					"icono"=>"error"
				]);
			}
		}

		/*--------- Eliminar venta ---------*/
		if($_POST['modulo_venta']=="eliminar_venta"){
			echo $insVenta->eliminarVentaControlador();
		}
		
	}else{
		session_destroy();
		header("Location: ".APP_URL."login/");
	}