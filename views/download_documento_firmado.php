<?php
session_start();
require_once '../config/database.php';
require_once '../models/InformeFinal.php';

// Verificar autenticación
if (!isset($_SESSION['user_id'])) {
    header('Location: login_estudiante.php');
    exit();
}

// Obtener parámetros
$informe_id = $_GET['id'] ?? null;

if (!$informe_id) {
    http_response_code(400);
    die('ID de informe requerido');
}

// Obtener informe
$database = new Database();
$pdo = $database->connect();
$query = "SELECT i.*, u.codigo, u.nombres, u.apellidos, u.especialidad 
          FROM informes_finales i 
          JOIN usuarios u ON i.estudiante_id = u.id 
          WHERE i.id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$informe_id]);
$informe = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$informe) {
    http_response_code(404);
    die('Informe no encontrado');
}

// Verificar que existe documento firmado
if (!$informe['documento_firmado']) {
    http_response_code(404);
    die('No hay documento firmado disponible');
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
if ($user['tipo'] === 'estudiante' && $informe['estudiante_id'] != $user['id']) {
    http_response_code(403);
    die('No tienes permisos para descargar este archivo');
}

// Permitir a coordinadores de la misma especialidad
if ($user['tipo'] === 'coordinador' && $informe['especialidad'] !== $user['especialidad']) {
    http_response_code(403);
    die('No tienes permisos para descargar este archivo');
}

// Construir ruta del archivo
$file_path = '../' . $informe['documento_firmado'];
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
$filename = "Informe_Final_Firmado_" . $informe['codigo'] . "_" . date('Y') . ".pdf";

// Limpiar output buffer
while (ob_get_level()) {
    ob_end_clean();
}

// Enviar headers para descarga
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($full_path));
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
header('Accept-Ranges: bytes');

// Forzar descarga inmediata
flush();

// Enviar archivo en chunks para archivos grandes
$handle = fopen($full_path, 'rb');
while (!feof($handle)) {
    echo fread($handle, 8192);
    flush();
}
fclose($handle);
exit();
?>