<?php
// Destáquese que todos los comentarios desaparecerán en las versiones de producción
/* Estados del juego:
    "Inicio": No hay cuentacuentos, el primer jugador en elegir carta y pista se convierte el cuentacuentos y se pasa al estado "PensandoCartas"
    "PensandoCC": El cuentacuentos está pensando, es el primer estado del turno (excepto el primer turno)
    "PensandoCartas:X": El cuentacuentos ha elegido carta y ahora la están eligiendo los demás jugadores. Quedan X jugadores por elegir carta
    "Votacion:X": Los jugadores están votando qué carta creen que es del cuentacuentos. Quedan X jugadores por votar
    "Puntos": Se están repartiendo los puntos. Se toma un tiempo en este paso para que todos los jugadores vean cómo van
*/

session_start();
if (!isset($_SESSION['iniciada'])){
    header("Location: ../login");
}
$ruta = '..';
include($ruta.'/definiciones.php');
require($ruta.'/bbdd.php');

$foto = $ruta.'/perfiles/'.parsearNombreArchivo($_SESSION['usuario_correo']).'.foto';
if (!is_file($foto)){
    $foto = $ruta.'/imgs/sin_foto.png';
}

function rutaFoto($correo){
    $foto = '../perfiles/'.parsearNombreArchivo($correo).'.foto';
    if (is_file($foto)){
        return $foto;
    }
    return '../imgs/sin_foto.png';
}

// Acceso a la base de datos

$bbdd = mysqli_connect($BBDD->servidor, $BBDD->usuario, $BBDD->contra, $BBDD->bbdd);
$error = false;
$jugadores = false;
if (!$bbdd){
    $error = 'La base de datos no está disponible<br>Sentimos las molestias';
} else {

    // Datos privados del Jugador en cuestión y la partida
    $sql = 'SELECT Mano, Cuentacuentos, Estado FROM partidas, partida_jugador WHERE Partida=Id AND Jugador=\''.$_SESSION['usuario_correo'].'\'';
    $resultado = $bbdd->query($sql);

    while (($fila = mysqli_fetch_array($resultado))){
        $tu_mano = explode(':', $fila[0]);
        $cuentacuentos = $fila[1];
        $estado = $fila[2];
    }
    $resultado->free();

    // Datos Jugadores de la Partida
    $sql = 'SELECT Nombre, Jugador, Posicion, Mano FROM usuarios, partida_jugador WHERE partida IN (SELECT Id FROM partidas WHERE Id IN (SELECT Partida FROM partida_jugador WHERE Jugador = \''.$_SESSION['usuario_correo'].'\')) AND Jugador = Correo';
    // die($sql);
    $resultado = $bbdd->query($sql);

    for($cont=0; ($fila = mysqli_fetch_array($resultado)); $cont++){
        if ($fila[1]==$_SESSION['usuario_correo']){
            $tu_mano = explode(':', $fila[3]);
            $jugador_indice = $cont;
        }
        $jugadores[$fila[1]] = array('Nombre'=>$fila[0], 'Posicion'=>$fila[2], 'Foto'=>rutaFoto($fila[1]));
    }
    $resultado->free();

    $bbdd->close();
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <?php
    echo '<link rel="stylesheet" href="'.$ruta.'/css/juego.css">
    <link rel="shortcut icon" href="'.$ruta.'/imgs/edixit-logo.png">
    <title>Dixit Electrónico - '.$_SESSION['usuario_nombre'].'</title>
    ';
    ?>
</head>

<body>
<!-- <body onload="init()"> -->
<?php
    // die($estado);
    if ($error){
        die('<p class="error">'.$error.'</p>');
    } else if (!$jugadores){
        die('<p class="error">No estás en ninguna partida</p><br><p class="error">Normalmente te sacaría de nuevo al menú pero necesito saber cuándo pasa esto.</p>');
    }
?>

<script>
// Para escribir desde php
var jugadores = new Array(
<?php
foreach ($jugadores as $correo => $jugador) {
    echo '{
        nombre: "'.$jugador['Nombre'].'",
        correo: "'.$correo.'",
        img: "'.$jugador['Foto'].'"
    }, ';
}
?>
);

var tuMano = new Array(<?php echo implode(',', $tu_mano);?>);

var estadoJuego = "<?php echo $estado ?>";
var cuentacuentos = "<?php echo $cuentacuentos ?>";
var jugadorIndice = "<?php echo $jugador_indice ?>";
</script>

<script src="juego.js" type="text/javascript"></script>
<noscript>Su navegador no puede ejecutar este juego, disculpe las molestias.</noscript>
<div id="indicadorAJAX">
    <!-- <img src='imgs/ajax-loading.gif'/> -->
</div>

<div id="jugadores">
    <h1>Jugadores</h1>
</div>
<div id="tablero">
    <div id="mensajes">
        <p id="mensaje1"></p>
        <p id="mensaje2"></p>
    </div>
    <!-- <img src="cartas/carta1.jpg" alt="xd"> -->
</div>
<div id="tusCartas">
    <!-- <h1>Tus Cartas:</h1> -->
</div>
</body>
</html>