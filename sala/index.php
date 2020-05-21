<?php
error_reporting(E_ALL);
ini_set('display_errors', 'on');
$ruta='..';
$pag = 'Salas Públicas';
require("../cabecera.php");
require('../bbdd.php');


if (!isset($_SESSION['iniciada'])){
    echo '<h3 class="salas mb-4">Recuerda que debes <a href="../login">iniciar sesión</a> para poder unirte a una sala</h3>';
}

$bbdd = mysqli_connect($BBDD->servidor, $BBDD->usuario, $BBDD->contra, $BBDD->bbdd);

if (!$bbdd){
    die('<p style="color: red;">Error al conectar la base de datos</p>');
}

$sql = 'SELECT Id, Anfitrion, Descripcion, Contra FROM salas';
$resultado = $bbdd->query($sql);
if ($resultado===false || $resultado->num_rows==0){
    // $salas = false;
    echo '<h3 class="salas">No hay salas, pero tú puedes <a href="crear">crear una sala</a>.</h3>';
} else {
    echo '<div class="container salas">
        <div class="row cabecera">
            <div class="col-1">
            </div>
            <div class="col-4">
                Anfitrión
            </div>
            <div class="col-4">
                Mensaje
            </div>
            <div class="col-3">
            </div>
        </div>';
    while ($fila = mysqli_fetch_array($resultado)){
        $salas[] = array($fila[0], $fila[1], $fila[2], $fila[3]===NULL? 'false': 'true');
        echo '<div class="row">
            <div class="col-1">'.
                ($fila[3]===null 
                ?'<svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="lock-open" class="svg-inline--fa fa-lock-open fa-w-18" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path fill="#ba9c4a" d="M423.5 0C339.5.3 272 69.5 272 153.5V224H48c-26.5 0-48 21.5-48 48v192c0 26.5 21.5 48 48 48h352c26.5 0 48-21.5 48-48V272c0-26.5-21.5-48-48-48h-48v-71.1c0-39.6 31.7-72.5 71.3-72.9 40-.4 72.7 32.1 72.7 72v80c0 13.3 10.7 24 24 24h32c13.3 0 24-10.7 24-24v-80C576 68 507.5-.3 423.5 0z"></path></svg>'
                :'<svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="lock" class="svg-inline--fa fa-lock fa-w-14" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="#ba9c4a" d="M400 224h-24v-72C376 68.2 307.8 0 224 0S72 68.2 72 152v72H48c-26.5 0-48 21.5-48 48v192c0 26.5 21.5 48 48 48h352c26.5 0 48-21.5 48-48V272c0-26.5-21.5-48-48-48zm-104 0H152v-72c0-39.7 32.3-72 72-72s72 32.3 72 72v72z"></path></svg>')
            .'</div>
            <div class="col-4">
                <p>'.$fila[1].'</p>
            </div>
            <div class="col-4">
                <p>'.$fila[2].'</p>
            </div>
            <div class="col-3">
                <button id="btn-'.$fila[0].'" '.(isset($_SESSION['iniciada'])?'':'disabled').'>Unirse</button>
            </div>
        </div>';
    }
    echo '</div>';
}

$bbdd->close();

if (isset($_SESSION['iniciada'])){
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
            echo '["'.$sala[0].'", document.getElementById("btn-'.$sala[0].'"), "'.$sala[1].'", "'.$sala[2].'", "'.$sala[3].'"], ';
        }
        echo '];
        
        for(let i=0; i<salas.length; i++){
            salas[i][1].dataset.indice = i;
            crearEvento(salas[i][1], "click", function(evento){
                let sala = salas[evento.target.dataset.indice];
                if (sala[4]=="true"){
                    let contra = prompt("Introduce la contraseña de la sala");
                    if (contra==""){
                        alert("Debes introducir la contraseña para entrar en esta sala");
                    } else if (contra!=null)
                        window.location.href = encodeURI("?sala="+sala[0]+"&contra="+contra);
                } else {
                    window.location.href = "?sala="+sala[0];
                }
            });
        }
    </script>';
}
echo '<!-- Iconos de sala con y sin contraseña - https://fontawesome.com/license-->';
?>

</body>
</html>