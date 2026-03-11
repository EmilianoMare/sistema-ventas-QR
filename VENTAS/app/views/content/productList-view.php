<div class="container is-fluid mb-6">
	<h1 class="title">Productos</h1>
	<h2 class="subtitle"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; Lista de productos</h2>
</div>
<div class="container pb-6 pt-6 product-list-page">

	<?php
		use app\controllers\productController;
		$insProducto = new productController();
	?>

	<div class="form-rest mb-6 mt-6"></div>

	<form method="GET" class="mb-5 form-row" action="">
		<div class="columns is-mobile is-multiline is-variable is-1">
			<div class="column is-5">
				<div class="field">
					<div class="control has-icons-left">
						<input class="input" type="search" name="busqueda" placeholder="Buscar por código, nombre, marca o modelo" value="<?php echo isset($_GET['busqueda']) ? htmlspecialchars($_GET['busqueda']) : ''; ?>" aria-label="Buscar productos">
						<span class="icon is-left"><i class="fas fa-search"></i></span>
					</div>
				</div>
			</div>

			<div class="column is-3">
				<div class="field">
					<div class="control">
						<select name="categoria" class="input">
							<option value="0">Todas las categorías</option>
							<?php
								$cats = $insProducto->seleccionarDatos("Normal","categoria","*",0);
								while($c=$cats->fetch()){
									$sel = (isset($_GET['categoria']) && $_GET['categoria']==$c['categoria_id'])? 'selected' : '';
									echo '<option value="'.$c['categoria_id'].'" '.$sel.'>'.$c['categoria_nombre'].'</option>';
								}
							?>
						</select>
					</div>
				</div>
			</div>

			<div class="column is-2">
				<div class="field">
					<div class="control">
						<?php
							// marcas disponibles
							$marcas = $insProducto->listarMarcasProducto();
						?>
						<select name="marca" class="input">
							<option value="">Todas las marcas</option>
							<?php while($m=$marcas->fetch()){ $mv = isset($_GET['marca'])? $_GET['marca'] : ''; $sel = ($mv==$m['producto_marca'])? 'selected' : ''; echo '<option value="'.htmlspecialchars($m['producto_marca']).'" '.$sel.'>'.htmlspecialchars($m['producto_marca']).'</option>'; } ?>
						</select>
					</div>
				</div>
			</div>

			<div class="column is-2">
				<div class="field">
					<div class="control">
						<select name="stock" class="input">
							<option value="all">Todos</option>
							<option value="in" <?php echo (isset($_GET['stock']) && $_GET['stock']=='in')? 'selected' : ''; ?>>Con stock</option>
							<option value="low" <?php echo (isset($_GET['stock']) && $_GET['stock']=='low')? 'selected' : ''; ?>>Poco stock (&le;10)</option>
							<option value="out" <?php echo (isset($_GET['stock']) && $_GET['stock']=='out')? 'selected' : ''; ?>>Sin stock</option>
						</select>
					</div>
				</div>
			</div>

			<div class="column is-12">
				<div class="columns is-mobile is-variable is-1">
					<div class="column is-narrow" style="max-width:160px;">
						<div class="field">
							<div class="control">
								<input class="input" type="text" name="precio_min" placeholder="Precio min (USD)" value="<?php echo isset($_GET['precio_min'])? htmlspecialchars($_GET['precio_min']) : ''; ?>">
							</div>
						</div>
					</div>
					<div class="column is-narrow" style="max-width:160px;">
						<div class="field">
							<div class="control">
								<input class="input" type="text" name="precio_max" placeholder="Precio max (USD)" value="<?php echo isset($_GET['precio_max'])? htmlspecialchars($_GET['precio_max']) : ''; ?>">
							</div>
						</div>
					</div>

					<div class="column is-narrow">
						<div class="field is-grouped">
							<div class="control">
								<button type="submit" class="button is-info"><i class="fas fa-filter"></i> Aplicar</button>
							</div>
							<div class="control">
								<a href="<?php echo APP_URL; ?>productList/" class="button is-danger full-width-on-mobile"><i class="fas fa-times"></i> Limpiar</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</form>

	<?php
		$busqueda = isset($_GET['busqueda']) ? $_GET['busqueda'] : "";
		$categoria_filter = isset($_GET['categoria']) ? intval($_GET['categoria']) : 0;
		$marca_filter = isset($_GET['marca']) ? $_GET['marca'] : '';
		$stock_filter = isset($_GET['stock']) ? $_GET['stock'] : 'all';
		$precio_min = isset($_GET['precio_min']) ? $_GET['precio_min'] : '';
		$precio_max = isset($_GET['precio_max']) ? $_GET['precio_max'] : '';

		echo $insProducto->listarProductoControlador($url[1],10,$url[0],$busqueda,$categoria_filter,$marca_filter,$stock_filter,$precio_min,$precio_max);
	?>
</div>