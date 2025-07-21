<?php
require_once 'config/session.php';
require_once 'models/ReporteSemanal.php';

// Verificar autenticación
if (!isAuthenticated()) {
    http_response_code(403);
    die('Acceso denegado');
}

// Obtener parámetros
$reporte_id = $_GET['reporte_id'] ?? '';
$file_name = $_GET['file_name'] ?? '';

if (empty($reporte_id) || empty($file_name)) {
    http_response_code(400);
    die('Parámetros incorrectos');
}

// Verificar que el usuario tenga acceso al reporte
$reporteModel = new ReporteSemanal();
$reporte = $reporteModel->findById($reporte_id);

if (!$reporte) {
    http_response_code(404);
    die('Reporte no encontrado');
}

// Verificar permisos
$user = getCurrentUser();
$can_access = false;

if ($user['tipo'] === 'estudiante' && $reporte['estudiante_id'] == $user['id']) {
    $can_access = true;
} elseif ($user['tipo'] === 'docente' || $user['tipo'] === 'coordinador' || $user['tipo'] === 'admin') {
    $can_access = true;
}

if (!$can_access) {
    http_response_code(403);
    die('No tienes permisos para descargar este archivo');
}

// Construir ruta completa del archivo
$full_path = __DIR__ . '/uploads/reportes/' . $file_name;

if (!file_exists($full_path)) {
    http_response_code(404);
    die('Archivo no encontrado');
}

// Determinar tipo de contenido
$extension = strtolower(pathinfo($full_path, PATHINFO_EXTENSION));
$content_types = [
    'pdf' => 'application/pdf',
    'doc' => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'txt' => 'text/plain',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'gif' => 'image/gif'
];

$content_type = $content_types[$extension] ?? 'application/octet-stream';

// Enviar headers para descarga
header('Content-Type: ' . $content_type);
header('Content-Disposition: attachment; filename="' . basename($full_path) . '"');
header('Content-Length: ' . filesize($full_path));
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Enviar archivo
readfile($full_path);
exit();
?>