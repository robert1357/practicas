<?php
require_once '../config/session.php';
require_once '../models/User.php';
require_once '../models/PlanPractica.php';
require_once '../models/InformeFinal.php';
require_once '../models/AsignacionDocente.php';

// Verificar autenticación
if (!isAuthenticated()) {
    header("Location: login_estudiante.php");
    exit();
}

$user = getCurrentUser();
$planPractica = new PlanPractica();
$informe = new InformeFinal();
$asignacion = new AsignacionDocente();

// Obtener información del estudiante
$plan = $planPractica->getByEstudiante($user['id']);
$informeEstudiante = $informe->getByEstudiante($user['id']);
$docenteAsesor = $asignacion->getDocenteAsesor($user['id']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentos Firmados - SYSPRE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <i class="fas fa-file-signature text-2xl text-blue-600"></i>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">Documentos Firmados</h1>
                        <p class="text-sm text-gray-600">Estudiante: <?php echo htmlspecialchars($user['nombres'] . ' ' . $user['apellidos']); ?></p>
                    </div>
                </div>
                <a href="estudiante_dashboard.php" class="text-blue-600 hover:text-blue-800">
                    <i class="fas fa-arrow-left mr-2"></i>Volver al Dashboard
                </a>
            </div>
        </div>
    </header>

    <div class="max-w-6xl mx-auto px-4 py-8">
        <!-- Información del Estudiante -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Información del Estudiante</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Código</label>
                    <p class="text-sm text-gray-900"><?php echo htmlspecialchars($user['codigo']); ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <p class="text-sm text-gray-900"><?php echo htmlspecialchars($user['email']); ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Especialidad</label>
                    <p class="text-sm text-gray-900"><?php echo htmlspecialchars($user['especialidad']); ?></p>
                </div>
            </div>
        </div>

        <!-- Docente Asesor -->
        <?php if ($docenteAsesor): ?>
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-user-tie mr-2 text-green-600"></i>Docente Asesor
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nombre</label>
                    <p class="text-sm text-gray-900"><?php echo htmlspecialchars($docenteAsesor['nombres'] . ' ' . $docenteAsesor['apellidos']); ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <p class="text-sm text-gray-900"><?php echo htmlspecialchars($docenteAsesor['email']); ?></p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Plan de Práctica Firmado -->
        <?php if ($plan && $plan['estado'] == 'aprobado_final' && !empty($plan['archivo_plan_firmado'])): ?>
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-file-contract mr-2 text-blue-600"></i>Plan de Práctica Firmado
            </h2>
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-medium text-blue-900">Plan de Práctica Pre-Profesional</p>
                        <p class="text-sm text-blue-700">Estado: <?php echo ucfirst($plan['estado']); ?></p>
                        <p class="text-sm text-blue-700">Fecha de aprobación: <?php echo date('d/m/Y', strtotime($plan['fecha_revision'])); ?></p>
                    </div>
                    <div class="flex space-x-2">
                        <a href="../download_plan_firmado.php?id=<?php echo $plan['id']; ?>" 
                           target="_blank"
                           class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="fas fa-download mr-2"></i>Descargar PDF
                        </a>
                    </div>
                </div>
                <?php if (!empty($plan['observaciones_docente'])): ?>
                <div class="mt-4 p-3 bg-white rounded border">
                    <h4 class="font-medium text-gray-900 mb-2">Observaciones del Proceso:</h4>
                    <p class="text-sm text-gray-700"><?php echo nl2br(htmlspecialchars($plan['observaciones_docente'])); ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Informe Final Firmado -->
        <?php if ($informeEstudiante && $informeEstudiante['estado'] == 'aprobado_final' && !empty($informeEstudiante['documento_firmado'])): ?>
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-file-signature mr-2 text-green-600"></i>Informe Final Firmado
            </h2>
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-medium text-green-900"><?php echo htmlspecialchars($informeEstudiante['titulo']); ?></p>
                        <p class="text-sm text-green-700">Estado: <?php echo ucfirst($informeEstudiante['estado']); ?></p>
                        <p class="text-sm text-green-700">Fecha de aprobación: <?php echo date('d/m/Y', strtotime($informeEstudiante['fecha_revision'])); ?></p>
                    </div>
                    <div class="flex space-x-2">
                        <a href="../download_documento_firmado.php?id=<?php echo $informeEstudiante['id']; ?>" 
                           target="_blank"
                           class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                            <i class="fas fa-download mr-2"></i>Descargar PDF
                        </a>
                    </div>
                </div>
                <?php if (!empty($informeEstudiante['observaciones_docente']) || !empty($informeEstudiante['observaciones_coordinador'])): ?>
                <div class="mt-4 p-3 bg-white rounded border">
                    <h4 class="font-medium text-gray-900 mb-2">Observaciones del Proceso:</h4>
                    <?php if (!empty($informeEstudiante['observaciones_docente'])): ?>
                    <p class="text-sm text-gray-700 mb-2"><strong>Docente:</strong> <?php echo nl2br(htmlspecialchars($informeEstudiante['observaciones_docente'])); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($informeEstudiante['observaciones_coordinador'])): ?>
                    <p class="text-sm text-gray-700"><strong>Coordinador:</strong> <?php echo nl2br(htmlspecialchars($informeEstudiante['observaciones_coordinador'])); ?></p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Mensaje si no hay documentos -->
        <?php if ((!$plan || $plan['estado'] != 'aprobado_final' || empty($plan['archivo_plan_firmado'])) && 
                  (!$informeEstudiante || $informeEstudiante['estado'] != 'aprobado_final' || empty($informeEstudiante['documento_firmado']))): ?>
        <div class="bg-white rounded-lg shadow-sm p-6 text-center">
            <i class="fas fa-info-circle text-4xl text-gray-400 mb-4"></i>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No hay documentos firmados disponibles</h3>
            <p class="text-gray-600">Los documentos aparecerán aquí cuando sean aprobados y firmados por el coordinador.</p>
        </div>
        <?php endif; ?>

        <!-- Flujo del Proceso -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-route mr-2 text-purple-600"></i>Flujo del Proceso
            </h2>
            <div class="space-y-4">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-check text-green-600"></i>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900">1. Docente Asesor</p>
                        <p class="text-sm text-gray-600">Revisa, califica y aprueba los documentos</p>
                    </div>
                </div>
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-signature text-blue-600"></i>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900">2. Coordinador</p>
                        <p class="text-sm text-gray-600">Firma digitalmente y aprueba los documentos finales</p>
                    </div>
                </div>
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-download text-purple-600"></i>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900">3. Estudiante</p>
                        <p class="text-sm text-gray-600">Descarga los documentos firmados oficialmente</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>