/* Funciones para directiva Vue para ingresar solo números, evitar pegado de texto y arrastrar y soltar */
function restrictToNumbers(event) {
    const value = event.target.value;
    if (!/^\d*$/.test(value)) {
        event.target.value = value.replace(/[^\d]/g, '');
    }
}

function handlePaste(event) {
    const pasteData = event.clipboardData.getData('text');
    if (!/^\d*$/.test(pasteData)) {
        event.preventDefault();
    }
}

function handleDrop(event) {
    event.preventDefault();
    const dropData = event.dataTransfer.getData('text');
    if (/^\d*$/.test(dropData)) {
        event.target.value = dropData;
    }
}
//Directiva Vue para agregar solo números en imputs, evitar pegado de texto y arrastrar y soltar
Vue.directive('only-numbers', {
    bind(el) {
        el.addEventListener('input', restrictToNumbers);
        el.addEventListener('paste', handlePaste);
        el.addEventListener('dragover', event => event.preventDefault());
        el.addEventListener('drop', handleDrop);
    },
    unbind(el) {
        el.removeEventListener('input', restrictToNumbers);
        el.removeEventListener('paste', handlePaste);
        el.removeEventListener('dragover', event => event.preventDefault());
        el.removeEventListener('drop', handleDrop);
    }
});

var url = "BD/infoUsusario.php"; /* Ruta de archivo php */
var Inventario = new Vue({
    el: '#datosUsuario', /* Nombre de contenedor en vista */
    data: {
        Datos: [],
        correoUsuario: "",
        telefonoUsuario: "",
        selectedFile: null,
        imageUrl: "",
        cambiosRealizados: false,
        idPersona: "",
        contrasenaUsuario:"",
        confirmacionContrasena: "",
        contrasenaValida: false,


    },
    methods: {
        /* Listar información del usuario e imagen*/
        listarInformacion: function () {
            axios.post(url, {
                opcion: "listar",
            }).then(response => {
                this.Datos = response.data;
                if (this.Datos.length > 0) {
                    let usuario = this.Datos[0];
                    if (usuario.imagen) {
                        this.imageUrl = this.baseUrl() + usuario.imagen + "?t=" + new Date().getTime();
                    }
                }
            });
        },
        /* Actualizar los datos de correo y teléfono del usuario */
        actualizarDatos: function () {
            this.correoUsuario = $("#correo_usuario").val();
            this.telefonoUsuario = $("#telefono_usuario").val();
            axios.post(url, {
                opcion: "actualizarDatos",
                correoUsuario: this.correoUsuario,
                telefonoUsuario: this.telefonoUsuario
            }).then(response => {
                this.correoUsuario = "";
                this.telefonoUsuario = "";
                this.listarInformacion();
                this.cambiosRealizados = false;
                Swal.fire({
                    title: 'Datos Actualziados!',
                    text: 'Se realizaron exitosamente los cambios',
                    icon: 'success',
                });
            });
        },
        /* Al dar click en el contenedor de la foto de perfil en realidad ejecuta el método que da click al imput de tipo file que se encuentra oculto */
        selectFile: function () {
            document.getElementById('imagen_usuario').click();
        },
        /* Validar si el archivo subido desde el dispositivo es una imagen y el tipo permitido */
        onFileChange: function (event) {
            this.selectedFile = event.target.files[0];
            if (this.selectedFile && this.selectedFile.type === 'image/jpeg') {
                this.imageUrl = URL.createObjectURL(this.selectedFile);
                this.uploadImage();
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: 'Por favor, suba una imagen JPG',
                    icon: 'error',
                });
                this.selectedFile = null;
                document.getElementById('imagen_usuario').value = '';
            }
        },
        /* Método para actualizar la imagen de usuario y la ruta en la base de datos */
        uploadImage: function () {
            if (!this.selectedFile) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Por favor, seleccione una imagen',
                    icon: 'error',
                });
                return;
            }
            let formData = new FormData();
            formData.append('image', this.selectedFile);
            /* Se envia la imagen para que se pueda guardar y se acualize la ruta en la base de datos */
            axios.post('BD/upload.php', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            }).then(response => {
                this.listarInformacion(); // Volver a cargar la información del usuario después de subir la imagen
                document.querySelector('.sidebar img').src = this.imageUrl;
                Swal.fire({
                    title: 'Foto Actualizada!',
                    text: 'Se realizo exitosamente la actualización.\nCierre la sesion y vuelva a ingresar para ver los cambios',
                    icon: 'success',
                });
            });
        },

        /* URL base de donde se este alojado el programa, si se encuentra en entorno de desarrollador localhost se debe especificar el nombre de la carpeta contenedora */
        baseUrl: function() {
            return window.location.protocol + "//" + window.location.host + "/MONTERREY/";
        },

        /* Mostrar botones si se realizan cabios en los datos del usuario para aceptar o descartar los cambios */
        mostrarBotones: function () {
            this.cambiosRealizados = true;
        },

        //Cargar modal editar contraseña
        btnCargarModalEditarContra: async function(idPersona){
            $("#idEditarContrasena").val(idPersona);
            $("#ModalEditarContrasena").modal("show");
        },
        //Editar contraseña
        btnEditarContrasena: async function(){
            this.idPersona = $("#idEditarContrasena").val();
            this.contrasenaUsuario = $("#contrasenaEditar").val();
            this.confirmacionContrasena = $("#confirmacionContrasenaEditar").val();
            /* Validar si la contraseña tiene el formato valido, si no lo tiene se mostrará un mensaje de error y no se enviará a actualizar la contraseña */
            if (!this.validarContrasena(this.contrasenaUsuario)) {
                Swal.fire({
                    title: 'Estructura de Contraseña Erronea!',
                    text: 'La contraseña debe contener al menos una letra mayúscula, una letra minúscula, un número y un carácter especial.',
                    icon: 'error',
                });
                return;
            }
            /* Si la contraseña y la confirmación son iguales se envia a actualizar la contraseña ingresada */
            if(this.contrasenaUsuario === this.confirmacionContrasena){
                axios.post(url, {
                    opcion:"editarContrasena",
                    idPersona:this.idPersona,
                    contrasenaUsuario:this.contrasenaUsuario,
                }
                ).then(response => {
                    this.listarInformacion();
                    this.idPersona = "";
                    this.contrasenaUsuario = "";
                    this.confirmacionContrasena = "";
                    $("#contrasenaEditar").val("");
                    $("#confirmacionContrasenaEditar").val("");
                    this.BorrCont(); //borrar contenido de imputs del modal
                    $("#ModalEditarContrasena").modal("hide");
                    Swal.fire({
                        title: 'Contraseña Actualizada!',
                        text: 'Se actualizo exitosamente la contraseña del usuario',
                        icon: 'success',
                    });
                });
            }else{
                /* Si no coinciden las contraseñas se mostrará un mensaje de error */
                Swal.fire({
                    title: 'Error!',
                    text: 'No coinciden las contraseñas',
                    icon: 'error',
                });
            }
        },

        /* Método para validar el formato de las contraseñas Mayusculas, minusculas, numeros y caracteres especiales */
        validarContrasena(contrasena) {
            const mayuscula = /[A-Z]/.test(contrasena);
            const minuscula = /[a-z]/.test(contrasena);
            const numero = /[0-9]/.test(contrasena);
            const caracterEspecial = /[!@#$%^&*(),.?":{}|<>]/.test(contrasena);
    
            return mayuscula && minuscula && numero && caracterEspecial;
        },

        /* Ejecutar validador de contraseña en tiempo real */
        validarContrasenaInput(event) {
            const contrasena = event.target.value;
            this.contrasenaValida = this.validarContrasena(contrasena);
        },

        /* Borrar contenido y estilos de imputs del modal editar contraseña */
        BorrCont: function(){
            $("#contrasenaEditar").val("");

            //Eliminar clases
            if($("#contrasenaEditar").hasClass("is-invalid")){
                $("#contrasenaEditar").removeClass("is-invalid");
            }else if($("#contrasenaEditar").hasClass("is-valid")){
                $("#contrasenaEditar").removeClass("is-valid");
            }

            //Eliminar Estilos
            if($("#contrasenaEditar").prop("style")){
                $("#contrasenaEditar").removeAttr("style");
            }

            $("#confirmacionContrasenaEditar").val("");
            
        },

        /* Cancelar y descartar los cambios de informaciòn del usuario */
        cancelar: function(){
            this.cambiosRealizados = false;
            this.listarInformacion();
        }

    },
    computed: {
        /* Método computarizado para monitorear si existen cambios en la información del usuario, retorna true o false de la variable cambiosRealizados */
        mostrarBotonesActivado: function () {
            return this.cambiosRealizados;
        }
    },
    created: function () {
        this.listarInformacion();
    },
});