<?php
include_once '../conexion.php';
$objeto = new Conexion();
$conexion = $objeto->Conectar();

$_POST = json_decode(file_get_contents("php://input"), true);
$opcion = $_POST['opcion'];

$idTabla = (isset($_POST['idTabla'])) ? $_POST['idTabla'] : null;
$fecha = (isset($_POST['fecha'])) ? $_POST['fecha'] : null;

switch ($opcion) {
    case 'lisTablas':
        $consulta = "SELECT `idTabla` FROM `tabla` ORDER BY `idTabla` ASC ";
        $resultado = $conexion->prepare($consulta);
        $resultado->execute();
        $data=$resultado->fetchAll(PDO::FETCH_ASSOC);
    break;
    case 'lisDatos':
        $consulta = "SELECT DISTINCT
                        CONCAT(UPPER(LEFT(`p`.`nombrePersona`, 1)), UPPER(LEFT(`p`.`apellidoPersona`, 1))) AS inicialesSocio,  
                        r.colIzquierda, 
                        r.colDerecha, 
                        t.nombreTabla 
                        FROM `asignar_ruta` ar 
                        JOIN asignar_bus ab ON ar.idAsignacionBus = ab.idAsignacionBus
                        JOIN bus b ON ab.idBus = b.idBus
                        JOIN usuario u ON b.idPropietario = u.idUsuario
                        JOIN persona p ON u.idPersona = p.idPersona
                        JOIN ruta r ON ar.idRuta = r.idRuta 
                        JOIN tabla t ON r.idTabla = t.idTabla 
                        WHERE `ar`.`fechaAsignacionRuta` = :fecha AND `t`.`idTabla` = :idTabla AND `ar`.`idGeneracion` = 2
                        ORDER BY `r`.`idTabla` ASC, `r`.`colIzquierda`";
        $resultado = $conexion->prepare($consulta);
        $resultado->bindParam(':idTabla', $idTabla, PDO::PARAM_INT);
        $resultado->bindParam(':fecha', $fecha, PDO::PARAM_STR);
        $resultado->execute();
        $data=$resultado->fetchAll(PDO::FETCH_ASSOC);
        break;
    
}

print json_encode($data, JSON_UNESCAPED_UNICODE);
$conexion = NULL;
?>