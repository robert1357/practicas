<?php
require_once 'config/session.php';
require_once 'models/DocumentoReglamento.php';

// Verificar autenticación
if (!isAuthenticated()) {
    http_response_code(403);
    die('Acceso denegado');
}

// Obtener parámetros
$file_path = $_GET['file'] ?? '';
$doc_id = $_GET['doc_id'] ?? '';

if (empty($file_path) || empty($doc_id)) {
    http_response_code(400);
    die('Parámetros incorrectos');
}

// Verificar que el usuario tenga acceso al documento
$documentoModel = new DocumentoReglamento();
$documento = $documentoModel->findById($doc_id);

if (!$documento) {
    http_response_code(404);
    die('Documento no encontrado');
}

// Verificar permisos por especialidad
$user = getCurrentUser();
if ($user['tipo'] === 'estudiante' && $documento['especialidad'] !== $user['especialidad']) {
    http_response_code(403);
    die('No tienes permisos para descargar este documento');
}

// Construir ruta completa del archivo
$full_path = __DIR__ . '/' . $file_path;

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
    'txt' => 'text/plain'
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