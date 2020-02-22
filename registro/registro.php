<?php

session_start();

if (!isset($nombre, $correo, $contra)){
    die('Error interno c0002');
}

$enlace = mysqli_connect('localhost', 'usuario_dixit', 'jy8-YBk*WV..DVM', 'db_dixit');

if (!$enlace) {
    echo "Error: No se pudo conectar a MySQL." . PHP_EOL;
    echo "errno de depuración: " . mysqli_connect_errno() . PHP_EOL;
    echo "error de depuración: " . mysqli_connect_error() . PHP_EOL;
    $error = 'Error al conectar a la base de datos';
} else if (empty($nombre)){
    $error = 'Debes introducir un nombre';
} else if (empty($correo)){
    $error = 'Debes introducir tu correo';
} else if (empty($contra)){
    $error = 'Debes introducir una contraseña';
} else if (strlen($contra)<8){
    $error = 'Por tu seguridad, por favor usa una contraseña de al menos 8 caracteres';
} else if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    $error = 'El correo introducido no es válido';
} else {
    $nombreE = $enlace->real_escape_string($nombre);
    $correoE = $enlace->real_escape_string($correo);
    $contraE = $enlace->real_escape_string($contra);

    $charsVer = 'qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM1234567890';
    $max = strlen($charsVer)-1;
    $codigoVerificacion = '';
    for ($i=0; $i < 30; $i++) { 
        $codigoVerificacion .= $charsVer[rand(0, $max)];
    }
    $sql = 'INSERT INTO usuarios (Nombre, Correo, Contra, Verificacion) VALUES (\''.$nombreE.'\', \''.$correoE.'\', MD5(\''.$contraE.'\'), \''.$codigoVerificacion.'\')';
    // die($sql);

    if ($enlace->query($sql) === TRUE) {
        $_SESSION['usuario_correo'] = $correo;
        $_SESSION['usuario_nombre'] = $nombre;
        $_SESSION['verificada'] = false;
        $_SESSION['verificacion'] = $codigoVerificacion;
        $_SESSION['iniciada'] = true;

        //Correo de verificación
        // ini_set('SMTP','myserver');
        // ini_set('smtp_port',25);

        // $para      = $correo;
        // $titulo    = 'Verifica tu cuenta en eDixit';
        // $mensaje   = 'Hola, para verificar tu cuenta haz click <a href="http://localhost/eDixit/usuario/verificar.php/?verificacion='.urlencode($codigoVerificacion).'&correo='.urlencode($correo).'>aquí</a>';
        // $cabeceras = 'From: webmaster@example.com' . "\r\n" .
        //     'Reply-To: webmaster@example.com' . "\r\n" .
        //     'X-Mailer: PHP/' . phpversion();

        // require('../usuario/verificar/enviar_correo.php');
        header('Location: ../usuario/verificar/enviar_correo.php?volver=../editar');
    // mysqli_close($enlace);
    // die();
    } else {
        /*
        https://www.fromdual.com/mysql-error-codes-and-messages
        */
        switch($enlace->errno){
            case 1062:
                $error = 'Ya hay un usuario registrado con el correo '.$correo;
                break;
            default:
                $error = 'Hubo un error inesperado del servidor, no se pudo registrar el usuario ('.($enlace->errno).')<br>Si el problema persiste, contacta con un administrador';
        }
    }
}

mysqli_close($enlace);
?>