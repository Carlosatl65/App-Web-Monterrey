<?php
// Obtener los datos de la solicitud
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['pdf'])) {
    $pdfData = $data['pdf'];
    $decoded = base64_decode($pdfData);
    $filePath = 'generated.pdf'; //nombre del archivo PDF

    if (file_put_contents($filePath, $decoded)) {
        echo json_encode(['url' => $filePath]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Error al guardar el PDF']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'No se recibió el PDF']);
}
?>
