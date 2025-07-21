<?php
require_once 'config/session.php';
require_once 'models/PlanPractica.php';

// Verificar autenticación
if (!isAuthenticated()) {
    http_response_code(403);
    die('Acceso denegado');
}

// Obtener parámetros
$file_path = $_GET['file'] ?? '';
$plan_id = $_GET['plan_id'] ?? '';
$estudiante_id = $_GET['estudiante'] ?? '';

if (empty($file_path) || (empty($plan_id) && empty($estudiante_id))) {
    http_response_code(400);
    die('Parámetros incorrectos');
}

// Verificar que el usuario tenga acceso al plan
$planModel = new PlanPractica();

if (!empty($plan_id)) {
    $plan = $planModel->getById($plan_id);
} else {
    $plan = $planModel->getByEstudiante($estudiante_id);
}

if (!$plan) {
    http_response_code(404);
    die('Plan no encontrado');
}

// Verificar permisos
$user = getCurrentUser();
$hasAccess = false;

switch ($user['tipo']) {
    case 'estudiante':
        $hasAccess = ($plan['estudiante_id'] == $user['id']);
        break;
    case 'docente':
    case 'coordinador':
    case 'admin':
        $hasAccess = true;
        break;
}

if (!$hasAccess) {
    http_response_code(403);
    die('No tienes permisos para descargar este archivo');
}

// Construir ruta completa del archivo
// Verificar en diferentes ubicaciones posibles
$possible_paths = [
    __DIR__ . '/' . $file_path,                    // Ruta original
    __DIR__ . '/views/' . $file_path,              // Ruta con views/
    __DIR__ . '/' . str_replace('uploads/', 'views/uploads/', $file_path)  // Forzar views/uploads/
];

$full_path = null;
foreach ($possible_paths as $path) {
    if (file_exists($path)) {
        $full_path = $path;
        break;
    }
}

if (!$full_path) {
    http_response_code(404);
    die('Archivo no encontrado. Rutas buscadas: ' . implode(', ', $possible_paths));
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

// Verificar si es para ver o descargar
$view_mode = isset($_GET['view']) && $_GET['view'] == '1';

// Crear nombre de archivo más descriptivo
$estudiante_info = $plan['nombres'] . '_' . $plan['apellidos'];
$fecha_registro = $plan['fecha_creacion'] ?? $plan['fecha_registro'] ?? date('Y-m-d');
$filename = 'Plan_' . $estudiante_info . '_' . date('Y-m-d', strtotime($fecha_registro)) . '.' . $extension;

// Enviar headers
header('Content-Type: ' . $content_type);

if ($view_mode && $extension === 'pdf') {
    // Para visualizar PDF en el navegador
    header('Content-Disposition: inline; filename="' . $filename . '"');
} else {
    // Para descargar
    header('Content-Disposition: attachment; filename="' . $filename . '"');
}

header('Content-Length: ' . filesize($full_path));
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Enviar archivo
readfile($full_path);
exit();
?>