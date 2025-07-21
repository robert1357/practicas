<?php
require_once '../config/session.php';
require_once '../config/database.php';
require_once '../models/User.php';
require_once '../models/PlanPractica.php';
require_once '../models/AsignacionDocente.php';

// Verificar autenticación
if (!isAuthenticated() || !hasRole('coordinador')) {
    header("Location: login_coordinador.php");
    exit();
}

$user = getCurrentUser();
$planPractica = new PlanPractica();
$asignacion = new AsignacionDocente();

// Obtener el plan
$plan_id = $_GET['id'] ?? null;
if (!$plan_id) {
    header("Location: coordinador_dashboard.php");
    exit();
}

$plan = $planPractica->getById($plan_id);
if (!$plan || $plan['especialidad'] != $user['especialidad']) {
    header("Location: coordinador_dashboard.php");
    exit();
}

$docente = $asignacion->getDocenteAsesor($plan['estudiante_id']);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'upload_firmado') {
        // Manejar carga del plan firmado
        require_once '../config/upload_config.php';
        
        if (isset($_FILES['archivo_plan_firmado']) && $_FILES['archivo_plan_firmado']['error'] == UPLOAD_ERR_OK) {
            $uploadResult = UploadConfig::uploadFile($_FILES['archivo_plan_firmado'], 'planes', $plan['estudiante_id'], 'plan_firmado');
            
            if ($uploadResult['success']) {
                // Actualizar el plan con la ruta del archivo firmado
                $db = new Database();
                $conn = $db->connect();
                $stmt = $conn->prepare("UPDATE planes_practica SET archivo_plan_firmado = ? WHERE id = ?");
                
                if ($stmt->execute([$uploadResult['path'], $plan_id])) {
                    $success = 'Plan firmado con TOCAPU subido exitosamente';
                    $plan = $planPractica->getById($plan_id); // Recargar datos
                } else {
                    $error = 'Error al guardar el archivo firmado';
                }
            } else {
                $error = 'Error al subir el archivo: ' . implode(', ', $uploadResult['errors']);
            }
        } else {
            $error = 'Error al subir el archivo firmado';
        }
    } else {
        // Manejar aprobación/rechazo del plan
        $estado = $_POST['estado'] ?? '';
        $comentarios = $_POST['comentarios'] ?? '';
        
        $resultado = $planPractica->aprobarPlan($plan_id, $estado, $comentarios);
        
        if ($resultado === true) {
            $success = 'Plan ' . ($estado == 'aprobado' ? 'aprobado' : 'rechazado') . ' exitosamente';
            $plan = $planPractica->getById($plan_id); // Recargar datos
        } elseif (is_array($resultado) && isset($resultado['error'])) {
            $error = $resultado['error'];
        } else {
            $error = 'Error al procesar la evaluación';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Revisar Plan de Prácticas - SYSPRE 2025</title>

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
      <a href="coordinador_dashboard.php" class="text-xl font-bold text-texto flex items-center gap-2">
        <i class="fas fa-user-tie"></i> SYSPRE 2025 - Coordinador
      </a>
      <div class="space-x-6 hidden md:flex">
        <a href="coordinador_dashboard.php" class="text-gris hover:text-texto transition"><i class="fas fa-tachometer-alt mr-1"></i>Dashboard</a>
        <a href="perfil_coordinador.php" class="text-gris hover:text-texto transition"><i class="fas fa-user mr-1"></i>Mi Perfil</a>
        <a href="../config/session.php?logout=1" class="text-gris hover:text-texto transition"><i class="fas fa-sign-out-alt mr-1"></i>Cerrar Sesión</a>
      </div>
    </div>
  </nav>

  <main class="pt-24 max-w-4xl mx-auto px-4">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-texto flex items-center">
            <i class="fas fa-file-alt mr-3 text-principal"></i>
            Revisar Plan de Prácticas
          </h1>
          <p class="text-gris mt-2">Estudiante: <?php echo htmlspecialchars($plan['nombres'] . ' ' . $plan['apellidos']); ?></p>
          <p class="text-sm text-gris">Código: <?php echo htmlspecialchars($plan['codigo']); ?> - <?php echo htmlspecialchars($plan['especialidad']); ?></p>
        </div>
        <div class="text-right">
          <div class="flex items-center space-x-4">
            <?php
            $estadoColor = 'bg-yellow-100 text-yellow-800';
            $estadoTexto = 'Pendiente de Docente';
            $estadoIcono = 'fas fa-clock';
            
            if ($plan['estado'] == 'aprobado_docente') {
                $estadoColor = 'bg-blue-100 text-blue-800';
                $estadoTexto = 'Aprobado por Docente';
                $estadoIcono = 'fas fa-check-circle';
            } elseif ($plan['estado'] == 'aprobado_final') {
                $estadoColor = 'bg-green-100 text-green-800';
                $estadoTexto = 'Aprobado Final';
                $estadoIcono = 'fas fa-trophy';
            } elseif ($plan['estado'] == 'rechazado') {
                $estadoColor = 'bg-red-100 text-red-800';
                $estadoTexto = 'Rechazado';
                $estadoIcono = 'fas fa-times-circle';
            }
            ?>
            <span class="px-3 py-1 rounded-full text-sm font-medium <?php echo $estadoColor; ?>">
              <i class="<?php echo $estadoIcono; ?> mr-1"></i>
              <?php echo $estadoTexto; ?>
            </span>
            <a href="coordinador_dashboard.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition">
              <i class="fas fa-arrow-left mr-2"></i>Volver al Dashboard
            </a>
          </div>
        </div>
      </div>
    </div>

    <?php if ($error): ?>
      <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6" role="alert">
        <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
      </div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6" role="alert">
        <span class="block sm:inline"><?php echo htmlspecialchars($success); ?></span>
      </div>
    <?php endif; ?>

    <!-- Flujo de Aprobación -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
      <h2 class="text-2xl font-bold text-texto mb-6 flex items-center">
        <i class="fas fa-route mr-3 text-principal"></i>
        Flujo de Aprobación
      </h2>
      
      <div class="flex items-center space-x-4">
        <!-- Paso 1: Docente -->
        <div class="flex-1">
          <div class="flex items-center space-x-2">
            <?php if ($plan['estado'] == 'pendiente'): ?>
              <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                <i class="fas fa-clock text-yellow-600"></i>
              </div>
            <?php elseif (in_array($plan['estado'], ['aprobado_docente', 'aprobado_final'])): ?>
              <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                <i class="fas fa-check text-green-600"></i>
              </div>
            <?php else: ?>
              <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                <i class="fas fa-times text-red-600"></i>
              </div>
            <?php endif; ?>
            <div>
              <p class="font-semibold text-texto">1. Aprobación Docente</p>
              <p class="text-sm text-gris">Revisión del docente asesor</p>
            </div>
          </div>
          <?php if ($plan['estado'] != 'pendiente'): ?>
            <div class="w-full bg-green-200 h-1 mt-2"></div>
          <?php else: ?>
            <div class="w-full bg-gray-200 h-1 mt-2"></div>
          <?php endif; ?>
        </div>
        
        <!-- Flecha -->
        <div class="px-2">
          <i class="fas fa-arrow-right text-gray-400"></i>
        </div>
        
        <!-- Paso 2: Coordinador -->
        <div class="flex-1">
          <div class="flex items-center space-x-2">
            <?php if ($plan['estado'] == 'aprobado_docente'): ?>
              <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                <i class="fas fa-clock text-blue-600"></i>
              </div>
            <?php elseif ($plan['estado'] == 'aprobado_final'): ?>
              <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                <i class="fas fa-check text-green-600"></i>
              </div>
            <?php elseif ($plan['estado'] == 'rechazado'): ?>
              <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                <i class="fas fa-times text-red-600"></i>
              </div>
            <?php else: ?>
              <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center">
                <i class="fas fa-clock text-gray-400"></i>
              </div>
            <?php endif; ?>
            <div>
              <p class="font-semibold text-texto">2. Aprobación Coordinador</p>
              <p class="text-sm text-gris">Aprobación final del coordinador</p>
            </div>
          </div>
          <?php if ($plan['estado'] == 'aprobado_final'): ?>
            <div class="w-full bg-green-200 h-1 mt-2"></div>
          <?php else: ?>
            <div class="w-full bg-gray-200 h-1 mt-2"></div>
          <?php endif; ?>
        </div>
      </div>
      
      <!-- Mensaje de estado -->
      <?php if ($plan['estado'] == 'pendiente'): ?>
        <div class="mt-4 p-3 bg-yellow-50 border-l-4 border-yellow-400">
          <p class="text-sm text-yellow-800">
            <i class="fas fa-info-circle mr-2"></i>
            El plan está pendiente de revisión del docente asesor. El coordinador podrá aprobar después de la aprobación del docente.
          </p>
        </div>
      <?php elseif ($plan['estado'] == 'aprobado_docente'): ?>
        <div class="mt-4 p-3 bg-blue-50 border-l-4 border-blue-400">
          <p class="text-sm text-blue-800">
            <i class="fas fa-check-circle mr-2"></i>
            El plan ha sido aprobado por el docente asesor. Ahora puedes proceder con la aprobación final.
          </p>
        </div>
      <?php elseif ($plan['estado'] == 'aprobado_final'): ?>
        <div class="mt-4 p-3 bg-green-50 border-l-4 border-green-400">
          <p class="text-sm text-green-800">
            <i class="fas fa-trophy mr-2"></i>
            El plan ha sido aprobado completamente. El estudiante puede proceder con las prácticas.
          </p>
        </div>
      <?php elseif ($plan['estado'] == 'rechazado'): ?>
        <div class="mt-4 p-3 bg-red-50 border-l-4 border-red-400">
          <p class="text-sm text-red-800">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            El plan ha sido rechazado. El estudiante debe realizar las correcciones necesarias.
          </p>
        </div>
      <?php endif; ?>
    </div>

    <!-- Información del Plan -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
      <h2 class="text-2xl font-bold text-texto mb-6 flex items-center">
        <i class="fas fa-building mr-3 text-principal"></i>
        Información de la Empresa
      </h2>
      
      <div class="grid gap-6 md:grid-cols-2">
        <div>
          <label class="block text-sm font-medium text-gris mb-2">Empresa</label>
          <p class="text-lg font-semibold text-texto"><?php echo htmlspecialchars($plan['empresa']); ?></p>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gris mb-2">RUC</label>
          <p class="text-lg font-semibold text-texto"><?php echo htmlspecialchars($plan['ruc']); ?></p>
        </div>
        
        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gris mb-2">Dirección</label>
          <p class="text-lg font-semibold text-texto"><?php echo htmlspecialchars($plan['direccion_empresa']); ?></p>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gris mb-2">Supervisor</label>
          <p class="text-lg font-semibold text-texto"><?php echo htmlspecialchars($plan['supervisor'] ?? 'No especificado'); ?></p>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gris mb-2">Teléfono</label>
          <p class="text-lg font-semibold text-texto"><?php echo htmlspecialchars($plan['telefono_empresa']); ?></p>
        </div>
      </div>
    </div>

    <!-- Información de las Prácticas -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
      <h2 class="text-2xl font-bold text-texto mb-6 flex items-center">
        <i class="fas fa-briefcase mr-3 text-principal"></i>
        Información de las Prácticas
      </h2>
      
      <div class="grid gap-6 md:grid-cols-2">
        <div>
          <label class="block text-sm font-medium text-gris mb-2">Área de Práctica</label>
          <p class="text-lg font-semibold text-texto"><?php echo htmlspecialchars($plan['especialidad'] ?? 'No especificada'); ?></p>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gris mb-2">Horas Semanales</label>
          <p class="text-lg font-semibold text-texto"><?php echo htmlspecialchars($plan['total_horas'] ?? 'No especificado'); ?> horas</p>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gris mb-2">Fecha de Inicio</label>
          <p class="text-lg font-semibold text-texto"><?php echo date('d/m/Y', strtotime($plan['fecha_inicio'])); ?></p>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gris mb-2">Fecha de Fin</label>
          <p class="text-lg font-semibold text-texto"><?php echo date('d/m/Y', strtotime($plan['fecha_fin'])); ?></p>
        </div>
      </div>
    </div>

    <!-- Objetivos y Actividades -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
      <h2 class="text-2xl font-bold text-texto mb-6 flex items-center">
        <i class="fas fa-target mr-3 text-principal"></i>
        Objetivos y Actividades
      </h2>
      
      <div class="space-y-6">
        <div>
          <label class="block text-sm font-medium text-gris mb-2">Objetivos</label>
          <div class="bg-gray-50 rounded-lg p-4">
            <p class="text-texto whitespace-pre-wrap"><?php echo htmlspecialchars($plan['objetivos']); ?></p>
          </div>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gris mb-2">Actividades a Realizar</label>
          <div class="bg-gray-50 rounded-lg p-4">
            <p class="text-texto whitespace-pre-wrap"><?php echo htmlspecialchars($plan['actividades']); ?></p>
          </div>
        </div>
      </div>
    </div>

    <!-- Documentos Adjuntos -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
      <h2 class="text-2xl font-bold text-texto mb-6 flex items-center">
        <i class="fas fa-file-alt mr-3 text-principal"></i>
        Documentos del Plan de Prácticas
      </h2>
      
      <div class="space-y-4">
        <?php if (isset($plan['archivo_plan']) && $plan['archivo_plan']): ?>
          <div class="p-4 bg-blue-50 rounded-lg">
            <div class="flex items-center justify-between mb-3">
              <div class="flex items-center">
                <i class="fas fa-file-pdf text-red-500 mr-3 text-xl"></i>
                <div>
                  <h4 class="font-semibold text-texto">Plan de Prácticas</h4>
                  <p class="text-sm text-gris">Archivo principal del plan</p>
                </div>
              </div>
              <a href="download_plan_file.php?file=<?php echo urlencode($plan['archivo_plan']); ?>&plan_id=<?php echo $plan['id']; ?>" 
                 class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition">
                <i class="fas fa-download mr-2"></i>Descargar
              </a>
            </div>
            
            <!-- Formulario para cargar plan firmado -->
            <?php if (!isset($plan['archivo_plan_firmado']) || !$plan['archivo_plan_firmado']): ?>
            <div class="border-t pt-3">
              <form method="POST" enctype="multipart/form-data" class="space-y-3">
                <div>
                  <label class="block text-sm font-medium text-gris mb-2">
                    <i class="fas fa-stamp mr-1 text-green-600"></i>
                    Cargar Plan Firmado con TOCAPU
                  </label>
                  <input 
                    type="file" 
                    name="archivo_plan_firmado" 
                    accept=".pdf,.doc,.docx"
                    class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-green-500"
                    required
                  >
                  <p class="text-xs text-gray-600 mt-1">
                    Sube el plan firmado y sellado con TOCAPU (PDF, DOC, DOCX)
                  </p>
                </div>
                <button 
                  type="submit" 
                  name="action" 
                  value="upload_firmado"
                  class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition text-sm"
                >
                  <i class="fas fa-upload mr-2"></i>Subir Plan Firmado
                </button>
              </form>
            </div>
            <?php else: ?>
            <!-- Mostrar plan firmado -->
            <div class="border-t pt-3">
              <div class="bg-green-50 p-3 rounded">
                <div class="flex items-center justify-between">
                  <div class="flex items-center">
                    <i class="fas fa-stamp text-green-600 mr-2"></i>
                    <span class="text-sm font-medium text-green-800">Plan Firmado con TOCAPU</span>
                  </div>
                  <a href="download_plan_file.php?file=<?php echo urlencode($plan['archivo_plan_firmado']); ?>&plan_id=<?php echo $plan['id']; ?>" 
                     class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm transition">
                    <i class="fas fa-download mr-1"></i>Descargar
                  </a>
                </div>
                <p class="text-xs text-green-700 mt-1">
                  Archivo firmado y sellado oficialmente
                </p>
              </div>
            </div>
            <?php endif; ?>
          </div>
        <?php endif; ?>
        
        <?php for ($i = 1; $i <= 3; $i++): ?>
          <?php if (isset($plan['archivo_documento' . $i]) && $plan['archivo_documento' . $i]): ?>
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
              <div class="flex items-center">
                <i class="fas fa-paperclip text-gray-500 mr-3 text-xl"></i>
                <div>
                  <h4 class="font-semibold text-texto">Documento Adicional <?php echo $i; ?></h4>
                  <p class="text-sm text-gris">Archivo adjunto</p>
                </div>
              </div>
              <a href="download_plan_file.php?file=<?php echo urlencode($plan['archivo_documento' . $i]); ?>&plan_id=<?php echo $plan['id']; ?>" 
                 class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition">
                <i class="fas fa-download mr-2"></i>Descargar
              </a>
            </div>
          <?php endif; ?>
        <?php endfor; ?>
        
        <?php if (empty($plan['archivo_plan']) && empty($plan['archivo_documento1']) && empty($plan['archivo_documento2']) && empty($plan['archivo_documento3'])): ?>
          <div class="text-center py-8">
            <i class="fas fa-file-slash text-gray-300 text-4xl mb-4"></i>
            <p class="text-gray-500">No hay documentos adjuntos en este plan.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Docente Asesor -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
      <h2 class="text-2xl font-bold text-texto mb-6 flex items-center">
        <i class="fas fa-user-tie mr-3 text-principal"></i>
        Docente Asesor
      </h2>
      
      <?php if ($docente): ?>
        <div class="bg-green-50 rounded-lg p-4">
          <p class="text-lg font-semibold text-texto"><?php echo htmlspecialchars($docente['nombres'] . ' ' . $docente['apellidos']); ?></p>
          <p class="text-gris"><?php echo htmlspecialchars($docente['email']); ?></p>
          <p class="text-gris">Código: <?php echo htmlspecialchars($docente['codigo']); ?></p>
        </div>
      <?php else: ?>
        <div class="bg-yellow-50 rounded-lg p-4">
          <p class="text-yellow-800 font-medium">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            Este estudiante aún no tiene un docente asesor asignado.
          </p>
          <a href="coordinador_dashboard.php" class="text-yellow-600 hover:text-yellow-800 underline">
            Asignar docente asesor
          </a>
        </div>
      <?php endif; ?>
    </div>

    <!-- Evaluación del Docente -->
    <?php if (isset($plan['nota_docente']) && $plan['nota_docente'] || isset($plan['comentarios_docente']) && $plan['comentarios_docente']): ?>
    <div class="bg-white rounded-lg shadow p-6 mb-8">
      <h2 class="text-2xl font-bold text-texto mb-6 flex items-center">
        <i class="fas fa-star mr-3 text-principal"></i>
        Evaluación del Docente
      </h2>
      
      <div class="bg-blue-50 rounded-lg p-4">
        <?php if (isset($plan['nota_docente']) && $plan['nota_docente']): ?>
          <div class="flex items-center justify-between mb-3">
            <h3 class="font-semibold text-texto">Calificación</h3>
            <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium">
              <?php echo $plan['nota_docente']; ?>/20
            </span>
          </div>
        <?php endif; ?>
        
        <?php if (isset($plan['comentarios_docente']) && $plan['comentarios_docente']): ?>
          <div>
            <h3 class="font-semibold text-texto mb-2">Comentarios</h3>
            <p class="text-gris whitespace-pre-wrap"><?php echo htmlspecialchars($plan['comentarios_docente']); ?></p>
          </div>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- Formulario de Evaluación -->
    <?php if ($plan['estado'] == 'aprobado_docente'): ?>
    <div class="bg-white rounded-lg shadow p-6 mb-8">
      <h2 class="text-2xl font-bold text-texto mb-6 flex items-center">
        <i class="fas fa-clipboard-check mr-3 text-principal"></i>
        Aprobación Final del Coordinador
      </h2>
      
      <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
        <div class="flex">
          <div class="flex-shrink-0">
            <i class="fas fa-info-circle text-blue-600"></i>
          </div>
          <div class="ml-3">
            <p class="text-sm text-blue-800">
              <strong>El docente ya aprobó este plan.</strong> Como coordinador, puedes hacer la aprobación final o rechazarlo si encuentras algún problema.
            </p>
          </div>
        </div>
      </div>
      
      <form method="POST" class="space-y-6">
        <div>
          <label class="block text-sm font-medium text-gris mb-2">Comentarios del Coordinador</label>
          <textarea 
            name="comentarios" 
            rows="4"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-principal focus:border-transparent"
            placeholder="Escribe tus comentarios sobre el plan de prácticas (opcional)..."
          ></textarea>
          <p class="text-sm text-gray-600 mt-1">
            Puedes agregar comentarios adicionales o dejar vacío para aprobar directamente.
          </p>
        </div>
        
        <div class="flex justify-end space-x-4">
          <button 
            type="submit" 
            name="estado" 
            value="rechazado"
            class="bg-red-500 hover:bg-red-600 text-white px-6 py-3 rounded-lg transition"
          >
            <i class="fas fa-times mr-2"></i>Rechazar Plan
          </button>
          <button 
            type="submit" 
            name="estado" 
            value="aprobado"
            class="bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-lg transition"
          >
            <i class="fas fa-trophy mr-2"></i>Aprobar Definitivamente
          </button>
        </div>
      </form>
    </div>
    <?php endif; ?>

    <!-- Comentarios del Coordinador -->
    <?php 
    $comentarios_coordinador = '';
    if (isset($plan['observaciones_docente']) && strpos($plan['observaciones_docente'], '[COORDINADOR]:') !== false) {
        $partes = explode('[COORDINADOR]:', $plan['observaciones_docente']);
        if (count($partes) > 1) {
            $comentarios_coordinador = trim($partes[1]);
        }
    }
    ?>
    <?php if ($comentarios_coordinador): ?>
    <div class="bg-white rounded-lg shadow p-6 mb-8">
      <h2 class="text-2xl font-bold text-texto mb-6 flex items-center">
        <i class="fas fa-comment mr-3 text-principal"></i>
        Comentarios del Coordinador
      </h2>
      
      <div class="bg-yellow-50 rounded-lg p-4">
        <p class="text-gris whitespace-pre-wrap"><?php echo htmlspecialchars($comentarios_coordinador); ?></p>
        <p class="text-sm text-gris mt-2">
          <i class="fas fa-clock mr-1"></i>
          Evaluado el <?php echo date('d/m/Y H:i', strtotime($plan['fecha_revision'] ?? date('Y-m-d H:i:s'))); ?>
        </p>
      </div>
    </div>
    <?php endif; ?>
  </main>

  <!-- Footer -->
  <footer class="bg-white border-t border-gray-200 mt-16 py-6">
    <div class="max-w-7xl mx-auto px-4 text-center text-gris">
      <p>&copy; 2025 Universidad Nacional del Altiplano - Puno. Sistema de Gestión de Prácticas Pre-Profesionales.</p>
    </div>
  </footer>


</body>
</html>