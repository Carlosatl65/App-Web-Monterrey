<?php
//Conexión con la bd
include_once 'conexion.php';
$objeto = new Conexion();
$conexion = $objeto->Conectar();

// Decodifica el cuerpo de la solicitud HTTP recibida en formato JSON y lo convierte en un array asociativo de PHP.
// Al establecer el segundo parámetro de json_decode() como true, el JSON se convierte en un array en lugar de un objeto.
$_POST = json_decode(file_get_contents("php://input"), true);
session_start(); //Iniciar sesión para extraer datos
$idPersona = $_SESSION['idPersona']; //Recupera el id de la persona que ingresó al sistema y lo guarda en una variable
$opcion = $_POST['opcion']; //Recupera la opción enviada por POST

$correoUsuario = (isset($_POST['correoUsuario'])) ? $_POST['correoUsuario'] : null; //Recupera el correo enviado por POST
$contrasenaUsuario = (isset($_POST['contrasenaUsuario'])) ? $_POST['contrasenaUsuario'] : null; //Recupera la contraseña enviada por POST
$telefonoUsuario = (isset($_POST['telefonoUsuario'])) ? $_POST['telefonoUsuario'] : null; //Recupera el teléfono enviado por POST

//Dependiendo de la opción se ejecuta una consulta de SQL
/* Prepara y ejecuta una consulta SQL utilizando PhpDataObjects y sentencias preparadas para evitar inyecciones SQL.
   Se vincula un parámetro a la consulta y se ejecuta, obteniendo los resultados como un array asociativo. */
switch ($opcion) {
    case 'listar':
        $consulta = "SELECT * FROM `persona` WHERE `idPersona` = :idPersona";
        $resultado = $conexion->prepare($consulta);
        $resultado->bindParam(':idPersona', $idPersona, PDO::PARAM_INT);
        $resultado->execute();
        $data=$resultado->fetchAll(PDO::FETCH_ASSOC);
    break;
    case 'actualizarDatos':
        $consulta = "UPDATE `persona` SET `correoPersona`= :correoUsuario, `telefonoPersona`= :telefonoUsuario WHERE `idPersona`= :idPersona";
        $resultado = $conexion->prepare($consulta);
        $resultado->bindParam(':correoUsuario', $correoUsuario, PDO::PARAM_STR);
        $resultado->bindParam(':telefonoUsuario', $telefonoUsuario, PDO::PARAM_STR);
        $resultado->bindParam(':idPersona', $idPersona, PDO::PARAM_INT);
        $resultado->execute();
    break;
    case 'editarContrasena':
        $criptoContrasena = hash('sha512', $contrasenaUsuario); //Encriptación de contraseña a sha512
        
        $consulta = "UPDATE `persona` p INNER JOIN `usuario` u ON p.idPersona = u.idPersona SET u.contrasenaUsuario = :contrasenaUsuario WHERE p.idPersona = :idPersona";
        $resultado = $conexion->prepare($consulta);
        $resultado->bindParam(':contrasenaUsuario', $criptoContrasena, PDO::PARAM_STR);
        $resultado->bindParam(':idPersona', $idPersona, PDO::PARAM_INT);
        $resultado->execute();
        $data = $resultado->fetchAll(PDO::FETCH_ASSOC);
        break;
}

// Convierte el array asociativo $data a formato JSON, preservando caracteres Unicode sin escapar.
print json_encode($data, JSON_UNESCAPED_UNICODE); 
$conexion = NULL; //Cierra la conexión a la base de datos asignando NULL al objeto de conexión PDO.
?>