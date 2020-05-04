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
<div class="container-fluid bg-danger text-danger-text pt-3 pb-2 mt-1 mb-5">
    <p class="mensaje_importante">Debido a problemas recientes es posible que si creó su cuenta de usuario en el último mes deba crear su cuenta de nuevo.<br>Disculpe las molestias.</p>
</div>

<div class="container">
    <fieldset>
        <!-- <legend class="ml-2">Introduce tus datos</legend> -->
        <legend class="ml-2">Iniciar Sesión</legend>
        <form method="POST">
            <div class="form-group">
                <label for="usuario">Correo</label>
                <input type="text" class="form-control" name="correo_edixit" id="usuario" <?php if (isset($usuario)) echo 'value="'.$usuario.'"'; ?> required>
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

<?php
if (isset($usuario)){
    echo '
    <script>
        document.getElementById("contra").focus();
    </script>
    ';
}
?>
</body>

</html>