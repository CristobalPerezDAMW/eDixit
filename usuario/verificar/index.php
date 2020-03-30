<?php
    session_start();
    if (!isset($_GET['verificacion'], $_GET['correo'])){
        header('Location: ..');
    }
    require('../../bbdd.php');
    $enlace = mysqli_connect($BBDD->servidor, $BBDD->usuario, $BBDD->contra, $BBDD->bbdd);
    
    $cod = $enlace->real_escape_string($_GET['verificacion']);
    $correo = urldecode($_GET['correo']);
    $correoE = $enlace->real_escape_string($correo);

    if ($enlace) {
        $sql = 'SELECT Nombre, Verificacion FROM usuarios WHERE Correo=\''.$correoE.'\' AND Verificacion=\''.$cod.'\'';
        // die($sql);
        $consulta = mysqli_query($enlace, $sql);

        if ($consulta) {
            if (mysqli_num_rows($consulta)>0){
                $sql = 'UPDATE usuarios SET Verificacion = NULL WHERE Correo = \''.$correoE.'\'';
                $consulta = mysqli_query($enlace, $sql);
                if ($consulta) {
                    if (isset($_SESSION['iniciada'])){
                        if ($correo == $_SESSION['usuario_correo']){
                            $_SESSION['verificada'] = true;
                            unset($_SESSION['verificacion']);
                            $cerrada = false;
                            // echo '<h1>Su correo ha sido verificado con éxito</h1>';
                        } else {
                            session_destroy();
                            $_SESSION = array();
                            // echo '<h1>Su correo ha sido verificado con éxito, se deberá iniciar sesión de nuevo</h1>';
                            $cerrada = true;
                        }
                    } else{
                        $cerrada = false;
                        // echo '<h1>Su correo ha sido verificado con éxito</h1>';
                    }
                    
                    $ruta = '../..';
                    $pag = 'Correo Verificado';
                    require('../../cabecera.php');
                    if ($cerrada){
                        echo '<h1>Su correo ha sido verificado con éxito, se deberá iniciar sesión de nuevo</h1>';
                    } else {
                        echo '<h1>Su correo ha sido verificado con éxito</h1>';
                    }
                } else {
                    echo '<h1>Ha habido un error al verificar su cuenta</h1>';
                    echo '<p>Lamentamos las molestias, por favor comunique el siguiente error a un administrador\nError: '.$enlace->errno.'<br>'.$sql.'</p>';
                }
            }else {
                header('Location: ..');
            }
        } else {
            header('Location: ..');
        }
    } else {
        echo "Error: No se pudo conectar a MySQL." . PHP_EOL;
        echo "errno de depuración: " . mysqli_connect_errno() . PHP_EOL;
        echo "error de depuración: " . mysqli_connect_error() . PHP_EOL;
        die('Error al conectar a la base de datos');
    }

    mysqli_close($enlace);
?>