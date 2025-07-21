<?php
require_once '../config/session.php';
require_once '../models/User.php';
require_once '../models/PlanPractica.php';

// Verificar autenticación
if (!isAuthenticated() || !hasRole('estudiante')) {
    header("Location: login_estudiante.php");
    exit();
}

$user = getCurrentUser();
$planPractica = new PlanPractica();

// Verificar si ya tiene un plan registrado
$planExistente = $planPractica->getByEstudiante($user['id']);
if ($planExistente) {
    header("Location: estudiante_ver_plan.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_once __DIR__ . '/../config/upload_config.php';
    
    // Calcular total de horas
    $horas_semanales = intval($_POST['horas_semanales']);
    $fecha_inicio = new DateTime($_POST['fecha_inicio']);
    $fecha_fin = new DateTime($_POST['fecha_fin']);
    $diff = $fecha_inicio->diff($fecha_fin);
    $semanas = ceil($diff->days / 7);
    $total_horas = $horas_semanales * $semanas;
    
    // Preparar datos básicos del plan
    $data = [
        'estudiante_id' => $user['id'],
        'nombres' => $user['nombres'],
        'apellidos' => $user['apellidos'],
        'codigo' => $user['codigo'],
        'especialidad' => $user['especialidad'],
        'email' => $user['email'],
        'telefono' => $user['telefono'] ?? $_POST['telefono'] ?? '000000000',
        'empresa' => $_POST['empresa'],
        'ruc' => $_POST['ruc'],
        'direccion_empresa' => $_POST['direccion_empresa'],
        'telefono_empresa' => $_POST['telefono_empresa'],
        'supervisor' => $_POST['supervisor_empresa'],
        'cargo_supervisor' => $_POST['cargo_supervisor'],
        'fecha_inicio' => $_POST['fecha_inicio'],
        'fecha_fin' => $_POST['fecha_fin'],
        'horario' => $_POST['horario'],
        'total_horas' => $total_horas,
        'actividades' => $_POST['actividades'],
        'objetivos' => $_POST['objetivos'],
        'estado' => 'pendiente'
    ];
    
    $uploadErrors = [];
    
    // Manejar carga de archivo del plan
    if (isset($_FILES['archivo_plan']) && $_FILES['archivo_plan']['error'] == UPLOAD_ERR_OK) {
        $uploadResult = UploadConfig::uploadFile($_FILES['archivo_plan'], 'planes', $user['id'], 'plan_practica');
        if ($uploadResult['success']) {
            $data['archivo_plan'] = $uploadResult['path'];
        } else {
            $uploadErrors = array_merge($uploadErrors, $uploadResult['errors']);
        }
    }
    
    // Manejar archivos documentos adicionales
    for ($i = 1; $i <= 3; $i++) {
        $fieldName = 'archivo_documento' . $i;
        if (isset($_FILES[$fieldName]) && $_FILES[$fieldName]['error'] == UPLOAD_ERR_OK) {
            $uploadResult = UploadConfig::uploadFile($_FILES[$fieldName], 'planes', $user['id'], 'documento' . $i);
            if ($uploadResult['success']) {
                $data[$fieldName] = $uploadResult['path'];
            } else {
                $uploadErrors = array_merge($uploadErrors, $uploadResult['errors']);
            }
        }
    }
    
    // Si hay errores en la carga, mostrarlos
    if (!empty($uploadErrors)) {
        $error = 'Errores en la carga de archivos: ' . implode(', ', $uploadErrors);
    } else {
        // Intentar crear el plan
        if ($planPractica->create($data)) {
            $success = 'Plan de prácticas registrado exitosamente con archivos adjuntos. Será revisado por el coordinador y docente.';
        } else {
            $error = 'Error al registrar el plan de prácticas. Intenta nuevamente.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Registro de Plan de Prácticas - SYSPRE 2025</title>

  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
  
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            principal: '#3eb489',
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
      <a href="estudiante_dashboard.php" class="text-xl font-bold text-texto flex items-center gap-2">
        <i class="fas fa-user-graduate"></i> SYSPRE 2025 - Estudiante
      </a>
      <div class="space-x-6 hidden md:flex">
        <a href="estudiante_dashboard.php" class="text-gris hover:text-texto transition"><i class="fas fa-tachometer-alt mr-1"></i>Dashboard</a>
        <a href="perfil_estudiante.php" class="text-gris hover:text-texto transition"><i class="fas fa-user mr-1"></i>Mi Perfil</a>
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
            <i class="fas fa-file-signature mr-3 text-principal"></i>
            Registro de Plan de Prácticas
          </h1>
          <p class="text-gris mt-2"><?php echo htmlspecialchars($user['nombres'] . ' ' . $user['apellidos']); ?> - <?php echo htmlspecialchars($user['especialidad']); ?></p>
        </div>
        <div class="text-right">
          <a href="estudiante_dashboard.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition">
            <i class="fas fa-arrow-left mr-2"></i>Volver al Dashboard
          </a>
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
        <div class="mt-2">
          <a href="estudiante_dashboard.php" class="text-green-600 hover:text-green-800 underline">
            Ir al Dashboard
          </a>
        </div>
      </div>
    <?php endif; ?>

    <!-- Formulario -->
    <div class="bg-white rounded-lg shadow p-6">
      <form method="POST" enctype="multipart/form-data" class="space-y-6">
        
        <!-- Información de la Empresa -->
        <div>
          <h3 class="text-xl font-semibold text-texto mb-4 flex items-center">
            <i class="fas fa-building mr-2 text-principal"></i>
            Información de la Empresa
          </h3>
          
          <div class="grid gap-4 md:grid-cols-2">
            <div>
              <label class="block text-sm font-medium text-gris mb-2">Nombre de la Empresa *</label>
              <input 
                type="text" 
                name="empresa" 
                required 
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-principal focus:border-transparent"
                placeholder="Ingrese el nombre de la empresa"
              >
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gris mb-2">RUC *</label>
              <input 
                type="text" 
                name="ruc" 
                required 
                maxlength="11"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-principal focus:border-transparent"
                placeholder="12345678901"
              >
            </div>
          </div>
          
          <div class="mt-4">
            <label class="block text-sm font-medium text-gris mb-2">Dirección de la Empresa *</label>
            <textarea 
              name="direccion_empresa" 
              required 
              rows="3"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-principal focus:border-transparent"
              placeholder="Ingrese la dirección completa de la empresa"
            ></textarea>
          </div>
        </div>

        <!-- Supervisor de la Empresa -->
        <div>
          <h3 class="text-xl font-semibold text-texto mb-4 flex items-center">
            <i class="fas fa-user-tie mr-2 text-principal"></i>
            Supervisor de la Empresa
          </h3>
          
          <div class="grid gap-4 md:grid-cols-2">
            <div>
              <label class="block text-sm font-medium text-gris mb-2">Nombre del Supervisor *</label>
              <input 
                type="text" 
                name="supervisor_empresa" 
                required 
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-principal focus:border-transparent"
                placeholder="Nombres y apellidos del supervisor"
              >
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gris mb-2">Teléfono del Supervisor *</label>
              <input 
                type="text" 
                name="telefono_empresa" 
                required 
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-principal focus:border-transparent"
                placeholder="999999999"
              >
            </div>
          </div>
          
          <div class="mt-4">
            <label class="block text-sm font-medium text-gris mb-2">Cargo del Supervisor *</label>
            <input 
              type="text" 
              name="cargo_supervisor" 
              required 
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-principal focus:border-transparent"
              placeholder="Ej: Jefe de Sistemas, Supervisor de Área, etc."
            >
          </div>
        </div>

        <!-- Información del Estudiante -->
        <div>
          <h3 class="text-xl font-semibold text-texto mb-4 flex items-center">
            <i class="fas fa-user mr-2 text-principal"></i>
            Información del Estudiante
          </h3>
          
          <div class="grid gap-4 md:grid-cols-2">
            <div>
              <label class="block text-sm font-medium text-gris mb-2">Nombres y Apellidos</label>
              <input 
                type="text" 
                value="<?php echo htmlspecialchars($user['nombres'] . ' ' . $user['apellidos']); ?>" 
                disabled
                class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-600"
              >
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gris mb-2">Teléfono Personal</label>
              <input 
                type="text" 
                name="telefono" 
                value="<?php echo htmlspecialchars($user['telefono'] ?? ''); ?>" 
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-principal focus:border-transparent"
                placeholder="999999999"
              >
            </div>
          </div>
        </div>

        <!-- Información de las Prácticas -->
        <div>
          <h3 class="text-xl font-semibold text-texto mb-4 flex items-center">
            <i class="fas fa-briefcase mr-2 text-principal"></i>
            Información de las Prácticas
          </h3>
          
          <div class="grid gap-4 md:grid-cols-2">
            <div>
              <label class="block text-sm font-medium text-gris mb-2">Horario de Prácticas *</label>
              <input 
                type="text" 
                name="horario" 
                required 
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-principal focus:border-transparent"
                placeholder="Ej: Lunes a Viernes 8:00 AM - 5:00 PM"
                value="Lunes a Viernes 8:00 AM - 5:00 PM"
              >
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gris mb-2">Horas Semanales *</label>
              <input 
                type="number" 
                name="horas_semanales" 
                required 
                min="20" 
                max="48"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-principal focus:border-transparent"
                placeholder="40"
              >
            </div>
          </div>
        </div>

        <!-- Información de las Prácticas -->
        <div>
          <h3 class="text-xl font-semibold text-texto mb-4 flex items-center">
            <i class="fas fa-briefcase mr-2 text-principal"></i>
            Información de las Prácticas
          </h3>
          

          
          <div class="grid gap-4 md:grid-cols-2 mt-4">
            <div>
              <label class="block text-sm font-medium text-gris mb-2">Fecha de Inicio *</label>
              <input 
                type="date" 
                name="fecha_inicio" 
                required 
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-principal focus:border-transparent"
              >
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gris mb-2">Fecha de Fin *</label>
              <input 
                type="date" 
                name="fecha_fin" 
                required 
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-principal focus:border-transparent"
              >
            </div>
          </div>
        </div>

        <!-- Objetivos y Actividades -->
        <div>
          <h3 class="text-xl font-semibold text-texto mb-4 flex items-center">
            <i class="fas fa-target mr-2 text-principal"></i>
            Objetivos y Actividades
          </h3>
          
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gris mb-2">Objetivos de las Prácticas *</label>
              <textarea 
                name="objetivos" 
                required 
                rows="4"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-principal focus:border-transparent"
                placeholder="Describe los objetivos que esperas lograr durante las prácticas..."
              ></textarea>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gris mb-2">Actividades a Realizar *</label>
              <textarea 
                name="actividades" 
                required 
                rows="4"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-principal focus:border-transparent"
                placeholder="Describe las actividades específicas que realizarás durante las prácticas..."
              ></textarea>
            </div>
          </div>
        </div>

        <!-- Documentos y Archivos -->
        <div>
          <h3 class="text-xl font-semibold text-texto mb-4 flex items-center">
            <i class="fas fa-file-upload mr-2 text-principal"></i>
            Documentos del Plan de Prácticas
          </h3>
          
          <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div class="flex items-center">
              <i class="fas fa-info-circle text-blue-500 mr-2"></i>
              <div>
                <p class="text-sm text-blue-800 font-medium">Información importante:</p>
                <p class="text-sm text-blue-700 mt-1">
                  Debes adjuntar el documento del plan de prácticas en formato PDF o Word. 
                  Este archivo será revisado por el coordinador y docente asesor.
                </p>
              </div>
            </div>
          </div>
          
          <div class="space-y-4">
            <!-- Archivo del Plan Principal -->
            <div class="border border-gray-200 rounded-lg p-4">
              <label class="block text-sm font-medium text-gris mb-2">
                <i class="fas fa-file-pdf mr-1 text-red-500"></i>
                Plan de Prácticas (PDF/Word) *
              </label>
              <input 
                type="file" 
                name="archivo_plan" 
                accept=".pdf,.doc,.docx"
                required
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-principal focus:border-transparent"
              >
              <p class="text-xs text-gray-500 mt-1">
                Formatos permitidos: PDF, DOC, DOCX. Tamaño máximo: 10MB
              </p>
            </div>
            
            <!-- Documentos Adicionales Opcionales -->
            <div class="border border-gray-200 rounded-lg p-4">
              <label class="block text-sm font-medium text-gris mb-2">
                <i class="fas fa-paperclip mr-1 text-gray-500"></i>
                Documentos Adicionales (Opcional)
              </label>
              
              <div class="space-y-3">
                <div>
                  <label class="text-sm text-gray-600">Documento 1:</label>
                  <input 
                    type="file" 
                    name="archivo_documento1" 
                    accept=".pdf,.doc,.docx"
                    class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-principal"
                  >
                </div>
                
                <div>
                  <label class="text-sm text-gray-600">Documento 2:</label>
                  <input 
                    type="file" 
                    name="archivo_documento2" 
                    accept=".pdf,.doc,.docx"
                    class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-principal"
                  >
                </div>
                
                <div>
                  <label class="text-sm text-gray-600">Documento 3:</label>
                  <input 
                    type="file" 
                    name="archivo_documento3" 
                    accept=".pdf,.doc,.docx"
                    class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-principal"
                  >
                </div>
              </div>
              
              <p class="text-xs text-gray-500 mt-2">
                Puedes adjuntar hasta 3 documentos adicionales como carta de aceptación, convenios, etc.
              </p>
            </div>
          </div>
        </div>

        <!-- Botones -->
        <div class="flex justify-end space-x-4 pt-6 border-t">
          <a href="estudiante_dashboard.php" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg transition">
            <i class="fas fa-times mr-2"></i>Cancelar
          </a>
          <button 
            type="submit" 
            class="bg-principal hover:bg-green-600 text-white px-6 py-3 rounded-lg transition"
          >
            <i class="fas fa-save mr-2"></i>Registrar Plan
          </button>
        </div>
      </form>
    </div>
  </main>

  <!-- Footer -->
  <footer class="bg-white border-t border-gray-200 mt-16 py-6">
    <div class="max-w-7xl mx-auto px-4 text-center text-gris">
      <p>&copy; 2025 Universidad Nacional del Altiplano - Puno. Sistema de Gestión de Prácticas Pre-Profesionales.</p>
    </div>
  </footer>

  <script>
    // Validación de fechas
    document.addEventListener('DOMContentLoaded', function() {
      const fechaInicio = document.querySelector('input[name="fecha_inicio"]');
      const fechaFin = document.querySelector('input[name="fecha_fin"]');
      
      fechaInicio.addEventListener('change', function() {
        fechaFin.min = this.value;
      });
      
      fechaFin.addEventListener('change', function() {
        if (this.value < fechaInicio.value) {
          alert('La fecha de fin no puede ser anterior a la fecha de inicio');
          this.value = '';
        }
      });
    });
    
    // Mostrar nombre del archivo seleccionado
    document.querySelectorAll('input[type="file"]').forEach(input => {
      input.addEventListener('change', function(e) {
        const fileName = e.target.files[0]?.name || 'No hay archivo seleccionado';
        const fileSize = e.target.files[0]?.size || 0;
        
        // Crear o actualizar el texto de información del archivo
        let infoElement = e.target.nextElementSibling;
        if (infoElement && infoElement.classList.contains('file-info')) {
          infoElement.remove();
        }
        
        if (e.target.files[0]) {
          const info = document.createElement('div');
          info.className = 'file-info text-sm text-green-600 mt-1 flex items-center';
          info.innerHTML = `
            <i class="fas fa-check-circle mr-1"></i>
            <span class="font-medium">${fileName}</span>
            <span class="text-gray-500 ml-2">(${(fileSize / 1024).toFixed(1)} KB)</span>
          `;
          e.target.parentNode.appendChild(info);
        }
      });
    });
    
    // Validar formulario antes de enviar
    document.querySelector('form').addEventListener('submit', function(e) {
      const archivoPlan = document.querySelector('input[name="archivo_plan"]');
      
      if (!archivoPlan.files[0]) {
        e.preventDefault();
        alert('Por favor, selecciona el archivo del plan de prácticas (requerido).');
        archivoPlan.focus();
        return false;
      }
      
      // Validar tamaño de archivos
      const maxSize = 10 * 1024 * 1024; // 10MB
      const fileInputs = document.querySelectorAll('input[type="file"]');
      
      for (let input of fileInputs) {
        if (input.files[0] && input.files[0].size > maxSize) {
          e.preventDefault();
          alert(`El archivo ${input.files[0].name} es demasiado grande. Máximo permitido: 10MB`);
          input.focus();
          return false;
        }
      }
      
      // Mostrar mensaje de carga
      const submitButton = document.querySelector('button[type="submit"]');
      submitButton.disabled = true;
      submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Subiendo archivos...';
    });
  </script>
</body>
</html>