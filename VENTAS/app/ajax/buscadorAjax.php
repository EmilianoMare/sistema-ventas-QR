<?php
	
	require_once "../../config/app.php";
	require_once "../views/inc/session_start.php";
	require_once "../../autoload.php";
	
	use app\controllers\searchController;

	if(isset($_POST['modulo_buscador'])){

		// Forzamos tipo de respuesta JSON para las peticiones AJAX
		header('Content-Type: application/json; charset=utf-8');

		$insBuscador = new searchController();

		if($_POST['modulo_buscador']=="buscar"){
			echo $insBuscador->iniciarBuscadorControlador();
		}

		if($_POST['modulo_buscador']=="eliminar"){
			echo $insBuscador->eliminarBuscadorControlador();
		}
		
	}else{
		session_destroy();
		header("Location: ".APP_URL."login/");
	}