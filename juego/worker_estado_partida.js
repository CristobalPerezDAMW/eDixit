var urlGetEstado, ajaxXHR = new objetoXHR();

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
        // var resultados = eval('(' + this.responseText + ')');
        postMessage(this.responseText);
    }
}

function comprobarEstadoPartida() {
    ajaxXHR.open('GET', urlGetEstado + "?getEstadoPartida=true", true);
    ajaxXHR.onreadystatechange = estadoPeticion;
    ajaxXHR.send(null);
}

onmessage = function(e) {
    urlGetEstado = e.data[0];
    // ajaxXHR = e.data[1];

    setTimeout("comprobarEstadoPartida()", 1000);
};