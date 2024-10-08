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

$idRuta = (isset($_POST['idRuta'])) ? $_POST['idRuta'] : null;
$colIzquierda = (isset($_POST['colIzquierda'])) ? $_POST['colIzquierda'] : null;
$colDerecha = (isset($_POST['colDerecha'])) ? $_POST['colDerecha'] : null;
$Tabla = (isset($_POST['Tabla'])) ? $_POST['Tabla'] : null;
$tipoFiltro = (isset($_POST['tipoFiltro'])) ? $_POST['tipoFiltro'] : null;
$filtroDias = (isset($_POST['filtroDias'])) ? $_POST['filtroDias'] : null;
$idDia = (isset($_POST['idDia'])) ? $_POST['idDia'] : null;

$idUsuario = (isset($_POST['idUsuario'])) ? $_POST['idUsuario'] : null;

$buscar = (isset($_POST['buscar'])) ? $_POST['buscar'] : null;

switch($opcion){
    case "crear":
        $consulta = "INSERT INTO `ruta`(`colIzquierda`, `colDerecha`, `idTabla`, `idDia`, `estado`) VALUES (:colIzquierda, :colDerecha, :Tabla, :idDia, '1')";
        $resultado = $conexion->prepare($consulta);
        $resultado->bindParam(':colIzquierda', $colIzquierda, PDO::PARAM_STR);
        $resultado->bindParam(':colDerecha', $colDerecha, PDO::PARAM_STR);
        $resultado->bindParam(':idDia', $idDia, PDO::PARAM_INT);
        $resultado->bindParam(':Tabla', $Tabla, PDO::PARAM_INT);
        $resultado->execute();

        $idRutaAsig = $conexion->lastInsertId();
        /* $data = $resultado->fetchAll(PDO::FETCH_ASSOC); */

        if($idUsuario != null){
            $con1 = "SELECT idBus FROM bus WHERE idPropietario = :idUsuario";
            $res1 = $conexion->prepare($con1);
            $res1->bindParam(":idUsuario",$idUsuario, PDO::PARAM_INT);
            $res1->execute();
            $idBus = $res1->fetch(PDO::FETCH_ASSOC);
            if($idBus != null){
                $con2 = "SELECT 
                            idAsignacionBus
                        FROM 
                            asignar_bus
                        WHERE 
                            idBus = :idBus";
                $res2 = $conexion->prepare($con2);
                $res2->bindParam(":idBus",$idBus['idBus'], PDO::PARAM_INT);
                $res2->execute();
                $asigBus = $res2->fetch(PDO::FETCH_ASSOC);

                if($asigBus != null){
                    $fecha = date("Y-m-d");
                    $con3 = "INSERT INTO `asignar_ruta`(`idAsignacionBus`, `idRuta`, `fechaAsignacionRuta`, `idGeneracion`, `confirmacion`) VALUES (:idAsignacion,:idRuta,:fecha,1,0)";
                    $res3 = $conexion->prepare($con3);
                    $res3->bindParam(":idAsignacion",$asigBus['idAsignacionBus'], PDO::PARAM_INT);
                    $res3->bindParam(":idRuta", $idRutaAsig, PDO::PARAM_INT);
                    $res3->bindParam(":fecha", $fecha, PDO::PARAM_STR);
                    $res3->execute();
                    $data = $res3->fetchAll(PDO::FETCH_ASSOC);
                }
            }
        }

        /* Mandar al App_LOG */
        $log_consulta = "INSERT INTO `app_log`(`idUsuario`, `descripcion`, `fechaHoraAccion`) VALUES (:idUsuario,'Se ha creado una nueva ruta',:fechaAccion)";
        $Log_resultado = $conexion->prepare($log_consulta);
        $Log_resultado->bindParam(":idUsuario",$idResponsable, PDO::PARAM_INT);
        $Log_resultado->bindParam(":fechaAccion",$fechaAccion, PDO::PARAM_STR);
        $Log_resultado->execute();
        break;
    case "eliminar":

        // Verificar si la ruta está siendo utilizada
        $consultaRuta = "SELECT COUNT(*) FROM asignar_ruta WHERE idRuta = :idRuta";
        $resRuta = $conexion->prepare($consultaRuta);
        $resRuta->bindParam(':idRuta', $idRuta, PDO::PARAM_INT);
        $resRuta->execute();
        $count = $resRuta->fetchColumn();

        if ($count > 0) {
            // Si está siendo utilizada, dar de baja la ruta
            $consulta = "UPDATE ruta SET estado = 0 WHERE idRuta = :idRuta";
            $res = $conexion->prepare($consulta);
            $res->bindParam(':idRuta', $idRuta, PDO::PARAM_INT);
            $res->execute();
            $data = $res->fetchAll(PDO::FETCH_ASSOC);
        } else {
            // Si no está siendo utilizada, eliminar la ruta
            $consulta = "DELETE FROM ruta WHERE idRuta = :idRuta";
            $res = $conexion->prepare($consulta);
            $res->bindParam(':idRuta', $idRuta, PDO::PARAM_INT);
            $res->execute();
            $data = $res->fetchAll(PDO::FETCH_ASSOC);
        }

        /* Mandar al App_LOG */
        $log_consulta = "INSERT INTO `app_log`(`idUsuario`, `descripcion`, `fechaHoraAccion`) VALUES (:idUsuario,'Se ha eliminado/dado de baja una ruta',:fechaAccion)";
        $Log_resultado = $conexion->prepare($log_consulta);
        $Log_resultado->bindParam(":idUsuario",$idResponsable, PDO::PARAM_INT);
        $Log_resultado->bindParam(":fechaAccion",$fechaAccion, PDO::PARAM_STR);
        $Log_resultado->execute();
        break;
    case "listar":
        if ($tipoFiltro == 0) {
            $consulta = "SELECT `r`.`idRuta`, `r`.`colIzquierda`, `r`.`colDerecha`, `t`.`nombreTabla` FROM `ruta` r JOIN `tabla` t ON `t`.`idTabla` = `r`.`idTabla` AND `r`.`estado` = 1 AND `r`.`idDia` = :filtroDias ORDER BY `t`.`idTabla` ASC, `r`.`colIzquierda` ASC";
        } else {
            $consulta = "SELECT `r`.`idRuta`, `r`.`colIzquierda`, `r`.`colDerecha`, `t`.`nombreTabla` FROM `ruta` r JOIN `tabla` t ON `t`.`idTabla` = `r`.`idTabla` AND `r`.`idTabla` = :tipoFiltro AND `r`.`idDia` = :filtroDias AND `r`.`estado` = 1 ORDER BY `t`.`idTabla` ASC, `r`.`colIzquierda` ASC";
        }
        $resultado = $conexion->prepare($consulta);
        $resultado->bindParam(':filtroDias', $filtroDias, PDO::PARAM_INT);
        if ($tipoFiltro != 0) {
            $resultado->bindParam(':tipoFiltro', $tipoFiltro, PDO::PARAM_INT);
        }
        $resultado->execute();
        $data = $resultado->fetchAll(PDO::FETCH_ASSOC);
        break;
    case "buscar":
        if ($tipoFiltro == 0) {
            $consulta = "SELECT `r`.`idRuta`, `r`.`colIzquierda`, `r`.`colDerecha`, `t`.`nombreTabla` FROM `ruta` r JOIN `tabla` t ON `t`.`idTabla` = `r`.`idTabla` AND `r`.`estado` = 1 AND `r`.`idDia` = :filtroDias AND `r`.`colIzquierda` LIKE :buscar ORDER BY `t`.`idTabla` ASC, `r`.`colIzquierda` ASC";
        } else {
            $consulta = "SELECT `r`.`idRuta`, `r`.`colIzquierda`, `r`.`colDerecha`, `t`.`nombreTabla` FROM `ruta` r JOIN `tabla` t ON `t`.`idTabla` = `r`.`idTabla` AND `r`.`idTabla` = :tipoFiltro AND `r`.`idDia` = :filtroDias AND `r`.`estado` = 1 AND `r`.`colIzquierda` LIKE :buscar ORDER BY `t`.`idTabla` ASC, `r`.`colIzquierda` ASC";
        }
        $resultado = $conexion->prepare($consulta);
        $buscarParam = "%".$buscar."%";
        $resultado->bindParam(':buscar', $buscarParam, PDO::PARAM_STR);
        $resultado->bindParam(':filtroDias', $filtroDias, PDO::PARAM_INT);
        if ($tipoFiltro != 0) {
            $resultado->bindParam(':tipoFiltro', $tipoFiltro, PDO::PARAM_INT);
        }
        $resultado->execute();
        $data = $resultado->fetchAll(PDO::FETCH_ASSOC);
        break;
    case 'lstTablas':
        $consulta = "SELECT * FROM `tabla` ORDER BY `idTabla` ASC";
        $resultado = $conexion->prepare($consulta);
        $resultado->execute();
        $data = $resultado->fetchAll(PDO::FETCH_ASSOC);
        break;
    case 'lstSocios':
        $consulta = "SELECT u.idUsuario, CONCAT(UPPER(LEFT(`p`.`nombrePersona`, 1)), UPPER(LEFT(`p`.`apellidoPersona`, 1))) AS nombre FROM persona p JOIN usuario u ON u.idPersona=p.idPersona WHERE u.idRol=2";
        $resultado = $conexion->prepare($consulta);
        $resultado->execute();
        $data = $resultado->fetchAll(PDO::FETCH_ASSOC);
        break;
    case "listarBajas":
        $consulta = "SELECT `r`.`idRuta`, `r`.`colIzquierda`, `r`.`colDerecha`, `ds`.`idDia`, `ds`.`nombreDia`, `t`.`nombreTabla` FROM `ruta` r JOIN `tabla` t JOIN `dias_semana` ds ON `t`.`idTabla` = `r`.`idTabla` AND `ds`.`idDia` = `r`.`idDia` AND `r`.`estado` = 0 ORDER BY `t`.`idTabla` ASC, `r`.`colIzquierda` ASC";
        $resultado = $conexion->prepare($consulta);
        $resultado->execute();
        $data = $resultado->fetchAll(PDO::FETCH_ASSOC);
        break;
    case "darAlta":
        $consulta = "UPDATE `ruta` SET `estado`= 1 WHERE `idRuta`= :idRuta";
        $resultado = $conexion->prepare($consulta);
        $resultado->bindParam(':idRuta', $idRuta, PDO::PARAM_INT);
        $resultado->execute();

        if($idUsuario != null){
            $con1 = "SELECT idBus FROM bus WHERE idPropietario = :idUsuario";
            $res1 = $conexion->prepare($con1);
            $res1->bindParam(":idUsuario",$idUsuario, PDO::PARAM_INT);
            $res1->execute();
            $idBus = $res1->fetch(PDO::FETCH_ASSOC);
            if($idBus != null){
                $con2 = "SELECT 
                            idAsignacionBus
                        FROM 
                            asignar_bus
                        WHERE 
                            idBus = :idBus";
                $res2 = $conexion->prepare($con2);
                $res2->bindParam(":idBus",$idBus['idBus'], PDO::PARAM_INT);
                $res2->execute();
                $asigBus = $res2->fetch(PDO::FETCH_ASSOC);

                if($asigBus != null){
                    $fecha = date("Y-m-d");
                    $con3 = "INSERT INTO `asignar_ruta`(`idAsignacionBus`, `idRuta`, `fechaAsignacionRuta`, `idGeneracion`, `confirmacion`) VALUES (:idAsignacion,:idRuta,:fecha,1,0)";
                    $res3 = $conexion->prepare($con3);
                    $res3->bindParam(":idAsignacion",$asigBus['idAsignacionBus'], PDO::PARAM_INT);
                    $res3->bindParam(":idRuta", $idRuta, PDO::PARAM_INT);
                    $res3->bindParam(":fecha", $fecha, PDO::PARAM_STR);
                    $res3->execute();
                    $data = $res3->fetchAll(PDO::FETCH_ASSOC);
                }
            }
        }

        /* Mandar al App_LOG */
        $log_consulta = "INSERT INTO `app_log`(`idUsuario`, `descripcion`, `fechaHoraAccion`) VALUES (:idUsuario,'Se ha dado de alta una ruta',:fechaAccion)";
        $Log_resultado = $conexion->prepare($log_consulta);
        $Log_resultado->bindParam(":idUsuario",$idResponsable, PDO::PARAM_INT);
        $Log_resultado->bindParam(":fechaAccion",$fechaAccion, PDO::PARAM_STR);
        $Log_resultado->execute();
        break;
}

print json_encode($data, JSON_UNESCAPED_UNICODE);
$conexion = NULL;
?>