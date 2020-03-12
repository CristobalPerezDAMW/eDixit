<?php
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

$bbdd = @mysqli_connect('localhost', 'usuario_dixit', 'jy8-YBk*WV..DVM', 'db_dixit');
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
    $sql = 'SELECT Nombre, Jugador, Posicion, Mano FROM usuarios, partida_jugador WHERE Partida IN (SELECT Id FROM Partidas WHERE Id IN (SELECT Partida FROM partida_jugador WHERE Jugador = \''.$_SESSION['usuario_correo'].'\')) AND Jugador = Correo';
    // die($sql);
    $resultado = $bbdd->query($sql);

    while (($fila = mysqli_fetch_array($resultado))){
        if ($fila[1]==$_SESSION['usuario_correo']){
            $tu_mano = explode(':', $fila[3]);
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

<body onload="init()">
<?php
    // die($estado);
    if ($error){
        die('<p class="error">'.$error.'</p>');
    } else if (!$jugadores){
        die('<p class="error">No estás en ninguna partida zopenco</p>');
    }
?>
<script>
var body, divTusCartas, divJugadores, jugadores, imgPerfil, tuMano, estadoJuego;

estadoJuego = "<?php echo $estado ?>";

jugadores = new Array(
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

tuMano = new Array(<?php echo implode(', ', $tu_mano);?>);
    

function init() {
    body = document.body;
    divTusCartas = document.getElementById("tusCartas");
    divJugadores = document.getElementById("jugadores");
    var nodoDivCartas = document.createElement("div");

    tuMano.forEach(carta => {
        img = new Image();
        img.src = "cartas/carta" + carta + ".jpg"
        img.title = "Carta "+carta;
        nodoDivCartas.appendChild(img);
    });
    divTusCartas.appendChild(nodoDivCartas);

    jugadores.forEach(jugador => {
        let src = jugador.img;
        jugador.img = new Image();
        jugador.img.src = src;
        jugador.img.title = jugador.nombre+" ("+jugador.correo+")";

        if (jugador.correo=="<?php echo $cuentacuentos?>"){
            jugador.img.classList.add("cuentacuentos");
            jugador.img.title += " ¡Cuentacuentos!";
        }
        if (jugador.correo=="<?php echo $_SESSION['usuario_correo']?>"){
            jugador.img.classList.add("tuPerfil");
        }

        divJugadores.appendChild(jugador.img);
        console.log(jugador.img);
    });
}
</script>

<div id="jugadores">
    <h1>Jugadores</h1>
</div>
<div id="tablero">
</div>
<div id="tusCartas">
    <h1>Tus Cartas:</h1>
</div>
</body>
</html>