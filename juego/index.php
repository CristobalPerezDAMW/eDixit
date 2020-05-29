<?php
error_reporting(E_ALL);
ini_set('display_errors', 'on');
header("Access-Control-Allow-Origin: *");
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
        if ($resultado!==false){
            while ($fila = mysqli_fetch_array($resultado)){
                $faltan_elegir[] = $fila[0];
            }
        }
        $sql = 'SELECT `Jugador`, `Posicion` FROM `partida_jugador` WHERE `Partida`=\''.$id_partida.'\'';
        $resultado = $bbdd->query($sql);
        while ($fila = mysqli_fetch_array($resultado)){
            $posiciones[] = $fila[0].':'.$fila[1];
            if ($fila[1]>=30 && $estado!='Puntuacion'){
                $estado = 'Final';
                $sql = 'UPDATE `partidas` SET `Estado` = \'Final\'';
                // die($sql);
                $bbdd->query($sql);
            }
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
        } else if ($estado == 'Final'){
            // $sql = 'SELECT COUNT(*) FROM `partida_jugador` WHERE `FinalVisto`=\'0\' AND `Partida`=\''.$id_partida.'\'';
            // // die($sql);
            // $resultado = $bbdd->query($sql);
            // $faltan_votar = array();
            // if ($resultado!==false)
            //     if ($fila = mysqli_fetch_array($resultado)){
            //         if ($fila[0]==0){
            //             $sql = 'DELETE FROM `partidas` WHERE `Id`=\''.$id_partida.'\'';
            //             $bbdd->query($sql);
            //             $sql = 'DELETE FROM `salas` WHERE `Id`=\''.$id_partida.'\'';
            //             $bbdd->query($sql);
            //         }
            //     }

            // $sql = 'DELETE FROM `partidas` WHERE `Id`=\''.$id_partida.'\'; DELETE FROM `salas` WHERE `Id`=\''.$id_partida.'\';';
            // $bbdd->multi_query($sql);

            // $bbdd->begin_transaction();
            // $bbdd->query('SELECT SLEEP(5);');
            // $sql = 'DELETE FROM `partidas` WHERE `Id`=\''.$id_partida.'\'';
            // $bbdd->query($sql);
            // $sql = 'DELETE FROM `salas` WHERE `Id`=\''.$id_partida.'\'';
            // $bbdd->query($sql);
            // $bbdd->commit();
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
            (isset($puntuacion_ronda) ? ';'.$puntuacion_ronda : ';null').
            ';'.implode(',', $posiciones)
            );

        case 'elegir_carta_inicio':
            if ($estado!='Inicio' || !isset($_GET['carta_elegida']) || !isset($_GET['pista']) || $_GET['pista']==null){
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

            $sql = 'SELECT `CartasDescartadas` FROM `partidas` WHERE `Id`=\''.$id_partida.'\'';
            $resultado = $bbdd->query($sql);
            if ($fila = mysqli_fetch_array($resultado)){
                if ($fila[0]==''){
                    $pilaDescartes = $_GET['carta_elegida'];
                } else {
                    $pilaDescartes = $fila[0].':'.$_GET['carta_elegida'];
                }
            }

            $sql = 'UPDATE `partidas` SET `CartasPila` = \''.$pilaCartas.'\', `CartasDescartadas`=\''.$pilaDescartes.'\' WHERE `Id`=\''.$id_partida.'\'';
            file_put_contents('log.txt', $sql);
            $bbdd->query($sql);
            
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

        case 'elegir_carta_pensando_cc':
            if ($estado!='PensandoCC' || $carta_elegida != null || $cuentacuentos != $_SESSION['usuario_correo']){
                die('Error: El estado actual no es PensandoCC, ya habías elegido o no eres el cuentacuentos');
            }

            if (!isset($_GET['pista']) || $_GET['pista']==null){
                die('Error: No se ha proporcionado una pista');
            }
            
            $mano_jugador = explode(':', $mano_jugador);
            for ($i=0; $i < count($mano_jugador); $i++) { 
                if ($mano_jugador[$i]==$_GET['carta_elegida']){
                    unset($mano_jugador[$i]);
                    break;
                }
            }
            $mano_jugador = implode(':', $mano_jugador);
            
            $sql = 'SELECT `CartasDescartadas` FROM `partidas` WHERE `Id`=\''.$id_partida.'\'';
            $resultado = $bbdd->query($sql);
            if ($fila = mysqli_fetch_array($resultado)){
                if ($fila[0]==''){
                    $pilaDescartes = $_GET['carta_elegida'];
                } else {
                    $pilaDescartes = $fila[0].':'.$_GET['carta_elegida'];
                }
            }

            $sql = 'UPDATE `partida_jugador` SET `Mano`=\''.$mano_jugador.'\', `CartaElegida`=\''.$_GET['carta_elegida'].'\' WHERE `Jugador`=\''.$_SESSION['usuario_correo'].'\'';
            // die($sql);
            $bbdd->query($sql);

            $sql = 'UPDATE `partidas` SET `Pista`=\''.$_GET['pista'].'\', `Estado`=\'PensandoCartas\', `CartasDescartadas`=\''.$pilaDescartes.'\', `UltActivo`=CURRENT_TIME() WHERE `Id`=\''.$id_partida.'\'';
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
            
            $sql = 'SELECT `CartasDescartadas` FROM `partidas` WHERE `Id`=\''.$id_partida.'\'';
            $resultado = $bbdd->query($sql);
            if ($fila = mysqli_fetch_array($resultado)){
                if ($fila[0]==''){
                    $pilaDescartes = $_GET['carta_elegida'];
                } else {
                    $pilaDescartes = $fila[0].':'.$_GET['carta_elegida'];
                }
            }

            $sql = 'UPDATE `partida_jugador` SET `Mano`=\''.$mano_jugador.'\', `CartaElegida`=\''.$_GET['carta_elegida'].'\' WHERE `Jugador`=\''.$_SESSION['usuario_correo'].'\'';
            // die($sql);
            $bbdd->query($sql);

            // Tiene que ser 1 porque es el número antes de añadir la carta elegida actual, y sabemos a ciencia cierta que antes no estaba registrado y ahora sí, de modo que si sólo hay 1 en $faltan_elegir es que ya no queda nadie
            if (count($faltan_elegir) == 1){
                $sql = 'UPDATE `partidas` SET `Estado`=\'Votacion\', `UltActivo`=CURRENT_TIME(), `CartasDescartadas`=\''.$pilaDescartes.'\' WHERE `Id`=\''.$id_partida.'\'';
                $bbdd->query($sql);
                $bbdd->close();
                die('Votacion');
            } else {
                $sql = 'UPDATE `partidas` SET `UltActivo`=CURRENT_TIME(), `CartasDescartadas`=\''.$pilaDescartes.'\' WHERE `Id`=\''.$id_partida.'\'';
                $bbdd->query($sql);
                $bbdd->close();
                die('PensandoCartas');
            }

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
                    $jugadores[] = array('correo'=>$fila[0], 'elegida'=>$fila[1], 'votada'=>$fila[2], 'puntos'=>0);
                }

                /*
                admin (cc) - Elegida: 3, Votada: NULL, Puntuación deseada: 3
                cris - Elegida: 10, Votada: 13, Puntuación deseada: 0
                usuario - Elegida: 13, Votada: 3, Puntuación deseada: 3+1
                */

                //Bueno, bueno. El algoritmo para los puntos. El resumen es que los reparte como dicen las normas del juego, para lo que hacen falta muchos bucles.
                /*
                Paso 1: Si todos los votos o ninguno han acertado la carta del cuentacuentos, todos los jugadores salvo el Cuentacuentos suman 2 puntos cada uno.
                Paso 2: Si al menos 1 voto ha acertado la carta del Cuentacuentos y al menos 1 voto ha fallado, el Cuentacuentos y todos los jugadores que han acertado la carta del Cuentacuentos suman 3 puntos cada uno.
                Paso 3: Para cada jugador, excepto el Cuentacuentos, por cada voto que haya recibido su carta sumará 1 punto adicional.
                */
                $aciertan = array();
                $fallan = array();
                $log = '';
                $iJugadorActual = -1;
                for ($i=0; $i < count($jugadores); $i++) {
                    if ($jugadores[$i]['correo'] == $_SESSION['usuario_correo']){
                        $iJugadorActual = $i;
                    }

                    if ($jugadores[$i]['correo'] != $cuentacuentos){
                        for ($j=0; $j < count($jugadores); $j++) {
                            if ($jugadores[$j]['correo'] != $cuentacuentos){
                                if ($jugadores[$j]['correo'] != $jugadores[$i]['correo'] && $jugadores[$i]['elegida'] == $jugadores[$j]['votada']){
                                    $jugadores[$i]['puntos']++;
                                }
                            } else if ($jugadores[$i]['votada'] == $jugadores[$j]['elegida']) {
                                $aciertan[] = $jugadores[$i]['correo'];
                            } else {
                                $fallan[] = $jugadores[$i]['correo'];
                            }
                        }
                    }
                }
                if (count($aciertan) == 0 || count($fallan) == 0){
                    for ($i=0; $i < count($jugadores); $i++) {
                        if ($jugadores[$i]['correo'] != $cuentacuentos){
                            $jugadores[$i]['puntos'] += 2;
                        }
                        
                        $sql = 'UPDATE `partida_jugador` SET `PuntuacionRonda`=\''.$jugadores[$i]['puntos'].'\' WHERE `Jugador`=\''.$jugadores[$i]['correo'].'\' AND `Partida`=\''.$id_partida.'\'';
                        $log.="\n".$sql;
                        $bbdd->query($sql);
                    }
                } else {
                    for ($i=0; $i < count($jugadores); $i++) {
                        if ($jugadores[$i]['correo'] == $cuentacuentos){
                            $jugadores[$i]['puntos'] += 3;
                        } else {
                            for ($j=0; $j < count($aciertan); $j++) {
                                if ($jugadores[$i]['correo'] == $aciertan[$j]){
                                    $jugadores[$i]['puntos'] += 3;
                                    break;
                                }
                            }
                        }
                        
                        $sql = 'UPDATE `partida_jugador` SET `PuntuacionRonda`=\''.$jugadores[$i]['puntos'].'\' WHERE `Jugador`=\''.$jugadores[$i]['correo'].'\' AND `Partida`=\''.$id_partida.'\'';
                        $log.="\n".$sql;
                        $bbdd->query($sql);
                        
                    }
                }
                $log.="\n".var_export($jugadores, true);
                file_put_contents('log.txt', $log);
                $bbdd->close();
                // die('Puntuacion;null;null;null;null;null;;;;'.$jugadores[$iJugadorActual]['puntos']);
                die('Puntuacion');
            } else {
                $bbdd->close();
                // die('Votacion;null;null;null;null;null;;;;'.$jugadores[$iJugadorActual]['puntos']);
                die('Votacion');
            }

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

                $sql = 'SELECT `CartasPila`, `CartasDescartadas` FROM `partidas` WHERE `Id`=\''.$id_partida.'\'';
                $resultado = $bbdd->query($sql);
                if ($fila = mysqli_fetch_array($resultado)){
                    // Si ya no quedan cartas, usamos las descartadas
                    if ($fila[0] == ''){
                        $mazo = explode(':', $fila[1]);
                        $descartes = '';
                    } else {
                        $mazo = explode(':', $fila[0]);
                        $descartes = $fila[1];
                    }
                }
                shuffle($mazo);

                for ($i=0; $i < count($jugadores); $i++) {
                    $jugadores[$i][1].=':'.array_shift($mazo);
                    $sql = 'UPDATE `partida_jugador` SET `Mano`=\''.$jugadores[$i][1].'\' WHERE `Jugador`=\''.$jugadores[$i][0].'\' AND `Partida`=\''.$id_partida.'\'';
                    $bbdd->query($sql);
                }

                $sql = 'UPDATE `partidas` SET `CartasPila`=\''.implode(':', $mazo).'\', `CartasDescartadas`=\''.$descartes.'\', `UltActivo`=CURRENT_TIME() WHERE `Id`=\''.$id_partida.'\'';
                $bbdd->query($sql);

                $bbdd->close();
                die('PensandoCC');
            } else {
                $bbdd->close();
                die('PensandoCC;null;null;null;null;null;;;;;true');
            }
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
        die('<script>window.location.href = "..";</script>');
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
        img: "'.$jugador['Foto'].'",
        posicion: "'.$jugador['Posicion'].'"
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