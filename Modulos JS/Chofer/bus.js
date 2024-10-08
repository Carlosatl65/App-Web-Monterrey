var url = "BD/Chofer/bus.php"; /* Ruta de archivo php */
var Bus = new Vue({
    el:'#busSocio', /* Nombre de contenedor en vista */
    data:{
        Bus: [],
        idBus: "",
        idEstadoBus: "",
    },
    methods:{
        
        //Listar buses en tabla
        listar:function(){
            axios.post(url, {opcion:"listar"}).then(response =>{
                this.Bus = response.data;
            });
        },
        
        //Cargar editar estado bus
        btnCargarModalCambiarEstado: async function(idBus, numeroBus, idEstado){
            $("#idBusEstadoEditar").val(idBus);
            $("#numeroEstadoEditar").val(numeroBus);
            $("#estadoEditar").val(idEstado);
            $("#ModalEstadoBus").modal("show");
        },

        //Editar estado bus
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
    }
});