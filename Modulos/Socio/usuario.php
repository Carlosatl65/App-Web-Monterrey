<!-- Importar inicio de página, menús y contenedor main -->
<?php
    require('../../Diseños/Socio/header.php');
?>

<!-- Cuerpo de la página -->
<div id="datosUsuario"> <!-- Contenedor con id nombrada en Vue -->
    <div class="container">
        <!-- Cabecera de la página -->
        <br><br>
        <h1 class="text-center">Datos de Usuario</h1>
        <br>
        <!-- Tarjeta contenedora de datos de usuario -->
        <div class="row">
            <div class="d-flex align-items-center justify-content-center">
                <div class="col-12 col-md-5 mb-3">
                    <div class="card" v-for="dato of Datos">
                        <div class="card-body">
                            <form @submit.prevent="actualizarDatos"> <!-- Prevención de envio de datos -->
                            <!-- Botón para cambiar contraseña -->
                                <div class="row justify-content-end">
                                    <div class="col-4">
                                        <button type="button" class="btn btn-success btn-sm" @click="btnCargarModalEditarContra(dato.idPersona)" title="Editar Contraseña" :hidden="mostrarBotonesActivado"><i class="bi bi-lock-fill"></i> Edit. Contraseña</button>
                                    </div>
                                </div>
                                <!-- Imagen de usuario -->
                                <div class="user-image-container text-center">
                                    <div class="form-container">
                                        <img :src="imageUrl" alt="User Image" class="img-fluid user-image" @click="selectFile">
                                        <div class="overlay" @click="selectFile">
                                            <span>Presione para cambiar la imagen</span>
                                        </div>
                                    </div>
                                </div>
                                <!-- Imput oculto para cambiar la foto de perfil -->
                                <div class="row">
                                    <div class="col-3"></div>
                                    <div class="col-6">
                                        <input class="form-control form-control-sm" id="imagen_usuario" type="file" accept="image/jpeg" hidden @change="onFileChange">
                                    </div>
                                    <div class="col-3"></div>
                                </div>
                                <br>
                                <!-- Datos de Usuario -->
                                <div class="row text-center">
                                    <div class="col-3"></div>
                                    <div class="col-6">
                                        <label for="ci_usuario" class="form-label" disable>Identificación</label>
                                        <input class="form-control form-control-sm text-center" type="text" placeholder="" id="ci_usuario" v-bind:value="dato.cedulaPersona" required disabled>
                                    </div>
                                    <div class="col-3"></div>
                                </div><br>
                                <div class="row">
                                    <div class="col-6">
                                        <label for="nombre_usuario" class="form-label">Nombre Usuario</label>
                                        <input class="form-control form-control-sm" type="text" placeholder="" id="nombre_usuario" v-bind:value="dato.nombrePersona" required disabled>
                                    </div>
                                    <div class="col-6">
                                        <label for="apellido_usuario" class="form-label">Apellido Usuario</label>
                                        <input class="form-control form-control-sm" type="text" placeholder="" id="apellido_usuario" v-bind:value="dato.apellidoPersona" required disabled>
                                    </div>
                                </div><br>
                                <div class="row">
                                    <div class="col-6">
                                        <label for="correo_usuario" class="form-label">Correo Electrónico</label>
                                        <input class="form-control form-control-sm" type="email" placeholder="" id="correo_usuario" v-bind:value="dato.correoPersona" @input="mostrarBotones" required>
                                    </div>
                                    <div class="col-6">
                                        <label for="telefono_usuario" class="form-label">Número Telefónico</label>
                                        <input class="form-control form-control-sm" type="text" placeholder="" id="telefono_usuario" minlength="10" maxlength="10" v-bind:value="dato.telefonoPersona" @input="mostrarBotones" v-only-numbers required>
                                    </div>
                                </div><br>
                                <!-- Botones para aplicar o descartar cambios de información de usuario -->
                                <div class="row" id="btnAcciones" :hidden="!mostrarBotonesActivado">
                                    <div class="col-2 col-sm-4 col-lg-3"></div>
                                    <div class="col-4 col-sm-2 col-lg-3 d-flex justify-content-center align-items-center">
                                        <button class="btn btn-success" type="submit">Aplicar</button>
                                    </div>
                            
                                    <div class="col-4 col-sm-2 col-lg-3 d-flex justify-content-center align-items-center">
                                        <button class="btn btn-danger" type="button" @click="cancelar">Cancelar</button>
                                    </div>
                                    <div class="col-2 col-sm-4 col-lg-3"></div>
                                </div><br>
                            </form>
                        </div>
                    </div> <!-- Final de la tarjeta -->
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
    </div> <!-- Final del contenedor -->
</div>

<?php
    require('../../Diseños/Socio/footer.php');
?>

<link rel="stylesheet" href="Modulos/Socio/usuario.css"> <!-- Estilos para foto de usuario -->
<script src="Modulos JS/Socio/usuario.js"></script>

<?php
    require('../../Diseños/Socio/end.php');
?>
