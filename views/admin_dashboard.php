<?php
require_once '../config/session.php';
require_once '../models/User.php';
require_once '../models/PlanPractica.php';
require_once '../models/AsignacionDocente.php';

// Verificar autenticación
if (!isAuthenticated() || !hasRole('admin')) {
    header("Location: login_admin.php");
    exit();
}

$user = getCurrentUser();
$userModel = new User();
$planPractica = new PlanPractica();
$asignacion = new AsignacionDocente();

// Obtener estadísticas generales
$totalEstudiantes = $userModel->countByType('estudiante');
$totalDocentes = $userModel->countByType('docente');
$totalCoordinadores = $userModel->countByType('coordinador');
$totalPlanes = $planPractica->countAll();
$planesAprobados = $planPractica->countByStatus('aprobado');
$planesPendientes = $planPractica->countByStatus('pendiente');

// Obtener todos los usuarios
$estudiantes = $userModel->getByType('estudiante');
$docentes = $userModel->getByType('docente');
$coordinadores = $userModel->getByType('coordinador');
$admins = $userModel->getByType('admin');

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'activar_usuario':
                $userId = $_POST['user_id'];
                if ($userModel->activateUser($userId)) {
                    $success = 'Usuario activado exitosamente';
                } else {
                    $error = 'Error al activar usuario';
                }
                break;
            
            case 'desactivar_usuario':
                $userId = $_POST['user_id'];
                if ($userModel->deactivateUser($userId)) {
                    $success = 'Usuario desactivado exitosamente';
                } else {
                    $error = 'Error al desactivar usuario';
                }
                break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard Administrador - SYSPRE 2025</title>

  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
  
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
<body class="bg-fondo min-h-screen">
  <!-- Header -->
  <header class="bg-principal text-white shadow-lg">
    <div class="container mx-auto px-4 py-4">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <i class="fas fa-cogs text-2xl"></i>
          <div>
            <h1 class="text-xl font-bold">SYSPRE 2025</h1>
            <p class="text-sm opacity-90">Panel de Administración</p>
          </div>
        </div>
        <div class="flex items-center space-x-4">
          <span class="text-sm">
            <i class="fas fa-user-shield mr-2"></i>
            <?php echo htmlspecialchars($user['nombres'] . ' ' . $user['apellidos']); ?>
          </span>
          <a href="../logout.php" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded-lg transition-colors">
            <i class="fas fa-sign-out-alt mr-2"></i>Cerrar Sesión
          </a>
        </div>
      </div>
    </div>
  </header>

  <div class="container mx-auto px-4 py-6">
    <!-- Estadísticas -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div class="bg-white p-6 rounded-lg shadow-sm">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gris text-sm">Total Estudiantes</p>
            <p class="text-2xl font-bold text-texto"><?php echo $totalEstudiantes; ?></p>
          </div>
          <i class="fas fa-user-graduate text-3xl text-principal"></i>
        </div>
      </div>
      
      <div class="bg-white p-6 rounded-lg shadow-sm">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gris text-sm">Total Docentes</p>
            <p class="text-2xl font-bold text-texto"><?php echo $totalDocentes; ?></p>
          </div>
          <i class="fas fa-chalkboard-teacher text-3xl text-principal"></i>
        </div>
      </div>
      
      <div class="bg-white p-6 rounded-lg shadow-sm">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gris text-sm">Coordinadores</p>
            <p class="text-2xl font-bold text-texto"><?php echo $totalCoordinadores; ?></p>
          </div>
          <i class="fas fa-users-cog text-3xl text-principal"></i>
        </div>
      </div>
      
      <div class="bg-white p-6 rounded-lg shadow-sm">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gris text-sm">Planes Registrados</p>
            <p class="text-2xl font-bold text-texto"><?php echo $totalPlanes; ?></p>
          </div>
          <i class="fas fa-file-alt text-3xl text-principal"></i>
        </div>
      </div>
    </div>

    <!-- Mensajes -->
    <?php if (isset($success)): ?>
      <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-4">
        <i class="fas fa-check-circle mr-2"></i><?php echo $success; ?>
      </div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
      <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-4">
        <i class="fas fa-exclamation-circle mr-2"></i><?php echo $error; ?>
      </div>
    <?php endif; ?>

    <!-- Botón de Registro de Docente/Coordinador -->
    <div class="mb-6 text-center">
      <a href="registro_docente_coordinador.php" class="bg-principal hover:bg-emerald-600 text-white font-bold py-3 px-6 rounded-lg transition-colors inline-flex items-center">
        <i class="fas fa-user-plus mr-2"></i>
        Registrar Docente/Coordinador
      </a>
    </div>

    <!-- Tabs -->
    <div class="bg-white rounded-lg shadow-sm">
      <div class="border-b border-gray-200">
        <nav class="flex space-x-8 px-6">
          <button class="tab-button active py-4 px-2 border-b-2 border-principal text-principal font-medium" onclick="switchTab('estudiantes')">
            <i class="fas fa-user-graduate mr-2"></i>Estudiantes
          </button>
          <button class="tab-button py-4 px-2 border-b-2 border-transparent text-gris hover:text-principal" onclick="switchTab('docentes')">
            <i class="fas fa-chalkboard-teacher mr-2"></i>Docentes
          </button>
          <button class="tab-button py-4 px-2 border-b-2 border-transparent text-gris hover:text-principal" onclick="switchTab('coordinadores')">
            <i class="fas fa-users-cog mr-2"></i>Coordinadores
          </button>
          <button class="tab-button py-4 px-2 border-b-2 border-transparent text-gris hover:text-principal" onclick="switchTab('estadisticas')">
            <i class="fas fa-chart-bar mr-2"></i>Estadísticas
          </button>
        </nav>
      </div>

      <!-- Contenido de tabs -->
      <div class="p-6">
        <!-- Tab Estudiantes -->
        <div id="estudiantes" class="tab-content">
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="text-left border-b border-gray-200">
                  <th class="pb-3 font-medium text-gris">Código</th>
                  <th class="pb-3 font-medium text-gris">Nombre</th>
                  <th class="pb-3 font-medium text-gris">Email</th>
                  <th class="pb-3 font-medium text-gris">Especialidad</th>
                  <th class="pb-3 font-medium text-gris">Estado</th>
                  <th class="pb-3 font-medium text-gris">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($estudiantes as $estudiante): ?>
                <tr class="border-b border-gray-100">
                  <td class="py-3"><?php echo htmlspecialchars($estudiante['codigo'] ?? ''); ?></td>
                  <td class="py-3"><?php echo htmlspecialchars(($estudiante['nombres'] ?? '') . ' ' . ($estudiante['apellidos'] ?? '')); ?></td>
                  <td class="py-3"><?php echo htmlspecialchars($estudiante['email'] ?? ''); ?></td>
                  <td class="py-3"><?php echo htmlspecialchars($estudiante['especialidad'] ?? 'No especificado'); ?></td>
                  <td class="py-3">
                    <span class="px-2 py-1 text-xs rounded-full <?php echo (($estudiante['estado'] ?? 'activo') == 'activo') ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                      <?php echo (($estudiante['estado'] ?? 'activo') == 'activo') ? 'Activo' : 'Inactivo'; ?>
                    </span>
                  </td>
                  <td class="py-3">
                    <form method="POST" class="inline">
                      <input type="hidden" name="user_id" value="<?php echo $estudiante['id']; ?>">
                      <?php if (($estudiante['estado'] ?? 'activo') == 'activo'): ?>
                        <button type="submit" name="action" value="desactivar_usuario" class="text-red-600 hover:text-red-800 mr-2">
                          <i class="fas fa-user-slash"></i>
                        </button>
                      <?php else: ?>
                        <button type="submit" name="action" value="activar_usuario" class="text-green-600 hover:text-green-800 mr-2">
                          <i class="fas fa-user-check"></i>
                        </button>
                      <?php endif; ?>
                    </form>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Tab Docentes -->
        <div id="docentes" class="tab-content hidden">
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="text-left border-b border-gray-200">
                  <th class="pb-3 font-medium text-gris">Código</th>
                  <th class="pb-3 font-medium text-gris">Nombre</th>
                  <th class="pb-3 font-medium text-gris">Email</th>
                  <th class="pb-3 font-medium text-gris">Especialidad</th>
                  <th class="pb-3 font-medium text-gris">Estado</th>
                  <th class="pb-3 font-medium text-gris">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($docentes as $docente): ?>
                <tr class="border-b border-gray-100">
                  <td class="py-3"><?php echo htmlspecialchars($docente['codigo'] ?? ''); ?></td>
                  <td class="py-3"><?php echo htmlspecialchars(($docente['nombres'] ?? '') . ' ' . ($docente['apellidos'] ?? '')); ?></td>
                  <td class="py-3"><?php echo htmlspecialchars($docente['email'] ?? ''); ?></td>
                  <td class="py-3"><?php echo htmlspecialchars($docente['especialidad'] ?? 'No especificado'); ?></td>
                  <td class="py-3">
                    <span class="px-2 py-1 text-xs rounded-full <?php echo (($docente['estado'] ?? 'activo') == 'activo') ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                      <?php echo (($docente['estado'] ?? 'activo') == 'activo') ? 'Activo' : 'Inactivo'; ?>
                    </span>
                  </td>
                  <td class="py-3">
                    <form method="POST" class="inline">
                      <input type="hidden" name="user_id" value="<?php echo $docente['id']; ?>">
                      <?php if (($docente['estado'] ?? 'activo') == 'activo'): ?>
                        <button type="submit" name="action" value="desactivar_usuario" class="text-red-600 hover:text-red-800 mr-2">
                          <i class="fas fa-user-slash"></i>
                        </button>
                      <?php else: ?>
                        <button type="submit" name="action" value="activar_usuario" class="text-green-600 hover:text-green-800 mr-2">
                          <i class="fas fa-user-check"></i>
                        </button>
                      <?php endif; ?>
                    </form>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Tab Coordinadores -->
        <div id="coordinadores" class="tab-content hidden">
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="text-left border-b border-gray-200">
                  <th class="pb-3 font-medium text-gris">Código</th>
                  <th class="pb-3 font-medium text-gris">Nombre</th>
                  <th class="pb-3 font-medium text-gris">Email</th>
                  <th class="pb-3 font-medium text-gris">Especialidad</th>
                  <th class="pb-3 font-medium text-gris">Estado</th>
                  <th class="pb-3 font-medium text-gris">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($coordinadores as $coordinador): ?>
                <tr class="border-b border-gray-100">
                  <td class="py-3"><?php echo htmlspecialchars($coordinador['codigo'] ?? ''); ?></td>
                  <td class="py-3"><?php echo htmlspecialchars(($coordinador['nombres'] ?? '') . ' ' . ($coordinador['apellidos'] ?? '')); ?></td>
                  <td class="py-3"><?php echo htmlspecialchars($coordinador['email'] ?? ''); ?></td>
                  <td class="py-3"><?php echo htmlspecialchars($coordinador['especialidad'] ?? 'No especificado'); ?></td>
                  <td class="py-3">
                    <span class="px-2 py-1 text-xs rounded-full <?php echo (($coordinador['estado'] ?? 'activo') == 'activo') ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                      <?php echo (($coordinador['estado'] ?? 'activo') == 'activo') ? 'Activo' : 'Inactivo'; ?>
                    </span>
                  </td>
                  <td class="py-3">
                    <form method="POST" class="inline">
                      <input type="hidden" name="user_id" value="<?php echo $coordinador['id']; ?>">
                      <?php if (($coordinador['estado'] ?? 'activo') == 'activo'): ?>
                        <button type="submit" name="action" value="desactivar_usuario" class="text-red-600 hover:text-red-800 mr-2">
                          <i class="fas fa-user-slash"></i>
                        </button>
                      <?php else: ?>
                        <button type="submit" name="action" value="activar_usuario" class="text-green-600 hover:text-green-800 mr-2">
                          <i class="fas fa-user-check"></i>
                        </button>
                      <?php endif; ?>
                    </form>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Tab Estadísticas -->
        <div id="estadisticas" class="tab-content hidden">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-gray-50 p-6 rounded-lg">
              <h3 class="text-lg font-bold text-texto mb-4">Resumen de Planes</h3>
              <div class="space-y-4">
                <div class="flex justify-between items-center">
                  <span class="text-gris">Total de Planes:</span>
                  <span class="font-bold text-texto"><?php echo $totalPlanes; ?></span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-gris">Planes Aprobados:</span>
                  <span class="font-bold text-green-600"><?php echo $planesAprobados; ?></span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-gris">Planes Pendientes:</span>
                  <span class="font-bold text-yellow-600"><?php echo $planesPendientes; ?></span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-gris">Planes Rechazados:</span>
                  <span class="font-bold text-red-600"><?php echo $totalPlanes - $planesAprobados - $planesPendientes; ?></span>
                </div>
              </div>
            </div>
            
            <div class="bg-gray-50 p-6 rounded-lg">
              <h3 class="text-lg font-bold text-texto mb-4">Resumen de Usuarios</h3>
              <div class="space-y-4">
                <div class="flex justify-between items-center">
                  <span class="text-gris">Total Usuarios:</span>
                  <span class="font-bold text-texto"><?php echo $totalEstudiantes + $totalDocentes + $totalCoordinadores + count($admins); ?></span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-gris">Estudiantes:</span>
                  <span class="font-bold text-blue-600"><?php echo $totalEstudiantes; ?></span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-gris">Docentes:</span>
                  <span class="font-bold text-purple-600"><?php echo $totalDocentes; ?></span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-gris">Coordinadores:</span>
                  <span class="font-bold text-orange-600"><?php echo $totalCoordinadores; ?></span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-gris">Administradores:</span>
                  <span class="font-bold text-red-600"><?php echo count($admins); ?></span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    function switchTab(tabName) {
      // Ocultar todos los contenidos
      document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
      });
      
      // Remover clase activa de todos los botones
      document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('active', 'border-principal', 'text-principal');
        button.classList.add('border-transparent', 'text-gris');
      });
      
      // Mostrar contenido seleccionado
      document.getElementById(tabName).classList.remove('hidden');
      
      // Activar botón seleccionado
      event.target.classList.add('active', 'border-principal', 'text-principal');
      event.target.classList.remove('border-transparent', 'text-gris');
    }
  </script>
</body>
</html>