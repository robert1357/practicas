<?php
require_once '../config/session.php';
require_once '../models/User.php';

// Verificar autenticación
if (!isAuthenticated() || !hasRole('estudiante')) {
    header("Location: login_estudiante.php");
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
        'semestre' => $_POST['semestre'],
        'ciclo_academico' => $_POST['ciclo_academico']
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
                <a href="estudiante_dashboard.php" class="text-principal hover:text-emerald-700 transition">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Volver al Dashboard
                </a>
                <div class="text-xl font-bold text-texto">
                    <i class="fas fa-user-circle mr-2"></i>
                    Mi Perfil
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <span class="text-gris">
                    <i class="fas fa-user mr-1"></i>
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
                        <div class="bg-principal text-white w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-user-graduate text-3xl"></i>
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
                            <i class="fas fa-calendar-week w-5 mr-2 text-gris"></i>
                            <span class="text-gris">Semestre: <?php echo htmlspecialchars($currentUser['semestre'] ?? 'No especificado'); ?></span>
                        </div>
                        <div class="flex items-center text-sm">
                            <i class="fas fa-calendar-alt w-5 mr-2 text-gris"></i>
                            <span class="text-gris">Ciclo: <?php echo htmlspecialchars($currentUser['ciclo_academico'] ?? 'No especificado'); ?></span>
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

                        <!-- Información Académica -->
                        <div>
                            <h4 class="text-md font-semibold text-texto mb-4">Información Académica</h4>
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
                                    <label class="block text-sm font-medium text-gris mb-2">Semestre *</label>
                                    <select name="semestre" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-principal" required>
                                        <?php for($i = 1; $i <= 10; $i++): ?>
                                            <option value="<?php echo $i; ?>" <?php echo (($currentUser['semestre'] ?? '') == $i) ? 'selected' : ''; ?>>
                                                <?php echo $i; ?> Semestre
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gris mb-2">Ciclo Académico *</label>
                                    <input type="text" name="ciclo_academico" value="<?php echo htmlspecialchars($currentUser['ciclo_academico'] ?? ''); ?>" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-principal" 
                                           placeholder="ej: 2025-I" required>
                                </div>

                            </div>
                        </div>

                        <!-- Cambio de Contraseña -->
                        <div>
                            <h4 class="text-md font-semibold text-texto mb-4">Cambiar Contraseña (Opcional)</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                
                                <div>
                                    <label class="block text-sm font-medium text-gris mb-2">Nueva Contraseña</label>
                                    <input type="password" name="nueva_password" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-principal">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gris mb-2">Confirmar Nueva Contraseña</label>
                                    <input type="password" name="confirmar_password" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-principal">
                                </div>

                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="flex space-x-4 pt-4">
                            <button type="submit" 
                                    class="bg-principal hover:bg-emerald-700 text-white px-6 py-2 rounded-lg transition">
                                <i class="fas fa-save mr-2"></i>Guardar Cambios
                            </button>
                            <a href="estudiante_dashboard.php" 
                               class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition">
                                <i class="fas fa-times mr-2"></i>Cancelar
                            </a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </main>
</body>
</html>