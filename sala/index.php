<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 'on');

require('../bbdd.php');
$bbdd = mysqli_connect($BBDD->servidor, $BBDD->usuario, $BBDD->contra, $BBDD->bbdd);
if (!$bbdd){
    die('<p style="color: red;">Error al conectar la base de datos</p>');
}

if (isset($_GET['accion'])){
    switch ($_GET['accion']) {
        case 'get_estado_sala':
            if (!isset($_GET['sala'])){
                die('Error: Información incompleta');
            }
            // die('admin@admin.ga:1;cristichi@hotmail.es:1;Chiquito de la Calzada:1');
            $sql = 'SELECT `Id` FROM `salas` WHERE `Id`=\''.$_GET['sala'].'\'';
            // die($sql);
            $resultado = $bbdd->query($sql);
            if (!mysqli_fetch_array($resultado)){
                die('No existe');
            }

            $sql = 'SELECT `Jugador`, `Listo`, `Maximo` FROM `salas`, `sala_jugador` WHERE `Id`=`Sala` AND `Id`=\''.$_GET['sala'].'\'';
            // die($sql);
            $resultado = $bbdd->query($sql);
            if ($resultado===false){
                die('Error: '.$bbdd->errno);
            }
            $todos = true;
            $participantes = [];
            while ($fila = mysqli_fetch_array($resultado)){
                $participantes[] = $fila[0].':'.$fila[1];
                if ($fila[1]!=1){
                    $todos = false;
                }
                $max = $fila[2];
            }
            if ($todos && isset($max) && count($participantes)+1>=$max){
                die('Empezar');
            }
            die(join(';', $participantes));
        case 'salir':
            if (!isset($_GET['sala'])){
                die('Error: Información incompleta');
            }
            // die('admin@admin.ga:1;cristichi@hotmail.es:1;Chiquito de la Calzada:1');
            $sql = 'DELETE FROM `sala_jugador` WHERE `Jugador`= \''.$_SESSION['usuario_correo'].'\' AND `Sala`=\''.$_GET['sala'].'\'';
            $bbdd->query($sql);
            $sql = 'DELETE FROM `salas` WHERE `Anfitrion`= \''.$_SESSION['usuario_correo'].'\'';
            $bbdd->query($sql);
            die('Has salido');

        case 'estoy_listo':
            if (!isset($_GET['sala'])){
                die('Error: Información incompleta');
            }
            $return = 'Listo';
            $sql = 'SELECT `Jugador`, `Listo`, `Anfitrion`, `Maximo` FROM `sala_jugador`, `salas` WHERE `Id`=`Sala` AND `Sala`=\''.$_GET['sala'].'\'';
            $resultado = $bbdd->query($sql);
            if ($resultado===false){
                die('Error: Error al acceder a la base de datos');
            }
            $cont = 0;
            while($fila = mysqli_fetch_array($resultado)){
                if ($fila[1]==0){
                    $cont++;
                    $jugador_falta = $fila[0];
                }
                $jugadores[] = array($fila[0], '');
                $host = $fila[2];
                $max = $fila[3];
            }
            $jugadores[] = array($host, '');
            //Si falta 1 y es el que va a estar listo ahora, y la sala está llena, empieza el juego
            if (count($jugadores)==$max && $cont==1){
                if ($jugador_falta==$_SESSION['usuario_correo']){
                    $resultado = $bbdd->query($sql);
                    //Vamos a preparar la partida, necesitamos generar primero las manos de los jugadores y también guardar las cartas sobrantes. Lo haremos a partir del número de cartas (Y), sabiendo que existen todas las cartas del formato carta(X).jpg donde X es un número entero dentro de [1, Y]
                    //(El máximo hay que modificarlo si se agregan o quitan cartas)
                    //Pasos a seguir:
                    //Paso 1: generamos el array de números y lo barajamos
                    $max = 50;
                    for ($i=1; $i <= $max; $i++) { 
                        $cartas[] = $i; 
                    }
                    shuffle($cartas);
                    //Paso 2: Damos 6 cartas a cada jugador
                    if (count($jugadores)*6 >= $max){
                        die('Error: No hay cartas para tantos jugadores ('.(count($jugadores)*6).' >= '.$max.')');
                    }
                    for($i=0; $i<count($jugadores); $i++){
                        $jugadores[$i][1] = array_shift($cartas).':'.array_shift($cartas).':'.array_shift($cartas).':'.array_shift($cartas).':'.array_shift($cartas).':'.array_shift($cartas);
                    }
                    
                    //Paso 4: Creamos la partida
                    $bbdd->begin_transaction();

                    $bbdd->query('INSERT INTO `partidas`(`Id`, `CartasPila`)
                    VALUES (\''.$_GET['sala'].'\', \''.join(':', $cartas).'\')');
                    foreach($jugadores as $j){
                        $bbdd->query('INSERT INTO `partida_jugador`(`Partida`, `Jugador`, `Mano`)
                        VALUES (\''.$_GET['sala'].'\', \''.$j[0].'\', \''.$j[1].'\')');
                    }
                    $bbdd->commit();
                    $return = 'Empezar';
                }
            }

            $sql = 'UPDATE `sala_jugador` SET `Listo`=\'1\' WHERE `Jugador`=\''.$_SESSION['usuario_correo'].'\'';
            $bbdd->query($sql);

            die($return);
    }
}

$ruta='..';
$pag = 'Salas Públicas';
require("../cabecera.php");

if (!isset($_SESSION['iniciada'])){
    echo '<h3 class="salas mb-4">Recuerda que debes <a href="../login">iniciar sesión</a> para poder unirte a una sala</h3>';
} else {
    //Si se entra a la página en un intento de entrar en una sala, si no es correcto se mostrará un mensaje y luego las salas, si es correcto se agregará a la sala y se actualizará la página, mostrando la sala al estar en una
    if (isset($_REQUEST['sala'])){
        $sql = 'SELECT `Id`, `Anfitrion`, `Descripcion`, `Maximo`, `Contra` FROM `salas` WHERE `Id` = '.$_REQUEST['sala'];
        $resultado = $bbdd->query($sql);
        if ($resultado===false){
            echo '<h3 class="salas">Error al conectar, sentimos las molestias</h3>';
        } else if ($fila = mysqli_fetch_array($resultado)){
            if ($fila[4]===null || isset($_REQUEST['contra']) && $fila[4]==$_REQUEST['contra']){
                //Acceso a la base de datos, INSERT
                $sql = 'INSERT INTO `sala_jugador` (`Sala`, `Jugador`) VALUES (\''.$_REQUEST['sala'].'\', \''.$_SESSION['usuario_correo'].'\');';
                $bbdd->query($sql);
                die('<script>window.location.href = window.location.href</script>');
            } else {
                echo '<h3 class="salas" style="color: red;">La contraseña es incorrecta</h3>';
            }
        } else {
            echo '<h3 class="salas">Esta sala ya no existe</h3>';
        }
    }

    //Si el usuario ya está en una sala, no se mostrará otra cosa más que la sala
    // $sql = 'SELECT `Id`, `Anfitrion`, `Descripcion`, `Maximo` FROM `salas`, `sala_jugador` WHERE `Id`=`Sala` AND (`Jugador`=\''.$_SESSION['usuario_correo'].'\' OR `Anfitrion`=\''.$_SESSION['usuario_correo'].'\')';
    $sql = 'SELECT `Id`, `Anfitrion`, `Descripcion`, `Maximo`, `Jugador` FROM `salas` LEFT JOIN `sala_jugador` ON (`Id`=`Sala` AND  `Jugador`=\''.$_SESSION['usuario_correo'].'\') OR `Anfitrion`=\''.$_SESSION['usuario_correo'].'\'';
    // $sql = 'SELECT `Id`, `Anfitrion`, `Descripcion`, `Maximo` FROM `salas`, `sala_jugador` WHERE `Id`=`Sala` AND  `Jugador`=\''.$_SESSION['usuario_correo'].'\'';
    // die($sql);
    $resultado = $bbdd->query($sql);
    if ($resultado===false){
        die('<h3 class="salas">Error al conectar con la base de datos, sentimos las molestias</h3>');
    } else if ($fila = mysqli_fetch_array($resultado)){
        $id = $fila[0];
        $host = $fila[1];
        $participantes[0] = array($fila[1], 1);
        $desc = $fila[2];
        $max = $fila[3];
        $jug = $fila[4];

        if ($host==$_SESSION['usuario_correo'] || $jug==$_SESSION['usuario_correo']){
            $sql = 'SELECT `Jugador`, `Listo` FROM `salas`, `sala_jugador` WHERE `Id`=`Sala` AND `Anfitrion`=\''.$host.'\'';
            // die($sql);
            $resultado = $bbdd->query($sql);
            $listo = 1;
            $num_jug = 1;
            while ($fila = mysqli_fetch_array($resultado)){
                $participantes[] = array($fila[0], $fila[1]);
                $num_jug++;
                if ($fila[0]==$_SESSION['usuario_correo']){
                    $listo = $fila[1];
                }
            }
            echo '<div class="container sala" id="containerSala">
                <div class="row cabecera">
                    <div class="col-12">
                        Sala de '.$host.'
                    </div>
                </div>
                <div class="row subcabecera">
                    <div class="col-12">
                        Jugadores: '.$num_jug.'/'.$max.'
                    </div>
                </div>';
            foreach ($participantes as $p) {
                echo '<div class="row jugador" id="jug-'.$p[0].'" data-listo="'.$p[1].'">
                    <div class="col-sm-6">'.
                        $p[0].'
                    </div>
                    <div class="col-sm-6">'.
                        ($p[1]==0
                        ?'<svg aria-hidden="true" focusable="false" data-prefix="far" data-icon="square" class="svg-inline--fa fa-square fa-w-14" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="red" d="M400 32H48C21.5 32 0 53.5 0 80v352c0 26.5 21.5 48 48 48h352c26.5 0 48-21.5 48-48V80c0-26.5-21.5-48-48-48zm-6 400H54c-3.3 0-6-2.7-6-6V86c0-3.3 2.7-6 6-6h340c3.3 0 6 2.7 6 6v340c0 3.3-2.7 6-6 6z"></path></svg> No preparado'
                        :'<svg aria-hidden="true" focusable="false" data-prefix="far" data-icon="check-square" class="svg-inline--fa fa-check-square fa-w-14" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="green" d="M400 32H48C21.49 32 0 53.49 0 80v352c0 26.51 21.49 48 48 48h352c26.51 0 48-21.49 48-48V80c0-26.51-21.49-48-48-48zm0 400H48V80h352v352zm-35.864-241.724L191.547 361.48c-4.705 4.667-12.303 4.637-16.97-.068l-90.781-91.516c-4.667-4.705-4.637-12.303.069-16.971l22.719-22.536c4.705-4.667 12.303-4.637 16.97.069l59.792 60.277 141.352-140.216c4.705-4.667 12.303-4.637 16.97.068l22.536 22.718c4.667 4.706 4.637 12.304-.068 16.971z"></path></svg> Preparado').'
                    </div>
                </div>';
            }
            echo '</div>
            </div>
        <div class="container" id="containerListo">
            <div class="row">
                <div class="col-12">
                    <button id="btnSalir">Salir de la sala</button>
                </div>
            </div>
        </div>';
            if ($listo==0){
                echo '<div class="container" id="containerListo">
                    <div class="row">
                        <div class="col-12">
                            <button id="btnListo">Estoy Preparado</button>
                        </div>
                    </div>
                </div>';
            }
            ?>
            <script>
            //Necesario para ir actualizando los datos de la sala
            var sala = <?php echo $id?>;
            var urlGet = "<?php echo $_SERVER['PHP_SELF'] ?>";
            var ajaxXHR;
            var containerSala = document.getElementById("containerSala");
            var btnListo = document.getElementById("btnListo");
            var btnSalir = document.getElementById("btnSalir");

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

            function getAsync(url) {
                if (ajaxXHR) {
                    ajaxXHR.open('GET', encodeURI(url), true);
                    ajaxXHR.onreadystatechange = enPeticionLista;
                    ajaxXHR.send(null);
                }
            }

            function enPeticionLista() {
                if (this.readyState == 4 && this.status == 200) {
                        console.log(this.responseText);
                    if (this.responseText.startsWith("Error") !== false) {
                        console.log(this.responseText);
                    } else if (this.responseText == "Empezar") {
                        window.location.href = "../juego";
                    } else if (this.responseText == "Listo") {
                        btnListo.parentNode.removeChild(btnListo);
                    } else if (this.responseText == "Has salido" || this.responseText == "No existe") {
                        location.reload();
                    } else if (this.responseText != "") {
                        let participantes = this.responseText.split(";");
                        
                        participantes.forEach(p => {
                            let jugador = p.split(':');
                            let listo = jugador[1];
                            jugador = jugador[0];
                            
                            let elementoYaExistente = document.getElementById("jug-"+jugador);
                            if (elementoYaExistente==null || elementoYaExistente.dataset.listo != listo){
                                if (elementoYaExistente!=null){
                                    containerSala.removeChild(elementoYaExistente);
                                }
                                let divRowJugador = document.createElement("div");
                                divRowJugador.classList.add("row");
                                divRowJugador.classList.add("jugador");
                                divRowJugador.id = "jug-"+jugador;
                                divRowJugador.dataset.listo = listo;
                                
                                let divRowJugCorreo = document.createElement("div");
                                divRowJugCorreo.classList.add("col-sm-6");
                                divRowJugCorreo.innerHTML = jugador;
                                divRowJugador.appendChild(divRowJugCorreo);
                                
                                let divRowJugListo = document.createElement("div");
                                divRowJugListo.classList.add("col-sm-6");
                                if (listo==0){
                                    divRowJugListo.innerHTML = '<svg aria-hidden="true" focusable="false" data-prefix="far" data-icon="square" class="svg-inline--fa fa-square fa-w-14" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="red" d="M400 32H48C21.5 32 0 53.5 0 80v352c0 26.5 21.5 48 48 48h352c26.5 0 48-21.5 48-48V80c0-26.5-21.5-48-48-48zm-6 400H54c-3.3 0-6-2.7-6-6V86c0-3.3 2.7-6 6-6h340c3.3 0 6 2.7 6 6v340c0 3.3-2.7 6-6 6z"></path></svg> No preparado';
                                }else {
                                    divRowJugListo.innerHTML = '<svg aria-hidden="true" focusable="false" data-prefix="far" data-icon="check-square" class="svg-inline--fa fa-check-square fa-w-14" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="green" d="M400 32H48C21.49 32 0 53.49 0 80v352c0 26.51 21.49 48 48 48h352c26.51 0 48-21.49 48-48V80c0-26.51-21.49-48-48-48zm0 400H48V80h352v352zm-35.864-241.724L191.547 361.48c-4.705 4.667-12.303 4.637-16.97-.068l-90.781-91.516c-4.667-4.705-4.637-12.303.069-16.971l22.719-22.536c4.705-4.667 12.303-4.637 16.97.069l59.792 60.277 141.352-140.216c4.705-4.667 12.303-4.637 16.97.068l22.536 22.718c4.667 4.706 4.637 12.304-.068 16.971z"></path></svg> Preparado';
                                }
                                divRowJugador.appendChild(divRowJugListo);
                                
                                containerSala.appendChild(divRowJugador);
                            }
                        });
                    }
                }
            }

            async function pedirEstadoSala() {
                //Este método repite cada X tiempo la acción de actualizar los datos de la partida, una acción costosa que no debería realizarse con demasiada frecuencia para que cuanto más se haga mejor
                getAsync(urlGet + "?accion=get_estado_sala&sala="+sala);
                setTimeout(() => {
                    pedirEstadoSala();
                }, 2000);
            };

            function init(){
                ajaxXHR = new objetoXHR();
                if (btnListo != null){
                    crearEvento(btnListo, "click", function() {
                        getAsync(urlGet+"?accion=estoy_listo&sala="+sala);
                    });
                }
                crearEvento(btnSalir, "click", function() {
                    if (confirm('¿Seguro que quieres salir? Si creaste la sala, la borrarás')){
                        getAsync(urlGet+"?accion=salir&sala="+sala);
                        location.href = location.href;    
                    }
                });
                pedirEstadoSala();
            }
            </script>
            <?php
            die();
        }
    }
}


$sql = 'SELECT `Id`, `Anfitrion`, `Descripcion`, `Maximo`, `Contra`, COUNT(`sala_jugador`.Jugador)+1 FROM `salas` LEFT OUTER JOIN `sala_jugador` ON `Sala` = `Id` GROUP BY `Id`, `Anfitrion`, `Descripcion`, `Maximo`, `Contra`';
$resultado = $bbdd->query($sql);
if ($resultado===false || $resultado->num_rows==0){
    echo '<h3 class="salas">No hay salas, pero tú puedes <a href="crear">crear una sala</a>.</h3>';
} else {
    echo '<div class="container salas">
        <div class="row cabecera">
            <div class="col-md-1 col-6">
            </div>
            <div class="col-md-3 col-6">
                Anfitrión
            </div>
            <div class="col-md-3 col-6">
                Mensaje
            </div>
            <div class="col-md-2 col-6">
                Participantes
            </div>
            <div class="col-md-3 d-md-block d-none">
            </div>
        </div>';
    
    //TODO: nada por GET, todo por POST
    while ($fila = mysqli_fetch_array($resultado)){
        // `Id`, `Anfitrion`, `Descripcion`, `Maximo`, `Contra`, count(`sala_jugador`.Jugador)
        $salas[] = array($fila[0], $fila[1], $fila[2], $fila[3], $fila[4]===NULL? 'false': 'true');
        echo '<div class="row">
            <div class="col-md-1 col-6">'.
                ($fila[4]===null 
                ?'<svg title="Sin Contraseña" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="lock-open" class="svg-inline--fa fa-lock-open fa-w-18" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path fill="#ba9c4a" d="M423.5 0C339.5.3 272 69.5 272 153.5V224H48c-26.5 0-48 21.5-48 48v192c0 26.5 21.5 48 48 48h352c26.5 0 48-21.5 48-48V272c0-26.5-21.5-48-48-48h-48v-71.1c0-39.6 31.7-72.5 71.3-72.9 40-.4 72.7 32.1 72.7 72v80c0 13.3 10.7 24 24 24h32c13.3 0 24-10.7 24-24v-80C576 68 507.5-.3 423.5 0z"></path></svg>'
                :'<svg title="Con Contraseña" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="lock" class="svg-inline--fa fa-lock fa-w-14" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="#ba9c4a" d="M400 224h-24v-72C376 68.2 307.8 0 224 0S72 68.2 72 152v72H48c-26.5 0-48 21.5-48 48v192c0 26.5 21.5 48 48 48h352c26.5 0 48-21.5 48-48V272c0-26.5-21.5-48-48-48zm-104 0H152v-72c0-39.7 32.3-72 72-72s72 32.3 72 72v72z"></path></svg>')
            .'</div>
            <div class="col-md-3 col-6">
                <p>'.$fila[1].'</p>
            </div>
            <div class="col-md-3 col-6">
                <p>'.$fila[2].'</p>
            </div>
            <div class="col-md-2 col-6">
                <p>'.$fila[5].'/'.$fila[3].'</p>
            </div>
            <div class="col-md-3">
                <form id="form-'.$fila[0].'" method="POST">
                    <input type="hidden" name="sala" value="'.$fila[0].'"/>
                    <input type="hidden" id="contra-'.$fila[0].'" name="contra" value=""/>
                    <input type="submit" id="btn-'.$fila[0].'" '.(isset($_SESSION['iniciada'])?'':'disabled').' name="unirse" value="Unirse"/>
                </form>
            </div>
        </div>';
    }
    echo '</div>';
}

$bbdd->close();

if (isset($_SESSION['iniciada']) && isset($salas)){
    echo '<script>
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
        var salas = [';
        foreach ($salas as $sala){
            echo '["'.$sala[0].'", document.getElementById("form-'.$sala[0].'"), "'.$sala[1].'", "'.$sala[2].'", "'.$sala[4].'"], ';
        }
        echo '];
        
        for(let i=0; i<salas.length; i++){
            salas[i][1].dataset.indice = i;
            crearEvento(salas[i][1], "submit", function(evento){
                let sala = salas[evento.target.dataset.indice];
                if (sala[4]=="true"){
                    let contra = prompt("Introduce la contraseña de la sala");
                    if (contra==""){
                        alert("Debes introducir la contraseña para entrar en esta sala");
                        return false;
                    } else if (contra!=null){
                        // window.location.href = encodeURI("?sala="+sala[0]+"&contra="+contra);
                        document.getElementById("contra-"+sala[0]).value= contra;
                    }
                } else {
                    // window.location.href = "?sala="+sala[0];
                }
                return true;
            });
        }
    </script>';
}

echo '<!-- Iconos de sala con y sin contraseña - https://fontawesome.com/license-->';
?>

</body>
</html>