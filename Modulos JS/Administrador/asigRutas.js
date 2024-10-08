var url = "BD/Administrador/asigRutas.php";
var Inventario = new Vue({
    el: '#TablasAsigRutas',
    data: {
        AsigTabla: [],
        lisSocios: [],
        lisTabla: [],
        visPrevia: [],
        tipoFiltro: "",
        nombreTabla: "",
        fecha: "",
        idGeneracion: "",
        fechaFormateada: "", // Para mostrar la fecha formateada en el HTML
        datosConfirmados: false, //Almacenar el estado de confirmación
        mostrarBtn: false, // Botón mostrado para confirmar rutas
        manualBtn: false, // Botón mostrado cuando se cambia a asignación manual
    },
    methods: {
        
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
                
        // Función para formatear la fecha
        formatearFecha: function(fecha) {
            const diasSemana = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
            const meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

            const diaSemana = diasSemana[fecha.getDay()];
            const dia = fecha.getDate();
            const mes = meses[fecha.getMonth()];
            const año = fecha.getFullYear();

            return `${diaSemana}, ${dia} de ${mes} del ${año}`;
        },

        // Listar tabla
        listar: function() {
            this.tipoFiltro = $("#Filtro option:selected").val();
            this.nombreTabla = $("#Filtro option:selected").text().toUpperCase();
            const fechaActual = new Date();
            let fechaParaEnviar;

            // Comprobar la hora actual y ajustar la fecha en consecuencia
            const horaActual = fechaActual.getHours();
            if (horaActual >= 19) {
                // Si la hora es 19:00 o más tarde, usar el día siguiente
                fechaParaEnviar = new Date(fechaActual);
                fechaParaEnviar.setDate(fechaActual.getDate() + 1);
            } else {
                // Si la hora es antes de 19:00, usar la fecha actual
                fechaParaEnviar = fechaActual;
            }

            this.fecha = this.formatearFechaSQL(fechaParaEnviar); // Formato SQL para enviar al servidor
            this.fechaFormateada = this.formatearFecha(fechaParaEnviar); // Formato con días de la semana para mostrar en HTML

            /* Enviar a consultar si existen rutas confirmadas o por confirmar */
            axios.post(url, {
                opcion: "listar",
                tipoFiltro: this.tipoFiltro,
                fecha: this.fecha,
                idGeneracion: 2 //Id de rutas confirmadas
            }).then(response => {
                if(response.data.length > 0){ //Si existen rutas confirmadas guardar en contenedor a mostrar
                    this.AsigTabla = response.data;
                    this.datosConfirmados = true;
                }else{
                    axios.post(url, {
                        opcion: "listar",
                        tipoFiltro: this.tipoFiltro,
                        fecha: this.fecha,
                        idGeneracion: 1 //Id de rutas por confirmar
                    }).then(response2 => {
                        this.AsigTabla = response2.data; // Guardar rutas por confirmar y mostrar en la vista
                        this.mostrarBtn = response2.data.length > 0; //Si existen rutas se activa el botón sino se mantiene oculto
                        this.datosConfirmados = response2.data.length > 0 && response2.data[0].confirmacion == 1; //Verificacion que las rutas no esten confirmadas
                    });
                }
            });
        },

        // Formatear fecha para SQL (YYYY-MM-DD)
        formatearFechaSQL: function(fecha) {
            const año = fecha.getFullYear();
            const mes = ('0' + (fecha.getMonth() + 1)).slice(-2);
            const día = ('0' + fecha.getDate()).slice(-2);
            return `${año}-${mes}-${día}`;
        },

        //Listado de Socios en cada celda de tabla
        listaSocios: async function(){
            axios.post(url, {opcion:"lstSocios"}).then(response =>{
                this.lisSocios = response.data;
            });
        },

        // Confirmar las rutas
        confirmarAsignaciones: function(){
            axios.post(url, {
                opcion: "confirmarRuta",
                asignaciones: this.AsigTabla,
            }).then(response => {
                this.listar();
            });
        },

        // Vista Previa de asignaciones de rutas
        btnVistaPrevia: async function(){
            
            // Ejecutar el primer script PHP
            axios.get('BD/Administrador/vistaPreSDia.php').then(response1 => {
                // Ejecutar el segundo script PHP con axios y obtener datos para el PDF
                axios.post(url, {
                    opcion: "datosVPrevia",
                }).then(response2 => {
                    if(response2.data){
                        this.visPrevia = response2.data;
                        
                        // Generar el PDF con los datos obtenidos
                        const { jsPDF } = window.jspdf;
                        const doc = new jsPDF('p','mm','a4');

                        doc.setFontSize(16);
                        doc.setFont('helvetica','bold');
                        doc.text("Vista Previa de Asignaciones", doc.internal.pageSize.getWidth() / 2, 10, {align: 'center'});
                        doc.setFontSize(12);
                        doc.text(`Fecha: `+ this.visPrevia[0]['fechaAsignacionRuta'], 10, 20);
                                            
                        // Configurar las columnas de la tabla
                        const columns = [
                            { title: "Iniciales Socio", dataKey: "inicialesSocio" },
                            { title: "Col Izquierda", dataKey: "colIzquierda" },
                            { title: "Col Derecha", dataKey: "colDerecha" },
                            { title: "Nombre Tabla", dataKey: "nombreTabla" }
                        ];

                        // Agregar la tabla al PDF
                        doc.autoTable({
                            head: [columns.map(col => col.title)],
                            body: this.visPrevia.map(row => columns.map(col => row[col.dataKey])),
                            startY: 30,
                            theme: 'striped'
                        });

                        // Obtener el número total de páginas
                        const totalPages = doc.internal.getNumberOfPages();

                        // Agregar marca de agua a cada página
                        for (let i = 1; i <= totalPages; i++) {
                            doc.setPage(i);
                            doc.setTextColor(0, 0, 0, 0.15); // Color claro
                            doc.setFontSize(50);
                            
    
                            // Calcular las coordenadas para la marca de agua
                            const pageWidth = doc.internal.pageSize.getWidth();
                            const pageHeight = doc.internal.pageSize.getHeight();
                            const text = "Vista Previa\nRutas no confirmadas";
    
                            // Agregar la marca de agua en el centro de la página
                            doc.saveGraphicsState();
                            doc.setGState(new doc.GState({opacity: 0.5}));
                            doc.text(text, pageWidth / 2, pageHeight / 2, {align: 'center', baseline: 'middle'})
                            doc.restoreGraphicsState();
                            

                        }                          

                        // Output del PDF como una cadena de bytes
                        const pdfOutput = doc.output('blob');

                        // Crear un enlace para abrir el PDF en una nueva pestaña
                        const link = document.createElement('a');
                        link.href = URL.createObjectURL(pdfOutput);
                        link.target = '_blank';
                        /* link.download = 'vista_previa.pdf'; */ // Opcional, si se desea que se descargue con un nombre específico

                        // Abrir el enlace en una nueva pestaña
                        link.click();

                    }else{
                        console.log("No se recibio información del servidor");
                    }

                    /* Una vez se crea el PDF se borra los datos de la BD calculados */
                    axios.post(url, {
                        opcion: "borrarDatosVPrevia"
                    }).then(response3 => {});

                }).catch(error => {
                    console.error("Error al ejecutar el segundo paso: ", error);
                });
            }).catch(error => {
                console.error("Error al ejecutar el primer script PHP:", error);
            });
        },

        /* Mensaje de advertencia para la Edición manual de asignación de rutas */
        btnEditManual: function(){
            Swal.fire({
                title: "Edición Manual",
                text: "¿Seguro desea editar manualmente las asignaciones del día?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Entrar Modo Manual"
              }).then((result) => {
                if (result.isConfirmed) {
                  this.datosConfirmados = false;
                  this.manualBtn = true;
                }
              });
        },

        /* Edición manual de asignación de rutas */
        confEditManual: function(){
            axios.post(url, {
                opcion: "editManual",
                asignaciones: this.AsigTabla,
            }).then(response => {
                this.listar();
                this.manualBtn = false;
                Swal.fire({
                    title: "Rutas Modificadas!",
                    text: "Se realizó la modificación exitosamente",
                    icon: "success"
                  });
            });
        },

    },
    created: function() {
        this.listadoTablas();
        this.listaSocios();
        this.listar();
    }
});
