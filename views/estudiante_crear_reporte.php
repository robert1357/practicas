<?php
require_once '../config/session.php';
require_once '../models/User.php';
require_once '../models/PlanPractica.php';
require_once '../models/ReporteSemanal.php';

// Verificar autenticación
if (!isAuthenticated() || !hasRole('estudiante')) {
    header("Location: login_estudiante.php");
    exit();
}

$user = getCurrentUser();
$planPractica = new PlanPractica();
$reportes = new ReporteSemanal();

// Verificar que tenga plan aprobado
$plan = $planPractica->getByEstudiante($user['id']);
if (!$plan || $plan['estado'] != 'aprobado') {
    header("Location: estudiante_dashboard.php");
    exit();
}

$semana = $_GET['semana'] ?? 1;

// Verificar que no tenga ya un reporte para esta semana
$reportesEstudiante = $reportes->getByEstudiante($user['id']);
foreach ($reportesEstudiante as $reporte) {
    if ($reporte['semana'] == $semana) {
        header("Location: estudiante_ver_reporte.php?id=" . $reporte['id']);
        exit();
    }
}

// Calcular fechas de la semana
$fechaInicio = new DateTime($plan['fecha_inicio']);
$fechaInicioSemana = clone $fechaInicio;
$fechaInicioSemana->modify('+' . (($semana - 1) * 7) . ' days');
$fechaFinSemana = clone $fechaInicioSemana;
$fechaFinSemana->modify('+6 days');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = [
        'estudiante_id' => $user['id'],
        'semana' => $semana,
        'fecha_inicio' => $fechaInicioSemana->format('Y-m-d'),
        'fecha_fin' => $fechaFinSemana->format('Y-m-d'),
        'actividades_realizadas' => $_POST['actividades_realizadas'],
        'logros_obtenidos' => $_POST['logros_obtenidos'],
        'dificultades_encontradas' => $_POST['dificultades_encontradas'],
        'horas_trabajadas' => $_POST['horas_trabajadas']
    ];
    
    if ($reportes->create($data)) {
        $success = 'Reporte semanal creado exitosamente. Será revisado por tu docente asesor.';
    } else {
        $error = 'Error al crear el reporte. Intenta nuevamente.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Crear Reporte Semanal - SYSPRE 2025</title>

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
            <i class="fas fa-file-alt mr-3 text-principal"></i>
            Crear Reporte Semanal
          </h1>
          <p class="text-gris mt-2">Semana <?php echo $semana; ?> - <?php echo htmlspecialchars($user['nombres'] . ' ' . $user['apellidos']); ?></p>
          <p class="text-sm text-gris">
            Período: <?php echo $fechaInicioSemana->format('d/m/Y'); ?> - <?php echo $fechaFinSemana->format('d/m/Y'); ?>
          </p>
        </div>
        <div class="text-right">
          <a href="estudiante_reportes_semanales.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition">
            <i class="fas fa-arrow-left mr-2"></i>Volver a Reportes
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
          <a href="estudiante_reportes_semanales.php" class="text-green-600 hover:text-green-800 underline">
            Volver a mis reportes
          </a>
        </div>
      </div>
    <?php endif; ?>

    <!-- Información de la Empresa -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
      <h2 class="text-xl font-bold text-texto mb-4 flex items-center">
        <i class="fas fa-building mr-3 text-principal"></i>
        Información de la Empresa
      </h2>
      
      <div class="grid gap-4 md:grid-cols-2">
        <div>
          <p class="text-sm text-gris">Empresa</p>
          <p class="font-semibold"><?php echo htmlspecialchars($plan['empresa']); ?></p>
        </div>
        <div>
          <p class="text-sm text-gris">Área</p>
          <p class="font-semibold"><?php echo htmlspecialchars($plan['area_practica']); ?></p>
        </div>
        <div>
          <p class="text-sm text-gris">Supervisor</p>
          <p class="font-semibold"><?php echo htmlspecialchars($plan['supervisor_empresa']); ?></p>
        </div>
        <div>
          <p class="text-sm text-gris">Horas Semanales</p>
          <p class="font-semibold"><?php echo htmlspecialchars($plan['horas_semanales']); ?> horas</p>
        </div>
      </div>
    </div>

    <!-- Formulario de Reporte -->
    <div class="bg-white rounded-lg shadow p-6">
      <h2 class="text-2xl font-bold text-texto mb-6 flex items-center">
        <i class="fas fa-edit mr-3 text-principal"></i>
        Detalles del Reporte
      </h2>
      
      <form method="POST" class="space-y-6">
        
        <!-- Actividades Realizadas -->
        <div>
          <label class="block text-sm font-medium text-gris mb-2">
            Actividades Realizadas *
          </label>
          <textarea 
            name="actividades_realizadas" 
            required 
            rows="5"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-principal focus:border-transparent"
            placeholder="Describe detalladamente las actividades que realizaste durante esta semana..."
          ></textarea>
          <p class="text-xs text-gris mt-1">
            Incluye proyectos, tareas específicas, reuniones, capacitaciones, etc.
          </p>
        </div>
        
        <!-- Logros Obtenidos -->
        <div>
          <label class="block text-sm font-medium text-gris mb-2">
            Logros Obtenidos *
          </label>
          <textarea 
            name="logros_obtenidos" 
            required 
            rows="4"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-principal focus:border-transparent"
            placeholder="Describe los logros, metas alcanzadas y resultados obtenidos..."
          ></textarea>
          <p class="text-xs text-gris mt-1">
            Menciona objetivos cumplidos, problemas resueltos, conocimientos adquiridos, etc.
          </p>
        </div>
        
        <!-- Dificultades Encontradas -->
        <div>
          <label class="block text-sm font-medium text-gris mb-2">
            Dificultades Encontradas *
          </label>
          <textarea 
            name="dificultades_encontradas" 
            required 
            rows="4"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-principal focus:border-transparent"
            placeholder="Describe las dificultades, obstáculos o desafíos que enfrentaste..."
          ></textarea>
          <p class="text-xs text-gris mt-1">
            Incluye problemas técnicos, de comunicación, organizacionales, de aprendizaje, etc.
          </p>
        </div>
        
        <!-- Horas Trabajadas -->
        <div>
          <label class="block text-sm font-medium text-gris mb-2">
            Horas Trabajadas en la Semana *
          </label>
          <div class="flex items-center space-x-4">
            <input 
              type="number" 
              name="horas_trabajadas" 
              required 
              min="1" 
              max="60"
              class="w-32 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-principal focus:border-transparent"
              placeholder="40"
            >
            <span class="text-gris">horas</span>
          </div>
          <p class="text-xs text-gris mt-1">
            Registro total de horas trabajadas durante esta semana
          </p>
        </div>
        
        <!-- Botones -->
        <div class="flex justify-end space-x-4 pt-6 border-t">
          <a href="estudiante_reportes_semanales.php" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg transition">
            <i class="fas fa-times mr-2"></i>Cancelar
          </a>
          <button 
            type="submit" 
            class="bg-principal hover:bg-green-600 text-white px-6 py-3 rounded-lg transition"
          >
            <i class="fas fa-save mr-2"></i>Guardar Reporte
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
</body>
</html>