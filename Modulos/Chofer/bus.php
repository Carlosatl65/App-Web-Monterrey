<!-- Importar inicio de página, menús y contenedor main -->
<?php
    require('../../Diseños/Chofer/header.php');
?>

<!-- Cuerpo de la página -->
<div id="busSocio"> <!-- Contenedor con id nombrada en Vue -->
    <div class="container">
        <br>
        <!-- Cabecera de la página --> 
        <h1 class="text-center fw-bolder">Unidad Asignada</h1>
        <br>
        <!-- Tarjeta donde se enlista la unidad asignada -->
        <div class="row row-cols-1 row-cols-md-2 g-4 justify-content-center">
            <div class="col-lg-6" v-for="datos of Bus">
                <div class="card border-info text-bg-light">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <!-- Botón para cambiar estado de bus -->
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-primary btn-sm" @click="btnCargarModalCambiarEstado(datos.idBus, datos.numeroBus, datos.idEstado)" title="Gestionar Estado"><i class="bi bi-tools"></i></button>
                            </div>
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

    </div>
</div>

<?php
    require('../../Diseños/Chofer/footer.php');
?>

    <script src="Modulos JS/Chofer/bus.js"></script>

<?php
    require('../../Diseños/Chofer/end.php');
?>