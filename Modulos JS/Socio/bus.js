var url = "BD/Socio/bus.php"; /* Ruta de archivo php */
var Bus = new Vue({
    el:'#busSocio', /* Nombre de contenedor en vista */
    data:{
        Bus: [],
        lisChoferes: [],
        idBus: "",
        idChofer: "",
        idEstadoBus: "",
    },
    methods:{
        
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
        },
        
        //Cargar modal editar estado bus
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
        
        
        //Cargar modal editar chofer
         btnCargarModalCambiarChofer: async function(idAsignacionBus, idChofer, numeroBus){
            $("#idAsignacionBus").val(idAsignacionBus);
            $("#choferEditar").val(idChofer);
            $("#numeroEditar").val(numeroBus);
            $("#ModalEditar").modal("show");
        },

        //Editar chofer asignado
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

        /* Formato de fecha SQL para ingresar en base de datos */
        formatearFechaSQL: function(fecha) {
            const año = fecha.getFullYear();
            const mes = ('0' + (fecha.getMonth() + 1)).slice(-2);
            const día = ('0' + fecha.getDate()).slice(-2);
            return `${año}-${mes}-${día}`;
        },
        
    },
    created: function(){
        this.listar();
        this.listadoChoferes();
        
    }
});