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



var url = "BD/Administrador/crudBuses.php"; /* Ruta de archivo php */
var Inventario = new Vue({
    el:'#TablaBuses', /* Nombre de contenedor en vista */
    data:{
        Bus: [],
        lisPropietarios: [],
        lisChoferes: [],
        lisTipoTransporte: [],
        lisBaja: [],
        tipoTransporteAgregar: "",
        idBus: "",
        placaBus: "",
        numeroBus: "",
        anioBus: "",
        capacidadBus: "",
        idPropietario: "",
        idChofer: "",
        idAsignacionBus: "",
        fecha: "",
        idEstadoBus: "",
    },
    methods:{
        
        //Listado Tipo de Transporte
        listadoTipoTransporte: function(){
            axios.post(url, {opcion:"lstTipoTransporte"}).then(response =>{
                this.lisTipoTransporte = response.data;
            });
        },

        // ID DE tipo de transporte
        setIdTipoTransporte(opc){
            for(var i=0; i<this.lisTipoTransporte.length; i++){
                if(opc == this.lisTipoTransporte[i]){
                    return this.lisTipoTransporte[i].idTipoUnidad;
                }
            }
        },

        //Listado Propietarios
        listadoPropietarios: function(){
            axios.post(url, {opcion:"lstPropietarios"}).then(response =>{
                this.lisPropietarios = response.data;
            });
        },

        // ID DE Propietarios
        setIdPropietario(opc){
            for(var i=0; i<this.lisPropietarios.length; i++){
                if(opc == this.lisPropietarios[i]){
                    return this.lisPropietarios[i].idUsuario;
                }
            }
        },

        //Listado Choferes
        listadoChoferes: function(){
            axios.post(url, {opcion:"lstChoferes"}).then(response =>{
                this.lisChoferes = response.data;
            });
        },

        // ID DE Choferes
        setIdChofer(opc){
            for(var i=0; i<this.lisChoferes.length; i++){
                if(opc == this.lisChoferes[i]){
                    return this.lisChoferes[i].idUsuario;
                }
            }
        },


        //Listar buses en tabla
        listar:function(){
            axios.post(url, {opcion:"listar"}).then(response =>{
                this.Bus = response.data;
            });
            $("#buscar").val("");
        },
        
        //Buscar
        btnBuscar:function(){
            dato = $("#buscar").val();
            axios.post(url, {opcion:"buscar", buscar:dato}).then(response =>{
                this.Bus = response.data;
            });
        },

        //agregar
        btnAgregar: async function(){
            this.tipoTransporteAgregar = $("#tipoTransporteAgregar option:selected").val();
            this.placaBus = $("#placaAgregar").val();
            this.numeroBus = $("#numeroAgregar").val();
            this.anioBus = $("#anioAgregar").val();
            this.capacidadBus = $("#capacidadAgregar").val();
            this.idPropietario = $("#propietarioAgregar option:selected").val();
            this.idChofer = $("#choferAgregar option:selected").val();
            this.fecha = this.formatearFechaSQL(new Date());
            $("#ModalAgregar").modal("hide");
            axios.post(url, {
                opcion:"crear",
                placaBus: this.placaBus,
                numeroBus: this.numeroBus,
                anioBus: this.anioBus,
                capacidadBus: this.capacidadBus,
                idPropietario: this.idPropietario,
                idChofer: this.idChofer,
                fechaAsignacion: this.fecha,
                tipoTransporteAgregar: this.tipoTransporteAgregar,
            }).then(response =>{
                this.listar();
                $("#placaAgregar").val("");
                $("#numeroAgregar").val("");
                $("#anioAgregar").val("");
                $("#capacidadAgregar").val("");
                $("#propietarioAgregar").val("");
                $("#choferAgregar").val("");
                $("#tipoTransporteAgregar").val("");
                this.placaBus = "";
                this.numeroBus = "";
                this.anioBus = "";
                this.capacidadBus = "";
                this.idPropietario = "";
                this.idChofer = "";
                this.fecha = "";
                this.tipoTransporteAgregar = "";
                Swal.fire({
                    title: 'Unidad Agregada!',
                    text: 'Se ingreso exitosamente la nueva unidad de transporte',
                    icon: 'success',
                });
            });
        },
        
        //Cargar editar estado bus
        btnCargarModalCambiarEstado: async function(idBus, numeroBus, idEstado){
            $("#idBusEstadoEditar").val(idBus);
            $("#numeroEstadoEditar").val(numeroBus);
            $("#estadoEditar").val(idEstado);
            $("#ModalEstadoBus").modal("show");
        },

        //editar estado bus
        btnEditarEstado: async function(){
            this.idBus = $("#idBusEstadoEditar").val();
            this.idEstadoBus = $("#estadoEditar option:selected").val();
            axios.post(url, {
                opcion:"editarEstadoBus",
                idBus: this.idBus,
                idEstadoBus: this.idEstadoBus,
            }).then(response => {
                this.listar();
                this.idBus = "";
                this.idEstadoBus = "";
                $("#estadoEditar").val("def");
                $("#idBusEstadoEditar").val("");
                $("#ModalEstadoBus").modal("hide");
                Swal.fire({
                    title: 'Estado Modificado!',
                    text: 'Se realizo exitosamente el cambio',
                    icon: 'success',
                });
            });
        },
        
        
        //Cargar editar chofer
       
         btnCargarModalCambiarChofer: async function(idAsignacionBus, idChofer, numeroBus){
            $("#idAsignacionBus").val(idAsignacionBus);
            $("#choferEditar").val(idChofer);
            $("#numeroEditar").val(numeroBus);
            $("#ModalEditar").modal("show");
        },

        //Editar chofer
        btnEditar: async function(){
            this.idAsignacionBus = $("#idAsignacionBus").val();
            this.idChofer = $("#choferEditar option:selected").val();
            this.fecha = this.formatearFechaSQL(new Date());
            axios.post(url, {
                opcion:"editarChofer",
                idAsignacionBus: this.idAsignacionBus,
                idChofer: this.idChofer,
                fechaAsignacion: this.fecha,
            }).then(response => {
                this.listar();
                this.idAsignacionBus = "";
                this.idChofer = "";
                this.fecha = "";
                $("#ModalEditar").modal("hide");
                Swal.fire({
                    title: 'Chofer Asignado!',
                    text: 'Se realizo exitosamente el cambio',
                    icon: 'success',
                });
            });
        },
        
        //Cargar eliminar
        btnCargarModalEliminar: async function(idBus, placaEliminar){
            $("#idEliminar").text(idBus);
            $("#placaEliminar").text(placaEliminar);
            $("#ModalEliminar").modal("show");
        },

        //Eliminar unidad
        btnEliminar: async function(){
            this.idBus = $("#idEliminar").text();
            $("#ModalEliminar").modal("hide");
            axios.post(url, {
                opcion: "eliminar",
                idBus: this.idBus
            }).then(response => {
                this.listar();
                this.listarBajas();
                Swal.fire({
                    title: 'Unidad Dada de Baja!',
                    text: 'Se realizo exitosamente la operación',
                    icon: 'success',
                });
            });
        },

        /* Formato de fecha SQL para ingresar en base de datos */
        formatearFechaSQL: function(fecha) {
            const año = fecha.getFullYear();
            const mes = ('0' + (fecha.getMonth() + 1)).slice(-2);
            const día = ('0' + fecha.getDate()).slice(-2);
            return `${año}-${mes}-${día}`;
        },

        /* Mostrar modal con unidades dadas de baja */
        btnCargarBajas: async function(){
            $("#ModalBaja").modal("show");
        },

        //Listar el personal dado de baja en modal
        listarBajas:function(){
            axios.post(url, {opcion:"listarBajas"}).then(response =>{
                this.lisBaja = response.data;
            });
        },

        /* Dar de alta una unidad */
        btnDarAlta: async function(idBus){
            axios.post(url, {
                opcion:"darAlta",
                idBus: idBus,
            }).then(response =>{
                this.listar();
                this.listarBajas();
            });
        },

        /* Dejar en blanco los imputs del modal agregar */
        BorrContAgregar: function(){
            $("#tipoTransporteAgregar").val("");
            $("#placaAgregar").val("");
            $("#numeroAgregar").val("");
            $("#anioAgregar").val("");
            $("#capacidadAgregar").val("");
            $("#propietarioAgregar").val("");
            $("#choferAgregar").val("");
        },
        
    },
    created: function(){
        this.listar();
        this.listadoPropietarios();
        this.listadoChoferes();
        this.listadoTipoTransporte();
        this.listarBajas();
    }
});