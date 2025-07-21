<?php
require_once '../config/session.php';
require_once '../models/User.php';
require_once '../models/PlanPractica.php';
require_once '../models/ReporteSemanal.php';
require_once '../models/InformeFinal.php';
require_once '../models/AsignacionDocente.php';
require_once '../models/Sustentacion.php';

// Verificar autenticación
if (!isAuthenticated() || !hasRole('docente')) {
    header("Location: login_docente.php");
    exit();
}

$user = getCurrentUser();
$planPractica = new PlanPractica();
$reportes = new ReporteSemanal();
$informe = new InformeFinal();
$asignacion = new AsignacionDocente();
$sustentacion = new Sustentacion();

// Obtener estudiantes asignados
$estudiantesAsesor = $asignacion->getEstudiantesByDocente($user['id'], 'asesor');
$estudiantesJurado = $asignacion->getEstudiantesByDocente($user['id'], 'jurado');

// Obtener planes, reportes e informes
$planes = $planPractica->getByDocente($user['id']);
$planesPendientes = $planPractica->getPendientesDocente(); // Solo planes pendientes para docente
$reportesDocente = $reportes->getByDocente($user['id']);
$informesDocente = $informe->getByDocente($user['id']);
$sustentacionesJurado = $sustentacion->getByDocente($user['id']);
$sustentacionesPresidente = $sustentacion->getByPresidente($user['id']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard Docente - SYSPRE 2025</title>

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
<body class="bg-fondo text-texto font-sans">

  <!-- NAV -->
  <nav class="bg-white shadow-md fixed w-full top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
      <a href="../index.php" class="text-xl font-bold text-texto flex items-center gap-2">
        <i class="fas fa-chalkboard-teacher"></i> SYSPRE 2025 - Docente
      </a>
      <div class="space-x-6 hidden md:flex">
        <a href="#" class="text-principal font-semibold"><i class="fas fa-tachometer-alt mr-1"></i>Dashboard</a>
        <a href="perfil_docente.php" class="text-gris hover:text-texto transition"><i class="fas fa-user mr-1"></i>Mi Perfil</a>
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
            <i class="fas fa-chalkboard-teacher mr-3 text-principal"></i>
            Dashboard Docente
          </h1>
          <p class="text-gris mt-2"><?php echo htmlspecialchars($user['nombres'] . ' ' . $user['apellidos']); ?></p>
          <p class="text-sm text-gris"><?php echo htmlspecialchars($user['especialidad']); ?> - Período: 2025-I</p>
        </div>
        <div class="text-right">
          <div class="flex space-x-4">
            <div class="bg-principal bg-opacity-10 px-4 py-2 rounded-lg">
              <p class="text-principal text-sm font-medium">Asesorados</p>
              <p class="text-lg font-bold text-principal"><?php echo count($estudiantesAsesor); ?></p>
            </div>
            <div class="bg-blue-100 px-4 py-2 rounded-lg">
              <p class="text-blue-800 text-sm font-medium">Jurado</p>
              <p class="text-lg font-bold text-blue-800"><?php echo count($estudiantesJurado); ?></p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Estadísticas Rápidas -->
    <div class="grid gap-6 md:grid-cols-4 mb-8">
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gris">Planes Pendientes</p>
            <p class="text-2xl font-bold text-texto">
              <?php echo count(array_filter($planes, function($p) { return $p['estado'] == 'pendiente'; })); ?>
            </p>
            <p class="text-xs text-gris">
              <?php echo count(array_filter($planes, function($p) { return in_array($p['estado'], ['aprobado_docente', 'aprobado_final']); })); ?> aprobados
            </p>
          </div>
          <div class="bg-yellow-100 rounded-full p-3">
            <i class="fas fa-clock text-yellow-600 text-xl"></i>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gris">Reportes por Revisar</p>
            <p class="text-2xl font-bold text-texto">
              <?php echo count(array_filter($reportesDocente, function($r) { return $r['estado'] == 'pendiente'; })); ?>
            </p>
          </div>
          <div class="bg-blue-100 rounded-full p-3">
            <i class="fas fa-file-alt text-blue-600 text-xl"></i>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gris">Informes Pendientes</p>
            <p class="text-2xl font-bold text-texto">
              <?php echo count(array_filter($informesDocente, function($i) { return $i['estado'] == 'pendiente'; })); ?>
            </p>
            <p class="text-xs text-gris">
              <?php echo count(array_filter($informesDocente, function($i) { return $i['estado'] == 'aprobado'; })); ?> calificados
            </p>
          </div>
          <div class="bg-purple-100 rounded-full p-3">
            <i class="fas fa-file-signature text-purple-600 text-xl"></i>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gris">Sustentaciones</p>
            <p class="text-2xl font-bold text-texto">0</p>
          </div>
          <div class="bg-green-100 rounded-full p-3">
            <i class="fas fa-users text-green-600 text-xl"></i>
          </div>
        </div>
      </div>
    </div>

    <!-- Estudiantes Asesorados -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
      <h2 class="text-2xl font-bold text-texto mb-6 flex items-center">
        <i class="fas fa-user-graduate mr-3 text-principal"></i>
        Estudiantes Asesorados
      </h2>
      
      <?php if (count($estudiantesAsesor) > 0): ?>
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="bg-gray-50">
                <th class="text-left p-3 font-semibold">Estudiante</th>
                <th class="text-left p-3 font-semibold">Código</th>
                <th class="text-left p-3 font-semibold">Estado del Plan</th>
                <th class="text-left p-3 font-semibold">Estado Informe</th>
                <th class="text-left p-3 font-semibold">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($estudiantesAsesor as $estudiante): ?>
              <?php
              // Buscar el plan del estudiante
              $planEstudiante = null;
              foreach ($planes as $plan) {
                  if ($plan['estudiante_id'] == $estudiante['estudiante_id']) {
                      $planEstudiante = $plan;
                      break;
                  }
              }
              
              // Buscar el informe del estudiante
              $informeEstudiante = null;
              foreach ($informesDocente as $informe) {
                  if ($informe['estudiante_id'] == $estudiante['estudiante_id']) {
                      $informeEstudiante = $informe;
                      break;
                  }
              }
              ?>
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
                  <?php if ($planEstudiante): ?>
                    <?php
                    $estadoColor = 'bg-yellow-100 text-yellow-800';
                    $estadoTexto = 'Pendiente';
                    $estadoIcono = 'fas fa-clock';
                    
                    if ($planEstudiante['estado'] == 'aprobado_docente') {
                        $estadoColor = 'bg-green-100 text-green-800';
                        $estadoTexto = 'Aprobado por Ti';
                        $estadoIcono = 'fas fa-check-circle';
                        if (!empty($planEstudiante['nota_docente'])) {
                            $estadoTexto = 'Aprobado (' . $planEstudiante['nota_docente'] . ')';
                        } elseif (!empty($planEstudiante['calificacion_docente'])) {
                            $estadoTexto = 'Aprobado (' . $planEstudiante['calificacion_docente'] . ')';
                        }
                    } elseif ($planEstudiante['estado'] == 'aprobado_final') {
                        $estadoColor = 'bg-green-100 text-green-800';
                        $estadoTexto = 'Aprobado Final';
                        $estadoIcono = 'fas fa-trophy';
                        if (!empty($planEstudiante['nota_docente'])) {
                            $estadoTexto = 'Aprobado Final (' . $planEstudiante['nota_docente'] . ')';
                        } elseif (!empty($planEstudiante['calificacion_docente'])) {
                            $estadoTexto = 'Aprobado Final (' . $planEstudiante['calificacion_docente'] . ')';
                        }
                    } elseif ($planEstudiante['estado'] == 'rechazado') {
                        $estadoColor = 'bg-red-100 text-red-800';
                        $estadoTexto = 'Rechazado';
                        $estadoIcono = 'fas fa-times-circle';
                        if (!empty($planEstudiante['nota_docente'])) {
                            $estadoTexto = 'Rechazado (' . $planEstudiante['nota_docente'] . ')';
                        } elseif (!empty($planEstudiante['calificacion_docente'])) {
                            $estadoTexto = 'Rechazado (' . $planEstudiante['calificacion_docente'] . ')';
                        }
                    }
                    ?>
                    <span class="px-2 py-1 rounded text-xs <?php echo $estadoColor; ?>">
                      <i class="<?php echo $estadoIcono; ?> mr-1"></i>
                      <?php echo $estadoTexto; ?>
                    </span>
                  <?php else: ?>
                    <span class="px-2 py-1 rounded text-xs bg-gray-100 text-gray-800">
                      <i class="fas fa-file-slash mr-1"></i>Sin Plan
                    </span>
                  <?php endif; ?>
                </td>
                <td class="p-3">
                  <?php if ($informeEstudiante): ?>
                    <?php
                    $informeColor = 'bg-yellow-100 text-yellow-800';
                    $informeTexto = 'Pendiente';
                    $informeIcono = 'fas fa-clock';
                    
                    if ($informeEstudiante['estado'] == 'aprobado') {
                        $informeColor = 'bg-green-100 text-green-800';
                        $informeTexto = 'Aprobado por Ti';
                        $informeIcono = 'fas fa-check-circle';
                        if ($informeEstudiante['calificacion_docente']) {
                            $informeTexto = 'Aprobado (' . $informeEstudiante['calificacion_docente'] . ')';
                        }
                    } elseif ($informeEstudiante['estado'] == 'aprobado_final') {
                        $informeColor = 'bg-green-100 text-green-800';
                        $informeTexto = 'Aprobado Final';
                        $informeIcono = 'fas fa-trophy';
                        if ($informeEstudiante['calificacion_docente']) {
                            $informeTexto = 'Aprobado Final (' . $informeEstudiante['calificacion_docente'] . ')';
                        }
                    } elseif ($informeEstudiante['estado'] == 'rechazado') {
                        $informeColor = 'bg-red-100 text-red-800';
                        $informeTexto = 'Rechazado';
                        $informeIcono = 'fas fa-times-circle';
                    }
                    ?>
                    <span class="px-2 py-1 rounded text-xs <?php echo $informeColor; ?>">
                      <i class="<?php echo $informeIcono; ?> mr-1"></i>
                      <?php echo $informeTexto; ?>
                    </span>
                  <?php else: ?>
                    <span class="px-2 py-1 rounded text-xs bg-gray-100 text-gray-800">
                      <i class="fas fa-file-slash mr-1"></i>Sin Informe
                    </span>
                  <?php endif; ?>
                </td>
                <td class="p-3">
                  <div class="flex space-x-2">
                    <?php if ($planEstudiante): ?>
                      <?php if ($planEstudiante['estado'] == 'pendiente'): ?>
                        <a href="aprobar_plan.php?estudiante=<?php echo $estudiante['estudiante_id']; ?>" 
                           class="bg-orange-500 hover:bg-orange-600 text-white px-3 py-1 rounded text-sm transition">
                          <i class="fas fa-edit mr-1"></i>Revisar Plan
                        </a>
                      <?php else: ?>
                        <a href="aprobar_plan.php?estudiante=<?php echo $estudiante['estudiante_id']; ?>" 
                           class="bg-principal hover:bg-green-600 text-white px-3 py-1 rounded text-sm transition">
                          <i class="fas fa-eye mr-1"></i>Ver Plan
                        </a>
                      <?php endif; ?>
                    <?php else: ?>
                      <span class="px-3 py-1 bg-gray-100 text-gray-500 rounded text-sm">
                        <i class="fas fa-clock mr-1"></i>Esperando Plan
                      </span>
                    <?php endif; ?>
                    
                    <a href="docente_ver_reportes.php?estudiante=<?php echo $estudiante['estudiante_id']; ?>" 
                       class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm transition">
                      <i class="fas fa-file-alt mr-1"></i>Reportes
                    </a>
                    <a href="docente_calificar_informe.php?estudiante=<?php echo $estudiante['estudiante_id']; ?>" 
                       class="bg-purple-500 hover:bg-purple-600 text-white px-3 py-1 rounded text-sm transition">
                      <i class="fas fa-file-signature mr-1"></i>Informe
                    </a>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <div class="text-center py-8">
          <i class="fas fa-user-graduate text-4xl text-gray-400 mb-4"></i>
          <p class="text-gris">No tienes estudiantes asesorados aún.</p>
          <p class="text-sm text-gris">El coordinador te asignará estudiantes próximamente.</p>
        </div>
      <?php endif; ?>
    </div>

    <!-- Estudiantes como Jurado -->
    <?php if (count($estudiantesJurado) > 0): ?>
    <div class="bg-white rounded-lg shadow p-6 mb-8">
      <h2 class="text-2xl font-bold text-texto mb-6 flex items-center">
        <i class="fas fa-users mr-3 text-blue-600"></i>
        Estudiantes - Jurado
      </h2>
      
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="bg-gray-50">
              <th class="text-left p-3 font-semibold">Estudiante</th>
              <th class="text-left p-3 font-semibold">Código</th>
              <th class="text-left p-3 font-semibold">Especialidad</th>
              <th class="text-left p-3 font-semibold">Fecha Sustentación</th>
              <th class="text-left p-3 font-semibold">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($estudiantesJurado as $estudiante): ?>
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
                <span class="text-sm text-gris"><?php echo htmlspecialchars($estudiante['especialidad']); ?></span>
              </td>
              <td class="p-3">
                <span class="text-sm text-gris">Por programar</span>
              </td>
              <td class="p-3">
                <div class="flex space-x-2">
                  <a href="docente_calificar_informe.php?estudiante=<?php echo $estudiante['estudiante_id']; ?>" 
                     class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm transition">
                    <i class="fas fa-eye mr-1"></i>Ver Informe
                  </a>
                  <a href="docente_calificar_informe.php?estudiante=<?php echo $estudiante['estudiante_id']; ?>" 
                     class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm transition">
                    <i class="fas fa-star mr-1"></i>Calificar
                  </a>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>

    <!-- Sustentaciones como Jurado -->
    <?php if (!empty($sustentacionesJurado)): ?>
    <div class="bg-white rounded-lg shadow p-6 mb-8">
      <h2 class="text-2xl font-bold text-texto mb-6 flex items-center">
        <i class="fas fa-gavel mr-3 text-principal"></i>
        Sustentaciones como Jurado
      </h2>
      
      <div class="overflow-x-auto">
        <table class="w-full border-collapse">
          <thead>
            <tr class="bg-gray-50">
              <th class="text-left p-3 font-semibold">Estudiante</th>
              <th class="text-left p-3 font-semibold">Fecha</th>
              <th class="text-left p-3 font-semibold">Hora</th>
              <th class="text-left p-3 font-semibold">Lugar</th>
              <th class="text-left p-3 font-semibold">Rol en Jurado</th>
              <th class="text-left p-3 font-semibold">Estado</th>
              <th class="text-left p-3 font-semibold">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($sustentacionesJurado as $sustentacion): ?>
            <tr class="border-b hover:bg-gray-50">
              <td class="p-3">
                <div>
                  <div class="font-medium"><?php echo htmlspecialchars($sustentacion['estudiante_nombres'] . ' ' . $sustentacion['estudiante_apellidos']); ?></div>
                  <div class="text-sm text-gris">Código: <?php echo htmlspecialchars($sustentacion['estudiante_codigo']); ?></div>
                </div>
              </td>
              <td class="p-3">
                <span class="text-sm font-medium"><?php echo date('d/m/Y', strtotime($sustentacion['fecha_sustentacion'])); ?></span>
              </td>
              <td class="p-3">
                <span class="text-sm"><?php echo date('H:i', strtotime($sustentacion['hora_sustentacion'])); ?></span>
              </td>
              <td class="p-3">
                <span class="text-sm"><?php echo htmlspecialchars($sustentacion['lugar']); ?></span>
              </td>
              <td class="p-3">
                <?php 
                $rol = '';
                if ($sustentacion['presidente_jurado'] == $user['id']) {
                  $rol = '<span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs font-medium">Presidente</span>';
                } elseif ($sustentacion['vocal_jurado'] == $user['id']) {
                  $rol = '<span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-medium">Vocal</span>';
                } elseif ($sustentacion['secretario_jurado'] == $user['id']) {
                  $rol = '<span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-medium">Secretario</span>';
                }
                echo $rol;
                ?>
              </td>
              <td class="p-3">
                <?php if ($sustentacion['estado'] == 'programado'): ?>
                  <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs font-medium">Programado</span>
                <?php elseif ($sustentacion['estado'] == 'realizado'): ?>
                  <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-medium">Realizado</span>
                <?php else: ?>
                  <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-xs font-medium"><?php echo ucfirst($sustentacion['estado']); ?></span>
                <?php endif; ?>
              </td>
              <td class="p-3">
                <div class="flex space-x-2">
                  <?php if ($sustentacion['estado'] == 'programado'): ?>
                    <?php 
                    $fechaSustentacion = strtotime($sustentacion['fecha_sustentacion']);
                    $hoy = strtotime(date('Y-m-d'));
                    if ($fechaSustentacion <= $hoy + 86400): // Hoy o mañana
                    ?>
                      <span class="bg-orange-500 text-white px-3 py-1 rounded text-sm">
                        <i class="fas fa-clock mr-1"></i>Próxima
                      </span>
                    <?php else: ?>
                      <span class="bg-blue-500 text-white px-3 py-1 rounded text-sm">
                        <i class="fas fa-calendar mr-1"></i>Programada
                      </span>
                    <?php endif; ?>
                  <?php else: ?>
                    <span class="bg-gray-500 text-white px-3 py-1 rounded text-sm">
                      <i class="fas fa-check mr-1"></i>Finalizada
                    </span>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      
      <!-- Resumen de sustentaciones -->
      <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-yellow-50 p-4 rounded-lg">
          <div class="flex items-center">
            <i class="fas fa-calendar-alt text-yellow-600 mr-2"></i>
            <div>
              <p class="text-sm font-medium text-yellow-800">Programadas</p>
              <p class="text-lg font-bold text-yellow-900">
                <?php echo count(array_filter($sustentacionesJurado, function($s) { return $s['estado'] == 'programado'; })); ?>
              </p>
            </div>
          </div>
        </div>
        <div class="bg-orange-50 p-4 rounded-lg">
          <div class="flex items-center">
            <i class="fas fa-clock text-orange-600 mr-2"></i>
            <div>
              <p class="text-sm font-medium text-orange-800">Esta Semana</p>
              <p class="text-lg font-bold text-orange-900">
                <?php 
                $estaSemana = 0;
                foreach ($sustentacionesJurado as $s) {
                  $fechaSust = strtotime($s['fecha_sustentacion']);
                  $inicioSemana = strtotime('monday this week');
                  $finSemana = strtotime('sunday this week');
                  if ($fechaSust >= $inicioSemana && $fechaSust <= $finSemana) {
                    $estaSemana++;
                  }
                }
                echo $estaSemana;
                ?>
              </p>
            </div>
          </div>
        </div>
        <div class="bg-green-50 p-4 rounded-lg">
          <div class="flex items-center">
            <i class="fas fa-check-circle text-green-600 mr-2"></i>
            <div>
              <p class="text-sm font-medium text-green-800">Realizadas</p>
              <p class="text-lg font-bold text-green-900">
                <?php echo count(array_filter($sustentacionesJurado, function($s) { return $s['estado'] == 'realizado'; })); ?>
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <!-- Sustentaciones como Presidente del Jurado -->
    <?php if (!empty($sustentacionesPresidente)): ?>
    <div class="bg-white rounded-lg shadow p-6 mb-8">
      <h2 class="text-2xl font-bold text-texto mb-6 flex items-center">
        <i class="fas fa-gavel mr-3 text-red-600"></i>
        Sustentaciones como Presidente del Jurado
      </h2>
      
      <div class="overflow-x-auto">
        <table class="w-full border-collapse">
          <thead>
            <tr class="bg-gray-50">
              <th class="text-left p-3 font-semibold">Estudiante</th>
              <th class="text-left p-3 font-semibold">Fecha</th>
              <th class="text-left p-3 font-semibold">Estado</th>
              <th class="text-left p-3 font-semibold">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($sustentacionesPresidente as $sust): ?>
            <tr class="border-b hover:bg-gray-50">
              <td class="p-3">
                <div>
                  <div class="font-medium"><?php echo htmlspecialchars($sust['estudiante_nombres'] . ' ' . $sust['estudiante_apellidos']); ?></div>
                  <div class="text-sm text-gris">Código: <?php echo htmlspecialchars($sust['estudiante_codigo']); ?></div>
                </div>
              </td>
              <td class="p-3">
                <div class="text-sm">
                  <div class="font-medium"><?php echo date('d/m/Y H:i', strtotime($sust['fecha_sustentacion'] . ' ' . $sust['hora_sustentacion'])); ?></div>
                  <div class="text-gris"><?php echo htmlspecialchars($sust['lugar']); ?></div>
                </div>
              </td>
              <td class="p-3">
                <?php if ($sust['estado'] == 'programado'): ?>
                  <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs font-medium">
                    <i class="fas fa-clock mr-1"></i>Pendiente Evaluación
                  </span>
                <?php elseif ($sust['estado'] == 'aprobado'): ?>
                  <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-medium">
                    <i class="fas fa-check-circle mr-1"></i>Aprobado (<?php echo $sust['calificacion_final']; ?>)
                  </span>
                <?php elseif ($sust['estado'] == 'rechazado'): ?>
                  <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs font-medium">
                    <i class="fas fa-times-circle mr-1"></i>Rechazado
                  </span>
                <?php endif; ?>
              </td>
              <td class="p-3">
                <a href="presidente_calificar_sustentacion.php?sustentacion_id=<?php echo $sust['id']; ?>" 
                   class="<?php echo $sust['estado'] == 'programado' ? 'bg-red-500 hover:bg-red-600' : 'bg-blue-500 hover:bg-blue-600'; ?> text-white px-3 py-1 rounded text-sm transition">
                  <i class="fas fa-<?php echo $sust['estado'] == 'programado' ? 'gavel' : 'eye'; ?> mr-1"></i>
                  <?php echo $sust['estado'] == 'programado' ? 'Calificar' : 'Ver Resultado'; ?>
                </a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>

    <!-- Tareas Pendientes -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
      <h2 class="text-2xl font-bold text-texto mb-6 flex items-center">
        <i class="fas fa-tasks mr-3 text-principal"></i>
        Tareas Pendientes
      </h2>
      
      <div class="space-y-4">
        <?php
        $tareasPendientes = 0;
        
        // Planes pendientes
        foreach ($planes as $plan) {
            if ($plan['estado'] == 'pendiente' && (empty($plan['nota_docente']) || $plan['nota_docente'] === null)) {
                $tareasPendientes++;
                ?>
                <div class="flex items-center justify-between bg-yellow-50 rounded-lg p-4">
                  <div class="flex items-center">
                    <i class="fas fa-file-alt text-yellow-600 mr-3"></i>
                    <div>
                      <p class="font-medium">Revisar Plan de Prácticas</p>
                      <p class="text-sm text-gris">Estudiante: <?php echo htmlspecialchars($plan['nombres'] . ' ' . $plan['apellidos']); ?></p>
                    </div>
                  </div>
                  <a href="aprobar_plan.php?estudiante=<?php echo $plan['estudiante_id']; ?>" 
                     class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded transition">
                    <i class="fas fa-eye mr-2"></i>Revisar
                  </a>
                </div>
                <?php
            }
        }
        
        // Reportes pendientes
        foreach ($reportesDocente as $reporte) {
            if ($reporte['estado'] == 'pendiente') {
                $tareasPendientes++;
                ?>
                <div class="flex items-center justify-between bg-blue-50 rounded-lg p-4">
                  <div class="flex items-center">
                    <i class="fas fa-file-alt text-blue-600 mr-3"></i>
                    <div>
                      <p class="font-medium">Calificar Reporte Semanal</p>
                      <p class="text-sm text-gris">Estudiante: <?php echo htmlspecialchars($reporte['nombres'] . ' ' . $reporte['apellidos']); ?> - Período: <?php echo date('d/m/Y', strtotime($reporte['fecha_inicio'])); ?> al <?php echo date('d/m/Y', strtotime($reporte['fecha_fin'])); ?></p>
                    </div>
                  </div>
                  <a href="calificar_reporte.php?id=<?php echo $reporte['id']; ?>" 
                     class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded transition">
                    <i class="fas fa-star mr-2"></i>Calificar
                  </a>
                </div>
                <?php
            }
        }
        
        // Informes pendientes
        foreach ($informesDocente as $informe) {
            if ($informe['estado'] == 'pendiente') {
                $tareasPendientes++;
                ?>
                <div class="flex items-center justify-between bg-purple-50 rounded-lg p-4">
                  <div class="flex items-center">
                    <i class="fas fa-file-signature text-purple-600 mr-3"></i>
                    <div>
                      <p class="font-medium">Calificar Informe Final</p>
                      <p class="text-sm text-gris">Estudiante: <?php echo htmlspecialchars($informe['nombres'] . ' ' . $informe['apellidos']); ?></p>
                    </div>
                  </div>
                  <a href="docente_calificar_informe.php?estudiante=<?php echo $informe['estudiante_id']; ?>" 
                     class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded transition">
                    <i class="fas fa-star mr-2"></i>Calificar
                  </a>
                </div>
                <?php
            }
        }
        
        if ($tareasPendientes == 0) {
            echo '<div class="text-center py-8">
                    <i class="fas fa-check-circle text-4xl text-green-400 mb-4"></i>
                    <p class="text-gris">¡Excelente! No tienes tareas pendientes.</p>
                  </div>';
        }
        ?>
      </div>
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