<!-- Importar inicio de página, menús y contenedor main -->
<?php
    require('../../Diseños/Administrador/header.php');
?>

<!-- Cuerpo de la página -->
<div id="TablasAsigRutas"> <!-- Contenedor con id nombrada en Vue -->
        <div class="container">
            <!-- Cabecera de la página -->    
            <br>
            <h1 class="text-center fw-bolder">CUADRO DE TRABAJO</h1>
            <br>
            <div class="row justify-content-around">
                <div class="col-lg-4 col-sm-12 col-12">
                    <p><b>Fecha asignación:</b> {{fechaFormateada}}</p>
                </div>
                <!-- Vue-if para mostrar botones de confirmaciones de rutas, si la variables datosConfirmados es true se mostrará la sección sino no -->
                <div class="col-lg-2 col-sm-4 col-4 text-center" v-if="datosConfirmados">
                    <div class="btn-group w-100">
                        <button class="btn btn-warning" title="Asignación Manual" @click="btnEditManual()"><i class="bi bi-pencil-square"></i></button>
                        <button class="btn btn-primary" title="Previsualización siguiente día" @click="btnVistaPrevia()"><i class="bi bi-eye-fill"></i></button>
                    </div>
                </div>
                <!-- Filtros de tablas -->
                <div class="col-lg-4 col-sm-8 col-8">
                    <select class="form-select" id="Filtro" @change="listar()">
                        <option value="def">Elija una tabla</option>
                        <option v-for="opcion of lisTabla" v-bind:value="setIdTabla(opcion)">{{opcion.nombreTabla}}</option>
                    </select>
                </div>
            </div>
                        
            <!-- Vue-if para mostrar las dos posibles formas de visualizar las asignaciones.
                Si las asignaciones no estan confirmadas se mostrarán en una tabla con los nombres de los socios en un selector para poder ser modificadas en caso de lunes a viernes y en caso de sabados y domingos se mostrarán los selectores en blanco 
                Si las asignaciones ya estan confirmadas se mostrará una tabla estática para la visualización del Administrador -->
            <div class="row mt-3" v-if="!datosConfirmados">
                <div class="col-lg-12 col-sm-12 table-responsive-sm">
                    <!-- Tabla con asignaciones no confirmadas -->
                    <table class="table table-sm table-striped table-bordered border-dark text-center">
                        <thead>
                            <tr><th class="text-center" colspan="5">RUTAS {{nombreTabla}}</th></tr>
                            <tr class="bg-info bg-opacity-50">
                                <th WIDTH="20%">Socio</th>
                                <th WIDTH="30%">Columna Izquierda</th>
                                <th WIDTH="50%">Columna Derecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="datos in AsigTabla" :key="datos.id">
                                <td>
                                    <select class="form-select" v-model="datos.idUsuario">
                                        <option v-for="dato in lisSocios" :key="dato.idUsuario" :value="dato.idUsuario">{{dato.inicialesSocio}}</option>
                                    </select>
                                </td>
                                <td>{{datos.colIzquierda}}</td>
                                <td v-if="datos.colDerecha != null">{{datos.colDerecha}}</td>
                                <td v-else></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- Botones para confirmar las rutas -->
                <div class="col-12 col-sm-12 col-lg-12 text-center">
                    <button class="btn btn-success" v-if="mostrarBtn" @click="confirmarAsignaciones">Confirmar Asignaciones</button> <!-- Botón se muestra si no están confirmadas las rutas -->
                    <button class="btn btn-warning" v-if="manualBtn" @click="confEditManual">Aplicar Cambios en Rutas</button> <!-- Botón se muestra si se va a realizar cambios manuales en las asignaciones -->
                </div>
            </div>
            <!-- En caso que el anterior Vue-if sea falso se muestra unicamente la tabla de información de asignaciones -->
            <div class="row mt-3" v-else>
                <div class="col-lg-12 col-sm-12 table-responsive-sm">
                    <!-- Tabla con asignaciones confirmadas -->
                    <table class="table table-sm table-striped table-bordered border-dark text-center">
                        <thead>
                            <tr><th class="text-center" colspan="5">RUTAS {{nombreTabla}}</th></tr>
                            <tr class="bg-info bg-opacity-50">
                                <th WIDTH="20%">Socio</th>
                                <th WIDTH="30%">Columna Izquierda</th>
                                <th WIDTH="50%">Columna Derecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="datos in AsigTabla">
                                <td>{{datos.inicialesSocio}}</td>
                                <td>{{datos.colIzquierda}}</td>
                                <td v-if="datos.colDerecha != null">{{datos.colDerecha}}</td>
                                <td v-else></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div> 
</div>

    <!-- CDN de jsPDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js" integrity="sha512-qZvrmS2ekKPF2mSznTQsxqPgnpkI4DNTlrdUmTzrDgektczlKNRRhy5X5AAOnx5S09ydFYWWNSfcEqDTTHgtNA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    
    <!-- CDN de jsPDF AutoTable -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js" integrity="sha512-2/YdOMV+YNpanLCF5MdQwaoFRVbTmrJ4u4EpqS/USXAQNUDgI5uwYi6J98WVtJKcfe1AbgerygzDFToxAlOGEQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<?php
    require('../../Diseños/Administrador/footer.php');  
?>
    
    <script src="Modulos JS/Administrador/asigRutas.js"></script>
    
<?php
    require('../../Diseños/Administrador/end.php');
?>