<?php
require("cabecera.php");
?>
<nav class="container p-5 indice">
    <div class="row salas_publicas">
        <div class="col-12">
            <h2>Partida Pública</h2>
        </div>
        <div class="col-md-6">
            <a href="sala">Buscar Salas</a>
        </div>
        <div class="col-md-6">
            <a href="sala/crear">Crear una Sala</a>
        </div>
    </div>
    <!-- Me hubiera gustado incluir un tipo de sala que estuviera oculta y fuera accesible por invitación o URL, pero de momento será sólo posible como una sala con contraseña
    <div class="row salas_privadas">
        <div class="col-12">
            <h2>Partida Privada</h2>
        </div>
        <div class="col-md-6">
            <a href="sala/privada">Buscar Salas</a>
        </div>
        <div class="col-md-6">
            <a href="sala/privada/crear">Crear una Sala</a>
        </div>
    </div> -->
</nav>
</body>
</html>