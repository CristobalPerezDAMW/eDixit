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
        // document.getElementById("indicadorAJAX").innerHTML = "<img src='imgs/ajax-loading.gif'/>";
        ajaxXHR.open('GET', encodeURI(url), true);
        ajaxXHR.onreadystatechange = enPeticionLista;
        try {
            ajaxXHR.send(null);
        } catch (e) {
            console.log("Error al cargar: " + e);
        }
    }
}

function enPeticionLista() {
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
            puntuacionRonda = -1;
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

                                                if (datos.length > 10) {
                                                    if (datos[10] != "null") {
                                                        let datosJugs = datos[10].split(',');
                                                        posicionJugadores = new Array(datosJugs.length);
                                                        for (let i = 0; i < datosJugs.length; i++) {
                                                            posicionJugadores[i] = datosJugs[i].split(':');
                                                        }
                                                    }
                                                }
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
    } else if (this.status != 200 && this.status != 0) {
        alert("Ha habido un error con la comunicación con el servidor.\nPor favor, recarga la página si sigue experimentando problemas.\n" + this.status);
    }
}

async function pedirEstadoJuego() {
    //Este método repite cada X tiempo la acción de actualizar los datos de la partida, una acción costosa que no debería realizarse con demasiada frecuencia para que cuanto más se haga mejor
    getAsync(urlGet + "?accion=get_estado_partida", true);
    if (estadoJuego != 'Final') {
        setTimeout(() => {
            pedirEstadoJuego();
        }, 1000);
    }
};

var ajaxXHR, body, divTusCartas, divJugadores, imgPerfil, divMensajes, mensaje1, mensaje2, mensajeImagen, mensajePista, divVotacion, divCartas, aceptarPuntuacion, eligeCarta, cartaElegida = 0,
    pista = "",
    cartaVotada = 0,
    cartasVotacion = new Array(),
    cartaVotada = 'null',
    puntuacionRonda = -1,
    posicionJugadores = new Array(),
    listaFaltan = new Array();

crearEvento(window, "load", init);

function agregarCarta(carta) {
    img = new Image();
    img.src = "cartas/carta" + carta + ".jpg";
    img.title = "Carta " + carta;
    img.dataset.numeroCarta = carta;
    img.classList.remove("elegible");
    if (eligeCarta) {
        crearEvento(img, "click", function() { elegirCarta(carta, tuMano.length) });
        img.classList.add("elegible");
    }
    divCartas.appendChild(img);
}

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
        let div = document.createElement("div");

        let src = jugador.img;
        jugador.img = new Image();
        jugador.img.src = src;
        jugador.img.title = jugador.nombre + " (" + jugador.correo + ")";

        let posicion = jugador.posicion;
        jugador.posicion = document.createElement("p");
        jugador.posicion.innerHTML = "Puntos: " + posicion;

        div.appendChild(jugador.img);
        div.appendChild(jugador.posicion);
        divJugadores.appendChild(div);
    });

    function foreachMano(item, index) {
        agregarCarta(item);
    }
    tuMano.forEach(foreachMano);
    divTusCartas.appendChild(divCartas);

    crearEvento(aceptarPuntuacion, "click", function() {
        if (estadoJuego == 'Puntuacion') {
            getAsync(urlGet + "?accion=aceptar_puntuacion");
        }
    });

    pedirEstadoJuego();

    var musica = new Audio('media/background.ogg');
    musica.autoplay = true;
    musica.addEventListener("canplaythrough", event => {
        musica.play();
    });
    musica.addEventListener('ended', function() {
        this.currentTime = 0;
        this.play();
    }, false);
}

function ponerEstado(eligeCartaAnterior) {
    //Limpieza del estado anterior
    divCartas.classList.remove("quitar");
    divMensajes.classList.remove("quitar");
    mensajeImagen.classList.add("quitar");
    mensajePista.classList.add("quitar");
    divVotacion.classList.add("quitar");
    aceptarPuntuacion.classList.add("quitar");
    mensaje1.innerHTML = "";
    mensaje2.innerHTML = "";

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

        //De esta forma no actualizamos las cartas a votar constantemente
        let yaEstanColocadas = false;
        //Break casero, para que cuando no haya muchas cartas tampoco se tire mucho rato en este bucle
        try {
            console.log("Comprobando si divvotacion está ya colocado");
            divVotacion.childNodes.forEach(nodo => {
                if (nodo.nodeType == 1) {
                    console.log("Comprobando " + nodo);
                    if (!cartasVotacion.includes(nodo.dataset.carta)) {
                        console.log(cartasVotacion + " includes " + nodo.dataset.carta + ": FALSE");
                        yaEstanColocadas = false;
                        throw "break";
                    }
                    console.log(cartasVotacion + " includes " + nodo.dataset.carta + ": TRUE");
                    yaEstanColocadas = true;
                }
            });
        } catch (e) {
            if (e != "break") {
                // console.log("BREAK");
                throw e;
            }
        }
        console.log("Conclusión: " + yaEstanColocadas);
        if (!yaEstanColocadas) {
            while (divVotacion.firstChild) {
                console.log("Eliminado divvotacion hijo: " + divVotacion.lastChild);
                divVotacion.removeChild(divVotacion.lastChild);
            }
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
                        while (divVotacion.firstChild) {
                            console.log("Eliminado divvotacion hijo: " + divVotacion.lastChild);
                            divVotacion.removeChild(divVotacion.lastChild);
                        }
                    });
                }

                div.dataset.carta = cartasVotacion[i];
                img.src = "cartas/carta" + cartasVotacion[i] + ".jpg";
                img.alt = "Carta número " + i + 1;
                img.dataset.numeroCarta = cartasVotacion[i];
                p.innerHTML = i + 1;
                p.dataset.numeroCarta = cartasVotacion[i];

                divVotacion.appendChild(div);
                div.appendChild(img);
                div.appendChild(p);
            }
        }
    } else if (estadoJuego == "Puntuacion") {
        mensaje1.innerHTML = "Ronda Finalizada";
        if (puntuacionRonda != -1) {
            aceptarPuntuacion.classList.remove("quitar");
            mensaje2.innerHTML = "Has conseguido " + puntuacionRonda + " puntos esta ronda.";
        } else {
            mensaje2.innerHTML = "Esperando a que todos acepten para empezar la siguiente ronda.";
        }
    } else if (estadoJuego == "Final") {
        divCartas.classList.add("quitar");
        var ganadores = new Array();
        for (let i = 0; i < posicionJugadores.length; i++) {
            if (posicionJugadores[1] >= 30) {
                ganadores.push(posicionJugadores[0]);
            }
        }
        mensaje1.innerHTML = "Fin del juego";
        mensaje2.innerHTML = "Ganadores: " + ganadores.join(", ") + "\n¡Bien jugado!";

        let btnVolver = document.createElement("button");
        btnVolver.innerHTML = "Salir del Juego";
        crearEvento(btnVolver, "click", function() {
            window.location.href = "..";
        });
        divMensajes.appendChild(btnVolver);
    } else {
        console.log("Error, no existe el estado " + estadoJuego);
        mensaje1.innerHTML = "Error: El estado no existe";
        mensaje2.innerHTML = "Quizás ha habido una actualización o un error en el servidor";
        return;
    }

    //Básicamente las siguientes dos cosas hacen que no parpadeen las cartas cada vez que se actualizan los datos pero no la mano

    //Comprueba si hay nuevas cartas que agregar a la mano visual
    let arrayManoVisual = Array.prototype.slice.call(divCartas.children);
    buclemano: for (let i = 0; i < tuMano.length; i++) {
        for (let j = 0; j < arrayManoVisual.length; j++) {
            let numJ = arrayManoVisual[j].dataset.numeroCarta;
            if (numJ == tuMano[i]) {
                //Lo siento Gerardo, pero me ahorro muchos ciclos de la CPU con esto de aquí
                continue buclemano;
            }
        }
        //Si hemos llegado aquí, es que no se ha encontrado tuMano[i] dentro de arrayManoVisual, de modo que tenemos que meterlo
        agregarCarta(tuMano[i]);
    }

    //Comprueba si hay visualmente cartas que ya no están en la mano
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
            if (sinCarta) {
                currentValue.parentNode.removeChild(currentValue);
            } else {
                if (eligeCarta != eligeCartaAnterior) {
                    currentValue.classList.remove("elegible");
                    if (eligeCarta) {
                        crearEvento(currentValue, "click", function(event) {
                            event.preventDefault();
                            try {
                                elegirCarta(numCarta, currentIndex)
                            } catch (e) {
                                console.log("Error al cargar: " + e);
                            }
                        });
                        currentValue.classList.add("elegible");
                    } else {
                        let imgClon = currentValue.cloneNode(true);
                        imgClon.dataset.numeroCarta = numCarta;
                        currentValue.parentNode.replaceChild(imgClon, currentValue);
                    }
                }
            }
        }
    );

    //Esto clasifica con clases quién es exactamente cada jugador, es decir, cuál eres tú, quién es el cuentacuentos, etc
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
                if (typeof posicionJugadores != "undefined" && typeof posicionJugadores[i] != "undefined" && typeof posicionJugadores[i][1] != "undefined") {
                    jugadores[i].posicion.innerHTML = "Puntos: " + posicionJugadores[i][1];
                }
                // for (let j = 0; j < listaFaltan.length; j++) {
                //     if (jugadores[i].correo == listaFaltan[j]) {
                //         currentValue.classList.add("eligiendo");
                //         currentValue.title += " (Está eligiendo carta)";
                //     }
                // }
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
        getAsync(urlGet + "?accion=elegir_carta_inicio&carta_elegida=" + carta + "&pista=" + pista);
    } else if (estadoJuego == "PensandoCartas") {
        cartaElegida = carta;
        getAsync(urlGet + "?accion=elegir_carta_pensando_cartas&carta_elegida=" + carta);
    } else if (estadoJuego == "PensandoCC") {
        if (cuentacuentos == jugadores[jugadorIndice].correo) {
            while (pista == "")
                pista = prompt("Ofrece una pista para los demás jugadores");
            if (pista == "null") {
                return;
            }
            getAsync(urlGet + "?accion=elegir_carta_pensando_cc&carta_elegida=" + carta + "&pista=" + pista);
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