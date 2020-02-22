<?php
$ruta='..';
$pag = 'Registro';

if (isset($_SESSION['iniciada'])){
    header('Location: ..');
} else if (isset($_POST['registrarse'], $_POST['nombre_edixit'], $_POST['correo_edixit'], $_POST['contra_edixit'])){
    $nombre = $_POST['nombre_edixit'];
    $correo = $_POST['correo_edixit'];
    $contra = $_POST['contra_edixit'];
    require('registro.php');
    // if (!$error){
    //     header('Location: ../usuario/editar');
    // }
}
require("../cabecera.php");
// $error = 'La página no hace nada: no se ha implementado todavía';
?>
<div class="container">
    <fieldset>
        <legend class="ml-2">Introduce tus datos</legend>
        <form method="POST" onsubmit="return check()">
            <div class="form-group">
                <label for="nombre">Nombre</label>
                <input type="text" class="form-control" name="nombre_edixit" id="nombre" aria-describedby="nombreHelp"
                    required>
                <small id="nombreHelp" class="form-text text-muted">Será el nombre que se mostrará a los demás
                    usuarios</small>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control" name="correo_edixit" id="email" aria-describedby="emailHelp"
                    required>
                <small id="emailHelp" class="form-text text-muted">Deberá verificar su correo para poder acceder al
                    juego y editar su perfil</small>
            </div>
            <div class="form-group ">
                <label for="contra">Contraseña</label>
                <input type="password" class="form-control" name="contra_edixit" id="contra" minlength="8" required>
            </div>
            <div class="form-group ">
                <label for="contra">Repita su contraseña</label>
                <input type="password" class="form-control" id="contra2" minlength="8" required>
            </div>

            <?php
            if (isset($error) && $error){
                echo '<p class="alert-danger">'.$error.'</p>';
            }
            ?>
            <button type="submit" class="btn btn-primary btn-outline-primary-text" name="registrarse">Registrar</button>
            <span class="ml-1 mr-1"> o, si ya tienes una cuenta, </span>
            <a href="../login">Inicia Sesión</a>
        </form>
    </fieldset>
    <script>
    function check() {
        if (document.getElementById("contra").value != document.getElementById("contra2").value) {
            alert("Las contraseñas no coinciden");
            return false;
        }
        return true;
    }
    </script>
</div>
</body>

</html>