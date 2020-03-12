<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($ruta)){
    $ruta = '.';
}
if (!isset($pag)){
    $pag = 'Inicio';
}
// die($ruta);
$actual = $_SERVER['PHP_SELF'];
include($ruta.'/definiciones.php');

$foto = $ruta.'/imgs/sin_foto.png';
if (isset($_SESSION['iniciada'])){
    $fotoBuena = $ruta.'/perfiles/'.parsearNombreArchivo($_SESSION['usuario_correo']).'.foto';
    if (is_file($fotoBuena)){
        $foto = $fotoBuena;
        unset($fotoBuena);
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <?php
    echo '
        <link rel="stylesheet" href="'.$ruta.'/css/main.css">
        <link rel="shortcut icon" href="'.$ruta.'/imgs/edixit-logo.png">
    ';
    ?>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">

    <?php echo '<title>Dixit Electrónico - '.$pag.'</title>'; ?>
</head>

<!-- Muchas veces es necesario en javascript que se hagan cosas tras la carga de la página, de modo que si existe intentará llamar a la función init() -->

<body onload=" if (typeof init === 'function') {init();}">
    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js"
        integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"
        integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous">
    </script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"
        integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous">
    </script>

    <!-- MENÚ -->
    <div class="container">
        <nav class="navbar navbar-expand-md navbar-light bg-light">
            <header>
                <?php
                echo '
                <a class="navbar-brand" href="'.$ruta.'">
                    <img src="'.$ruta.'/imgs/edixit-banner.png" alt="Logo eDixit">
                </a>
                ';
            ?>
            </header>
            <button class="navbar-toggler ml-auto d-md-none" type="button" data-toggle="collapse"
                data-target="#collapsibleNavId" aria-controls="collapsibleNavId" aria-expanded="false"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="collapsibleNavId">
                <ul class="navbar-nav ml-auto mt-2 mt-md-0">
                    <?php
                    $esInicio = $actual=='/eDixit/index.php';
                    echo '
                    <li class="nav-item '.($esInicio?'disabled':'active').'">
                        <a class="nav-link '.($esInicio?'disabled':'').'" href="'.$ruta.'">Inicio</a>
                    </li>
                    ';
                ?>
                    <li class="nav-item dropdown active">
                        <?php
                    echo '<a class="nav-link dropdown-toggle" href="'.$ruta.'/#" id="dropdown1" data-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false">Cómo jugar</a>';
                    ?>
                        <div class="dropdown-menu" aria-labelledby="dropdown1">
                            <?php
                        echo '
                        <a class="dropdown-item" href="'.$ruta.'/archivos/dixit-reglas.pdf" target="_blank">Manual del
                            Juego <i class="fa fa-external-link" aria-hidden="true"></i></a>
                        <a class="dropdown-item" href="https://www.eljuegodemesa.com/como-se-juega-a-dixit/"
                            target="_blank">Tutorial <i class="fa fa-external-link" aria-hidden="true"></i></a>
                        ';
                        ?>
                        </div>
                    </li>
                    <li class="nav-item dropdown active">
                        <?php
                        echo '
                        <a class="nav-link dropdown-toggle" href="'.$ruta.'/#" id="dropdown2" data-toggle="dropdown"
                            aria-haspopup="true" aria-expanded="false">Contacto</a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item '.($actual=='/eDixit/nosotros/index.php'?'disabled':'').'" href="'.$ruta.'/nosotros/">Nosotros</a>
                        </div>
                        ';
                    ?>
                    </li>
                    <?php 
                        $esLogin = $actual=='/eDixit/login/index.php';
                        echo '<li class="nav-item dropdown '.($esLogin?'disabled':'active').'">';
                        if (isset($_SESSION['iniciada'])){
                            echo '<a class="nav-link dropdown-toggle" href="./#" id="dropdown3" data-toggle="dropdown"
                            aria-haspopup="true" aria-expanded="false"><img class="perfil-mini mr-1" src="'.$foto.'?='.filemtime($foto).'" alt="Tu foto de perfil"/>'.$_SESSION['usuario_nombre'].'</a>';
                            echo '
                            <div class="dropdown-menu">
                                <a class="dropdown-item '.($actual=='/eDixit/usuario/index.php'?'disabled':'').'" href="'.$ruta.'/usuario">Mi perfil</a>
                                <a class="dropdown-item '.($actual=='/eDixit/usuario/editar/index.php'?'disabled':'').'" href="'.$ruta.'/usuario/editar">Editar Perfil</a>
                                <a class="dropdown-item" href="'.$ruta.'/login/cerrar.php">Cerrar Sesión</a>
                            </div>
                            ';
                        } else {
                            echo '<a class="nav-link '.($esLogin?'disabled':'').'" href="'.$ruta.'/login/">Iniciar Sesión</a>';
                        }
                        echo '</li>';
                    ?>
                </ul>
                <!-- Jejeje esperemos usarlo
                <form class="form-inline my-2 my-lg-0">
                    <input class="form-control mr-sm-2" type="text" placeholder="Buscar">
                    <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Buscar</button>
                </form>
                -->
            </div>
        </nav>
    </div>

    <?php
    echo '    
    <div class="container-fluid bg-primary text-primary-text pt-3 pb-2 mt-1 mb-5">
        <p class="importante">'.$pag.'</p>
    </div>
    ';
    if (isset($_SESSION['mensaje_cabecera'])){
        echo '
        <div class="container-fluid '.($_SESSION['mensaje_cabecera_bien']?'bg-success':'bg-danger').' mb-4 text-primary-text d-flex justify-content-center align-items-center">
            '.$_SESSION['mensaje_cabecera'].'
        </div>
        ';
        unset($_SESSION['mensaje_cabecera']);
    }
    ?>