<?php
require_once '../config/session.php';
require_once '../models/InformeFinal.php';
require_once '../models/User.php';

// Verificar autenticación
if (!isAuthenticated() || !hasRole('coordinador')) {
    header("Location: login_coordinador.php");
    exit();
}

$informeModel = new InformeFinal();
$userModel = new User();

// Obtener ID del estudiante
$estudiante_id = $_GET['estudiante'] ?? null;

if (!$estudiante_id) {
    header("Location: coordinador_dashboard.php");
    exit();
}

// Obtener datos del estudiante
$estudiante = $userModel->getById($estudiante_id);
$informe = $informeModel->getByEstudiante($estudiante_id);

if (!$estudiante || !$informe) {
    header("Location: coordinador_dashboard.php");
    exit();
}

// Procesar aprobación/rechazo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $comentarios = $_POST['comentarios'] ?? '';
    
    if ($accion === 'aprobar') {
        $documento_firmado = null;
        
        // Procesar archivo firmado si se subió
        if (isset($_FILES['documento_firmado']) && $_FILES['documento_firmado']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/documentos_firmados/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $extension = pathinfo($_FILES['documento_firmado']['name'], PATHINFO_EXTENSION);
            $nombreArchivo = 'informe_firmado_' . $informe['id'] . '_' . time() . '.' . $extension;
            $rutaArchivo = $uploadDir . $nombreArchivo;
            
            if (move_uploaded_file($_FILES['documento_firmado']['tmp_name'], $rutaArchivo)) {
                $documento_firmado = 'uploads/documentos_firmados/' . $nombreArchivo;
            }
        }
        
        $informeModel->aprobarPorCoordinador($informe['id'], $comentarios, $documento_firmado);
        header("Location: coordinador_dashboard.php?mensaje=Informe aprobado exitosamente");
        exit();
    } elseif ($accion === 'rechazar') {
        $informeModel->rechazarPorCoordinador($informe['id'], $comentarios);
        header("Location: coordinador_dashboard.php?mensaje=Informe rechazado");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revisar Informe Final - SYSPRE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card-shadow {
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .content-section {
            background: #f8fafc;
            border-left: 4px solid #667eea;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <!-- Header con gradiente -->
        <div class="gradient-bg text-white">
            <div class="container mx-auto px-4 py-8">
                <div class="max-w-6xl mx-auto">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-3xl font-bold flex items-center">
                                <i class="fas fa-file-check mr-3 text-yellow-300"></i>
                                Revisión de Informe Final
                            </h1>
                            <p class="text-blue-100 mt-2 text-lg">
                                <?php echo htmlspecialchars($estudiante['nombres'] . ' ' . $estudiante['apellidos']); ?>
                            </p>
                            <p class="text-blue-200 text-sm">
                                Código: <?php echo htmlspecialchars($estudiante['codigo']); ?> | 
                                Especialidad: <?php echo htmlspecialchars($estudiante['especialidad']); ?>
                            </p>
                        </div>
                        <a href="coordinador_dashboard.php" class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white px-6 py-3 rounded-lg transition duration-200 flex items-center">
                            <i class="fas fa-arrow-left mr-2"></i>Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="container mx-auto px-4 py-8">
            <div class="max-w-6xl mx-auto space-y-8">

            <!-- Información del Informe -->
            <div class="bg-white rounded-lg card-shadow p-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                    <i class="fas fa-info-circle mr-3 text-blue-600"></i>
                    Información del Informe
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <div class="flex items-center mb-2">
                            <i class="fas fa-calendar-alt mr-2 text-blue-600"></i>
                            <span class="font-semibold text-blue-800">Fecha de Creación</span>
                        </div>
                        <p class="text-gray-700"><?php echo date('d/m/Y H:i', strtotime($informe['fecha_creacion'])); ?></p>
                    </div>
                    
                    <div class="bg-<?php echo $informe['estado'] == 'aprobado' ? 'green' : ($informe['estado'] == 'rechazado' ? 'red' : 'yellow'); ?>-50 p-4 rounded-lg">
                        <div class="flex items-center mb-2">
                            <i class="fas fa-flag mr-2 text-<?php echo $informe['estado'] == 'aprobado' ? 'green' : ($informe['estado'] == 'rechazado' ? 'red' : 'yellow'); ?>-600"></i>
                            <span class="font-semibold text-<?php echo $informe['estado'] == 'aprobado' ? 'green' : ($informe['estado'] == 'rechazado' ? 'red' : 'yellow'); ?>-800">Estado Actual</span>
                        </div>
                        <p class="text-gray-700"><?php echo ucfirst($informe['estado']); ?></p>
                    </div>
                    
                    <?php if ($informe['calificacion_docente']): ?>
                    <div class="bg-green-50 p-4 rounded-lg">
                        <div class="flex items-center mb-2">
                            <i class="fas fa-star mr-2 text-green-600"></i>
                            <span class="font-semibold text-green-800">Calificación Docente</span>
                        </div>
                        <p class="text-gray-700 text-xl font-bold"><?php echo $informe['calificacion_docente']; ?>/20</p>
                        <p class="text-gray-600 text-sm"><?php echo date('d/m/Y H:i', strtotime($informe['fecha_revision'])); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Contenido del Informe -->
            <div class="bg-white rounded-lg card-shadow p-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                    <i class="fas fa-file-alt mr-3 text-purple-600"></i>
                    Contenido del Informe Final
                </h2>
                
                <div class="space-y-6">
                    <!-- Título del Informe -->
                    <div class="content-section p-4 rounded-lg">
                        <h3 class="font-semibold text-lg text-gray-800 mb-2">
                            <i class="fas fa-heading mr-2 text-blue-600"></i>Título
                        </h3>
                        <p class="text-gray-700"><?php echo htmlspecialchars($informe['titulo'] ?? 'Sin título especificado'); ?></p>
                    </div>
                    
                    <!-- Resumen Ejecutivo -->
                    <?php if ($informe['resumen_ejecutivo']): ?>
                    <div class="content-section p-4 rounded-lg">
                        <h3 class="font-semibold text-lg text-gray-800 mb-2">
                            <i class="fas fa-clipboard-list mr-2 text-green-600"></i>Resumen Ejecutivo
                        </h3>
                        <p class="text-gray-700 leading-relaxed"><?php echo nl2br(htmlspecialchars($informe['resumen_ejecutivo'])); ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Objetivos -->
                    <?php if ($informe['objetivos']): ?>
                    <div class="content-section p-4 rounded-lg">
                        <h3 class="font-semibold text-lg text-gray-800 mb-2">
                            <i class="fas fa-bullseye mr-2 text-red-600"></i>Objetivos
                        </h3>
                        <p class="text-gray-700 leading-relaxed"><?php echo nl2br(htmlspecialchars($informe['objetivos'])); ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Metodología -->
                    <?php if ($informe['metodologia']): ?>
                    <div class="content-section p-4 rounded-lg">
                        <h3 class="font-semibold text-lg text-gray-800 mb-2">
                            <i class="fas fa-cog mr-2 text-purple-600"></i>Metodología
                        </h3>
                        <p class="text-gray-700 leading-relaxed"><?php echo nl2br(htmlspecialchars($informe['metodologia'])); ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Resultados -->
                    <?php if ($informe['resultados']): ?>
                    <div class="content-section p-4 rounded-lg">
                        <h3 class="font-semibold text-lg text-gray-800 mb-2">
                            <i class="fas fa-chart-line mr-2 text-blue-600"></i>Resultados
                        </h3>
                        <p class="text-gray-700 leading-relaxed"><?php echo nl2br(htmlspecialchars($informe['resultados'])); ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Conclusiones -->
                    <?php if ($informe['conclusiones']): ?>
                    <div class="content-section p-4 rounded-lg">
                        <h3 class="font-semibold text-lg text-gray-800 mb-2">
                            <i class="fas fa-check-circle mr-2 text-green-600"></i>Conclusiones
                        </h3>
                        <p class="text-gray-700 leading-relaxed"><?php echo nl2br(htmlspecialchars($informe['conclusiones'])); ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Recomendaciones -->
                    <?php if ($informe['recomendaciones']): ?>
                    <div class="content-section p-4 rounded-lg">
                        <h3 class="font-semibold text-lg text-gray-800 mb-2">
                            <i class="fas fa-lightbulb mr-2 text-yellow-600"></i>Recomendaciones
                        </h3>
                        <p class="text-gray-700 leading-relaxed"><?php echo nl2br(htmlspecialchars($informe['recomendaciones'])); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Archivo del Informe -->
            <?php if ($informe['archivo_informe']): ?>
            <div class="bg-white rounded-lg card-shadow p-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                    <i class="fas fa-file-download mr-3 text-red-600"></i>
                    Archivo del Informe
                </h2>
                <div class="bg-gray-50 rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="bg-red-100 p-3 rounded-full">
                                <i class="fas fa-file-pdf text-2xl text-red-600"></i>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800">Informe Final - <?php echo htmlspecialchars($estudiante['codigo']); ?></p>
                                <p class="text-gray-600 text-sm">Documento principal del informe</p>
                            </div>
                        </div>
                        <?php
                        // Procesar archivo para descarga directa
                        $file_path = $informe['archivo_informe'];
                        if (str_starts_with($file_path, '[') && str_ends_with($file_path, ']')) {
                            $files = json_decode($file_path, true);
                            if (is_array($files) && !empty($files)) {
                                $direct_url = '../uploads/informes/' . $files[0];
                                echo '<a href="' . $direct_url . '" download="Informe_Final_' . htmlspecialchars($estudiante['codigo']) . '_2025.pdf" 
                                       class="bg-red-500 hover:bg-red-600 text-white px-6 py-3 rounded-lg transition duration-200 flex items-center">
                                        <i class="fas fa-download mr-2"></i>Descargar PDF
                                      </a>';
                            }
                        } else {
                            echo '<a href="../download_informe_file.php?id=' . $informe['id'] . '" target="_blank" 
                                   class="bg-red-500 hover:bg-red-600 text-white px-6 py-3 rounded-lg transition duration-200 flex items-center">
                                    <i class="fas fa-download mr-2"></i>Descargar PDF
                                  </a>';
                        }
                        ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Comentarios del Docente -->
            <?php if ($informe['observaciones_docente'] || $informe['comentarios_docente']): ?>
            <div class="bg-white rounded-lg card-shadow p-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                    <i class="fas fa-comments mr-3 text-indigo-600"></i>
                    Evaluación del Docente
                </h2>
                <div class="bg-indigo-50 border-l-4 border-indigo-500 p-6 rounded-r-lg">
                    <div class="flex items-start">
                        <div class="bg-indigo-100 p-2 rounded-full mr-4">
                            <i class="fas fa-user-tie text-indigo-600"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-indigo-800 mb-2">Observaciones del Docente Asesor</h3>
                            <p class="text-gray-700 leading-relaxed">
                                <?php echo nl2br(htmlspecialchars($informe['observaciones_docente'] ?? $informe['comentarios_docente'] ?? 'Sin comentarios')); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Formulario de Revisión -->
            <?php if ($informe['estado'] == 'aprobado'): ?>
            <div class="bg-white rounded-lg card-shadow p-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                    <i class="fas fa-clipboard-check mr-3 text-green-600"></i>
                    Decisión del Coordinador
                </h2>
                <!-- Información importante sobre el flujo -->
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-6 mb-6">
                    <div class="flex items-start">
                        <div class="bg-yellow-100 p-2 rounded-full mr-4">
                            <i class="fas fa-info-circle text-yellow-600"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-yellow-800 mb-2">Flujo de Aprobación con Firma Digital</h3>
                            <p class="text-yellow-700 text-sm">
                                Para aprobar este informe, debe:
                                <br>1. Descargar el PDF del informe del estudiante (arriba)
                                <br>2. Firmarlo digitalmente con Tocapu
                                <br>3. Subir el documento firmado al sistema
                                <br>4. Completar la aprobación
                            </p>
                        </div>
                    </div>
                </div>
                
                <form method="POST" enctype="multipart/form-data" class="space-y-6">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-3">
                            <i class="fas fa-pen mr-2"></i>Comentarios y Observaciones
                        </label>
                        <textarea name="comentarios" rows="5" 
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                  placeholder="Escriba sus comentarios sobre el informe final, sugerencias o felicitaciones..."></textarea>
                        <p class="text-gray-500 text-sm mt-2">
                            <i class="fas fa-info-circle mr-1"></i>
                            Los comentarios serán visibles para el estudiante y quedarán registrados en el sistema
                        </p>
                    </div>
                    
                    <!-- Campo para subir documento firmado -->
                    <div class="bg-blue-50 border-l-4 border-blue-400 p-6">
                        <div class="flex items-start">
                            <div class="bg-blue-100 p-2 rounded-full mr-4">
                                <i class="fas fa-file-signature text-blue-600"></i>
                            </div>
                            <div class="flex-1">
                                <h3 class="font-semibold text-blue-800 mb-2">Documento Firmado con Tocapu</h3>
                                <p class="text-blue-700 text-sm mb-4">
                                    Suba el informe firmado digitalmente con Tocapu (formato PDF)
                                </p>
                                <input type="file" name="documento_firmado" accept=".pdf" 
                                       class="mt-1 block w-full text-sm text-gray-500
                                              file:mr-4 file:py-2 file:px-4
                                              file:rounded-full file:border-0
                                              file:text-sm file:font-semibold
                                              file:bg-blue-50 file:text-blue-700
                                              hover:file:bg-blue-100">
                                <p class="mt-2 text-xs text-gray-500">
                                    Solo archivos PDF. Máximo 10MB.
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <h3 class="font-semibold text-gray-800 mb-4">Tome su decisión:</h3>
                        <div class="flex flex-col sm:flex-row gap-4">
                            <button type="submit" name="accion" value="aprobar" 
                                    class="flex-1 bg-green-500 hover:bg-green-600 text-white px-8 py-4 rounded-lg transition duration-200 flex items-center justify-center text-lg font-semibold">
                                <i class="fas fa-check-circle mr-3"></i>Aprobar Informe Final
                            </button>
                            <button type="submit" name="accion" value="rechazar" 
                                    class="flex-1 bg-red-500 hover:bg-red-600 text-white px-8 py-4 rounded-lg transition duration-200 flex items-center justify-center text-lg font-semibold">
                                <i class="fas fa-times-circle mr-3"></i>Rechazar Informe
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <?php else: ?>
            <div class="bg-white rounded-lg card-shadow p-8">
                <div class="text-center">
                    <div class="bg-blue-100 p-4 rounded-full w-20 h-20 mx-auto mb-4 flex items-center justify-center">
                        <i class="fas fa-info-circle text-blue-600 text-3xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">Informe ya procesado</h2>
                    <p class="text-gray-600">Este informe ya ha sido procesado por el coordinador.</p>
                    <p class="text-gray-500 text-sm mt-2">Estado actual: <?php echo ucfirst($informe['estado']); ?></p>
                </div>
            </div>
            <?php endif; ?>
            
            </div>
        </div>
    </div>
</body>
</html>