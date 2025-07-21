<?php
require_once '../config/session.php';
require_once '../models/User.php';

// Verificar autenticación
if (!isAuthenticated() || !hasRole('coordinador')) {
    header("Location: login_coordinador.php");
    exit();
}

$currentUser = getCurrentUser();
$userModel = new User();
$success = $error = '';

// Procesamiento del formulario de actualización
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $updateData = [
        'telefono' => $_POST['telefono'],
        'direccion' => $_POST['direccion'],
        'categoria_docente' => $_POST['categoria_docente'],
        'dedicacion' => $_POST['dedicacion'],
        'grado_academico' => $_POST['grado_academico']
    ];
    
    // Actualizar contraseña si se proporciona
    if (!empty($_POST['nueva_password'])) {
        if ($_POST['nueva_password'] !== $_POST['confirmar_password']) {
            $error = "Las contraseñas no coinciden";
        } else {
            $updateData['password'] = $_POST['nueva_password'];
        }
    }
    
    if (empty($error)) {
        $result = $userModel->update($currentUser['id'], $updateData);
        if ($result) {
            $success = "Perfil actualizado correctamente";
            // Recargar información del usuario
            $currentUser = $userModel->findById($currentUser['id']);
            $_SESSION['user'] = $currentUser;
        } else {
            $error = "Error al actualizar el perfil";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - SYSPRE 2025</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
<body class="bg-fondo min-h-screen">

    <!-- Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <a href="coordinador_dashboard.php" class="text-principal hover:text-emerald-700 transition">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Volver al Dashboard
                </a>
                <div class="text-xl font-bold text-texto">
                    <i class="fas fa-user-cog mr-2"></i>
                    Mi Perfil Coordinador
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <span class="text-gris">
                    <i class="fas fa-user-tie mr-1"></i>
                    <?php echo htmlspecialchars($currentUser['nombres'] . ' ' . $currentUser['apellidos']); ?>
                </span>
                <a href="../logout.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition">
                    <i class="fas fa-sign-out-alt mr-1"></i>
                    Cerrar Sesión
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-4xl mx-auto px-4 py-8">
        
        <!-- Mensajes -->
        <?php if (!empty($success)): ?>
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6">
                <i class="fas fa-check-circle mr-2"></i><?php echo $success; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6">
                <i class="fas fa-exclamation-circle mr-2"></i><?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Información del Perfil -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="text-center mb-6">
                        <div class="bg-yellow-500 text-white w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-user-cog text-3xl"></i>
                        </div>
                        <h2 class="text-xl font-bold text-texto"><?php echo htmlspecialchars($currentUser['nombres'] . ' ' . $currentUser['apellidos']); ?></h2>
                        <p class="text-gris"><?php echo htmlspecialchars($currentUser['especialidad']); ?></p>
                        <p class="text-sm text-gris">Código: <?php echo htmlspecialchars($currentUser['codigo']); ?></p>
                    </div>
                    
                    <div class="space-y-3">
                        <div class="flex items-center text-sm">
                            <i class="fas fa-envelope w-5 mr-2 text-gris"></i>
                            <span class="text-gris"><?php echo htmlspecialchars($currentUser['email']); ?></span>
                        </div>
                        <div class="flex items-center text-sm">
                            <i class="fas fa-phone w-5 mr-2 text-gris"></i>
                            <span class="text-gris"><?php echo htmlspecialchars($currentUser['telefono'] ?? 'No especificado'); ?></span>
                        </div>
                        <div class="flex items-center text-sm">
                            <i class="fas fa-id-card w-5 mr-2 text-gris"></i>
                            <span class="text-gris">DNI: <?php echo htmlspecialchars($currentUser['dni'] ?? 'No especificado'); ?></span>
                        </div>
                        <div class="flex items-center text-sm">
                            <i class="fas fa-map-marker-alt w-5 mr-2 text-gris"></i>
                            <span class="text-gris"><?php echo htmlspecialchars($currentUser['direccion'] ?? 'No especificado'); ?></span>
                        </div>
                        <div class="flex items-center text-sm">
                            <i class="fas fa-user-tie w-5 mr-2 text-gris"></i>
                            <span class="text-gris"><?php echo htmlspecialchars($currentUser['categoria_docente'] ?? 'No especificado'); ?></span>
                        </div>
                        <div class="flex items-center text-sm">
                            <i class="fas fa-clock w-5 mr-2 text-gris"></i>
                            <span class="text-gris"><?php echo htmlspecialchars($currentUser['dedicacion'] ?? 'No especificado'); ?></span>
                        </div>
                        <div class="flex items-center text-sm">
                            <i class="fas fa-graduation-cap w-5 mr-2 text-gris"></i>
                            <span class="text-gris"><?php echo htmlspecialchars($currentUser['grado_academico'] ?? 'No especificado'); ?></span>
                        </div>
                        <div class="flex items-center text-sm">
                            <i class="fas fa-briefcase w-5 mr-2 text-gris"></i>
                            <span class="text-gris font-medium">Coordinador de Especialidad</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formulario de Edición -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-bold text-texto mb-6 flex items-center">
                        <i class="fas fa-edit mr-2 text-principal"></i>
                        Editar Información del Perfil
                    </h3>

                    <form method="post" class="space-y-6">
                        
                        <!-- Información Personal -->
                        <div>
                            <h4 class="text-md font-semibold text-texto mb-4">Información Personal</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                
                                <div>
                                    <label class="block text-sm font-medium text-gris mb-2">Nombres (No editable)</label>
                                    <input type="text" value="<?php echo htmlspecialchars($currentUser['nombres']); ?>" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100" readonly>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gris mb-2">Apellidos (No editable)</label>
                                    <input type="text" value="<?php echo htmlspecialchars($currentUser['apellidos']); ?>" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100" readonly>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gris mb-2">Email (No editable)</label>
                                    <input type="email" value="<?php echo htmlspecialchars($currentUser['email']); ?>" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100" readonly>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gris mb-2">DNI (No editable)</label>
                                    <input type="text" value="<?php echo htmlspecialchars($currentUser['dni'] ?? ''); ?>" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100" readonly>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gris mb-2">Teléfono *</label>
                                    <input type="tel" name="telefono" value="<?php echo htmlspecialchars($currentUser['telefono'] ?? ''); ?>" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-principal" required>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gris mb-2">Dirección *</label>
                                    <input type="text" name="direccion" value="<?php echo htmlspecialchars($currentUser['direccion'] ?? ''); ?>" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-principal" required>
                                </div>

                            </div>
                        </div>

                        <!-- Información Académica y Profesional -->
                        <div>
                            <h4 class="text-md font-semibold text-texto mb-4">Información Profesional</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                
                                <div>
                                    <label class="block text-sm font-medium text-gris mb-2">Código (No editable)</label>
                                    <input type="text" value="<?php echo htmlspecialchars($currentUser['codigo']); ?>" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100" readonly>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gris mb-2">Especialidad (No editable)</label>
                                    <input type="text" value="<?php echo htmlspecialchars($currentUser['especialidad']); ?>" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100" readonly>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gris mb-2">Categoría Docente *</label>
                                    <select name="categoria_docente" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-principal" required>
                                        <option value="">Seleccionar...</option>
                                        <option value="principal" <?php echo ($currentUser['categoria_docente'] == 'principal') ? 'selected' : ''; ?>>Docente Principal</option>
                                        <option value="asociado" <?php echo ($currentUser['categoria_docente'] == 'asociado') ? 'selected' : ''; ?>>Docente Asociado</option>
                                        <option value="auxiliar" <?php echo ($currentUser['categoria_docente'] == 'auxiliar') ? 'selected' : ''; ?>>Docente Auxiliar</option>
                                        <option value="contratado" <?php echo ($currentUser['categoria_docente'] == 'contratado') ? 'selected' : ''; ?>>Docente Contratado</option>
                                        <option value="jefe_practica" <?php echo ($currentUser['categoria_docente'] == 'jefe_practica') ? 'selected' : ''; ?>>Jefe de Práctica</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gris mb-2">Dedicación *</label>
                                    <select name="dedicacion" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-principal" required>
                                        <option value="">Seleccionar...</option>
                                        <option value="exclusiva" <?php echo ($currentUser['dedicacion'] == 'exclusiva') ? 'selected' : ''; ?>>Dedicación Exclusiva</option>
                                        <option value="completa" <?php echo ($currentUser['dedicacion'] == 'completa') ? 'selected' : ''; ?>>Tiempo Completo</option>
                                        <option value="parcial" <?php echo ($currentUser['dedicacion'] == 'parcial') ? 'selected' : ''; ?>>Tiempo Parcial</option>
                                    </select>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gris mb-2">Grado Académico *</label>
                                    <select name="grado_academico" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-principal" required>
                                        <option value="">Seleccionar...</option>
                                        <option value="bachiller" <?php echo ($currentUser['grado_academico'] == 'bachiller') ? 'selected' : ''; ?>>Bachiller</option>
                                        <option value="licenciado" <?php echo ($currentUser['grado_academico'] == 'licenciado') ? 'selected' : ''; ?>>Licenciado/Ingeniero</option>
                                        <option value="magister" <?php echo ($currentUser['grado_academico'] == 'magister') ? 'selected' : ''; ?>>Magíster</option>
                                        <option value="doctor" <?php echo ($currentUser['grado_academico'] == 'doctor') ? 'selected' : ''; ?>>Doctor</option>
                                    </select>
                                </div>

                            </div>
                        </div>

                        <!-- Responsabilidades como Coordinador -->
                        <div>
                            <h4 class="text-md font-semibold text-texto mb-4">Responsabilidades como Coordinador</h4>
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                <div class="flex items-start">
                                    <i class="fas fa-info-circle text-yellow-600 mr-3 mt-1"></i>
                                    <div>
                                        <h5 class="font-medium text-yellow-800 mb-2">Funciones del Coordinador</h5>
                                        <ul class="text-sm text-yellow-700 space-y-1">
                                            <li>• Gestionar y aprobar planes de prácticas pre-profesionales</li>
                                            <li>• Asignar docentes asesores a estudiantes</li>
                                            <li>• Revisar y aprobar informes finales</li>
                                            <li>• Programar sustentaciones y asignar jurados</li>
                                            <li>• Supervisar el proceso completo de prácticas</li>
                                            <li>• Subir documentos y reglamentos para estudiantes</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Cambio de Contraseña -->
                        <div>
                            <h4 class="text-md font-semibold text-texto mb-4">Cambio de Contraseña</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gris mb-2">Nueva Contraseña</label>
                                    <input type="password" name="nueva_password" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-principal" 
                                           placeholder="Dejar vacío si no desea cambiar">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gris mb-2">Confirmar Contraseña</label>
                                    <input type="password" name="confirmar_password" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-principal" 
                                           placeholder="Confirmar nueva contraseña">
                                </div>
                            </div>
                        </div>

                        <!-- Botón de Envío -->
                        <div class="flex justify-end">
                            <button type="submit" 
                                    class="bg-principal hover:bg-emerald-700 text-white px-6 py-2 rounded-lg transition flex items-center">
                                <i class="fas fa-save mr-2"></i>
                                Guardar Cambios
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </main>

</body>
</html>