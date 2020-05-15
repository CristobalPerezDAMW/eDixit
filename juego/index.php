<?php
// Destáquese que todos los comentarios desaparecerán en las versiones de producción
/* Estados del juego:
    "Inicio": No hay cuentacuentos, el primer jugador en elegir carta y pista se convierte el cuentacuentos y se pasa al estado "PensandoCartas"
    "PensandoCC": El cuentacuentos está pensando, es el primer estado del turno (excepto el primer turno)
    "PensandoCartas": El cuentacuentos ha elegido carta y ahora la están eligiendo los demás jugadores.
    "Votacion": Los jugadores están votando qué carta creen que es del cuentacuentos.
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
        die('Error al conectar la base de datos');
    } 
    $sql = 'SELECT Id, Estado, Mano, Cuentacuentos, Pista, CartaElegida FROM partidas, partida_jugador WHERE Partida=Id AND Jugador=\''.$_SESSION['usuario_correo'].'\'';
    $resultado = $bbdd->query($sql);

    if ($fila = mysqli_fetch_array($resultado)){
        $id_partida = $fila[0];
        $estado = $fila[1];
        $mano_jugador = $fila[2];
        $cuentacuentos = $fila[3];
        $pista = urldecode($fila[4]);
        $carta_elegida = $fila[5];

        $sql = 'SELECT Jugador FROM `partida_jugador` WHERE `CartaElegida` is NULL AND `Partida`=\''.$id_partida.'\'';
        // die($sql);
        $resultado = $bbdd->query($sql);
        while ($fila = mysqli_fetch_array($resultado)){
            $faltan[] = $fila[0];
        }
    }else {
        die('Error: ¿La partida no existe?');
    }
    $resultado->free();

    switch($_GET['accion']){
        case 'get_estado_partida':
            $bbdd->close();
            die($estado.';'.$mano_jugador.';'.$cuentacuentos.';'.$pista.';'.$carta_elegida.';'.implode(',', $faltan));
            break;

        case 'elegir_carta_inicio':
            if ($estado!='Inicio' || $_GET['pista']==null){
                die('Error: El estado actual no es el inicial o faltan datos');
            }
            $sql = 'UPDATE `partidas` SET `Cuentacuentos`=\''.$_SESSION['usuario_correo'].'\', `Pista`=\''.$_GET['pista'].'\', `Estado`=\'PensandoCartas\', `UltActivo`=CURRENT_TIME() WHERE `Id`=\''.$id_partida.'\'';
            file_put_contents('log.txt', $sql);
            $bbdd->query($sql);
            
            // $sql = 'UPDATE `partida_jugador` SET `CartaElegida`=\''.$_GET['carta_elegida'].'\' WHERE `Jugador`=\''.$_SESSION['usuario_correo'].'\' AND `Partida`=\''.$id_partida.'\'';
            // die($sql);
            // $bbdd->query($sql);
            
            $mano_jugador = explode(':', $mano_jugador);
            for ($i=0; $i < count($mano_jugador); $i++) { 
                if ($mano_jugador[$i]==$_GET['carta_elegida']){
                    unset($mano_jugador[$i]);
                    break;
                }
            }
            $mano_jugador = implode(':', $mano_jugador);
            
            $sql = 'UPDATE `partida_jugador` SET `Mano`=\''.$mano_jugador.'\', `CartaElegida`=\''.$_GET['carta_elegida'].'\' WHERE `Jugador`=\''.$_SESSION['usuario_correo'].'\' AND `Partida`=\''.$id_partida.'\'';
            // die($sql);
            $bbdd->query($sql);
            $bbdd->close();
            foreach ($faltan as $key => $value) {
                if ($value==$_SESSION['usuario_correo']){
                    unset($faltan[$key]);
                    break;
                }
            }
            die('PensandoCartas;'.$mano_jugador.';'.$cuentacuentos.';'.$pista.';null;'.implode(',', $faltan));
            break;
    
        case 'elegir_carta_pensando_cartas':
            if ($estado!='PensandoCartas' || $carta_elegida != null){
                die('Error: El estado actual no es PensandoCartas o ya has elegido una carta');
            }
            
            $mano_jugador = explode(':', $mano_jugador);
            for ($i=0; $i < count($mano_jugador); $i++) { 
                if ($mano_jugador[$i]==$_GET['carta_elegida']){
                    unset($mano_jugador[$i]);
                    break;
                }
            }
            $mano_jugador = implode(':', $mano_jugador);

            $sql = 'UPDATE `partida_jugador` SET `Mano`=\''.$mano_jugador.'\', `CartaElegida`=\''.$_GET['carta_elegida'].'\' WHERE `Jugador`=\''.$_SESSION['usuario_correo'].'\'';

            // die($sql);
            $bbdd->query($sql);

            // Tiene que ser 1 porque es el número antes de añadir el voto actual, y sabemos a ciencia cierta que antes no estaba registrado y ahora sí
            if (count($faltan) == 1){
                $sql = 'UPDATE `partidas` SET `Estado`=\'Votacion\' WHERE `Id`=\''.$id_partida.'\'';
                $bbdd->query($sql);
                $bbdd->close();
                die('Votacion');
            } else {
                $bbdd->close();
                die('PensandoCartas');
            }
            die('Votacion;'.$mano_jugador.';null;null;'.$carta_elegida.';'.implode(',', $faltan));
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
        return $foto."?=9999999999999";
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
        die('<p class="error">No estás en ninguna partida</p><br><p class="error">Normalmente te sacaría de nuevo al menú pero por ahora es recomendable saber cuándo ocurre esto.<br><a href=".." style="background: white; color: blue; font-size: 5rem">VOLVER</a></p>');
    }
?>

<script>
// Para escribir en javascript desde php
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
<noscript>Su navegador no puede ejecutar este juego, disculpe las molestias. <a href="..">Pulse aquí para volver</a></noscript>
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
    <p id="mensajePista"></p>
</div>
<div id="tusCartas">
</div>
</body>
</html>