<div class="container is-fluid mb-6">
	<h1 class="title">Ventas</h1>
	<h2 class="subtitle"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; Lista de Ventas</h2>
</div>
<div class="container pb-6 pt-6">

	<div class="form-rest mb-6 mt-6"></div>

	<?php
		use app\controllers\saleController;

		$insVenta = new saleController();

		// --- Buscador (usa buscadorAjax.php / searchController) ---
		if(!isset($_SESSION[$url[0]]) || empty($_SESSION[$url[0]])){
	?>
	<div class="columns mb-4">
		<div class="column">
			<form class="FormularioAjax form-row" action="<?php echo APP_URL; ?>app/ajax/buscadorAjax.php" method="POST" autocomplete="off" >
				<input type="hidden" name="modulo_buscador" value="buscar">
				<input type="hidden" name="modulo_url" value="<?php echo $url[0]; ?>">
				<div class="field has-addons">
					<div class="control is-expanded">
						<input class="input" type="text" name="txt_buscador" placeholder="Buscar por código, nombre o apellido" pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\- ]{1,30}" maxlength="30" required >
					</div>
					<div class="control">
						<button class="button is-info" type="submit" >
							<i class="fas fa-search"></i> Buscar
						</button>
					</div>
				</div>
			</form>
		</div>
	</div>
	<?php }else{ ?>
	<div class="columns mb-4">
		<div class="column">
			<form class="has-text-centered mt-2 mb-2 FormularioAjax form-row" action="<?php echo APP_URL; ?>app/ajax/buscadorAjax.php" method="POST" autocomplete="off" >
				<input type="hidden" name="modulo_buscador" value="eliminar">
				<input type="hidden" name="modulo_url" value="<?php echo $url[0]; ?>">
				<p><i class="fas fa-search fa-fw"></i> &nbsp; Estás buscando: <strong>“<?php echo $_SESSION[$url[0]]; ?>”</strong></p>
				<br>
				<button type="submit" class="button is-danger is-rounded"><i class="fas fa-trash-restore"></i> &nbsp; Eliminar búsqueda</button>
			</form>
		</div>
	</div>
	<?php }

		$busqueda = isset($_SESSION[$url[0]]) ? $_SESSION[$url[0]] : "";

		echo $insVenta->listarVentaControlador($url[1],15,$url[0], $busqueda);

		include "./app/views/inc/print_invoice_script.php";
	?>
</div>