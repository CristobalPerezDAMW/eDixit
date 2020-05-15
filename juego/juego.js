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

function getAsync(url, saltarIndicador = false) {
    if (ajaxXHR) {
        // document.getElementById("indicadorAJAX").innerHTML = "<img src='imgs/ajax-loading.gif'/>";
        // console.log(url);
        // console.log(encodeURI(url));
        ajaxXHR.open('GET', encodeURI(url), true);
        ajaxXHR.onreadystatechange = enPeticionLista;
        ajaxXHR.send(null);
    }
}

function enPeticionLista(saltarIndicador = false) {
    if (this.readyState == 4 && this.status == 200) {
        // document.getElementByI("indicadorAJAX").innerHTML = "";
        if (this.responseText.startsWith('Error') !== false) {
            console.log(this.responseText);
        } else {
            // console.log(this.responseText);
            cartaElegida = 0;
            listaFaltan = new Array();
            let datos = this.responseText.split(";");
            if (datos.length > 1) {
                if (datos[1] != "null")
                    tuMano = datos[1].split(":");

                if (datos.length > 2) {
                    if (datos[2] != "null")

                        cuentacuentos = datos[2];
                    if (datos.length > 3) {
                        if (datos[3] != "null")
                            pista = datos[3];

                        if (datos.length > 4) {
                            if (datos[4] != "null")
                                cartaElegida = datos[4];

                            if (datos.length > 5) {
                                if (datos[5] != "")
                                    listaFaltan = datos[5].split(',');
                            }
                        }
                    }
                }
            }
            if (datos[0] != estadoJuego) {
                estadoJuego = datos[0];
            }
            ponerEstado(eligeCarta);
        }
    }
}

async function pedirEstadoJuego() {
    getAsync(urlGet + "?accion=get_estado_partida", true);
    setTimeout(() => {
        pedirEstadoJuego();
    }, 2000);
};

var ajaxXHR, body, divTusCartas, divJugadores, imgPerfil, divMensajes, mensaje1, mensaje2, mensajeImagen, mensajePista, divCartas, eligeCarta, cartaElegida = 0,
    pista = "",
    listaFaltan = new Array();

crearEvento(window, "load", init);

function init() {
    ajaxXHR = new objetoXHR();

    body = document.body;
    divTusCartas = document.getElementById("tusCartas");
    divJugadores = document.getElementById("jugadores");
    divMensajes = document.getElementById("mensajes");
    mensaje1 = document.getElementById("mensaje1");
    mensaje2 = document.getElementById("mensaje2");
    mensajeImagen = document.getElementById("mensajeImagen");
    mensajePista = document.getElementById("mensajePista");
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

function ponerEstado(eligeCartaAnterior) {
    //Limpieza del estado anterior
    divMensajes.classList.remove("quitar");
    mensajeImagen.classList.add("quitar");
    mensajePista.classList.add("quitar");

    eligeCarta = false;
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
        } else if (cartaElegida == 0) {
            mensajeImagen.src = "cartas/cartaReverso.jpg";
            mensajePista.innerHTML = "Pista:<br>" + pista;
            divMensajes.classList.add("quitar");
            mensajeImagen.classList.remove("quitar");
            mensajePista.classList.remove("quitar");
            eligeCarta = true;
        } else {
            mensajeImagen.classList.remove("quitar");
            mensajePista.classList.remove("quitar");
            mensaje1.innerHTML = "Has elegido carta";
            mensaje2.innerHTML = "Debes esperar mientras los demás eligen su carta para esta ronda.";
            eligeCarta = false;
        }
    } else if (estadoJuego == "Votacion") {
        if (cuentacuentos == jugadores[jugadorIndice].correo) {
            mensaje1.innerHTML = "Esperando";
            mensaje2.innerHTML = "Debes esperar mientras los demás votan una carta.";
        } else if (cartaElegida == 0) {
            mensaje1.innerHTML = "Estado Votación";
            mensaje2.innerHTML = "Deberías ver el panel de votación pero requiere más trabajo.";
            eligeCarta = true;
        } else {
            mensaje1.innerHTML = "Has elegido carta";
            mensaje2.innerHTML = "Debes esperar mientras los demás votan una carta.";
            eligeCarta = false;
        }
    } else {
        console.log("Error, no existe el estado " + estadoJuego);
        // divMensajes.classList.add("quitar");
        mensaje1.innerHTML = "Error: El estado no existe";
        // mensaje2.innerHTML = "Lo sentimos mucho, algo no está del todo pulido por aquí detras.";
        mensaje2.innerHTML = "¿Qué esperabas? El juego no está terminado, pero gracias por intentarlo.";
        return;
    }
    if (cartaElegida != 0) {
        // eligeCarta = false;
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
                // console.log("Carta " + numCarta + " sigue en la mano");
                if (eligeCarta != eligeCartaAnterior) {
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
                }
            } else {
                console.log("Carta " + numCarta + " YA NO sigue en la mano");
                currentValue.parentNode.removeChild(currentValue);
            }
        }
    );

    let i = 0;
    divJugadores.childNodes.forEach(
        function(currentValue, currentIndex, listObj) {
            if (currentValue.nodeType == Node.ELEMENT_NODE) {
                if (i == jugadorIndice) {
                    currentValue.classList.add("tuPerfil");
                }
                // console.log("Comprobando nodo " + currentValue + ", " + currentIndex);
                // console.log(currentValue);
                if (jugadores[i].correo == cuentacuentos) {
                    currentValue.classList.add("cuentacuentos");
                    // currentValue.classList.add("eligiendo");
                    currentValue.title = jugadores[i].nombre + " (" + jugadores[i].correo + ") ¡Cuentacuentos!";
                } else {
                    currentValue.classList.remove("cuentacuentos");
                    // currentValue.classList.add("eligiendo");
                    currentValue.title = jugadores[i].nombre + " (" + jugadores[i].correo + ")";
                }
                for (let j = 0; j < listaFaltan.length; j++) {
                    if (jugadores[i].correo == listaFaltan[j]) {
                        currentValue.classList.add("eligiendo");
                        currentValue.title += " (Está eligiendo carta)";
                    } else {
                        currentValue.classList.remove("eligiendo");
                    }
                }
                i++;
            }
        }
    );
}

function elegirCarta(carta, indice) {
    // alert("hola, has seleccionado la carta num " + indice + ", que es la " + carta);
    let pista = "";
    if (estadoJuego == "Inicio") {
        while (pista == "")
            pista = prompt("Ofrece una pista para los demás jugadores");
        if (pista == "null") {
            return;
        }
        getAsync(urlGet + "?accion=elegir_carta_inicio&carta_elegida=" + carta + "" + "&pista=" + pista);
    } else if (estadoJuego == "PensandoCartas") {
        getAsync(urlGet + "?accion=elegir_carta_pensando_cartas&carta_elegida=" + carta);
    } else {
        console.log("Error, no se admite durante el estado " + estadoJuego);
        alert("No puedes elegir cartas ahora");
        return;
    }
    cartaElegida = carta;
    ponerEstado(true);
}