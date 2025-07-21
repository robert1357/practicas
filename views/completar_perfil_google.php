<?php
require_once '../config/session.php';
require_once '../models/User.php';

// Verificar que hay datos temporales de Google
if (!isset($_SESSION['temp_google_data'])) {
    header('Location: login_estudiante.php');
    exit();
}

$googleData = $_SESSION['temp_google_data'];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $codigo = trim($_POST['codigo']);
    $escuela = trim($_POST['escuela']);
    $telefono = trim($_POST['telefono']);
    $direccion = trim($_POST['direccion']);
    $especialidad = trim($_POST['especialidad']);
    
    // Validaciones
    if (empty($codigo) || empty($escuela) || empty($telefono) || empty($direccion) || empty($especialidad)) {
        $error = 'Todos los campos son obligatorios';
    } else {
        // Crear el usuario completo
        $user = new User();
        
        // Verificar que el código no exista
        $existingCode = $user->getUserByCode($codigo);
        if ($existingCode) {
            $error = 'El código de estudiante ya está registrado';
        } else {
            // Crear usuario con datos de Google + datos adicionales
            $userData = [
                'email' => $googleData['email'],
                'nombres' => $googleData['nombres'],
                'apellidos' => $googleData['apellidos'],
                'codigo' => $codigo,
                'escuela' => $escuela,
                'telefono' => $telefono,
                'direccion' => $direccion,
                'especialidad' => $especialidad,
                'tipo' => 'estudiante',
                'google_auth' => true
            ];
            
            $userId = $user->createGoogleUser($userData);
            
            if ($userId) {
                // Iniciar sesión automáticamente
                $_SESSION['user_id'] = $userId;
                $_SESSION['user_email'] = $googleData['email'];
                $_SESSION['user_nombres'] = $googleData['nombres'];
                $_SESSION['user_apellidos'] = $googleData['apellidos'];
                $_SESSION['user_codigo'] = $codigo;
                $_SESSION['user_type'] = 'estudiante';
                $_SESSION['user_especialidad'] = $especialidad;
                $_SESSION['google_auth'] = true;
                
                // Limpiar datos temporales
                unset($_SESSION['temp_google_data']);
                
                header('Location: estudiante_dashboard.php');
                exit();
            } else {
                $error = 'Error al crear el usuario';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Completar Perfil - SYSPRE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    
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
    
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #3eb489 0%, #2d8659 100%);
        }
        .login-card {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
        }
    </style>
</head>

<body class="gradient-bg min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-2xl">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="inline-block mb-4">
                <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center shadow-lg">
                    <i class="fab fa-google text-3xl text-red-500"></i>
                </div>
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">Completar Perfil</h1>
            <p class="text-green-100">Ingresa la información adicional para completar tu registro</p>
        </div>

        <!-- Form -->
        <div class="login-card rounded-2xl shadow-2xl p-8 mb-6">
            <!-- Datos de Google -->
            <div class="mb-6 p-4 bg-green-50 rounded-lg border border-green-200">
                <h3 class="font-semibold text-green-800 mb-2">
                    <i class="fab fa-google mr-2"></i>
                    Datos de Google
                </h3>
                <p class="text-sm text-green-700">
                    <strong>Email:</strong> <?php echo htmlspecialchars($googleData['email']); ?>
                </p>
                <p class="text-sm text-green-700">
                    <strong>Nombre:</strong> <?php echo htmlspecialchars($googleData['nombres'] . ' ' . $googleData['apellidos']); ?>
                </p>
            </div>

            <form method="POST" class="space-y-6">
                <?php if ($error): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <!-- DNI para autocompletar nombres -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-id-card-alt mr-2"></i>
                        DNI (para verificar identidad)
                    </label>
                    <div class="flex gap-2">
                        <input 
                            type="text" 
                            id="dni" 
                            name="dni" 
                            placeholder="Ej: 12345678"
                            maxlength="8"
                            class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-principal"
                        >
                        <button 
                            type="button" 
                            id="consultarDNI"
                            class="px-4 py-3 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition duration-200"
                        >
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Se verificarán automáticamente los nombres con RENIEC</p>
                </div>

                <!-- Nombres actualizados desde Google/RENIEC -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-user mr-2"></i>
                            Nombres
                        </label>
                        <input 
                            type="text" 
                            id="nombres" 
                            name="nombres" 
                            value="<?php echo htmlspecialchars($googleData['nombres']); ?>"
                            readonly
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50 focus:outline-none"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-user mr-2"></i>
                            Apellidos
                        </label>
                        <input 
                            type="text" 
                            id="apellidos" 
                            name="apellidos" 
                            value="<?php echo htmlspecialchars($googleData['apellidos']); ?>"
                            readonly
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50 focus:outline-none"
                        >
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Código de estudiante -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-id-card mr-2"></i>
                            Código de Estudiante
                        </label>
                        <input 
                            type="text" 
                            name="codigo" 
                            placeholder="Ej: 2020001234"
                            required 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-principal"
                        >
                    </div>

                    <!-- Escuela Profesional -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-graduation-cap mr-2"></i>
                            Escuela Profesional
                        </label>
                        <select 
                            name="escuela" 
                            required 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-principal"
                        >
                            <option value="">Seleccionar escuela</option>
                            <option value="Ingeniería de Sistemas">Ingeniería de Sistemas</option>
                            <option value="Ingeniería Civil">Ingeniería Civil</option>
                            <option value="Ingeniería Industrial">Ingeniería Industrial</option>
                            <option value="Medicina">Medicina</option>
                            <option value="Enfermería">Enfermería</option>
                            <option value="Administración">Administración</option>
                            <option value="Contabilidad">Contabilidad</option>
                            <option value="Derecho">Derecho</option>
                            <option value="Educación">Educación</option>
                            <option value="Psicología">Psicología</option>
                        </select>
                    </div>

                    <!-- Teléfono -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-phone mr-2"></i>
                            Teléfono
                        </label>
                        <input 
                            type="tel" 
                            name="telefono" 
                            placeholder="Ej: 951234567"
                            required 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-principal"
                        >
                    </div>

                    <!-- Especialidad -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-star mr-2"></i>
                            Especialidad
                        </label>
                        <input 
                            type="text" 
                            name="especialidad" 
                            placeholder="Ej: Desarrollo de Software"
                            required 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-principal"
                        >
                    </div>
                </div>

                <!-- Dirección -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-map-marker-alt mr-2"></i>
                        Dirección
                    </label>
                    <textarea 
                        name="direccion" 
                        placeholder="Ingresa tu dirección completa"
                        required 
                        rows="3"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-principal"
                    ></textarea>
                </div>

                <!-- Botón de envío -->
                <button 
                    type="submit" 
                    class="w-full bg-principal hover:bg-green-600 text-white font-semibold py-3 px-6 rounded-lg transition duration-200 transform hover:scale-105"
                >
                    <i class="fas fa-check mr-2"></i>
                    Completar Registro
                </button>
            </form>
        </div>

        <!-- Back to login -->
        <div class="text-center">
            <a href="login_estudiante.php" class="text-white hover:text-green-100 transition duration-200">
                <i class="fas fa-arrow-left mr-2"></i>
                Volver al login
            </a>
        </div>
    </div>

    <script>
        document.getElementById('consultarDNI').addEventListener('click', function() {
            const dni = document.getElementById('dni').value;
            const button = this;
            
            if (dni.length !== 8) {
                alert('Por favor ingrese un DNI válido de 8 dígitos');
                return;
            }
            
            // Mostrar loading
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            button.disabled = true;
            
            // Consultar API de RENIEC
            fetch(`../api/reniec_api.php?dni=${dni}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Actualizar campos con datos de RENIEC
                        document.getElementById('nombres').value = data.nombres;
                        document.getElementById('apellidos').value = data.apellidos;
                        
                        // Mostrar mensaje de éxito
                        const successMsg = document.createElement('div');
                        successMsg.className = 'mt-2 p-2 bg-green-100 border border-green-300 text-green-700 rounded text-sm';
                        successMsg.innerHTML = '<i class="fas fa-check-circle mr-1"></i> Datos verificados con RENIEC';
                        document.getElementById('dni').parentNode.parentNode.appendChild(successMsg);
                        
                        // Remover mensaje después de 3 segundos
                        setTimeout(() => {
                            successMsg.remove();
                        }, 3000);
                        
                    } else {
                        // Mostrar error
                        const errorMsg = document.createElement('div');
                        errorMsg.className = 'mt-2 p-2 bg-red-100 border border-red-300 text-red-700 rounded text-sm';
                        errorMsg.innerHTML = '<i class="fas fa-exclamation-triangle mr-1"></i> ' + (data.error || 'Error al consultar DNI');
                        document.getElementById('dni').parentNode.parentNode.appendChild(errorMsg);
                        
                        // Remover mensaje después de 3 segundos
                        setTimeout(() => {
                            errorMsg.remove();
                        }, 3000);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al consultar DNI. Intente nuevamente.');
                })
                .finally(() => {
                    // Restaurar botón
                    button.innerHTML = '<i class="fas fa-search"></i>';
                    button.disabled = false;
                });
        });
        
        // Permitir consulta con Enter
        document.getElementById('dni').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('consultarDNI').click();
            }
        });
        
        // Validar solo números en DNI
        document.getElementById('dni').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    </script>
</body>
</html>