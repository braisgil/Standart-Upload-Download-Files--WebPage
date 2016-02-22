<?php
//PARAMETROS NECESARIOS PARA LA CONEXION
$host="localhost";
$base_datos="fueradeseries";
$loginbd="root";
$passwordbd="1234.Abcd";

//COMPROBACIONES
if (($conexion_bd=@mysql_connect($host, $loginbd, $passwordbd))==null) {
	//PASO EL CODIGO "CONEXION" PARA SABER QUE LO QUE FALLA ES LA CONEXION
	//header("location:index.php?codigo=conexion");
	echo "conexion";
} else if (($mysql_bd=@mysql_select_db($base_datos))==null) {
	//PASO EL CODIGO "BASEDEDATOS" PARA SABER QUE LO QUE FALLA ES LA BASE DE DATOS
	echo "basededatos";
} else {
	@mysql_set_charset("utf8");
}
?>