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
if (isset($_SESSION['iniciada'])){
    $sql = 'SELECT Count(*) FROM `salas` WHERE `Anfitrion`=\''.$_SESSION['usuario_correo'].'\'';
    if (mysqli_fetch_array($bbdd->query($sql))[0] > 0){
        ?>
        <div class="container alert-primary">
            <h2 class="mensaje_importante">No puedes crear una sala porque ya estás en una</h2>
        </div>
        <?php
        die();
    //} else if (isset($_POST['crear_sala_edixit'], $_POST['desc_edixit'], $_POST['max_edixit'], $_POST['privada_edixit'], $_POST['contra_edixit'])){
    } else if (isset($_POST['crear_sala_edixit'])){
        $sql = 'INSERT INTO `salas`(`Anfitrion`, `Descripcion`, `Maximo`'.(isset($_POST['privada_edixit']) ? ', `Contra`' : '').') 
        VALUES (\''.$_SESSION['usuario_correo'].'\',\''.$_POST['desc_edixit'].'\',\''.$_POST['max_edixit'].'\''.(isset($_POST['privada_edixit'])?',\''.$_POST['contra_edixit'].'\'':'').')';
        // var_export($_POST);
        // echo '<br>';
        // die($sql);
        $bbdd->query($sql);
        die('<script>location.href = ".."</script>');
    }
} else {
    ?>
    <div class="container alert-primary">
        <h2 class="mensaje_importante">No puedes crear una sala porque no has iniciado sesión</h2>
    </div>
    <?php
}

$bbdd->close();
?>

<script>
var iDesc, iMax, iPrivada, iContra;

function init() {
    iDesc = document.getElementById("txtDesc");
    iMax = document.getElementById("numMax");
    iPrivada = document.getElementById("cbPrivada");
    iContra = document.getElementById("pwContra");

    iDesc.addEventListener("click", function(evento) {
        iDesc.setSelectionRange(0, iDesc.value.length)
    });

    iPrivada.addEventListener("click", function(evento) {
        if (evento.target.checked) {
            iContra.disabled = false;
        } else {
            iContra.disabled = true;
        }
    });
}

function check() {
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
</script>
<div class="container">
    <div class="row">
        <div class="col-12">
            <fieldset>
                <legend>Datos de tu sala</legend>
                <form method="POST" onsubmit="return check()">
                    <div class="form-group">
                        <label for="txtDesc">Descripción</label>
                        <input type="text" class="form-control" name="desc_edixit" id="txtDesc" value="¡Vamos a pasarlo bien!" >
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

                    <?php
                    if (isset($_SESSION['iniciada'])){
                        echo '<button type="submit" name="crear_sala_edixit" class="btn btn-primary">Crear Sala</button>';
                    } else {
                        echo '<button class="btn btn-disabled" disabled>Crear Sala (debes iniciar sesión)</button>';
                    }
                    ?>
                </form>
            </fieldset>
        </div>
    </div>
</div>
</body>
</html>