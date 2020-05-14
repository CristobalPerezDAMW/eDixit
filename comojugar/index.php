<?php
$ruta = '..';
$pag = 'Cómo Jugar';
require("../cabecera.php");
?>
<div class="container comojugar">
    <div class="row">
        <h1>El turno</h1>
        <picture>
            <source srcset="imgs/fisico-900.webp" media="(min-width: 600px)" type="image/webp">
            <source srcset="imgs/fisico-600.webp" type="image/webp">
            <source srcset="imgs/fisico-900.jpg" media="(min-width: 600px)">
            <source srcset="imgs/fisico-600.jpg">
            <img src="imgs/fisico-900.jpg" alt="Interfaz del juego online" >
        </picture>
    </div>
    <div class="row">
        <h3>El Cuentacuentos</h3>
        <p>En cada ronda se designa un jugador como Cuentacuentos para la ronda en cuestión. Al principio del turno, éste debe elegir una carta de su mano, sin mostrarla al resto de jugadores, y colocarla en la mesa boca abajo aportando una pista que ayude al resto de jugadores a identificar su carta. Durante el primer turno, el Cuentacuentos será el primero en colocar una carta y dar su pista, y en los siguientes será el jugador colocado a la izquierda del Cuentacuentos anterior.</p>
    </div>
    <div class="row">
        <h3>Aportación de los jugadores</h3>
        <p>Una vez el Cuentacuentos ha colocado su carta, los demás jugadores deben aportar una carta de su propia mano (de nuevo, sin enseñar al resto de jugadores) pero sin aportar ninguna pista, sólo el Cuentacuentos lo hace. Una vez todos han colocado sus cartas, el Cuentacuentos coge la suya y las del resto de jugadores y las baraja.</p>
        <picture>
            <source srcset="imgs/eligiendocarta-1440.webp" media="(min-width: 900px)" type="image/webp">
            <source srcset="imgs/eligiendocarta-900.webp" media="(min-width: 600px)" type="image/webp">
            <source srcset="imgs/eligiendocarta-600.webp" type="image/webp">
            <source srcset="imgs/eligiendocarta-1440.jpg" media="(min-width: 900px)">
            <source srcset="imgs/eligiendocarta-900.jpg" media="(min-width: 600px)">
            <source srcset="imgs/eligiendocarta-600.jpg">
            <img src="imgs/eligiendocarta-1440.jpg" alt="Interfaz del juego online" >
        </picture>
    </div>
    <div class="row">
        <h3>Votación</h3>
        <div class="col-md-6">
            <p>Una vez el Cuentacuentos ha barajado las cartas, las coloca en orden y las enumera desde el 1, y los demás jugadores votan la carta que creen que ha sido la colocada por el Cuentacuentos. Los jugadores no pueden votar a su propia carta.</p>
        </div>
        <div class="col-md-6">
            <p>El voto es secreto, de modo que en el juego de mesa físico los jugadores tienen fichas numeradas para colocar boca-abajo delante de ellos el número de la carta que quieren votar.</p>
        </div>
    </div>
    <div class="row">
        <h3>Puntuación</h3>
        <div class="col-md-6">
            <p>Cuando todos los jugadores, excepto el Cuentacuentos, han votado, se revela el voto de cada jugador, luego cada jugador revela al resto cuál es su carta y se aportan puntos de la siguiente forma:</p>
        </div>
        <div class="col-md-6">
            <ol>
                <li>Si todos los votos o ninguno han acertado la carta del cuentacuentos, todos los jugadores salvo el Cuentacuentos suman 2 puntos cada uno.</li>
                <li>Si al menos 1 voto ha acertado la carta del Cuentacuentos y al menos 1 voto ha fallado, el Cuentacuentos y todos los jugadores que han acertado la carta del Cuentacuentos suman 3 puntos cada uno.</li>
                <li>Para cada jugador, excepto el Cuentacuentos, por cada voto que haya recibido su carta sumará 1 punto adicional.</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <h3>Fin de ronda</h3>
        <p>Una vez designados los puntos, todos los jugadores roban una carta y se procede al siguiente turno. El Cuentacuentos del siguiente turno será el del jugador colocado a la izquierda del Cuentacuentos del turno actual.</p>
    </div>
</div>
<div class="container comojugar">
    <div class="row">
        <h1>El objetivo</h1>
    </div>
    <div class="row">
        <h3>Fin del Juego</h3>
        <p>El primer jugador en llegar a los 30 puntos gana. En caso de que más de un jugador alcance los 30 puntos en el mismo turno, quedan empatados.</p>
        <p>Sin embargo, en lugar de jugar hasta 30 puntos se puede jugar hasta que se agoten las cartas, en cuyo caso el ganador es el jugador con más puntos cuando esto ocurre, de nuevo con posibilidad de empate.</p>
        
        <picture>
            <source srcset="imgs/cartas-900.webp" media="(min-width: 600px)" type="image/webp">
            <source srcset="imgs/cartas-600.webp" type="image/webp">
            <source srcset="imgs/cartas-900.jpg" media="(min-width: 600px)">
            <source srcset="imgs/cartas-600.jpg">
            <img src="imgs/cartas-900.jpg" alt="Interfaz del juego online" >
        </picture>
    </div>
    <div class="row">
        <h3>Una pista ni fácil ni difícil</h3>
        <p>Lo ideal para el Cuentacuentos es que al menos 1 persona acierte su carta y al menos 1 falle, de modo que una pista demasiado obvia o demasiado enrevesada será perjudicial. La clave está en encontrar el equilibrio entre ambos, buscar referencias en el cine o los libros que sepas que alguien del grupo no ha visto/leído y procurar no dar pistas que puedan llevar a elegir otras cartas.</p>
    </div>
    <div class="row">
        <h3>Identificar y engañar</h3>
        <p>En cada ronda lo ideal, si no eres el Cuentacuentos, es engañar a tus oponentes para que piensen que la carta que fue la colocada por el Cuentacuentos es la tuya, elegiendo una de tu mano que sea apropiada con la pista dada, y al mismo tiempo identificar correctamente la verdadera carta del Cuentacuentos. De esta forma puntuarás muchos más puntos y negarás puntos a tus adversarios.</p>
    </div>
</div>
</body>

</html>