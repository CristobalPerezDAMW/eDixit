<?php
session_start();
$ruta='..';
$pag = 'Mi Perfil';
if (!isset($_SESSION['iniciada'])){
    header('Location: ../login');
}
require("../cabecera.php");
// echo '<img src="'.$foto.'"/>';
?>

<div class="container">
    <div class="row">
        <div class="col-12 d-flex justify-content-center">
            <?php
            echo '<img class="perfil-grande" src="'.$foto.'" alt="Tu foto de perfil"/>';
            ?>
        </div>
        <div class="col-12 d-flex justify-content-center">
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
            echo '<p class="error">'.($_SESSION['verificada']?'':'¡Tienes que verificar tu cuenta!').'</p>';
            ?>
        </div>
        <div class="col-12 d-flex flex-column align-items-center justify-content-center">
            <?php 
            echo '<p>'.($_SESSION['verificada']?'':'<a href="verificar/enviar_correo.php?volver=../editar">Volver a enviar email</a></p><p>¿Tienes <a href="verificar/informacion">problemas para recibir el correo de verificación</a>?').'</p>';
            ?>
        </div>
    </div>
</div>

<div class="container mt-5">
    <div class="row">
        <div class="col-12 d-flex justify-content-center">
            <h2>Pronto: Tus stats</h2>
            <p></p>
        </div>
    </div>
</div>

</body>

</html>