<!-- Importar inicio de página, menús y contenedor main -->
<?php
    require('../../Diseños/Administrador/header.php');
?>

<!-- Cuerpo de la página -->
<div id="ReportesAsig"> <!-- Contenedor con id nombrada en Vue -->
        <div class="container">
            <br>
            <!-- Cabecera de la página -->
            <h1 class="text-center fw-bolder">REPORTES DE ASIGNACIONES</h1>
            <br>
            <div class="row">
                <!-- Texto informativo -->
                <div class="col-lg-3 col-sm-6 col-12 text-center">
                    <h5 for="fechaBuscar" class="form-label">Fecha del reporte a buscar:</h5>
                </div>
                <!-- Imput de tipo date con restricción de fechas mínima y máxima (día actual) -->
                <div class="col-lg-2 col-sm-3 col-7">
                    <input id="fechaBuscar" class="form-control" type="date" min="2024-07-05" max="<?= date("Y-m-d") ?>">
                </div>
                <!-- Botón para generar el reporte -->
                <div class="col-lg-3 col-sm-3 col-5">
                    <button class="btn btn-success" @click="btnVistaPrevia()"><i class="bi bi-search"></i> Buscar</button>
                </div>
            </div><br><br>
            <!-- iFrame para mostrar el pdf del reporte -->
            <div class="row">
                <iframe id="pdf-viewer" width="100%" height="550px"></iframe>
            </div>
        </div>

</div>



    <!-- CDN de jsPDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js" integrity="sha512-qZvrmS2ekKPF2mSznTQsxqPgnpkI4DNTlrdUmTzrDgektczlKNRRhy5X5AAOnx5S09ydFYWWNSfcEqDTTHgtNA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    
    <!-- CDN de jsPDF AutoTable -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js" integrity="sha512-2/YdOMV+YNpanLCF5MdQwaoFRVbTmrJ4u4EpqS/USXAQNUDgI5uwYi6J98WVtJKcfe1AbgerygzDFToxAlOGEQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<?php
    require('../../Diseños/Administrador/footer.php');  
?>

    <script src="Modulos JS/Administrador/reportes.js"></script>
    
<?php
    require('../../Diseños/Administrador/end.php');
?>