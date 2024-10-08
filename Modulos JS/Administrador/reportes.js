var url = "BD/Administrador/reportes.php"; /* Ruta de archivo php */
var Reportes = new Vue({
    el: '#ReportesAsig', /* Nombre de contenedor en vista */
    data: {
        AsigTabla: [],
        lisDatos: [],
        fecha: "",
        fechaForm: "", //Fecha que el usuario ingresa
    },
    methods: {
        
        /* Método para realizar un pdf con el reporte de fecha específica */
        btnVistaPrevia: async function(){
            const fechaActual = new Date().toLocaleDateString('es', { weekday:"long", year:"numeric", month:"short", day:"numeric"}) 
            /* Preparar la variable de pdf y fecha ingresada */
            const pdfViewer = $("#pdf-viewer"); 
            this.fechaForm = $("#fechaBuscar").val();
            /* Validar que se haya ingresado una fecha para la generaciòn del reporte */
            if($("#fechaBuscar").val().length > 0){
                // Generar el PDF
                const { jsPDF } = window.jspdf;
                const doc = new jsPDF('p','mm','a4');
                /* Cabecera del documento PDF */
                doc.setFontSize(16);
                doc.setFont('helvetica','bold');
                doc.text('Cooperativa de Transporte de Pasajeros\n"Monterrey-Villegas"', doc.internal.pageSize.getWidth() / 2, 20, {align: 'center'});
                doc.setFontSize(14);
                doc.text("Reporte de Asignaciones", doc.internal.pageSize.getWidth() / 2, 35, {align: 'center'});
                doc.setFontSize(12);
                doc.text(`Fecha de Asignaciones: `+ this.fechaForm, 10, 45);
                doc.text(`Fecha de Busqueda: `+ fechaActual, 10, 55);

                try {
                    /* Listar las tablas existetes */
                    const response = await axios.post(url, {
                        opcion: "lisTablas",
                    });
                    this.AsigTabla = response.data; //Guaradar la respuesta en una variable
                    /* Prepararar Iteraciones en todas las tablas en busqueda de datos en la fecha seleccionada en el formulario */
                    const dataPromises = this.AsigTabla.map(element => 
                        axios.post(url, {
                            opcion: "lisDatos",
                            idTabla: element.idTabla,
                            fecha: this.fechaForm
                        }).then(response2 => response2.data)
                    );
                    /* Mediante promesas espera que se itere sobre todas las tablas y se guarda el resultado en una variable */
                    const allData = await Promise.all(dataPromises);
                    
                    let startY = 70; //Punto de partida de donde comienza a mostrarse las tablas en el documento PDF
                    allData.forEach((lisDatos, index) => {
                        if (lisDatos.length === 0) return; // Si no hay datos, saltar a la siguiente iteración
                        
                        if (index > 0) {
                            startY = doc.autoTable.previous.finalY + 10; // Espacio entre tablas
                        }
                        /* Preparar las columnas a mostrar en las tablas */
                        const columns = [
                            { title: "Iniciales Socio", dataKey: "inicialesSocio" },
                            { title: "Col Izquierda", dataKey: "colIzquierda" },
                            { title: "Col Derecha", dataKey: "colDerecha" },
                        ];
                        /* Mediante jsPDF Autotable se generan las tablas con los datos en el archivo PDF */
                        doc.autoTable({
                            head: [
                                [{ content: lisDatos[0].nombreTabla, colSpan: 3, styles: {halign: 'center', fillColor: [40, 109, 132]} }],
                                columns.map(col => col.title)
                            ],
                            body: lisDatos.map(row => columns.map(col => row[col.dataKey])),
                            startY: startY,
                            theme: 'striped'
                        });
                    });

                    const pdfOutput = doc.output('blob');

                    // Detectar si el usuario está en un dispositivo móvil
                    const isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);

                    if (isMobile) {
                        // Descargar el archivo PDF en dispositivos móviles
                        const pdfUrl = URL.createObjectURL(pdfOutput);
                        const a = document.createElement('a');
                        a.href = pdfUrl;
                        a.download = 'reporte_asignaciones_'+this.fechaForm+'.pdf'; //nombre del archivo a descargar
                        document.body.appendChild(a);
                        a.click();
                        document.body.removeChild(a);
                    } else {
                        // Mostrar el archivo PDF en un iframe en computadoras
                        const pdfBlobUrl = URL.createObjectURL(pdfOutput);
                        pdfViewer.attr('src', pdfBlobUrl);
                    }
                    
                } catch (error) {
                    console.error("Error al ejecutar las solicitudes: ", error);
                }

            }else{
                /* Si no se establece una fecha de reporte saldrá un mensaje de error indicando que se ingrese una fecha */
                Swal.fire({
                    title: 'No se puede realizar la operación!',
                    text: 'Ingrese una fecha para realizar la busqueda',
                    icon: 'error',
                });
            }
        },

    },
    created: function() {
    }
});