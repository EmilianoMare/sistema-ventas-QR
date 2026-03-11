<?php
	
	namespace app\models;
	use \PDO;

	if(file_exists(__DIR__."/../../config/server.php")){
		require_once __DIR__."/../../config/server.php";
	}

	class mainModel{

		private $server=DB_SERVER;
		private $db=DB_NAME;
		private $user=DB_USER;
		private $pass=DB_PASS;


		/*----------  Funcion conectar a BD  ----------*/
		protected function conectar(){
			try{
				$conexion = new PDO("mysql:host=".$this->server.";dbname=".$this->db, $this->user, $this->pass, [
					PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
					PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
				]);
				$conexion->exec("SET CHARACTER SET utf8");
				return $conexion;
			}catch(\PDOException $e){
				// Log the real error for debugging
				error_log("DB connection error: ".$e->getMessage());
				// Show a friendly message in the browser during development
				if(php_sapi_name() !== 'cli'){
					echo '<div style="padding:1rem;background:#fff;border:1px solid #e5e5e5;margin:1rem;font-family:Arial,Helvetica,sans-serif;">';
					echo '<h2 style="margin:0 0 .5rem;color:#c00;">Error de conexión a la base de datos</h2>';
					echo '<p>Comprueba que el servicio MySQL esté corriendo y que las credenciales en <strong>config/server.php</strong> sean correctas.</p>';
					echo '<p style="font-size:.9rem;color:#666;">Detalle técnico: '.htmlentities($e->getMessage()).'</p>';
					echo '</div>';
				}
				exit;
			}
		}


		/*----------  Funcion ejecutar consultas  ----------*/
		protected function ejecutarConsulta($consulta){
			$sql=$this->conectar()->prepare($consulta);
			$sql->execute();
			return $sql;
		}


		/*----------  Funcion limpiar cadenas  ----------*/
		public function limpiarCadena($cadena){

			$palabras = [
				'<script>',
				'</script>',
				'<script src',
				'<script type=',
				'SELECT * FROM',
				'SELECT ',
				' SELECT ',
				'DELETE FROM',
				'INSERT INTO',
				'DROP TABLE',
				'DROP DATABASE',
				'TRUNCATE TABLE',
				'SHOW TABLES',
				'SHOW DATABASES',
				'<?php',
				'?>',
				'--',
				'^',
				'<',
				'>',
				'==',
				';',
				'::'
			];

			$cadena=trim($cadena);
			$cadena=stripslashes($cadena);

			foreach($palabras as $palabra){
				$cadena=str_ireplace($palabra, "", $cadena);
			}

			$cadena=trim($cadena);
			$cadena=stripslashes($cadena);

			return $cadena;
		}


		/*---------- Funcion verificar datos (expresion regular) ----------*/
		protected function verificarDatos($filtro,$cadena){
			if(preg_match("/^".$filtro."$/", $cadena)){
				return false;
            }else{
                return true;
            }
		}


		/*----------  Funcion para ejecutar una consulta INSERT preparada  ----------*/
		protected function guardarDatos($tabla,$datos){

			// Validate provided campo_nombre values against actual table columns to avoid Unknown column errors
			try{
				$columnsStmt = $this->conectar()->prepare("DESCRIBE $tabla");
				$columnsStmt->execute();
				$dbColumns = array_column($columnsStmt->fetchAll(PDO::FETCH_ASSOC), 'Field');
			}catch(\Exception $e){
				$dbColumns = null; // if DESCRIBE fails, fall back to original behavior
			}

			// If we have column info, filter $datos to only include existing columns
			$filteredDatos = $datos;
			$removed = [];
			if (is_array($dbColumns)){
				$filteredDatos = [];
				foreach ($datos as $d){
					if (in_array($d['campo_nombre'], $dbColumns)){
						$filteredDatos[] = $d;
					}else{
						$removed[] = $d['campo_nombre'];
					}
				}
				if (!empty($removed)){
					error_log("guardarDatos: removed unknown columns for table $tabla: " . implode(',', $removed));
				}
			}

			if (empty($filteredDatos)){
				throw new \Exception("No valid columns to insert for table $tabla");
			}

			// Build INSERT query from filtered data
			$query = "INSERT INTO $tabla (";
			$first = true;
			foreach ($filteredDatos as $col){
				if (!$first) $query .= ",";
				$query .= $col['campo_nombre'];
				$first = false;
			}
			$query .= ") VALUES (";
			$first = true;
			foreach ($filteredDatos as $col){
				if (!$first) $query .= ",";
				$query .= $col['campo_marcador'];
				$first = false;
			}
			$query .= ")";

			$sql = $this->conectar()->prepare($query);

			try{
				foreach ($filteredDatos as $clave){
					$sql->bindParam($clave["campo_marcador"],$clave["campo_valor"]);
				}

				$sql->execute();

				return $sql;
			}catch(\PDOException $e){
				$params = [];
				foreach ($filteredDatos as $clave){
					$params[$clave["campo_marcador"]] = $clave["campo_valor"];
				}
				error_log("SQL Error: " . $e->getMessage() . " | Query: " . $query . " | Params: " . json_encode($params));
				throw $e;
			}
		}


		/*---------- Funcion seleccionar datos ----------*/
        public function seleccionarDatos($tipo,$tabla,$campo,$id){
			$tipo=$this->limpiarCadena($tipo);
			$tabla=$this->limpiarCadena($tabla);
			$campo=$this->limpiarCadena($campo);
			$id=$this->limpiarCadena($id);

            if($tipo=="Unico"){
                $sql=$this->conectar()->prepare("SELECT * FROM $tabla WHERE $campo=:ID");
                $sql->bindParam(":ID",$id);
            }elseif($tipo=="Normal"){
                $sql=$this->conectar()->prepare("SELECT $campo FROM $tabla");
            }
            $sql->execute();

            return $sql;
		}


		/*----------  Funcion para ejecutar una consulta UPDATE preparada  ----------*/
		protected function actualizarDatos($tabla,$datos,$condicion){

			$query="UPDATE $tabla SET ";

			$C=0;
			foreach ($datos as $clave){
				if($C>=1){ $query.=","; }
				$query.=$clave["campo_nombre"]."=".$clave["campo_marcador"];
				$C++;
			}

			$query.=" WHERE ".$condicion["condicion_campo"]."=".$condicion["condicion_marcador"];

			$sql=$this->conectar()->prepare($query);

			foreach ($datos as $clave){
				$sql->bindParam($clave["campo_marcador"],$clave["campo_valor"]);
			}

			$sql->bindParam($condicion["condicion_marcador"],$condicion["condicion_valor"]);

			$sql->execute();

			return $sql;
		}


		/*---------- Funcion eliminar registro ----------*/
        protected function eliminarRegistro($tabla,$campo,$id){
            $sql=$this->conectar()->prepare("DELETE FROM $tabla WHERE $campo=:id");
            $sql->bindParam(":id",$id);
            $sql->execute();
            
            return $sql;
        }


		/*---------- Paginador de tablas ----------*/
		protected function paginadorTablas($pagina,$numeroPaginas,$url,$botones){
	        $tabla='<nav class="pagination is-centered is-rounded" role="navigation" aria-label="pagination">';

	        // preservar parámetros GET (filtros) en la paginación
	        $qs = '';
	        if(!empty($_GET)){
	            $qs = '?'.http_build_query($_GET);
	        }

	        if($pagina<=1){
	            $tabla.='
	            <a class="pagination-previous is-disabled" disabled ><i class="fas fa-arrow-alt-circle-left"></i> &nbsp; Anterior</a>
	            <ul class="pagination-list">
	            ';
	        }else{
	            $tabla.='
	            <a class="pagination-previous" href="'.htmlspecialchars($url.($pagina-1).'/'.$qs).'\"><i class="fas fa-arrow-alt-circle-left"></i> &nbsp; Anterior</a>
	            <ul class="pagination-list">
	                <li><a class="pagination-link" href="'.htmlspecialchars($url.'1/'.$qs).'">1</a></li>
	                <li><span class="pagination-ellipsis">&hellip;</span></li>
	            ';
	        }


	        $ci=0;
	        for($i=$pagina; $i<=$numeroPaginas; $i++){

	            if($ci>=$botones){
	                break;
	            }

	            if($pagina==$i){
	                $tabla.='<li><a class="pagination-link is-current" href="'.htmlspecialchars($url.$i.'/'.$qs).'">'.$i.'</a></li>';
	            }else{
	                $tabla.='<li><a class="pagination-link" href="'.htmlspecialchars($url.$i.'/'.$qs).'">'.$i.'</a></li>';
	            }

	            $ci++;
	        }


	        if($pagina==$numeroPaginas){
	            $tabla.='
	            </ul>
	            <a class="pagination-next is-disabled" disabled ><i class="fas fa-arrow-alt-circle-right"></i> &nbsp; Siguiente</a>
	            ';
	        }else{
	            $tabla.='
	                <li><span class="pagination-ellipsis">&hellip;</span></li>
	                <li><a class="pagination-link" href="'.htmlspecialchars($url.$numeroPaginas.'/'.$qs).'">'.$numeroPaginas.'</a></li>
	            </ul>
	            <a class="pagination-next" href="'.htmlspecialchars($url.($pagina+1).'/'.$qs).'"><i class="fas fa-arrow-alt-circle-right"></i> &nbsp; Siguiente</a>
	            ';
	        }

	        $tabla.='</nav>';
	        return $tabla;
	    }


	    /*----------  Funcion generar select ----------*/
		public function generarSelect($datos,$campo_db){
			$check_select='';
			$text_select='';
			$count_select=1;
			$select='';
			foreach($datos as $row){

				if($campo_db==$row){
					$check_select='selected=""';
					$text_select=' (Actual)';
				}

				$select.='<option value="'.$row.'" '.$check_select.'>'.$count_select.' - '.$row.$text_select.'</option>';

				$check_select='';
				$text_select='';
				$count_select++;
			}
			return $select;
		}

		/*----------  Funcion generar codigos aleatorios  ----------*/
		protected function generarCodigoAleatorio($longitud,$correlativo){
			$codigo="";
			$caracter="Letra";
			for($i=1; $i<=$longitud; $i++){
				if($caracter=="Letra"){
					$letra_aleatoria=chr(rand(ord("a"),ord("z")));
					$letra_aleatoria=strtoupper($letra_aleatoria);
					$codigo.=$letra_aleatoria;
					$caracter="Numero";
				}else{
					$numero_aleatorio=rand(0,9);
					$codigo.=$numero_aleatorio;
					$caracter="Letra";
				}
			}
			return $codigo."-".$correlativo;
		}


		/*----------  Limitar cadenas de texto  ----------*/
		public function limitarCadena($cadena,$limite,$sufijo){
			if(strlen($cadena)>$limite){
				return substr($cadena,0,$limite).$sufijo;
			}else{
				return $cadena;
			}
		}
	    
	}