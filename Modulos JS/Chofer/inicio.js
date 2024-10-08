var url = "BD/Chofer/asignaciones.php"; /* Ruta de archivo php */
var Asig = new Vue({
    el: '#Asignaciones', /* Nombre de contenedor en vista */
    data: {
        AsigTabla: [],
        lisDatos: [],
        fecha: "",
    },
    methods: {
        /* Cargar Visor PDF y mostrar archivo generado para asignaciones diarias */
        listar: async function(){ 

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

                this.fecha = this.formatearFechaSQL(fechaParaEnviar);

                const pdfViewer = $("#pdf-viewer"); //preparar la variable pdf

                // Generar el PDF
                const { jsPDF } = window.jspdf;
                const doc = new jsPDF('p','mm','a4'); //formato de la hoja de pdf
                /* Encabezado del pdf */
                doc.setFontSize(16);
                doc.setFont('helvetica','bold');
                doc.text('Cooperativa de Transporte de Pasajeros\n"Monterrey-Villegas"', doc.internal.pageSize.getWidth() / 2, 20, {align: 'center'});
                doc.setFontSize(14);
                doc.text("Cuadro de Trabajo", doc.internal.pageSize.getWidth() / 2, 40, {align: 'center'});
                doc.setFontSize(12);
                doc.text(`Fecha: `+this.fecha, 10, 50);

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
                            fecha: this.fecha
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
                            { title: "Socio", dataKey: "inicialesSocio" },
                            { title: "Col Izquierda", dataKey: "colIzquierda" },
                            { title: "Col Derecha", dataKey: "colDerecha" },
                        ];
                        /* Mediante jsPDF Autotable se generan las tablas con los datos en el archivo PDF */
                        doc.autoTable({
                            head: [
                                [{ content: lisDatos[0].nombreTabla, colSpan: 4, styles: {halign: 'center', fillColor: [40, 109, 132]} }],
                                columns.map(col => col.title)
                            ],
                            body: lisDatos.map(row => columns.map(col => row[col.dataKey])),
                            startY: startY,
                            theme: 'striped'
                        });
                    });

                    
                    const pdfOutput = doc.output('datauristring');
                    
                    
                    // Enviar el PDF al servidor
                    const saveResponse = await axios.post('BD/Socio/savepdf.php', JSON.stringify({
                        pdf: pdfOutput.split(',')[1]
                    }), {
                        headers: {
                            'Content-Type': 'application/json'
                        }
                    });

                    //Buscar pdf y obtener ruta abosluta para cargar el documento en Viewer PDF 
                    const absoluteUrl = `../../BD/Socio/${saveResponse.data.url}`;
                    const viewer = encodeURI('Complementos/viewerjs-0.5.8/ViewerJS/#../'+absoluteUrl);

                    pdfViewer.attr('src', viewer); //Pasar ruta al contenedor que muestra el visor pdf con el archivo en la vista
                    
                } catch (error) {
                    console.error("Error al ejecutar las solicitudes: ", error);
                }
        },

        // Formatear fecha para SQL (YYYY-MM-DD)
        formatearFechaSQL: function(fecha) {
            const año = fecha.getFullYear();
            const mes = ('0' + (fecha.getMonth() + 1)).slice(-2);
            const día = ('0' + fecha.getDate()).slice(-2);
            return `${año}-${mes}-${día}`;
        },

    },
    created: function() {
        $(document).ready(this.listar);
    }
});