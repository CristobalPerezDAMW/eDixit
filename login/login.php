<?php
session_start();
if (!isset($usuario, $contra)){
    die('Error interno c0001');
}
require($ruta.'/bbdd.php');
$enlace = mysqli_connect($BBDD->servidor, $BBDD->usuario, $BBDD->contra, $BBDD->bbdd);

if (!$enlace) {
    echo "Error: No se pudo conectar a MySQL." . PHP_EOL;
    echo "errno de depuración: " . mysqli_connect_errno() . PHP_EOL;
    echo "error de depuración: " . mysqli_connect_error() . PHP_EOL;
    $error = 'Error al conectar a la base de datos';
}else {
    $usuarioE = $enlace->real_escape_string($usuario);
    $contraE = $enlace->real_escape_string($contra);
    $sql = 'SELECT Nombre, Verificacion FROM usuarios WHERE Correo=\''.$usuarioE.'\' AND Contra=MD5(\''.$contraE.'\')';
    $consulta = mysqli_query($enlace, $sql);

    if ($consulta) {
        if (mysqli_num_rows($consulta)==0){
            $error = 'Usuario o contraseña incorrectos';
        }else {
            $obj = $consulta->fetch_object();
            $_SESSION['usuario_correo'] = $usuario;
            $_SESSION['usuario_nombre'] = $obj->Nombre;
            $_SESSION['verificada'] = $obj->Verificacion==null?true:false;
            $_SESSION['verificacion'] = $obj->Verificacion;
            $_SESSION['iniciada'] = true;
            $error = false;
            $consulta->close();
            unset($obj);
        }
    } else {
        $error = 'Error del servidor login-1';
    }
}

mysqli_close($enlace);
?>