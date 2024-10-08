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

$sortedData = (isset($_POST['sortedData'])) ? $_POST['sortedData'] : null;

switch($opcion){
    case "listar":
        $consulta = "SELECT CONCAT(UPPER(LEFT(`p`.`nombrePersona`, 1)), UPPER(LEFT(`p`.`apellidoPersona`, 1)),' - Flota #', `b`.`numeroBus`) AS infoBuses, `b`.`idBus` 
                     FROM `bus` b 
                     JOIN `usuario` u 
                     JOIN `persona` p 
                     ON `u`.`idPersona` = `p`.`idPersona` 
                     AND `b`.`idPropietario` = `u`.`idUsuario`
                     AND `b`.`idTipoUnidad` = 2 
                     AND `b`.`idEstado` IN ('1', '2')
                     ORDER BY `b`.`ordenAsigRuta` ASC";
        $resultado = $conexion->prepare($consulta);
        $resultado->execute();
        $data = $resultado->fetchAll(PDO::FETCH_ASSOC);
        break;
    case "ordenLista":
        foreach ($sortedData as $item) {
            $consulta = "UPDATE `bus` SET `ordenAsigRuta` = :orden WHERE `idBus` = :id";
            $resultado = $conexion->prepare($consulta);
            $resultado->bindParam(':orden', $item['orden'], PDO::PARAM_INT);
            $resultado->bindParam(':id', $item['id'], PDO::PARAM_INT);
            $resultado->execute();
        }

        /* Mandar al App_LOG */
        $log_consulta = "INSERT INTO `app_log`(`idUsuario`, `descripcion`, `fechaHoraAccion`) VALUES (:idUsuario,'Se ha cambiado el orden de las rancheras',:fechaAccion)";
        $Log_resultado = $conexion->prepare($log_consulta);
        $Log_resultado->bindParam(":idUsuario",$idResponsable, PDO::PARAM_INT);
        $Log_resultado->bindParam(":fechaAccion",$fechaAccion, PDO::PARAM_STR);
        $Log_resultado->execute();
        break;
}

print json_encode($data, JSON_UNESCAPED_UNICODE);
$conexion = NULL;
?>