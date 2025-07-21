<?php
require_once '../config/session.php';
require_once '../models/InformeFinal.php';

// Verificar que el usuario esté autenticado y sea coordinador o docente
if (!isAuthenticated() || !in_array($_SESSION['user_type'], ['coordinador', 'docente'])) {
    header('Location: ../index.php');
    exit();
}

$informe_id = $_GET['id'] ?? null;
if (!$informe_id) {
    header('Location: ' . $_SESSION['user_type'] . '_dashboard.php');
    exit();
}

$informeModel = new InformeFinal();
$informe = $informeModel->findById($informe_id);

if (!$informe) {
    header('Location: ' . $_SESSION['user_type'] . '_dashboard.php');
    exit();
}

// Procesamiento del formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $campo_calificacion = $_SESSION['user_type'] == 'coordinador' ? 'calificacion_coordinador' : 'calificacion_docente';
    $campo_comentarios = $_SESSION['user_type'] == 'coordinador' ? 'comentarios_coordinador' : 'comentarios_docente';
    $campo_observaciones = $_SESSION['user_type'] == 'coordinador' ? 'observaciones_coordinador' : 'observaciones_docente';
    
    $data = [
        'estado' => $_POST['estado'],
        $campo_calificacion => $_POST['calificacion'],
        $campo_comentarios => $_POST['comentarios'],
        $campo_observaciones => $_POST['observaciones'],
        'fecha_revision' => date('Y-m-d H:i:s')
    ];
    
    $result = $informeModel->updateRevision($informe_id, $data);
    if ($result) {
        $success = "Informe revisado exitosamente";
    } else {
        $error = "Error al revisar el informe";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revisar Informe - SYSPRE 2025</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen">

    <!-- Header -->
    <header class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
            <a href="<?php echo $_SESSION['user_type']; ?>_dashboard.php" class="text-xl font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-arrow-left"></i>
                <i class="fas fa-<?php echo $_SESSION['user_type'] == 'coordinador' ? 'user-tie' : 'chalkboard-teacher'; ?>"></i>
                SYSPRE 2025
            </a>
            <div class="text-sm text-gray-600">
                Revisión de Informe Final
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            
            <!-- Header del Informe -->
            <div class="bg-purple-600 text-white p-6">
                <h1 class="text-2xl font-bold">
                    <i class="fas fa-file-signature mr-2"></i>
                    Revisión de Informe Final
                </h1>
                <p class="mt-2 opacity-90">
                    Título: <?php echo htmlspecialchars($informe['titulo']); ?>
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
                    
                    <!-- Contenido del Informe -->
                    <div class="lg:col-span-2 space-y-6">
                        
                        <!-- Resumen Ejecutivo -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold mb-4 text-gray-800">
                                <i class="fas fa-file-alt mr-2 text-blue-600"></i>
                                Resumen Ejecutivo
                            </h3>
                            <div class="bg-white p-3 rounded border">
                                <p class="text-sm text-gray-700 whitespace-pre-wrap"><?php echo htmlspecialchars($informe['resumen_ejecutivo']); ?></p>
                            </div>
                        </div>

                        <!-- Introducción -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold mb-4 text-gray-800">
                                <i class="fas fa-play-circle mr-2 text-green-600"></i>
                                Introducción
                            </h3>
                            <div class="bg-white p-3 rounded border">
                                <p class="text-sm text-gray-700 whitespace-pre-wrap"><?php echo htmlspecialchars($informe['introduccion']); ?></p>
                            </div>
                        </div>

                        <!-- Objetivos -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold mb-4 text-gray-800">
                                <i class="fas fa-bullseye mr-2 text-red-600"></i>
                                Objetivos
                            </h3>
                            <div class="bg-white p-3 rounded border">
                                <p class="text-sm text-gray-700 whitespace-pre-wrap"><?php echo htmlspecialchars($informe['objetivos']); ?></p>
                            </div>
                        </div>

                        <!-- Metodología -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold mb-4 text-gray-800">
                                <i class="fas fa-cogs mr-2 text-purple-600"></i>
                                Metodología
                            </h3>
                            <div class="bg-white p-3 rounded border">
                                <p class="text-sm text-gray-700 whitespace-pre-wrap"><?php echo htmlspecialchars($informe['metodologia']); ?></p>
                            </div>
                        </div>

                        <!-- Resultados -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold mb-4 text-gray-800">
                                <i class="fas fa-chart-line mr-2 text-blue-600"></i>
                                Resultados
                            </h3>
                            <div class="bg-white p-3 rounded border">
                                <p class="text-sm text-gray-700 whitespace-pre-wrap"><?php echo htmlspecialchars($informe['resultados']); ?></p>
                            </div>
                        </div>

                        <!-- Conclusiones -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold mb-4 text-gray-800">
                                <i class="fas fa-check-circle mr-2 text-green-600"></i>
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
                    </div>

                    <!-- Panel de Revisión -->
                    <div class="space-y-6">
                        <!-- Información del Informe -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold mb-4 text-gray-800">
                                <i class="fas fa-info-circle mr-2 text-blue-600"></i>
                                Información del Informe
                            </h3>
                            <div class="space-y-2 text-sm">
                                <p><strong>Estado:</strong> 
                                    <span class="px-2 py-1 rounded text-xs font-medium <?php echo $informe['estado'] == 'pendiente' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800'; ?>">
                                        <?php echo ucfirst($informe['estado']); ?>
                                    </span>
                                </p>
                                <p><strong>Fecha de Creación:</strong> <?php echo date('d/m/Y H:i', strtotime($informe['fecha_creacion'])); ?></p>
                                <?php if ($informe['fecha_revision']): ?>
                                    <p><strong>Última Revisión:</strong> <?php echo date('d/m/Y H:i', strtotime($informe['fecha_revision'])); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Formulario de Revisión -->
                        <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                            <h3 class="text-lg font-semibold mb-4 text-gray-800">
                                <i class="fas fa-star mr-2 text-blue-600"></i>
                                Revisión del <?php echo $_SESSION['user_type'] == 'coordinador' ? 'Coordinador' : 'Docente'; ?>
                            </h3>
                            
                            <form method="POST" class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Estado del Informe *
                                    </label>
                                    <select name="estado" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">Seleccionar...</option>
                                        <option value="aprobado" class="text-green-600">✓ Aprobar Informe</option>
                                        <option value="rechazado" class="text-red-600">✗ Rechazar Informe</option>
                                        <option value="revision" class="text-yellow-600">⚠ Requiere Revisión</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Calificación (0-20) *
                                    </label>
                                    <input type="number" name="calificacion" min="0" max="20" step="0.1" required 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Comentarios *
                                    </label>
                                    <textarea name="comentarios" required rows="4" 
                                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                              placeholder="Comentarios sobre el informe final..."></textarea>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Observaciones y Recomendaciones
                                    </label>
                                    <textarea name="observaciones" rows="3" 
                                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                              placeholder="Observaciones adicionales..."></textarea>
                                </div>

                                <div class="pt-4 space-y-3">
                                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-md transition">
                                        <i class="fas fa-check mr-2"></i>Enviar Revisión
                                    </button>
                                    <a href="<?php echo $_SESSION['user_type']; ?>_dashboard.php" class="w-full bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded-md transition text-center block">
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
                                    <span class="text-gray-600">30%</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Metodología aplicada</span>
                                    <span class="text-gray-600">25%</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Análisis de resultados</span>
                                    <span class="text-gray-600">25%</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Conclusiones y recomendaciones</span>
                                    <span class="text-gray-600">20%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Cambiar color del select según la opción seleccionada
        document.querySelector('select[name="estado"]').addEventListener('change', function() {
            const select = this;
            const value = select.value;
            
            select.className = 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500';
            
            if (value === 'aprobado') {
                select.className += ' bg-green-50 border-green-300';
            } else if (value === 'rechazado') {
                select.className += ' bg-red-50 border-red-300';
            } else if (value === 'revision') {
                select.className += ' bg-yellow-50 border-yellow-300';
            }
        });

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