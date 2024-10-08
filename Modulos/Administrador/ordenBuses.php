<!-- Importar inicio de página, menús y contenedor main -->
<?php
    require('../../Diseños/Administrador/header.php');
?>

<!-- Cuerpo de la página -->
<div id="Lista"> <!-- Contenedor con id nombrada en Vue -->
    <div class="container">
        <br>
            <!-- Cabecera de la página -->
            <h1 class="text-center fw-bolder">Orden de Asignación de Rutas</h1>
            <br><br>
            <!-- Botón activador de reorganización -->
            <div class="row justify-content-end">
                <div class="col-lg-3">
                    <button id="reorganizar-btn" class="btn btn-info" @click="reorganizar()">Reorganizar lista</button>
                </div>
            </div><br>
            <!-- Listado de Buses en el orden establecido -->
            <div class="row mt-3 justify-content-center">
                <div class="col-lg-4">
                    <ul class="list-group" id="lista-buses-reorganizable">
                        <li class="list-group-item text-center tarea" v-for="dato of listBuses" v-bind:data-id="dato.idBus">{{dato.infoBuses}}</li>
                    </ul>
                </div>
            </div><br><br>
            <!-- Botones para aceptar o descartar cambios cuando se active la reorganización -->
            <div class="row justify-content-center">
                <div class="col-lg-3 d-grid gap-2">
                    <button id="aceptar-btn" class="btn btn-success" @click="aceptarCambios()" style="display: none;">Aceptar cambios</button>        
                </div>
                <div class="col-lg-3 d-grid gap-2">
                    <button id="cancelar-btn" class="btn btn-danger" @click="cancelarCambios()" style="display: none;">Cancelar cambios</button>
                </div>
            </div>
            
    </div>
</div>

<?php
    require('../../Diseños/Administrador/footer.php');  
?>
    <script src="https://cdn.jsdelivr.net/npm/@shopify/draggable@1.0.0-beta.11/lib/sortable.js"></script> //Librería Sorteable para mover los elementos
    <script src="Modulos JS/Administrador/ordenBuses.js"></script>

<?php
    require('../../Diseños/Administrador/end.php');
?>