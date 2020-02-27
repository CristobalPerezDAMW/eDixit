<?php
session_start();
$ruta='../..';
$pag = 'Editar Perfil';
if (!isset($_SESSION['iniciada'])){
    header('Location: ../../login');
} else if (isset($_POST['actualizar_info'])){
    $imagen = $_FILES['imagen_edixit'];
    $nombre = $_POST['nombre_edixit'];
    $correo = $_POST['correo_edixit'];
    $contra = $_POST['contra_edixit'];
    require('editar.php');
    if (!$error){
        header('Location: ..');
    }
}
require("../../cabecera.php");

?>
<script src="verificar.js">
</script>
<div class="container">
    <div class="row">
        <div class="col-12 d-flex justify-content-center">
            <?php
            echo '<img class="perfil-grande" src="'.$foto.'" alt="Tu foto de perfil"/>';
            ?>
        </div>
        <div class=" col-12 d-flex justify-content-center">
            <?php 
            echo '<h1>'.$_SESSION['usuario_nombre'].'</h1>';
            ?>
        </div>
        <div class="col-12 d-flex justify-content-center">
            <?php 
            echo '<p>'.$_SESSION['usuario_correo'].'</p>';
            ?>
        </div>
        <div class="col-12 d-flex justify-content-center">
            <?php 
            echo '<p class="error">'.($_SESSION['verificada']?'':'Tienes que verificar tu cuenta para editar tu perfil').'</p>';
            ?>
        </div>
        <div class="col-12 d-flex justify-content-center">
            <?php 
            echo '<p>'.($_SESSION['verificada']?'':'<a href="../verificar/enviar_correo.php?volver=../editar">Volver a enviar email</a>').'</p>';
            ?>
        </div>
    </div>
</div>

<div class="container">
    <fieldset>
        <legend class="ml-2">Cambiar tus datos</legend>
        <?php 
            if ($_SESSION['verificada'])
                echo '<form method="POST" enctype="multipart/form-data" onsubmit="return check()">';
        ?>
        <div class="form-group">
            <label for="imagen">Cambiar imagen de perfil</label>
            <input type="file" class="form-control-file" name="imagen_edixit" id="cImagen" accept="image/*"
                placeholder="" aria-describedby="imagenHelp" title="¡Prueba a subir una imagen animada!">
            <small id="imagenHelp" class="form-text text-muted">No seleccione ningún archivo si no desea cambiar su
                foto</small>
        </div>
        <div class="form-group">
            <label for="nombre">Nombre</label>
            <input type="text" class="form-control" name="nombre_edixit" id="cNombre" aria-describedby="nombreHelp">
            <small id="nombreHelp" class="form-text text-muted">Deje el campo en blanco si no quiere cambiar su
                nombre</small>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" class="form-control" name="correo_edixit" id="cEmail" aria-describedby="emailHelp">
            <small id="emailHelp" class="form-text text-muted">Deje el campo en blanco si no quiere cambiar su
                email</small>
        </div>
        <div class="form-group ">
            <label for="contra">Contraseña</label>
            <input type="password" class="form-control" name="contra_edixit" id="cContra1" minlength="8"
                aria-describedby="contraHelp">
            <small id="contraHelp" class="form-text text-muted">Deje este campo en blanco si no desea
                cambiar la contraseña</small>
        </div>
        <div class="form-group ">
            <label for="contra">Repita su contraseña</label>
            <input type="password" class="form-control" id="cContra2" minlength="8">
        </div>

        <?php
            if (isset($error) && $error){
                echo '<p class="alert-danger">'.$error.'</p>';
            }
            ?>
        <?php 
        echo'<button type="submit"
                class="btn '.($_SESSION['verificada']?'btn-primary btn-outline-primary-text"':'btn-disabled btn-outline-disabled-text" disabled').' name="actualizar_info">Actualizar información</button>';?>
        </form>
    </fieldset>
</div>
</body>

</html>