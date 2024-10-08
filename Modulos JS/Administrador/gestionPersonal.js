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


var url = "BD/Administrador/crudGestionPersonal.php"; /* Ruta de archivo php */
var Inventario = new Vue({
    el:'#TablaPersonal', /* Nombre de contenedor en vista */
    data:{
        Personal: [],
        lisRol: [],
        lisBaja: [],
        cedulaPersona: "",
        idPersona: "",
        nombrePersona: "",
        apellidoPersona: "",
        correoPersona: "",
        telefonoPersona: "",
        contrasenaUsuario: "",
        confirmacionContrasena: "",
        idRol: "",
        contrasenaValida: false,
        
    },
    methods:{
        
        //Listado Roles
        listadoRol: function(){
            axios.post(url, {opcion:"lstRol"}).then(response =>{
                this.lisRol = response.data;
            });
        },

        // ID DE Listado Roles
        setIdRol(opc){
            for(var i=0; i<this.lisRol.length; i++){
                if(opc == this.lisRol[i]){
                    return this.lisRol[i].idRol;
                }
            }
        },

        //Listar el personal en tabla
        listar:function(){
            axios.post(url, {opcion:"listar"}).then(response =>{
                this.Personal = response.data;
            });
            $("#buscar").val("");
        },
        //Buscar
        btnBuscar:function(){
            dato = $("#buscar").val();
            axios.post(url, {opcion:"buscar", buscar:dato}).then(response =>{
                this.Personal = response.data;
            });
        },
        //agregar
        btnAgregar: async function(){
            this.cedulaPersona = $("#cedulaAgregar").val();
            this.nombrePersona = $("#nombreAgregar").val();
            this.apellidoPersona = $("#apellidoAgregar").val();
            this.correoPersona = $("#correoAgregar").val();
            this.telefonoPersona = $("#telefonoAgregar").val();
            this.contrasenaUsuario = $("#contrasenaAgregar").val();
            this.idRol = $("#rolAgregar option:selected").val();

            // Si el formato de la contraseña no es correcto saldra un mensaje y no se enviarán los datos
            if (!this.validarContrasena(this.contrasenaUsuario)) {
                Swal.fire({
                    title: 'Estructura de Contraseña Erronea!',
                    text: 'La contraseña debe contener al menos una letra mayúscula, una letra minúscula, un número y un carácter especial.',
                    icon: 'error',
                });
                return;
            }

            $("#exampleModal").modal("hide");
            axios.post(url, {
                opcion:"crear",
                cedulaPersona: this.cedulaPersona,
                nombrePersona: this.nombrePersona,
                apellidoPersona: this.apellidoPersona,
                correoPersona: this.correoPersona,
                telefonoPersona: this.telefonoPersona,
                contrasenaUsuario: this.contrasenaUsuario,
                idRol: this.idRol,
            }).then(response =>{
                this.listar();
                $("#rolAgregar").val("");
                $("#cedulaAgregar").val("");
                $("#nombreAgregar").val("");
                $("#apellidoAgregar").val("");
                $("#correoAgregar").val("");
                $("#telefonoAgregar").val("");
                $("#contrasenaAgregar").val("");
                this.cedulaPersona = "";
                this.nombrePersona = "";
                this.apellidoPersona = "";
                this.correoPersona = "";
                this.telefonoPersona = "";
                this.contrasenaUsuario = "";
                this.idRol = "";
                Swal.fire({
                    title: 'Usuario Agregado!',
                    text: 'Se ingreso exitosamente el nuevo usuario',
                    icon: 'success',
                });
            });
        },

        //Cargar modal editar
         btnCargarModalEditar: async function(idPersona,cedulaPersona, nombrePersona, apellidoPersona, telefonoPersona, correoPersona, idRol){
            $("#idEditar").val(idPersona);
            $("#cedulaEditar").val(cedulaPersona);
            $("#nombreEditar").val(nombrePersona);
            $("#apellidoEditar").val(apellidoPersona);
            $("#telefonoEditar").val(telefonoPersona);
            $("#correoEditar").val(correoPersona);
            $("#rolEditar").val(idRol);
            $("#ModalEditar").modal("show");
        },
        //editar los datos
        btnEditar: async function(){
            this.idPersona = $("#idEditar").val();
            this.nombrePersona = $("#nombreEditar").val();
            this.apellidoPersona = $("#apellidoEditar").val();
            this.telefonoPersona = $("#telefonoEditar").val();
            this.correoPersona = $("#correoEditar").val();
            this.idRol = $("#rolEditar option:selected").val();
            axios.post(url, {
                opcion:"editar",
                idPersona:this.idPersona,
                nombrePersona:this.nombrePersona,
                apellidoPersona: this.apellidoPersona,
                telefonoPersona: this.telefonoPersona,
                correoPersona: this.correoPersona,
                idRol: this.idRol,
            }).then(response => {
                this.listar();
                this.idPersona = "";
                this.nombrePersona = "";
                this.apellidoPersona = "";
                this.correoPersona = "";
                this.telefonoPersona = "";
                this.idRol = "";
                $("#ModalEditar").modal("hide");
                Swal.fire({
                    title: 'Datos Actualizados!',
                    text: 'Se actualizo exitosamente los datos del usuario',
                    icon: 'success',
                });
            });
        },

        //Cargar editar contraseña
        btnCargarModalEditarContra: async function(idPersona,nombreUsuario){
            $("#idEditarContrasena").val(idPersona);
            $("#nombreUsuarioContrasena").val(nombreUsuario);
            $("#ModalEditarContrasena").modal("show");
        },
        //editar contraseña
        btnEditarContrasena: async function(){
            this.idPersona = $("#idEditarContrasena").val();
            this.contrasenaUsuario = $("#contrasenaEditar").val();
            this.confirmacionContrasena = $("#confirmacionContrasenaEditar").val();

            // Si el formato de la contraseña no es correcto saldra un mensaje y no se enviarán los datos
            if (!this.validarContrasena(this.contrasenaUsuario)) {
                Swal.fire({
                    title: 'Estructura de Contraseña Erronea!',
                    text: 'La contraseña debe contener al menos una letra mayúscula, una letra minúscula, un número y un carácter especial.',
                    icon: 'error',
                });
                return;
            }

            // Si la contraseña ingresada y la confirmación son iguales se cambiará la contraseña, caso contrario se mostrará un mensaje de error y no se enviarán los datos
            if(this.contrasenaUsuario === this.confirmacionContrasena){
                axios.post(url, {
                    opcion:"editarContrasena",
                    idPersona:this.idPersona,
                    contrasenaUsuario:this.contrasenaUsuario,
                }
                ).then(response => {
                    this.listar();
                    this.idPersona = "";
                    this.contrasenaUsuario = "";
                    this.confirmacionContrasena = "";
                    $("#contrasenaEditar").val("");
                    $("#confirmacionContrasenaEditar").val("");
                    $("#ModalEditarContrasena").modal("hide");
                    Swal.fire({
                        title: 'Contraseña Actualizada!',
                        text: 'Se actualizo exitosamente la contraseña del usuario',
                        icon: 'success',
                    });
                });
            }else{
                Swal.fire({
                    title: 'Error!',
                    text: 'No coinciden las contraseñas',
                    icon: 'error',
                });
            }
        },

        //Cargar modal eliminar
        btnCargarModalEliminar: async function(idUsuario, nombreCompleto){
            $("#idEliminar").val(idUsuario);
            $("#nombreEliminar").text(nombreCompleto);
            $("#ModalEliminar").modal("show");
        },

        //Eliminar personal
        btnEliminar: async function(){
            this.idPersona = $("#idEliminar").val();
            $("#ModalEliminar").modal("hide");
            axios.post(url, {
                opcion: "eliminar",
                idUsuario: this.idPersona
            }).then(response => {
                /* Comprobar que el usuario no tenga asociado una unidad de transporte activa para poder dar de baja caso contrario se mostrará un mensaje de error */
                if (response.data == "Error: Primero debe dar de baja el bus"){
                    Swal.fire({
                        title: 'Error de Acción!',
                        text: 'Primero debe dar de baja el bus asociado al usuario',
                        icon: 'error',
                    });
                }else{
                    this.listar();
                    this.listarBajas();
                    Swal.fire({
                        title: 'Usuario Dado de Baja!',
                        text: 'Se realizo exitosamente la operación',
                        icon: 'success',
                    });
                }
            });
        },

        /* Mostrar modal de bajas */
        btnCargarBajas: async function(){
            $("#ModalBaja").modal("show");
        },

        //Listar el personal dado de baja en modal
        listarBajas:function(){
            axios.post(url, {opcion:"listarBajas"}).then(response =>{
                this.lisBaja = response.data;
            });
        },

        /* Dar de alta a usuarios */
        btnDarAlta: async function(idUsuario){
            axios.post(url, {
                opcion:"darAlta",
                idUsuario: idUsuario,
            }).then(response =>{
                this.listar();
                this.listarBajas();
            });
        },

        /* Método validador de contraseñas Mayusculas, minusculas, numeros y caracteres especiales */
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
                $("#contrasenaAgregar").removeClass("is-invalid");
            }else if($("#contrasenaEditar").hasClass("is-valid")){
                $("#contrasenaEditar").removeClass("is-valid");
                $("#contrasenaAgregar").removeClass("is-valid");
            }

            //Eliminar Estilos
            if($("#contrasenaEditar").prop("style")){
                $("#contrasenaEditar").removeAttr("style");
                $("#contrasenaAgregar").removeAttr("style");
            }

            $("#confirmacionContrasenaEditar").val("");
            
        },

        /* Borrar contenido y estilos de imputs del modal agregar */
        BorrContAgregar: function(){
            
            $("#cedulaAgregar").val("");
            $("#nombreAgregar").val("");
            $("#apellidoAgregar").val("");
            $("#telefonoAgregar").val("");
            $("#correoAgregar").val("");
            $("#contrasenaAgregar").val("");
            $("#rolAgregar").val("");

            //Eliminar clases
            if($("#contrasenaAgregar").hasClass("is-invalid")){
                $("#contrasenaEditar").removeClass("is-invalid");
                $("#contrasenaAgregar").removeClass("is-invalid");
            }else if($("#contrasenaAgregar").hasClass("is-valid")){
                $("#contrasenaEditar").removeClass("is-valid");
                $("#contrasenaAgregar").removeClass("is-valid");
            }

            //Eliminar Estilos
            if($("#contrasenaAgregar").prop("style")){
                $("#contrasenaEditar").removeAttr("style");
                $("#contrasenaAgregar").removeAttr("style");
            }

        },
        
    },
    created: function(){
        this.listar();
        this.listadoRol();
        this.listarBajas();
    }
});