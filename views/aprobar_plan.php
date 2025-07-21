<?php
require_once '../config/session.php';
require_once '../models/PlanPractica.php';

// Verificar que el usuario esté autenticado y sea docente
if (!isAuthenticated() || $_SESSION['user_type'] !== 'docente') {
    header('Location: login_docente.php');
    exit();
}

$estudiante_id = $_GET['estudiante'] ?? $_GET['id'] ?? null;
if (!$estudiante_id) {
    header('Location: docente_dashboard.php');
    exit();
}

$planPractica = new PlanPractica();

// Si tenemos ID de estudiante, buscar su plan
if (isset($_GET['estudiante'])) {
    $plan = $planPractica->getByEstudiante($estudiante_id);
} else {
    // Si tenemos ID del plan directamente
    $plan = $planPractica->getById($estudiante_id);
}

if (!$plan) {
    header('Location: docente_dashboard.php');
    exit();
}

$plan_id = $plan['id'];

// Procesamiento del formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = [
        'estado' => $_POST['estado'],
        'calificacion_docente' => $_POST['calificacion'],
        'comentarios_docente' => $_POST['comentarios'],
        'observaciones_docente' => $_POST['observaciones'],
        'fecha_revision' => date('Y-m-d H:i:s')
    ];
    
    $result = $planPractica->updateStatus($plan_id, $data);
    if ($result) {
        $success = "Plan de práctica " . ($data['estado'] == 'aprobado' ? 'aprobado' : 'rechazado') . " exitosamente";
    } else {
        $error = "Error al actualizar el plan de práctica";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revisar Plan - SYSPRE 2025</title>
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
                Revisión de Plan de Práctica
            </div>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            
            <!-- Header del Plan -->
            <div class="bg-blue-600 text-white p-6">
                <h1 class="text-2xl font-bold">
                    <i class="fas fa-file-signature mr-2"></i>
                    Revisión de Plan de Práctica
                </h1>
                <p class="mt-2 opacity-90">Estudiante: <?php echo htmlspecialchars($plan['nombres'] . ' ' . $plan['apellidos']); ?></p>
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
                <div class="grid lg:grid-cols-2 gap-8">
                    
                    <!-- Información del Plan -->
                    <div class="space-y-6">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold mb-4 text-gray-800">
                                <i class="fas fa-user mr-2 text-blue-600"></i>
                                Información del Estudiante
                            </h3>
                            <div class="space-y-2">
                                <p><strong>Código:</strong> <?php echo htmlspecialchars($plan['codigo']); ?></p>
                                <p><strong>Especialidad:</strong> <?php echo htmlspecialchars($plan['especialidad']); ?></p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($plan['email']); ?></p>
                                <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($plan['telefono']); ?></p>
                            </div>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold mb-4 text-gray-800">
                                <i class="fas fa-building mr-2 text-green-600"></i>
                                Información de la Empresa
                            </h3>
                            <div class="space-y-2">
                                <p><strong>Empresa:</strong> <?php echo htmlspecialchars($plan['empresa']); ?></p>
                                <p><strong>RUC:</strong> <?php echo htmlspecialchars($plan['ruc']); ?></p>
                                <p><strong>Dirección:</strong> <?php echo htmlspecialchars($plan['direccion_empresa']); ?></p>
                                <p><strong>Supervisor:</strong> <?php echo htmlspecialchars($plan['supervisor']); ?></p>
                                <p><strong>Cargo:</strong> <?php echo htmlspecialchars($plan['cargo_supervisor']); ?></p>
                            </div>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold mb-4 text-gray-800">
                                <i class="fas fa-calendar mr-2 text-purple-600"></i>
                                Detalles de la Práctica
                            </h3>
                            <div class="space-y-2">
                                <p><strong>Fecha Inicio:</strong> <?php echo date('d/m/Y', strtotime($plan['fecha_inicio'])); ?></p>
                                <p><strong>Fecha Fin:</strong> <?php echo date('d/m/Y', strtotime($plan['fecha_fin'])); ?></p>
                                <p><strong>Horario:</strong> <?php echo htmlspecialchars($plan['horario']); ?></p>
                                <p><strong>Total Horas:</strong> <?php echo htmlspecialchars($plan['total_horas']); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Contenido del Plan -->
                    <div class="space-y-6">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold mb-4 text-gray-800">
                                <i class="fas fa-tasks mr-2 text-orange-600"></i>
                                Actividades Propuestas
                            </h3>
                            <div class="bg-white p-3 rounded border">
                                <p class="text-sm text-gray-700 whitespace-pre-wrap"><?php echo htmlspecialchars($plan['actividades']); ?></p>
                            </div>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold mb-4 text-gray-800">
                                <i class="fas fa-bullseye mr-2 text-red-600"></i>
                                Objetivos
                            </h3>
                            <div class="bg-white p-3 rounded border">
                                <p class="text-sm text-gray-700 whitespace-pre-wrap"><?php echo htmlspecialchars($plan['objetivos']); ?></p>
                            </div>
                        </div>

                        <!-- Archivos Adjuntos -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold mb-4 text-gray-800">
                                <i class="fas fa-paperclip mr-2 text-indigo-600"></i>
                                Archivos Adjuntos
                            </h3>
                            <div class="space-y-3">
                                <?php if (!empty($plan['archivo_plan'])): ?>
                                <div class="bg-white p-3 rounded border flex items-center justify-between">
                                    <div class="flex items-center">
                                        <i class="fas fa-file-pdf text-red-500 mr-3 text-xl"></i>
                                        <div>
                                            <p class="font-medium text-gray-800">Plan de Práctica</p>
                                            <p class="text-sm text-gray-500"><?php echo basename($plan['archivo_plan']); ?></p>
                                        </div>
                                    </div>
                                    <div class="flex space-x-2">
                                        <a href="../download_plan_file.php?file=<?php echo urlencode($plan['archivo_plan']); ?>&estudiante=<?php echo $plan['estudiante_id']; ?>" 
                                           class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm transition"
                                           target="_blank">
                                            <i class="fas fa-download mr-1"></i>Descargar
                                        </a>
                                        <a href="../download_plan_file.php?file=<?php echo urlencode($plan['archivo_plan']); ?>&estudiante=<?php echo $plan['estudiante_id']; ?>&view=1" 
                                           class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm transition"
                                           target="_blank">
                                            <i class="fas fa-eye mr-1"></i>Ver
                                        </a>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($plan['archivo_documento1'])): ?>
                                <div class="bg-white p-3 rounded border flex items-center justify-between">
                                    <div class="flex items-center">
                                        <i class="fas fa-file-alt text-blue-500 mr-3 text-xl"></i>
                                        <div>
                                            <p class="font-medium text-gray-800">Documento Adicional 1</p>
                                            <p class="text-sm text-gray-500"><?php echo basename($plan['archivo_documento1']); ?></p>
                                        </div>
                                    </div>
                                    <div class="flex space-x-2">
                                        <a href="../download_plan_file.php?file=<?php echo urlencode($plan['archivo_documento1']); ?>&estudiante=<?php echo $plan['estudiante_id']; ?>" 
                                           class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm transition"
                                           target="_blank">
                                            <i class="fas fa-download mr-1"></i>Descargar
                                        </a>
                                        <a href="../download_plan_file.php?file=<?php echo urlencode($plan['archivo_documento1']); ?>&estudiante=<?php echo $plan['estudiante_id']; ?>&view=1" 
                                           class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm transition"
                                           target="_blank">
                                            <i class="fas fa-eye mr-1"></i>Ver
                                        </a>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($plan['archivo_documento2'])): ?>
                                <div class="bg-white p-3 rounded border flex items-center justify-between">
                                    <div class="flex items-center">
                                        <i class="fas fa-file-alt text-green-500 mr-3 text-xl"></i>
                                        <div>
                                            <p class="font-medium text-gray-800">Documento Adicional 2</p>
                                            <p class="text-sm text-gray-500"><?php echo basename($plan['archivo_documento2']); ?></p>
                                        </div>
                                    </div>
                                    <div class="flex space-x-2">
                                        <a href="../download_plan_file.php?file=<?php echo urlencode($plan['archivo_documento2']); ?>&estudiante=<?php echo $plan['estudiante_id']; ?>" 
                                           class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm transition"
                                           target="_blank">
                                            <i class="fas fa-download mr-1"></i>Descargar
                                        </a>
                                        <a href="../download_plan_file.php?file=<?php echo urlencode($plan['archivo_documento2']); ?>&estudiante=<?php echo $plan['estudiante_id']; ?>&view=1" 
                                           class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm transition"
                                           target="_blank">
                                            <i class="fas fa-eye mr-1"></i>Ver
                                        </a>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($plan['archivo_documento3'])): ?>
                                <div class="bg-white p-3 rounded border flex items-center justify-between">
                                    <div class="flex items-center">
                                        <i class="fas fa-file-alt text-purple-500 mr-3 text-xl"></i>
                                        <div>
                                            <p class="font-medium text-gray-800">Documento Adicional 3</p>
                                            <p class="text-sm text-gray-500"><?php echo basename($plan['archivo_documento3']); ?></p>
                                        </div>
                                    </div>
                                    <div class="flex space-x-2">
                                        <a href="../download_plan_file.php?file=<?php echo urlencode($plan['archivo_documento3']); ?>&estudiante=<?php echo $plan['estudiante_id']; ?>" 
                                           class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm transition"
                                           target="_blank">
                                            <i class="fas fa-download mr-1"></i>Descargar
                                        </a>
                                        <a href="../download_plan_file.php?file=<?php echo urlencode($plan['archivo_documento3']); ?>&estudiante=<?php echo $plan['estudiante_id']; ?>&view=1" 
                                           class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm transition"
                                           target="_blank">
                                            <i class="fas fa-eye mr-1"></i>Ver
                                        </a>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if (empty($plan['archivo_plan']) && empty($plan['archivo_documento1']) && empty($plan['archivo_documento2']) && empty($plan['archivo_documento3'])): ?>
                                <div class="bg-white p-3 rounded border text-center">
                                    <i class="fas fa-inbox text-gray-400 text-2xl mb-2"></i>
                                    <p class="text-gray-500">No hay archivos adjuntos</p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Formulario de Revisión -->
                        <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200">
                            <h3 class="text-lg font-semibold mb-4 text-gray-800">
                                <i class="fas fa-edit mr-2 text-yellow-600"></i>
                                Revisión del Docente
                            </h3>
                            
                            <form method="POST" class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Estado del Plan *
                                    </label>
                                    <select name="estado" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">Seleccionar...</option>
                                        <option value="aprobado" class="text-green-600">✓ Aprobar Plan</option>
                                        <option value="rechazado" class="text-red-600">✗ Rechazar Plan</option>
                                        <option value="revision" class="text-yellow-600">⚠ Requiere Revisión</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Calificación (0-20) *
                                    </label>
                                    <input type="number" name="calificacion" min="0" max="20" step="0.1" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Comentarios *
                                    </label>
                                    <textarea name="comentarios" required rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Comentarios sobre el plan de práctica..."></textarea>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Observaciones y Recomendaciones
                                    </label>
                                    <textarea name="observaciones" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Observaciones adicionales o recomendaciones..."></textarea>
                                </div>

                                <div class="flex gap-4 pt-4">
                                    <a href="docente_dashboard.php" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded-md transition text-center">
                                        <i class="fas fa-arrow-left mr-2"></i>Volver
                                    </a>
                                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-md transition">
                                        <i class="fas fa-check mr-2"></i>Enviar Revisión
                                    </button>
                                </div>
                            </form>
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
    </script>

</body>
</html>