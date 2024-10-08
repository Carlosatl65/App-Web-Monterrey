<?php
/* Se crea la conexión a la base de datos */
include_once 'BD/conexion.php';
$objeto = new Conexion();
$conexion = $objeto->Conectar();

/* Desencriptar los datos */
$_POST = json_decode(file_get_contents("php://input"), true);

/* Guardar parametros enviados en variables nuevas, si no existe información la variable tiene el valor null */
$usuario = (isset($_POST['usuario'])) ? $_POST['usuario'] : null;
$contraseña = (isset($_POST['contraseña'])) ? $_POST['contraseña'] : null;

/* Encriptar contraseña que se envia con sha512 */
$criptoContrasena = hash('sha512', $contraseña);

/* Consulta preparada para verificar si los datos proporcionados de login son correctos 
   En la consulta se obtiene nombre del usuario, cédula, nombre de rol asignado, id de la persona y ruta de imagen de usuario*/
$consulta = "SELECT CONCAT(SUBSTRING_INDEX(`p`.`nombrePersona`, ' ', 1),' ',SUBSTRING_INDEX(`p`.`apellidoPersona`, ' ', 1)) AS nombreUsuario, `p`.`cedulaPersona`, `r`.`nombreRol` as rol, `p`.`idPersona`, `p`.`imagen` FROM `persona` p LEFT JOIN `usuario` u on `u`.`idPersona` = `p`.`idPersona` LEFT JOIN `rol` r on `r`.`idRol`=`u`.`idRol` WHERE `p`.`correoPersona` = :usuario AND `u`.`contrasenaUsuario` = :contrasena AND `u`.`estado` = '1'";
$resultado = $conexion->prepare($consulta);
$resultado->bindParam(":usuario", $usuario, PDO::PARAM_STR);
$resultado->bindParam(":contrasena", $criptoContrasena, PDO::PARAM_STR);
$resultado->execute();

$data = $resultado->fetch(PDO::FETCH_ASSOC);

/* Si los datos de login son correctos y se encuentran datos, se los guarda en la sesión activa */
if($data){    
    session_start();
    $_SESSION['idPersona'] = $data['idPersona'];
    $_SESSION['cedulaPersona'] = $data['cedulaPersona'];
    $_SESSION['nombreUsuario'] = $data['nombreUsuario'];
    $_SESSION['rol'] = $data['rol'];
    $_SESSION['imagen'] = $data['imagen'];
}   

/* Encriptar los datos */
print json_encode($data, JSON_UNESCAPED_UNICODE);
        
$conexion = NULL;
?>