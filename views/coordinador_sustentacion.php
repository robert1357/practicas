<?php
require_once '../config/session.php';
require_once '../models/User.php';
require_once '../models/Sustentacion.php';

// Verificar que el usuario esté autenticado y sea coordinador
if (!isAuthenticated() || $_SESSION['user_type'] !== 'coordinador') {
    header('Location: login_coordinador.php');
    exit();
}

$estudiante_id = $_GET['estudiante'] ?? null;
if (!$estudiante_id) {
    header('Location: coordinador_dashboard.php');
    exit();
}

$userModel = new User();
$sustentacionModel = new Sustentacion();

$estudiante = $userModel->getById($estudiante_id);
if (!$estudiante) {
    header('Location: coordinador_dashboard.php');
    exit();
}

// Obtener coordinador actual para filtrar por especialidad
$coordinador = getCurrentUser();

// Obtener docentes para el jurado de la misma especialidad
$docentes = $userModel->getByType('docente', $coordinador['especialidad']);

// Obtener sustentaciones programadas
$sustentaciones = $sustentacionModel->getByEstudiante($estudiante_id);

// Procesamiento del formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validar que no se repitan los miembros del jurado
    $presidente = $_POST['presidente_jurado'];
    $vocal = $_POST['vocal_jurado'];
    $secretario = $_POST['secretario_jurado'];
    
    if ($presidente == $vocal || $presidente == $secretario || $vocal == $secretario) {
        $error = "No se puede seleccionar el mismo docente para diferentes roles en el jurado";
    } else {
        $data = [
            'estudiante_id' => $estudiante_id,
            'fecha_sustentacion' => $_POST['fecha_sustentacion'],
            'hora_sustentacion' => $_POST['hora_sustentacion'],
            'lugar' => $_POST['lugar'],
            'modalidad' => $_POST['modalidad'],
            'presidente_jurado' => $presidente,
            'vocal_jurado' => $vocal,
            'secretario_jurado' => $secretario,
            'observaciones' => $_POST['observaciones'],
            'estado' => 'programado'
        ];
        
        $result = $sustentacionModel->create($data);
        if ($result) {
            $success = "Sustentación programada exitosamente";
            $sustentaciones = $sustentacionModel->getByEstudiante($estudiante_id);
        } else {
            $error = "Error al programar la sustentación";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Programar Sustentación - SYSPRE 2025</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen">

    <!-- Header -->
    <header class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
            <a href="coordinador_dashboard.php" class="text-xl font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-arrow-left"></i>
                <i class="fas fa-user-tie"></i>
                SYSPRE 2025
            </a>
            <div class="text-sm text-gray-600">
                Programar Sustentación
            </div>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            
            <!-- Header -->
            <div class="bg-indigo-600 text-white p-6">
                <h1 class="text-2xl font-bold">
                    <i class="fas fa-calendar-plus mr-2"></i>
                    Programar Sustentación
                </h1>
                <p class="mt-2 opacity-90">
                    Estudiante: <?php echo htmlspecialchars($estudiante['nombres'] . ' ' . $estudiante['apellidos']); ?>
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
                    
                    <!-- Información del Estudiante -->
                    <div class="lg:col-span-1">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold mb-4 text-gray-800">
                                <i class="fas fa-user-graduate mr-2 text-blue-600"></i>
                                Información del Estudiante
                            </h3>
                            <div class="space-y-2 text-sm">
                                <p><strong>Código:</strong> <?php echo htmlspecialchars($estudiante['codigo']); ?></p>
                                <p><strong>Especialidad:</strong> <?php echo htmlspecialchars($estudiante['especialidad']); ?></p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($estudiante['email']); ?></p>
                                <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($estudiante['telefono']); ?></p>
                                <p><strong>Semestre:</strong> <?php echo htmlspecialchars($estudiante['semestre']); ?></p>
                            </div>
                        </div>

                        <!-- Sustentaciones Programadas -->
                        <div class="bg-gray-50 p-4 rounded-lg mt-6">
                            <h3 class="text-lg font-semibold mb-4 text-gray-800">
                                <i class="fas fa-calendar-check mr-2 text-green-600"></i>
                                Sustentaciones Programadas
                            </h3>
                            <div class="space-y-3">
                                <?php if (empty($sustentaciones)): ?>
                                    <div class="text-center py-4">
                                        <i class="fas fa-calendar-times text-3xl text-gray-300 mb-2"></i>
                                        <p class="text-gray-500 text-sm">No hay sustentaciones programadas</p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($sustentaciones as $sustentacion): ?>
                                        <div class="bg-white p-3 rounded-lg border border-gray-200">
                                            <div class="flex justify-between items-start mb-2">
                                                <span class="font-medium text-gray-900">
                                                    <?php echo !empty($sustentacion['fecha_sustentacion']) ? date('d/m/Y', strtotime($sustentacion['fecha_sustentacion'])) : 'Sin fecha'; ?>
                                                </span>
                                                <span class="px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                    <?php echo !empty($sustentacion['estado']) ? ucfirst($sustentacion['estado']) : 'Sin estado'; ?>
                                                </span>
                                            </div>
                                            <p class="text-sm text-gray-600">
                                                <i class="fas fa-clock mr-1"></i>
                                                <?php echo !empty($sustentacion['hora_sustentacion']) ? $sustentacion['hora_sustentacion'] : 'Sin hora'; ?>
                                            </p>
                                            <p class="text-sm text-gray-600">
                                                <i class="fas fa-map-marker-alt mr-1"></i>
                                                <?php echo !empty($sustentacion['lugar']) ? htmlspecialchars($sustentacion['lugar']) : 'Sin lugar'; ?>
                                            </p>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Formulario de Programación -->
                    <div class="lg:col-span-2">
                        <div class="bg-indigo-50 p-6 rounded-lg border border-indigo-200">
                            <h3 class="text-lg font-semibold mb-4 text-gray-800">
                                <i class="fas fa-calendar-plus mr-2 text-indigo-600"></i>
                                Nueva Sustentación
                            </h3>
                            
                            <form method="POST" class="space-y-6">
                                
                                <!-- Fecha y Hora -->
                                <div class="grid md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Fecha de Sustentación *
                                        </label>
                                        <input type="date" name="fecha_sustentacion" required 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Hora de Sustentación *
                                        </label>
                                        <input type="time" name="hora_sustentacion" required 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    </div>
                                </div>

                                <!-- Lugar y Modalidad -->
                                <div class="grid md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Lugar *
                                        </label>
                                        <input type="text" name="lugar" required 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                               placeholder="Aula, laboratorio, etc.">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Modalidad *
                                        </label>
                                        <select name="modalidad" required 
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                            <option value="">Seleccionar...</option>
                                            <option value="presencial">Presencial</option>
                                            <option value="virtual">Virtual</option>
                                            <option value="mixta">Mixta</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Jurado -->
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h4 class="text-md font-semibold mb-4 text-gray-800">
                                        <i class="fas fa-gavel mr-2 text-yellow-600"></i>
                                        Composición del Jurado
                                    </h4>
                                    
                                    <div class="grid md:grid-cols-3 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                                Presidente *
                                            </label>
                                            <select name="presidente_jurado" required 
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                                <option value="">Seleccionar...</option>
                                                <?php foreach ($docentes as $docente): ?>
                                                    <option value="<?php echo $docente['id']; ?>">
                                                        <?php echo htmlspecialchars($docente['nombres'] . ' ' . $docente['apellidos']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                                Vocal *
                                            </label>
                                            <select name="vocal_jurado" required 
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                                <option value="">Seleccionar...</option>
                                                <?php foreach ($docentes as $docente): ?>
                                                    <option value="<?php echo $docente['id']; ?>">
                                                        <?php echo htmlspecialchars($docente['nombres'] . ' ' . $docente['apellidos']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                                Secretario *
                                            </label>
                                            <select name="secretario_jurado" required 
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                                <option value="">Seleccionar...</option>
                                                <?php foreach ($docentes as $docente): ?>
                                                    <option value="<?php echo $docente['id']; ?>">
                                                        <?php echo htmlspecialchars($docente['nombres'] . ' ' . $docente['apellidos']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Observaciones -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Observaciones
                                    </label>
                                    <textarea name="observaciones" rows="3" 
                                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                              placeholder="Observaciones adicionales sobre la sustentación..."></textarea>
                                </div>

                                <!-- Botones -->
                                <div class="flex gap-4 pt-4">
                                    <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded-md transition">
                                        <i class="fas fa-calendar-plus mr-2"></i>Programar Sustentación
                                    </button>
                                    <a href="coordinador_dashboard.php" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded-md transition text-center">
                                        <i class="fas fa-arrow-left mr-2"></i>Volver
                                    </a>
                                </div>
                            </form>
                        </div>

                        <!-- Instrucciones -->
                        <div class="bg-yellow-50 p-4 rounded-lg mt-6 border border-yellow-200">
                            <h4 class="text-md font-semibold mb-3 text-yellow-800">
                                <i class="fas fa-info-circle mr-2"></i>
                                Instrucciones Importantes
                            </h4>
                            <ul class="text-sm text-yellow-700 space-y-1">
                                <li>• Verificar la disponibilidad del aula antes de programar</li>
                                <li>• Asegurarse de que los docentes del jurado estén disponibles</li>
                                <li>• Notificar al estudiante con al menos 7 días de anticipación</li>
                                <li>• Para sustentaciones virtuales, incluir el enlace en observaciones</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Establecer fecha mínima (mañana)
        document.querySelector('input[name="fecha_sustentacion"]').min = new Date(Date.now() + 86400000).toISOString().split('T')[0];
    </script>

</body>
</html>