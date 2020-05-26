if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
}

var cImagen, cNombre, cEmail, cContra1, cContra2;

function init() {
    cImagen = document.getElementById("cImagen");
    cNombre = document.getElementById("cNombre");
    cEmail = document.getElementById("cEmail");
    cContra1 = document.getElementById("cContra1");
    cContra2 = document.getElementById("cContra2");
}

function check() {
    if (cImagen.value == "" && cNombre.value == "" && cEmail.value == "" && cContra1.value == "") {
        alert("Esta acción no hubiera supuesto ningún cambio");
        return false;
    }
    if (cContra1.value != cContra2.value) {
        alert("Las contraseñas no coinciden");
        return false;
    }
    return true;
}