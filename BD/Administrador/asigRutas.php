<?php
include_once '../conexion.php';
$objeto = new Conexion();
$conexion = $objeto->Conectar();

$_POST = json_decode(file_get_contents("php://input"), true);
$opcion = $_POST['opcion'];

session_start();
$idResponsable = $_SESSION['idPersona'];
date_default_timezone_set('America/Guayaquil');
$fechaAccion = date("Y-m-d H:i:s");

$tipoFiltro = (isset($_POST['tipoFiltro'])) ? $_POST['tipoFiltro'] : null;
$fecha = (isset($_POST['fecha'])) ? $_POST['fecha'] : null;
$idGeneracion = (isset($_POST['idGeneracion'])) ? $_POST['idGeneracion'] : null;
$asignaciones = (isset($_POST['asignaciones'])) ? $_POST['asignaciones'] : null;


switch($opcion){
    case "listar":
        $consulta = "SELECT CONCAT(UPPER(LEFT(`p`.`nombrePersona`, 1)), UPPER(LEFT(`p`.`apellidoPersona`, 1))) AS inicialesSocio,
                        u.idUsuario,
                        r.colIzquierda,
                        r.colDerecha,
                        r.idTabla,
                        ar.idRuta,
                        ar.idAsignacionRuta,
                        ar.fechaAsignacionRuta,
                        ar.idGeneracion,
                        ar.confirmacion
                    FROM 
                        asignar_ruta ar
                    LEFT JOIN 
                        asignar_bus ab ON ar.idAsignacionBus = ab.idAsignacionBus
                    LEFT JOIN
                        bus b ON ab.idBus = b.idBus
                    LEFT JOIN 
                        usuario u ON b.idPropietario = u.idUsuario
                    LEFT JOIN 
                        persona p ON u.idPersona = p.idPersona
                    LEFT JOIN 
                        ruta r ON ar.idRuta = r.idRuta
                    WHERE
                        r.idTabla = :tipoFiltro 
                        AND r.estado = 1 
                        AND ar.fechaAsignacionRuta = :fecha
                        AND ar.idGeneracion = :idGeneracion
                    ORDER BY `r`.`idTabla` ASC, `r`.`colIzquierda` ASC";
        $resultado = $conexion->prepare($consulta);
        $resultado->bindParam(':tipoFiltro', $tipoFiltro, PDO::PARAM_INT);
        $resultado->bindParam(':fecha', $fecha, PDO::PARAM_STR);
        $resultado->bindParam(':idGeneracion', $idGeneracion, PDO::PARAM_STR);
        $resultado->execute();
        $data = $resultado->fetchAll(PDO::FETCH_ASSOC);
        break;
    case "lstSocios":
        $consulta = "SELECT `usuario`.`idUsuario`, CONCAT(UPPER(LEFT(`persona`.`nombrePersona`, 1)), UPPER(LEFT(`persona`.`apellidoPersona`, 1))) AS inicialesSocio 
                     FROM `persona` 
                     JOIN `usuario` ON `usuario`.`idPersona` = `persona`.`idPersona`
                     WHERE `usuario`.`idRol` = '2' AND `usuario`.`estado` = '1'
                     ORDER BY `persona`.`apellidoPersona` ASC";
        $resultado = $conexion->prepare($consulta);
        $resultado->execute();
        $data = $resultado->fetchAll(PDO::FETCH_ASSOC);
        break;
    case "confirmarRuta":
        if($asignaciones) {
            foreach($asignaciones as $asignacion) {
                $idUsuario = $asignacion['idUsuario'];
                /* $idAsignacionRuta = $asignacion['idAsignacionRuta']; */ 
                $idRuta = $asignacion['idRuta'];
                $fechaAsignacionRuta = $asignacion['fechaAsignacionRuta'];

                // Obtener el idBus correspondiente al idUsuario
                $consulta = "SELECT idBus FROM bus WHERE idPropietario  = :idUsuario";
                $resultado = $conexion->prepare($consulta);
                $resultado->bindParam(':idUsuario', $idUsuario, PDO::PARAM_INT);
                $resultado->execute();
                $bus = $resultado->fetch(PDO::FETCH_ASSOC);

                if($bus) {
                    $idBus = $bus['idBus'];

                    // Obtener el idAsignacionBus correspondiente al idBus
                    $consulta = "SELECT idAsignacionBus FROM asignar_bus WHERE idBus = :idBus";
                    $resultado = $conexion->prepare($consulta);
                    $resultado->bindParam(':idBus', $idBus, PDO::PARAM_INT);
                    $resultado->execute();
                    $asignacionBus = $resultado->fetch(PDO::FETCH_ASSOC);

                    if($asignacionBus) {
                        $idAsignacionBus = $asignacionBus['idAsignacionBus'];

                        // Insertar a la tabla asignar_ruta con las asignaciones manuales
                        /* $consulta = "UPDATE asignar_ruta
                                     SET idAsignacionBus = :idAsignacionBus, confirmacion = 0
                                     WHERE idAsignacionRuta = :idAsignacionRuta"; */
                        $consulta = "INSERT INTO `asignar_ruta`(`idAsignacionBus`, `idRuta`, `fechaAsignacionRuta`, `idGeneracion`, `confirmacion`)
                                     VALUES (:idAsignacionBus, :idRuta, :fechaAsignacionRuta, 2, 1)";
                        $resultado = $conexion->prepare($consulta);
                        $resultado->bindParam(':idAsignacionBus', $idAsignacionBus, PDO::PARAM_INT);
                        $resultado->bindParam(':idRuta', $idRuta, PDO::PARAM_INT);
                        $resultado->bindParam(':fechaAsignacionRuta', $fechaAsignacionRuta, PDO::PARAM_STR);
                        /* $resultado->bindParam(':idAsignacionRuta', $idAsignacionRuta, PDO::PARAM_INT); */
                        $resultado->execute();
                    }
                }
            }
            $data = array("mensaje" => "Rutas confirmadas exitosamente");

            /* Mandar al App_LOG */
            $log_consulta = "INSERT INTO `app_log`(`idUsuario`, `descripcion`, `fechaHoraAccion`) VALUES (:idUsuario,'Se han confirmado rutas',:fechaAccion)";
            $Log_resultado = $conexion->prepare($log_consulta);
            $Log_resultado->bindParam(":idUsuario",$idResponsable, PDO::PARAM_INT);
            $Log_resultado->bindParam(":fechaAccion",$fechaAccion, PDO::PARAM_STR);
            $Log_resultado->execute();

        } else {
            $data = array("mensaje" => "Datos incompletos");
        }
        break;
    case 'lstTablas':
            $consulta = "SELECT * FROM `tabla` ORDER BY `idTabla` ASC";
            $resultado = $conexion->prepare($consulta);
            $resultado->execute();
            $data = $resultado->fetchAll(PDO::FETCH_ASSOC);
        break;
    case 'datosVPrevia':
            $consulta = "SELECT 
                        CONCAT(UPPER(LEFT(`p`.`nombrePersona`, 1)), UPPER(LEFT(`p`.`apellidoPersona`, 1))) AS inicialesSocio, 
                        vp.fechaAsignacionRuta, 
                        r.colIzquierda, 
                        r.colDerecha, 
                        t.nombreTabla 
                        FROM `vista_previa` vp 
                        JOIN asignar_bus ab ON vp.idAsignacionBus = ab.idAsignacionBus
                        JOIN bus b ON ab.idBus = b.idBus
                        JOIN usuario u ON b.idPropietario = u.idUsuario
                        JOIN persona p ON u.idPersona = p.idPersona
                        JOIN ruta r ON vp.idRuta = r.idRuta 
                        JOIN tabla t ON r.idTabla = t.idTabla 
                        ORDER BY `r`.`idTabla` ASC, `r`.`colIzquierda`";
            $resultado = $conexion->prepare($consulta);
            $resultado->execute();
            $data = $resultado->fetchAll(PDO::FETCH_ASSOC);

            /* Mandar al App_LOG */
            $log_consulta = "INSERT INTO `app_log`(`idUsuario`, `descripcion`, `fechaHoraAccion`) VALUES (:idUsuario,'Se ha revisado asignaciones del dìa siguiente',:fechaAccion)";
            $Log_resultado = $conexion->prepare($log_consulta);
            $Log_resultado->bindParam(":idUsuario",$idResponsable, PDO::PARAM_INT);
            $Log_resultado->bindParam(":fechaAccion",$fechaAccion, PDO::PARAM_STR);
            $Log_resultado->execute();
        break;
    case 'borrarDatosVPrevia':
            $consulta = "TRUNCATE TABLE `vista_previa`";
            $resultado = $conexion->prepare($consulta);
            $resultado->execute();
            $data = $resultado->fetchAll(PDO::FETCH_ASSOC);
        break;
        
    case 'editManual':
            if($asignaciones) {
                foreach($asignaciones as $asignacion) {
                    $idUsuario = $asignacion['idUsuario'];
                    $idAsignacionRuta = $asignacion['idAsignacionRuta'];
                    $idRuta = $asignacion['idRuta'];
                    /* $fechaAsignacionRuta = $asignacion['fechaAsignacionRuta']; */

                    // Obtener el idBus correspondiente al idUsuario
                    $consulta = "SELECT idBus FROM bus WHERE idPropietario  = :idUsuario";
                    $resultado = $conexion->prepare($consulta);
                    $resultado->bindParam(':idUsuario', $idUsuario, PDO::PARAM_INT);
                    $resultado->execute();
                    $bus = $resultado->fetch(PDO::FETCH_ASSOC);

                    if($bus) {
                        $idBus = $bus['idBus'];

                        // Obtener el idAsignacionBus correspondiente al idBus
                        $consulta = "SELECT idAsignacionBus FROM asignar_bus WHERE idBus = :idBus";
                        $resultado = $conexion->prepare($consulta);
                        $resultado->bindParam(':idBus', $idBus, PDO::PARAM_INT);
                        $resultado->execute();
                        $asignacionBus = $resultado->fetch(PDO::FETCH_ASSOC);

                        if($asignacionBus) {
                            $idAsignacionBus = $asignacionBus['idAsignacionBus'];

                            // Insertar a la tabla asignar_ruta con las asignaciones manuales
                            $consulta = "UPDATE asignar_ruta
                                        SET idAsignacionBus = :idAsignacionBus
                                        WHERE idAsignacionRuta = :idAsignacionRuta";
                            $resultado = $conexion->prepare($consulta);
                            $resultado->bindParam(':idAsignacionBus', $idAsignacionBus, PDO::PARAM_INT);
                            $resultado->bindParam(':idAsignacionRuta', $idAsignacionRuta, PDO::PARAM_INT);
                            $resultado->execute();
                        }
                    }
                }
                $data = array("mensaje" => "Rutas confirmadas exitosamente");

                /* Mandar al App_LOG */
                $log_consulta = "INSERT INTO `app_log`(`idUsuario`, `descripcion`, `fechaHoraAccion`) VALUES (:idUsuario,'Se han editado las rutas de forma manual',:fechaAccion)";
                $Log_resultado = $conexion->prepare($log_consulta);
                $Log_resultado->bindParam(":idUsuario",$idResponsable, PDO::PARAM_INT);
                $Log_resultado->bindParam(":fechaAccion",$fechaAccion, PDO::PARAM_STR);
                $Log_resultado->execute();
            }else{
                $data = array("mensaje" => "Datos incompletos");
            }
        break;
}

print json_encode($data, JSON_UNESCAPED_UNICODE);
$conexion = NULL;
?>