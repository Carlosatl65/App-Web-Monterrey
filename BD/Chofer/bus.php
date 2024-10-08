<?php
include_once '../conexion.php';
$objeto = new Conexion();
$conexion = $objeto->Conectar();

$_POST = json_decode(file_get_contents("php://input"), true);
$opcion = $_POST['opcion'];


$idBus = (isset($_POST['idBus'])) ? $_POST['idBus'] : null;
$idEstadoBus = (isset($_POST['idEstadoBus'])) ? $_POST['idEstadoBus'] : null;

session_start();
$idPersona = $_SESSION['idPersona'];


switch($opcion){   

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
                        b.idEstado IN ('1', '2') AND p2.idPersona = :idPersona
                    ORDER BY 
                        b.numeroBus ASC";
        $resultado = $conexion->prepare($consulta);
        $resultado->bindParam(':idPersona', $idPersona, PDO::PARAM_INT);
        $resultado->execute();
        $data = $resultado->fetchAll(PDO::FETCH_ASSOC);
        break;
    
    case "editarEstadoBus":
        $consulta = "UPDATE `bus` SET `idEstado`= :idEstadoBus WHERE `idBus`= :idBus";
        $resultado = $conexion->prepare($consulta);
        $resultado->bindParam(':idEstadoBus', $idEstadoBus, PDO::PARAM_INT);
        $resultado->bindParam(':idBus', $idBus, PDO::PARAM_INT);
        $resultado->execute();
        $data = $resultado->fetchAll(PDO::FETCH_ASSOC);
        break;
}

print json_encode($data, JSON_UNESCAPED_UNICODE);
$conexion = NULL;
?>