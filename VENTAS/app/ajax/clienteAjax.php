<?php
	
	require_once "../../config/app.php";
	require_once "../../app/views/inc/session_start.php";
	require_once "../../autoload.php";
	
	use app\controllers\clientController;

	if (isset($_POST['modulo_cliente'])) {

		// Forzamos tipo de respuesta JSON y limpiamos cualquier salida previa
		header('Content-Type: application/json; charset=utf-8');
		if (ob_get_length()) { while (ob_get_level()) ob_end_clean(); }

		$insCliente = new clientController();

		if ($_POST['modulo_cliente'] == "registrar") {
			echo $insCliente->registrarClienteControlador();
			exit();
		}

		if ($_POST['modulo_cliente'] == "eliminar") {
			echo $insCliente->eliminarClienteControlador();
			exit();
		}

		if ($_POST['modulo_cliente'] == "actualizar") {
			echo $insCliente->actualizarClienteControlador();
			exit();
		}

		// ──────────────────────────────────────────────────────
		// BÚSQUEDA PARA REGISTRO DE PAGO EN CUENTA CORRIENTE
		// ──────────────────────────────────────────────────────
		if ($_POST['modulo_cliente'] == "buscar_para_pago") {

				// Forzamos tipo de respuesta JSON desde el principio
				header('Content-Type: application/json; charset=utf-8');

				// Limpiamos cualquier salida previa que pueda romper el JSON
				if (ob_get_length()) ob_end_clean();

				$termino = $insCliente->limpiarCadena($_POST['termino'] ?? '');

				if (strlen($termino) < 3) {
					echo json_encode([
						'success' => false,
						'message' => 'Escribe al menos 3 caracteres para buscar'
					]);
					exit();
				}

				// Construimos la consulta usando el método ejecutarConsulta de mainModel
				$t = $termino;
				$tLike = "%$t%";

				$sql = "SELECT 
							cliente_id AS id,
							cliente_nombre AS nombre,
							cliente_apellido AS apellido,
							cliente_tipo_documento AS tipo_documento,
							cliente_numero_documento AS numero_documento,
							COALESCE((
								SELECT saldo 
								FROM cuenta_corriente 
								WHERE cliente_id = cliente.cliente_id 
								LIMIT 1
							), 0) AS saldo
						FROM cliente 
						WHERE cliente_id != '1'
						  AND (
							  cliente_nombre LIKE '%".addslashes($t)."%'
							  OR cliente_apellido LIKE '%".addslashes($t)."%'
							  OR cliente_numero_documento LIKE '%".addslashes($t)."%'
							  OR cliente_telefono LIKE '%".addslashes($t)."%'
						  )
						ORDER BY cliente_nombre ASC 
						LIMIT 5";

				$stmt = $insCliente->ejecutarConsulta($sql);

				if ($stmt->rowCount() > 0) {
					$cliente = $stmt->fetch();
					echo json_encode([
						'success' => true,
						'cliente' => [
							'id'                => $cliente['id'],
							'nombre'            => trim($cliente['nombre'] . ' ' . $cliente['apellido']),
							'tipo_documento'    => $cliente['tipo_documento'],
							'numero_documento'  => $cliente['numero_documento'],
							'saldo'             => floatval($cliente['saldo'] ?? 0)
						]
					]);
				} else {
					echo json_encode([
						'success' => false,
						'message' => 'No se encontró ningún cliente con ese criterio'
					]);
				}

				exit();
		}

		// Si ninguno de los módulos anteriores coincidió
		echo json_encode([
			'success' => false,
			'message' => 'Módulo no reconocido'
		]);
		exit();

	} else {
		session_destroy();
		header("Location: " . APP_URL . "login/");
		exit();
	}