<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($ruta)){
    $ruta = '..';
}
// die($ruta);
// $actual = $_SERVER['PHP_SELF'];
include($ruta.'/definiciones.php');

$foto = $ruta.'/perfiles/'.parsearNombreArchivo($_SESSION['usuario_correo']).'.foto';
if (!is_file($foto)){
    $foto = $ruta.'/imgs/sin_foto.png';
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <?php
    echo '
        <link rel="stylesheet" href="'.$ruta.'/css/juego.css">
        <link rel="shortcut icon" href="'.$ruta.'/imgs/edixit-logo.png">
    ';
    ?>
    <!-- <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css"> -->

    <?php echo '<title>Dixit Electrónico - '.$_SESSION['usuario_nombre'].'</title>'; ?>
</head>

<body onload="init()">