<?php
require_once('conexion.inc.php');
print_r($_POST);
if ($_FILES['fichero']['error'] > 0)
{
	echo "Error: " . $_FILES['fichero']['error'] . "</br>";
} else {
	echo "Subiendo archivo...";
		$nombre = $_FILES['fichero']['name'];
		$fecha = date("Y-m-d");
		$peso = $_FILES['fichero']['size'];
		$url = "/subidas/" . $nombre;
		$categoria = $_POST['categoria'];
		//$categoria = $_POST['select'];

 
move_uploaded_file($_FILES['fichero']['tmp_name'], $url);

$consultaguardar = "INSERT INTO fichero (nombre, peso, url, id_categoria,fecha) values ('$nombre', $peso, '$url', $categoria,'$fecha')";
echo $consultaguardar;
$resultadoguardar = mysql_query($consultaguardar);

header("location:index.php");
}


?>
