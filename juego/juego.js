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
        ajaxXHR.onreadystatechange = enPeticionLista;
        ajaxXHR.send(null);
    }
}

function enPeticionLista() {
    if (this.readyState == 4 && this.status == 200) {
        document.getElementById("indicadorAJAX").innerHTML = "";
        if (estadoJuego != this.responseText) {
            let datos = this.responseText.split(";");
            estadoJuego = datos[0];
            tuMano = datos[1].split(":");
            cuentacuentos = datos[2];
            ponerEstado();
        }
    }
}

async function pedirEstadoJuego() {
    getAsync(urlGet + "?accion=get_estado_partida");
    setTimeout(() => {
        pedirEstadoJuego();
    }, 2000);
};

var ajaxXHR, body, divTusCartas, divJugadores, imgPerfil, divMensajes, mensaje1, mensaje2, divCartas;

crearEvento(window, "load", init);

function init() {
    ajaxXHR = new objetoXHR();

    body = document.body;
    divTusCartas = document.getElementById("tusCartas");
    divJugadores = document.getElementById("jugadores");
    divMensajes = document.getElementById("mensajes");
    mensaje1 = document.getElementById("mensaje1");
    mensaje2 = document.getElementById("mensaje2");
    divCartas = document.createElement("div");

    jugadores.forEach(jugador => {
        let src = jugador.img;
        jugador.img = new Image();
        jugador.img.src = src;
        jugador.img.title = jugador.nombre + " (" + jugador.correo + ")";

        divJugadores.appendChild(jugador.img);
    });

    function foreachMano(item, index) {
        img = new Image();
        img.src = "cartas/carta" + item + ".jpg";
        img.title = "Cata " + item;
        img.dataset.numeroCarta = item;
        divCartas.appendChild(img);
    }
    tuMano.forEach(foreachMano);
    divTusCartas.appendChild(divCartas);

    pedirEstadoJuego();
}

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
    } else if (estadoJuego == "PensandoCartas") {
        if (cuentacuentos == jugadores[jugadorIndice].correo) {
            mensaje1.innerHTML = "Has elegido carta";
            mensaje2.innerHTML = "Debes esperar mientras los demás eligen su carta para esta ronda.";
        } else {
            eligeCarta = true;
            mensaje1.innerHTML = "El cuentacuentos ha elegido carta";
            mensaje2.innerHTML = "Elige algo que se asemeje a la psita (sí, no conoces la pista, lo sé, espera que estoy todavía haciendo el juego no tengas prisa)";
        }
    } else {
        console.log("Error, no existe el estado " + estadoJuego);
        // divMensajes.classList.add("quitar");
        mensaje1.innerHTML = "Error: El estado no existe";
        // mensaje2.innerHTML = "Lo sentimos mucho, algo no está del todo pulido por aquí detras.";
        mensaje2.innerHTML = "¿Qué esperabas? El juego no está terminado, pero gracias por intentarlo.";
        return;
    }

    divCartas.childNodes.forEach(
        function(currentValue, currentIndex, listObj) {
            let numCarta = currentValue.dataset.numeroCarta;
            let sinCarta = true;
            for (let i = 0; i < tuMano.length; i++) {
                if (tuMano[i] == numCarta) {
                    sinCarta = false;
                    break;
                }
            }
            if (!sinCarta) {
                console.log("Carta " + numCarta + " sigue en la mano");
                currentValue.classList.remove("elegible");
                if (eligeCarta) {
                    // console.log("Dando evento a " + currentValue);
                    crearEvento(currentValue, "click", function() { elegirCarta(numCarta, currentIndex) });
                    currentValue.classList.add("elegible");
                } else {
                    // console.log("Quitando eventos a " + currentValue);
                    let imgClon = currentValue.cloneNode(true);
                    imgClon.dataset.numeroCarta = numCarta;
                    currentValue.parentNode.replaceChild(imgClon, currentValue);
                }
            } else {
                console.log("Carta " + numCarta + " YA NO sigue en la mano");
                currentValue.parentNode.removeChild(currentValue);
            }
        }
    );

    divJugadores.childNodes.forEach(
        function(currentValue, currentIndex, listObj) {
            if (jugador[currentIndex] == cuentacuentos) {
                currentValue.classList.add("cuentacuentos");
                currentValue.title = jugador.nombre + " (" + jugador.correo + ") ¡Cuentacuentos!";
            } else {
                currentValue.classList.remove("cuentacuentos");
                currentValue.title = jugador.nombre + " (" + jugador.correo + ")";
            }
        }
    );
}

function elegirCarta(carta, indice) {
    // alert("hola, has seleccionado la carta num " + indice + ", que es la " + carta);

    if (estadoJuego == "Inicio") {
        getAsync(urlGet + "?accion=elegir_carta_inicio&carta_elegida=" + carta + "");
    } else if (estadoJuego == "PensandoCC") {
        if (cuentacuentos == jugadores[jugadorIndice].correo) {
            mensaje1.innerHTML = "Eres el Cuentacuentos";
            mensaje2.innerHTML = "Te toca elegir carta. Recuerda no pensar en una pista demasiado fácil.";
        } else {
            mensaje1.innerHTML = "Esperando al Cuentacuentos";
            mensaje2.innerHTML = "El cuentacuentos está pensando qué carta elegir";
        }
    } else if (estadoJuego.startsWith("Error")) {
        console.log(estadoJuego);
        // divMensajes.classList.add("quitar");
        mensaje1.innerHTML = "¡¡¡!!!";
        mensaje2.innerHTML = estadoJuego;
    } else {
        console.log("Error, no existe el estado " + estadoJuego);
        divMensajes.classList.add("quitar");
    }
}