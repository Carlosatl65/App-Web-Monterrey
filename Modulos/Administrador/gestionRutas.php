<!-- Importar inicio de página, menús y contenedor main -->
<?php
    require('../../Diseños/Administrador/header.php');

?>

<!-- Cuerpo de la página -->
<div id="TablaRutas"> <!-- Contenedor con id nombrada en Vue -->
        <div class="container">
            <!-- Cabecera de la página -->
            <br>
            <h1 class="text-center fw-bolder">Gestión de Rutas</h1>
            <br>
            <div class="row">
                <!-- Botón de nuevo ingreso -->
                <div class="col-lg-2 col-sm-3 col-3">
                    <button class="btn btn-info" title="Nuevo Registro" data-bs-toggle="modal" data-bs-target="#exampleModal">Nuevo <i class="bi bi-file-earmark-plus-fill"></i></button>
                </div>
                <!-- Barra de busqueda -->
                <div class="col-lg-3 col-sm-3 col-6">
                    <input type="text" id="buscar" class="form-control" placeholder="Buscar por columna izquierda" @keyup="btnBuscar">
                </div>
                <div class="col-lg-2 col-sm-1 col-1"></div> <!-- Separador -->
                <!-- Botón para abrir modal de bajas -->
                <div class="col-lg-1 col-sm-1 col-2">
                    <button class="btn btn-secondary" data-bs-toggle="tooltip" data-bs-placement="top" title="Datos dados de Baja" @click="btnCargarBajas()"><i class="bi bi-database-fill-up"></i></button>
                </div>
                <!-- Filtro de Tablas -->
                <div class="col-lg-2 col-sm-2 col-6">
                    <select class="form-select" id="Filtro" @change="listar()">
                        <option value="0" selected>Todo</option>
                        <option v-for="opcion of lisTabla" v-bind:value="setIdTabla(opcion)">{{opcion.nombreTabla}}</option>
                    </select>
                </div>
                <!-- Filtro de días de rutas -->
                <div class="col-lg-2 col-sm-2 col-6">
                    <select class="form-select" id="FiltroDias" @change="listar()">
                        <option value="1">Lunes a Viernes</option>
                        <option value="2">Sábado</option>
                        <option value="3">Domingo</option>
                    </select>
                </div>
            </div>
            <!-- Tabla que enlista las rutas según los filtros -->
            <div class="row mt-3">
                <div class="col-lg-12 table-responsive-sm">
                    <table class="table table-sm table-striped table-bordered border-dark align-middle text-center">
                        <thead>
                            <tr class="bg-info bg-opacity-50">
                                <th>Columna Izquierda</th>
                                <th>Columna Derecha</th>
                                <th>Tabla</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="datos of Rutas">
                                <td>{{datos.colIzquierda}}</td>
                                <td v-if="datos.colDerecha != null">{{datos.colDerecha}}</td> <!-- Si la columna derecha tiene información mostrar la misma -->
                                <td v-else></td> <!-- Si la columna derecha no tiene información o es null, se deja la celda en blanco -->
                                <td>{{datos.nombreTabla}}</td>
                                <td>
                                    <!-- Botón de acción para dar de baja/eliminar la ruta -->
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-danger btn-sm" @click="btnCargarModalEliminar(datos.idRuta, datos.colIzquierda, datos.colDerecha, datos.nombreTabla)" title="Eliminar"><i class="bi bi-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!--Modal Agregar-->
            <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Nueva Ruta</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form @submit.prevent="btnAgregar"> <!-- Prevención de submit -->
                            <div class="modal-body">
                                <div class="row mb-2">
                                    <label class="col-sm-4 col-form-label">Inf. Col Izquierda</label>
                                    <div class="col-sm-7"><input id="colIzquierdaAgregar" type="text" class="form-control" maxlength="100" required></div>
                                </div>
                                <div class="row mb-2">
                                    <label class="col-12 col-sm-4 col-form-label">Inf. Col Derecha</label>
                                    <div class="col-9 col-sm-7"><input id="colDerechaAgregar" type="text" class="form-control" maxlength="100"></div>
                                    <button class="col-3 col-sm-1 btn" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Si la ruta no tiene información en este campo, dejar el parámetro en blanco"><i class="bi bi-info-circle"></i></button>
                                </div>
                                <div class="row mb-2">
                                    <label class="col-sm-4 col-form-label">Días de Horario</label>
                                    <div class="col-sm-7">
                                        <select class="form-select" aria-label="Default select example" id="idDia" @change="mostrarSocios()" required>
                                            <option value="" disabled="disabled" selected>Seleccione un horario</option>
                                            <option value="1">Lunes a Viernes</option>
                                            <option value="2">Sábado</option>
                                            <option value="3">Domingo</option>
                                        </select>
                                    </div>
                                </div>
                                <!-- Si se escoje los días 'lunes a vineres' para agregar la ruta se mostrará el apartado para entrenar las asignaciones automáticas -->
                                <div class="row mb-2" v-if="entrenamientoAlgoritmo">
                                    <label class="col-sm-4 col-form-label">Socio Asignado el Día de Hoy</label>
                                    <div class="col-sm-7">
                                        <select class="form-select" aria-label="Default select example" id="Socios" required>
                                            <option value="" disabled="disabled" selected>Seleccione una socio</option>
                                            <option v-for="opcion of Socios" v-bind:value="setIdSocios(opcion)">{{opcion.nombre}}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <label class="col-sm-4 col-form-label">Tabla Asignada</label>
                                    <div class="col-sm-7">
                                        <select class="form-select" aria-label="Default select example" id="tablaAsignar" required>
                                            <option value="" disabled="disabled" selected>Seleccione una tabla</option>
                                            <option v-for="opcion of lisTabla" v-bind:value="setIdTabla(opcion)">{{opcion.nombreTabla}}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" @click="BorrContAgregar">Cerrar</button>
                                <button type="submit" class="btn btn-primary">Añadir Ruta</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            

            <!-- Modal Eliminar -->
            <div class="modal fade" id="ModalEliminar" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Eliminar Ruta</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row mb-2" hidden>
                                <label class="col-sm-4 col-form-label">datos</label>
                                <div class="col-sm-7"><input id="idEliminar" type="text" class="form-control"></div>
                            </div>
                            <label class="col-form-label">¿Desea dar de baja la ruta </label>
                            <label id="nombreEliminar"></label> ?
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            <button type="button" @click="btnEliminar" class="btn btn-danger">Eliminar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Datos dados de Baja -->
            <div class="modal fade bd-example-modal-lg" id="ModalBaja" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Rutas Dadas de Baja</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body table-responsive-sm table-responsive-lg">
                            <table class="table table-sm table-striped table-bordered border-dark align-middle text-center">
                                <thead>
                                    <tr class="bg-info bg-opacity-50">
                                        <th>Columna Izquierda</th>
                                        <th>Columna Derecha</th>
                                        <th>Días de Asignación</th>
                                        <th>Tabla</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="datos of lisBaja">
                                        <td>{{datos.colIzquierda}}</td>
                                        <td v-if="datos.colDerecha != null">{{datos.colDerecha}}</td>
                                        <td v-else></td>
                                        <td>{{datos.nombreDia}}</td>
                                        <td>{{datos.nombreTabla}}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-success btn-sm"  @click="btnDarAlta(datos.idRuta, datos.idDia)" title="Dar de Alta"><i class="bi bi-check-lg"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div> 
</div>


<?php
    require('../../Diseños/Administrador/footer.php');
?>

<script src="Modulos JS/Administrador/gestionRutas.js"></script>

<?php
    require('../../Diseños/Administrador/end.php');
?>