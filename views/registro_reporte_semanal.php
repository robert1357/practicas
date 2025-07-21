<?php
require_once '../config/session.php';
require_once '../models/ReporteSemanal.php';

// Verificar que el usuario esté autenticado y sea estudiante
if (!isAuthenticated() || !hasRole('estudiante')) {
    header('Location: login_estudiante.php');
    exit();
}

// Obtener datos del usuario actual
$user = getCurrentUser();

// Procesamiento del formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $reporte = new ReporteSemanal();
    
    $data = [
        'estudiante_id' => $_SESSION['user_id'],
        'fecha_inicio' => $_POST['fecha_inicio'],
        'fecha_fin' => $_POST['fecha_fin'],
        'total_horas' => $_POST['total_horas'],
        'asesor_empresarial' => $_POST['asesor_empresarial'],
        'area_trabajo' => $_POST['area_trabajo'],
        'actividades' => $_POST['actividades'],
        'aprendizajes' => $_POST['aprendizajes'],
        'dificultades' => $_POST['dificultades'],
        'estado' => 'pendiente'
    ];
    
    $result = $reporte->create($data);
    if ($result) {
        $success = "Reporte semanal registrado exitosamente";
    } else {
        $error = "Error al registrar el reporte semanal";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Crear Reporte Semanal - SYSPRE 2025</title>
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

    <style>
        .fade-in {
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.5s ease-in-out;
        }
        
        .fade-in.visible {
            opacity: 1;
            transform: translateY(0);
        }
        
        .file-upload-area {
            border: 2px dashed #d1d5db;
            transition: all 0.3s ease;
        }
        
        .file-upload-area.dragover {
            border-color: #3eb489;
            background-color: #f0fdf4;
        }
        
        .progress-bar {
            transition: width 0.3s ease;
        }
    </style>
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
                <a href="estudiante_reportes_semanales.php" class="text-principal font-semibold"><i class="fas fa-file-alt mr-1"></i>Reportes</a>
                <a href="#" class="text-gris hover:text-texto transition"><i class="fas fa-envelope mr-1"></i>Mensajes</a>
                <a href="../logout.php" class="text-gris hover:text-texto transition"><i class="fas fa-sign-out-alt mr-1"></i>Cerrar Sesión</a>
            </div>
        </div>
    </nav>

    <main class="pt-24 max-w-7xl mx-auto px-4 pb-10">
        
        <!-- Header -->
        <div class="bg-white rounded-lg shadow p-6 mb-8 fade-in">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-texto flex items-center">
                        <i class="fas fa-plus-circle mr-3 text-principal"></i>
                        Crear Nuevo Reporte Semanal
                    </h1>
                    <p class="text-gris mt-2"><?php echo htmlspecialchars($user['nombres'] . ' ' . $user['apellidos']); ?> - Período 2025-I</p>
                </div>
                <div class="text-right">
                    <a href="estudiante_reportes_semanales.php" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-semibold transition flex items-center">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Volver a Reportes
                    </a>
                </div>
            </div>
        </div>

        <!-- Progreso del Formulario -->
        <div class="bg-white rounded-lg shadow p-6 mb-8 fade-in">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-texto">Progreso del Reporte</h2>
                <span class="text-sm text-gris" id="progress-text">0% completado</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-principal h-2 rounded-full progress-bar" id="progress-bar" style="width: 0%"></div>
            </div>
            <div class="flex justify-between mt-3 text-sm text-gris">
                <span>Información Básica</span>
                <span>Actividades</span>
                <span>Evidencias</span>
                <span>Revisión</span>
            </div>
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

        <form id="reporteForm" class="space-y-8" method="POST">
            
            <!-- Información Básica -->
            <div class="bg-white rounded-lg shadow p-6 fade-in">
                <div class="flex items-center mb-6">
                    <div class="bg-principal text-white rounded-full p-3 mr-4">
                        <i class="fas fa-info-circle text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold text-texto">Información Básica</h2>
                        <p class="text-gris">Datos generales del reporte semanal</p>
                    </div>
                </div>
                
                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-texto mb-2">
                            <i class="fas fa-calendar-alt mr-2"></i>Período del Reporte
                        </label>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs text-gris mb-1">Fecha de Inicio</label>
                                <input type="date" name="fecha_inicio" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-principal focus:border-transparent" required>
                            </div>
                            <div>
                                <label class="block text-xs text-gris mb-1">Fecha de Fin</label>
                                <input type="date" name="fecha_fin" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-principal focus:border-transparent" required>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-texto mb-2">
                            <i class="fas fa-clock mr-2"></i>Total de Horas Trabajadas
                        </label>
                        <input type="number" name="total_horas" min="1" max="50" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-principal focus:border-transparent" placeholder="Ej: 40" required>
                        <p class="text-xs text-gris mt-1">Horas trabajadas durante la semana</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-texto mb-2">
                            <i class="fas fa-user-tie mr-2"></i>Asesor Empresarial
                        </label>
                        <input type="text" name="asesor_empresarial" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-principal focus:border-transparent" placeholder="Nombre del asesor" required>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-texto mb-2">
                            <i class="fas fa-building mr-2"></i>Área de Trabajo
                        </label>
                        <input type="text" name="area_trabajo" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-principal focus:border-transparent" placeholder="Área de trabajo" required>
                    </div>
                </div>
            </div>

            <!-- Actividades Realizadas -->
            <div class="bg-white rounded-lg shadow p-6 fade-in">
                <div class="flex items-center mb-6">
                    <div class="bg-blue-600 text-white rounded-full p-3 mr-4">
                        <i class="fas fa-tasks text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold text-texto">Actividades Realizadas</h2>
                        <p class="text-gris">Describe las actividades realizadas durante la semana</p>
                    </div>
                </div>
                
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-texto mb-2">
                            <i class="fas fa-list-ul mr-2"></i>Resumen de Actividades
                        </label>
                        <textarea name="actividades" rows="6" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-principal focus:border-transparent" placeholder="Describe las principales actividades realizadas durante la semana..." required></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-texto mb-2">
                            <i class="fas fa-lightbulb mr-2"></i>Aprendizajes y Logros
                        </label>
                        <textarea name="aprendizajes" rows="4" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-principal focus:border-transparent" placeholder="¿Qué aprendiste esta semana? ¿Qué logros destacarías?" required></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-texto mb-2">
                            <i class="fas fa-exclamation-triangle mr-2"></i>Dificultades Encontradas
                        </label>
                        <textarea name="dificultades" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-principal focus:border-transparent" placeholder="Describe las dificultades encontradas y cómo las solucionaste..."></textarea>
                    </div>
                </div>
            </div>

            <!-- Evidencias y Documentos -->
            <div class="bg-white rounded-lg shadow p-6 fade-in">
                <div class="flex items-center mb-6">
                    <div class="bg-purple-600 text-white rounded-full p-3 mr-4">
                        <i class="fas fa-camera text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold text-texto">Evidencias y Documentos</h2>
                        <p class="text-gris">Adjunta fotografías y documentos que respalden tu trabajo</p>
                    </div>
                </div>
                
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-texto mb-2">
                            <i class="fas fa-file-alt mr-2"></i>Documentos Adicionales
                        </label>
                        <div class="file-upload-area border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-principal transition cursor-pointer" onclick="document.getElementById('documentos').click()">
                            <div class="space-y-2">
                                <i class="fas fa-file-upload text-4xl text-gris"></i>
                                <p class="text-gris">Sube documentos relacionados con tu trabajo</p>
                                <p class="text-xs text-gris">Formatos: PDF, DOC, DOCX, XLS, XLSX (máx. 10MB cada uno)</p>
                            </div>
                            <input type="file" id="documentos" name="documentos[]" multiple accept=".pdf,.doc,.docx,.xls,.xlsx" class="hidden">
                        </div>
                        <div id="documentos-preview" class="mt-4 space-y-2"></div>
                    </div>
                </div>
            </div>

            <!-- Revisión y Envío -->
            <div class="bg-white rounded-lg shadow p-6 fade-in">
                <div class="flex items-center mb-6">
                    <div class="bg-green-600 text-white rounded-full p-3 mr-4">
                        <i class="fas fa-check-circle text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold text-texto">Revisión y Envío</h2>
                        <p class="text-gris">Revisa toda la información antes de enviar</p>
                    </div>
                </div>
                
                <div class="space-y-6">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h3 class="text-lg font-semibold text-blue-800 mb-2">
                            <i class="fas fa-info-circle mr-2"></i>Recordatorios Importantes
                        </h3>
                        <ul class="text-sm text-blue-700 space-y-1">
                            <li>• Asegúrate de que todas las fechas sean correctas</li>
                            <li>• Revisa que el total de horas sea preciso</li>
                            <li>• Verifica que las evidencias estén relacionadas con el trabajo</li>
                            <li>• Usa un lenguaje formal y profesional</li>
                        </ul>
                    </div>
                    
                    <div class="flex justify-between items-center pt-4 border-t border-gray-200">
                        <a href="estudiante_reportes_semanales.php" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg transition">
                            <i class="fas fa-times mr-2"></i>Cancelar
                        </a>
                        <div class="space-x-3">
                            <button type="submit" class="bg-principal hover:bg-emerald-700 text-white px-6 py-3 rounded-lg transition">
                                <i class="fas fa-paper-plane mr-2"></i>Enviar Reporte
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

    </main>

    <script>
        // Configurar fechas por defecto (semana actual)
        document.addEventListener('DOMContentLoaded', function() {
            const hoy = new Date();
            const lunes = new Date(hoy.setDate(hoy.getDate() - hoy.getDay() + 1));
            const viernes = new Date(hoy.setDate(hoy.getDate() - hoy.getDay() + 5));
            
            document.querySelector('input[name="fecha_inicio"]').value = lunes.toISOString().split('T')[0];
            document.querySelector('input[name="fecha_fin"]').value = viernes.toISOString().split('T')[0];
            
            // Configurar animaciones
            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                    }
                });
            }, {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            });

            document.querySelectorAll('.fade-in').forEach(el => {
                observer.observe(el);
            });
        });

        // Progreso del formulario
        function actualizarProgreso() {
            const campos = document.querySelectorAll('input[required], textarea[required]');
            const camposCompletos = Array.from(campos).filter(campo => campo.value.trim() !== '').length;
            const progreso = Math.round((camposCompletos / campos.length) * 100);
            
            document.getElementById('progress-bar').style.width = progreso + '%';
            document.getElementById('progress-text').textContent = progreso + '% completado';
        }

        // Escuchar cambios en los campos
        document.addEventListener('input', actualizarProgreso);
        document.addEventListener('change', actualizarProgreso);

        // Preview de archivos
        document.getElementById('documentos').addEventListener('change', function() {
            const files = this.files;
            const previewContainer = document.getElementById('documentos-preview');
            previewContainer.innerHTML = '';
            
            Array.from(files).forEach(file => {
                const fileDiv = document.createElement('div');
                fileDiv.className = 'flex items-center justify-between bg-gray-50 p-3 rounded-lg';
                fileDiv.innerHTML = `
                    <div class="flex items-center">
                        <i class="fas fa-file-alt text-blue-600 mr-3"></i>
                        <span class="text-sm">${file.name}</span>
                        <span class="text-xs text-gris ml-2">(${(file.size / 1024 / 1024).toFixed(2)} MB)</span>
                    </div>
                    <button type="button" class="text-red-600 hover:text-red-800" onclick="this.parentElement.remove()">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                previewContainer.appendChild(fileDiv);
            });
        });
    </script>

</body>
</html>