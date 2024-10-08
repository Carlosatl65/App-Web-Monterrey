var url = "BD/Administrador/crudRutas.php"; /* Ruta de archivo php */
var Inventario = new Vue({
    el:'#TablaRutas', /* Nombre de contenedor en vista */
    data:{
        Rutas: [],
        lisTabla: [],
        lisBaja: [],
        Socios: [],
        idRuta: "",
        colIzquierda: "",
        colDerecha: "",
        Tabla: "",
        tipoFiltro: "",
        filtroDias: "",
        idDia: "",
        idUsuario: "",
        entrenamientoAlgoritmo: false,
    },
    methods:{
        
        //Listado Socios
        listadoSocios: function(){
            axios.post(url, {opcion:"lstSocios"}).then(response =>{
                this.Socios = response.data;
            });
        },

        // ID de Listado Tablas
        setIdSocios(opc){
            for(var i=0; i<this.Socios.length; i++){
                if(opc == this.Socios[i]){
                    return this.Socios[i].idUsuario;
                }
            }
        },

        //Listado Tablas
        listadoTablas: function(){
            axios.post(url, {opcion:"lstTablas"}).then(response =>{
                this.lisTabla = response.data;
            });
        },

        // ID de Listado Tablas
        setIdTabla(opc){
            for(var i=0; i<this.lisTabla.length; i++){
                if(opc == this.lisTabla[i]){
                    return this.lisTabla[i].idTabla;
                }
            }
        },

        //Listar el rutas en tabla según filtros por días y sectores
        listar:function(){
            this.tipoFiltro = $("#Filtro option:selected").val();
            this.filtroDias = $("#FiltroDias option:selected").val();
            axios.post(url, {
                opcion:"listar",
                tipoFiltro: this.tipoFiltro,
                filtroDias: this.filtroDias,
            }).then(response =>{
                this.Rutas = response.data;
            });
            $("#buscar").val("");
        },

        //Buscar rutas dentro de filtro actual
        btnBuscar:function(){
            dato = $("#buscar").val();
            this.tipoFiltro = $("#Filtro option:selected").val();
            this.filtroDias = $("#FiltroDias option:selected").val();
            axios.post(url, {
                opcion:"buscar",
                buscar:dato,
                tipoFiltro: this.tipoFiltro,
                filtroDias: this.filtroDias,
            }).then(response =>{
                this.Rutas = response.data;
            });
        },

        //Agregar ruta
        btnAgregar: async function(){
            this.colIzquierda = $("#colIzquierdaAgregar").val();
            // Si la columan derecha esta vacía su valor será null
            if ($("#colDerechaAgregar").val().length != 0) {
                this.colDerecha = $("#colDerechaAgregar").val();
            }else{
                this.colDerecha = null;
            }
            this.Tabla = $("#tablaAsignar option:selected").val();
            this.idDia = $("#idDia option:selected").val();

            $("#exampleModal").modal("hide");

            // Si se agrega una ruta de 'lunes a viernes' se establece el socio para tener la base del algoritmo para asignaciones automáticas
            if(this.idDia == 1){
                this.idUsuario = $("#Socios option:selected").val();

                axios.post(url, {
                    opcion:"crear",
                    colIzquierda: this.colIzquierda,
                    colDerecha: this.colDerecha,
                    Tabla: this.Tabla,
                    idDia: this.idDia,
                    idUsuario: this.idUsuario,
                }).then(response =>{
                    console.log(response);
                    this.listar();
                    $("#tablaAsignar").val("");
                    $("#colIzquierdaAgregar").val("");
                    $("#colDerechaAgregar").val("");
                    $("#idDia").val("");
                    $("#Socios").val("");
                    this.colDerecha = "";
                    this.colIzquierda = "";
                    this.Tabla = "";
                    this.idDia = "";
                    this.idUsuario = "";
                    this.entrenamientoAlgoritmo = false;
                    Swal.fire({
                        title: 'Ruta Agregada!',
                        text: 'Se ingreso exitosamente la nueva ruta',
                        icon: 'success',
                    });
                });
            }else{
                // Si se agrega una ruta 'sabado' o 'domingo' solo se agrega los datos de la ruta
                axios.post(url, {
                    opcion:"crear",
                    colIzquierda: this.colIzquierda,
                    colDerecha: this.colDerecha,
                    Tabla: this.Tabla,
                    idDia: this.idDia,
                }).then(response =>{
                    this.listar();
                    $("#tablaAsignar").val("");
                    $("#colIzquierdaAgregar").val("");
                    $("#colDerechaAgregar").val("");
                    $("#idDia").val("");
                    this.colDerecha = "";
                    this.colIzquierda = "";
                    this.Tabla = "";
                    this.idDia = "";
                    this.entrenamientoAlgoritmo = false;
                    Swal.fire({
                        title: 'Ruta Agregada!',
                        text: 'Se ingreso exitosamente la nueva ruta',
                        icon: 'success',
                    });
                });
            }
        },
        
        //Cargar modal eliminar
        btnCargarModalEliminar: async function(idRuta, colIzquierda, colDerecha, nombreTabla){
            $("#idEliminar").text(idRuta);
            $("#nombreEliminar").text(colIzquierda + " - " + colDerecha + " de la tabla" + nombreTabla);
            $("#ModalEliminar").modal("show");
        },
        //Eliminar ruta
        btnEliminar: async function(){
            this.idRuta = $("#idEliminar").text();
            $("#ModalEliminar").modal("hide");
            axios.post(url, {
                opcion: "eliminar",
                idRuta: this.idRuta
            }).then(response => {
                this.listar();
                this.listarBajas();
                Swal.fire({
                    title: 'Ruta Dada de Baja!',
                    text: 'Se realizo exitosamente la operación',
                    icon: 'success',
                });
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

        /* Dar de alta rutas */
        btnDarAlta: async function(idRuta, idDia){
            /* Si se da de alta una ruta de 'lunes a viernes' se muestra un modal para seleccionar el socio base para el algoritmo para asignaciones automáticas */
            if(idDia == 1){
                let optionsHtml = '';
                this.Socios.forEach(socio => {
                    optionsHtml += `<option value="${socio.idUsuario}">${socio.nombre}</option>`;
                });
                Swal.fire({
                    title: 'Ingresar Socio para Asignación',
                    html: `
                        <select class="form-select" aria-label="Default select example" id="asiSocio">
                            ${optionsHtml}
                        </select>
                        `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Confirmar',
                    cancelButtonText: 'Cancelar',
                }).then((result) => {
                    if (result.isConfirmed) {
                    const idSocio = document.getElementById('asiSocio').value;
                    if (idSocio) {
                        axios.post(url, {
                        opcion: "darAlta",
                        idRuta: idRuta,
                        idUsuario: idSocio,
                        }).then(response => {
                        this.listar();
                        this.listarBajas();
                        });
                    } else {
                        Swal.fire({
                        title: 'Error',
                        text: 'Debe seleccionar un socio',
                        icon: 'error',
                        });
                    }
                    }
                });
            }else{
                /* Si se da de alta rutas de 'sabado' o 'domingo' solo se da de alta en el sistema */
                axios.post(url, {
                    opcion:"darAlta",
                    idRuta: idRuta,
                }).then(response =>{
                    this.listar();
                    this.listarBajas();
                });
            }
        },

        /* Borrar contenido de imputs del modal agregar */
        BorrContAgregar: function(){
            $("#colIzquierdaAgregar").val("");
            $("#colDerechaAgregar").val("");
            $("#idDia").val("");
            $("#tablaAsignar").val("");
            this.entrenamientoAlgoritmo = false;
        },

        /* Mostrar seccion en modal agregar para tener el socio base para algoritmo de asignación automática */
        mostrarSocios: function(){
            var seleccion = $("#idDia option:selected").val();
            if(seleccion == 1){
                this.entrenamientoAlgoritmo = true;
            }else{
                this.entrenamientoAlgoritmo = false;
            }
           
        }
        
    },
    created: function(){
        this.listar();
        this.listadoTablas();
        this.listarBajas();
        this.listadoSocios();
    }
});