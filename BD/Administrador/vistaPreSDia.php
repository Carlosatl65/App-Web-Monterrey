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
INSERT INTO vista_previa (idAsignacionBus, idRuta, fechaAsignacionRuta)
VALUES (:idAsignacionBus, :idRuta, :fechaAsignacionRuta);
";

$stmt = $conexion->prepare($consultaInsertarAsignacion);


// Obtener la fecha y hora actuales
$now = new DateTime();


$consultaRutas = "SELECT idRuta FROM ruta WHERE idDia = 1 AND estado = 1";
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


    if ($now->format('w') == 0 && $now->format('H') >= 19) { // Domingo desde las 7 de la tarde setea fecha de martes
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
    } elseif ($now->format('w') == 1 && $now->format('H') < 19) { // Lunes antes de las 7 setea fecha de martes
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
    } elseif ($now->format('w') == 1 && $now->format('H') >= 19) { // Lunes desde las 7 de la tarde setea fecha miercoles
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
    } elseif ($now->format('w') == 2 && $now->format('H') < 19) { // Martes antes de las 7 setea fecha miercoles
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
    } elseif ($now->format('w') == 2 && $now->format('H') >= 19) { // Martes desde las 7 de la tarde setea fecha jueves
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
    } elseif ($now->format('w') == 3 && $now->format('H') < 19) { // Miércoles antes de las 7 ver jueves
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
    } elseif ($now->format('w') == 3 && $now->format('H') >= 19) { // Miércoles desde las 7 de la tarde setea fecha viernes
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
    } elseif ($now->format('w') == 4 && $now->format('H') < 19) { // Jueves antes de las 7 ver viernes
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
    } elseif ($now->format('w') == 4 && $now->format('H') >= 19) { // Jueves desde las 7 ver lunes
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
    } elseif ($now->format('w') == 5 && $now->format('H') < 19) { // Viernes antes de las 7 ver lunes
        // Buscar el tercer bus o ranchera siguiente en la lista
        foreach ($busesRancheras as $bus) {
            if ($bus['idTipoUnidad'] === $ultimoTipoUnidadAsignado && $bus['ordenAsigRuta'] > ($ultimoBusAsignado + 2)) {
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
                if ($bus['idTipoUnidad'] === $ultimoTipoUnidadAsignado && $bus['ordenAsigRuta'] > ($sumaDias - $TotalUnidadesdeTipo['cuenta'])) {
                    $siguienteBusAsignado = $bus['idBus'];
                    $asignacionBus = $bus;
                    break;
                }
            }
        }
    } elseif ($now->format('w') == 5 && $now->format('H') >= 19) { // Viernes desde las 7 ver lunes
        // Buscar el tercer bus o ranchera siguiente en la lista
        foreach ($busesRancheras as $bus) {
            if ($bus['idTipoUnidad'] === $ultimoTipoUnidadAsignado && $bus['ordenAsigRuta'] > ($ultimoBusAsignado + 2)) {
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
                if ($bus['idTipoUnidad'] === $ultimoTipoUnidadAsignado && $bus['ordenAsigRuta'] > ($sumaDias - $TotalUnidadesdeTipo['cuenta'])) {
                    $siguienteBusAsignado = $bus['idBus'];
                    $asignacionBus = $bus;
                    break;
                }
            }
        }
    } elseif ($now->format('w') == 6 && $now->format('H') < 19) { // Sábado antes de las 7 ver lunes
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
    } elseif ($now->format('w') == 6 && $now->format('H') >= 19) { // Sábado desde las 7 ver lunes
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
    } else { // Domingo antes de las 7 ver lunes
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


        // Determinar la fecha de asignación según reglas establecidas
        if ($now->format('w') == 0 && $now->format('H') >= 19) { // Domingo desde las 7 de la tarde setea fecha de martes
            $fechaAsignacion = date('Y-m-d', strtotime('next tuesday'));
        } elseif ($now->format('w') == 1 && $now->format('H') < 19) { // Lunes antes de las 7 setea fecha de martes
            $fechaAsignacion = date('Y-m-d', strtotime('next tuesday'));
        } elseif ($now->format('w') == 1 && $now->format('H') >= 19) { // Lunes desde las 7 de la tarde setea fecha miercoles
            $fechaAsignacion = date('Y-m-d', strtotime('next wednesday'));
        } elseif ($now->format('w') == 2 && $now->format('H') < 19) { // Martes antes de las 7 setea fecha miercoles
            $fechaAsignacion = date('Y-m-d', strtotime('next wednesday'));
        } elseif ($now->format('w') == 2 && $now->format('H') >= 19) { // Martes desde las 7 de la tarde setea fecha jueves
            $fechaAsignacion = date('Y-m-d', strtotime('next thursday'));
        } elseif ($now->format('w') == 3 && $now->format('H') < 19) { // Miércoles antes de las 7 setea fecha jueves
            $fechaAsignacion = date('Y-m-d', strtotime('next thursday'));
        } elseif ($now->format('w') == 3 && $now->format('H') >= 19) { // Miércoles desde las 7 de la tarde setea fecha viernes
            $fechaAsignacion = date('Y-m-d', strtotime('next friday'));
        } elseif ($now->format('w') == 4 && $now->format('H') < 19) { // Jueves antes de las 7 setea fecha viernes
            $fechaAsignacion = date('Y-m-d', strtotime('next friday'));
        } elseif ($now->format('w') == 4 && $now->format('H') >= 19) { // Jueves desde las 7 de la tarde setea fecha de lunes
            $fechaAsignacion = date('Y-m-d', strtotime('next monday'));
        } elseif ($now->format('w') == 5 && $now->format('H') < 19) { // Viernes antes de las 7 setea fecha de lunes
            $fechaAsignacion = date('Y-m-d', strtotime('next monday'));
        } elseif ($now->format('w') == 5 && $now->format('H') >= 19) { // Viernes desde las 7 setea fecha de lunes
            $fechaAsignacion = date('Y-m-d', strtotime('next monday'));
        } elseif ($now->format('w') == 6 && $now->format('H') < 19) { // Sábado antes de las 7 setea fecha de lunes
            $fechaAsignacion = date('Y-m-d', strtotime('next monday'));
        } elseif ($now->format('w') == 6 && $now->format('H') >= 19) { // Sábado desde las 7 de la tarde setea fecha de lunes
            $fechaAsignacion = date('Y-m-d', strtotime('next monday'));
        } else { // Domingo antes de las 7
            $fechaAsignacion = date('Y-m-d', strtotime('next monday'));
        }
        
         // Insertar la asignación en la tabla asignar_ruta
        $stmt->bindParam(':idAsignacionBus', $asignacionBus['idAsignacionBus']);
        $stmt->bindParam(':idRuta', $ruta['idRuta']);
        $stmt->bindParam(':fechaAsignacionRuta', $fechaAsignacion);

        $stmt->execute();
    }
}

$conexion = NULL;
?>