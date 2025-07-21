<?php
require_once '../config/session.php';
require_once '../models/ReporteSemanal.php';

// Verificar que el usuario esté autenticado y sea estudiante
if (!isAuthenticated() || $_SESSION['user_type'] !== 'estudiante') {
    header('Location: login_estudiante.php');
    exit();
}

$reporteModel = new ReporteSemanal();
$reportes = $reporteModel->getByEstudiante($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Reportes Semanales - SYSPRE 2025</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen">

    <!-- Header -->
    <header class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
            <a href="estudiante_dashboard.php" class="text-xl font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-arrow-left"></i>
                <i class="fas fa-graduation-cap"></i>
                SYSPRE 2025
            </a>
            <div class="text-sm text-gray-600">
                Reportes Semanales
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-lg">
            
            <!-- Header -->
            <div class="bg-blue-600 text-white p-6 rounded-t-lg">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold">
                            <i class="fas fa-file-alt mr-2"></i>
                            Mis Reportes Semanales
                        </h1>
                        <p class="mt-2 opacity-90">
                            Gestiona tus reportes semanales de prácticas
                        </p>
                    </div>
                    <a href="registro_reporte_semanal.php" class="bg-white text-blue-600 px-4 py-2 rounded-lg font-semibold hover:bg-gray-100 transition">
                        <i class="fas fa-plus mr-2"></i>Nuevo Reporte
                    </a>
                </div>
            </div>

            <!-- Estadísticas -->
            <div class="p-6 border-b border-gray-200">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-blue-600"><?php echo count($reportes); ?></div>
                        <div class="text-sm text-gray-600">Total Reportes</div>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-green-600">
                            <?php echo count(array_filter($reportes, fn($r) => $r['estado'] == 'aprobado')); ?>
                        </div>
                        <div class="text-sm text-gray-600">Aprobados</div>
                    </div>
                    <div class="bg-yellow-50 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-yellow-600">
                            <?php echo count(array_filter($reportes, fn($r) => $r['estado'] == 'pendiente')); ?>
                        </div>
                        <div class="text-sm text-gray-600">Pendientes</div>
                    </div>
                    <div class="bg-purple-50 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-purple-600">
                            <?php 
                            $totalHoras = array_sum(array_column($reportes, 'total_horas'));
                            echo $totalHoras;
                            ?>
                        </div>
                        <div class="text-sm text-gray-600">Total Horas</div>
                    </div>
                </div>
            </div>

            <!-- Lista de Reportes -->
            <div class="p-6">
                <?php if (empty($reportes)): ?>
                    <div class="text-center py-12">
                        <i class="fas fa-file-alt text-6xl text-gray-300 mb-4"></i>
                        <h3 class="text-xl font-semibold text-gray-600 mb-2">No hay reportes registrados</h3>
                        <p class="text-gray-500 mb-6">Comienza creando tu primer reporte semanal</p>
                        <a href="registro_reporte_semanal.php" class="bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
                            <i class="fas fa-plus mr-2"></i>Crear Primer Reporte
                        </a>
                    </div>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($reportes as $reporte): ?>
                            <div class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition">
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-800">
                                            Reporte Semanal
                                        </h3>
                                        <p class="text-sm text-gray-600">
                                            Período: <?php echo date('d/m/Y', strtotime($reporte['fecha_inicio'])); ?> - 
                                            <?php echo date('d/m/Y', strtotime($reporte['fecha_fin'])); ?>
                                        </p>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <span class="px-3 py-1 rounded-full text-sm font-medium 
                                            <?php 
                                            switch($reporte['estado']) {
                                                case 'aprobado':
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
                                            <?php echo ucfirst($reporte['estado']); ?>
                                        </span>
                                        <?php if ($reporte['calificacion_docente']): ?>
                                            <span class="px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                                Nota: <?php echo $reporte['calificacion_docente']; ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="grid md:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <p class="text-sm text-gray-600">
                                            <i class="fas fa-clock mr-2"></i>
                                            <strong>Horas:</strong> <?php echo $reporte['total_horas']; ?>
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            <i class="fas fa-user-tie mr-2"></i>
                                            <strong>Asesor:</strong> <?php echo htmlspecialchars($reporte['asesor_empresarial']); ?>
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">
                                            <i class="fas fa-building mr-2"></i>
                                            <strong>Área:</strong> <?php echo htmlspecialchars($reporte['area_trabajo']); ?>
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            <i class="fas fa-calendar mr-2"></i>
                                            <strong>Creado:</strong> <?php echo date('d/m/Y H:i', strtotime($reporte['fecha_creacion'])); ?>
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="bg-gray-50 p-3 rounded-lg mb-4">
                                    <h4 class="font-medium text-gray-800 mb-2">Actividades Realizadas:</h4>
                                    <p class="text-sm text-gray-700">
                                        <?php echo htmlspecialchars(substr($reporte['actividades'], 0, 200)) . '...'; ?>
                                    </p>
                                </div>
                                
                                <?php if ($reporte['comentarios_docente']): ?>
                                    <div class="bg-blue-50 p-3 rounded-lg mb-4">
                                        <h4 class="font-medium text-blue-800 mb-2">Comentarios del Docente:</h4>
                                        <p class="text-sm text-blue-700">
                                            <?php echo htmlspecialchars($reporte['comentarios_docente']); ?>
                                        </p>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="flex justify-between items-center">
                                    <div class="flex space-x-2">
                                        <a href="ver_reporte_estudiante.php?id=<?php echo $reporte['id']; ?>" 
                                           class="text-blue-600 hover:text-blue-800 text-sm font-medium transition">
                                            <i class="fas fa-eye mr-1"></i>Ver Detalles
                                        </a>
                                        <?php if ($reporte['estado'] == 'pendiente'): ?>
                                            <a href="editar_reporte_semanal.php?id=<?php echo $reporte['id']; ?>" 
                                               class="text-yellow-600 hover:text-yellow-800 text-sm font-medium transition">
                                                <i class="fas fa-edit mr-1"></i>Editar
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($reporte['estado'] == 'calificado'): ?>
                                            <span class="text-green-600 text-sm font-medium">
                                                <i class="fas fa-check-circle mr-1"></i>Calificado
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        ID: <?php echo $reporte['id']; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

</body>
</html>