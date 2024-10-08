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

$tipoTransporteAgregar = (isset($_POST['tipoTransporteAgregar'])) ? $_POST['tipoTransporteAgregar'] : null;
$idBus = (isset($_POST['idBus'])) ? $_POST['idBus'] : null;
$placaBus = (isset($_POST['placaBus'])) ? $_POST['placaBus'] : null;
$numeroBus = (isset($_POST['numeroBus'])) ? $_POST['numeroBus'] : null;
$anioBus = (isset($_POST['anioBus'])) ? $_POST['anioBus'] : null;
$capacidadBus = (isset($_POST['capacidadBus'])) ? $_POST['capacidadBus'] : null;
$idPropietario = (isset($_POST['idPropietario'])) ? $_POST['idPropietario'] : null;
$idChofer = (isset($_POST['idChofer'])) ? $_POST['idChofer'] : null;
$idAsignacionBus = (isset($_POST['idAsignacionBus'])) ? $_POST['idAsignacionBus'] : null;
$fechaAsignacion = (isset($_POST['fechaAsignacion'])) ? $_POST['fechaAsignacion'] : null;
$idEstadoBus = (isset($_POST['idEstadoBus'])) ? $_POST['idEstadoBus'] : null;

$buscar = (isset($_POST['buscar'])) ? $_POST['buscar'] : null;

switch($opcion){
    case "crear":
        $consulta = "SET @ordenAsigRuta = (SELECT COALESCE(MAX(`ordenAsigRuta`), 0) + 1 FROM `bus` WHERE `idEstado` = 1)";
        $resultado = $conexion->prepare($consulta);
        $resultado->execute();

        $consulta = "INSERT INTO `bus` (`placaBus`, `numeroBus`, `anioBus`, `capacidadBus`, `idPropietario`, `ordenAsigRuta`, `idEstado`, `idTipoUnidad`) 
                     VALUES (:placaBus, :numeroBus, :anioBus, :capacidadBus, :idPropietario, @ordenAsigRuta, '1', :tipoTransporteAgregar)";
        $resultado = $conexion->prepare($consulta);
        $resultado->bindParam(':placaBus', $placaBus);
        $resultado->bindParam(':numeroBus', $numeroBus);
        $resultado->bindParam(':anioBus', $anioBus);
        $resultado->bindParam(':capacidadBus', $capacidadBus);
        $resultado->bindParam(':idPropietario', $idPropietario);
        $resultado->bindParam(':tipoTransporteAgregar', $tipoTransporteAgregar);
        $resultado->execute();

        // Obtener el id del bus recién creado
         $idBusAgregado = $conexion->lastInsertId();

        // Insertar en asignar_bus el chofer asociado al bus recién creado
        $consulta = "INSERT INTO `asignar_bus` (`idBus`, `idUsuario`, `fechaAsignacion`) 
                    VALUES (:idBus, :idChofer, :fechaAsignacion)";
        $resultado = $conexion->prepare($consulta);
        $resultado->bindParam(':idBus', $idBusAgregado);
        $resultado->bindParam(':idChofer', $idChofer);
        $resultado->bindParam(':fechaAsignacion', $fechaAsignacion);
        $resultado->execute();

        /* Mandar al App_LOG */
        $log_consulta = "INSERT INTO `app_log`(`idUsuario`, `descripcion`, `fechaHoraAccion`) VALUES (:idUsuario,'Se ha ingresado una nueva unidad de transporte en la app',:fechaAccion)";
        $Log_resultado = $conexion->prepare($log_consulta);
        $Log_resultado->bindParam(":idUsuario",$idResponsable, PDO::PARAM_INT);
        $Log_resultado->bindParam(":fechaAccion",$fechaAccion, PDO::PARAM_STR);
        $Log_resultado->execute();
        break;
    
    case "editarChofer":
        $consulta = "UPDATE `asignar_bus` SET `idUsuario`= :idChofer ,`fechaAsignacion`= :fechaAsignacion WHERE `idAsignacionBus`= :idAsignacionBus";
        $resultado = $conexion->prepare($consulta);
        $resultado->bindParam(':idAsignacionBus', $idAsignacionBus);
        $resultado->bindParam(':idChofer', $idChofer);
        $resultado->bindParam(':fechaAsignacion', $fechaAsignacion);
        $resultado->execute();

        /* Mandar al App_LOG */
        $log_consulta = "INSERT INTO `app_log`(`idUsuario`, `descripcion`, `fechaHoraAccion`) VALUES (:idUsuario,'Se ha editado el chofer de una unidad',:fechaAccion)";
        $Log_resultado = $conexion->prepare($log_consulta);
        $Log_resultado->bindParam(":idUsuario",$idResponsable, PDO::PARAM_INT);
        $Log_resultado->bindParam(":fechaAccion",$fechaAccion, PDO::PARAM_STR);
        $Log_resultado->execute();
        break;

    case "eliminar":

        $conAsignacion = "SELECT COUNT(*) AS count FROM asignar_ruta ar JOIN asignar_bus ab ON ab.idAsignacionBus = ar.idAsignacionBus JOIN bus b ON b.idBus = ab.idBus WHERE b.idBus = :idBus";
        $resAsignacion = $conexion->prepare($conAsignacion);
        $resAsignacion->bindParam(":idBus", $idBus, PDO::PARAM_INT);
        $resAsignacion->execute();
        $cuentaAsignacion = $resAsignacion->fetch(PDO::FETCH_ASSOC);

        if($cuentaAsignacion['count'] > 0){
            $consulta = "UPDATE bus SET idEstado = 0 WHERE idBus = :idBus";
            $resultado = $conexion->prepare($consulta);
            $resultado->bindParam(":idBus", $idBus, PDO::PARAM_INT);
            $resultado->execute();
            $data = $resultado->fetchAll(PDO::FETCH_ASSOC);

            /* Mandar al App_LOG */
            $log_consulta = "INSERT INTO `app_log`(`idUsuario`, `descripcion`, `fechaHoraAccion`) VALUES (:idUsuario,'Se ha dado de baja una unidad de transporte',:fechaAccion)";
            $Log_resultado = $conexion->prepare($log_consulta);
            $Log_resultado->bindParam(":idUsuario",$idResponsable, PDO::PARAM_INT);
            $Log_resultado->bindParam(":fechaAccion",$fechaAccion, PDO::PARAM_STR);
            $Log_resultado->execute();
        }else{
            
            $conAsignacion = "SELECT ab.idAsignacionBus FROM asignar_bus ab JOIN bus b ON b.idBus = ab.idBus WHERE b.idBus = :idBus";
            $resIdAsignacion = $conexion->prepare($conAsignacion);
            $resIdAsignacion->bindParam(":idBus", $idBus, PDO::PARAM_INT);
            $resIdAsignacion->execute();
            $idAsignacionBUS = $resIdAsignacion->fetch(PDO::FETCH_ASSOC);

            if($idAsignacionBUS){
                $consultaChofer = "DELETE FROM asignar_bus WHERE idAsignacionBus = :idAsignacionBus";
                $res = $conexion->prepare($consultaChofer);
                $res->bindParam(":idAsignacionBus", $idAsignacionBUS['idAsignacionBus'], PDO::PARAM_INT);
                $res->execute();
            }

            $consulta = "DELETE FROM bus WHERE idBus = :idBus";
            $resultado = $conexion->prepare($consulta);
            $resultado->bindParam(":idBus", $idBus, PDO::PARAM_INT);
            $resultado->execute();
            $data = $resultado->fetchAll(PDO::FETCH_ASSOC);

            /* Mandar al App_LOG */
            $log_consulta = "INSERT INTO `app_log`(`idUsuario`, `descripcion`, `fechaHoraAccion`) VALUES (:idUsuario,'Se ha eliminado una unidad de transporte',:fechaAccion)";
            $Log_resultado = $conexion->prepare($log_consulta);
            $Log_resultado->bindParam(":idUsuario",$idResponsable, PDO::PARAM_INT);
            $Log_resultado->bindParam(":fechaAccion",$fechaAccion, PDO::PARAM_STR);
            $Log_resultado->execute();
        }
        
        break;

    case "listar":
        $consulta = "SELECT 
                        ab.idAsignacionBus,
                        b.idBus, 
                        b.placaBus, 
                        b.numeroBus, 
                        b.anioBus, 
                        b.capacidadBus, 
                        b.idEstado,
                        b.idTipoUnidad,
                        tu.nombreTipo,
                        u2.idUsuario AS idChofer, 
                        CONCAT(p1.apellidoPersona, ' ', p1.nombrePersona) AS nombrePropietario,
                        CONCAT(p2.apellidoPersona, ' ', p2.nombrePersona) AS nombreAsignado
                    FROM 
                        asignar_bus ab
                    JOIN 
                        bus b ON ab.idBus = b.idBus
                    JOIN 
                        usuario u ON b.idPropietario = u.idUsuario
                    JOIN 
                        persona p1 ON u.idPersona = p1.idPersona
                    JOIN 
                        usuario u2 ON ab.idUsuario = u2.idUsuario
                    JOIN 
                        persona p2 ON u2.idPersona = p2.idPersona
                    JOIN
                        tipo_unidad tu ON b.idTipoUnidad = tu.idTipoUnidad 
                    WHERE 
                        b.idEstado IN ('1', '2')
                    ORDER BY 
                        b.numeroBus ASC";
        $resultado = $conexion->prepare($consulta);
        $resultado->execute();
        $data = $resultado->fetchAll(PDO::FETCH_ASSOC);
        break;

    case "buscar":
        $consulta = "SELECT 
                        ab.idAsignacionBus,
                        b.idBus, 
                        b.placaBus, 
                        b.numeroBus, 
                        b.anioBus, 
                        b.capacidadBus, 
                        b.idEstado,
                        b.idTipoUnidad,
                        tu.nombreTipo,
                        u2.idUsuario AS idChofer, 
                        CONCAT(p1.apellidoPersona, ' ', p1.nombrePersona) AS nombrePropietario,
                        CONCAT(p2.apellidoPersona, ' ', p2.nombrePersona) AS nombreAsignado
                    FROM 
                        asignar_bus ab
                    JOIN 
                        bus b ON ab.idBus = b.idBus
                    JOIN 
                        usuario u ON b.idPropietario = u.idUsuario
                    JOIN 
                        persona p1 ON u.idPersona = p1.idPersona
                    JOIN 
                        usuario u2 ON ab.idUsuario = u2.idUsuario
                    JOIN 
                        persona p2 ON u2.idPersona = p2.idPersona
                    JOIN
                        tipo_unidad tu ON b.idTipoUnidad = tu.idTipoUnidad
                    WHERE 
                        b.idEstado IN ('1', '2') AND `b`.`placaBus` LIKE :buscar
                    ORDER BY 
                        b.numeroBus ASC";
        $resultado = $conexion->prepare($consulta);
        $paramBuscar = "%$buscar%";
        $resultado->bindParam(':buscar', $paramBuscar, PDO::PARAM_STR);
        $resultado->execute();
        $data = $resultado->fetchAll(PDO::FETCH_ASSOC);
        break;

    case "lstPropietarios":
        $consulta = "SELECT `usuario`.`idUsuario`, 
                            CONCAT(`persona`.`apellidoPersona`, ' ', `persona`.`nombrePersona`) nombreCompleto 
                     FROM `persona` 
                     JOIN `usuario` ON `usuario`.`idPersona` = `persona`.`idPersona`
                     WHERE `usuario`.`idRol` = '2' AND `usuario`.`estado` = '1'
                     ORDER BY `persona`.`apellidoPersona` ASC";
        $resultado = $conexion->prepare($consulta);
        $resultado->execute();
        $data = $resultado->fetchAll(PDO::FETCH_ASSOC);
        break;

    case "lstChoferes":
        $consulta = "SELECT `usuario`.`idUsuario`, 
                            CONCAT(`persona`.`apellidoPersona`, ' ', `persona`.`nombrePersona`) nombreCompleto 
                     FROM `persona` 
                     JOIN `usuario` ON `usuario`.`idPersona` = `persona`.`idPersona`
                     WHERE `usuario`.`idRol` IN ('2', '3') AND `usuario`.`estado` = '1'
                     ORDER BY `persona`.`apellidoPersona` ASC";
        $resultado = $conexion->prepare($consulta);
        $resultado->execute();
        $data = $resultado->fetchAll(PDO::FETCH_ASSOC);
        break;
    case "lstTipoTransporte":
        $consulta = "SELECT * FROM `tipo_unidad` ORDER BY `idTipoUnidad` ASC";
        $resultado = $conexion->prepare($consulta);
        $resultado->execute();
        $data = $resultado->fetchAll(PDO::FETCH_ASSOC);
        break;
    
    case "listarBajas":
        $consulta = "SELECT 
                        b.idBus, 
                        b.placaBus, 
                        tu.nombreTipo, 
                        CONCAT(p1.apellidoPersona, ' ', p1.nombrePersona) AS nombrePropietario
                    FROM 
                        bus b
                    JOIN 
                        usuario u ON b.idPropietario = u.idUsuario
                    JOIN 
                        persona p1 ON u.idPersona = p1.idPersona
                    JOIN
                        tipo_unidad tu ON b.idTipoUnidad = tu.idTipoUnidad 
                    WHERE 
                        b.idEstado = 0
                    ORDER BY 
                        p1.apellidoPersona ASC";
        $resultado = $conexion->prepare($consulta);
        $resultado->execute();
        $data = $resultado->fetchAll(PDO::FETCH_ASSOC);
        break;
    case "darAlta":
        $consulta = "UPDATE `bus` SET `idEstado`= 1 WHERE `idBus`= :idBus";
        $resultado = $conexion->prepare($consulta);
        $resultado->bindParam(':idBus', $idBus, PDO::PARAM_INT);
        $resultado->execute();
        $data = $resultado->fetchAll(PDO::FETCH_ASSOC);

        /* Mandar al App_LOG */
        $log_consulta = "INSERT INTO `app_log`(`idUsuario`, `descripcion`, `fechaHoraAccion`) VALUES (:idUsuario,'Se ha dado de alta una unidad de trasporte',:fechaAccion)";
        $Log_resultado = $conexion->prepare($log_consulta);
        $Log_resultado->bindParam(":idUsuario",$idResponsable, PDO::PARAM_INT);
        $Log_resultado->bindParam(":fechaAccion",$fechaAccion, PDO::PARAM_STR);
        $Log_resultado->execute();
        break;
    case "editarEstadoBus":
        $consulta = "UPDATE `bus` SET `idEstado`= :idEstadoBus WHERE `idBus`= :idBus";
        $resultado = $conexion->prepare($consulta);
        $resultado->bindParam(':idEstadoBus', $idEstadoBus, PDO::PARAM_INT);
        $resultado->bindParam(':idBus', $idBus, PDO::PARAM_INT);
        $resultado->execute();
        $data = $resultado->fetchAll(PDO::FETCH_ASSOC);

        /* Mandar al App_LOG */
        $log_consulta = "INSERT INTO `app_log`(`idUsuario`, `descripcion`, `fechaHoraAccion`) VALUES (:idUsuario,'Se ha editado el estado de una unidad de transporte',:fechaAccion)";
        $Log_resultado = $conexion->prepare($log_consulta);
        $Log_resultado->bindParam(":idUsuario",$idResponsable, PDO::PARAM_INT);
        $Log_resultado->bindParam(":fechaAccion",$fechaAccion, PDO::PARAM_STR);
        $Log_resultado->execute();
        break;
}

print json_encode($data, JSON_UNESCAPED_UNICODE);
$conexion = NULL;
?>