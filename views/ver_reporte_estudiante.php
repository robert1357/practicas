<?php
require_once '../config/session.php';
require_once '../models/ReporteSemanal.php';

// Verificar que el usuario esté autenticado y sea estudiante
if (!isAuthenticated() || $_SESSION['user_type'] !== 'estudiante') {
    header('Location: login_estudiante.php');
    exit();
}

$reporte_id = $_GET['id'] ?? null;
if (!$reporte_id) {
    header('Location: estudiante_reportes_semanales.php');
    exit();
}

$reporteModel = new ReporteSemanal();
$reporte = $reporteModel->findById($reporte_id);

if (!$reporte || $reporte['estudiante_id'] != $_SESSION['user_id']) {
    header('Location: estudiante_reportes_semanales.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Reporte - SYSPRE 2025</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen">

    <!-- Header -->
    <header class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
            <a href="estudiante_reportes_semanales.php" class="text-xl font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-arrow-left"></i>
                <i class="fas fa-graduation-cap"></i>
                SYSPRE 2025
            </a>
            <div class="text-sm text-gray-600">
                Ver Reporte Semanal
            </div>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            
            <!-- Header del Reporte -->
            <div class="bg-blue-600 text-white p-6">
                <h1 class="text-2xl font-bold">
                    <i class="fas fa-file-alt mr-2"></i>
                    Reporte Semanal
                </h1>
                <p class="mt-2 opacity-90">
                    Período: <?php echo date('d/m/Y', strtotime($reporte['fecha_inicio'])); ?> - <?php echo date('d/m/Y', strtotime($reporte['fecha_fin'])); ?>
                </p>
            </div>

            <div class="p-6">
                <div class="grid lg:grid-cols-3 gap-6">
                    
                    <!-- Información del Reporte -->
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
                                        <span class="px-2 py-1 rounded text-xs font-medium <?php echo $reporte['estado'] == 'pendiente' ? 'bg-yellow-100 text-yellow-800' : ($reporte['estado'] == 'aprobado' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'); ?>">
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
                                    <p class="text-sm text-gray-500 italic">No hay archivos adjuntos</p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Panel lateral -->
                    <div class="space-y-6">
                        <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                            <h3 class="text-lg font-semibold mb-4 text-gray-800">
                                <i class="fas fa-calendar mr-2 text-blue-600"></i>
                                Fechas Importantes
                            </h3>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span>Creado:</span>
                                    <span class="font-medium"><?php echo date('d/m/Y H:i', strtotime($reporte['fecha_creacion'])); ?></span>
                                </div>
                                <?php if ($reporte['fecha_calificacion']): ?>
                                <div class="flex justify-between">
                                    <span>Calificado:</span>
                                    <span class="font-medium"><?php echo date('d/m/Y H:i', strtotime($reporte['fecha_calificacion'])); ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if ($reporte['calificacion_docente']): ?>
                        <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                            <h3 class="text-lg font-semibold mb-4 text-gray-800">
                                <i class="fas fa-star mr-2 text-green-600"></i>
                                Calificación
                            </h3>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-green-600 mb-2">
                                    <?php echo $reporte['calificacion_docente']; ?>
                                </div>
                                <div class="text-sm text-gray-600">sobre 20</div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if ($reporte['comentarios_docente']): ?>
                        <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200">
                            <h3 class="text-lg font-semibold mb-4 text-gray-800">
                                <i class="fas fa-comment mr-2 text-yellow-600"></i>
                                Comentarios del Docente
                            </h3>
                            <p class="text-sm text-gray-700"><?php echo htmlspecialchars($reporte['comentarios_docente']); ?></p>
                        </div>
                        <?php endif; ?>

                        <?php if ($reporte['observaciones_docente']): ?>
                        <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
                            <h3 class="text-lg font-semibold mb-4 text-gray-800">
                                <i class="fas fa-clipboard-list mr-2 text-purple-600"></i>
                                Observaciones
                            </h3>
                            <p class="text-sm text-gray-700"><?php echo htmlspecialchars($reporte['observaciones_docente']); ?></p>
                        </div>
                        <?php endif; ?>

                        <div class="space-y-3">
                            <a href="estudiante_reportes_semanales.php" 
                               class="w-full bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded-md transition text-center block">
                                <i class="fas fa-arrow-left mr-2"></i>Volver a Reportes
                            </a>
                            <?php if ($reporte['estado'] == 'pendiente'): ?>
                            <a href="editar_reporte_semanal.php?id=<?php echo $reporte['id']; ?>" 
                               class="w-full bg-yellow-500 hover:bg-yellow-600 text-white py-2 px-4 rounded-md transition text-center block">
                                <i class="fas fa-edit mr-2"></i>Editar Reporte
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

</body>
</html>