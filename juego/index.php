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
    $sql = 'SELECT Nombre, Jugador, Posicion, Mano FROM usuarios, partida_jugador WHERE Partida IN (SELECT Id FROM partidas WHERE Id IN (SELECT Partida FROM partida_jugador WHERE Jugador = \''.$_SESSION['usuario_correo'].'\')) AND Jugador = Correo';
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

<body>
<!-- <body onload="init()"> -->
<?php
    // die($estado);
    if ($error){
        die('<p class="error">'.$error.'</p>');
    } else if (!$jugadores){
        die('<p class="error">No estás en ninguna partida zopenco</p>');
    }
?>
<script>
// Crear evento compatible con múltiples navegadores
var crearEvento = (function() {
    function w3c_crearEvento(elemento, evento, mifuncion) {
        elemento.addEventListener(evento, mifuncion, false);
    }

    function ie_crearEvento(elemento, evento, mifuncion) {
        var fx = function() {
            mifuncion.call(elemento);
        };
        elemento.attachEvent("on" + evento, fx);
    }
    if (typeof window.addEventListener !== "undefined") {
        return w3c_crearEvento;
    } else if (typeof window.attachEvent !== "undefined") {
        return ie_crearEvento;
    }
})();

//AJAX
function objetoXHR() {
    if (window.XMLHttpRequest) {
        return new XMLHttpRequest();
    } else if (window.ActiveXObject) {
        var versionesIE = new Array(
            "Msxml2.XMLHTTP.5.0",
            "Msxml2.XMLHTTP.4.0",
            "Msxml2.XMLHTTP.3.0",
            "Msxml2.XMLHTTP",
            "Microsoft.XMLHTTP"
        );
        for (var i = 0; i < versionesIE.length; i++) {
            try {
                return new ActiveXObject(versionesIE[i]);
            } catch (errorControlado) {}
        }
    }
    throw new Error("No se pudo crear el objeto XMLHttpRequest");
}
function cargarAsync(url) {
    if (miXHR) {
        document.getElementById("indicadorAJAX").innerHTML = "<img src='imgs/ajax-loading.gif'/>";
        miXHR.open('GET', url, true);
        miXHR.onreadystatechange = estadoPeticion;
        miXHR.send(null);
    }
}
function estadoPeticion() {
    if (this.readyState == 4 && this.status == 200) {
        var resultados = eval('(' + this.responseText + ')');
        texto = "<table border=1><tr><th>Nombre Centro </th><th>Localidad</th> <th> Provincia </th><th>Telefono</th> <th> Fecha Visita </th><th>Numero Visitantes</th> </tr>";
        for (var i = 0; i < resultados.length; i++) {
            objeto = resultados[i];
            texto += "<tr><td>" + objeto.nombrecentro + "</td><td>" +
                objeto.localidad + "</td><td>" + objeto.provincia + "</td><td>" +
                objeto.telefono + "</td><td>" + objeto.fechavisita + "</td><td>" +
                objeto.numvisitantes + "</td></tr>";
        }
        document.getElementById("indicador").innerHTML = "";
        document.getElementById("resultados").innerHTML = texto;
    }
}

var ajaxXHR, body, divTusCartas, divJugadores, jugadores, imgPerfil, tuMano, estadoJuego;

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

tuMano = new Array(<?php echo implode(',', $tu_mano);?>);
    
crearEvento(window, "load", init);

function init() {
    miXHR = new objetoXHR();
    
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
        // console.log(jugador.img);
    });
}
</script>
<div id="indicadorAJAX">
    <!-- <img src='imgs/ajax-loading.gif'/> -->
</div>

<div id="jugadores">
    <h1>Jugadores</h1>
</div>
<div id="tablero">
    <img src="cartas/carta1.jpg" alt="xd">
</div>
<div id="tusCartas">
    <!-- <h1>Tus Cartas:</h1> -->
</div>
</body>
</html>