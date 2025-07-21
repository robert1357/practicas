<?php
require_once '../config/session.php';
require_once '../models/InformeFinal.php';
require_once '../models/User.php';

// Verificar que el usuario esté autenticado y sea docente
if (!isAuthenticated() || $_SESSION['user_type'] !== 'docente') {
    header('Location: login_docente.php');
    exit();
}

$estudiante_id = $_GET['estudiante'] ?? null;
if (!$estudiante_id) {
    header('Location: docente_dashboard.php');
    exit();
}

// Obtener información del estudiante
$userModel = new User();
$estudiante = $userModel->getById($estudiante_id);

if (!$estudiante || $estudiante['tipo'] !== 'estudiante') {
    header('Location: docente_dashboard.php');
    exit();
}

// Obtener el informe final del estudiante
$informeModel = new InformeFinal();
$informe = $informeModel->getByEstudiante($estudiante_id);

if (!$informe) {
    header('Location: docente_dashboard.php');
    exit();
}

// Procesar calificación
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $calificacion = $_POST['calificacion'];
    $comentarios = $_POST['comentarios'];
    $accion = $_POST['accion']; // 'aprobar' o 'rechazar'
    
    $estado = ($accion == 'aprobar') ? 'aprobado' : 'rechazado';
    
    if ($informeModel->calificarDocente($informe['id'], $calificacion, $comentarios, $estado)) {
        $success = "Informe calificado exitosamente";
        // Recargar el informe
        $informe = $informeModel->getByEstudiante($estudiante_id);
    } else {
        $error = "Error al calificar el informe";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calificar Informe Final - SYSPRE 2025</title>
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
                Calificar Informe Final
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-lg">
            
            <!-- Header del Informe -->
            <div class="bg-purple-600 text-white p-6 rounded-t-lg">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold">
                            <i class="fas fa-file-signature mr-2"></i>
                            Calificar Informe Final
                        </h1>
                        <p class="mt-2 opacity-90">
                            Estudiante: <?php echo htmlspecialchars($estudiante['nombres'] . ' ' . $estudiante['apellidos']); ?>
                        </p>
                        <p class="opacity-80 text-sm">
                            Código: <?php echo htmlspecialchars($estudiante['codigo']); ?> | 
                            Especialidad: <?php echo htmlspecialchars($estudiante['especialidad']); ?>
                        </p>
                    </div>
                    <div class="text-right">
                        <span class="px-3 py-1 rounded-full text-sm font-medium 
                            <?php 
                            switch($informe['estado']) {
                                case 'aprobado_docente':
                                    echo 'bg-green-100 text-green-800';
                                    break;
                                case 'rechazado':
                                    echo 'bg-red-100 text-red-800';
                                    break;
                                case 'pendiente':
                                    echo 'bg-yellow-100 text-yellow-800';
                                    break;
                                default:
                                    echo 'bg-gray-100 text-gray-800';
                            }
                            ?>">
                            <?php echo ucfirst($informe['estado']); ?>
                        </span>
                    </div>
                </div>
            </div>

            <?php if (isset($success)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 m-6 rounded">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 m-6 rounded">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <div class="p-6">
                <div class="grid lg:grid-cols-3 gap-6">
                    
                    <!-- Contenido del Informe -->
                    <div class="lg:col-span-2 space-y-6">
                        
                        <!-- Información General -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold mb-4 text-gray-800">
                                <i class="fas fa-info-circle mr-2 text-blue-600"></i>
                                Información General
                            </h3>
                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <p><strong>Título:</strong> <?php echo htmlspecialchars($informe['titulo']); ?></p>
                                    <p><strong>Fecha de Envío:</strong> <?php echo date('d/m/Y H:i', strtotime($informe['fecha_creacion'])); ?></p>
                                </div>
                                <div>
                                    <p><strong>Estado:</strong> 
                                        <span class="px-2 py-1 rounded text-xs font-medium <?php echo $informe['estado'] == 'pendiente' ? 'bg-yellow-100 text-yellow-800' : ($informe['estado'] == 'aprobado' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'); ?>">
                                            <?php echo ucfirst($informe['estado']); ?>
                                        </span>
                                    </p>
                                    <?php if (!empty($informe['fecha_revision'])): ?>
                                        <p><strong>Calificado:</strong> <?php echo date('d/m/Y H:i', strtotime($informe['fecha_revision'])); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Resumen Ejecutivo -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold mb-4 text-gray-800">
                                <i class="fas fa-file-alt mr-2 text-green-600"></i>
                                Resumen Ejecutivo
                            </h3>
                            <div class="bg-white p-3 rounded border">
                                <p class="text-sm text-gray-700 whitespace-pre-wrap"><?php echo htmlspecialchars($informe['resumen_ejecutivo']); ?></p>
                            </div>
                        </div>

                        <!-- Introducción -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold mb-4 text-gray-800">
                                <i class="fas fa-play mr-2 text-blue-600"></i>
                                Introducción
                            </h3>
                            <div class="bg-white p-3 rounded border">
                                <p class="text-sm text-gray-700 whitespace-pre-wrap"><?php echo htmlspecialchars($informe['introduccion']); ?></p>
                            </div>
                        </div>

                        <!-- Objetivos -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold mb-4 text-gray-800">
                                <i class="fas fa-target mr-2 text-purple-600"></i>
                                Objetivos
                            </h3>
                            <div class="bg-white p-3 rounded border">
                                <p class="text-sm text-gray-700 whitespace-pre-wrap"><?php echo htmlspecialchars($informe['objetivos']); ?></p>
                            </div>
                        </div>

                        <!-- Metodología -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold mb-4 text-gray-800">
                                <i class="fas fa-cogs mr-2 text-orange-600"></i>
                                Metodología
                            </h3>
                            <div class="bg-white p-3 rounded border">
                                <p class="text-sm text-gray-700 whitespace-pre-wrap"><?php echo htmlspecialchars($informe['metodologia']); ?></p>
                            </div>
                        </div>

                        <!-- Resultados -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold mb-4 text-gray-800">
                                <i class="fas fa-chart-line mr-2 text-green-600"></i>
                                Resultados
                            </h3>
                            <div class="bg-white p-3 rounded border">
                                <p class="text-sm text-gray-700 whitespace-pre-wrap"><?php echo htmlspecialchars($informe['resultados']); ?></p>
                            </div>
                        </div>

                        <!-- Conclusiones -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold mb-4 text-gray-800">
                                <i class="fas fa-check-circle mr-2 text-red-600"></i>
                                Conclusiones
                            </h3>
                            <div class="bg-white p-3 rounded border">
                                <p class="text-sm text-gray-700 whitespace-pre-wrap"><?php echo htmlspecialchars($informe['conclusiones']); ?></p>
                            </div>
                        </div>

                        <!-- Recomendaciones -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold mb-4 text-gray-800">
                                <i class="fas fa-lightbulb mr-2 text-yellow-600"></i>
                                Recomendaciones
                            </h3>
                            <div class="bg-white p-3 rounded border">
                                <p class="text-sm text-gray-700 whitespace-pre-wrap"><?php echo htmlspecialchars($informe['recomendaciones']); ?></p>
                            </div>
                        </div>

                        <!-- Archivos Adjuntos -->
                        <?php if (!empty($informe['archivo_informe'])): ?>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold mb-4 text-gray-800">
                                <i class="fas fa-paperclip mr-2 text-blue-600"></i>
                                Documentos Adjuntos
                            </h3>
                            <div class="space-y-2">
                                <?php
                                $archivos = json_decode($informe['archivo_informe'], true);
                                if (is_array($archivos) && !empty($archivos)):
                                    foreach ($archivos as $archivo):
                                        if (!empty($archivo)):
                                            $file_name = basename($archivo);
                                            $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                                            
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
                                            }
                                ?>
                                <div class="bg-white p-3 rounded border flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        <i class="<?php echo $icon_class; ?> <?php echo $icon_color; ?> text-lg"></i>
                                        <div>
                                            <p class="text-sm font-medium text-gray-800"><?php echo htmlspecialchars($file_name); ?></p>
                                            <p class="text-xs text-gray-500"><?php echo strtoupper($file_extension); ?></p>
                                        </div>
                                    </div>
                                    <a href="../uploads/informes/<?php echo urlencode($file_name); ?>" 
                                       class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm transition"
                                       target="_blank">
                                        <i class="fas fa-download mr-1"></i>Descargar
                                    </a>
                                </div>
                                <?php
                                        endif;
                                    endforeach;
                                endif;
                                ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Panel de Calificación -->
                    <div class="space-y-6">
                        
                        <!-- Calificación Actual -->
                        <?php if ($informe['calificacion_docente']): ?>
                        <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                            <h3 class="text-lg font-semibold mb-4 text-gray-800">
                                <i class="fas fa-star mr-2 text-green-600"></i>
                                Calificación Actual
                            </h3>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-green-600 mb-2">
                                    <?php echo $informe['calificacion_docente']; ?>
                                </div>
                                <div class="text-sm text-gray-600">sobre 20</div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Comentarios Actuales -->
                        <?php if ($informe['comentarios_docente']): ?>
                        <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                            <h3 class="text-lg font-semibold mb-4 text-gray-800">
                                <i class="fas fa-comment mr-2 text-blue-600"></i>
                                Comentarios Actuales
                            </h3>
                            <p class="text-sm text-gray-700"><?php echo htmlspecialchars($informe['comentarios_docente']); ?></p>
                        </div>
                        <?php endif; ?>

                        <!-- Formulario de Calificación -->
                        <?php if ($informe['estado'] == 'pendiente'): ?>
                        <div class="bg-white p-4 rounded-lg border border-gray-200">
                            <h3 class="text-lg font-semibold mb-4 text-gray-800">
                                <i class="fas fa-edit mr-2 text-purple-600"></i>
                                Calificar Informe
                            </h3>
                            <form method="POST" class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Calificación (0-20)
                                    </label>
                                    <input type="number" name="calificacion" min="0" max="20" step="0.1" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                                           required>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Comentarios
                                    </label>
                                    <textarea name="comentarios" rows="4" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                                            placeholder="Comentarios sobre el informe..."></textarea>
                                </div>
                                
                                <div class="flex space-x-2">
                                    <button type="submit" name="accion" value="aprobar" 
                                            class="flex-1 bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded-md transition">
                                        <i class="fas fa-check mr-2"></i>Aprobar
                                    </button>
                                    <button type="submit" name="accion" value="rechazar" 
                                            class="flex-1 bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded-md transition">
                                        <i class="fas fa-times mr-2"></i>Rechazar
                                    </button>
                                </div>
                            </form>
                        </div>
                        <?php endif; ?>

                        <!-- Navegación -->
                        <div class="space-y-3">
                            <a href="docente_dashboard.php" 
                               class="w-full bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded-md transition text-center block">
                                <i class="fas fa-arrow-left mr-2"></i>Volver al Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

</body>
</html>