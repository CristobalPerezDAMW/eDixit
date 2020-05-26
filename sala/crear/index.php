<?php
error_reporting(E_ALL);
ini_set('display_errors', 'on');
$ruta='../..';
$pag = 'Crear Sala Pública';
require("../../cabecera.php");
require('../../bbdd.php');

$bbdd = mysqli_connect($BBDD->servidor, $BBDD->usuario, $BBDD->contra, $BBDD->bbdd);

if (!$bbdd){
    die('<p style="color: red;">Error al conectar la base de datos</p>');
}
$sql = 'SELECT Count(*) FROM `salas` WHERE `Anfitrion`=\''.$_SESSION['usuario_correo'].'\'';
if (mysqli_fetch_array($bbdd->query($sql))[0] > 0){
    ?>
        <div class="container alert-primary">
            <h2 class="mensaje_importante">No puedes crear una sala porque ya estás en una</h2>
        </div>
    <?php
    die();
}

$bbdd->close();
?>

<script>
function submit() {
    if (iMax.value <= 2) {
        alert("No pueden jugar menos de 3 personas a eDixit");
    } else if (iMax.value > 12) {
        alert("Por motivos técnicos, por favor no permitas más de 12 personas en la sala");
    } else if (iPrivada.checked && iContra.value == "") {
        alert("Si seleccionas que la sala es privada debes escribir una contraseña");
    } else {
        return true;
    }
    return false;
};

function init() {
    // var iDesc = document.getElementById("txtDesc");
    var iMax = document.getElementById("numMax");
    var iPrivada = document.getElementById("cbPrivada");
    var iContra = document.getElementById("pwContra");

    iPrivada.addEventListener("click", function(evento) {
        if (evento.target.checked) {
            iContra.disabled = false;
        } else {
            iContra.disabled = true;
        }
    });
}
</script>
<div class="container">
    <div class="row">
        <div class="col-12">
            <fieldset>
                <legend>Datos de tu sala</legend>
                <form method="POST" onsubmit="submit(); return false;">
                    <div class="form-group">
                        <label for="txtDesc">Descripción</label>
                        <input type="text" class="form-control" name="desc_edixit" id="txtDesc" placeholder="¡Vamos a pasarlo bien!">
                    </div>
                    <div class="form-group">
                        <label for="numMax">Máximo de personas</label>
                        <input type="number" class="form-control" name="max_edixit" id="numMax" aria-describedby="helpMax" value="3" min="3" max="12" autocomplete="off">
                        <small id="helpMax" class="form-text text-muted">El número de personas que podrán entrar en la sala. Una vez se llene, la partida comienza.</small>
                    </div>
                    <div class="form-check">
                        <label class="form-check-label">
                            <input type="checkbox" class="form-check-input" name="privada_edixit" id="cbPrivada">
                            Privada
                        </label>
                    </div>

                    <div class="form-group">
                        <label for="pwContra">Contraseña</label>
                        <input type="password" class="form-control" name="contra_edixit" id="pwContra" disabled autocomplete="off">
                    </div>

                    <button type="submit" value="crear_sala_edixit" class="btn btn-primary">Crear Sala</button>
                </form>
            </fieldset>
        </div>
    </div>
</div>
</body>
</html>