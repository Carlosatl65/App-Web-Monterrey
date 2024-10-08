var url = "BD/Administrador/ordenRancheras.php"; /* Ruta de archivo php */
var Inventario = new Vue({
    el:'#Lista', /* Nombre de contenedor en vista */
    data:{
        listRancheras: [],
        isReorganizing: false,
        sortableInstance: null,
    },
    methods:{
        
        //Listar unicamente las rancheras
        listar:function(){
            axios.post(url, {opcion:"listar"}).then(response =>{
                this.listRancheras = response.data;
            });
        },        
        
        /* Para activar el modo de edición primero se muestra un mensaje de alerta, en caso de continuar la lista podrá ser organizada arrastrando los elementos a la posición deseada */
        reorganizar: function(){
            Swal.fire({
                title: "Estas seguro de activar la reorganización?",
                text: "La reorganización afectará las asignaciones diarias",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Habilitar Opción"
              }).then((result) => {
                if (result.isConfirmed) {
                    this.isReorganizing = true; //Se activa el indicador de reorganización
                    $('#reorganizar-btn').hide(); //Se esconde el botón activador
                    $('#aceptar-btn, #cancelar-btn').show(); //Se muestran los botones de confirmar o cancelar la reorganización
                    const ulElement = $('#lista-buses-reorganizable')[0];
                    /* Se activan los items li para ser arrastrados y organizados */
                    this.sortableInstance  = new Sortable.default(ulElement,{
                        draggable: 'li'
                    });
                }
              });
        },

        /* Si se aceptan los cambios se crea una Array con el nuevo orden y se pasa a la base de datos */
        aceptarCambios: function() {
            const tareas = $(".tarea");
            const sortedData = new Array();

            [...tareas].forEach((tarea, index)=>{
                sortedData.push({
                    id: tarea.getAttribute('data-id'),
                    orden: (index+1)
                });
            });

            axios.post(url, {
                opcion:"ordenLista",
                sortedData: sortedData,
            }).then(response =>{
                /* Se desactivan los elementos para poder ser arrastrados y organizados */
                if (this.sortableInstance !== null) {
                    this.sortableInstance.destroy();
                    this.sortableInstance = null;
                }
                this.isReorganizing = false; // indicador de organización en falso
                $('#reorganizar-btn').show(); // mostrar el botón para activar la reorganización
                $('#aceptar-btn, #cancelar-btn').hide(); // ocultar los botones de aceptar o cancelar cambios
                Swal.fire({
                    title: "Lista Guardada!",
                    text: "El nuevo orden de rancheras se guardó exitosamente",
                    icon: "success"
                });
            });
            
        },

        /* Cancelar cambios hechos en reorganización */
        cancelarCambios() {
            /* Se desactivan los elementos para poder ser arrastrados y organizados */
            if (this.sortableInstance !== null) {
                this.sortableInstance.destroy();
                this.sortableInstance = null;
            }
            this.isReorganizing = false; // indicador de organización en falso
            $('#reorganizar-btn').show(); // mostrar el botón para activar la reorganización
            $('#aceptar-btn, #cancelar-btn').hide(); // ocultar los botones de aceptar o cancelar cambios
            //Restaurar la lista original desde base de datos
            this.listRancheras = "";
            this.listar();
        }

    },
    created: function(){
        this.listar();
    }
});