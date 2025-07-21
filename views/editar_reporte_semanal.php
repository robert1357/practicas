<?php
require_once '../config/session.php';
require_once '../models/ReporteSemanal.php';

// Verificar que el usuario esté autenticado y sea estudiante
if (!isAuthenticated() || $_SESSION['user_type'] !== 'estudiante') {
    header('Location: login_estudiante.php');
    exit();
}

$reporte_id = $_GET['id'] ?? null;
if (!$reporte_id) {
    header('Location: estudiante_reportes_semanales.php');
    exit();
}

$reporteModel = new ReporteSemanal();
$reporte = $reporteModel->findById($reporte_id);

if (!$reporte || $reporte['estudiante_id'] != $_SESSION['user_id']) {
    header('Location: estudiante_reportes_semanales.php');
    exit();
}

// Solo permitir edición si está pendiente
if ($reporte['estado'] !== 'pendiente') {
    header('Location: ver_reporte_estudiante.php?id=' . $reporte_id);
    exit();
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    
    // Validar campos requeridos
    if (empty($_POST['fecha_inicio'])) $errors[] = 'La fecha de inicio es requerida';
    if (empty($_POST['fecha_fin'])) $errors[] = 'La fecha de fin es requerida';
    if (empty($_POST['total_horas'])) $errors[] = 'El total de horas es requerido';
    if (empty($_POST['asesor_empresarial'])) $errors[] = 'El asesor empresarial es requerido';
    if (empty($_POST['area_trabajo'])) $errors[] = 'El área de trabajo es requerida';
    if (empty($_POST['actividades'])) $errors[] = 'Las actividades son requeridas';
    if (empty($_POST['aprendizajes'])) $errors[] = 'Los aprendizajes son requeridos';
    
    if (empty($errors)) {
        $data = [
            'fecha_inicio' => $_POST['fecha_inicio'],
            'fecha_fin' => $_POST['fecha_fin'],
            'total_horas' => (int)$_POST['total_horas'],
            'asesor_empresarial' => $_POST['asesor_empresarial'],
            'area_trabajo' => $_POST['area_trabajo'],
            'actividades' => $_POST['actividades'],
            'aprendizajes' => $_POST['aprendizajes'],
            'dificultades' => $_POST['dificultades'] ?? ''
        ];
        
        // Actualizar reporte
        $query = "UPDATE reportes_semanales SET 
                  fecha_inicio = :fecha_inicio,
                  fecha_fin = :fecha_fin,
                  total_horas = :total_horas,
                  asesor_empresarial = :asesor_empresarial,
                  area_trabajo = :area_trabajo,
                  actividades = :actividades,
                  aprendizajes = :aprendizajes,
                  dificultades = :dificultades
                  WHERE id = :id AND estudiante_id = :estudiante_id";
        
        $database = new Database();
        $conn = $database->connect();
        $stmt = $conn->prepare($query);
        
        $stmt->bindParam(':fecha_inicio', $data['fecha_inicio']);
        $stmt->bindParam(':fecha_fin', $data['fecha_fin']);
        $stmt->bindParam(':total_horas', $data['total_horas']);
        $stmt->bindParam(':asesor_empresarial', $data['asesor_empresarial']);
        $stmt->bindParam(':area_trabajo', $data['area_trabajo']);
        $stmt->bindParam(':actividades', $data['actividades']);
        $stmt->bindParam(':aprendizajes', $data['aprendizajes']);
        $stmt->bindParam(':dificultades', $data['dificultades']);
        $stmt->bindParam(':id', $reporte_id);
        $stmt->bindParam(':estudiante_id', $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            $success = 'Reporte actualizado exitosamente';
            // Actualizar datos del reporte para mostrar en el formulario
            $reporte = $reporteModel->findById($reporte_id);
        } else {
            $errors[] = 'Error al actualizar el reporte';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Reporte Semanal - SYSPRE 2025</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen">

    <!-- Header -->
    <header class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
            <a href="ver_reporte_estudiante.php?id=<?php echo $reporte_id; ?>" class="text-xl font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-arrow-left"></i>
                <i class="fas fa-graduation-cap"></i>
                SYSPRE 2025
            </a>
            <div class="text-sm text-gray-600">
                Editar Reporte Semanal
            </div>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-lg">
            
            <!-- Header -->
            <div class="bg-yellow-600 text-white p-6 rounded-t-lg">
                <h1 class="text-2xl font-bold">
                    <i class="fas fa-edit mr-2"></i>
                    Editar Reporte Semanal
                </h1>
                <p class="mt-2 opacity-90">
                    Modifica los datos de tu reporte semanal
                </p>
            </div>

            <!-- Mensajes -->
            <?php if (isset($success)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 m-6 rounded">
                    <i class="fas fa-check-circle mr-2"></i>
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 m-6 rounded">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <ul class="list-disc list-inside">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Formulario -->
            <div class="p-6">
                <form method="POST" class="space-y-6">
                    
                    <!-- Fechas y Horas -->
                    <div class="grid md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Fecha de Inicio *
                            </label>
                            <input type="date" name="fecha_inicio" required 
                                   value="<?php echo htmlspecialchars($reporte['fecha_inicio']); ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Fecha de Fin *
                            </label>
                            <input type="date" name="fecha_fin" required 
                                   value="<?php echo htmlspecialchars($reporte['fecha_fin']); ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Total de Horas *
                            </label>
                            <input type="number" name="total_horas" min="1" max="168" required 
                                   value="<?php echo htmlspecialchars($reporte['total_horas']); ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <!-- Información de la Empresa -->
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Asesor Empresarial *
                            </label>
                            <input type="text" name="asesor_empresarial" required 
                                   value="<?php echo htmlspecialchars($reporte['asesor_empresarial']); ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Área de Trabajo *
                            </label>
                            <input type="text" name="area_trabajo" required 
                                   value="<?php echo htmlspecialchars($reporte['area_trabajo']); ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <!-- Actividades -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Actividades Realizadas *
                        </label>
                        <textarea name="actividades" rows="6" required 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                  placeholder="Describe detalladamente las actividades que realizaste durante esta semana..."><?php echo htmlspecialchars($reporte['actividades']); ?></textarea>
                    </div>

                    <!-- Aprendizajes -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Aprendizajes y Logros *
                        </label>
                        <textarea name="aprendizajes" rows="4" required 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                  placeholder="¿Qué aprendiste esta semana? ¿Qué logros obtuviste?"><?php echo htmlspecialchars($reporte['aprendizajes']); ?></textarea>
                    </div>

                    <!-- Dificultades -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Dificultades Encontradas
                        </label>
                        <textarea name="dificultades" rows="3" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                  placeholder="¿Encontraste alguna dificultad? ¿Cómo la resolviste?"><?php echo htmlspecialchars($reporte['dificultades']); ?></textarea>
                    </div>

                    <!-- Archivos adjuntos actuales -->
                    <?php if (!empty($reporte['archivos_adjuntos'])): ?>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Archivos Adjuntos Actuales
                        </label>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <?php
                            $archivos = json_decode($reporte['archivos_adjuntos'], true);
                            if (is_array($archivos) && !empty($archivos)):
                                foreach ($archivos as $archivo):
                                    if (!empty($archivo)):
                                        $file_name = basename($archivo);
                                        $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                                        $icon_class = $file_extension === 'pdf' ? 'fas fa-file-pdf text-red-600' : 'fas fa-file text-gray-600';
                            ?>
                            <div class="flex items-center space-x-2 mb-2">
                                <i class="<?php echo $icon_class; ?>"></i>
                                <span class="text-sm"><?php echo htmlspecialchars($file_name); ?></span>
                                <a href="../download_report_file.php?reporte_id=<?php echo $reporte['id']; ?>&file_name=<?php echo urlencode($file_name); ?>" 
                                   class="text-blue-600 hover:text-blue-800 text-sm"
                                   target="_blank">
                                    <i class="fas fa-download"></i>
                                </a>
                            </div>
                            <?php
                                    endif;
                                endforeach;
                            endif;
                            ?>
                            <p class="text-sm text-gray-600 mt-2">
                                <i class="fas fa-info-circle mr-1"></i>
                                Para cambiar los archivos adjuntos, contacta a tu docente.
                            </p>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Botones -->
                    <div class="flex justify-between pt-6">
                        <a href="ver_reporte_estudiante.php?id=<?php echo $reporte_id; ?>" 
                           class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-md transition">
                            <i class="fas fa-times mr-2"></i>Cancelar
                        </a>
                        <button type="submit" 
                                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md transition">
                            <i class="fas fa-save mr-2"></i>Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        // Validar fechas
        document.addEventListener('DOMContentLoaded', function() {
            const fechaInicio = document.querySelector('input[name="fecha_inicio"]');
            const fechaFin = document.querySelector('input[name="fecha_fin"]');
            
            function validarFechas() {
                if (fechaInicio.value && fechaFin.value) {
                    if (new Date(fechaFin.value) < new Date(fechaInicio.value)) {
                        fechaFin.setCustomValidity('La fecha de fin debe ser posterior a la fecha de inicio');
                    } else {
                        fechaFin.setCustomValidity('');
                    }
                }
            }
            
            fechaInicio.addEventListener('change', validarFechas);
            fechaFin.addEventListener('change', validarFechas);
        });
    </script>

</body>
</html>