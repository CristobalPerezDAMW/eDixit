<?php
if (!isset($_SESSION['iniciada'])){
    header('Locacion: ../../login');
    die();
}
if (!$_SESSION['verificada']){
    header('Location: ..');
    die();
}
if (!isset($imagen) && !isset($nombre) && !isset($correo) && !isset($contra)){
    die('Error interno c0002');
}

$cambios = false;
if (!empty($nombre) 
    // || !empty($correo) 
    || !empty($contra)) {
    $cambios = true;
    $enlace = mysqli_connect('localhost', 'usuario_dixit', 'jy8-YBk*WV..DVM', 'db_dixit');

    if (!$enlace) {
        echo "Error: No se pudo conectar a MySQL." . PHP_EOL;
        echo "errno de depuración: " . mysqli_connect_errno() . PHP_EOL;
        echo "error de depuración: " . mysqli_connect_error() . PHP_EOL;
        $error = 'Error al conectar a la base de datos';
    } else {
        $sql = 'UPDATE usuarios SET '
        .(empty($nombre)?'':'Nombre=\''.$enlace->real_escape_string($nombre).'\'')
        // .(empty($correo)?'':'Correo=\''.$enlace->real_escape_string($correo).'\'')
        .(empty($contra)?'':'Contra=MD5(\''.$enlace->real_escape_string($contra).'\')')
        .'WHERE Correo=\''.$_SESSION['usuario_correo'].'\''
        ;
        var_dump($sql);

        if ($enlace->query($sql) === TRUE) {
            if (!empty($nombre))
                $_SESSION['usuario_nombre'] = $nombre;
            // if (!empty($correo))
            //     $_SESSION['usuario_correo'] = $correo;
        } else {
            /*
            https://www.fromdual.com/mysql-error-codes-and-messages
            */
            switch($enlace->errno){
                default:
                    $error = 'Hubo un error inesperado del servidor, no se pudo actualizar el usuario ('.($enlace->errno).')<br>Si el problema persiste, contacta con un administrador';
            }
        }
    }

    mysqli_close($enlace);
}

if (!empty($imagen['tmp_name'])){
    $cambios = true;
    if ($imagen['error'] === UPLOAD_ERR_OK){
        $tipo = mime_content_type($imagen['tmp_name']);
        if (strpos($tipo, 'image/')!==false){
            $carpeta_subidas = '../../perfiles/';

            // $nombre = $_SESSION['usuario_correo'];
            // foreach ($prohibidas as $prohibida -> $reemplazo) {
            //     $nombre = str_replace($prohibida, $reemplazo, $nombre);
            // }
            require('../../definiciones.php');
            $nombre = parsearNombreArchivo($_SESSION['usuario_correo']).'.foto';
            
            $fichero = $carpeta_subidas.$nombre;
            if (!move_uploaded_file($imagen['tmp_name'], $fichero)){
                $error = 'Error del servidor';
            }
        } else {
            $error = 'La imagen es de un tipo no permitido';
        }
    } else {
        if ($imagen['error']==1){
            $error = 'Error: No se permiten archivos tan grandes';
        } else if ($imagen['error']==2){
            $error = 'Error: No se permiten archivos tan grandes en esta subida';
        } else if ($imagen['error']==3){
            $error = 'Error: La subida del archivo fue interrumpida';
        } else if ($imagen['error']==4){
            $error = 'Error: No se subió ningún archivo';
        } else if ($imagen['error']==6){
            // La carpeta temporal no está accesible
            $error = 'Error: Error del servidor 6';
        } else if ($imagen['error']==7){
            // No se pudo escribir el fichero en el disco
            $error = 'Error: Error del servidor 7';
        } else if ($imagen['error']==8){
            // Una extensión de php detuvo la subida
            $error = 'Error: Error del servidor 8';
        } else {
            $error = 'Error: No se pudo subir el archivo ('.$imagen['error'].')';
        }
    }
} 
if (!$cambios) {
    var_dump($imagen);
    die();
    $error = 'No hubo cambios ';
}
?>