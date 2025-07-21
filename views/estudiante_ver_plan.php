<?php
require_once '../config/session.php';
require_once '../models/PlanPractica.php';

// Verificar que el usuario esté autenticado y sea estudiante
if (!isAuthenticated() || $_SESSION['user_type'] !== 'estudiante') {
    header('Location: login_estudiante.php');
    exit();
}

$planModel = new PlanPractica();
$plan = $planModel->getByEstudiante($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Plan de Práctica - SYSPRE 2025</title>
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
                Mi Plan de Práctica
            </div>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-lg">
            
            <!-- Header -->
            <div class="bg-green-600 text-white p-6 rounded-t-lg">
                <h1 class="text-2xl font-bold">
                    <i class="fas fa-file-signature mr-2"></i>
                    Mi Plan de Práctica
                </h1>
                <p class="mt-2 opacity-90">
                    Estado actual de tu plan de práctica pre-profesional
                </p>
            </div>

            <?php if (!$plan): ?>
                <div class="p-6 text-center">
                    <i class="fas fa-file-plus text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-600 mb-2">No tienes un plan de práctica registrado</h3>
                    <p class="text-gray-500 mb-6">Debes registrar tu plan de práctica para continuar con el proceso</p>
                    <a href="registro_plan_practica.php" class="bg-green-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-green-700 transition">
                        <i class="fas fa-plus mr-2"></i>Registrar Plan de Práctica
                    </a>
                </div>
            <?php else: ?>
                <div class="p-6">
                    <!-- Estado del Plan -->
                    <div class="mb-6">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-gray-800">Estado del Plan</h2>
                            <span class="px-4 py-2 rounded-full text-sm font-medium 
                                <?php 
                                switch($plan['estado']) {
                                    case 'aprobado':
                                        echo 'bg-green-100 text-green-800';
                                        break;
                                    case 'rechazado':
                                        echo 'bg-red-100 text-red-800';
                                        break;
                                    case 'revision':
                                        echo 'bg-yellow-100 text-yellow-800';
                                        break;
                                    default:
                                        echo 'bg-gray-100 text-gray-800';
                                }
                                ?>">
                                <i class="fas fa-<?php echo $plan['estado'] == 'aprobado' ? 'check' : ($plan['estado'] == 'rechazado' ? 'times' : 'clock'); ?> mr-2"></i>
                                <?php echo ucfirst($plan['estado']); ?>
                            </span>
                        </div>
                        
                        <?php if ($plan['calificacion_docente']): ?>
                            <div class="mt-2">
                                <span class="text-sm text-gray-600">Calificación: </span>
                                <span class="px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                    <?php echo $plan['calificacion_docente']; ?>/20
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Información Personal -->
                    <div class="grid lg:grid-cols-2 gap-6 mb-6">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold mb-4 text-gray-800">
                                <i class="fas fa-user mr-2 text-blue-600"></i>
                                Información Personal
                            </h3>
                            <div class="space-y-2">
                                <p><strong>Nombres:</strong> <?php echo htmlspecialchars($plan['nombres']); ?></p>
                                <p><strong>Apellidos:</strong> <?php echo htmlspecialchars($plan['apellidos']); ?></p>
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
                                <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($plan['telefono_empresa']); ?></p>
                                <p><strong>Supervisor:</strong> <?php echo htmlspecialchars($plan['supervisor']); ?></p>
                                <p><strong>Cargo:</strong> <?php echo htmlspecialchars($plan['cargo_supervisor']); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Detalles de la Práctica -->
                    <div class="bg-gray-50 p-4 rounded-lg mb-6">
                        <h3 class="text-lg font-semibold mb-4 text-gray-800">
                            <i class="fas fa-calendar mr-2 text-purple-600"></i>
                            Detalles de la Práctica
                        </h3>
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <p><strong>Fecha Inicio:</strong> <?php echo date('d/m/Y', strtotime($plan['fecha_inicio'])); ?></p>
                                <p><strong>Fecha Fin:</strong> <?php echo date('d/m/Y', strtotime($plan['fecha_fin'])); ?></p>
                            </div>
                            <div>
                                <p><strong>Horario:</strong> <?php echo htmlspecialchars($plan['horario']); ?></p>
                                <p><strong>Total Horas:</strong> <?php echo htmlspecialchars($plan['total_horas']); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Actividades y Objetivos -->
                    <div class="space-y-6">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold mb-4 text-gray-800">
                                <i class="fas fa-tasks mr-2 text-orange-600"></i>
                                Actividades a Realizar
                            </h3>
                            <div class="bg-white p-3 rounded border">
                                <p class="text-sm text-gray-700 whitespace-pre-wrap"><?php echo htmlspecialchars($plan['actividades']); ?></p>
                            </div>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold mb-4 text-gray-800">
                                <i class="fas fa-bullseye mr-2 text-red-600"></i>
                                Objetivos de la Práctica
                            </h3>
                            <div class="bg-white p-3 rounded border">
                                <p class="text-sm text-gray-700 whitespace-pre-wrap"><?php echo htmlspecialchars($plan['objetivos']); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Comentarios del Docente -->
                    <?php if ($plan['comentarios_docente']): ?>
                        <div class="bg-blue-50 p-4 rounded-lg mt-6">
                            <h3 class="text-lg font-semibold mb-4 text-blue-800">
                                <i class="fas fa-comment mr-2"></i>
                                Comentarios del Docente
                            </h3>
                            <div class="bg-white p-3 rounded border">
                                <p class="text-sm text-blue-700 whitespace-pre-wrap"><?php echo htmlspecialchars($plan['comentarios_docente']); ?></p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Observaciones del Docente -->
                    <?php if ($plan['observaciones_docente']): ?>
                        <div class="bg-yellow-50 p-4 rounded-lg mt-6">
                            <h3 class="text-lg font-semibold mb-4 text-yellow-800">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                Observaciones del Docente
                            </h3>
                            <div class="bg-white p-3 rounded border">
                                <p class="text-sm text-yellow-700 whitespace-pre-wrap"><?php echo htmlspecialchars($plan['observaciones_docente']); ?></p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Acciones -->
                    <div class="mt-6 flex justify-between items-center">
                        <div class="text-sm text-gray-500">
                            Plan creado el: <?php echo date('d/m/Y H:i', strtotime($plan['fecha_creacion'])); ?>
                        </div>
                        <div class="space-x-3">
                            <?php if ($plan['estado'] == 'rechazado' || $plan['estado'] == 'revision'): ?>
                                <a href="registro_plan_practica.php?edit=<?php echo $plan['id']; ?>" 
                                   class="bg-yellow-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-yellow-700 transition">
                                    <i class="fas fa-edit mr-2"></i>Editar Plan
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($plan['estado'] == 'aprobado'): ?>
                                <a href="estudiante_reportes_semanales.php" 
                                   class="bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-blue-700 transition">
                                    <i class="fas fa-file-alt mr-2"></i>Ver Reportes
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

</body>
</html>