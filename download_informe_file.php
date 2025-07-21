<?php
require_once 'config/session.php';
require_once 'models/InformeFinal.php';

// Verificar autenticación
if (!isAuthenticated()) {
    http_response_code(403);
    die('Acceso denegado');
}

// Obtener parámetros
$informe_id = $_GET['id'] ?? '';

if (empty($informe_id)) {
    http_response_code(400);
    die('ID de informe requerido');
}

// Obtener informe
$informeModel = new InformeFinal();
$informe = $informeModel->getById($informe_id);

if (!$informe) {
    http_response_code(404);
    die('Informe no encontrado');
}

// Verificar permisos
$user = getCurrentUser();
if ($user['tipo'] === 'estudiante' && $informe['estudiante_id'] != $user['id']) {
    http_response_code(403);
    die('No tienes permisos para descargar este archivo');
}

// Permitir a docentes y coordinadores descargar informes de su especialidad
if (in_array($user['tipo'], ['docente', 'coordinador']) && $informe['especialidad'] !== $user['especialidad']) {
    http_response_code(403);
    die('No tienes permisos para descargar este archivo');
}

// Construir ruta completa del archivo
$file_path = $informe['archivo_informe'];
if (empty($file_path)) {
    http_response_code(404);
    die('No hay archivo asociado a este informe');
}

// Si el archivo está en formato JSON array, obtener el primer archivo
if (str_starts_with($file_path, '[') && str_ends_with($file_path, ']')) {
    $files = json_decode($file_path, true);
    if (is_array($files) && !empty($files)) {
        $file_path = 'uploads/informes/' . $files[0]; // Primer archivo del array
    } else {
        http_response_code(404);
        die('No se pudo procesar el archivo del informe');
    }
} else {
    // Si no es JSON, asumir que es una ruta directa
    if (!str_starts_with($file_path, 'uploads/')) {
        $file_path = 'uploads/informes/' . $file_path;
    }
}

$full_path = __DIR__ . '/' . $file_path;

if (!file_exists($full_path)) {
    http_response_code(404);
    die('Archivo no encontrado en el sistema: ' . $full_path);
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

// Generar nombre amigable
$filename = "Informe_Final_" . $informe['codigo'] . "_" . date('Y') . "." . $extension;

// Limpiar cualquier output previo
if (ob_get_level()) {
    ob_end_clean();
}

// Verificar que el archivo se puede leer
if (!is_readable($full_path)) {
    http_response_code(403);
    die('No se puede leer el archivo');
}

// Enviar headers para descarga
header('Content-Type: ' . $content_type);
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($full_path));
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Forzar descarga inmediata
flush();

// Enviar archivo
readfile($full_path);
exit();
?>