<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_FILES['image'])) {
        $file = $_FILES['image'];
        $cedulaPersona = $_SESSION['cedulaPersona'];
        
        // Validar tipo de archivo
        $allowedTypes = ['image/jpeg'];
        if (!in_array($file['type'], $allowedTypes)) {
            echo json_encode(['message' => 'Invalid file type']);
            exit;
        }

        // Directorio de uploads
        $uploadDirectory = __DIR__ . '/../uploads/'; // Ruta absoluta al directorio de uploads
        if (!is_dir($uploadDirectory)) {
            mkdir($uploadDirectory, 0777, true); // Crea el directorio si no existe
        }
        
        $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $uploadPath = $uploadDirectory . $cedulaPersona . '.' . $fileExtension;

        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            // Actualizar la base de datos con la ruta de la imagen
            include_once 'conexion.php';
            $objeto = new Conexion();
            $conexion = $objeto->Conectar();
            
            $relativeUploadPath = 'uploads/' . $cedulaPersona . '.' . $fileExtension; // Ruta relativa para almacenar en la base de datos
            
            $consulta = "UPDATE `persona` SET `imagen` = :rutaImagen WHERE `cedulaPersona` = :cedulaPersona";
            $resultado = $conexion->prepare($consulta);
            $resultado->bindParam(':rutaImagen', $relativeUploadPath, PDO::PARAM_STR);
            $resultado->bindParam(':cedulaPersona', $cedulaPersona, PDO::PARAM_STR);
            $resultado->execute();

            echo json_encode(['message' => 'File uploaded successfully']);
        } else {
            echo json_encode(['message' => 'Failed to move uploaded file']);
        }
    } else {
        echo json_encode(['message' => 'No file uploaded']);
    }
} else {
    echo json_encode(['message' => 'Invalid request method']);
}

$conexion = NULL;
?>
