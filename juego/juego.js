// Crear evento compatible con múltiples navegadores
var crearEvento = (function() {
    function w3c_crearEvento(elemento, evento, mifuncion) {
        elemento.addEventListener(evento, mifuncion, false);
    }

    function ie_crearEvento(elemento, evento, mifuncion) {
        var fx = function() {
            mifuncion.call(elemento);
        };
        elemento.attachEvent("on" + evento, fx);
    }
    if (typeof window.addEventListener !== "undefined") {
        return w3c_crearEvento;
    } else if (typeof window.attachEvent !== "undefined") {
        return ie_crearEvento;
    }
})();

//AJAX
function objetoXHR() {
    if (window.XMLHttpRequest) {
        return new XMLHttpRequest();
    } else if (window.ActiveXObject) {
        var versionesIE = new Array(
            "Msxml2.XMLHTTP.5.0",
            "Msxml2.XMLHTTP.4.0",
            "Msxml2.XMLHTTP.3.0",
            "Msxml2.XMLHTTP",
            "Microsoft.XMLHTTP"
        );
        for (var i = 0; i < versionesIE.length; i++) {
            try {
                return new ActiveXObject(versionesIE[i]);
            } catch (errorControlado) {}
        }
    }
    throw new Error("No se pudo crear el objeto XMLHttpRequest");
}

function getAsync(url) {
    if (ajaxXHR) {
        document.getElementById("indicadorAJAX").innerHTML = "<img src='imgs/ajax-loading.gif'/>";
        ajaxXHR.open('GET', url, true);
        ajaxXHR.onreadystatechange = estadoPeticion;
        ajaxXHR.send(null);
    }
}

function estadoPeticion() {
    if (this.readyState == 4 && this.status == 200) {
        estadoJuego = this.responseText;
        document.getElementById("indicadorAJAX").innerHTML = "";
        ponerEstado();
    }
}

async function pedirEstadoJuego() {
    getAsync(urlGetEstado + "?getEstadoPartida=true");
};

var ajaxXHR, body, divTusCartas, divJugadores, imgPerfil, divMensajes, mensaje1, mensaje2, nodoDivCartas;

crearEvento(window, "load", init);

function init() {
    ajaxXHR = new objetoXHR();

    body = document.body;
    divTusCartas = document.getElementById("tusCartas");
    divJugadores = document.getElementById("jugadores");
    divMensajes = document.getElementById("mensajes");
    mensaje1 = document.getElementById("mensaje1");
    mensaje2 = document.getElementById("mensaje2");
    nodoDivCartas = document.createElement("div");

    jugadores.forEach(jugador => {
        let src = jugador.img;
        jugador.img = new Image();
        jugador.img.src = src;
        jugador.img.title = jugador.nombre + " (" + jugador.correo + ")";

        if (jugador.correo == cuentacuentos) {
            jugador.img.classList.add("cuentacuentos");
            jugador.img.title += " ¡Cuentacuentos!";
        }
        if (jugador.correo == jugadores[jugadorIndice].correo) {
            jugador.img.classList.add("tuPerfil");
        }

        divJugadores.appendChild(jugador.img);
    });


    function foreachMano(item, index) {
        img = new Image();
        img.src = "cartas/carta" + item + ".jpg"
        nodoDivCartas.appendChild(img);
    }
    tuMano.forEach(foreachMano);
    divTusCartas.appendChild(nodoDivCartas);

    pedirEstadoJuego();
}

/* Estados del juego:
    "Inicio": No hay cuentacuentos, el primer jugador en elegir carta y pista se convierte el cuentacuentos y se pasa al estado "PensandoCartas"
    "PensandoCC": El cuentacuentos está pensando, es el primer estado del turno (excepto el primer turno)
    "PensandoCartas:X": El cuentacuentos ha elegido carta y ahora la están eligiendo los demás jugadores. Quedan X jugadores por elegir carta
    "Votacion:X": Los jugadores están votando qué carta creen que es del cuentacuentos. Quedan X jugadores por votar
    "Puntos": Se están repartiendo los puntos. Se toma un tiempo en este paso para que todos los jugadores vean cómo van
*/
function ponerEstado() {
    console.log("PonerEstado con estado " + estadoJuego);
    //Limpieza del estado anterior
    divMensajes.classList.remove("quitar");

    var eligeCarta = false;
    if (estadoJuego == "Inicio") {
        mensaje1.innerHTML = "Fase Inicial";
        mensaje2.innerHTML = "El primero en poner carta es el primer cuentacuentos";
        eligeCarta = true;
    } else if (estadoJuego == "PensandoCC") {
        if (cuentacuentos == jugadores[jugadorIndice].correo) {
            mensaje1.innerHTML = "Eres el Cuentacuentos";
            mensaje2.innerHTML = "Te toca elegir carta. Recuerda no pensar en una pista demasiado fácil.";
            eligeCarta = true;
        } else {
            mensaje1.innerHTML = "Esperando al Cuentacuentos";
            mensaje2.innerHTML = "El cuentacuentos está pensando qué carta elegir";
        }
    } else {
        divMensajes.classList.add("quitar");
    }

    nodoDivCartas.childNodes.forEach(
        function(currentValue, currentIndex, listObj) {
            currentValue.classList.remove("elegible");
            if (eligeCarta) {
                console.log("Dando evento a " + currentValue);
                crearEvento(currentValue, "click", function() { elegirCarta(currentValue, currentIndex) });
                currentValue.classList.add("elegible");
            } else {
                console.log("Quitando eventos a " + currentValue);
                let imgClon = currentValue.cloneNode(true);

                currentValue.parentNode.replaceChild(imgClon, currentValue);
            }
        }
    );

}

function elegirCarta(carta, indice) {
    alert("hola, has seleccionado la carta num " + indice + ", que es la " + carta);
}