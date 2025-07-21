<?php
require_once '../config/session.php';
require_once '../models/User.php';
require_once '../models/PlanPractica.php';
require_once '../models/ReporteSemanal.php';
require_once '../models/InformeFinal.php';
require_once '../models/AsignacionDocente.php';
require_once '../models/DocumentoReglamento.php';
require_once '../models/Sustentacion.php';

// Verificar autenticación
if (!isAuthenticated() || !hasRole('estudiante')) {
    header("Location: login_estudiante.php");
    exit();
}

$user = getCurrentUser();
$planPractica = new PlanPractica();
$reportes = new ReporteSemanal();
$informe = new InformeFinal();
$asignacion = new AsignacionDocente();
$documentoModel = new DocumentoReglamento();
$sustentacion = new Sustentacion();
$userModel = new User();

// Obtener el plan del estudiante
$plan = $planPractica->getByEstudiante($user['id']);
$docenteAsesor = $asignacion->getDocenteAsesor($user['id']);
$reportesEstudiante = $reportes->getByEstudiante($user['id']);
$informeEstudiante = $informe->getByEstudiante($user['id']);
$documentosEspecialidad = $documentoModel->getByEspecialidad($user['especialidad']);
$sustentacionEstudiante = $sustentacion->getByEstudiante($user['id']);

// Obtener coordinador de la especialidad
$coordinadoresEspecialidad = $userModel->getByType('coordinador');
$coordinador = null;
foreach ($coordinadoresEspecialidad as $coord) {
    if ($coord['especialidad'] == $user['especialidad']) {
        $coordinador = $coord;
        break;
    }
}

// Determinar el estado actual del proceso
$pasoActual = 1;
$estadoGlobal = 'Inicio';

// Verificar primero si la sustentación está aprobada (estado final)
if ($sustentacionEstudiante && $sustentacionEstudiante['estado'] == 'aprobado') {
    $pasoActual = 7;
    $estadoGlobal = 'Proceso Completado';
} elseif ($sustentacionEstudiante && in_array($sustentacionEstudiante['estado'], ['programada', 'programado'])) {
    $pasoActual = 6;
    $estadoGlobal = 'Sustentación Programada';
} elseif ($informeEstudiante) {
    if ($informeEstudiante['estado'] == 'aprobado_final') {
        $pasoActual = 6;
        $estadoGlobal = 'Listo para Sustentación';
    } elseif ($informeEstudiante['estado'] == 'aprobado') {
        $pasoActual = 5;
        $estadoGlobal = 'Informe - Pendiente Coordinador';
    } elseif ($informeEstudiante['estado'] == 'rechazado') {
        $pasoActual = 5;
        $estadoGlobal = 'Informe Rechazado';
    } else {
        $pasoActual = 5;
        $estadoGlobal = 'Informe - Pendiente Docente';
    }
} elseif ($plan) {
    if ($plan['estado'] == 'aprobado_final') {
        $pasoActual = 4;
        $estadoGlobal = 'Prácticas en Proceso';
    } elseif ($plan['estado'] == 'aprobado_docente') {
        $pasoActual = 3;
        $estadoGlobal = 'Pendiente Coordinador';
    } elseif ($plan['estado'] == 'aprobado') {
        $pasoActual = 4;
        $estadoGlobal = 'Prácticas en Proceso';
    } elseif ($plan['estado'] == 'rechazado') {
        $pasoActual = 1;
        $estadoGlobal = 'Plan Rechazado';
    } elseif ($plan['estado'] == 'pendiente') {
        $pasoActual = 2;
        $estadoGlobal = 'Pendiente Docente';
    } else {
        $pasoActual = 2;
        $estadoGlobal = 'Plan Enviado';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard Estudiante - SYSPRE 2025</title>

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
      <a href="../index.php" class="text-xl font-bold text-texto flex items-center gap-2">
        <i class="fas fa-user-graduate"></i> SYSPRE 2025 - Estudiante
      </a>
      <div class="space-x-6 hidden md:flex">
        <a href="#" class="text-principal font-semibold"><i class="fas fa-tachometer-alt mr-1"></i>Dashboard</a>
        <a href="estudiante_reportes_semanales.php" class="text-gris hover:text-texto transition"><i class="fas fa-file-text mr-1"></i>Reportes</a>
        <a href="estudiante_ver_informe_simple.php" class="text-gris hover:text-texto transition"><i class="fas fa-file-signature mr-1"></i>Documentos Firmados</a>
        <a href="perfil_estudiante.php" class="text-gris hover:text-texto transition"><i class="fas fa-user mr-1"></i>Mi Perfil</a>
        <a href="../logout.php" class="text-gris hover:text-texto transition"><i class="fas fa-sign-out-alt mr-1"></i>Cerrar Sesión</a>
      </div>
    </div>
  </nav>

  <main class="pt-24 max-w-7xl mx-auto px-4">
    <!-- Header personalizado del estudiante -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-texto flex items-center">
            <i class="fas fa-graduation-cap mr-3 text-principal"></i>
            Gestión de Prácticas Pre-Profesionales
          </h1>
          <p class="text-gris mt-2"><?php echo htmlspecialchars($user['nombres'] . ' ' . $user['apellidos']); ?> - Código: <?php echo htmlspecialchars($user['codigo']); ?></p>
          <p class="text-sm text-gris"><?php echo htmlspecialchars($user['especialidad'] ?? 'Especialidad'); ?> - Período: 2025-I</p>
        </div>
        <div class="text-right">
          <div class="bg-principal bg-opacity-10 px-4 py-2 rounded-lg">
            <p class="text-principal text-sm font-medium">Estado Actual</p>
            <p class="text-lg font-bold text-principal"><?php echo $estadoGlobal; ?></p>
          </div>
        </div>
      </div>
    </div>

    <!-- PROCESO DE PRÁCTICAS PRE-PROFESIONALES -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-texto flex items-center">
          <i class="fas fa-route mr-3 text-principal"></i>
          Proceso de Prácticas Pre-Profesionales
        </h2>
        <div class="text-sm text-gris">
          Paso <?php echo $pasoActual; ?> de 7
        </div>
      </div>
      
      <!-- Barra de progreso -->
      <div class="mb-8">
        <div class="flex justify-between items-center mb-2">
          <span class="text-sm font-medium text-gris">Progreso General</span>
          <span class="text-sm font-medium text-principal">
            <?php echo round(($pasoActual / 7) * 100); ?>%
          </span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2">
          <div class="bg-principal h-2 rounded-full transition-all duration-300" 
               style="width: <?php echo round(($pasoActual / 7) * 100); ?>%"></div>
        </div>
      </div>

      <!-- TIMELINE DE PASOS -->
      <div class="space-y-6">
        
        <!-- PASO 1: Registro del Plan -->
        <div class="flex items-start space-x-4">
          <div class="flex-shrink-0">
            <div class="w-10 h-10 <?php echo $plan ? 'bg-green-500' : 'bg-principal'; ?> rounded-full flex items-center justify-center text-white font-bold">
              <?php echo $plan ? '✓' : '1'; ?>
            </div>
          </div>
          <div class="flex-1 min-w-0">
            <div class="bg-gray-50 rounded-lg p-4 border-l-4 <?php echo $plan ? 'border-green-500' : 'border-principal'; ?>">
              <h3 class="text-lg font-semibold text-texto mb-2">
                <i class="fas fa-file-alt mr-2"></i>Registro de Plan de Prácticas
              </h3>
              <p class="text-sm text-gris mb-3">Completa y envía tu plan de prácticas pre-profesionales</p>
              <div class="flex space-x-2">
                <?php if ($plan): ?>
                  <a href="estudiante_ver_plan.php" class="inline-flex items-center px-3 py-2 bg-green-500 text-white text-sm rounded hover:bg-green-600 transition">
                    <i class="fas fa-eye mr-2"></i>Ver Plan
                  </a>
                <?php else: ?>
                  <a href="estudiante_registro_plan.php" class="inline-flex items-center px-3 py-2 bg-principal text-white text-sm rounded hover:bg-green-600 transition">
                    <i class="fas fa-plus mr-2"></i>Registrar Plan
                  </a>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>

        <!-- PASO 2: Aprobación Docente -->
        <div class="flex items-start space-x-4">
          <div class="flex-shrink-0">
            <div class="w-10 h-10 <?php 
              if ($plan && in_array($plan['estado'], ['aprobado_docente', 'aprobado_final', 'aprobado'])) {
                echo 'bg-green-500';
              } elseif ($plan && $plan['estado'] == 'rechazado') {
                echo 'bg-red-500';
              } elseif ($plan && $plan['estado'] == 'pendiente') {
                echo 'bg-yellow-500';
              } else {
                echo 'bg-gray-400';
              }
            ?> rounded-full flex items-center justify-center text-white font-bold">
              <?php 
              if ($plan && in_array($plan['estado'], ['aprobado_docente', 'aprobado_final', 'aprobado'])) {
                echo '✓';
              } elseif ($plan && $plan['estado'] == 'rechazado') {
                echo '✗';
              } elseif ($plan && $plan['estado'] == 'pendiente') {
                echo '⏳';
              } else {
                echo '2';
              }
              ?>
            </div>
          </div>
          <div class="flex-1 min-w-0">
            <div class="bg-gray-50 rounded-lg p-4 border-l-4 <?php 
              if ($plan && in_array($plan['estado'], ['aprobado_docente', 'aprobado_final', 'aprobado'])) {
                echo 'border-green-500';
              } elseif ($plan && $plan['estado'] == 'rechazado') {
                echo 'border-red-500';
              } elseif ($plan && $plan['estado'] == 'pendiente') {
                echo 'border-yellow-500';
              } else {
                echo 'border-gray-400';
              }
            ?>">
              <h3 class="text-lg font-semibold text-texto mb-2">
                <i class="fas fa-user-check mr-2"></i>Aprobación del Docente Asesor
              </h3>
              <p class="text-sm text-gris mb-3">Tu docente asesor revisa y aprueba el plan de prácticas</p>
              <div class="flex space-x-2">
                <?php if ($plan && in_array($plan['estado'], ['aprobado_docente', 'aprobado_final', 'aprobado'])): ?>
                  <span class="inline-flex items-center px-3 py-2 bg-green-500 text-white text-sm rounded">
                    <i class="fas fa-check mr-2"></i>Aprobado por Docente
                  </span>
                <?php elseif ($plan && $plan['estado'] == 'rechazado'): ?>
                  <span class="inline-flex items-center px-3 py-2 bg-red-500 text-white text-sm rounded">
                    <i class="fas fa-times mr-2"></i>Plan Rechazado
                  </span>
                <?php elseif ($plan && $plan['estado'] == 'pendiente'): ?>
                  <span class="inline-flex items-center px-3 py-2 bg-yellow-500 text-white text-sm rounded">
                    <i class="fas fa-clock mr-2"></i>Pendiente de Revisión
                  </span>
                <?php else: ?>
                  <span class="inline-flex items-center px-3 py-2 bg-gray-400 text-white text-sm rounded">
                    <i class="fas fa-clock mr-2"></i>Esperando Plan
                  </span>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>

        <!-- PASO 3: Aprobación Coordinador -->
        <div class="flex items-start space-x-4">
          <div class="flex-shrink-0">
            <div class="w-10 h-10 <?php 
              if ($plan && in_array($plan['estado'], ['aprobado_final', 'aprobado'])) {
                echo 'bg-green-500';
              } elseif ($plan && $plan['estado'] == 'aprobado_docente') {
                echo 'bg-yellow-500';
              } else {
                echo 'bg-gray-400';
              }
            ?> rounded-full flex items-center justify-center text-white font-bold">
              <?php 
              if ($plan && in_array($plan['estado'], ['aprobado_final', 'aprobado'])) {
                echo '✓';
              } elseif ($plan && $plan['estado'] == 'aprobado_docente') {
                echo '⏳';
              } else {
                echo '3';
              }
              ?>
            </div>
          </div>
          <div class="flex-1 min-w-0">
            <div class="bg-gray-50 rounded-lg p-4 border-l-4 <?php 
              if ($plan && in_array($plan['estado'], ['aprobado_final', 'aprobado'])) {
                echo 'border-green-500';
              } elseif ($plan && $plan['estado'] == 'aprobado_docente') {
                echo 'border-yellow-500';
              } else {
                echo 'border-gray-400';
              }
            ?>">
              <h3 class="text-lg font-semibold text-texto mb-2">
                <i class="fas fa-user-tie mr-2"></i>Aprobación del Coordinador
              </h3>
              <p class="text-sm text-gris mb-3">El coordinador revisa y aprueba finalmente el plan</p>
              <div class="flex space-x-2">
                <?php if ($plan && in_array($plan['estado'], ['aprobado_final', 'aprobado'])): ?>
                  <span class="inline-flex items-center px-3 py-2 bg-green-500 text-white text-sm rounded">
                    <i class="fas fa-check mr-2"></i>Aprobado por Coordinador
                  </span>
                <?php elseif ($plan && $plan['estado'] == 'aprobado_docente'): ?>
                  <span class="inline-flex items-center px-3 py-2 bg-yellow-500 text-white text-sm rounded">
                    <i class="fas fa-clock mr-2"></i>Pendiente de Coordinador
                  </span>
                <?php else: ?>
                  <span class="inline-flex items-center px-3 py-2 bg-gray-400 text-white text-sm rounded">
                    <i class="fas fa-clock mr-2"></i>Esperando Aprobación
                  </span>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>

        <!-- PASO 4: Reportes Semanales -->
        <div class="flex items-start space-x-4">
          <div class="flex-shrink-0">
            <div class="w-10 h-10 <?php 
              if ($informeEstudiante && $informeEstudiante['estado'] == 'aprobado_final') {
                echo 'bg-green-500';
              } elseif ($plan && in_array($plan['estado'], ['aprobado_final', 'aprobado'])) {
                echo 'bg-yellow-500';
              } else {
                echo 'bg-gray-400';
              }
            ?> rounded-full flex items-center justify-center text-white font-bold">
              <?php 
              if ($informeEstudiante && $informeEstudiante['estado'] == 'aprobado_final') {
                echo '✓';
              } elseif ($plan && in_array($plan['estado'], ['aprobado_final', 'aprobado'])) {
                echo '📝';
              } else {
                echo '4';
              }
              ?>
            </div>
          </div>
          <div class="flex-1 min-w-0">
            <div class="bg-gray-50 rounded-lg p-4 border-l-4 <?php 
              if ($informeEstudiante && $informeEstudiante['estado'] == 'aprobado_final') {
                echo 'border-green-500';
              } elseif ($plan && in_array($plan['estado'], ['aprobado_final', 'aprobado'])) {
                echo 'border-yellow-500';
              } else {
                echo 'border-gray-400';
              }
            ?>">
              <h3 class="text-lg font-semibold text-texto mb-2">
                <i class="fas fa-file-alt mr-2"></i>Reportes Semanales
              </h3>
              <p class="text-sm text-gris mb-3">Envía reportes semanales de tus actividades durante las prácticas</p>
              <div class="flex space-x-2">
                <?php if ($informeEstudiante && $informeEstudiante['estado'] == 'aprobado_final'): ?>
                  <span class="inline-flex items-center px-3 py-2 bg-green-500 text-white text-sm rounded">
                    <i class="fas fa-check mr-2"></i>Reportes Completados
                  </span>
                <?php elseif ($plan && in_array($plan['estado'], ['aprobado_final', 'aprobado'])): ?>
                  <a href="estudiante_reportes_semanales.php" class="inline-flex items-center px-3 py-2 bg-yellow-500 text-white text-sm rounded hover:bg-yellow-600 transition">
                    <i class="fas fa-file-alt mr-2"></i>Crear Reportes
                  </a>
                <?php else: ?>
                  <span class="inline-flex items-center px-3 py-2 bg-gray-400 text-white text-sm rounded">
                    <i class="fas fa-file-alt mr-2"></i>Esperando Aprobación
                  </span>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>

        <!-- PASO 5: Informe Final -->
        <div class="flex items-start space-x-4">
          <div class="flex-shrink-0">
            <div class="w-10 h-10 <?php 
              if ($informeEstudiante && $informeEstudiante['estado'] == 'aprobado_final') {
                echo 'bg-green-500';
              } elseif ($informeEstudiante && $informeEstudiante['estado'] == 'aprobado') {
                echo 'bg-yellow-500';
              } elseif ($informeEstudiante && $informeEstudiante['estado'] == 'rechazado') {
                echo 'bg-red-500';
              } elseif ($informeEstudiante) {
                echo 'bg-blue-500';
              } elseif ($plan && in_array($plan['estado'], ['aprobado_final', 'aprobado'])) {
                echo 'bg-yellow-500';
              } else {
                echo 'bg-gray-400';
              }
            ?> rounded-full flex items-center justify-center text-white font-bold">
              <?php 
              if ($informeEstudiante && $informeEstudiante['estado'] == 'aprobado_final') {
                echo '✓';
              } elseif ($informeEstudiante && $informeEstudiante['estado'] == 'aprobado') {
                echo '⏳';
              } elseif ($informeEstudiante && $informeEstudiante['estado'] == 'rechazado') {
                echo '✗';
              } elseif ($informeEstudiante) {
                echo '📝';
              } elseif ($plan && in_array($plan['estado'], ['aprobado_final', 'aprobado'])) {
                echo '📋';
              } else {
                echo '5';
              }
              ?>
            </div>
          </div>
          <div class="flex-1 min-w-0">
            <div class="bg-gray-50 rounded-lg p-4 border-l-4 <?php 
              if ($informeEstudiante && $informeEstudiante['estado'] == 'aprobado_final') {
                echo 'border-green-500';
              } elseif ($informeEstudiante && $informeEstudiante['estado'] == 'aprobado') {
                echo 'border-yellow-500';
              } elseif ($informeEstudiante && $informeEstudiante['estado'] == 'rechazado') {
                echo 'border-red-500';
              } elseif ($informeEstudiante) {
                echo 'border-blue-500';
              } elseif ($plan && in_array($plan['estado'], ['aprobado_final', 'aprobado'])) {
                echo 'border-yellow-500';
              } else {
                echo 'border-gray-400';
              }
            ?>">
              <h3 class="text-lg font-semibold text-texto mb-2">
                <i class="fas fa-file-signature mr-2"></i>Informe Final
              </h3>
              <p class="text-sm text-gris mb-3">Elabora, envía y espera la aprobación del informe final</p>
              <div class="flex space-x-2">
                <?php if ($informeEstudiante): ?>
                  <?php if ($informeEstudiante['estado'] == 'aprobado_final'): ?>
                    <a href="estudiante_ver_informe_simple.php" class="inline-flex items-center px-3 py-2 bg-green-500 text-white text-sm rounded hover:bg-green-600 transition">
                      <i class="fas fa-check-double mr-2"></i>Ver Informe Aprobado
                    </a>
                  <?php elseif ($informeEstudiante['estado'] == 'aprobado'): ?>
                    <span class="inline-flex items-center px-3 py-2 bg-green-500 text-white text-sm rounded">
                      <i class="fas fa-check mr-2"></i>Aprobado por Docente
                    </span>
                    <span class="inline-flex items-center px-3 py-2 bg-yellow-500 text-white text-sm rounded">
                      <i class="fas fa-clock mr-2"></i>Pendiente Coordinador
                    </span>
                  <?php elseif ($informeEstudiante['estado'] == 'rechazado'): ?>
                    <a href="registro_informe_final.php" class="inline-flex items-center px-3 py-2 bg-red-500 text-white text-sm rounded hover:bg-red-600 transition">
                      <i class="fas fa-edit mr-2"></i>Revisar y Corregir
                    </a>
                  <?php else: ?>
                    <span class="inline-flex items-center px-3 py-2 bg-blue-500 text-white text-sm rounded">
                      <i class="fas fa-clock mr-2"></i>Esperando Revisión
                    </span>
                  <?php endif; ?>
                <?php elseif ($plan && in_array($plan['estado'], ['aprobado_final', 'aprobado'])): ?>
                  <a href="registro_informe_final.php" class="inline-flex items-center px-3 py-2 bg-yellow-500 text-white text-sm rounded hover:bg-yellow-600 transition">
                    <i class="fas fa-file-signature mr-2"></i>Crear Informe Final
                  </a>
                <?php else: ?>
                  <span class="inline-flex items-center px-3 py-2 bg-gray-400 text-white text-sm rounded">
                    <i class="fas fa-file-signature mr-2"></i>Esperando Reportes
                  </span>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>

        <!-- PASO 6: Sustentación -->
        <div class="flex items-start space-x-4">
          <div class="flex-shrink-0">
            <div class="w-10 h-10 <?php 
              if ($sustentacionEstudiante && $sustentacionEstudiante['estado'] == 'aprobado') {
                echo 'bg-green-500';
              } elseif ($sustentacionEstudiante && in_array($sustentacionEstudiante['estado'], ['programada', 'programado'])) {
                echo 'bg-blue-500';
              } elseif ($informeEstudiante && $informeEstudiante['estado'] == 'aprobado_final') {
                echo 'bg-yellow-500';
              } else {
                echo 'bg-gray-400';
              }
            ?> rounded-full flex items-center justify-center text-white font-bold">
              <?php 
              if ($sustentacionEstudiante && $sustentacionEstudiante['estado'] == 'aprobado') {
                echo '✓';
              } elseif ($sustentacionEstudiante && in_array($sustentacionEstudiante['estado'], ['programada', 'programado'])) {
                echo '🎓';
              } elseif ($informeEstudiante && $informeEstudiante['estado'] == 'aprobado_final') {
                echo '⏳';
              } else {
                echo '6';
              }
              ?>
            </div>
          </div>
          <div class="flex-1 min-w-0">
            <div class="bg-gray-50 rounded-lg p-4 border-l-4 <?php 
              if ($sustentacionEstudiante && $sustentacionEstudiante['estado'] == 'aprobado') {
                echo 'border-green-500';
              } elseif ($sustentacionEstudiante && in_array($sustentacionEstudiante['estado'], ['programada', 'programado'])) {
                echo 'border-blue-500';
              } elseif ($informeEstudiante && $informeEstudiante['estado'] == 'aprobado_final') {
                echo 'border-yellow-500';
              } else {
                echo 'border-gray-400';
              }
            ?>">
              <h3 class="text-lg font-semibold text-texto mb-2">
                <i class="fas fa-graduation-cap mr-2"></i>Sustentación
              </h3>
              <p class="text-sm text-gris mb-3">Sustenta tu informe ante el jurado asignado</p>
              <div class="flex space-x-2">
                <?php if ($sustentacionEstudiante && $sustentacionEstudiante['estado'] == 'aprobado'): ?>
                  <span class="inline-flex items-center px-3 py-2 bg-green-500 text-white text-sm rounded">
                    <i class="fas fa-check-circle mr-2"></i>Sustentación Aprobada
                  </span>
                <?php elseif ($sustentacionEstudiante && in_array($sustentacionEstudiante['estado'], ['programada', 'programado'])): ?>
                  <span class="inline-flex items-center px-3 py-2 bg-blue-500 text-white text-sm rounded">
                    <i class="fas fa-calendar-check mr-2"></i>Sustentación Programada
                  </span>
                  <span class="inline-flex items-center px-3 py-2 bg-blue-100 text-blue-800 text-sm rounded">
                    <i class="fas fa-map-marker-alt mr-2"></i><?php echo htmlspecialchars($sustentacionEstudiante['lugar']); ?>
                  </span>
                <?php elseif ($informeEstudiante && $informeEstudiante['estado'] == 'aprobado_final'): ?>
                  <span class="inline-flex items-center px-3 py-2 bg-yellow-500 text-white text-sm rounded">
                    <i class="fas fa-clock mr-2"></i>Esperando Programación
                  </span>
                <?php else: ?>
                  <span class="inline-flex items-center px-3 py-2 bg-gray-400 text-white text-sm rounded">
                    <i class="fas fa-clock mr-2"></i>Pendiente
                  </span>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>

        <!-- PASO 7: Certificación -->
        <div class="flex items-start space-x-4">
          <div class="flex-shrink-0">
            <div class="w-10 h-10 <?php 
              if ($sustentacionEstudiante && $sustentacionEstudiante['estado'] == 'aprobado') {
                echo 'bg-green-500';
              } else {
                echo 'bg-gray-400';
              }
            ?> rounded-full flex items-center justify-center text-white font-bold">
              <?php 
              if ($sustentacionEstudiante && $sustentacionEstudiante['estado'] == 'aprobado') {
                echo '✓';
              } else {
                echo '7';
              }
              ?>
            </div>
          </div>
          <div class="flex-1 min-w-0">
            <div class="bg-gray-50 rounded-lg p-4 border-l-4 <?php 
              if ($sustentacionEstudiante && $sustentacionEstudiante['estado'] == 'aprobado') {
                echo 'border-green-500';
              } else {
                echo 'border-gray-400';
              }
            ?>">
              <h3 class="text-lg font-semibold text-texto mb-2">
                <i class="fas fa-certificate mr-2"></i>Certificación
              </h3>
              <p class="text-sm text-gris mb-3">Recibe tu certificado de prácticas pre-profesionales</p>
              <div class="flex space-x-2">
                <?php if ($sustentacionEstudiante && $sustentacionEstudiante['estado'] == 'aprobado'): ?>
                  <span class="inline-flex items-center px-3 py-2 bg-green-500 text-white text-sm rounded">
                    <i class="fas fa-certificate mr-2"></i>Proceso Completado
                  </span>
                <?php else: ?>
                  <span class="inline-flex items-center px-3 py-2 bg-gray-400 text-white text-sm rounded">
                    <i class="fas fa-clock mr-2"></i>Pendiente
                  </span>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- Información adicional cuando la sustentación está completada -->
    <?php if ($sustentacionEstudiante && $sustentacionEstudiante['estado'] == 'aprobado'): ?>
    <div class="bg-green-50 border border-green-200 rounded-lg p-6 mb-8">
      <div class="flex items-center mb-4">
        <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center text-white">
          <i class="fas fa-check-circle text-xl"></i>
        </div>
        <div class="ml-4">
          <h3 class="text-lg font-semibold text-green-800">¡Sustentación Aprobada!</h3>
          <p class="text-sm text-green-600">Has completado exitosamente tus prácticas pre-profesionales</p>
        </div>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg p-4 border border-green-200">
          <h4 class="font-semibold text-green-800 mb-2">
            <i class="fas fa-star mr-2"></i>Resultado de la Sustentación
          </h4>
          <div class="text-sm space-y-1">
            <div><strong>Calificación Final:</strong> <?php echo htmlspecialchars($sustentacionEstudiante['calificacion_final']); ?></div>
            <div><strong>Fecha de Aprobación:</strong> <?php echo date('d/m/Y', strtotime($sustentacionEstudiante['fecha_aprobacion'])); ?></div>
          </div>
        </div>
        
        <div class="bg-white rounded-lg p-4 border border-green-200">
          <h4 class="font-semibold text-green-800 mb-2">
            <i class="fas fa-calendar-alt mr-2"></i>Detalles de la Sustentación
          </h4>
          <div class="text-sm space-y-1">
            <div><strong>Fecha:</strong> <?php echo date('d/m/Y', strtotime($sustentacionEstudiante['fecha_sustentacion'])); ?></div>
            <div><strong>Hora:</strong> <?php echo date('H:i', strtotime($sustentacionEstudiante['hora_sustentacion'])); ?></div>
            <div><strong>Lugar:</strong> <?php echo htmlspecialchars($sustentacionEstudiante['lugar']); ?></div>
          </div>
        </div>
      </div>
      
      <?php if ($sustentacionEstudiante['presidente_jurado'] || $sustentacionEstudiante['vocal_jurado'] || $sustentacionEstudiante['secretario_jurado']): ?>
      <div class="bg-white rounded-lg p-4 border border-green-200 mt-4">
        <h4 class="font-semibold text-green-800 mb-2">
          <i class="fas fa-users mr-2"></i>Jurado Evaluador
        </h4>
        <div class="text-sm space-y-1">
          <?php if ($sustentacionEstudiante['presidente_jurado']): ?>
          <div><strong>Presidente:</strong> <?php echo htmlspecialchars($sustentacionEstudiante['presidente_nombre']); ?></div>
          <?php endif; ?>
          <?php if ($sustentacionEstudiante['vocal_jurado']): ?>
          <div><strong>Vocal:</strong> <?php echo htmlspecialchars($sustentacionEstudiante['vocal_nombre']); ?></div>
          <?php endif; ?>
          <?php if ($sustentacionEstudiante['secretario_jurado']): ?>
          <div><strong>Secretario:</strong> <?php echo htmlspecialchars($sustentacionEstudiante['secretario_nombre']); ?></div>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Estadísticas del Progreso -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
      <h2 class="text-2xl font-bold text-texto mb-6 flex items-center">
        <i class="fas fa-chart-line mr-3 text-principal"></i>
        Estadísticas de Progreso
      </h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-blue-50 p-4 rounded-lg text-center">
          <div class="text-3xl font-bold text-blue-700"><?php echo count($reportesEstudiante); ?></div>
          <div class="text-sm text-blue-600">Reportes Semanales</div>
        </div>
        <div class="bg-green-50 p-4 rounded-lg text-center">
          <div class="text-3xl font-bold text-green-700"><?php echo $informeEstudiante ? '1' : '0'; ?></div>
          <div class="text-sm text-green-600">Informe Final</div>
        </div>
        <div class="bg-purple-50 p-4 rounded-lg text-center">
          <div class="text-3xl font-bold text-purple-700"><?php echo $sustentacionEstudiante ? '1' : '0'; ?></div>
          <div class="text-sm text-purple-600">Sustentación</div>
        </div>
      </div>
    </div>

    <!-- Documentos de la Especialidad -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
      <h2 class="text-2xl font-bold text-texto mb-4 flex items-center">
        <i class="fas fa-folder-open mr-3 text-principal"></i>
        Documentos de <?php echo htmlspecialchars($user['especialidad'] ?? 'la Especialidad'); ?>
      </h2>
      <p class="text-gris mb-4">Reglamentos y documentos importantes para las prácticas pre-profesionales</p>
      
      <?php if (empty($documentosEspecialidad)): ?>
        <div class="text-center py-8">
          <i class="fas fa-folder-open text-4xl text-gray-300 mb-4"></i>
          <p class="text-gris">No hay documentos disponibles para tu especialidad</p>
          <p class="text-sm text-gray-500">Tu coordinador puede subir documentos desde su panel</p>
        </div>
      <?php else: ?>
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
          <?php foreach ($documentosEspecialidad as $documento): ?>
            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
              <div class="flex items-center mb-2">
                <?php 
                $extension = pathinfo($documento['archivo_url'], PATHINFO_EXTENSION);
                $iconClass = 'fas fa-file-alt text-gray-500';
                if (in_array(strtolower($extension), ['pdf'])) {
                  $iconClass = 'fas fa-file-pdf text-red-500';
                } elseif (in_array(strtolower($extension), ['doc', 'docx'])) {
                  $iconClass = 'fas fa-file-word text-blue-500';
                }
                ?>
                <i class="<?php echo $iconClass; ?> mr-2"></i>
                <span class="font-semibold"><?php echo htmlspecialchars($documento['titulo']); ?></span>
              </div>
              <p class="text-sm text-gris mb-3"><?php echo htmlspecialchars($documento['descripcion']); ?></p>
              <div class="flex justify-between items-center">
                <?php if ($documento['archivo_url']): ?>
                  <a href="../download_file.php?file=<?php echo urlencode($documento['archivo_url']); ?>&doc_id=<?php echo $documento['id']; ?>" 
                     target="_blank" 
                     class="text-principal hover:text-green-600 text-sm">
                    <i class="fas fa-download mr-1"></i>Descargar
                  </a>
                <?php else: ?>
                  <span class="text-gray-400 text-sm">Sin archivo</span>
                <?php endif; ?>
                <span class="px-2 py-1 rounded-full text-xs font-medium
                  <?php 
                  switch($documento['tipo_documento']) {
                    case 'reglamento': echo 'bg-blue-100 text-blue-800'; break;
                    case 'formato': echo 'bg-green-100 text-green-800'; break;
                    case 'guia': echo 'bg-yellow-100 text-yellow-800'; break;
                    default: echo 'bg-gray-100 text-gray-800';
                  }
                  ?>">
                  <?php echo ucfirst($documento['tipo_documento']); ?>
                </span>
              </div>
              <div class="text-xs text-gray-500 mt-2">
                <i class="fas fa-calendar mr-1"></i>
                <?php echo date('d/m/Y', strtotime($documento['fecha_creacion'])); ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- Documentos Firmados -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
      <h2 class="text-2xl font-bold text-texto mb-4 flex items-center">
        <i class="fas fa-certificate mr-3 text-green-600"></i>
        Documentos Firmados
      </h2>
      <p class="text-gris mb-4">Documentos oficiales firmados digitalmente</p>
      
      <div class="grid gap-4 md:grid-cols-2">
        <!-- Plan de Prácticas Firmado -->
        <?php if ($plan && $plan['estado'] == 'aprobado_final' && !empty($plan['archivo_plan_firmado'])): ?>
          <div class="border border-green-200 rounded-lg p-4 hover:shadow-md transition bg-green-50">
            <div class="flex items-center mb-2">
              <i class="fas fa-file-signature text-green-600 mr-2"></i>
              <span class="font-semibold">Plan de Prácticas Firmado</span>
            </div>
            <p class="text-sm text-gris mb-3">Plan de prácticas aprobado y firmado por el coordinador</p>
            <div class="flex justify-between items-center">
              <a href="../download_plan_firmado.php?id=<?php echo $plan['id']; ?>" 
                 target="_blank" 
                 class="text-green-600 hover:text-green-800 text-sm font-medium">
                <i class="fas fa-download mr-1"></i>Descargar
              </a>
              <span class="px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                Plan Firmado
              </span>
            </div>
            <div class="text-xs text-gray-500 mt-2">
              <i class="fas fa-calendar mr-1"></i>
              <?php echo date('d/m/Y', strtotime($plan['fecha_revision'])); ?>
            </div>
          </div>
        <?php endif; ?>
        
        <!-- Informe Final Firmado -->
        <?php if ($informeEstudiante && $informeEstudiante['estado'] == 'aprobado_final' && !empty($informeEstudiante['documento_firmado'])): ?>
          <div class="border border-green-200 rounded-lg p-4 hover:shadow-md transition bg-green-50">
            <div class="flex items-center mb-2">
              <i class="fas fa-file-contract text-green-600 mr-2"></i>
              <span class="font-semibold">Informe Final Firmado</span>
            </div>
            <p class="text-sm text-gris mb-3">Informe final aprobado y firmado por el coordinador</p>
            <div class="flex justify-between items-center">
              <a href="../download_documento_firmado.php?id=<?php echo $informeEstudiante['id']; ?>" 
                 target="_blank" 
                 class="text-green-600 hover:text-green-800 text-sm font-medium">
                <i class="fas fa-download mr-1"></i>Descargar
              </a>
              <span class="px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                Informe Firmado
              </span>
            </div>
            <div class="text-xs text-gray-500 mt-2">
              <i class="fas fa-calendar mr-1"></i>
              <?php echo date('d/m/Y', strtotime($informeEstudiante['fecha_revision'])); ?>
            </div>
          </div>
        <?php endif; ?>
        
        <!-- Mensaje cuando no hay documentos firmados -->
        <?php if ((!$plan || $plan['estado'] != 'aprobado_final' || empty($plan['archivo_plan_firmado'])) && 
                  (!$informeEstudiante || $informeEstudiante['estado'] != 'aprobado_final' || empty($informeEstudiante['documento_firmado']))): ?>
          <div class="col-span-2 text-center py-8">
            <i class="fas fa-file-signature text-4xl text-gray-300 mb-4"></i>
            <p class="text-gris">No hay documentos firmados disponibles</p>
            <p class="text-sm text-gray-500">Los documentos aparecerán aquí cuando sean aprobados y firmados</p>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Información del Equipo Académico -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
      <h2 class="text-2xl font-bold text-texto mb-6 flex items-center">
        <i class="fas fa-users mr-3 text-principal"></i>
        Equipo Académico
      </h2>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Coordinador -->
        <div class="bg-blue-50 rounded-lg p-4">
          <div class="flex items-center justify-between mb-3">
            <h3 class="text-lg font-semibold text-texto flex items-center">
              <i class="fas fa-user-cog mr-2 text-blue-600"></i>
              Coordinador
            </h3>
            <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium">
              <i class="fas fa-university mr-1"></i>Especialidad
            </span>
          </div>
          <?php if ($coordinador): ?>
            <div class="space-y-2">
              <p class="text-lg font-semibold text-texto"><?php echo htmlspecialchars(($coordinador['nombres'] ?? '') . ' ' . ($coordinador['apellidos'] ?? '')); ?></p>
              <p class="text-sm text-gris"><i class="fas fa-envelope mr-1"></i><?php echo htmlspecialchars($coordinador['email'] ?? 'No disponible'); ?></p>
              <p class="text-sm text-gris"><i class="fas fa-id-card mr-1"></i>Código: <?php echo htmlspecialchars($coordinador['codigo'] ?? 'No disponible'); ?></p>
              <p class="text-sm text-gris"><i class="fas fa-graduation-cap mr-1"></i><?php echo htmlspecialchars($coordinador['especialidad'] ?? 'No especificada'); ?></p>
            </div>
          <?php else: ?>
            <p class="text-gris">No hay coordinador asignado para tu especialidad</p>
          <?php endif; ?>
        </div>

        <!-- Docente Asesor -->
        <div class="bg-green-50 rounded-lg p-4">
          <div class="flex items-center justify-between mb-3">
            <h3 class="text-lg font-semibold text-texto flex items-center">
              <i class="fas fa-user-tie mr-2 text-green-600"></i>
              Docente Asesor
            </h3>
            <?php if ($docenteAsesor): ?>
              <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">
                <i class="fas fa-check mr-1"></i>Asignado
              </span>
            <?php else: ?>
              <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm font-medium">
                <i class="fas fa-clock mr-1"></i>Pendiente
              </span>
            <?php endif; ?>
          </div>
          <?php if ($docenteAsesor): ?>
            <div class="space-y-2">
              <p class="text-lg font-semibold text-texto"><?php echo htmlspecialchars(($docenteAsesor['nombres'] ?? '') . ' ' . ($docenteAsesor['apellidos'] ?? '')); ?></p>
              <p class="text-sm text-gris"><i class="fas fa-envelope mr-1"></i><?php echo htmlspecialchars($docenteAsesor['email'] ?? 'No disponible'); ?></p>
              <p class="text-sm text-gris"><i class="fas fa-id-card mr-1"></i>Código: <?php echo htmlspecialchars($docenteAsesor['codigo'] ?? 'No disponible'); ?></p>
              <p class="text-sm text-gris"><i class="fas fa-graduation-cap mr-1"></i><?php echo htmlspecialchars($docenteAsesor['especialidad'] ?? 'No especificada'); ?></p>
            </div>
          <?php else: ?>
            <p class="text-gris">Aún no tienes un docente asesor asignado. El coordinador te asignará uno una vez que registres tu plan de prácticas.</p>
          <?php endif; ?>
        </div>
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