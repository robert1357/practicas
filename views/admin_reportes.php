<?php
require_once '../config/session.php';
require_once '../models/User.php';
require_once '../models/PlanPractica.php';
require_once '../models/ReporteSemanal.php';
require_once '../models/InformeFinal.php';
require_once '../models/Sustentacion.php';

// Verificar que el usuario esté autenticado y sea admin
if (!isAuthenticated() || $_SESSION['user_type'] !== 'admin') {
    header('Location: login_admin.php');
    exit();
}

// Obtener estadísticas generales
$userModel = new User();
$planModel = new PlanPractica();
$reporteModel = new ReporteSemanal();
$informeModel = new InformeFinal();
$sustentacionModel = new Sustentacion();

$usuarios = $userModel->getAll();
$planes = $planModel->getAll();
$reportes = $reporteModel->getAll();
$informes = $informeModel->getAll();
$sustentaciones = $sustentacionModel->getAll();

// Estadísticas por especialidad
$especialidades = [];
foreach ($usuarios as $usuario) {
    if ($usuario['especialidad']) {
        $especialidades[$usuario['especialidad']] = ($especialidades[$usuario['especialidad']] ?? 0) + 1;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes del Sistema - SYSPRE 2025</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-50 min-h-screen">

    <!-- Header -->
    <header class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
            <a href="admin_dashboard.php" class="text-xl font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-arrow-left"></i>
                <i class="fas fa-user-shield"></i>
                SYSPRE 2025
            </a>
            <div class="text-sm text-gray-600">
                Reportes del Sistema
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 py-8">
        
        <!-- Estadísticas Generales -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-8">
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                        <i class="fas fa-users text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">Total Usuarios</p>
                        <p class="text-2xl font-bold text-gray-800"><?php echo count($usuarios); ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-600">
                        <i class="fas fa-file-signature text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">Planes Aprobados</p>
                        <p class="text-2xl font-bold text-gray-800">
                            <?php echo count(array_filter($planes, fn($p) => $p['estado'] == 'aprobado')); ?>
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                        <i class="fas fa-file-alt text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">Reportes Semanales</p>
                        <p class="text-2xl font-bold text-gray-800"><?php echo count($reportes); ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                        <i class="fas fa-clipboard-check text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">Informes Finales</p>
                        <p class="text-2xl font-bold text-gray-800"><?php echo count($informes); ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-red-100 text-red-600">
                        <i class="fas fa-calendar-check text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">Sustentaciones</p>
                        <p class="text-2xl font-bold text-gray-800"><?php echo count($sustentaciones); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráficos -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Gráfico de Usuarios por Tipo -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold mb-4">Usuarios por Tipo</h3>
                <canvas id="userTypeChart"></canvas>
            </div>

            <!-- Gráfico de Estados de Planes -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold mb-4">Estados de Planes de Práctica</h3>
                <canvas id="planStatusChart"></canvas>
            </div>
        </div>

        <!-- Tablas de Datos -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Usuarios por Especialidad -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold mb-4">Usuarios por Especialidad</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Especialidad</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cantidad</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($especialidades as $especialidad => $cantidad): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($especialidad); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo $cantidad; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Sustentaciones Próximas -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold mb-4">Sustentaciones Próximas</h3>
                <?php $proximas = $sustentacionModel->getProximas(); ?>
                <?php if (empty($proximas)): ?>
                    <p class="text-gray-500 text-center py-8">No hay sustentaciones programadas para los próximos 7 días</p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($proximas as $sustentacion): ?>
                            <div class="border-l-4 border-blue-500 pl-4 py-2">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h4 class="font-medium text-gray-900">
                                            <?php echo htmlspecialchars($sustentacion['estudiante_nombres'] . ' ' . $sustentacion['estudiante_apellidos']); ?>
                                        </h4>
                                        <p class="text-sm text-gray-600">
                                            <?php echo htmlspecialchars($sustentacion['estudiante_codigo']); ?>
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-medium text-gray-900">
                                            <?php echo date('d/m/Y', strtotime($sustentacion['fecha_sustentacion'])); ?>
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            <?php echo date('H:i', strtotime($sustentacion['hora_sustentacion'])); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script>
        // Datos para gráfico de usuarios por tipo
        const userTypeData = {
            labels: ['Estudiantes', 'Docentes', 'Coordinadores', 'Administradores'],
            datasets: [{
                data: [
                    <?php echo count(array_filter($usuarios, fn($u) => $u['tipo'] == 'estudiante')); ?>,
                    <?php echo count(array_filter($usuarios, fn($u) => $u['tipo'] == 'docente')); ?>,
                    <?php echo count(array_filter($usuarios, fn($u) => $u['tipo'] == 'coordinador')); ?>,
                    <?php echo count(array_filter($usuarios, fn($u) => $u['tipo'] == 'admin')); ?>
                ],
                backgroundColor: [
                    '#10B981', // Verde para estudiantes
                    '#F59E0B', // Amarillo para docentes
                    '#8B5CF6', // Púrpura para coordinadores
                    '#EF4444'  // Rojo para administradores
                ]
            }]
        };

        // Datos para gráfico de estados de planes
        const planStatusData = {
            labels: ['Aprobados', 'Pendientes', 'En Revisión', 'Rechazados'],
            datasets: [{
                data: [
                    <?php echo count(array_filter($planes, fn($p) => $p['estado'] == 'aprobado')); ?>,
                    <?php echo count(array_filter($planes, fn($p) => $p['estado'] == 'pendiente')); ?>,
                    <?php echo count(array_filter($planes, fn($p) => $p['estado'] == 'revision')); ?>,
                    <?php echo count(array_filter($planes, fn($p) => $p['estado'] == 'rechazado')); ?>
                ],
                backgroundColor: [
                    '#10B981', // Verde para aprobados
                    '#F59E0B', // Amarillo para pendientes
                    '#3B82F6', // Azul para revisión
                    '#EF4444'  // Rojo para rechazados
                ]
            }]
        };

        // Crear gráficos
        const userTypeCtx = document.getElementById('userTypeChart').getContext('2d');
        new Chart(userTypeCtx, {
            type: 'doughnut',
            data: userTypeData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        const planStatusCtx = document.getElementById('planStatusChart').getContext('2d');
        new Chart(planStatusCtx, {
            type: 'pie',
            data: planStatusData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>

</body>
</html>