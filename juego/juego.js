var body, divTusCartas, jugadores;

function init() {
    body = document.body;
    divTusCartas = document.getElementById("tusCartas");
    jugadores = new Array({ correo: "<?php echo $_SESSION['usuario_correo']?>", cartas: new Array(1, 3, 4, 23, 10, 27) }, { correo: "otroseñor", cartas: new Array(6, 7, 9, 10, 13) }, { correo: "lul", cartas: new Array(2, 8, 5, 17, 12, 22) });
    console.log(jugadores);

    jugadores.forEach(jugador => {
        if (jugador.correo == "<?php echo $_SESSION['usuario_correo']?>") {
            var nodoDiv = document.createElement("div");
            jugador.cartas.forEach(carta => {
                var img = new Image();
                img.src = "cartas/carta" + carta + ".jpg"
                img.title = "carta" + carta;
                nodoDiv.appendChild(img);
            });
            divTusCartas.appendChild(nodoDiv);
        }
    });
}