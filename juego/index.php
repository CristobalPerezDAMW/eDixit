<?php
// Destáquese que todos los comentarios desaparecerán en las versiones de producción
/* Estados del juego:
    "Inicio": No hay cuentacuentos, el primer jugador en elegir carta y pista se convierte el cuentacuentos y se pasa al estado "PensandoCartas"
    "PensandoCC": El cuentacuentos está pensando, es el primer estado del turno (excepto el primer turno)
    "PensandoCartas:X": El cuentacuentos ha elegido carta y ahora la están eligiendo los demás jugadores. X jugadores ya han elegido carta (tienen que elegir todos menos 1)
    "Votacion:X": Los jugadores están votando qué carta creen que es del cuentacuentos. X jugadores ya han votado (tienen que votar todos menos 1)
    "Puntos": Se están repartiendo los puntos. Se toma un tiempo en este paso para que todos los jugadores vean cómo van
*/

session_start();
if (!isset($_SESSION['iniciada'])){
    header("Location: ../login");
}
$ruta = '..';
include($ruta.'/definiciones.php');
require($ruta.'/bbdd.php');

if (isset($_GET['accion'])){
    $bbdd = mysqli_connect($BBDD->servidor, $BBDD->usuario, $BBDD->contra, $BBDD->bbdd);
    if (!$bbdd){
        die('Error');
    } 
    $sql = 'SELECT Id, Estado, Mano, Cuentacuentos FROM partidas, partida_jugador WHERE Partida=Id AND Jugador=\''.$_SESSION['usuario_correo'].'\'';
    $resultado = $bbdd->query($sql);

    if ($fila = mysqli_fetch_array($resultado)){
        $id_partida = $fila[0];
        $estado = $fila[1];
        $mano_jugador = $fila[2];
        $cuentacuentos = $fila[3];
    }else {
        die('Error: ¿La partida no existe?');
    }
    $resultado->free();

    switch($_GET['accion']){
        case 'get_estado_partida':
            $bbdd->close();
            die($estado.';'.$mano_jugador.';'.$cuentacuentos);
            break;

        case 'elegir_carta_inicio':
            if (!$estado=='Inicio'){
                die('Error');
            }
            $sql = 'UPDATE `partidas` SET `Cuentacuentos`=\''.$_SESSION['usuario_correo'].'\',`Estado`=\'PensandoCartas\';';
            $bbdd->query($sql);
            
            $sql = 'UPDATE `partida_jugador` SET `CartaElegida`=\''.$_GET['carta_elegida'].'\' WHERE `Jugador`=\''.$_SESSION['usuario_correo'].'\';';
            // die($sql);
            $bbdd->query($sql);
            
            // $sql = 'SELECT `Mano` FROM `partida_jugador` WHERE `Partida`=\''.$id_partida.'\' AND `Jugador`=\''.$_SESSION['usuario_correo'].'\';';
            // $resultado = $bbdd->query($sql);
            // if ($fila = mysqli_fetch_array($resultado)){
            //     $mano = $fila[0];
            // } else {
            //     die('Error: No se pudo procesar la mano del jugador');
            // }
            $mano_jugador = explode(':', $mano_jugador);
            for ($i=0; $i < count($mano_jugador); $i++) { 
                if ($mano_jugador[$i]==$_GET['carta_elegida']){
                    unset($mano_jugador[$i]);
                    break;
                }
            }
            $mano_jugador = implode(':', $mano_jugador);
            
            $sql = 'UPDATE `partida_jugador` SET `Mano`=\''.$mano_jugador.'\' WHERE `Jugador`=\''.$_SESSION['usuario_correo'].'\';';
            // die($sql);
            $bbdd->query($sql);
            
            $bbdd->close();
            die($estado);
            break;
    
        case 'elegir_carta_pensando_cartas':
            // Datos privados del Jugador en cuestión y la partida
            $sql = 'UPDATE `partidas` SET `Cuentacuentos`=\''.$_SESSION['usuario_correo'].'\',`Estado`=\'PensandoCartas:0\';';
            die($sql);
            $bbdd->query($sql);

            // Datos privados del Jugador en cuestión y la partida
            $sql = 'SELECT Estado FROM partidas, partida_jugador WHERE Partida=Id AND Jugador=\''.$_SESSION['usuario_correo'].'\'';
            $resultado = $bbdd->query($sql);
        
            while (($fila = mysqli_fetch_array($resultado))){
                $estado = $fila[0];
            }
            $resultado->free();
            $bbdd->close();
            die($estado);
            break;
    }
}

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

// var estadoJuego = "<?php echo $estado ?>";
var estadoJuego = "";
var cuentacuentos = "<?php echo $cuentacuentos ?>";
var jugadorIndice = "<?php echo $jugador_indice ?>";

//AJAX URLs
var urlGet = "<?php echo $_SERVER['PHP_SELF'] ?>";
</script>

<script src="juego.js" type="text/javascript"></script>
<noscript>Su navegador no puede ejecutar este juego, disculpe las molestias.</noscript>
<div id="indicadorAJAX">
    <!-- <img src='imgs/ajax-loading.gif'/> -->
</div>

<div id="jugadores">
</div>
<div id="tablero">
    <div id="mensajes">
        <p id="mensaje1"></p>
        <p id="mensaje2"></p>
    </div>
    <img id="mensajeImagen"/>
</div>
<div id="tusCartas">
</div>
</body>
</html>