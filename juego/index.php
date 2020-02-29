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
<script>
var body, divTusCartas, jugadores;

function init() {
    body = document.body;
    divTusCartas = document.getElementById("tusCartas");
    jugadores = new Array(
        { correo: "<?php echo $_SESSION['usuario_correo']?>", cartas: new Array(1, 3, 4, 23, 10, 27) },
        { correo: "otroseñor", cartas: new Array(6, 7, 9, 10, 13) },
        { correo: "lul", cartas: new Array(2, 8, 5, 17, 12, 22) });
    console.log(jugadores);

    jugadores.forEach(jugador => {
        if (jugador.correo == "<?php echo $_SESSION['usuario_correo']?>") {
            let nodoDiv = document.createElement("div");
            jugador.cartas.forEach(carta => {
                let img = new Image();
                img.src = "cartas/carta" + carta + ".jpg"
                img.title = "carta"+carta;
                nodoDiv.appendChild(img);
            });
            divTusCartas.appendChild(nodoDiv);
        }
    });
}
</script>


<div id="tusCartas">
</div>
</body>
</html>