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

$idPersona = (isset($_POST['idPersona'])) ? $_POST['idPersona'] : null;
$idUsuario = (isset($_POST['idUsuario'])) ? $_POST['idUsuario'] : null;
$cedulaPersona = (isset($_POST['cedulaPersona'])) ? $_POST['cedulaPersona'] : null;
$nombrePersona = (isset($_POST['nombrePersona'])) ? $_POST['nombrePersona'] : null;
$apellidoPersona = (isset($_POST['apellidoPersona'])) ? $_POST['apellidoPersona'] : null;
$correoPersona = (isset($_POST['correoPersona'])) ? $_POST['correoPersona'] : null;
$telefonoPersona = (isset($_POST['telefonoPersona'])) ? $_POST['telefonoPersona'] : null;
$contrasenaUsuario = (isset($_POST['contrasenaUsuario'])) ? $_POST['contrasenaUsuario'] : null;
$idRol = (isset($_POST['idRol'])) ? $_POST['idRol'] : null;



$buscar = (isset($_POST['buscar'])) ? $_POST['buscar'] : null;

switch($opcion){
    case "crear":
        $consultaPersona = "INSERT INTO `persona` (`cedulaPersona`, `nombrePersona`, `apellidoPersona`, `correoPersona`, `telefonoPersona`, `imagen`) VALUES (:cedulaPersona, :nombrePersona, :apellidoPersona, :correoPersona, :telefonoPersona, 'uploads/usuario.jpg')";
        $resultadoPersona = $conexion->prepare($consultaPersona);
        $resultadoPersona->bindParam(':cedulaPersona', $cedulaPersona, PDO::PARAM_STR);
        $resultadoPersona->bindParam(':nombrePersona', $nombrePersona, PDO::PARAM_STR);
        $resultadoPersona->bindParam(':apellidoPersona', $apellidoPersona, PDO::PARAM_STR);
        $resultadoPersona->bindParam(':correoPersona', $correoPersona, PDO::PARAM_STR);
        $resultadoPersona->bindParam(':telefonoPersona', $telefonoPersona, PDO::PARAM_STR);
        $resultadoPersona->execute();

        $criptoContrasena = hash('sha512', $contrasenaUsuario);
        
        $consultaUsuario = "INSERT INTO `usuario` (`contrasenaUsuario`, `idPersona`, `idRol`, `estado`) SELECT :contrasenaUsuario, LAST_INSERT_ID(), :idRol, '1' FROM `persona` WHERE `cedulaPersona` = :cedulaPersona";
        $resultadoUsuario = $conexion->prepare($consultaUsuario);
        $resultadoUsuario->bindParam(':contrasenaUsuario', $criptoContrasena, PDO::PARAM_STR);
        $resultadoUsuario->bindParam(':idRol', $idRol, PDO::PARAM_INT);
        $resultadoUsuario->bindParam(':cedulaPersona', $cedulaPersona, PDO::PARAM_STR);
        $resultadoUsuario->execute();

        /* Mandar al App_LOG */
        $log_consulta = "INSERT INTO `app_log`(`idUsuario`, `descripcion`, `fechaHoraAccion`) VALUES (:idUsuario,'Se ha ingresado un nuevo usuario en la app',:fechaAccion)";
        $Log_resultado = $conexion->prepare($log_consulta);
        $Log_resultado->bindParam(":idUsuario",$idResponsable, PDO::PARAM_INT);
        $Log_resultado->bindParam(":fechaAccion",$fechaAccion, PDO::PARAM_STR);
        $Log_resultado->execute();
        
        $data = $resultadoUsuario->fetchAll(PDO::FETCH_ASSOC);
        break;
    case "editar":
        $consulta = "UPDATE `persona` p INNER JOIN `usuario` u ON p.idPersona = u.idPersona SET p.nombrePersona = :nombrePersona, p.apellidoPersona = :apellidoPersona, p.correoPersona = :correoPersona, p.telefonoPersona = :telefonoPersona, u.idRol = :idRol WHERE p.idPersona = :idPersona";
        $resultado = $conexion->prepare($consulta);
        $resultado->bindParam(':nombrePersona', $nombrePersona, PDO::PARAM_STR);
        $resultado->bindParam(':apellidoPersona', $apellidoPersona, PDO::PARAM_STR);
        $resultado->bindParam(':correoPersona', $correoPersona, PDO::PARAM_STR);
        $resultado->bindParam(':telefonoPersona', $telefonoPersona, PDO::PARAM_STR);
        $resultado->bindParam(':idRol', $idRol, PDO::PARAM_INT);
        $resultado->bindParam(':idPersona', $idPersona, PDO::PARAM_INT);
        $resultado->execute();
        $data = $resultado->fetchAll(PDO::FETCH_ASSOC);

        /* Mandar al App_LOG */
        $log_consulta = "INSERT INTO `app_log`(`idUsuario`, `descripcion`, `fechaHoraAccion`) VALUES (:idUsuario,'Se han editado los datos de un usuario',:fechaAccion)";
        $Log_resultado = $conexion->prepare($log_consulta);
        $Log_resultado->bindParam(":idUsuario",$idResponsable, PDO::PARAM_INT);
        $Log_resultado->bindParam(":fechaAccion",$fechaAccion, PDO::PARAM_STR);
        $Log_resultado->execute();
        break;
    case "eliminar":

        // Obtener el rol del usuario
        $consultaID = "SELECT idRol, idPersona FROM usuario WHERE idUsuario = :idUsuario";
        $resID = $conexion->prepare($consultaID);
        $resID->bindParam(":idUsuario",$idUsuario, PDO::PARAM_INT);
        $resID->execute();
        $user = $resID->fetch(PDO::FETCH_ASSOC);

        if ($user['idRol'] == 1) {
            // Si el rol es 1, dar de baja el usuario
            $consultabaja = "UPDATE usuario SET estado = 0 WHERE idUsuario = :idUsuario";
            $resBaja = $conexion->prepare($consultabaja);
            $resBaja->bindParam(":idUsuario",$idUsuario, PDO::PARAM_INT);
            $resBaja->execute();
            $data = $resBaja->fetchAll(PDO::FETCH_ASSOC);

            /* Mandar al App_LOG */
            $log_consulta = "INSERT INTO `app_log`(`idUsuario`, `descripcion`, `fechaHoraAccion`) VALUES (:idUsuario,'Se ha dado de baja un usuario administrador',:fechaAccion)";
            $Log_resultado = $conexion->prepare($log_consulta);
            $Log_resultado->bindParam(":idUsuario",$idResponsable, PDO::PARAM_INT);
            $Log_resultado->bindParam(":fechaAccion",$fechaAccion, PDO::PARAM_STR);
            $Log_resultado->execute();

        } else if (in_array($user['idRol'], [2, 3])) {
            // Si el rol es 2 o 3, comprobar si está asignado a algún bus
            $consultaDueno = "SELECT idEstado FROM bus WHERE idPropietario = :idUsuario";
            $resDueno = $conexion->prepare($consultaDueno);
            $resDueno->bindParam(":idUsuario",$idUsuario, PDO::PARAM_INT);
            $resDueno->execute();
            $busesPropietario = $resDueno->fetchAll(PDO::FETCH_ASSOC);
            
            $consultaChofer = "SELECT b.idEstado 
                               FROM asignar_bus ab
                               JOIN bus b ON ab.idBus = b.idBus
                               WHERE ab.idUsuario = :idUsuario";
            $resChofer = $conexion->prepare($consultaChofer);
            $resChofer->bindParam(":idUsuario",$idUsuario, PDO::PARAM_INT);
            $resChofer->execute();
            $busesChofer = $resChofer->fetchAll(PDO::FETCH_ASSOC);

            $buses = array_merge($busesPropietario, $busesChofer);

            if (empty($buses)) {
                // Si no está asignado a ningún bus, eliminar el usuario y la persona asociada
                $consultaBorrarUsuario = "DELETE FROM usuario WHERE idUsuario = :idUsuario";
                $resUsuario = $conexion->prepare($consultaBorrarUsuario);
                $resUsuario->bindParam(":idUsuario",$idUsuario, PDO::PARAM_INT);
                $resUsuario->execute();

                $consultaBorrarPersona = "DELETE FROM persona WHERE idPersona = :idPersona";
                $resPersona = $conexion->prepare($consultaBorrarPersona);
                $resPersona->bindParam(":idPersona",$user['idPersona'], PDO::PARAM_INT);
                $resPersona->execute();

                $data = $resPersona->fetch(PDO::FETCH_ASSOC);

                /* Mandar al App_LOG */
                $log_consulta = "INSERT INTO `app_log`(`idUsuario`, `descripcion`, `fechaHoraAccion`) VALUES (:idUsuario,'Se ha eliminado un usuario y datos personales',:fechaAccion)";
                $Log_resultado = $conexion->prepare($log_consulta);
                $Log_resultado->bindParam(":idUsuario",$idResponsable, PDO::PARAM_INT);
                $Log_resultado->bindParam(":fechaAccion",$fechaAccion, PDO::PARAM_STR);
                $Log_resultado->execute();
            } else {
                $busActivo = false;
                foreach ($buses as $bus) {
                    if (in_array($bus['idEstado'], [1, 2])) {
                        $busActivo = true;
                        break;
                    }
                }
    
                if ($busActivo) {
                    // Si el bus está activo o en mantenimiento, mostrar un mensaje de error
                    $data = "Error: Primero debe dar de baja el bus";
                } else {
                    // Si el bus está dado de baja, dar de baja el usuario
                    $consulta = "UPDATE usuario SET estado = 0 WHERE idUsuario = :idUsuario";
                    $res = $conexion->prepare($consulta);
                    $res->bindParam(":idUsuario",$idUsuario, PDO::PARAM_INT);
                    $res->execute();
                    $data = $res->fetch(PDO::FETCH_ASSOC);

                    /* Mandar al App_LOG */
                    $log_consulta = "INSERT INTO `app_log`(`idUsuario`, `descripcion`, `fechaHoraAccion`) VALUES (:idUsuario,'Se ha dado de baja un usuario',:fechaAccion)";
                    $Log_resultado = $conexion->prepare($log_consulta);
                    $Log_resultado->bindParam(":idUsuario",$idResponsable, PDO::PARAM_INT);
                    $Log_resultado->bindParam(":fechaAccion",$fechaAccion, PDO::PARAM_STR);
                    $Log_resultado->execute();
                }
            }
        }

        break;
    case "listar":
        $consulta = "SELECT `usuario`.`idUsuario`, `persona`.`idPersona`, `persona`.`cedulaPersona`, `persona`.`nombrePersona`, `persona`.`apellidoPersona`, CONCAT(`persona`.`nombrePersona`,' ',`persona`.`apellidoPersona`) nombreCompleto,`persona`.`telefonoPersona`, `persona`.`correoPersona`, `usuario`.`idRol`, `rol`.`nombreRol`  FROM `persona`, `usuario`, `rol` WHERE (`usuario`.`idRol` = `rol`.`idRol` and `usuario`.`idPersona` = `persona`.`idPersona`) and `usuario`.`estado` = '1' ORDER BY `persona`.`idPersona` ASC";
        $resultado = $conexion->prepare($consulta);
        $resultado->execute();
        $data = $resultado->fetchAll(PDO::FETCH_ASSOC);
        break;
    case "buscar":
        $consulta = "SELECT `usuario`.`idUsuario`, `persona`.`idPersona`, `persona`.`cedulaPersona`, `persona`.`nombrePersona`, `persona`.`apellidoPersona`, CONCAT(`persona`.`nombrePersona`,' ',`persona`.`apellidoPersona`) nombreCompleto, `persona`.`telefonoPersona`, `persona`.`correoPersona`, `usuario`.`idRol`, `rol`.`nombreRol` FROM `persona`, `usuario`, `rol` WHERE (`usuario`.`idRol` = `rol`.`idRol` and `usuario`.`idPersona` = `persona`.`idPersona`) and (`persona`.`nombrePersona` LIKE :buscar OR `persona`.`apellidoPersona` LIKE :buscar) and `usuario`.`estado` = '1' ORDER BY `persona`.`idPersona` ASC";
        $resultado = $conexion->prepare($consulta);
        $buscarParam = "%".$buscar."%";
        $resultado->bindParam(':buscar', $buscarParam, PDO::PARAM_STR);
        $resultado->execute();
        $data = $resultado->fetchAll(PDO::FETCH_ASSOC);
        break;
    case 'lstRol':
        $consulta = "SELECT * FROM `rol`";
        $resultado = $conexion->prepare($consulta);
        $resultado->execute();
        $data = $resultado->fetchAll(PDO::FETCH_ASSOC);
        break;
    case 'editarContrasena':
        $criptoContrasena = hash('sha512', $contrasenaUsuario);
        
        $consulta = "UPDATE `persona` p INNER JOIN `usuario` u ON p.idPersona = u.idPersona SET u.contrasenaUsuario = :contrasenaUsuario WHERE p.idPersona = :idPersona";
        $resultado = $conexion->prepare($consulta);
        $resultado->bindParam(':contrasenaUsuario', $criptoContrasena, PDO::PARAM_STR);
        $resultado->bindParam(':idPersona', $idPersona, PDO::PARAM_INT);
        $resultado->execute();
        $data = $resultado->fetchAll(PDO::FETCH_ASSOC);
        
        /* Mandar al App_LOG */
        $log_consulta = "INSERT INTO `app_log`(`idUsuario`, `descripcion`, `fechaHoraAccion`) VALUES (:idUsuario,'Se ha modificado la contraseña de un usuario',:fechaAccion)";
        $Log_resultado = $conexion->prepare($log_consulta);
        $Log_resultado->bindParam(":idUsuario",$idResponsable, PDO::PARAM_INT);
        $Log_resultado->bindParam(":fechaAccion",$fechaAccion, PDO::PARAM_STR);
        $Log_resultado->execute();
        break;
    case "listarBajas":
        $consulta = "SELECT `usuario`.`idUsuario`, CONCAT(`persona`.`nombrePersona`,' ',`persona`.`apellidoPersona`) nombreCompleto, `rol`.`nombreRol`  FROM `persona`, `usuario`, `rol` WHERE (`usuario`.`idRol` = `rol`.`idRol` and `usuario`.`idPersona` = `persona`.`idPersona`) and `usuario`.`estado` = '0' ORDER BY `persona`.`idPersona` ASC";
        $resultado = $conexion->prepare($consulta);
        $resultado->execute();
        $data = $resultado->fetchAll(PDO::FETCH_ASSOC);
        break;
    case "darAlta":
        $consulta = "UPDATE `usuario` SET `estado`='1' WHERE `idUsuario`= :idUsuario";
        $resultado = $conexion->prepare($consulta);
        $resultado->bindParam(':idUsuario', $idUsuario, PDO::PARAM_INT);
        $resultado->execute();
        $data = $resultado->fetchAll(PDO::FETCH_ASSOC);

        /* Mandar al App_LOG */
        $log_consulta = "INSERT INTO `app_log`(`idUsuario`, `descripcion`, `fechaHoraAccion`) VALUES (:idUsuario,'Se ha dado de alta a un usuario',:fechaAccion)";
        $Log_resultado = $conexion->prepare($log_consulta);
        $Log_resultado->bindParam(":idUsuario",$idResponsable, PDO::PARAM_INT);
        $Log_resultado->bindParam(":fechaAccion",$fechaAccion, PDO::PARAM_STR);
        $Log_resultado->execute();
        break;
}
print json_encode($data, JSON_UNESCAPED_UNICODE);
$conexion = NULL;
?>