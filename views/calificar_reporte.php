<?php
require_once '../config/session.php';
require_once '../models/ReporteSemanal.php';

// Verificar que el usuario esté autenticado y sea docente
if (!isAuthenticated() || !hasRole('docente')) {
    header('Location: login_docente.php');
    exit();
}

$reporte_id = $_GET['id'] ?? null;
if (!$reporte_id) {
    header('Location: docente_dashboard.php');
    exit();
}

$reporteSemanal = new ReporteSemanal();
$reporte = $reporteSemanal->findById($reporte_id);

if (!$reporte) {
    header('Location: docente_dashboard.php');
    exit();
}

// Procesamiento del formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = [
        'calificacion_docente' => $_POST['calificacion'],
        'comentarios_docente' => $_POST['comentarios'],
        'observaciones_docente' => $_POST['observaciones'],
        'estado' => 'calificado',
        'fecha_calificacion' => date('Y-m-d H:i:s')
    ];
    
    $result = $reporteSemanal->updateCalificacion($reporte_id, $data);
    if ($result) {
        $success = "Reporte calificado exitosamente";
    } else {
        $error = "Error al calificar el reporte";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calificar Reporte - SYSPRE 2025</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen">

    <!-- Header -->
    <header class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
            <a href="docente_dashboard.php" class="text-xl font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-arrow-left"></i>
                <i class="fas fa-chalkboard-teacher"></i>
                SYSPRE 2025
            </a>
            <div class="text-sm text-gray-600">
                Calificar Reporte Semanal
            </div>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            
            <!-- Header del Reporte -->
            <div class="bg-green-600 text-white p-6">
                <h1 class="text-2xl font-bold">
                    <i class="fas fa-file-alt mr-2"></i>
                    Calificar Reporte Semanal
                </h1>
                <p class="mt-2 opacity-90">
                    Período: <?php echo date('d/m/Y', strtotime($reporte['fecha_inicio'])); ?> - <?php echo date('d/m/Y', strtotime($reporte['fecha_fin'])); ?>
                </p>
            </div>

            <?php if (isset($success)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <div class="p-6">
                <div class="grid lg:grid-cols-3 gap-6">
                    
                    <!-- Información Básica -->
                    <div class="lg:col-span-2 space-y-6">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold mb-4 text-gray-800">
                                <i class="fas fa-info-circle mr-2 text-blue-600"></i>
                                Información Básica
                            </h3>
                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <p><strong>Total de Horas:</strong> <?php echo htmlspecialchars($reporte['total_horas']); ?></p>
                                    <p><strong>Asesor Empresarial:</strong> <?php echo htmlspecialchars($reporte['asesor_empresarial']); ?></p>
                                </div>
                                <div>
                                    <p><strong>Área de Trabajo:</strong> <?php echo htmlspecialchars($reporte['area_trabajo']); ?></p>
                                    <p><strong>Estado:</strong> 
                                        <span class="px-2 py-1 rounded text-xs font-medium <?php echo $reporte['estado'] == 'pendiente' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800'; ?>">
                                            <?php echo ucfirst($reporte['estado']); ?>
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold mb-4 text-gray-800">
                                <i class="fas fa-tasks mr-2 text-purple-600"></i>
                                Actividades Realizadas
                            </h3>
                            <div class="bg-white p-3 rounded border">
                                <p class="text-sm text-gray-700 whitespace-pre-wrap"><?php echo htmlspecialchars($reporte['actividades']); ?></p>
                            </div>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold mb-4 text-gray-800">
                                <i class="fas fa-lightbulb mr-2 text-yellow-600"></i>
                                Aprendizajes y Logros
                            </h3>
                            <div class="bg-white p-3 rounded border">
                                <p class="text-sm text-gray-700 whitespace-pre-wrap"><?php echo htmlspecialchars($reporte['aprendizajes']); ?></p>
                            </div>
                        </div>

                        <?php if (!empty($reporte['dificultades'])): ?>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold mb-4 text-gray-800">
                                <i class="fas fa-exclamation-triangle mr-2 text-red-600"></i>
                                Dificultades Encontradas
                            </h3>
                            <div class="bg-white p-3 rounded border">
                                <p class="text-sm text-gray-700 whitespace-pre-wrap"><?php echo htmlspecialchars($reporte['dificultades']); ?></p>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($reporte['archivos_adjuntos'])): ?>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold mb-4 text-gray-800">
                                <i class="fas fa-paperclip mr-2 text-blue-600"></i>
                                Documentos Adjuntos
                            </h3>
                            <div class="space-y-2">
                                <?php
                                $archivos = json_decode($reporte['archivos_adjuntos'], true);
                                if (is_array($archivos) && !empty($archivos)):
                                    foreach ($archivos as $archivo):
                                        if (!empty($archivo)):
                                            $file_name = basename($archivo);
                                            $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                                            $file_size = '';
                                            $file_path = __DIR__ . '/../uploads/reportes/' . $file_name;
                                            
                                            if (file_exists($file_path)) {
                                                $file_size = ' (' . number_format(filesize($file_path) / 1024, 1) . ' KB)';
                                            }
                                            
                                            // Determinar icono según extensión
                                            $icon_class = 'fas fa-file';
                                            $icon_color = 'text-gray-600';
                                            
                                            switch ($file_extension) {
                                                case 'pdf':
                                                    $icon_class = 'fas fa-file-pdf';
                                                    $icon_color = 'text-red-600';
                                                    break;
                                                case 'doc':
                                                case 'docx':
                                                    $icon_class = 'fas fa-file-word';
                                                    $icon_color = 'text-blue-600';
                                                    break;
                                                case 'jpg':
                                                case 'jpeg':
                                                case 'png':
                                                case 'gif':
                                                    $icon_class = 'fas fa-file-image';
                                                    $icon_color = 'text-green-600';
                                                    break;
                                                case 'txt':
                                                    $icon_class = 'fas fa-file-alt';
                                                    $icon_color = 'text-gray-600';
                                                    break;
                                            }
                                ?>
                                <div class="bg-white p-3 rounded border flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        <i class="<?php echo $icon_class; ?> <?php echo $icon_color; ?> text-lg"></i>
                                        <div>
                                            <p class="text-sm font-medium text-gray-800"><?php echo htmlspecialchars($file_name); ?></p>
                                            <p class="text-xs text-gray-500">
                                                <?php echo strtoupper($file_extension); ?> 
                                                <?php echo $file_size; ?>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex space-x-2">
                                        <a href="../download_report_file.php?reporte_id=<?php echo $reporte['id']; ?>&file_name=<?php echo urlencode($file_name); ?>" 
                                           class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm transition"
                                           target="_blank">
                                            <i class="fas fa-download mr-1"></i>Descargar
                                        </a>
                                        <?php if (in_array($file_extension, ['pdf', 'jpg', 'jpeg', 'png', 'gif'])): ?>
                                        <a href="../download_report_file.php?reporte_id=<?php echo $reporte['id']; ?>&file_name=<?php echo urlencode($file_name); ?>" 
                                           class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm transition"
                                           target="_blank">
                                            <i class="fas fa-eye mr-1"></i>Ver
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php
                                        endif;
                                    endforeach;
                                else:
                                ?>
                                <div class="bg-white p-3 rounded border">
                                    <p class="text-sm text-gray-500 italic">No se encontraron archivos adjuntos</p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Panel de Calificación -->
                    <div class="space-y-6">
                        <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                            <h3 class="text-lg font-semibold mb-4 text-gray-800">
                                <i class="fas fa-star mr-2 text-blue-600"></i>
                                Calificación del Reporte
                            </h3>
                            
                            <form method="POST" class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Calificación (0-20) *
                                    </label>
                                    <input type="number" name="calificacion" min="0" max="20" step="0.1" required 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                           value="<?php echo $reporte['calificacion_docente'] ?? ''; ?>">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Comentarios *
                                    </label>
                                    <textarea name="comentarios" required rows="4" 
                                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                              placeholder="Comentarios sobre el reporte..."><?php echo $reporte['comentarios_docente'] ?? ''; ?></textarea>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Observaciones y Recomendaciones
                                    </label>
                                    <textarea name="observaciones" rows="3" 
                                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                              placeholder="Observaciones adicionales..."><?php echo $reporte['observaciones_docente'] ?? ''; ?></textarea>
                                </div>

                                <div class="pt-4 space-y-3">
                                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-md transition">
                                        <i class="fas fa-save mr-2"></i>Guardar Calificación
                                    </button>
                                    <a href="docente_dashboard.php" class="w-full bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded-md transition text-center block">
                                        <i class="fas fa-arrow-left mr-2"></i>Volver
                                    </a>
                                </div>
                            </form>
                        </div>

                        <!-- Criterios de Evaluación -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="text-md font-semibold mb-3 text-gray-800">
                                <i class="fas fa-clipboard-check mr-2 text-green-600"></i>
                                Criterios de Evaluación
                            </h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span>Calidad del contenido</span>
                                    <span class="text-gray-600">40%</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Claridad en la redacción</span>
                                    <span class="text-gray-600">30%</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Cumplimiento de objetivos</span>
                                    <span class="text-gray-600">20%</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Presentación y formato</span>
                                    <span class="text-gray-600">10%</span>
                                </div>
                            </div>
                        </div>

                        <!-- Escala de Calificación -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="text-md font-semibold mb-3 text-gray-800">
                                <i class="fas fa-chart-bar mr-2 text-purple-600"></i>
                                Escala de Calificación
                            </h4>
                            <div class="space-y-1 text-sm">
                                <div class="flex justify-between">
                                    <span>Excelente</span>
                                    <span class="text-green-600 font-medium">18-20</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Bueno</span>
                                    <span class="text-blue-600 font-medium">15-17</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Regular</span>
                                    <span class="text-yellow-600 font-medium">12-14</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Deficiente</span>
                                    <span class="text-red-600 font-medium">0-11</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Actualizar color del input según la calificación
        document.querySelector('input[name="calificacion"]').addEventListener('input', function() {
            const input = this;
            const value = parseFloat(input.value);
            
            input.className = 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500';
            
            if (value >= 18) {
                input.className += ' bg-green-50 border-green-300';
            } else if (value >= 15) {
                input.className += ' bg-blue-50 border-blue-300';
            } else if (value >= 12) {
                input.className += ' bg-yellow-50 border-yellow-300';
            } else if (value > 0) {
                input.className += ' bg-red-50 border-red-300';
            }
        });
    </script>

</body>
</html>