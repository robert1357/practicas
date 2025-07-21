<?php
session_start();
require_once '../config/database.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'estudiante') {
    header('Location: login_estudiante.php');
    exit();
}

$database = new Database();
$pdo = $database->connect();

// Obtener el informe final del estudiante actual
$query = "SELECT i.*, u.codigo, u.nombres, u.apellidos 
          FROM informes_finales i 
          JOIN usuarios u ON i.estudiante_id = u.id 
          WHERE i.estudiante_id = ? AND i.estado = 'aprobado_final'";
$stmt = $pdo->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$informe = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$informe) {
    echo "No tienes un informe final aprobado disponible para descarga.";
    exit();
}

if (!$informe['documento_firmado']) {
    echo "Tu informe final aún no ha sido firmado por el coordinador.";
    exit();
}

$file_path = '../' . $informe['documento_firmado'];

if (!file_exists($file_path)) {
    echo "El archivo del documento firmado no se encuentra disponible.";
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