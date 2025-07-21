<?php
require_once '../config/session.php';
require_once '../config/database.php';

// Verificar autenticación
if (!isAuthenticated() || $_SESSION['user_type'] !== 'estudiante') {
    header('Location: login_estudiante.php');
    exit();
}

$database = new Database();
$pdo = $database->connect();

// Obtener datos del usuario
$query = "SELECT * FROM usuarios WHERE id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Obtener informe final
$query = "
    SELECT i.*
    FROM informes_finales i
    WHERE i.estudiante_id = ?
    ORDER BY i.fecha_creacion DESC
    LIMIT 1
";
$stmt = $pdo->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$informe = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$informe) {
    header('Location: estudiante_dashboard.php');
    exit();
}

// Obtener información del docente y coordinador desde asignaciones
$docenteInfo = null;
$coordinadorInfo = null;

// Buscar docente asignado
$query = "SELECT u.* FROM usuarios u 
          JOIN asignaciones_docente ad ON u.id = ad.docente_id 
          WHERE ad.estudiante_id = ? AND u.tipo = 'docente'";
$stmt = $pdo->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$docenteInfo = $stmt->fetch(PDO::FETCH_ASSOC);

// Buscar coordinador (puede ser el que aprobó el plan)
$query = "SELECT u.* FROM usuarios u 
          WHERE u.tipo = 'coordinador' AND u.especialidad = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$user['especialidad']]);
$coordinadorInfo = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informe Final - SYSPRE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --azul-principal: #4a6fdc;
            --verde-aprobado: #10b981;
            --rojo-rechazado: #ef4444;
            --amarillo-pendiente: #f59e0b;
            --gris-texto: #6b7280;
            --gris-claro: #f9fafb;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navegación -->
    <nav class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <i class="fas fa-file-alt text-2xl text-blue-600 mr-3"></i>
                    <h1 class="text-xl font-bold text-gray-900">Informe Final</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-500">
                        <?php echo htmlspecialchars($user['nombres'] . ' ' . $user['apellidos']); ?>
                    </span>
                    <a href="estudiante_dashboard.php" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition">
                        <i class="fas fa-arrow-left mr-2"></i>Volver al Dashboard
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Estado del Informe -->
        <div class="mb-8">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-2xl font-bold text-gray-900">Estado del Informe Final</h2>
                    <div class="flex items-center">
                        <?php if ($informe['estado'] == 'aprobado_final'): ?>
                            <span class="bg-green-100 text-green-800 px-4 py-2 rounded-full font-medium flex items-center">
                                <i class="fas fa-check-circle mr-2"></i>Aprobado Final
                            </span>
                        <?php elseif ($informe['estado'] == 'aprobado'): ?>
                            <span class="bg-yellow-100 text-yellow-800 px-4 py-2 rounded-full font-medium flex items-center">
                                <i class="fas fa-clock mr-2"></i>Aprobado por Docente
                            </span>
                        <?php elseif ($informe['estado'] == 'rechazado'): ?>
                            <span class="bg-red-100 text-red-800 px-4 py-2 rounded-full font-medium flex items-center">
                                <i class="fas fa-times-circle mr-2"></i>Rechazado
                            </span>
                        <?php else: ?>
                            <span class="bg-blue-100 text-blue-800 px-4 py-2 rounded-full font-medium flex items-center">
                                <i class="fas fa-hourglass-half mr-2"></i>En Revisión
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="font-semibold text-gray-900 mb-2">Información General</h3>
                        <div class="space-y-2 text-sm">
                            <div><strong>Fecha de Envío:</strong> <?php echo date('d/m/Y H:i', strtotime($informe['fecha_creacion'])); ?></div>
                            <div><strong>Título:</strong> <?php echo htmlspecialchars($informe['titulo']); ?></div>
                            <div><strong>Especialidad:</strong> <?php echo htmlspecialchars($user['especialidad']); ?></div>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="font-semibold text-gray-900 mb-2">Fechas Importantes</h3>
                        <div class="space-y-2 text-sm">
                            <div><strong>Fecha de Creación:</strong> <?php echo date('d/m/Y', strtotime($informe['fecha_creacion'])); ?></div>
                            <?php if ($informe['fecha_revision']): ?>
                                <div><strong>Última Revisión:</strong> <?php echo date('d/m/Y', strtotime($informe['fecha_revision'])); ?></div>
                            <?php endif; ?>
                            <?php if ($informe['estado'] == 'aprobado_final'): ?>
                                <div><strong>Aprobación Final:</strong> <?php echo date('d/m/Y', strtotime($informe['fecha_revision'])); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="font-semibold text-gray-900 mb-2">Archivos</h3>
                        <div class="space-y-2">
                            <?php if ($informe['archivo_informe']): ?>
                                <a href="../download_informe_file.php?id=<?php echo $informe['id']; ?>" 
                                   class="flex items-center text-blue-600 hover:text-blue-800 text-sm">
                                    <i class="fas fa-file-pdf mr-2"></i>Informe Original
                                </a>
                            <?php endif; ?>
                            <?php if ($informe['documento_firmado']): ?>
                                <a href="descargar_pdf.php" 
                                   class="flex items-center text-green-600 hover:text-green-800 text-sm font-medium">
                                    <i class="fas fa-certificate mr-2"></i>Documento Firmado
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Evaluación del Docente -->
        <?php if ($docenteInfo): ?>
        <div class="mb-8">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-chalkboard-teacher mr-3 text-blue-600"></i>
                    Evaluación del Docente
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="font-semibold text-gray-900 mb-3">Información del Docente</h3>
                        <div class="space-y-2 text-sm">
                            <div><strong>Nombre:</strong> <?php echo htmlspecialchars($docenteInfo['nombres'] . ' ' . $docenteInfo['apellidos']); ?></div>
                            <div><strong>Email:</strong> <?php echo htmlspecialchars($docenteInfo['email']); ?></div>
                            <?php if ($informe['fecha_revision']): ?>
                                <div><strong>Fecha de Revisión:</strong> <?php echo date('d/m/Y H:i', strtotime($informe['fecha_revision'])); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div>
                        <h3 class="font-semibold text-gray-900 mb-3">Calificación</h3>
                        <?php if ($informe['calificacion_docente']): ?>
                            <div class="bg-blue-50 rounded-lg p-4">
                                <div class="text-2xl font-bold text-blue-600 mb-2">
                                    <?php echo htmlspecialchars($informe['calificacion_docente']); ?>
                                </div>
                                <div class="text-sm text-gray-600">Calificación del Docente</div>
                            </div>
                        <?php else: ?>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="text-gray-500">Pendiente de calificación</div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if ($informe['observaciones_docente']): ?>
                <div class="mt-4">
                    <h3 class="font-semibold text-gray-900 mb-2">Observaciones del Docente</h3>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-sm text-gray-700"><?php echo nl2br(htmlspecialchars($informe['observaciones_docente'])); ?></p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Evaluación del Coordinador -->
        <?php if ($coordinadorInfo): ?>
        <div class="mb-8">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-user-tie mr-3 text-green-600"></i>
                    Evaluación del Coordinador
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="font-semibold text-gray-900 mb-3">Información del Coordinador</h3>
                        <div class="space-y-2 text-sm">
                            <div><strong>Nombre:</strong> <?php echo htmlspecialchars($coordinadorInfo['nombres'] . ' ' . $coordinadorInfo['apellidos']); ?></div>
                            <div><strong>Email:</strong> <?php echo htmlspecialchars($coordinadorInfo['email']); ?></div>
                            <?php if ($informe['fecha_revision']): ?>
                                <div><strong>Fecha de Revisión:</strong> <?php echo date('d/m/Y H:i', strtotime($informe['fecha_revision'])); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div>
                        <h3 class="font-semibold text-gray-900 mb-3">Calificación Final</h3>
                        <?php if ($informe['calificacion_coordinador']): ?>
                            <div class="bg-green-50 rounded-lg p-4">
                                <div class="text-2xl font-bold text-green-600 mb-2">
                                    <?php echo htmlspecialchars($informe['calificacion_coordinador']); ?>
                                </div>
                                <div class="text-sm text-gray-600">Calificación del Coordinador</div>
                            </div>
                        <?php else: ?>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="text-gray-500">Pendiente de calificación</div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if ($informe['observaciones_coordinador']): ?>
                <div class="mt-4">
                    <h3 class="font-semibold text-gray-900 mb-2">Observaciones del Coordinador</h3>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-sm text-gray-700"><?php echo nl2br(htmlspecialchars($informe['observaciones_coordinador'])); ?></p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Documento Firmado -->
        <?php if ($informe['documento_firmado'] && $informe['estado'] == 'aprobado_final'): ?>
        <div class="mb-8">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-certificate mr-3 text-green-600"></i>
                    Documento Firmado
                </h2>
                
                <div class="bg-green-50 rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-green-800 mb-2">Documento Oficial Disponible</h3>
                            <p class="text-green-700 text-sm mb-4">
                                Tu informe final ha sido aprobado y firmado digitalmente por el coordinador. 
                                Este documento tiene validez oficial para tu expediente académico.
                            </p>
                            <div class="text-sm text-green-600">
                                <i class="fas fa-check-circle mr-2"></i>Documento verificado y firmado
                            </div>
                        </div>
                        <div class="ml-6">
                            <a href="descargar_pdf.php" 
                               class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition flex items-center">
                                <i class="fas fa-download mr-2"></i>Descargar PDF Firmado
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Resumen del Proceso -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-clipboard-check mr-3 text-blue-600"></i>
                Resumen del Proceso
            </h2>
            
            <div class="space-y-4">
                <div class="flex items-center">
                    <div class="bg-blue-500 text-white rounded-full w-8 h-8 flex items-center justify-center text-sm font-bold mr-4">1</div>
                    <div class="flex-1">
                        <h3 class="font-semibold text-gray-900">Envío del Informe</h3>
                        <p class="text-sm text-gray-600">Enviado el <?php echo date('d/m/Y H:i', strtotime($informe['fecha_creacion'])); ?></p>
                    </div>
                    <i class="fas fa-check text-green-500"></i>
                </div>
                
                <div class="flex items-center">
                    <div class="<?php echo $informe['calificacion_docente'] ? 'bg-blue-500' : 'bg-gray-400'; ?> text-white rounded-full w-8 h-8 flex items-center justify-center text-sm font-bold mr-4">2</div>
                    <div class="flex-1">
                        <h3 class="font-semibold text-gray-900">Revisión del Docente</h3>
                        <p class="text-sm text-gray-600">
                            <?php if ($informe['calificacion_docente']): ?>
                                Calificado con <?php echo $informe['calificacion_docente']; ?>
                            <?php else: ?>
                                Pendiente de revisión
                            <?php endif; ?>
                        </p>
                    </div>
                    <?php if ($informe['calificacion_docente']): ?>
                        <i class="fas fa-check text-green-500"></i>
                    <?php else: ?>
                        <i class="fas fa-clock text-yellow-500"></i>
                    <?php endif; ?>
                </div>
                
                <div class="flex items-center">
                    <div class="<?php echo $informe['calificacion_coordinador'] ? 'bg-blue-500' : 'bg-gray-400'; ?> text-white rounded-full w-8 h-8 flex items-center justify-center text-sm font-bold mr-4">3</div>
                    <div class="flex-1">
                        <h3 class="font-semibold text-gray-900">Revisión del Coordinador</h3>
                        <p class="text-sm text-gray-600">
                            <?php if ($informe['calificacion_coordinador']): ?>
                                Calificado con <?php echo $informe['calificacion_coordinador']; ?>
                            <?php else: ?>
                                Pendiente de revisión
                            <?php endif; ?>
                        </p>
                    </div>
                    <?php if ($informe['calificacion_coordinador']): ?>
                        <i class="fas fa-check text-green-500"></i>
                    <?php else: ?>
                        <i class="fas fa-clock text-yellow-500"></i>
                    <?php endif; ?>
                </div>
                
                <div class="flex items-center">
                    <div class="<?php echo $informe['estado'] == 'aprobado_final' ? 'bg-green-500' : 'bg-gray-400'; ?> text-white rounded-full w-8 h-8 flex items-center justify-center text-sm font-bold mr-4">4</div>
                    <div class="flex-1">
                        <h3 class="font-semibold text-gray-900">Documento Firmado</h3>
                        <p class="text-sm text-gray-600">
                            <?php if ($informe['estado'] == 'aprobado_final'): ?>
                                Documento firmado y disponible para descarga
                            <?php else: ?>
                                Pendiente de aprobación final
                            <?php endif; ?>
                        </p>
                    </div>
                    <?php if ($informe['estado'] == 'aprobado_final'): ?>
                        <i class="fas fa-check text-green-500"></i>
                    <?php else: ?>
                        <i class="fas fa-clock text-yellow-500"></i>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>