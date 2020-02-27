<?php
    // require('cabecera.php');
?>
<?php
session_start();
if (!isset($_SESSION['iniciada'])){
    header("Location: ../login");
}
$ruta = '..';
include($ruta.'/definiciones.php');

$foto = $ruta.'/perfiles/'.@parsearNombreArchivo($_SESSION['usuario_correo']).'.foto';
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

    <?php echo '<title>Dixit Electrónico - '.@$_SESSION['usuario_nombre'].'</title>'; ?>
</head>

<body onload="init()" onresize="redimensionm()">
<script>
    var canvas;
    var ctx;

    var imgPerfil;
    var posXPerfil = 0, posYPerfil = 0;

    function init() {
        canvas = document.getElementById("juego");
        ctx = canvas.getContext("2d");

        imgPerfil = new Image;
        imgPerfil.src = "<?php echo $foto ?>";
        
        setInterval(function(){
            frame();
        }, 100);

        redimension();
    }

    function redimension(){
        canvas.width  = document.body.clientWidth;
        canvas.height = window.innerHeight;

        ctx.moveTo(0, 0);
        ctx.fillStyle = 'black';
        ctx.fillRect(0, 0, 20000, 20000);
        ctx.stroke();
    }

    function frame(){
        if (imgPerfil.complete){
            ctx.drawImage(imgPerfil, posXPerfil, posYPerfil);
            posXPerfil += 1;
            posYPerfil += 1;
        } else {
            ctx.fillStyle = 'green';
            ctx.moveTo(0, 0);
            ctx.fillRect(200, 200, 100, 100);
        }

    }
</script>

<canvas id="juego">
No puede jugar a este juego con el navegador que está usando actualmente, sentimos las molestias
</canvas>