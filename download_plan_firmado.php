<?php
require_once 'config/session.php';
require_once 'config/database.php';

// Verificar autenticación
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    die('No autorizado');
}

// Obtener parámetros
$plan_id = $_GET['id'] ?? null;

if (!$plan_id) {
    http_response_code(400);
    die('ID de plan requerido');
}

// Obtener plan
$database = new Database();
$pdo = $database->connect();
$query = "SELECT p.*, u.codigo, u.nombres, u.apellidos, u.especialidad 
          FROM planes_practica p 
          JOIN usuarios u ON p.estudiante_id = u.id 
          WHERE p.id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$plan_id]);
$plan = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$plan) {
    http_response_code(404);
    die('Plan no encontrado');
}

// Verificar que existe documento firmado
if (!$plan['archivo_plan_firmado']) {
    http_response_code(404);
    die('No hay plan firmado disponible');
}

// Obtener datos del usuario actual
$user_query = "SELECT * FROM usuarios WHERE id = ?";
$user_stmt = $pdo->prepare($user_query);
$user_stmt->execute([$_SESSION['user_id']]);
$user = $user_stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    http_response_code(401);
    die('Usuario no encontrado');
}

// Verificar permisos
if ($user['tipo'] === 'estudiante' && $plan['estudiante_id'] != $user['id']) {
    http_response_code(403);
    die('No tienes permisos para descargar este archivo');
}

// Permitir a coordinadores de la misma especialidad
if ($user['tipo'] === 'coordinador' && $plan['especialidad'] !== $user['especialidad']) {
    http_response_code(403);
    die('No tienes permisos para descargar este archivo');
}

// Construir ruta del archivo
$file_path = $plan['archivo_plan_firmado'];
$full_path = __DIR__ . '/' . $file_path;

if (!file_exists($full_path)) {
    http_response_code(404);
    die('Archivo no encontrado en el sistema');
}

// Verificar que el archivo se puede leer
if (!is_readable($full_path)) {
    http_response_code(403);
    die('No se puede leer el archivo');
}

// Limpiar cualquier output previo
if (ob_get_level()) {
    ob_end_clean();
}

// Generar nombre amigable
$filename = "Plan_Practica_Firmado_" . $plan['codigo'] . "_" . date('Y') . ".pdf";

// Enviar headers para descarga
header('Content-Type: application/pdf');
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