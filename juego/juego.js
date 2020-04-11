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

function cargarAsync(url) {
    if (miXHR) {
        document.getElementById("indicadorAJAX").innerHTML = "<img src='imgs/ajax-loading.gif'/>";
        miXHR.open('GET', url, true);
        miXHR.onreadystatechange = estadoPeticion;
        miXHR.send(null);
    }
}

function estadoPeticion() {
    if (this.readyState == 4 && this.status == 200) {
        var resultados = eval('(' + this.responseText + ')');
        texto = "<table border=1><tr><th>Nombre Centro </th><th>Localidad</th> <th> Provincia </th><th>Telefono</th> <th> Fecha Visita </th><th>Numero Visitantes</th> </tr>";
        for (var i = 0; i < resultados.length; i++) {
            objeto = resultados[i];
            texto += "<tr><td>" + objeto.nombrecentro + "</td><td>" +
                objeto.localidad + "</td><td>" + objeto.provincia + "</td><td>" +
                objeto.telefono + "</td><td>" + objeto.fechavisita + "</td><td>" +
                objeto.numvisitantes + "</td></tr>";
        }
        document.getElementById("indicador").innerHTML = "";
        document.getElementById("resultados").innerHTML = texto;
    }
}

var ajaxXHR, body, divTusCartas, divJugadores, imgPerfil, divMensajes, mensaje1, mensaje2;

crearEvento(window, "load", init);

function init() {
    miXHR = new objetoXHR();

    body = document.body;
    divTusCartas = document.getElementById("tusCartas");
    divJugadores = document.getElementById("jugadores");
    divMensajes = document.getElementById("mensajes");
    mensaje1 = document.getElementById("mensaje1");
    mensaje2 = document.getElementById("mensaje2");
    var nodoDivCartas = document.createElement("div");

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
        // console.log(jugador.img);
    });

    /* Estados del juego:
        "Inicio": No hay cuentacuentos, el primer jugador en elegir carta y pista se convierte el cuentacuentos y se pasa al estado "PensandoCartas"
        "PensandoCC": El cuentacuentos está pensando, es el primer estado del turno (excepto el primer turno)
        "PensandoCartas:X": El cuentacuentos ha elegido carta y ahora la están eligiendo los demás jugadores. Quedan X jugadores por elegir carta
        "Votacion:X": Los jugadores están votando qué carta creen que es del cuentacuentos. Quedan X jugadores por votar
        "Puntos": Se están repartiendo los puntos. Se toma un tiempo en este paso para que todos los jugadores vean cómo van
    */
    var eligeCarta = false;
    if (estadoJuego == "Inicio") {
        divMensajes.classList.remove("quitar");
        // divMensajes.classList.add("quitar");
        mensaje1.innerHTML = "Fase Inicial";
        mensaje2.innerHTML = "El primero en poner carta es el primer cuentacuentos";
        eligeCarta = true;
    } else if (estadoJuego == "PensandoCC") {
        divMensajes.classList.remove("quitar");
        if (cuentacuentos == jugadores[jugadorIndice].correo) {
            mensaje1.innerHTML = "Eres el Cuentacuentos";
            mensaje2.innerHTML = "Te toca elegir carta. Recuerda no pensar en una pista demasiado fácil.";
            eligeCarta = true;
        } else {
            mensaje1.innerHTML = "Esperando al Cuentacuentos";
            mensaje2.innerHTML = "El cuentacuentos está pensando qué carta elegir";
        }
    } else {
        divMensajes.classList.remove("quitar");
        divMensajes.classList.add("quitar");
    }

    function elegirCarta(carta, indice) {
        // tuMano[indice].classList.add("quitar");
        alert("hola, has seleccionado la carta num " + indice + ", que es la " + carta);
    }

    function foreachMano(item, index) {
        img = new Image();
        img.src = "cartas/carta" + item + ".jpg"
        img.title = "Carta " + item;
        if (eligeCarta) {
            crearEvento(img, "click", function() { elegirCarta(item, index) });
        }
        nodoDivCartas.appendChild(img);
    }
    tuMano.forEach(foreachMano);
    divTusCartas.appendChild(nodoDivCartas);
}