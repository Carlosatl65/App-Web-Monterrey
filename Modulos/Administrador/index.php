<!-- Importar inicio de página, menús y contenedor main -->
<?php
    require('../../Diseños/Administrador/header.php');
?>

    <!-- Cuerpo de la página -->
    <br><br>
    <div class="container-fluid" id="Tarjetas"> <!-- Contenedor con id nombrada en Vue -->
        <div class="row">   
            <!-- Tarjeta de Buses -->
            <div class="col-6 col-sm-4 col-lg-4 mb-3">
                <div class="card shadow-lg text-center text-bg-danger h-100">
                    <div class="card-header">
                        <i class="bi bi-bus-front fs-1"></i>
                    </div>
                    <div class="card-body" v-for="dato of Buses">
                        <h2 class="card-title">Buses</h2>
                        <p class="card-text fs-1">{{dato.TotalBuses}}</p>
                    </div>
                </div>
            </div>
            <!-- Tarjeta de Usuarios -->
            <div class="col-6 col-sm-4 col-lg-4 mb-3">
                <div class="card shadow-lg text-center text-bg-info h-100">
                    <div class="card-header">
                        <i class="bi bi-people fs-1"></i>
                    </div>
                    <div class="card-body" v-for="dato of Usuarios">
                        <h2 class="card-title">Personal</h2>
                        <p class="card-text fs-1">{{dato.TotalUsuarios}}</p>
                    </div>
                </div>
            </div>
            <!-- Tarjeta de Rutas -->
            <div class="col-12 col-sm-4 col-lg-4 mb-3">
                <div class="card shadow-lg text-center text-bg-warning h-100">
                    <div class="card-header">
                        <i class="bi bi-geo-alt fs-1"></i>
                    </div>
                    <div class="card-body" v-for="dato of Rutas">
                        <h2 class="card-title">Rutas</h2>
                        <p class="card-text fs-6 fw-medium">{{dato.lunesVienes}} - Lunes a Viernes<br>{{dato.sabado}} - Sabados<br>{{dato.domingo}} - Domingos</p>
                    </div>
                </div>
            </div>     
        </div><br>
        <!-- Cuadro informativo -->
        <div class="row justify-content-center">
            <div class="col-lg-6 col-sm-10 col-12">
                <div class="card shadow-sm border-dark text-center h-100">
                    <div class="card-body" v-for="dato of Rutas">
                        <p class="card-text fw-medium">Esta aplicación es de uso exclusivo para la Cooperativa de Transporte de Pasajeros "Monterrey-Villegas".<br>Permite la asignación de rutas y la centralización de datos operativos para mejorar la eficiencia y el control de nuestras operaciones.<br>Por favor, asegúrese de seguir las políticas y procedimientos establecidos para el uso adecuado de esta herramienta.<br><i class="fs-3 bi-person-workspace"></i><br>Para informar de cualquier problema técnico o consulta, por favor contacte al equipo de soporte técnico: <a href="mailto:carlosatl65@gmail.com" class="text-decoration-none">carlosatl65@gmail.com</a> <i class="fs-5 bi-tools"></i></p>
                    </div>
                </div>
            </div>
        </div>
        <br>
    </div>

<?php
    require('../../Diseños/Administrador/footer.php');
?>

    <script src="Modulos JS/Administrador/inicio.js"></script>

<?php
    require('../../Diseños/Administrador/end.php');
?>