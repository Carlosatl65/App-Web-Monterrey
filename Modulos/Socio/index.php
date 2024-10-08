<!-- Importar inicio de página, menús y contenedor main -->
<?php
    require('../../Diseños/Socio/header.php');
?>

<!-- Cuerpo de la página -->
<div id="Asignaciones"> <!-- Contenedor con id nombrada en Vue -->
        <div class="container">
            <br>
            <h1 class="text-center fw-bolder">Asignaciones del Día de Hoy</h1>
            <br>
            <!-- iFrame para mostrar el pdf de las asignaciones diarias -->
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
    require('../../Diseños/Socio/footer.php');
?>

    <script src="Modulos JS/Socio/inicio.js"></script>

<?php
    require('../../Diseños/Socio/end.php');
?>