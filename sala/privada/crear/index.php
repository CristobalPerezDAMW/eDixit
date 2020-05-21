<?php
error_reporting(E_ALL);
ini_set('display_errors', 'on');
$ruta='../../..';
$pag = 'Crear Sala Privada';
require("../../../cabecera.php");
require('../../../bbdd.php');

$bbdd = mysqli_connect($BBDD->servidor, $BBDD->usuario, $BBDD->contra, $BBDD->bbdd);

if (!$bbdd){
    die('<p style="color: red;">Error al conectar la base de datos</p>');
} 
$bbdd->close();
?>

</body>
</html>