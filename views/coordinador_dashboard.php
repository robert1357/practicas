<?php
require_once '../config/session.php';
require_once '../models/User.php';
require_once '../models/PlanPractica.php';
require_once '../models/AsignacionDocente.php';
require_once '../models/DocumentoReglamento.php';
require_once '../models/InformeFinal.php';
require_once '../config/upload_config.php';

// Verificar autenticación
if (!isAuthenticated() || !hasRole('coordinador')) {
    header("Location: login_coordinador.php");
    exit();
}

$user = getCurrentUser();
$userModel = new User();
$planPractica = new PlanPractica();
$asignacion = new AsignacionDocente();
$documentoModel = new DocumentoReglamento();
$informeModel = new InformeFinal();

// Obtener estudiantes y docentes de la especialidad
$estudiantes = $asignacion->getEstudiantesConAsesor($user['especialidad']);
$docentes = $userModel->getByType('docente', $user['especialidad']);
$planes = $planPractica->getByEspecialidad($user['especialidad']);
$planesParaCoordinador = $planPractica->getPendientesCoordinador($user['especialidad']); // Solo aprobados por docente
$informesPendientes = $informeModel->getPendientesCoordinador($user['especialidad']); // Informes aprobados por docente
$documentos = $documentoModel->getByEspecialidad($user['especialidad']);

// Procesar formularios
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'asignar_docente') {
        $estudiante_id = $_POST['estudiante_id'];
        $docente_id = $_POST['docente_id'];
        $tipo = $_POST['tipo'] ?? 'asesor';
        
        if ($asignacion->asignar($estudiante_id, $docente_id, $tipo)) {
            $success = 'Docente asignado exitosamente';
        } else {
            $error = 'Error al asignar docente';
        }
        
        // Recargar datos
        $estudiantes = $asignacion->getEstudiantesConAsesor($user['especialidad']);
    }
    
    // Procesar subida de documento
    if (isset($_POST['action']) && $_POST['action'] == 'subir_documento') {
        // Validar archivo
        $archivo = $_FILES['archivo'];
        $uploadSuccess = false;
        $fileName = '';
        
        if ($archivo['error'] === UPLOAD_ERR_OK) {
            // Validar tipo de archivo
            $allowedTypes = ['application/pdf'];
            $fileType = $archivo['type'];
            $fileExt = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
            
            if ($fileExt === 'pdf' && in_array($fileType, $allowedTypes)) {
                // Validar tamaño (10MB máximo)
                if ($archivo['size'] <= 10 * 1024 * 1024) {
                    // Crear directorio si no existe
                    $uploadDir = '../uploads/documentos/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    
                    // Generar nombre único
                    $fileName = time() . '_' . uniqid() . '.pdf';
                    $uploadPath = $uploadDir . $fileName;
                    
                    if (move_uploaded_file($archivo['tmp_name'], $uploadPath)) {
                        $uploadSuccess = true;
                    } else {
                        $error = 'Error al mover el archivo al directorio de destino';
                    }
                } else {
                    $error = 'El archivo es demasiado grande. Máximo permitido: 10MB';
                }
            } else {
                $error = 'Solo se permiten archivos PDF';
            }
        } else {
            $error = 'Error en la carga del archivo';
        }
        
        if ($uploadSuccess) {
            $documentoData = [
                'titulo' => $_POST['titulo'],
                'descripcion' => $_POST['descripcion'],
                'tipo_documento' => $_POST['tipo_documento'],
                'archivo_url' => $fileName,
                'especialidad' => $user['especialidad'],
                'creado_por' => $user['id']
            ];
            
            if ($documentoModel->create($documentoData)) {
                $success = 'Documento subido exitosamente';
                $documentos = $documentoModel->getByEspecialidad($user['especialidad']);
            } else {
                $error = 'Error al guardar el documento en la base de datos';
            }
        }
    }
    
    // Procesar eliminación de documento
    if (isset($_POST['action']) && $_POST['action'] == 'eliminar_documento') {
        $documento_id = $_POST['documento_id'];
        
        if ($documentoModel->delete($documento_id)) {
            $success = 'Documento eliminado exitosamente';
            $documentos = $documentoModel->getByEspecialidad($user['especialidad']);
        } else {
            $error = 'Error al eliminar el documento';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard Coordinador - SYSPRE 2025</title>

  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
  
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            principal: '#d6a62c',
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
      <a href="../index.php" class="text-xl font-bold text-texto flex items-center gap-2">
        <i class="fas fa-user-tie"></i> SYSPRE 2025 - Coordinador
      </a>
      <div class="space-x-6 hidden md:flex">
        <a href="#" class="text-principal font-semibold"><i class="fas fa-tachometer-alt mr-1"></i>Dashboard</a>
        <a href="perfil_coordinador.php" class="text-gris hover:text-texto transition"><i class="fas fa-user mr-1"></i>Mi Perfil</a>
        <a href="../logout.php" class="text-gris hover:text-texto transition"><i class="fas fa-sign-out-alt mr-1"></i>Cerrar Sesión</a>
      </div>
    </div>
  </nav>

  <main class="pt-24 max-w-7xl mx-auto px-4">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-texto flex items-center">
            <i class="fas fa-user-tie mr-3 text-principal"></i>
            Dashboard Coordinador
          </h1>
          <p class="text-gris mt-2"><?php echo htmlspecialchars($user['nombres'] . ' ' . $user['apellidos']); ?></p>
          <p class="text-sm text-gris"><?php echo htmlspecialchars($user['especialidad']); ?> - Período: 2025-I</p>
        </div>
        <div class="text-right">
          <div class="bg-principal bg-opacity-10 px-4 py-2 rounded-lg">
            <p class="text-principal text-sm font-medium">Estudiantes</p>
            <p class="text-lg font-bold text-principal"><?php echo count($estudiantes); ?></p>
          </div>
        </div>
      </div>
    </div>

    <?php if (isset($success)): ?>
      <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6" role="alert">
        <span class="block sm:inline"><?php echo htmlspecialchars($success); ?></span>
      </div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
      <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6" role="alert">
        <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
      </div>
    <?php endif; ?>

    <!-- Tareas Pendientes -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
      <h2 class="text-2xl font-bold text-texto mb-4 flex items-center">
        <i class="fas fa-tasks mr-3 text-principal"></i>
        Tareas Pendientes
      </h2>
      <p class="text-gris mb-4">Revisiones pendientes que requieren tu atención</p>
      
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Planes Pendientes -->
        <div class="bg-yellow-50 rounded-lg p-4">
          <h3 class="font-semibold text-yellow-800 mb-3 flex items-center">
            <i class="fas fa-file-alt mr-2"></i>
            Planes de Práctica para Revisar
          </h3>
          <?php if (empty($planesParaCoordinador)): ?>
            <p class="text-yellow-600 text-sm">No hay planes pendientes de revisión</p>
          <?php else: ?>
            <div class="space-y-2 max-h-48 overflow-y-auto">
              <?php foreach ($planesParaCoordinador as $plan): ?>
                <div class="bg-white rounded p-3 border border-yellow-200">
                  <div class="flex items-center justify-between">
                    <div>
                      <p class="font-medium text-sm"><?php echo htmlspecialchars($plan['nombres'] . ' ' . $plan['apellidos']); ?></p>
                      <p class="text-xs text-gray-600"><?php echo htmlspecialchars($plan['codigo']); ?></p>
                    </div>
                    <a href="aprobar_plan.php?id=<?php echo $plan['id']; ?>" 
                       class="bg-yellow-500 text-white px-3 py-1 rounded text-xs hover:bg-yellow-600 transition">
                      Revisar
                    </a>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>

        <!-- Informes Pendientes -->
        <div class="bg-blue-50 rounded-lg p-4">
          <h3 class="font-semibold text-blue-800 mb-3 flex items-center">
            <i class="fas fa-file-check mr-2"></i>
            Informes Finales para Revisar
          </h3>
          <?php if (empty($informesPendientes)): ?>
            <p class="text-blue-600 text-sm">No hay informes pendientes de revisión</p>
          <?php else: ?>
            <div class="space-y-2 max-h-48 overflow-y-auto">
              <?php foreach ($informesPendientes as $informe): ?>
                <div class="bg-white rounded p-3 border border-blue-200">
                  <div class="flex items-center justify-between">
                    <div>
                      <p class="font-medium text-sm"><?php echo htmlspecialchars($informe['nombres'] . ' ' . $informe['apellidos']); ?></p>
                      <p class="text-xs text-gray-600">Calificación: <?php echo $informe['calificacion_docente']; ?>/20</p>
                    </div>
                    <a href="coordinador_informe.php?estudiante=<?php echo $informe['estudiante_id']; ?>" 
                       class="bg-blue-500 text-white px-3 py-1 rounded text-xs hover:bg-blue-600 transition">
                      Revisar
                    </a>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Subir Documentos -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
      <h2 class="text-2xl font-bold text-texto mb-4 flex items-center">
        <i class="fas fa-upload mr-3 text-principal"></i>
        Documentos Reglamentarios
      </h2>
      <p class="text-gris mb-4">Sube reglamentos y documentos importantes para los estudiantes de <?php echo htmlspecialchars($user['especialidad']); ?></p>
      
      <!-- Formulario para subir documento -->
      <form method="POST" enctype="multipart/form-data" class="space-y-4">
        <input type="hidden" name="action" value="subir_documento">
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gris mb-2">Título del Documento</label>
            <input type="text" name="titulo" required 
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-principal focus:border-transparent"
                   placeholder="Ej: Reglamento de Prácticas 2025">
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gris mb-2">Tipo de Documento</label>
            <select name="tipo_documento" required 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-principal focus:border-transparent">
              <option value="">Seleccionar tipo</option>
              <option value="reglamento">Reglamento</option>
              <option value="manual">Manual</option>
              <option value="guia">Guía</option>
              <option value="formato">Formato</option>
              <option value="otros">Otros</option>
            </select>
          </div>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gris mb-2">Descripción</label>
          <textarea name="descripcion" rows="3" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-principal focus:border-transparent"
                    placeholder="Descripción del documento..."></textarea>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gris mb-2">Archivo PDF</label>
          <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 sm:p-6">
            <div class="flex flex-col sm:flex-row items-start sm:items-center space-y-3 sm:space-y-0 sm:space-x-4">
              <i class="fas fa-file-pdf text-3xl text-gray-400 mx-auto sm:mx-0"></i>
              <div class="flex-1 w-full">
                <input type="file" name="archivo" accept=".pdf" required 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-principal focus:border-transparent">
                <p class="text-sm text-gris mt-2 text-center sm:text-left">Solo archivos PDF - Tamaño máximo: 10MB</p>
              </div>
            </div>
          </div>
        </div>
        
        <div class="flex justify-end">
          <button type="submit" class="bg-principal hover:bg-yellow-600 text-white px-6 py-2 rounded-lg transition">
            <i class="fas fa-plus mr-2"></i>Subir Documento
          </button>
        </div>
      </form>
      
      <!-- Lista de documentos existentes -->
      <div class="mt-8">
        <h3 class="text-lg font-semibold mb-4">Documentos Existentes</h3>
        <div class="space-y-2">
          <?php if (empty($documentos)): ?>
            <div class="text-center py-8 text-gris">
              <i class="fas fa-folder-open text-4xl mb-4"></i>
              <p>No hay documentos subidos aún</p>
            </div>
          <?php else: ?>
            <?php foreach ($documentos as $doc): ?>
              <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between bg-gray-50 rounded-lg p-3 hover:bg-gray-100 transition space-y-3 sm:space-y-0">
                <div class="flex items-start sm:items-center">
                  <i class="fas fa-file-pdf text-red-500 mr-3 mt-1 sm:mt-0"></i>
                  <div>
                    <p class="font-medium"><?php echo htmlspecialchars($doc['titulo']); ?></p>
                    <p class="text-sm text-gris">
                      <?php echo htmlspecialchars($doc['tipo_documento']); ?> • 
                      Subido el <?php echo date('d/m/Y', strtotime($doc['fecha_creacion'])); ?>
                    </p>
                    <?php if ($doc['descripcion']): ?>
                      <p class="text-sm text-gris mt-1"><?php echo htmlspecialchars($doc['descripcion']); ?></p>
                    <?php endif; ?>
                  </div>
                </div>
                <div class="flex space-x-2 justify-end sm:justify-start">
                  <a href="../download_file.php?type=documentos&file=<?php echo urlencode($doc['archivo_url']); ?>" 
                     class="text-blue-500 hover:text-blue-700 px-2 py-1 rounded transition" 
                     title="Descargar">
                    <i class="fas fa-download"></i>
                  </a>
                  <form method="POST" class="inline" onsubmit="return confirm('¿Está seguro de eliminar este documento?');">
                    <input type="hidden" name="action" value="eliminar_documento">
                    <input type="hidden" name="documento_id" value="<?php echo $doc['id']; ?>">
                    <button type="submit" class="text-red-500 hover:text-red-700 px-2 py-1 rounded transition" title="Eliminar">
                      <i class="fas fa-trash"></i>
                    </button>
                  </form>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Gestión de Estudiantes -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
      <h2 class="text-2xl font-bold text-texto mb-4 flex items-center">
        <i class="fas fa-users mr-3 text-principal"></i>
        Gestión de Estudiantes
      </h2>
      
      <!-- Filtros -->
      <div class="flex flex-wrap gap-4 mb-6">
        <div class="flex-1 min-w-64">
          <input 
            type="text" 
            placeholder="Buscar por nombre, apellido o código..." 
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-principal focus:border-transparent"
            id="searchInput"
          >
        </div>
        <select class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-principal focus:border-transparent">
          <option value="">Todos los estados</option>
          <option value="sin_plan">Sin plan</option>
          <option value="plan_pendiente">Plan pendiente</option>
          <option value="plan_aprobado">Plan aprobado</option>
          <option value="sin_asesor">Sin asesor</option>
        </select>
      </div>

      <!-- Tabla de estudiantes -->
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="bg-gray-50">
              <th class="text-left p-3 font-semibold">Estudiante</th>
              <th class="text-left p-3 font-semibold">Código</th>
              <th class="text-left p-3 font-semibold">Docente Asesor</th>
              <th class="text-left p-3 font-semibold">Estado Plan</th>
              <th class="text-left p-3 font-semibold">Acciones</th>
            </tr>
          </thead>
          <tbody id="studentTableBody">
            <?php foreach ($estudiantes as $estudiante): ?>
            <tr class="border-b hover:bg-gray-50">
              <td class="p-3">
                <div>
                  <p class="font-medium"><?php echo htmlspecialchars($estudiante['nombres'] . ' ' . $estudiante['apellidos']); ?></p>
                  <p class="text-sm text-gris"><?php echo htmlspecialchars($estudiante['email']); ?></p>
                </div>
              </td>
              <td class="p-3">
                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-sm">
                  <?php echo htmlspecialchars($estudiante['codigo']); ?>
                </span>
              </td>
              <td class="p-3">
                <?php if ($estudiante['docente_nombres']): ?>
                  <div class="flex items-center">
                    <i class="fas fa-user-check text-green-500 mr-2"></i>
                    <div>
                      <p class="font-medium text-sm"><?php echo htmlspecialchars($estudiante['docente_nombres'] . ' ' . $estudiante['docente_apellidos']); ?></p>
                      <p class="text-xs text-gris"><?php echo htmlspecialchars($estudiante['docente_email']); ?></p>
                    </div>
                  </div>
                <?php else: ?>
                  <div class="flex items-center">
                    <i class="fas fa-user-times text-red-500 mr-2"></i>
                    <span class="text-sm text-red-600">Sin asignar</span>
                  </div>
                <?php endif; ?>
              </td>
              <td class="p-3">
                <?php
                // Buscar plan del estudiante en TODOS los planes, no solo pendientes
                $planEstudiante = null;
                foreach ($planes as $plan) {
                    if ($plan['estudiante_id'] == $estudiante['id']) {
                        $planEstudiante = $plan;
                        break;
                    }
                }
                
                if ($planEstudiante) {
                    $estadoColor = 'bg-gray-100 text-gray-800';
                    $estadoTexto = 'Pendiente';
                    $estadoIcono = 'fas fa-clock';
                    
                    if ($planEstudiante['estado'] == 'pendiente') {
                        $estadoColor = 'bg-yellow-100 text-yellow-800';
                        $estadoTexto = 'Pendiente Docente';
                        $estadoIcono = 'fas fa-hourglass-half';
                    } elseif ($planEstudiante['estado'] == 'aprobado_docente') {
                        $estadoColor = 'bg-blue-100 text-blue-800';
                        $estadoTexto = 'Pendiente Coordinador';
                        $estadoIcono = 'fas fa-clock';
                    } elseif ($planEstudiante['estado'] == 'aprobado_final') {
                        $estadoColor = 'bg-green-100 text-green-800';
                        $estadoTexto = '✓ Aprobado Completo';
                        $estadoIcono = 'fas fa-check-double';
                    } elseif ($planEstudiante['estado'] == 'rechazado') {
                        $estadoColor = 'bg-red-100 text-red-800';
                        $estadoTexto = 'Rechazado';
                        $estadoIcono = 'fas fa-times-circle';
                    }
                    
                    echo "<span class='px-3 py-1 rounded-full text-xs font-medium {$estadoColor}'><i class='{$estadoIcono} mr-1'></i>{$estadoTexto}</span>";
                } else {
                    echo "<span class='px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600'><i class='fas fa-exclamation-triangle mr-1'></i>Sin plan enviado</span>";
                }
                ?>
              </td>
              <td class="p-3">
                <div class="flex flex-wrap gap-2">
                  <?php if (!$estudiante['docente_nombres']): ?>
                    <button 
                      onclick="openAssignModal(<?php echo $estudiante['id']; ?>, '<?php echo htmlspecialchars($estudiante['nombres'] . ' ' . $estudiante['apellidos']); ?>')"
                      class="bg-principal hover:bg-yellow-600 text-white px-2 py-1 rounded text-xs transition"
                    >
                      <i class="fas fa-user-plus mr-1"></i>Asignar
                    </button>
                  <?php endif; ?>
                  
                  <?php if ($planEstudiante): ?>
                    <a href="coordinador_revisar_plan.php?id=<?php echo $planEstudiante['id']; ?>" 
                       class="bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded text-xs transition">
                      <i class="fas fa-eye mr-1"></i>Ver Plan
                    </a>
                  <?php endif; ?>
                  
                  <a href="coordinador_reportes.php?estudiante=<?php echo $estudiante['id']; ?>" 
                     class="bg-green-500 hover:bg-green-600 text-white px-2 py-1 rounded text-xs transition">
                    <i class="fas fa-file-alt mr-1"></i>Reportes
                  </a>
                  
                  <a href="coordinador_informe.php?estudiante=<?php echo $estudiante['id']; ?>" 
                     class="bg-purple-500 hover:bg-purple-600 text-white px-2 py-1 rounded text-xs transition">
                    <i class="fas fa-file-signature mr-1"></i>Informe
                  </a>
                  
                  <a href="coordinador_sustentacion.php?estudiante=<?php echo $estudiante['id']; ?>" 
                     class="bg-indigo-500 hover:bg-indigo-600 text-white px-2 py-1 rounded text-xs transition">
                    <i class="fas fa-calendar-alt mr-1"></i>Sustentación
                  </a>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>

  <!-- Modal para Asignar Docente -->
  <div id="assignModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
      <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
        <div class="p-6">
          <h3 class="text-xl font-bold mb-4">Asignar Docente Asesor</h3>
          <form method="POST">
            <input type="hidden" name="action" value="asignar_docente">
            <input type="hidden" name="estudiante_id" id="estudiante_id">
            <input type="hidden" name="tipo" value="asesor">
            
            <div class="mb-4">
              <label class="block text-sm font-medium text-gris mb-2">Estudiante</label>
              <p id="estudiante_nombre" class="text-lg font-semibold"></p>
            </div>
            
            <div class="mb-4">
              <label class="block text-sm font-medium text-gris mb-2">Seleccionar Docente</label>
              <select name="docente_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-principal focus:border-transparent">
                <option value="">Seleccione un docente</option>
                <?php foreach ($docentes as $docente): ?>
                  <option value="<?php echo $docente['id']; ?>">
                    <?php echo htmlspecialchars($docente['nombres'] . ' ' . $docente['apellidos']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            
            <div class="flex justify-end space-x-4">
              <button type="button" onclick="closeAssignModal()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                Cancelar
              </button>
              <button type="submit" class="bg-principal hover:bg-yellow-600 text-white px-4 py-2 rounded">
                Asignar
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <footer class="bg-white border-t border-gray-200 mt-16 py-6">
    <div class="max-w-7xl mx-auto px-4 text-center text-gris">
      <p>&copy; 2025 Universidad Nacional del Altiplano - Puno. Sistema de Gestión de Prácticas Pre-Profesionales.</p>
    </div>
  </footer>

  <script>
    function openAssignModal(estudianteId, estudianteNombre) {
      document.getElementById('estudiante_id').value = estudianteId;
      document.getElementById('estudiante_nombre').textContent = estudianteNombre;
      document.getElementById('assignModal').classList.remove('hidden');
    }

    function closeAssignModal() {
      document.getElementById('assignModal').classList.add('hidden');
    }



    // Filtro de búsqueda
    document.getElementById('searchInput').addEventListener('input', function() {
      const searchTerm = this.value.toLowerCase();
      const rows = document.querySelectorAll('#studentTableBody tr');
      
      rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
      });
    });


  </script>
</body>
</html>