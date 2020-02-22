<?php
    session_start();
    if (!isset($_GET['verificacion'], $_GET['correo'])){
        header('Location: ..');
    }
    $enlace = mysqli_connect('localhost', 'usuario_dixit', 'jy8-YBk*WV..DVM', 'db_dixit');
    
    $cod = $enlace->real_escape_string($_GET['verificacion']);
    $correoE = $enlace->real_escape_string(urldecode($_GET['correo']));

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
                        session_destroy();
                        $_SESSION = array();
                        $iniciada = true;
                    } else
                        $iniciada = false;
                    
                    $ruta = '../..';
                    $pag = 'Correo Verificado';
                    require('../../cabecera.php');
                    if ($iniciada){
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