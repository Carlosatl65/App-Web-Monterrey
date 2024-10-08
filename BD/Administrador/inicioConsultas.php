<?php
include_once '../conexion.php';
$objeto = new Conexion();
$conexion = $objeto->Conectar();

$_POST = json_decode(file_get_contents("php://input"), true);
$opcion = $_POST['opcion'];

switch ($opcion) {
    case 'listarUsuario':
        $consulta = "SELECT COUNT(*) AS TotalUsuarios FROM `usuario` WHERE estado = 1";
        $resultado = $conexion->prepare($consulta);
        $resultado->execute();
        $data=$resultado->fetchAll(PDO::FETCH_ASSOC);
    break;
    case 'listarBus':
        $consulta = "SELECT COUNT(*) AS TotalBuses FROM `bus` WHERE idEstado = 1";
        $resultado = $conexion->prepare($consulta);
        $resultado->execute();
        $data=$resultado->fetchAll(PDO::FETCH_ASSOC);
    break;
    case 'listarRutas':
        $consulta = "SELECT 
                        (SELECT COUNT(*) FROM `ruta` WHERE `idDia`=1) AS lunesVienes,
                        (SELECT COUNT(*) FROM `ruta` WHERE `idDia`=2) AS sabado,
                        (SELECT COUNT(*) FROM `ruta` WHERE `idDia`=3) AS domingo;";
        $resultado = $conexion->prepare($consulta);
        $resultado->execute();
        $data=$resultado->fetchAll(PDO::FETCH_ASSOC);
    break;
    
}

print json_encode($data, JSON_UNESCAPED_UNICODE);
$conexion = NULL;
?>