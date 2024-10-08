<!-- Importar inicio de página, menús y contenedor main -->
<?php
    require('../../Diseños/Administrador/header.php');
?>

<!-- Cuerpo de la página -->
<div id="TablaPersonal"> <!-- Contenedor con id nombrada en Vue -->
        <div class="container">
            <!-- Cabecera de la página -->    
            <br>
            <h1 class="text-center fw-bolder">Gestión de Personal</h1>
            <br>
            <div class="row">
                <!-- Botón de nuevo ingreso -->
                <div class="col-lg-2 col-sm-3 col-4">
                    <button class="btn btn-info" title="Nuevo" data-bs-toggle="modal" data-bs-target="#exampleModal">Nueva <i class="bi bi-file-earmark-plus-fill"></i></button>
                </div>
                <!-- Barra de busqueda -->
                <div class="col-lg-3 col-sm-5 col-5">
                    <input type="text" id="buscar" class="form-control" placeholder="Buscar Personal" @keyup="btnBuscar">
                </div>
                <div class="col-lg-6 col-sm-3 col-1"></div> <!-- Separador -->
                <!-- Botón para abrir modal de bajas -->
                <div class="col-lg-1 col-sm-1 col-2">
                    <button class="btn btn-secondary" data-bs-toggle="tooltip" data-bs-placement="top" title="Datos dados de Baja" @click="btnCargarBajas()"><i class="bi bi-database-fill-up"></i></button>
                </div>
            </div>
            <!-- Tabla con los datos de los usuarios agregados en el sistema -->
            <div class="row mt-3">
                <div class="col-lg-12 table-responsive table-responsive-sm">
                    <table class="table table-sm table-striped table-bordered border-dark align-middle text-center">
                        <thead>
                            <tr class="bg-info bg-opacity-50">
                                <th>C.I.</th>
                                <th>Nombre Completo</th>
                                <th>Teléfono</th>
                                <th>Correo</th>
                                <th>Rol Asignado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="datos of Personal">
                                <td>{{datos.cedulaPersona}}</td>
                                <td>{{datos.nombreCompleto}}</td>
                                <td>{{datos.telefonoPersona}}</td>
                                <td>{{datos.correoPersona}}</td>
                                <td>{{datos.nombreRol}}</td>
                                <td>
                                    <!-- Botones de acciones -->
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-primary btn-sm"  @click="btnCargarModalEditar(datos.idPersona,datos.cedulaPersona,datos.nombrePersona,datos.apellidoPersona,datos.telefonoPersona,datos.correoPersona,datos.idRol)" title="Editar Información"><i class="bi bi-pencil-square"></i></button> <!-- Editar datos del usuario -->
                                        <button type="button" class="btn btn-success btn-sm" @click="btnCargarModalEditarContra(datos.idPersona, datos.nombreCompleto)" title="Editar Contraseña"><i class="bi bi-lock-fill"></i></button> <!-- Editar contraseña -->
                                        <button type="button" class="btn btn-danger btn-sm" @click="btnCargarModalEliminar(datos.idUsuario, datos.nombreCompleto)" title="Eliminar"><i class="bi bi-trash"></i></button> <!-- Dar de baja al usuario -->
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
                            <h5 class="modal-title" id="exampleModalLabel">Nuevo Usuario</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form @submit.prevent="btnAgregar">
                            <div class="modal-body">
                                <div class="row mb-2">
                                    <label class="col-sm-4 col-form-label">Cédula</label>
                                    <div class="col-sm-7"><input id="cedulaAgregar" type="text" class="form-control" minlength="10" maxlength="10" required v-only-numbers></div>
                                </div>
                                <div class="row mb-2">
                                    <label class="col-sm-4 col-form-label">Nombre</label>
                                    <div class="col-sm-7"><input id="nombreAgregar" type="text" class="form-control" maxlength="50" required></div>
                                </div>
                                <div class="row mb-2">
                                    <label class="col-sm-4 col-form-label">Apellidos</label>
                                    <div class="col-sm-7"><input id="apellidoAgregar" type="text" class="form-control" maxlength="50" required></div>
                                </div>
                                <div class="row mb-2">
                                    <label class="col-sm-4 col-form-label">Teléfono</label>
                                    <div class="col-sm-7"><input id="telefonoAgregar" type="tel" class="form-control" minlength="10" maxlength="10" required v-only-numbers></div>
                                </div>
                                <div class="row mb-2">
                                    <label class="col-sm-4 col-form-label">Correo</label>
                                    <div class="col-sm-7"><input id="correoAgregar" type="email" class="form-control" maxlength="50" required></div>
                                </div>
                                <div class="row mb-2">
                                    <label class="col-sm-4 col-form-label">Contraseña</label>
                                    <div class="col-sm-7"><input id="contrasenaAgregar" type="password" class="form-control" minlength="8" maxlength="10" :class="{'is-valid': contrasenaValida, 'is-invalid': !contrasenaValida && contrasenaUsuario}" @input="validarContrasenaInput" :style="{ borderColor: contrasenaValida ? '#28a745' : (contrasenaUsuario ? '#dc3545' : '') }" required></div>
                                </div>
                                <div class="row mb-2">
                                    <label class="col-sm-4 col-form-label">Rol Asignado</label>
                                    <div class="col-sm-7">
                                        <select class="form-select" aria-label="Default select example" id="rolAgregar" required>
                                            <option value="" disabled="disabled" selected>Seleccione un Rol</option>
                                            <option v-for="opcion of lisRol" v-bind:value="setIdRol(opcion)">{{opcion.nombreRol}}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" @click="BorrContAgregar">Cerrar</button>
                                <!-- <button type="button" @click="btnAgregar" class="btn btn-primary">Añadir Usuario</button> -->
                                <button type="submit" class="btn btn-primary">Añadir Usuario</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal Editar -->
            <div class="modal fade" id="ModalEditar" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Editar Usuario</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form @submit.prevent="btnEditar">
                            <div class="modal-body">
                                <div class="row mb-2" hidden>
                                    <label class="col-sm-4 col-form-label">idUsuario</label>
                                    <div class="col-sm-7"><input id="idEditar" type="text" class="form-control"></div>
                                </div>
                                <div class="row mb-2">
                                    <label class="col-sm-4 col-form-label">Cédula</label>
                                    <div class="col-sm-7"><input id="cedulaEditar" type="text" class="form-control" minlength="10" maxlength="10" disabled required v-only-numbers></div>
                                </div>     
                                <div class="row mb-2">
                                    <label class="col-sm-4 col-form-label">Nombre</label>
                                    <div class="col-sm-7"><input id="nombreEditar" type="text" class="form-control" maxlength="50" required></div>
                                </div>
                                <div class="row mb-2">
                                    <label class="col-sm-4 col-form-label">Apellidos</label>
                                    <div class="col-sm-7"><input id="apellidoEditar" type="text" class="form-control" maxlength="50" required></div>
                                </div>
                                <div class="row mb-2">
                                    <label class="col-sm-4 col-form-label">Teléfono</label>
                                    <div class="col-sm-7"><input id="telefonoEditar" type="tel" class="form-control" minlength="10" maxlength="10" v-only-numbers required></div>
                                </div>
                                <div class="row mb-2">
                                    <label class="col-sm-4 col-form-label">Correo</label>
                                    <div class="col-sm-7"><input id="correoEditar" type="email" class="form-control" maxlength="50" required></div>
                                </div>
                                <div class="row mb-2">
                                    <label class="col-sm-4 col-form-label">Rol Asignado</label>
                                    <div class="col-sm-7">
                                        <select class="form-select" aria-label="Default select example" id="rolEditar" required>
                                            <option v-for="opcion of lisRol" v-bind:value="setIdRol(opcion)">{{opcion.nombreRol}}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                <button type="submit" class="btn btn-primary">Guardar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal Editar Contraseña -->
            <div class="modal fade" id="ModalEditarContrasena" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Editar Contraseña</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form @submit.prevent="btnEditarContrasena">
                            <div class="modal-body">
                                <div class="row mb-2" hidden>
                                    <label class="col-sm-4 col-form-label">idUsuario</label>
                                    <div class="col-sm-7"><input id="idEditarContrasena" type="text" class="form-control" required></div>
                                </div>
                                <div class="row mb-2">
                                    <label class="col-sm-4 col-form-label">Usuario</label>
                                    <div class="col-sm-7"><input id="nombreUsuarioContrasena" type="text" class="form-control" disabled required></div>
                                </div> 
                                <div class="row mb-2">
                                    <label class="col-sm-4 col-form-label">Nueva Contraseña</label>
                                    <div class="col-sm-7"><input id="contrasenaEditar" type="text" class="form-control" minlength="8" maxlength="10" :class="{'is-valid': contrasenaValida, 'is-invalid': !contrasenaValida && contrasenaUsuario}" @input="validarContrasenaInput" :style="{ borderColor: contrasenaValida ? '#28a745' : (contrasenaUsuario ? '#dc3545' : '') }" required></div>
                                </div>     
                                <div class="row mb-2">
                                    <label class="col-sm-4 col-form-label">Confirmar Contraseña</label>
                                    <div class="col-sm-7"><input id="confirmacionContrasenaEditar" type="text" class="form-control" minlength="8" maxlength="10" required></div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" @click="BorrCont">Cerrar</button>
                                <button type="submit" class="btn btn-primary">Guardar</button>
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
                            <h5 class="modal-title" id="exampleModalLabel">Eliminar Personal</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row mb-2" hidden>
                                <label class="col-sm-4 col-form-label">idUsuario</label>
                                <div class="col-sm-7"><input id="idEliminar" type="text" class="form-control"></div>
                            </div>
                            <label class="col-form-label">¿Desea dar de baja a </label>
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
                            <h5 class="modal-title" id="exampleModalLabel">Personal Dado de Baja</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <table class="table table-sm table-striped table-bordered border-dark align-middle text-center">
                                <thead>
                                    <tr class="bg-info bg-opacity-50">
                                        <th>Nombre Completo</th>
                                        <th>Rol Asignado</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="datos of lisBaja">
                                        <td>{{datos.nombreCompleto}}</td>
                                        <td>{{datos.nombreRol}}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-success btn-sm"  @click="btnDarAlta(datos.idUsuario)" title="Dar de Alta"><i class="bi bi-check-lg"></i></button>
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
    <script src="Modulos JS/Administrador/gestionPersonal.js"></script>

<?php
    require('../../Diseños/Administrador/end.php');
?>