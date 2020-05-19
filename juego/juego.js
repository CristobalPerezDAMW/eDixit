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
            cartaElegida = 0;
            listaFaltan = new Array();
            cartasVotacion = new Array();
            cartaVotada = "";
            listaFaltanVotar = new Array();
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

                                if (datos.length > 6) {
                                    if (datos[6] != "null")
                                        cartasVotacion = datos[6].split(',');

                                    if (datos.length > 7) {
                                        if (datos[7] != "null")
                                            cartaVotada = datos[7];

                                        if (datos.length > 8) {
                                            if (datos[8] != "")
                                                faltanVotar = datos[8].split(',');

                                            if (datos.length > 9) {
                                                if (datos[9] != "null")
                                                    puntuacionRonda = datos[9];
                                            }
                                        }
                                    }
                                }
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

var ajaxXHR, body, divTusCartas, divJugadores, imgPerfil, divMensajes, mensaje1, mensaje2, mensajeImagen, mensajePista, divVotacion, divCartas, aceptarPuntuacion, eligeCarta, cartaElegida = 0,
    pista = "",
    cartaVotada = 0,
    cartasVotacion = new Array(),
    cartaVotada = 'null',
    puntuacionRonda = 0;
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
    divVotacion = document.getElementById("cartasVotacion");
    divCartas = document.createElement("div");
    aceptarPuntuacion = document.getElementById("aceptarPuntuacion");

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

    crearEvento(aceptarPuntuacion, "click", function() {
        if (estadoJuego == 'Puntuacion') {
            getAsync(urlGet + "?accion=aceptar_puntuacion");
        }
    });

    pedirEstadoJuego();
}

function ponerEstado(eligeCartaAnterior) {
    //Limpieza del estado anterior
    divCartas.classList.remove("quitar");
    divMensajes.classList.remove("quitar");
    mensajeImagen.classList.add("quitar");
    mensajePista.classList.add("quitar");
    divVotacion.classList.add("quitar");
    aceptarPuntuacion.classList.add("quitar");

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
        }
    } else if (estadoJuego == "Votacion") {
        divCartas.classList.add("quitar");
        divVotacion.classList.remove("quitar");
        if (cuentacuentos == jugadores[jugadorIndice].correo) {
            mensaje1.innerHTML = "Eres el Cuentacuentos";
            mensaje2.innerHTML = "Debes esperar a que voten los demás jugadores.";
        } else if (cartaVotada == 0) {
            mensaje1.innerHTML = "¿Qué carta es del cuentacuentos?";
            mensaje2.innerHTML = "La pista es: " + pista;
        } else {
            mensaje1.innerHTML = "Has elegido carta";
            mensaje2.innerHTML = "Debes esperar mientras los demás votan una carta.";
        }

        let yaEstanColocadas = false;
        //Break casero, para que cuando no haya muchas cartas tampoco se tire mucho rato en este bucle
        try {
            divVotacion.childNodes.forEach(nodo => {
                if (nodo.nodeType == 1) {
                    yaEstanColocadas = true;
                    throw "break";
                }
            });
        } catch (e) {
            if (e != "break") {
                throw e;
            }
        }
        if (!yaEstanColocadas)
            for (let i = 0; i < cartasVotacion.length; i++) {
                let div = document.createElement("div");
                let img = document.createElement("img");
                let p = document.createElement("p");

                if (cartasVotacion[i] == cartaElegida) {
                    div.title = "Esta carta la has colocado tú";
                    img.classList.add("elegida");
                    p.classList.add("elegida");
                    if (jugadores[jugadorIndice].correo != cuentacuentos)
                        crearEvento(div, "click", function(event) {
                            alert("No puedes votar la carta que tú mismo has puesto");
                        });
                } else if (cartasVotacion[i] == cartaVotada) {
                    div.title = "Esta carta la has votado tú";
                    img.classList.add("votada");
                    p.classList.add("votada");
                } else if (cartaVotada != "" || jugadores[jugadorIndice].correo == cuentacuentos) {
                    div.title = "No puedes elegir carta";
                    img.classList.add("no_elegible");
                    p.classList.add("no_elegible");
                    if (jugadores[jugadorIndice].correo != cuentacuentos)
                        crearEvento(div, "click", function(event) {
                            alert("Ya has votado alguna carta");
                        });
                } else {
                    div.title = "Podría ser ésta la carta del Cuentacuentos";
                    crearEvento(div, "click", function(event) {
                        // alert(urlGet + "?accion=votar_carta&carta_votada=" + event.target.dataset.numeroCarta);
                        getAsync(urlGet + "?accion=votar_carta&carta_votada=" + event.target.dataset.numeroCarta);
                    });
                }

                // div.style = "max-width: " + (100 / cartasVotacion.length) + "%";
                img.src = "cartas/carta" + cartasVotacion[i] + ".jpg";
                img.alt = "Carta número " + i + 1;
                img.dataset.numeroCarta = cartasVotacion[i];
                p.innerHTML = i + 1;
                p.dataset.numeroCarta = cartasVotacion[i];

                divVotacion.appendChild(div);
                div.appendChild(img);
                div.appendChild(p);
            }
    } else if (estadoJuego == "Puntuacion") {
        mensaje1.innerHTML = "Estado Puntuación";
        if (puntuacionRonda != 0) {
            aceptarPuntuacion.classList.remove("quitar");
            mensaje2.innerHTML = "Has conseguido " + puntuacionRonda + " puntos esta ronda.";
        } else {
            mensaje2.innerHTML = "Esperando a que todos acepten para empezar la siguiente ronda.";
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
                if (eligeCarta != eligeCartaAnterior) {
                    currentValue.classList.remove("elegible");
                    if (eligeCarta) {
                        crearEvento(currentValue, "click", function() { elegirCarta(numCarta, currentIndex) });
                        currentValue.classList.add("elegible");
                    } else {
                        let imgClon = currentValue.cloneNode(true);
                        imgClon.dataset.numeroCarta = numCarta;
                        currentValue.parentNode.replaceChild(imgClon, currentValue);
                    }
                }
            } else {
                currentValue.parentNode.removeChild(currentValue);
            }
        }
    );

    let i = 0;
    divJugadores.childNodes.forEach(
        function(currentValue, currentIndex, listObj) {
            if (currentValue.nodeType == Node.ELEMENT_NODE) {
                currentValue.classList.remove("eligiendo");
                if (i == jugadorIndice) {
                    currentValue.classList.add("tuPerfil");
                }
                if (jugadores[i].correo == cuentacuentos) {
                    currentValue.classList.add("cuentacuentos");
                    currentValue.title = jugadores[i].nombre + " (" + jugadores[i].correo + ") ¡Cuentacuentos!";
                } else {
                    currentValue.classList.remove("cuentacuentos");
                    currentValue.title = jugadores[i].nombre + " (" + jugadores[i].correo + ")";
                }
                for (let j = 0; j < listaFaltan.length; j++) {
                    if (jugadores[i].correo == listaFaltan[j]) {
                        currentValue.classList.add("eligiendo");
                        currentValue.title += " (Está eligiendo carta)";
                    }
                }
                i++;
            }
        }
    );
}

function elegirCarta(carta, indice) {
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
    } else if (estadoJuego == "PensandoCC") {
        if (cuentacuentos == jugadores[jugadorIndice].correo) {
            getAsync(urlGet + "?accion=elegir_carta_pensando_cc&carta_elegida=" + carta);
        } else {
            console.log("Error, no se admite durante el estado " + estadoJuego);
        }
    } else {
        console.log("Error, no se admite durante el estado " + estadoJuego);
        alert("No puedes elegir cartas ahora");
        return;
    }
    cartaElegida = carta;
    ponerEstado(true);
}