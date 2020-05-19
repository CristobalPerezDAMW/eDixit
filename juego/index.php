<?php
error_reporting(E_ALL);
ini_set('display_errors', 'on');
// Destáquese que todos los comentarios desaparecerán en las versiones de producción
/* Estados del juego:
    "Inicio": No hay cuentacuentos, el primer jugador en elegir carta y pista se convierte el cuentacuentos y se pasa al estado "PensandoCartas"
    "PensandoCC": El cuentacuentos está pensando, es el primer estado del turno (excepto el primer turno)
    "PensandoCartas": El cuentacuentos ha elegido carta y ahora la están eligiendo los demás jugadores.
    "Votacion": Los jugadores están votando qué carta creen que es del cuentacuentos.
    "Puntuacion": Se están repartiendo los puntos. Se toma un tiempo en este paso para que todos los jugadores vean cómo van.
    "Final": La partida ha terminado.
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
    $sql = 'SELECT Id, Estado, Posicion, Mano, Cuentacuentos, Pista, CartaElegida FROM partidas, partida_jugador WHERE Partida=Id AND Jugador=\''.$_SESSION['usuario_correo'].'\'';
    $resultado = $bbdd->query($sql);

    if ($fila = mysqli_fetch_array($resultado)){
        $id_partida = $fila[0];
        $estado = $fila[1];
        $posicion = $fila[2];
        $mano_jugador = $fila[3];
        $cuentacuentos = $fila[4];
        $pista = urldecode($fila[5]);
        $carta_elegida = $fila[6];

        $sql = 'SELECT Jugador FROM `partida_jugador` WHERE `CartaElegida` is NULL AND `Partida`=\''.$id_partida.'\'';
        // die($sql);
        $resultado = $bbdd->query($sql);
        $faltan_elegir = array();
        if ($resultado!==false)
            while ($fila = mysqli_fetch_array($resultado)){
                $faltan_elegir[] = $fila[0];
            }

        if ($estado=='Votacion'){
            $sql = 'SELECT `CartaElegida` FROM `partida_jugador` WHERE `Partida`=\''.$id_partida.'\'';
            $resultado = $bbdd->query($sql);
            while ($fila = mysqli_fetch_array($resultado)){
                $cartas_votacion[] = $fila[0];
            }
            shuffle($cartas_votacion);
            
            $sql = 'SELECT `Jugador` FROM `partida_jugador` WHERE `CartaVotada` is NULL AND `Partida`=\''.$id_partida.'\'';
            // die($sql);
            $resultado = $bbdd->query($sql);
            $faltan_votar = array();
            if ($resultado!==false)
                while ($fila = mysqli_fetch_array($resultado)){
                    $faltan_votar[] = $fila[0];
                }

            $sql = 'SELECT `CartaVotada` FROM `partida_jugador` WHERE `Jugador`=\''.$_SESSION['usuario_correo'].'\' AND `Partida`=\''.$id_partida.'\'';
            $resultado = $bbdd->query($sql);
            $carta_votada = 'null';
            if ($resultado!==false)
                $carta_votada = mysqli_fetch_array($resultado)[0];
        } else if ($estado == 'Puntuacion'){
            $sql = 'SELECT `PuntuacionRonda` FROM `partida_jugador` WHERE `Jugador`=\''.$_SESSION['usuario_correo'].'\' AND `Partida`=\''.$id_partida.'\'';
            $puntuacion_ronda = mysqli_fetch_array($bbdd->query($sql))[0];
        }
    }else {
        die('Error: El jugador no está en ninguna partida');
    }
    if ($resultado!==false)
        $resultado->free();

    switch($_GET['accion']){
        case 'get_estado_partida':
            $bbdd->close();
            die($estado.';'.$mano_jugador.';'.$cuentacuentos.';'.$pista.';'.$carta_elegida.';'.implode(',', $faltan_elegir).
            (isset($cartas_votacion) ? ';'.implode(',',$cartas_votacion) .';'.$carta_votada.';'.implode(',',$faltan_votar) : ';;null;').
            (isset($puntuacion_ronda) ? ';'.$puntuacion_ronda : 'null')
            );
            break;

        case 'elegir_carta_inicio':
            if ($estado!='Inicio' || !isset($_GET['pista']) || $_GET['pista']==null){
                die('Error: El estado actual no es el inicial o faltan datos');
            }

            $sql = 'UPDATE `partidas` SET `Cuentacuentos`=\''.$_SESSION['usuario_correo'].'\', `Pista`=\''.$_GET['pista'].'\', `Estado`=\'PensandoCartas\', `UltActivo`=CURRENT_TIME() WHERE `Id`=\''.$id_partida.'\'';
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
            foreach ($faltan_elegir as $key => $value) {
                if ($value==$_SESSION['usuario_correo']){
                    unset($faltan_elegir[$key]);
                    break;
                }
            }
            die('PensandoCartas;'.$mano_jugador.';'.$cuentacuentos.';'.$pista.';null;'.implode(',', $faltan_elegir));
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

            // Tiene que ser 1 porque es el número antes de añadir la carta elegida actual, y sabemos a ciencia cierta que antes no estaba registrado y ahora sí, de modo que si sólo hay 1 en $faltan_elegir es que ya no queda nadie
            if (count($faltan_elegir) == 1){
                $sql = 'UPDATE `partidas` SET `Estado`=\'Votacion\', `UltActivo`=CURRENT_TIME() WHERE `Id`=\''.$id_partida.'\'';
                $bbdd->query($sql);
                $bbdd->close();
                die('Votacion');
            } else {
                $bbdd->close();
                die('PensandoCartas');
            }
            // die('PensandoCartas;'.$mano_jugador.';null;null;'.$carta_elegida.';'.implode(',', $faltan_elegir));
            break;

            case 'votar_carta':
                if ($estado!='Votacion' || !isset($_GET['carta_votada']) || isset($carta_votada)){
                    die('Error: El estado actual no es Votacion o ya has elegido una carta');
                }
    
                $sql = 'UPDATE `partida_jugador` SET `CartaVotada`=\''.$_GET['carta_votada'].'\' WHERE `Jugador`=\''.$_SESSION['usuario_correo'].'\'';
                $bbdd->query($sql);
    
                // Tiene que ser 2 porque es el número antes de añadir el voto actual, y sabemos a ciencia cierta que antes no estaba registrado y ahora sí, de modo que si sólo hay 2 en $faltan_votar es que antes de añadir este voto sólo quedaban el actual y el Cuentacuentos, que no vota
                if (count($faltan_votar) == 2){
                    $sql = 'UPDATE `partidas` SET `Estado`=\'Puntuacion\', `UltActivo`=CURRENT_TIME() WHERE `Id`=\''.$id_partida.'\'';
                    $bbdd->query($sql);

                    $sql = 'SELECT `Jugador`, `CartaElegida`, `CartaVotada` FROM `partida_jugador` WHERE `Partida`=\''.$id_partida.'\'';
                    $resultado = $bbdd->query($sql);
                    while ($fila = mysqli_fetch_array($resultado)){
                        //En el tercer valor de cada jugador se guardan sus puntos para esta ronda
                        $jugadores[$fila[0]] = array($fila[1], $fila[2], 0);
                    }

                    /*
                    admin - Elegida: 3, Votada: NULL, Puntuación deseada: 3
                    cris - Elegida: 10, Votada: 3, Puntuación deseada: 3
                    usuario - Elegida: 13, Votada: 10, Puntuación deseada: 0
                    */

                    //Bueno, bueno. El algoritmo para los puntos. El resumen es que los reparte como dicen las normas del juego, para lo que hacen falta muchos bucles.
                    $x3Acierto = false;
                    $x3Fallo = false;
                    foreach ($jugadores as $jugador => $datos) {
                        foreach ($jugadores as $jJ => $dJ) {
                            if ($jJ != $cuentacuentos){
                                if ($dJ[1] == $datos[0]) {
                                    $datos[2]++;
                                }
                            }
                            
                        }
                        if ($datos[1] == $jugadores[$cuentacuentos][0]) {
                            $x3Acierto = true;
                        } else {
                            $x3Fallo = true;
                        }
                    }
                    foreach ($jugadores as $jugador => $datos) {
                        if ($x3Acierto===true && $x3Fallo===true) {
                            if ($jugador==$cuentacuentos || $datos[1] == $jugadores[$cuentacuentos][0]) {
                                $datos[2]+= 3;
                            }
                        } else if ($jugador != $cuentacuentos) {
                            $datos[2]+= 2;
                        }
                        $sql = 'UPDATE `partida_jugador` SET `PuntuacionRonda`=\''.$datos[2].'\' WHERE `Jugador`=\''.$jugador.'\' AND `Partida`=\''.$id_partida.'\'';
                        $bbdd->query($sql);
                    }

                    $bbdd->close();
                    die('Puntuacion;null;null;null;null;null;;;;'.$jugadores[$_SESSION['usuario_correo']][2]);
                } else {
                    $bbdd->close();
                    die('Votacion');
                }
                break;

            case 'aceptar_puntuacion':
                if ($estado!='Puntuacion' || !isset($puntuacion_ronda)){
                    die('Error: El estado actual no es Puntuacion o ya has aceptado');
                }
    
                $sql = 'UPDATE `partida_jugador` SET `CartaVotada`=NULL, `CartaElegida`=NULL, PuntuacionRonda=NULL, `Posicion`='.($posicion + $puntuacion_ronda).' WHERE `Jugador`=\''.$_SESSION['usuario_correo'].'\'';
                $bbdd->query($sql);
                
                $sql = 'SELECT Count(*) FROM `partida_jugador` WHERE `PuntuacionRonda` is not NULL AND `Partida`=\''.$id_partida.'\'';
                $resultado = $bbdd->query($sql);
                $faltan_aceptar = mysqli_fetch_array($resultado)[0];

                //En este caso sería 0 porque se hace antes el UPDATE del actual y después el COUNT para saber cuántos quedan
                if ($faltan_aceptar == 0){
                    $sql = 'SELECT `Jugador`, `Mano` FROM `partida_jugador` WHERE `Partida`=\''.$id_partida.'\' ORDER BY `Jugador`';
                    $resultado = $bbdd->query($sql);
                    $cc = 0;
                    $i = -1;
                    while ($fila = mysqli_fetch_array($resultado)){
                        $i++;
                        $jugadores[$i] = array($fila[0], $fila[1]);
                        if ($fila[0] == $cuentacuentos){
                            $cc = $i;
                        }
                    }
                    if ($cc == $i){
                        $nuevo_cc = $jugadores[0][0];
                    }else {
                        $nuevo_cc = $jugadores[$cc+1][0];
                    }

                    $sql = 'UPDATE `partidas` SET `Estado`=\'PensandoCC\', `Cuentacuentos`=\''.$nuevo_cc.'\' WHERE `Id`=\''.$id_partida.'\'';
                    $bbdd->query($sql);

                    $sql = 'SELECT CartasPila FROM `partidas` WHERE `Id`=\''.$id_partida.'\'';
                    $mazo = explode(':', mysqli_fetch_array($bbdd->query($sql))[0]);
                    shuffle($mazo);

                    for ($i=0; $i < count($jugadores); $i++) { 
                        $jugadores[$i][1].=':'.array_shift($mazo);
                        $sql = 'UPDATE `partida_jugador` SET `Mano`=\''.$jugadores[$i][1].'\' WHERE `Jugador`=\''.$jugadores[$i][0].'\' AND `Partida`=\''.$id_partida.'\'';
                        $bbdd->query($sql);
                    }

                    $sql = 'UPDATE `partidas` SET `CartasPila`=\''.implode(':', $mazo).'\', `UltActivo`=CURRENT_TIME() WHERE `Id`=\''.$id_partida.'\'';
                    $bbdd->query($sql);

                    $bbdd->close();
                    die('PensandoCC');
                } else {
                    $bbdd->close();
                    die('PensandoCC;null;null;null;null;null;;;;;true');
                }
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
        <div id="cartasVotacion">
            <!-- <div>
                <img src="cartas/carta1.jpg" alt="Cartita">
                <p>1</p>
            </div>
            <div>
                <img src="cartas/carta20.jpg" alt="Cartita">
                <p>2</p>
            </div>
            <div>
                <img src="cartas/carta13.jpg" alt="Cartita">
                <p>3</p>
            </div> -->
        </div>
        <button id="aceptarPuntuacion">Aceptar</button>
    </div>
    <img id="mensajeImagen"/>
    <p id="mensajePista"></p>
</div>
<div id="tusCartas">
</div>
</body>
</html>