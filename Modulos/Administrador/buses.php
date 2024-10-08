<!-- Importar inicio de página, menús y contenedor main -->
<?php
    require('../../Diseños/Administrador/header.php');
?>

<!-- Cuerpo de la página -->
<div id="TablaBuses"> <!-- Contenedor con id nombrada en Vue -->
        <div class="container">
            <!-- Cabecera de la página -->    
            <br>
            <h1 class="text-center fw-bolder">Gestión de Buses</h1>
            <br>
            <div class="row">
                <!-- Botón para agregar unidad -->
                <div class="col-lg-2 col-sm-4 col-5">
                    <button class="btn btn-info" title="Nuevo" data-bs-toggle="modal" data-bs-target="#ModalAgregar">Nueva Unidad <i class="bi bi-file-earmark-plus-fill"></i></button>
                </div>
                <!-- Buscador -->
                <div class="col-lg-3 col-sm-6 col-4">
                    <input type="text" id="buscar" class="form-control" placeholder="Buscar Bus (Placa)" @keyup="btnBuscar">
                </div>
                <div class="col-lg-6 col-sm-1 col-1"></div>
                <!-- Botón para abrir modal de bajas -->
                <div class="col-lg-1 col-sm-1 col-2">
                    <button class="btn btn-secondary" data-bs-toggle="tooltip" data-bs-placement="top" title="Datos dados de Baja" @click="btnCargarBajas()"><i class="bi bi-database-fill-up"></i></button>
                </div>
            </div>
            <br><br>
            <!-- Tarjetas donde se enlistan las unidades -->
            <div class="row row-cols-1 row-cols-md-2 g-4">
                <div class="col-lg-3 col-sm-6 col-12" v-for="datos of Bus">
                    <div class="card border-info text-bg-light">
                        <div class="card-body">
                            <!-- Botones de cambio de chofer, estado de unidad y borrar tarjeta -->
                            <div class="d-flex justify-content-between">
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-outline-warning btn-sm" @click="btnCargarModalCambiarChofer(datos.idAsignacionBus, datos.idChofer, datos.numeroBus)" title="Gestionar Chofer"><i class="bi bi-person-fill-gear"></i></button>
                                    <button type="button" class="btn btn-outline-primary btn-sm" @click="btnCargarModalCambiarEstado(datos.idBus, datos.numeroBus, datos.idEstado)" title="Gestionar Estado"><i class="bi bi-tools"></i></button>
                                </div>
                                <button type="button" class="btn btn-danger btn-sm btn-close" @click="btnCargarModalEliminar(datos.idBus, datos.placaBus)"></button>
                            </div>
                            <!-- Iconos dependiendo el tipo de unidad -->
                            <div class="d-flex justify-content-center">
                                <span v-if="datos.idTipoUnidad == 1">
                                    <i class="fs-1 bi-bus-front-fill"></i>
                                </span>
                                <span v-else-if="datos.idTipoUnidad == 2">
                                    <i class="fs-1 bi-truck-front"></i>
                                </span>
                            </div>
                            <!-- Contenido de la Tarjeta -->
                            <h5 class="card-title text-center">Unidad {{datos.numeroBus}}<br>
                                <span v-if="datos.idEstado == 1">
                                    <i class="bi-check-circle-fill text-success"></i> Activo
                                </span>
                                <span v-else-if="datos.idEstado == 2">
                                    <i class="bi-tools text-warning"></i> Mantenimiento
                                </span>
                            </h5>
                            <p class="card-text"><b>Tipo:</b> {{datos.nombreTipo}}<br>
                                                <b>Placa:</b> {{datos.placaBus}}<br>
                                                <b>Año:</b> {{datos.anioBus}}<br>
                                                <b>Capacidad de pasajeros:</b> {{datos.capacidadBus}}<br>
                                                <b>Propietario:</b> {{datos.nombrePropietario}}<br>
                                                <b>Chofer asignado:</b> {{datos.nombreAsignado}}<br>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!--Modal Agregar-->
            <div class="modal fade" id="ModalAgregar" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Nueva Unidad</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form @submit.prevent="btnAgregar">
                            <div class="modal-body">
                                <div class="row mb-2">
                                    <label class="col-sm-4 col-form-label">Tipo de Transporte</label>
                                    <div class="col-sm-7">
                                        <select class="form-select" aria-label="Default select example" id="tipoTransporteAgregar" required>
                                            <option value="" disabled="disabled" selected>Seleccione el tipo</option>
                                            <option v-for="opcion of lisTipoTransporte" v-bind:value="setIdTipoTransporte(opcion)">{{opcion.nombreTipo}}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <label class="col-sm-4 col-form-label">Placa</label>
                                    <div class="col-sm-7"><input id="placaAgregar" type="text" class="form-control" minlength="7" maxlength="7" required></div>
                                </div>
                                <div class="row mb-2">
                                    <label class="col-sm-4 col-form-label">Número de Unidad</label>
                                    <div class="col-sm-7"><input id="numeroAgregar" type="text" class="form-control" maxlength="3" required v-only-numbers></div>
                                </div>
                                <div class="row mb-2">
                                    <label class="col-sm-4 col-form-label">Año de la Unidad</label>
                                    <div class="col-sm-7"><input id="anioAgregar" type="text" class="form-control" minlength="4" maxlength="4" required v-only-numbers></div>
                                </div>
                                <div class="row mb-2">
                                    <label class="col-sm-4 col-form-label">Capacidad de Pasajeros</label>
                                    <div class="col-sm-7"><input id="capacidadAgregar" type="text" class="form-control" minlength="2" maxlength="2" required v-only-numbers></div>
                                </div>
                                <div class="row mb-2">
                                    <label class="col-sm-4 col-form-label">Propietario</label>
                                    <div class="col-sm-7">
                                        <select class="form-select" aria-label="Default select example" id="propietarioAgregar" required>
                                            <option value="" disabled="disabled" selected>Seleccione el propietario</option>
                                            <option v-for="opcion of lisPropietarios" v-bind:value="setIdPropietario(opcion)">{{opcion.nombreCompleto}}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <label class="col-sm-4 col-form-label">Chofer Asignado</label>
                                    <div class="col-sm-7">
                                        <select class="form-select" aria-label="Default select example" id="choferAgregar" required>
                                            <option value="" disabled="disabled" selected>Seleccione el chofer</option>
                                            <option v-for="opcion of lisChoferes" v-bind:value="setIdChofer(opcion)">{{opcion.nombreCompleto}}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" @click="BorrContAgregar">Cerrar</button>
                                <button type="submit" class="btn btn-primary">Añadir Unidad</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal Editar Chofer -->
            <div class="modal fade" id="ModalEditar" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Editar Chofer</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row mb-2" hidden>
                                <label class="col-sm-4 col-form-label">idAsignacionBus</label>
                                <div class="col-sm-7"><input id="idAsignacionBus" type="text" class="form-control" disabled></div>
                            </div>
                            <div class="row mb-2">
                                <label class="col-sm-4 col-form-label">Número de Unidad</label>
                                <div class="col-sm-7"><input id="numeroEditar" type="text" class="form-control" maxlength="3" disabled></div>
                            </div>
                            <div class="row mb-2">
                                <label class="col-sm-4 col-form-label">Chofer Asignado</label>
                                <div class="col-sm-7">
                                    <select class="form-select" aria-label="Default select example" id="choferEditar">
                                        <option value="def" disabled="disabled">Seleccione el chofer</option>
                                        <option v-for="opcion of lisChoferes" v-bind:value="setIdChofer(opcion)">{{opcion.nombreCompleto}}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            <button type="button" @click="btnEditar" class="btn btn-primary">Editar Chofer</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Cambiar Estado de Bus -->
            <div class="modal fade" id="ModalEstadoBus" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Editar Estado Bus</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row mb-2" hidden>
                                <label class="col-sm-4 col-form-label">idBus</label>
                                <div class="col-sm-7"><input id="idBusEstadoEditar" type="text" class="form-control" disabled></div>
                            </div>
                            <div class="row mb-2">
                                <label class="col-sm-4 col-form-label">Número de Unidad</label>
                                <div class="col-sm-7"><input id="numeroEstadoEditar" type="text" class="form-control" maxlength="3" disabled></div>
                            </div>
                            <div class="row mb-2">
                                <label class="col-sm-4 col-form-label">Estado</label>
                                <div class="col-sm-7">
                                    <select class="form-select" aria-label="Default select example" id="estadoEditar">
                                        <option value="def" disabled="disabled">Seleccione el estado</option>
                                        <option value="1">Activo</option>
                                        <option value="2">Mantenimiento</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            <button type="button" @click="btnEditarEstado" class="btn btn-primary">Editar Estado</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Eliminar -->
            <div class="modal fade" id="ModalEliminar" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Eliminar Unidad</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row mb-2" hidden>
                                <label class="col-sm-4 col-form-label">idBus</label>
                                <div class="col-sm-7"><input id="idEliminar" type="text" class="form-control"></div>
                            </div>
                            <label class="col-form-label">¿Desea eliminar la unidad con placa </label>
                            <label id="placaEliminar"></label> ?
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
                            <h5 class="modal-title" id="exampleModalLabel">Vehículos Dados de Baja</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <table class="table table-sm table-striped table-bordered border-dark align-middle text-center">
                                <thead>
                                    <tr class="bg-info bg-opacity-50">
                                        <th>Placa</th>
                                        <th>Tipo</th>
                                        <th>Propietario</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="datos of lisBaja">
                                        <td>{{datos.placaBus}}</td>
                                        <td>{{datos.nombreTipo}}</td>
                                        <td>{{datos.nombrePropietario}}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-success btn-sm"  @click="btnDarAlta(datos.idBus)" title="Dar de Alta"><i class="bi bi-check-lg"></i></button>
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
    <script src="Modulos JS/Administrador/buses.js"></script>

<?php
    require('../../Diseños/Administrador/end.php');
?>