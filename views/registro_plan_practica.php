<?php
require_once '../config/session.php';
require_once '../models/PlanPractica.php';

// Verificar que el usuario esté autenticado y sea estudiante
if (!isAuthenticated() || $_SESSION['user_type'] !== 'estudiante') {
    header('Location: login_estudiante.php');
    exit();
}

require_once '../config/upload_config.php';

// Procesamiento del formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $planPractica = new PlanPractica();
    
    $data = [
        'estudiante_id' => $_SESSION['user_id'],
        'nombres' => $_POST['nombres'],
        'apellidos' => $_POST['apellidos'],
        'codigo' => $_POST['codigo'],
        'especialidad' => $_POST['especialidad'],
        'telefono' => $_POST['telefono'],
        'email' => $_POST['email'],
        'empresa' => $_POST['empresa'],
        'ruc' => $_POST['ruc'],
        'telefono_empresa' => $_POST['telefono_empresa'],
        'direccion_empresa' => $_POST['direccion_empresa'],
        'supervisor' => $_POST['supervisor'],
        'cargo_supervisor' => $_POST['cargo_supervisor'],
        'fecha_inicio' => $_POST['fecha_inicio'],
        'fecha_fin' => $_POST['fecha_fin'],
        'horario' => $_POST['horario'],
        'total_horas' => $_POST['total_horas'],
        'actividades' => $_POST['actividades'],
        'objetivos' => $_POST['objetivos'],
        'estado' => 'pendiente'
    ];
    
    // Manejar carga de archivos
    $archivos = [];
    $upload_errors = [];
    
    // Procesar archivo principal del plan
    if (isset($_FILES['plan_practica']) && $_FILES['plan_practica']['error'] == 0) {
        $uploadResult = UploadConfig::uploadFile($_FILES['plan_practica'], 'planes', $_SESSION['user_id'], 'plan');
        if ($uploadResult['success']) {
            $data['archivo_plan'] = $uploadResult['filename'];
            $archivos['plan_practica'] = $uploadResult;
        } else {
            $upload_errors[] = 'Plan de práctica: ' . implode(', ', $uploadResult['errors']);
        }
    }
    
    // Procesar documentos adicionales
    for ($i = 1; $i <= 3; $i++) {
        $field = "documento_$i";
        if (isset($_FILES[$field]) && $_FILES[$field]['error'] == 0) {
            $uploadResult = UploadConfig::uploadFile($_FILES[$field], 'planes', $_SESSION['user_id'], "doc$i");
            if ($uploadResult['success']) {
                $data["archivo_documento$i"] = $uploadResult['filename'];
                $archivos[$field] = $uploadResult;
            } else {
                $upload_errors[] = "Documento $i: " . implode(', ', $uploadResult['errors']);
            }
        }
    }
    
    if (empty($upload_errors)) {
        $result = $planPractica->create($data);
        if ($result) {
            $success = "Plan de práctica registrado exitosamente";
            if (!empty($archivos)) {
                $success .= "<br>Archivos cargados: " . count($archivos);
            }
        } else {
            $error = "Error al registrar el plan de práctica";
        }
    } else {
        $error = "Errores en la carga de archivos:<br>" . implode("<br>", $upload_errors);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Registro de Plan - SYSPRE 2025</title>
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
                <a href="#" class="text-gris hover:text-texto transition"><i class="fas fa-file-alt mr-1"></i>Formatos</a>
                <a href="../logout.php" class="text-gris hover:text-texto transition"><i class="fas fa-sign-out-alt mr-1"></i>Cerrar Sesión</a>
            </div>
        </div>
    </nav>

    <main class="pt-24 max-w-4xl mx-auto px-4 pb-10">
        <div class="bg-white rounded-lg shadow-lg">
            <!-- Header -->
            <div class="bg-principal text-white p-6 rounded-t-lg">
                <h1 class="text-2xl font-bold flex items-center">
                    <i class="fas fa-file-signature mr-3"></i>
                    Registro de Plan de Prácticas Pre-Profesionales
                </h1>
                <p class="mt-2 opacity-90">Complete todos los campos requeridos para enviar su solicitud</p>
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

            <!-- Formulario -->
            <form class="p-6" method="POST" enctype="multipart/form-data">
                
                <!-- Información Personal -->
                <div class="mb-8">
                    <h2 class="text-xl font-semibold mb-4 text-texto border-b border-gray-200 pb-2">
                        <i class="fas fa-user mr-2 text-principal"></i>Información Personal
                    </h2>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gris mb-2">Nombres Completos *</label>
                            <input type="text" name="nombres" value="<?php echo $_SESSION['nombres'] ?? ''; ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-principal">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gris mb-2">Apellidos Completos *</label>
                            <input type="text" name="apellidos" value="<?php echo $_SESSION['apellidos'] ?? ''; ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-principal">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gris mb-2">Código Universitario *</label>
                            <input type="text" name="codigo" value="<?php echo $_SESSION['codigo'] ?? ''; ?>" required readonly class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-principal bg-gray-100">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gris mb-2">Especialidad *</label>
                            <input type="text" name="especialidad" value="<?php echo $_SESSION['especialidad'] ?? ''; ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-principal">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gris mb-2">Teléfono *</label>
                            <input type="tel" name="telefono" value="<?php echo $_SESSION['telefono'] ?? ''; ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-principal">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gris mb-2">Email *</label>
                            <input type="email" name="email" value="<?php echo $_SESSION['email'] ?? ''; ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-principal">
                        </div>
                    </div>
                </div>

                <!-- Información de la Empresa/Institución -->
                <div class="mb-8">
                    <h2 class="text-xl font-semibold mb-4 text-texto border-b border-gray-200 pb-2">
                        <i class="fas fa-building mr-2 text-principal"></i>Información de la Empresa/Institución
                    </h2>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gris mb-2">Nombre de la Empresa/Institución *</label>
                            <input type="text" name="empresa" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-principal">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gris mb-2">RUC *</label>
                            <input type="text" name="ruc" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-principal">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gris mb-2">Teléfono de la Empresa</label>
                            <input type="tel" name="telefono_empresa" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-principal">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gris mb-2">Dirección Completa *</label>
                            <textarea name="direccion_empresa" required rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-principal"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gris mb-2">Nombre del Supervisor *</label>
                            <input type="text" name="supervisor" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-principal">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gris mb-2">Cargo del Supervisor *</label>
                            <input type="text" name="cargo_supervisor" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-principal">
                        </div>
                    </div>
                </div>

                <!-- Detalles de las Prácticas -->
                <div class="mb-8">
                    <h2 class="text-xl font-semibold mb-4 text-texto border-b border-gray-200 pb-2">
                        <i class="fas fa-tasks mr-2 text-principal"></i>Detalles de las Prácticas
                    </h2>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gris mb-2">Fecha de Inicio *</label>
                            <input type="date" name="fecha_inicio" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-principal">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gris mb-2">Fecha de Finalización *</label>
                            <input type="date" name="fecha_fin" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-principal">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gris mb-2">Horario de Trabajo *</label>
                            <input type="text" name="horario" required placeholder="Ej: Lunes a Viernes 8:00-17:00" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-principal">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gris mb-2">Total de Horas *</label>
                            <input type="number" name="total_horas" required min="120" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-principal">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gris mb-2">Descripción de Actividades a Realizar *</label>
                            <textarea name="actividades" required rows="4" placeholder="Describa detalladamente las actividades que realizará durante sus prácticas..." class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-principal"></textarea>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gris mb-2">Objetivos de las Prácticas *</label>
                            <textarea name="objetivos" required rows="3" placeholder="Describa los objetivos que espera alcanzar con estas prácticas..." class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-principal"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Documentos -->
                <div class="mb-8">
                    <h2 class="text-xl font-semibold mb-4 text-texto border-b border-gray-200 pb-2">
                        <i class="fas fa-file-upload mr-2 text-principal"></i>Documentos Requeridos
                    </h2>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gris mb-2">Plan de Practica *</label>
                            <input type="file" name="plan_practica" accept=".pdf,.doc,.docx" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-principal">
                            <p class="text-xs text-gris mt-1">Formatos permitidos: PDF, DOC, DOCX</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gris mb-2">Documento Adicional 1 (opcional)</label>
                            <input type="file" name="documento_1" accept=".pdf,.doc,.docx" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-principal">
                            <p class="text-xs text-gris mt-1">Formatos permitidos: PDF, DOC, DOCX</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gris mb-2">Documento Adicional 2 (opcional)</label>
                            <input type="file" name="documento_2" accept=".pdf,.doc,.docx" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-principal">
                            <p class="text-xs text-gris mt-1">Formato permitido: PDF, DOC, DOCX</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gris mb-2">Documento Adicional 3 (opcional)</label>
                            <input type="file" name="documento_3" accept=".pdf,.doc,.docx" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-principal">
                            <p class="text-xs text-gris mt-1">Formato permitido: PDF, DOC, DOCX</p>
                        </div>
                    </div>
                </div>

                <!-- Botones -->
                <div class="flex gap-4 justify-end">
                    <a href="estudiante_dashboard.php" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg transition">
                        <i class="fas fa-times mr-2"></i>Cancelar
                    </a>
                    <button type="submit" class="bg-principal hover:bg-emerald-700 text-white px-6 py-3 rounded-lg transition">
                        <i class="fas fa-paper-plane mr-2"></i>Enviar Solicitud
                    </button>
                </div>
            </form>
        </div>
    </main>

    <script>
        // Validación de archivos
        document.querySelectorAll('input[type="file"]').forEach(input => {
            input.addEventListener('change', function() {
                const file = this.files[0];
                if (file && file.size > 5 * 1024 * 1024) { // 5MB
                    alert('El archivo no debe superar los 5MB');
                    this.value = '';
                }
            });
        });
    </script>

</body>
</html>