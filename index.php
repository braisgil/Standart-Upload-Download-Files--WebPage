<?php
session_start();
//ENLAZO CON LOS INCLUDES
require_once('conexion.inc.php');
require_once('login.php');
//require_once('logout.php');

if (isset($_POST['login']) && isset($_POST['password'])){
//CONSULTA DE LOGIN
$consulta = "SELECT * FROM usuario WHERE login LIKE '".$_POST['login']."' AND password LIKE md5('".$_POST['password']."')";
$resultado = @mysql_query($consulta);
	//VALIDACION DE USUARIO
	if ($fila = @mysql_fetch_array($resultado)==NULL){
		//NO EXISTE
	} else {
		//SI EXISTE
		$_SESSION['login'] = $_POST['login'];
		$_SESSION['password'] = $_POST['password'];
		$login = $_SESSION['login'];
		$password = $_SESSION['password'];
	}
}
if(isset($_SESSION['login']) && isset($_SESSION['password'])) {
	//SI EXISTE
		$saludo = "<h1> Hola ".$_SESSION['login']." </h1>";
		$subida ="<form action='guardar.php' enctype='multipart/form-data' method='post'>
			  		<fieldset>
			  			<label for='fichero'>Subir fichero:</label>
			  			</br>
					 	<input type='file' name='fichero' id='fichero'/>
					 	</br>
					 	<select name='categoria' id='categoria'>
					 		<option value='1'>Peliculas</option>
					 		<option value='2'>Series</option>
					 		<option value='3'>Documentales</option>
					 		<option value='4'>Libros</option>
					 	</select>
						</br>
			  			<input type='submit' value='subir'/>
			  		</fieldset>
			  	</form>";
		$logout ="<a href='logout.php'>Logout</a>"; //LLAMO A LOGOUT PHP QUE ME DESCONECTA Y ME REDIRIGE A INDEX
}

//CONSULTA DE LISTA DE TORRENTS

//PAGINACION

//Calculos de los enlaces para primero, ultimo, anterior y siguiente

	//total de registros sin limit. La consulta es la misma pero sin ordenar y sin el limit, y en el select se cuentan los registros no se obtienen todos.
	$consulta_count_total="select count(*) as total_registros from fichero where 1";
	$resultado_count_total=mysql_query($consulta_count_total);
	$fila_count=mysql_fetch_assoc($resultado_count_total);
	$total_registros=$fila_count["total_registros"];
	$limit_cantidad = 10;
	if(isset($_GET['limit_inicio']))
		$limit_inicio=$_GET['limit_inicio'];
	else $limit_inicio=0;
//	echo "<br />total_registros = $total_registros";
	$registros_pagina=$limit_cantidad;
//	echo "<br />registros_pagina = $registros_pagina";


	//PRIMERO : nada que calcular, siempre es 0
	$inicio_primero=0;


	//ULTIMO : en funcion del numero de registros que devuelve la consulta y el numero de registros mostrados
	//numero de paginas = total de registros / registros por pagina + resto
	//se obtiene el numero de paginas completas dividiendo y redondeando al entero hacia abajo. ceil redondearia hacia arriba y round al mas proximo.
	$resultado_num_paginas=floor($total_registros/$registros_pagina);
	//se calcula cuantos registros habra en la pagina de restos, es decir, que sobren de pagina completa al final
	$resultado_resto=$total_registros % $registros_pagina;
	//en caso de que resto sea mayor que cero, la ultima pagina comienza en total_registros-resultado_resto, en caso contrario, total_registros-registros_pagina
	if($resultado_resto>0)	$inicio_ultimo=$total_registros-$resultado_resto;
	else 					$inicio_ultimo=$total_registros-$registros_pagina;


	//ANTERIOR : si es la primera pagina, -1, en caso contrario, el inicio que se este usando menos el numero de registros por pagina
	if($limit_inicio==$inicio_primero)	$inicio_anterior=-1;
	else 								$inicio_anterior=$limit_inicio-$limit_cantidad;


	//SIGUIENTE : si es la ultima pagina, -1, en caso contrario, el inico que se este usando mas el numero de registros por pagina
	if($limit_inicio==$inicio_ultimo)	$inicio_siguiente=-1;
	else 								$inicio_siguiente=$limit_inicio+$limit_cantidad;


//CONSULTA
$nombre_categoria = $_GET['cat'];
$criterio_busqueda = $_GET['buscar'];
$criterio_busqueda = "%" . str_replace(" ", "%", $criterio_busqueda) . "%";
$consultalista = "SELECT fichero.nombre as nombre, fichero.fecha as fecha, categoria.nombre_cat as categoria, fichero.peso as peso, fichero.url as url
					from fichero inner join categoria on fichero.id_categoria = categoria.id_categoria 
					WHERE TRUE
					AND nombre like '$criterio_busqueda' 
					AND categoria.nombre_cat LIKE '%$nombre_categoria%'  
					ORDER BY fecha DESC
					LIMIT $limit_inicio, $registros_pagina";
$resultadolista = @mysql_query($consultalista);


//IMPRESION LISTA DE TORRENTS
if (($resultadolista != null) && (mysql_num_rows($resultadolista)>0)) {

	$tabla="<h2>Listado de Ficheros</h2>
			<table>
				<tr> 
					<th>Nombre</th> <th>Peso</th> <th>Url</th> <th>Fecha</th> <th>Categoria</th>
				</tr>";

	while(($fila = mysql_fetch_assoc($resultadolista))!=null) {
		$idfich=$fila['id_fichero'];
		$nombre=$fila['nombre'];
		$peso=$fila['peso'];
		$url=$fila['url'];
		$fecha=$fila['fecha'];
		$categoria=$fila['categoria'];
		$nombre_corto=substr($nombre,0,40);
		if(strcmp($sombre,$nombre_corto)!=0)
			$nombre_corto.="...";
		$tabla.="<tr>
					<td title='$nombre'>$nombre_corto</td>
					<td>$peso</td>   
					<td>
						<a href='$url' title='descargar archivo'> $url </a>
					</td>
					<td>$fecha</td>
					<td>$categoria</td> 
				</tr>";
	}

	$tabla.="\n </table>";

};



	//Composicion de los enlaces que llevaran a cada pagina

	$tabla.="<p id='paginacion'>";
	$tabla.="<a href='index.php?limit_inicio=$inicio_primero'>Primera</a>";
	$tabla.=" | ";
	if($inicio_anterior!=-1)	$tabla.="<a href='index.php?limit_inicio=$inicio_anterior'>Anterior</a>";
	else 						$tabla.="Anterior";
	$tabla.=" | ";
	if($inicio_siguiente!=-1)	$tabla.="<a href='index.php?limit_inicio=$inicio_siguiente'>Siguiente</a>";
	else 						$tabla.="Siguiente";
	$tabla.=" | ";
	$tabla.="<a href='index.php?limit_inicio=$inicio_ultimo'>&Uacute;ltima</a>";
	$tabla.="</p>";



//PARA PAGINAR LOS RESULTADOS
/*
a) Sentencia para todo el listado
$consulta="SELECT * FROM fichero";

Hago el $reg_totales=mysql_num_rows($consulta); para saber cuantos registros tengo que paginar.

b) Número de resultados por página: 10
$items_por_pagina=10;

c) Enlaces en la paginación: Utilizar $items_inicio
PRIMERO -> Siempre $items_inicio=1
ANTERIOR -> $items_inicio=$items_inicio-$items_por_pagina(si $items_inicio>1)
SIGUIENTE -> $items_inicio=$items_inicio+$items_por_pagina(si $items_inicio+$items_por_pagina<=$reg_totales)
ULTIMO -> $items_inicio=$registros_totales-(($reg_totales-1)%$items_por_pagina)+1 ES POSIBLE QUE HAYA QUE QUITAR EL +1

d) Sentencia para el listado de 10 elementos: (1ºpagina)
SELECT * FROM fichero LIMIT 10 <-$items_por_pagina

e) Sentencia para listado de 10 elementos (3ºpagina)
SELECT * FROM fichero LIMIT 20,10   20->$items_inicio  10->$items_por_pagina
*/



/*
//PARA BUSCAR
SELECT * FROM fichero WHERE nombre like '%cosaquesebusca%cosaquesebusca2%';
*/


//PARA SUBIR ARCHIVOS
/*
EN HTML
<form action='guardar.php' enctype='multiport/form-data'>
<label for='fichero'>FICHERO</label>
<input type='file' name='fichero' id='fichero'/>

EN PHP
$_FILES['ficheros']['name'];
"size"
"type"
"tmp_name"
"error"
*/



//FUNCIONES PARA EL MANEJO DE FICHEROS
/*
basename - devuelve el nombre de un fichero en una ruta completa -> basename(string $ruta [,string $sufijo])
ejemplo:
	echo basename("./directorio/subdirectorio/fichero.txt");

chmod - cambia los permisos de un fichero -> chmod(string $ruta, int $permisos)
ejemplo:
	chmod ("./directorio/fichero",775);

file_exists - devuelve true si el fichero existe. -> bool file_exists(string $ruta)
ejemplo:
	if(!file_exists("./directorio") echo "el directorio no existe");

is_readable - devuelve true si hay permisos de lectura.

is_writeable - lo mismo pero de escritura.

is_file - devuelve true si es un fichero (no directorio ni enlace)

is_dir - true si es directorio

mkdir - crea un directorio
bool mkdir (string $ruta[,int $permisos[,int $recursivo]]) -> si no existe la estructura previa pones un true en el recursivo.

rmdir - elimina directorio
bool rmdir(string ruta)

move_uploaded_file - mueve un fichero dentro del sistema desde el temporal del webserver.
bool move_uploaded_file (string $ruta_origen, string $ruta_destino).
ejemplo:
	move_uploaded_file ($_FILES['fichero']['tmp_name'],"torrents/".$_FILES['fichero']['name']);

*/
?>

<html>
	<header>
		<link rel='stylesheet' type='text/css' href='estilo.css'/>
	</header>
		<body>
			<div id='contenedor'>
			<a href="index.php?buscar=">
			<div id='cabecera'>
			</div>
			</a>

			<div id='divlogin'>
			<form action='index.php' id='login' method='post'>
				<fieldset>
					<label for='login'>Login</label>
					<input type='text' name='login' id='login'/>
					

					<label for='password'>Password</label>
					<input type='password' name='password' id='password'/>

					<input type='submit' value='inicio'/>
				</fieldset>
			</form>
			</div>

			<div id='divbusqueda'>	
			<form action='index.php' method='get'>
				<fieldset>
					<label for='buscar'>Busqueda</label>
					<input type='text' name='buscar' id='buscar' value='introduce criterio' />
					<input type='submit' value='search'/>
				</fieldset>
			</form>
			</div>

			<div id='menu'>
				<a href='index.php?cat=peliculas'>Peliculas</a>
				<a href='index.php?cat=series'>Series</a>
				<a href='index.php?cat=documentales'>Documentales</a>
				<a href='index.php?cat=libros'>Libros</a>
			</div>

			<div id='logueado'>
			<?=$saludo?>
			<?=$subida?>
			<?=$logout?>
			</div>

			<div id='listado'>
				<?=$tabla?>
			</div>


			<div id='botones'>
			</div>

		</div>
		</body>
<html>

