<?php
$ruta='..';
$pag = 'Iniciar Sesión';

if (isset($_SESSION['iniciada'])){
    header('Location: ..');
} else if (isset($_POST['iniciar_sesion'], $_POST['correo_edixit'], $_POST['contra_edixit'])){
    $usuario = $_POST['correo_edixit'];
    $contra = $_POST['contra_edixit'];
    require('login.php');
    if (!$error){
        header('Location: ..');
    }
} 
require("../cabecera.php");
?>
<div class="container">
    <fieldset>
        <legend class="ml-2">Introduce tus datos</legend>
        <form method="POST">
            <div class="form-group">
                <label for="usuario">Correo</label>
                <input type="text" class="form-control" name="correo_edixit" id="usuario" <?php if (isset($malUsuario)) echo $malUsuario; ?> required>
            </div>
            <div class="form-group ">
                <label for="contra">Contraseña</label>
                <input type="password" class="form-control" name="contra_edixit" id="contra" required>
            </div>

            <?php
            if (isset($error) && $error){
                echo '<p class="alert-danger">'.$error.'</p>';
            }
            ?>
            <a href="../registro">Crea una Cuenta</a>
            <span class="ml-1 mr-1"> o </span>
            <button type="submit" class="btn btn-primary btn-outline-primary-text" name="iniciar_sesion">Iniciar
                Sesión</button>
        </form>
    </fieldset>
</div>
</body>

</html>