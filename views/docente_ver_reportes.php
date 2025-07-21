<?php
require_once '../config/session.php';
require_once '../models/ReporteSemanal.php';
require_once '../models/User.php';

// Verificar que el usuario esté autenticado y sea docente
if (!isAuthenticated() || !hasRole('docente')) {
    header('Location: login_docente.php');
    exit();
}

$estudiante_id = $_GET['estudiante'] ?? null;
if (!$estudiante_id) {
    header('Location: docente_dashboard.php');
    exit();
}

$user = getCurrentUser();
$reportes = new ReporteSemanal();
$userModel = new User();

// Obtener datos del estudiante
$estudiante = $userModel->getById($estudiante_id);
if (!$estudiante) {
    header('Location: docente_dashboard.php');
    exit();
}

// Obtener reportes del estudiante
$reportesEstudiante = $reportes->getByEstudiante($estudiante_id);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes del Estudiante - SYSPRE 2025</title>
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

    <!-- NAV -->
    <nav class="bg-white shadow-md fixed w-full top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
            <a href="docente_dashboard.php" class="text-xl font-bold text-texto flex items-center gap-2">
                <i class="fas fa-chalkboard-teacher"></i> SYSPRE 2025 - Docente
            </a>
            <div class="space-x-6 hidden md:flex">
                <a href="docente_dashboard.php" class="text-gris hover:text-texto transition"><i class="fas fa-home mr-1"></i>Dashboard</a>
                <a href="#" class="text-principal font-semibold"><i class="fas fa-file-alt mr-1"></i>Reportes</a>
                <a href="../logout.php" class="text-gris hover:text-texto transition"><i class="fas fa-sign-out-alt mr-1"></i>Cerrar Sesión</a>
            </div>
        </div>
    </nav>

    <main class="pt-24 max-w-7xl mx-auto px-4 pb-10">
        
        <!-- Header -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-texto flex items-center">
                        <i class="fas fa-file-alt mr-3 text-principal"></i>
                        Reportes Semanales
                    </h1>
                    <p class="text-gris mt-2">Estudiante: <?php echo htmlspecialchars($estudiante['nombres'] . ' ' . $estudiante['apellidos']); ?></p>
                    <p class="text-sm text-gris">Código: <?php echo htmlspecialchars($estudiante['codigo']); ?> - <?php echo htmlspecialchars($estudiante['especialidad']); ?></p>
                </div>
                <div class="text-right">
                    <a href="docente_dashboard.php" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-semibold transition flex items-center">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Volver al Dashboard
                    </a>
                </div>
            </div>
        </div>

        <!-- Lista de Reportes -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-2xl font-bold text-texto mb-6 flex items-center">
                <i class="fas fa-list mr-3 text-blue-600"></i>
                Reportes Semanales del Estudiante
            </h2>
            
            <?php if (count($reportesEstudiante) > 0): ?>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="text-left p-3 font-semibold">Período</th>
                                <th class="text-left p-3 font-semibold">Horas</th>
                                <th class="text-left p-3 font-semibold">Estado</th>
                                <th class="text-left p-3 font-semibold">Calificación</th>
                                <th class="text-left p-3 font-semibold">Fecha Envío</th>
                                <th class="text-left p-3 font-semibold">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reportesEstudiante as $reporte): ?>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="p-3">
                                    <div class="font-medium"><?php echo date('d/m/Y', strtotime($reporte['fecha_inicio'])); ?></div>
                                    <div class="text-sm text-gris">al <?php echo date('d/m/Y', strtotime($reporte['fecha_fin'])); ?></div>
                                </td>
                                <td class="p-3">
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-sm">
                                        <?php echo htmlspecialchars($reporte['total_horas']); ?> hrs
                                    </span>
                                </td>
                                <td class="p-3">
                                    <?php if ($reporte['estado'] == 'pendiente'): ?>
                                        <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-sm">
                                            <i class="fas fa-clock mr-1"></i>Pendiente
                                        </span>
                                    <?php else: ?>
                                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-sm">
                                            <i class="fas fa-check mr-1"></i>Calificado
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-3">
                                    <?php if ($reporte['calificacion_docente']): ?>
                                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded font-semibold">
                                            <?php echo htmlspecialchars($reporte['calificacion_docente']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-gray-400 text-sm">Sin calificar</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-3">
                                    <div class="text-sm"><?php echo date('d/m/Y H:i', strtotime($reporte['fecha_creacion'])); ?></div>
                                </td>
                                <td class="p-3">
                                    <div class="flex space-x-2">
                                        <?php if ($reporte['estado'] == 'pendiente'): ?>
                                            <a href="calificar_reporte.php?id=<?php echo $reporte['id']; ?>" 
                                               class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm transition">
                                                <i class="fas fa-star mr-1"></i>Calificar
                                            </a>
                                        <?php else: ?>
                                            <a href="calificar_reporte.php?id=<?php echo $reporte['id']; ?>" 
                                               class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm transition">
                                                <i class="fas fa-eye mr-1"></i>Ver Calificación
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-8">
                    <i class="fas fa-file-alt text-4xl text-gray-400 mb-4"></i>
                    <p class="text-gris">Este estudiante aún no ha enviado reportes semanales.</p>
                    <p class="text-sm text-gris">Los reportes aparecerán aquí una vez que el estudiante los envíe.</p>
                </div>
            <?php endif; ?>
        </div>

    </main>
</body>
</html>