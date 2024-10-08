<?php
date_default_timezone_set('America/Guayaquil');

include_once '../conexion.php';
$objeto = new Conexion();
$conexion = $objeto->Conectar();

// Consulta para obtener los buses y rancheras activos y ordenados
$consultaBusesRancheras = "
WITH BusesActivos AS (
    SELECT 
        b.idBus,
        b.ordenAsigRuta,
        b.idTipoUnidad
    FROM 
        bus b
    WHERE 
        b.idEstado IN (1, 2)
),
Buses AS (
    SELECT 
        idBus,
        ordenAsigRuta,
        idTipoUnidad,
        ROW_NUMBER() OVER (ORDER BY ordenAsigRuta) AS row_num
    FROM 
        BusesActivos
    WHERE 
        idTipoUnidad = 1
),
Rancheras AS (
    SELECT 
        idBus,
        ordenAsigRuta,
        idTipoUnidad,
        ROW_NUMBER() OVER (ORDER BY ordenAsigRuta) AS row_num
    FROM 
        BusesActivos
    WHERE 
        idTipoUnidad = 2
)
SELECT * FROM Buses
UNION ALL
SELECT * FROM Rancheras;
";

$resultadoBusesRancheras = $conexion->prepare($consultaBusesRancheras);
$resultadoBusesRancheras->execute();
$busesRancheras = $resultadoBusesRancheras->fetchAll(PDO::FETCH_ASSOC);


// Preparar la consulta para insertar las asignaciones en la tabla asignar_ruta
$consultaInsertarAsignacion = "
INSERT INTO asignar_ruta (idAsignacionBus, idRuta, fechaAsignacionRuta, idGeneracion, confirmacion)
VALUES (:idAsignacionBus, :idRuta, :fechaAsignacionRuta, :idGeneracion, :confirmacion);
";

$stmt = $conexion->prepare($consultaInsertarAsignacion);



$diaSemana = date('w');

if($diaSemana == 0){
    //Domingo
    // Iterar sobre las rutas del lunes a viernes
    $consultaRutas = "SELECT idRuta FROM ruta WHERE idDia = 1 AND estado=1";
    $resultadoRutas = $conexion->prepare($consultaRutas);
    $resultadoRutas->execute();
    $rutas = $resultadoRutas->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rutas as $ruta) {
        // Obtener la última asignación para la ruta actual
        $consultaUltimaAsignacionRuta = "
        SELECT 
            ar.idAsignacionRuta,
            ar.idAsignacionBus,
            ab.idBus,
            b.idTipoUnidad,
            b.ordenAsigRuta,
            ar.fechaAsignacionRuta
        FROM 
            asignar_ruta ar
        JOIN 
            asignar_bus ab ON ar.idAsignacionBus = ab.idAsignacionBus
        JOIN 
            bus b ON ab.idBus = b.idBus
        WHERE 
            b.idTipoUnidad IN (1, 2)
            AND ar.idRuta = :idRuta
            AND ar.idGeneracion = 1
        ORDER BY 
            ar.fechaAsignacionRuta DESC, ar.idAsignacionRuta DESC LIMIT 1;
        ";
    
        $stmtRuta = $conexion->prepare($consultaUltimaAsignacionRuta);
        $stmtRuta->bindParam(':idRuta', $ruta['idRuta']);
        $stmtRuta->execute();
        $ultimaAsignacionRuta = $stmtRuta->fetch(PDO::FETCH_ASSOC);
    
        // Buscar el siguiente bus o ranchera para la ruta actual
        $ultimoBusAsignado = $ultimaAsignacionRuta['ordenAsigRuta'] ?? 0;
        $ultimoTipoUnidadAsignado = $ultimaAsignacionRuta['idTipoUnidad'] ?? 0;
    
        $siguienteBusAsignado = null;
        $asignacionBus = null;
    
        // Buscar el tercer bus o ranchera siguiente en la lista
        foreach ($busesRancheras as $bus) {
            if ($bus['idTipoUnidad'] === $ultimoTipoUnidadAsignado && $bus['ordenAsigRuta'] > $ultimoBusAsignado + 2) {
                $siguienteBusAsignado = $bus['idBus'];
                $asignacionBus = $bus;
                break;
            }
        }
    
        // Si no se encontró el siguiente bus o ranchera, se empieza desde el inicio de la lista de cada tipo de unidad
        if ($siguienteBusAsignado === null) {
            $sumaDias = $ultimoBusAsignado + 2;

            $consultaNumTransporte = "SELECT COUNT(*) AS cuenta FROM `bus` WHERE `idEstado` IN (1,2) AND `idTipoUnidad` = :idTipoUnidad";
            $resNumTransporte = $conexion->prepare($consultaNumTransporte);
            $resNumTransporte->bindParam(":idTipoUnidad",$ultimoTipoUnidadAsignado, PDO::PARAM_INT);
            $resNumTransporte->execute();
            $TotalUnidadesdeTipo = $resNumTransporte->fetch(PDO::FETCH_ASSOC);

            foreach ($busesRancheras as $bus) {
                if ($bus['idTipoUnidad'] === $ultimoTipoUnidadAsignado && $bus['ordenAsigRuta'] > $sumaDias - $TotalUnidadesdeTipo['cuenta']) {
                    $siguienteBusAsignado = $bus['idBus'];
                    $asignacionBus = $bus;
                    break;
                }
            }
        }
    
        // Obtener el id de asignación del bus o ranchera
        if ($asignacionBus !== null) {
            $consultaAsignacionBus = "
                SELECT 
                    idAsignacionBus
                FROM 
                    asignar_bus
                WHERE 
                    idBus = :idBus;
            ";
            $stmtAsignacionBus = $conexion->prepare($consultaAsignacionBus);
            $stmtAsignacionBus->bindParam(':idBus', $asignacionBus['idBus']);
            $stmtAsignacionBus->execute();
            $asignacionBus = $stmtAsignacionBus->fetch(PDO::FETCH_ASSOC);
    
            // Insertar la asignación en la tabla asignar_ruta
            $fechaAsignacion = date('Y-m-d', strtotime('+1 day')); // Fecha día siguiente
            $idGeneracion = 1;
            $confirmacion = 0;
    
            $stmt->bindParam(':idAsignacionBus', $asignacionBus['idAsignacionBus']);
            $stmt->bindParam(':idRuta', $ruta['idRuta']);
            $stmt->bindParam(':fechaAsignacionRuta', $fechaAsignacion);
            $stmt->bindParam(':idGeneracion', $idGeneracion);
            $stmt->bindParam(':confirmacion', $confirmacion);
    
            $stmt->execute();
        }
    }



}elseif($diaSemana >= 1 and $diaSemana <= 4){
    // Iterar sobre las rutas del lunes a viernes
    $consultaRutas = "SELECT idRuta FROM ruta WHERE idDia = 1";
    $resultadoRutas = $conexion->prepare($consultaRutas);
    $resultadoRutas->execute();
    $rutas = $resultadoRutas->fetchAll(PDO::FETCH_ASSOC);
    
    //Lunes a jueves
    foreach ($rutas as $ruta) {
        // Obtener la última asignación para la ruta actual
        $consultaUltimaAsignacionRuta = "
        SELECT 
            ar.idAsignacionRuta,
            ar.idAsignacionBus,
            ab.idBus,
            b.idTipoUnidad,
            b.ordenAsigRuta,
            ar.fechaAsignacionRuta
        FROM 
            asignar_ruta ar
        JOIN 
            asignar_bus ab ON ar.idAsignacionBus = ab.idAsignacionBus
        JOIN 
            bus b ON ab.idBus = b.idBus
        WHERE 
            b.idTipoUnidad IN (1, 2)
            AND ar.idRuta = :idRuta
            AND ar.idGeneracion = 1
        ORDER BY 
            ar.fechaAsignacionRuta DESC LIMIT 1;
        ";
    
        $stmtRuta = $conexion->prepare($consultaUltimaAsignacionRuta);
        $stmtRuta->bindParam(':idRuta', $ruta['idRuta']);
        $stmtRuta->execute();
        $ultimaAsignacionRuta = $stmtRuta->fetch(PDO::FETCH_ASSOC);
    
        // Buscar el siguiente bus o ranchera para la ruta actual
        $ultimoBusAsignado = $ultimaAsignacionRuta['ordenAsigRuta'] ?? 0;
        $ultimoTipoUnidadAsignado = $ultimaAsignacionRuta['idTipoUnidad'] ?? 0;
    
        $siguienteBusAsignado = null;
        $asignacionBus = null;
    
        // Buscar el siguiente bus o ranchera en la lista
        foreach ($busesRancheras as $bus) {
            if ($bus['idTipoUnidad'] === $ultimoTipoUnidadAsignado && $bus['ordenAsigRuta'] > $ultimoBusAsignado) {
                $siguienteBusAsignado = $bus['idBus'];
                $asignacionBus = $bus;
                break;
            }
        }
    
        // Si no se encontró el siguiente bus o ranchera, se empieza desde el inicio de la lista de cada tipo de unidad
        if ($siguienteBusAsignado === null) {
            foreach ($busesRancheras as $bus) {
                if ($bus['idTipoUnidad'] === $ultimoTipoUnidadAsignado) {
                    $siguienteBusAsignado = $bus['idBus'];
                    $asignacionBus = $bus;
                    break;
                }
            }
        }
    
        // Obtener el id de asignación del bus o ranchera
        if ($asignacionBus !== null) {
            $consultaAsignacionBus = "
                SELECT 
                    idAsignacionBus
                FROM 
                    asignar_bus
                WHERE 
                    idBus = :idBus;
            ";
            $stmtAsignacionBus = $conexion->prepare($consultaAsignacionBus);
            $stmtAsignacionBus->bindParam(':idBus', $asignacionBus['idBus']);
            $stmtAsignacionBus->execute();
            $asignacionBus = $stmtAsignacionBus->fetch(PDO::FETCH_ASSOC);
    
            // Insertar la asignación en la tabla asignar_ruta
            $fechaAsignacion = date('Y-m-d', strtotime('+1 day')); // Fecha día siguiente
            $idGeneracion = 1;
            $confirmacion = 0;
    
            $stmt->bindParam(':idAsignacionBus', $asignacionBus['idAsignacionBus']);
            $stmt->bindParam(':idRuta', $ruta['idRuta']);
            $stmt->bindParam(':fechaAsignacionRuta', $fechaAsignacion);
            $stmt->bindParam(':idGeneracion', $idGeneracion);
            $stmt->bindParam(':confirmacion', $confirmacion);
    
            $stmt->execute();
        }
    }

}elseif($diaSemana == 5){
    //Viernes
    $fechaAsignacion = date('Y-m-d', strtotime('+1 day'));

    // Seleccionar todas las rutas con idDia=2
    /* $consultaRutasSabado = "SELECT idRuta FROM ruta WHERE idDia = 2 AND estado = 1";
    $resRutasSabado = $conexion->prepare($consultaRutasSabado);
    $resRutasSabado->execute();
    $rutasSabado = $resRutasSabado->fetchAll(PDO::FETCH_ASSOC); */
 
    
    $consulta = "INSERT INTO asignar_ruta (idAsignacionBus, idRuta, fechaAsignacionRuta, idGeneracion, confirmacion)
                SELECT 
                    null,
                    r.idRuta,
                    :fechaAsignacion,
                    1,
                    0
                FROM ruta r
                WHERE r.idDia = 2";
    $respuesta = $conexion->prepare($consulta);
    $respuesta->bindParam(':fechaAsignacion', $fechaAsignacion, PDO::PARAM_STR);
    $respuesta->execute();
    
}elseif($diaSemana == 6){
    //Sabado
    $fechaAsignacion = date('Y-m-d', strtotime('+1 day'));

    // Seleccionar todas las rutas con idDia=3
    /* $consultaRutasDomingo = "SELECT idRuta FROM ruta WHERE idDia = 3 AND estado = 1";
    $resRutasDomingo = $conexion->prepare($consultaRutasDomingo);
    $resRutasDomingo->execute();
    $rutasDomingo = $resRutasDomingo->fetchAll(PDO::FETCH_ASSOC); */
    
    
    $consulta = "INSERT INTO asignar_ruta (idAsignacionBus, idRuta, fechaAsignacionRuta, idGeneracion, confirmacion)
                SELECT 
                    null,
                    r.idRuta,
                    :fechaAsignacion,
                    1,
                    0
                FROM ruta r
                WHERE r.idDia = 3";
    $respuesta = $conexion->prepare($consulta);
    $respuesta->bindParam(':fechaAsignacion', $fechaAsignacion, PDO::PARAM_STR);
    $respuesta->execute();
}



$conexion = NULL;
?>