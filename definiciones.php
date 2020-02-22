<?php
$prohibidas = array(
    '<'=>'{menorque}',
    '>'=>'{mayorque}',
    ':'=>'{puntos}',
    '"'=>'{comillas}',
    '/'=>'{barrader}',
    '\\'=>'{barraizq}',
    '|'=>'{barra}',
    '?'=>'{interr}',
    '*'=>'{asterix}'
);

function parsearNombreArchivo($nombre){
    global $prohibidas;
    foreach ($prohibidas as $prohibida => $reemplazo) {
        $nombre = str_replace($prohibida, $reemplazo, $nombre);
    }
    return $nombre;
}
?>