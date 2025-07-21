<?php
require_once '../config/session.php';
require_once '../models/User.php';
require_once '../models/AsignacionDocente.php';

// Verificar que el usuario esté autenticado y sea coordinador
if (!isAuthenticated() || $_SESSION['user_type'] !== 'coordinador') {
    header('Location: login_coordinador.php');
    exit();
}

$estudiante_id = $_GET['estudiante_id'] ?? null;
if (!$estudiante_id) {
    header('Location: coordinador_dashboard.php');
    exit();
}

$userModel = new User();
$asignacionModel = new AsignacionDocente();

$estudiante = $userModel->findById($estudiante_id);
if (!$estudiante) {
    header('Location: coordinador_dashboard.php');
    exit();
}

// Obtener coordinador actual para filtrar por especialidad
$coordinador = getCurrentUser();

// Obtener docentes de la misma especialidad del coordinador
$docentes = $userModel->getByType('docente', $coordinador['especialidad']);

// Obtener asignaciones existentes
$asignaciones = $asignacionModel->getByEstudiante($estudiante_id);

// Procesamiento del formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tipo_asignacion = $_POST['tipo_asignacion'];
    $docente_id = $_POST['docente_id'];
    
    if ($tipo_asignacion == 'asesor') {
        // Asignar asesor
        $data = [
            'estudiante_id' => $estudiante_id,
            'docente_asesor_id' => $docente_id,
            'fecha_asignacion' => date('Y-m-d H:i:s'),
            'estado' => 'activo'
        ];
        $result = $asignacionModel->asignarAsesor($data);
    } else {
        // Asignar jurado
        $data = [
            'estudiante_id' => $estudiante_id,
            'docente_jurado_id' => $docente_id,
            'fecha_asignacion' => date('Y-m-d H:i:s'),
            'estado' => 'activo'
        ];
        $result = $asignacionModel->asignarJurado($data);
    }
    
    if ($result) {
        $success = ucfirst($tipo_asignacion) . " asignado exitosamente";
        // Actualizar las asignaciones
        $asignaciones = $asignacionModel->getByEstudiante($estudiante_id);
    } else {
        $error = "Error al asignar " . $tipo_asignacion;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asignar Docente - SYSPRE 2025</title>
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
                Asignación de Docentes
            </div>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            
            <!-- Header -->
            <div class="bg-yellow-600 text-white p-6">
                <h1 class="text-2xl font-bold">
                    <i class="fas fa-users mr-2"></i>
                    Asignar Docente
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
                                <p><strong>Semestre:</strong> <?php echo htmlspecialchars($estudiante['semestre']); ?></p>
                            </div>
                        </div>

                        <!-- Asignaciones Actuales -->
                        <div class="bg-gray-50 p-4 rounded-lg mt-6">
                            <h3 class="text-lg font-semibold mb-4 text-gray-800">
                                <i class="fas fa-check-circle mr-2 text-green-600"></i>
                                Asignaciones Actuales
                            </h3>
                            <div class="space-y-3">
                                <?php if (!empty($asignaciones['asesor'])): ?>
                                    <div class="bg-green-100 p-3 rounded border border-green-200">
                                        <p class="font-medium text-green-800">
                                            <i class="fas fa-user-tie mr-2"></i>Asesor Asignado
                                        </p>
                                        <p class="text-sm text-green-700">
                                            <?php echo htmlspecialchars($asignaciones['asesor']['nombres'] . ' ' . $asignaciones['asesor']['apellidos']); ?>
                                        </p>
                                    </div>
                                <?php else: ?>
                                    <div class="bg-yellow-100 p-3 rounded border border-yellow-200">
                                        <p class="text-yellow-800">
                                            <i class="fas fa-exclamation-triangle mr-2"></i>Sin asesor asignado
                                        </p>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($asignaciones['jurado'])): ?>
                                    <div class="bg-blue-100 p-3 rounded border border-blue-200">
                                        <p class="font-medium text-blue-800">
                                            <i class="fas fa-gavel mr-2"></i>Jurado Asignado
                                        </p>
                                        <p class="text-sm text-blue-700">
                                            <?php echo htmlspecialchars($asignaciones['jurado']['nombres'] . ' ' . $asignaciones['jurado']['apellidos']); ?>
                                        </p>
                                    </div>
                                <?php else: ?>
                                    <div class="bg-yellow-100 p-3 rounded border border-yellow-200">
                                        <p class="text-yellow-800">
                                            <i class="fas fa-exclamation-triangle mr-2"></i>Sin jurado asignado
                                        </p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Formulario de Asignación -->
                    <div class="lg:col-span-2">
                        <div class="bg-blue-50 p-6 rounded-lg border border-blue-200">
                            <h3 class="text-lg font-semibold mb-4 text-gray-800">
                                <i class="fas fa-user-plus mr-2 text-blue-600"></i>
                                Nueva Asignación
                            </h3>
                            
                            <form method="POST" class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Tipo de Asignación *
                                    </label>
                                    <select name="tipo_asignacion" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">Seleccionar...</option>
                                        <option value="asesor">Asesor de Prácticas</option>
                                        <option value="jurado">Jurado Evaluador</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Docente *
                                    </label>
                                    <select name="docente_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">Seleccionar docente...</option>
                                        <?php foreach ($docentes as $docente): ?>
                                            <option value="<?php echo $docente['id']; ?>">
                                                <?php echo htmlspecialchars($docente['nombres'] . ' ' . $docente['apellidos']); ?>
                                                (<?php echo htmlspecialchars($docente['especialidad']); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="flex gap-4 pt-4">
                                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-md transition">
                                        <i class="fas fa-plus mr-2"></i>Asignar Docente
                                    </button>
                                    <a href="coordinador_dashboard.php" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded-md transition text-center">
                                        <i class="fas fa-arrow-left mr-2"></i>Volver
                                    </a>
                                </div>
                            </form>
                        </div>

                        <!-- Lista de Docentes Disponibles -->
                        <div class="bg-gray-50 p-6 rounded-lg mt-6">
                            <h3 class="text-lg font-semibold mb-4 text-gray-800">
                                <i class="fas fa-list mr-2 text-gray-600"></i>
                                Docentes Disponibles - <?php echo htmlspecialchars($estudiante['especialidad']); ?>
                            </h3>
                            
                            <div class="space-y-3">
                                <?php if (empty($docentes)): ?>
                                    <div class="text-center py-8">
                                        <i class="fas fa-users text-4xl text-gray-300 mb-4"></i>
                                        <p class="text-gray-500">No hay docentes disponibles para esta especialidad</p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($docentes as $docente): ?>
                                        <div class="bg-white p-4 rounded-lg border border-gray-200 hover:shadow-md transition">
                                            <div class="flex justify-between items-start">
                                                <div class="flex-1">
                                                    <h4 class="font-medium text-gray-900">
                                                        <?php echo htmlspecialchars($docente['nombres'] . ' ' . $docente['apellidos']); ?>
                                                    </h4>
                                                    <p class="text-sm text-gray-600">
                                                        <i class="fas fa-envelope mr-1"></i>
                                                        <?php echo htmlspecialchars($docente['email']); ?>
                                                    </p>
                                                    <p class="text-sm text-gray-600">
                                                        <i class="fas fa-graduation-cap mr-1"></i>
                                                        <?php echo htmlspecialchars($docente['especialidad']); ?>
                                                    </p>
                                                </div>
                                                <div class="flex flex-col space-y-1">
                                                    <span class="px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800">
                                                        Disponible
                                                    </span>
                                                    <?php if ($docente['codigo']): ?>
                                                        <span class="text-xs text-gray-500">
                                                            ID: <?php echo htmlspecialchars($docente['codigo']); ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Cambiar color según el tipo de asignación
        document.querySelector('select[name="tipo_asignacion"]').addEventListener('change', function() {
            const select = this;
            const value = select.value;
            
            select.className = 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500';
            
            if (value === 'asesor') {
                select.className += ' bg-green-50 border-green-300';
            } else if (value === 'jurado') {
                select.className += ' bg-blue-50 border-blue-300';
            }
        });
    </script>

</body>
</html>