<?php
require_once '../config/session.php';
require_once '../models/InformeFinal.php';
require_once '../models/User.php';

// Verificar que el usuario esté autenticado y sea estudiante
if (!isAuthenticated() || $_SESSION['user_type'] !== 'estudiante') {
    header('Location: login_estudiante.php');
    exit();
}

// Obtener información del usuario
$userModel = new User();
$user = $userModel->getById($_SESSION['user_id']);
if (!$user) {
    header('Location: login_estudiante.php');
    exit();
}

// Procesamiento del formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $informe = new InformeFinal();
    
    // Procesamiento de archivos
    $archivos_subidos = [];
    $upload_dir = '../uploads/informes/';
    
    // Crear directorio si no existe
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Procesar archivo PDF del informe
    if (isset($_FILES['informe_pdf']) && $_FILES['informe_pdf']['error'] == 0) {
        $file = $_FILES['informe_pdf'];
        $allowed_types = ['application/pdf'];
        
        if (in_array($file['type'], $allowed_types) && $file['size'] <= 10 * 1024 * 1024) {
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $new_filename = 'informe_final_' . time() . '.' . $extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                $archivos_subidos[] = $new_filename;
            }
        }
    }
    
    // Procesar archivo de anexos
    if (isset($_FILES['anexos_file']) && $_FILES['anexos_file']['error'] == 0) {
        $file = $_FILES['anexos_file'];
        $allowed_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip'];
        
        if (in_array($file['type'], $allowed_types) && $file['size'] <= 10 * 1024 * 1024) {
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $new_filename = 'anexos_' . time() . '.' . $extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                $archivos_subidos[] = $new_filename;
            }
        }
    }
    
    $data = [
        'estudiante_id' => $_SESSION['user_id'],
        'titulo' => $_POST['titulo'],
        'resumen_ejecutivo' => $_POST['resumen_ejecutivo'],
        'introduccion' => $_POST['introduccion'],
        'objetivos' => $_POST['objetivos'],
        'metodologia' => $_POST['metodologia'],
        'resultados' => $_POST['resultados'],
        'conclusiones' => $_POST['conclusiones'],
        'recomendaciones' => $_POST['recomendaciones'],
        'bibliografia' => $_POST['bibliografia'],
        'anexos' => $_POST['anexos'],
        'archivo_informe' => !empty($archivos_subidos) ? json_encode($archivos_subidos) : null,
        'estado' => 'pendiente'
    ];
    
    $result = $informe->create($data);
    if ($result) {
        $success = "Informe final registrado exitosamente" . (!empty($archivos_subidos) ? " con " . count($archivos_subidos) . " archivo(s) adjunto(s)" : "");
    } else {
        $error = "Error al registrar el informe final";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Informe Final - SYSPRE 2025</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
    
    <script>
        tailwind.config = {
            devtools: false,
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
                <i class="fas fa-graduation-cap"></i> SYSPRE 2025
            </a>
            <div class="space-x-6 hidden md:flex">
                <a href="estudiante_dashboard.php" class="text-gris hover:text-texto transition"><i class="fas fa-home mr-1"></i>Dashboard</a>
                <a href="#" class="text-gris hover:text-texto transition"><i class="fas fa-file-alt mr-1"></i>Reportes</a>
                <a href="#" class="text-gris hover:text-texto transition"><i class="fas fa-envelope mr-1"></i>Mensajes</a>
                <a href="../logout.php" class="text-gris hover:text-texto transition"><i class="fas fa-sign-out-alt mr-1"></i>Cerrar Sesión</a>
            </div>
        </div>
    </nav>

    <main class="pt-24 max-w-6xl mx-auto px-4 pb-10">
        
        <!-- Header -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <h1 class="text-3xl font-bold text-texto flex items-center">
                <i class="fas fa-file-signature mr-3 text-principal"></i>
                Informe Final de Prácticas Pre-Profesionales
            </h1>
            <p class="text-gris mt-2"><?php echo htmlspecialchars($user['nombres'] . ' ' . $user['apellidos']); ?> - <?php echo htmlspecialchars($user['especialidad']); ?></p>
        </div>

        <?php if (isset($success)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="space-y-8">
            
            <!-- Información General -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4 text-texto border-b border-gray-200 pb-2">
                    <i class="fas fa-info-circle mr-2 text-principal"></i>Información General
                </h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gris mb-2">Título del Informe *</label>
                        <input type="text" name="titulo" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-principal" placeholder="Título descriptivo del informe">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gris mb-2">Resumen Ejecutivo *</label>
                        <textarea name="resumen_ejecutivo" required rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-principal" placeholder="Breve resumen del informe, objetivos y principales resultados..."></textarea>
                    </div>
                </div>
            </div>

            <!-- Introducción -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4 text-texto border-b border-gray-200 pb-2">
                    <i class="fas fa-play-circle mr-2 text-principal"></i>Introducción
                </h2>
                <div>
                    <label class="block text-sm font-medium text-gris mb-2">Introducción *</label>
                    <textarea name="introduccion" required rows="6" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-principal" placeholder="Contexto de las prácticas, descripción de la empresa, justificación..."></textarea>
                </div>
            </div>

            <!-- Objetivos -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4 text-texto border-b border-gray-200 pb-2">
                    <i class="fas fa-bullseye mr-2 text-principal"></i>Objetivos
                </h2>
                <div>
                    <label class="block text-sm font-medium text-gris mb-2">Objetivos *</label>
                    <textarea name="objetivos" required rows="5" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-principal" placeholder="Objetivo general y objetivos específicos de las prácticas..."></textarea>
                </div>
            </div>

            <!-- Metodología -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4 text-texto border-b border-gray-200 pb-2">
                    <i class="fas fa-cogs mr-2 text-principal"></i>Metodología
                </h2>
                <div>
                    <label class="block text-sm font-medium text-gris mb-2">Metodología *</label>
                    <textarea name="metodologia" required rows="6" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-principal" placeholder="Descripción de los métodos, herramientas y procedimientos utilizados..."></textarea>
                </div>
            </div>

            <!-- Resultados -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4 text-texto border-b border-gray-200 pb-2">
                    <i class="fas fa-chart-line mr-2 text-principal"></i>Resultados
                </h2>
                <div>
                    <label class="block text-sm font-medium text-gris mb-2">Resultados *</label>
                    <textarea name="resultados" required rows="8" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-principal" placeholder="Descripción detallada de los resultados obtenidos durante las prácticas..."></textarea>
                </div>
            </div>

            <!-- Conclusiones -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4 text-texto border-b border-gray-200 pb-2">
                    <i class="fas fa-check-circle mr-2 text-principal"></i>Conclusiones
                </h2>
                <div>
                    <label class="block text-sm font-medium text-gris mb-2">Conclusiones *</label>
                    <textarea name="conclusiones" required rows="6" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-principal" placeholder="Conclusiones principales derivadas de los resultados obtenidos..."></textarea>
                </div>
            </div>

            <!-- Recomendaciones -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4 text-texto border-b border-gray-200 pb-2">
                    <i class="fas fa-lightbulb mr-2 text-principal"></i>Recomendaciones
                </h2>
                <div>
                    <label class="block text-sm font-medium text-gris mb-2">Recomendaciones *</label>
                    <textarea name="recomendaciones" required rows="5" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-principal" placeholder="Recomendaciones para la empresa, la universidad o futuros estudiantes..."></textarea>
                </div>
            </div>

            <!-- Bibliografía -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4 text-texto border-b border-gray-200 pb-2">
                    <i class="fas fa-book mr-2 text-principal"></i>Bibliografía
                </h2>
                <div>
                    <label class="block text-sm font-medium text-gris mb-2">Bibliografía</label>
                    <textarea name="bibliografia" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-principal" placeholder="Referencias bibliográficas utilizadas (formato APA)..."></textarea>
                </div>
            </div>

            <!-- Anexos -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4 text-texto border-b border-gray-200 pb-2">
                    <i class="fas fa-paperclip mr-2 text-principal"></i>Anexos
                </h2>
                <div>
                    <label class="block text-sm font-medium text-gris mb-2">Anexos</label>
                    <textarea name="anexos" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-principal" placeholder="Descripción de los anexos incluidos..."></textarea>
                </div>
            </div>

            <!-- Documentos -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4 text-texto border-b border-gray-200 pb-2">
                    <i class="fas fa-file-upload mr-2 text-principal"></i>Documentos
                </h2>
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gris mb-2">Informe Final (PDF) *</label>
                        <input type="file" name="informe_pdf" accept=".pdf" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-principal">
                        <p class="text-xs text-gris mt-1">Formato: PDF (máx. 10MB)</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gris mb-2">Anexos (Opcional)</label>
                        <input type="file" name="anexos_file" accept=".pdf,.doc,.docx,.zip" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-principal">
                        <p class="text-xs text-gris mt-1">Formatos: PDF, DOC, DOCX, ZIP</p>
                    </div>
                </div>
            </div>

            <!-- Botones -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex justify-between items-center">
                    <a href="estudiante_dashboard.php" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg transition">
                        <i class="fas fa-times mr-2"></i>Cancelar
                    </a>
                    <div class="space-x-3">
                        <button type="button" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg transition">
                            <i class="fas fa-save mr-2"></i>Guardar Borrador
                        </button>
                        <button type="submit" class="bg-principal hover:bg-emerald-700 text-white px-6 py-3 rounded-lg transition">
                            <i class="fas fa-paper-plane mr-2"></i>Enviar Informe
                        </button>
                    </div>
                </div>
            </div>

        </form>

    </main>

    <script>
        // Contador de caracteres
        document.querySelectorAll('textarea').forEach(textarea => {
            textarea.addEventListener('input', function() {
                const maxLength = this.getAttribute('maxlength');
                if (maxLength) {
                    const remaining = maxLength - this.value.length;
                    // Aquí podrías agregar un contador visual
                }
            });
        });

        // Validación de archivos
        document.querySelectorAll('input[type="file"]').forEach(input => {
            input.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const maxSize = 10 * 1024 * 1024; // 10MB
                    if (file.size > maxSize) {
                        alert('El archivo no debe superar los 10MB');
                        this.value = '';
                    }
                }
            });
        });

        // Auto-guardado (opcional)
        let autoSaveInterval;
        function startAutoSave() {
            autoSaveInterval = setInterval(() => {
                // Implementar auto-guardado aquí
                console.log('Auto-guardando...');
            }, 30000); // Cada 30 segundos
        }

        // Iniciar auto-guardado
        // startAutoSave();
    </script>

</body>
</html>