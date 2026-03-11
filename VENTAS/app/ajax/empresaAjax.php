<?php
	
	require_once "../../config/app.php";
	require_once "../views/inc/session_start.php";
	require_once "../../autoload.php";
	
	use app\controllers\companyController;

	if(isset($_POST['modulo_empresa'])){

		// Forzamos tipo de respuesta JSON para las peticiones AJAX
		header('Content-Type: application/json; charset=utf-8');

		$insEmpresa = new companyController();

		if($_POST['modulo_empresa']=="registrar"){
			echo $insEmpresa->registrarEmpresaControlador();
		}

		if($_POST['modulo_empresa']=="actualizar"){
			echo $insEmpresa->actualizarEmpresaControlador();
		}
		
	}else{
		session_destroy();
		header("Location: ".APP_URL."login/");
	}