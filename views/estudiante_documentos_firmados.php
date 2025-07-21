<?php
require_once '../config/session.php';
require_once '../models/User.php';
require_once '../models/PlanPractica.php';
require_once '../models/InformeFinal.php';
require_once '../models/DocumentoReglamento.php';

// Verificar autenticación
if (!isAuthenticated() || !hasRole('estudiante')) {
    header("Location: login_estudiante.php");
    exit();
}

$user = getCurrentUser();
$planPractica = new PlanPractica();
$informe = new InformeFinal();
$documentoModel = new DocumentoReglamento();

// Obtener documentos del estudiante
$plan = $planPractica->getByEstudiante($user['id']);
$informeEstudiante = $informe->getByEstudiante($user['id']);
$documentosEspecialidad = $documentoModel->getByEspecialidad($user['especialidad']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Documentos Firmados - SYSPRE 2025</title>
  
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
        <a href="estudiante_dashboard.php" class="text-gris hover:text-texto transition"><i class="fas fa-tachometer-alt mr-1"></i>Dashboard</a>
        <a href="estudiante_reportes_semanales.php" class="text-gris hover:text-texto transition"><i class="fas fa-file-text mr-1"></i>Reportes</a>
        <a href="#" class="text-principal font-semibold"><i class="fas fa-file-signature mr-1"></i>Documentos Firmados</a>
        <a href="perfil_estudiante.php" class="text-gris hover:text-texto transition"><i class="fas fa-user mr-1"></i>Mi Perfil</a>
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
            <i class="fas fa-file-signature mr-3 text-principal"></i>
            Documentos Firmados
          </h1>
          <p class="text-gris mt-2"><?php echo htmlspecialchars($user['nombres'] . ' ' . $user['apellidos']); ?> - Código: <?php echo htmlspecialchars($user['codigo']); ?></p>
        </div>
        <div class="text-right">
          <div class="bg-principal bg-opacity-10 px-4 py-2 rounded-lg">
            <p class="text-principal text-sm font-medium">Documentos Disponibles</p>
            <p class="text-lg font-bold text-principal">
              <?php 
                $count = 0;
                if ($plan && $plan['estado'] == 'aprobado') $count++;
                if ($informeEstudiante && $informeEstudiante['estado'] == 'aprobado') $count++;
                echo $count;
              ?>
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Documentos del Estudiante -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
      
      <!-- Plan de Prácticas Firmado -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-xl font-bold text-texto flex items-center">
            <i class="fas fa-file-contract mr-2 text-principal"></i>
            Plan de Prácticas
          </h2>
          <?php if ($plan && $plan['estado'] == 'aprobado'): ?>
            <span class="bg-green-100 text-green-800 text-xs font-medium px-2 py-1 rounded">
              <i class="fas fa-check mr-1"></i>Firmado
            </span>
          <?php else: ?>
            <span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2 py-1 rounded">
              <i class="fas fa-clock mr-1"></i>Pendiente
            </span>
          <?php endif; ?>
        </div>
        
        <?php if ($plan && $plan['estado'] == 'aprobado'): ?>
          <div class="space-y-2 mb-4">
            <p class="text-sm text-gris"><strong>Empresa:</strong> <?php echo htmlspecialchars($plan['empresa']); ?></p>
            <p class="text-sm text-gris"><strong>Fecha de Aprobación:</strong> <?php echo date('d/m/Y', strtotime($plan['fecha_actualizacion'])); ?></p>
            <p class="text-sm text-gris"><strong>Estado:</strong> Aprobado y Firmado</p>
          </div>
          <div class="flex space-x-2">
            <a href="../download_plan_firmado.php?id=<?php echo $plan['id']; ?>" 
               class="flex-1 bg-principal hover:bg-green-600 text-white px-4 py-2 rounded text-center transition">
              <i class="fas fa-download mr-1"></i>Descargar PDF
            </a>
            <a href="estudiante_ver_plan.php" 
               class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded text-center transition">
              <i class="fas fa-eye mr-1"></i>Ver Detalles
            </a>
          </div>
        <?php else: ?>
          <div class="text-center py-8">
            <i class="fas fa-file-alt text-gray-300 text-4xl mb-4"></i>
            <p class="text-gris">Plan de prácticas no aprobado aún</p>
            <p class="text-sm text-gris mt-2">
              <?php if (!$plan): ?>
                <a href="estudiante_registro_plan.php" class="text-principal hover:underline">Registrar plan de prácticas</a>
              <?php else: ?>
                Estado actual: <?php echo ucfirst($plan['estado']); ?>
              <?php endif; ?>
            </p>
          </div>
        <?php endif; ?>
      </div>

      <!-- Informe Final Firmado -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-xl font-bold text-texto flex items-center">
            <i class="fas fa-file-signature mr-2 text-principal"></i>
            Informe Final
          </h2>
          <?php if ($informeEstudiante && $informeEstudiante['estado'] == 'aprobado'): ?>
            <span class="bg-green-100 text-green-800 text-xs font-medium px-2 py-1 rounded">
              <i class="fas fa-check mr-1"></i>Firmado
            </span>
          <?php else: ?>
            <span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2 py-1 rounded">
              <i class="fas fa-clock mr-1"></i>Pendiente
            </span>
          <?php endif; ?>
        </div>
        
        <?php if ($informeEstudiante && $informeEstudiante['estado'] == 'aprobado'): ?>
          <div class="space-y-2 mb-4">
            <p class="text-sm text-gris"><strong>Título:</strong> <?php echo htmlspecialchars($informeEstudiante['titulo']); ?></p>
            <p class="text-sm text-gris"><strong>Fecha de Aprobación:</strong> <?php echo date('d/m/Y', strtotime($informeEstudiante['fecha_actualizacion'])); ?></p>
            <p class="text-sm text-gris"><strong>Nota:</strong> <?php echo $informeEstudiante['nota_docente'] ?? 'Sin calificar'; ?></p>
          </div>
          <div class="flex space-x-2">
            <a href="../download_informe_firmado.php?id=<?php echo $informeEstudiante['id']; ?>" 
               class="flex-1 bg-principal hover:bg-green-600 text-white px-4 py-2 rounded text-center transition">
              <i class="fas fa-download mr-1"></i>Descargar PDF
            </a>
            <a href="estudiante_ver_informe_simple.php" 
               class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded text-center transition">
              <i class="fas fa-eye mr-1"></i>Ver Detalles
            </a>
          </div>
        <?php else: ?>
          <div class="text-center py-8">
            <i class="fas fa-file-alt text-gray-300 text-4xl mb-4"></i>
            <p class="text-gris">Informe final no aprobado aún</p>
            <p class="text-sm text-gris mt-2">
              <?php if (!$informeEstudiante): ?>
                <a href="registro_informe_final.php" class="text-principal hover:underline">Crear informe final</a>
              <?php else: ?>
                Estado actual: <?php echo ucfirst($informeEstudiante['estado']); ?>
              <?php endif; ?>
            </p>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Documentos de Reglamento -->
    <div class="bg-white rounded-lg shadow p-6">
      <h2 class="text-2xl font-bold text-texto flex items-center mb-6">
        <i class="fas fa-book mr-3 text-principal"></i>
        Documentos de Reglamento
      </h2>
      
      <?php if ($documentosEspecialidad && count($documentosEspecialidad) > 0): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <?php foreach ($documentosEspecialidad as $doc): ?>
            <div class="border rounded-lg p-4 hover:shadow-md transition">
              <div class="flex items-center justify-between mb-2">
                <h3 class="font-semibold text-sm"><?php echo htmlspecialchars($doc['titulo']); ?></h3>
                <span class="text-xs text-gris"><?php echo ucfirst($doc['tipo_documento']); ?></span>
              </div>
              <p class="text-xs text-gris mb-3"><?php echo htmlspecialchars($doc['descripcion']); ?></p>
              <a href="../<?php echo $doc['archivo_url']; ?>" 
                 class="block w-full bg-principal hover:bg-green-600 text-white text-xs py-2 px-3 rounded text-center transition">
                <i class="fas fa-download mr-1"></i>Descargar
              </a>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="text-center py-8">
          <i class="fas fa-file-alt text-gray-300 text-4xl mb-4"></i>
          <p class="text-gris">No hay documentos de reglamento disponibles</p>
        </div>
      <?php endif; ?>
    </div>

    <!-- Botón Volver -->
    <div class="mt-8 text-center">
      <a href="estudiante_dashboard.php" 
         class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg transition">
        <i class="fas fa-arrow-left mr-2"></i>Volver al Dashboard
      </a>
    </div>
  </main>
</body>
</html>