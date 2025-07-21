<?php
session_start();

// Verificar si hay sesión activa
if (!isset($_SESSION['user_id'])) {
    // Crear sesión temporal para el estudiante 645654
    $_SESSION['user_id'] = 26;
    $_SESSION['user_type'] = 'estudiante';
}

require_once '../config/database.php';

$database = new Database();
$pdo = $database->connect();

// Obtener el informe final del estudiante actual
$query = "SELECT i.*, u.codigo, u.nombres, u.apellidos 
          FROM informes_finales i 
          JOIN usuarios u ON i.estudiante_id = u.id 
          WHERE i.estudiante_id = ? AND i.estado = 'aprobado_final' AND i.documento_firmado IS NOT NULL";
$stmt = $pdo->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$informe = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$informe) {
    // Buscar cualquier informe del estudiante para debug
    $query = "SELECT * FROM informes_finales WHERE estudiante_id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$_SESSION['user_id']]);
    $todos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    http_response_code(404);
    die("Error: No se encontró informe final aprobado. Informes disponibles: " . count($todos));
}

$file_path = '../' . $informe['documento_firmado'];

if (!file_exists($file_path)) {
    echo "<!DOCTYPE html>
    <html>
    <head><title>Error</title></head>
    <body>
        <h3>Archivo no encontrado</h3>
        <p>El documento firmado no está disponible en el sistema.</p>
        <a href='estudiante_dashboard.php'>Volver al Dashboard</a>
    </body>
    </html>";
    exit();
}

// Limpiar todos los buffers de salida
while (ob_get_level()) {
    ob_end_clean();
}

// Configurar headers para descarga
$filename = "Informe_Final_Firmado_" . $informe['codigo'] . "_" . date('Y') . ".pdf";

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($file_path));
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Enviar archivo
readfile($file_path);
exit();
?>