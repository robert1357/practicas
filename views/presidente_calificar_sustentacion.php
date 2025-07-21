<?php
require_once '../config/session.php';
require_once '../models/Sustentacion.php';
require_once '../models/InformeFinal.php';

// Verificar autenticación
if (!isAuthenticated() || !hasRole('docente')) {
    header("Location: login_docente.php");
    exit();
}

$user = getCurrentUser();
$sustentacion = new Sustentacion();
$informe = new InformeFinal();

$sustentacion_id = $_GET['sustentacion_id'] ?? null;
if (!$sustentacion_id) {
    header("Location: docente_dashboard.php");
    exit();
}

$sustentacionData = $sustentacion->findById($sustentacion_id);
if (!$sustentacionData || $sustentacionData['presidente_jurado'] != $user['id']) {
    header("Location: docente_dashboard.php");
    exit();
}

// Obtener informe del estudiante
$informeEstudiante = $informe->getByEstudiante($sustentacionData['estudiante_id']);

// Procesar formulario de calificación
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $accion = $_POST['accion'] ?? '';
    $calificacion = $_POST['calificacion'] ?? '';
    $observaciones = $_POST['observaciones'] ?? '';
    
    if ($accion == 'aprobar' && $calificacion && $observaciones) {
        if ($sustentacion->aprobarSustentacion($sustentacion_id, $user['id'], $calificacion, $observaciones)) {
            $mensaje = "Sustentación aprobada exitosamente.";
            $tipo_mensaje = "success";
            // Recargar datos
            $sustentacionData = $sustentacion->findById($sustentacion_id);
        } else {
            $mensaje = "Error al aprobar la sustentación.";
            $tipo_mensaje = "error";
        }
    } elseif ($accion == 'rechazar' && $observaciones) {
        if ($sustentacion->rechazarSustentacion($sustentacion_id, $user['id'], $observaciones)) {
            $mensaje = "Sustentación rechazada.";
            $tipo_mensaje = "warning";
            // Recargar datos
            $sustentacionData = $sustentacion->findById($sustentacion_id);
        } else {
            $mensaje = "Error al rechazar la sustentación.";
            $tipo_mensaje = "error";
        }
    } else {
        $mensaje = "Datos incompletos. Verifique los campos requeridos.";
        $tipo_mensaje = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calificar Sustentación - SYSPRE 2025</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        principal: '#3aa87a',
                        fondo: '#f1f5f9',
                        texto: '#1e293b',
                        gris: '#64748b',
                    },
                },
            },
        };
    </script>
</head>
<body class="bg-fondo text-texto font-sans">
    <!-- Navigation -->
    <nav class="bg-white shadow-md fixed w-full top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
            <a href="docente_dashboard.php" class="text-xl font-bold text-texto flex items-center gap-2">
                <i class="fas fa-gavel"></i> SYSPRE 2025 - Presidente Jurado
            </a>
            <div class="space-x-6 hidden md:flex">
                <a href="docente_dashboard.php" class="text-gris hover:text-texto transition">
                    <i class="fas fa-arrow-left mr-1"></i>Volver al Dashboard
                </a>
                <a href="../config/session.php?logout=1" class="text-gris hover:text-texto transition">
                    <i class="fas fa-sign-out-alt mr-1"></i>Cerrar Sesión
                </a>
            </div>
        </div>
    </nav>

    <main class="pt-24 max-w-6xl mx-auto px-4">
        <?php if (isset($mensaje)): ?>
        <div class="mb-6 p-4 rounded-lg <?php echo $tipo_mensaje == 'success' ? 'bg-green-100 text-green-800' : ($tipo_mensaje == 'warning' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'); ?>">
            <i class="fas fa-<?php echo $tipo_mensaje == 'success' ? 'check-circle' : ($tipo_mensaje == 'warning' ? 'exclamation-triangle' : 'times-circle'); ?> mr-2"></i>
            <?php echo htmlspecialchars($mensaje); ?>
        </div>
        <?php endif; ?>

        <!-- Header -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <h1 class="text-3xl font-bold text-texto flex items-center mb-4">
                <i class="fas fa-gavel mr-3 text-principal"></i>
                Calificar Sustentación
            </h1>
            <div class="bg-blue-50 rounded-lg p-4">
                <h2 class="text-xl font-semibold text-blue-800 mb-2">Información de la Sustentación</h2>
                <div class="grid md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <p><strong>Estudiante:</strong> <?php echo htmlspecialchars($sustentacionData['estudiante_nombres'] . ' ' . $sustentacionData['estudiante_apellidos']); ?></p>
                        <p><strong>Código:</strong> <?php echo htmlspecialchars($sustentacionData['estudiante_codigo']); ?></p>
                        <p><strong>Fecha:</strong> <?php echo date('d/m/Y', strtotime($sustentacionData['fecha_sustentacion'])); ?></p>
                        <p><strong>Hora:</strong> <?php echo date('H:i', strtotime($sustentacionData['hora_sustentacion'])); ?></p>
                    </div>
                    <div>
                        <p><strong>Lugar:</strong> <?php echo htmlspecialchars($sustentacionData['lugar']); ?></p>
                        <p><strong>Vocal:</strong> <?php echo htmlspecialchars($sustentacionData['vocal_nombres'] . ' ' . $sustentacionData['vocal_apellidos']); ?></p>
                        <p><strong>Secretario:</strong> <?php echo htmlspecialchars($sustentacionData['secretario_nombres'] . ' ' . $sustentacionData['secretario_apellidos']); ?></p>
                        <p><strong>Estado:</strong> 
                            <span class="px-2 py-1 rounded text-xs <?php 
                                echo $sustentacionData['estado'] == 'aprobado' ? 'bg-green-100 text-green-800' : 
                                    ($sustentacionData['estado'] == 'rechazado' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800'); 
                            ?>">
                                <?php echo ucfirst($sustentacionData['estado']); ?>
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Informe del Estudiante -->
        <?php if ($informeEstudiante): ?>
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <h2 class="text-2xl font-bold text-texto mb-4 flex items-center">
                <i class="fas fa-file-alt mr-3 text-principal"></i>
                Informe Final del Estudiante
            </h2>
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <h3 class="font-semibold text-gray-700 mb-2">Título del Informe</h3>
                    <p class="text-sm text-gray-600 mb-4"><?php echo htmlspecialchars($informeEstudiante['titulo'] ?? 'No especificado'); ?></p>
                    
                    <h3 class="font-semibold text-gray-700 mb-2">Resumen Ejecutivo</h3>
                    <p class="text-sm text-gray-600 mb-4"><?php echo htmlspecialchars(substr($informeEstudiante['resumen_ejecutivo'] ?? '', 0, 200)) . '...'; ?></p>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-700 mb-2">Calificación del Docente</h3>
                    <p class="text-sm text-gray-600 mb-4"><?php echo htmlspecialchars($informeEstudiante['calificacion_docente'] ?? 'Sin calificar'); ?></p>
                    
                    <h3 class="font-semibold text-gray-700 mb-2">Estado del Informe</h3>
                    <span class="px-2 py-1 rounded text-xs <?php 
                        echo $informeEstudiante['estado'] == 'aprobado_final' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; 
                    ?>">
                        <?php echo ucfirst($informeEstudiante['estado']); ?>
                    </span>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Formulario de Calificación -->
        <?php if ($sustentacionData['estado'] == 'programado'): ?>
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <h2 class="text-2xl font-bold text-texto mb-6 flex items-center">
                <i class="fas fa-star mr-3 text-principal"></i>
                Calificar Sustentación
            </h2>
            
            <form method="POST" class="space-y-6">
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label for="calificacion" class="block text-sm font-medium text-gray-700 mb-2">
                            Calificación (0-20)
                        </label>
                        <input type="number" id="calificacion" name="calificacion" 
                               min="0" max="20" step="0.1" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-principal">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Decisión
                        </label>
                        <div class="flex gap-4">
                            <label class="flex items-center">
                                <input type="radio" name="accion" value="aprobar" required class="mr-2">
                                <span class="text-green-600">Aprobar</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="accion" value="rechazar" required class="mr-2">
                                <span class="text-red-600">Rechazar</span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <div>
                    <label for="observaciones" class="block text-sm font-medium text-gray-700 mb-2">
                        Observaciones y Comentarios
                    </label>
                    <textarea id="observaciones" name="observaciones" rows="4" required
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-principal"
                              placeholder="Detalle los aspectos evaluados, fortalezas y áreas de mejora..."></textarea>
                </div>
                
                <div class="flex justify-end space-x-4">
                    <a href="docente_dashboard.php" 
                       class="px-6 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition">
                        Cancelar
                    </a>
                    <button type="submit" 
                            class="px-6 py-2 bg-principal text-white rounded-md hover:bg-green-600 transition">
                        <i class="fas fa-save mr-2"></i>Guardar Calificación
                    </button>
                </div>
            </form>
        </div>
        <?php elseif ($sustentacionData['estado'] == 'aprobado'): ?>
        <div class="bg-green-50 rounded-lg p-6 mb-8">
            <h2 class="text-2xl font-bold text-green-800 mb-4 flex items-center">
                <i class="fas fa-check-circle mr-3"></i>
                Sustentación Aprobada
            </h2>
            <div class="space-y-2">
                <p><strong>Calificación Final:</strong> <?php echo htmlspecialchars($sustentacionData['calificacion_final']); ?></p>
                <p><strong>Fecha de Aprobación:</strong> <?php echo date('d/m/Y H:i', strtotime($sustentacionData['fecha_aprobacion'])); ?></p>
                <p><strong>Observaciones:</strong></p>
                <p class="text-sm bg-white p-3 rounded border"><?php echo htmlspecialchars($sustentacionData['observaciones_aprobacion']); ?></p>
            </div>
        </div>
        <?php elseif ($sustentacionData['estado'] == 'rechazado'): ?>
        <div class="bg-red-50 rounded-lg p-6 mb-8">
            <h2 class="text-2xl font-bold text-red-800 mb-4 flex items-center">
                <i class="fas fa-times-circle mr-3"></i>
                Sustentación Rechazada
            </h2>
            <div class="space-y-2">
                <p><strong>Fecha de Rechazo:</strong> <?php echo date('d/m/Y H:i', strtotime($sustentacionData['fecha_aprobacion'])); ?></p>
                <p><strong>Observaciones:</strong></p>
                <p class="text-sm bg-white p-3 rounded border"><?php echo htmlspecialchars($sustentacionData['observaciones_aprobacion']); ?></p>
            </div>
        </div>
        <?php endif; ?>
    </main>

    <script>
        // Habilitar/deshabilitar campo de calificación según la decisión
        document.querySelectorAll('input[name="accion"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const calificacionInput = document.getElementById('calificacion');
                if (this.value === 'rechazar') {
                    calificacionInput.disabled = true;
                    calificacionInput.value = '';
                } else {
                    calificacionInput.disabled = false;
                }
            });
        });
    </script>
</body>
</html>