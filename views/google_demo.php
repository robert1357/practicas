<?php
require_once '../config/session.php';
require_once '../config/google_oauth.php';

$step = $_GET['step'] ?? '1';
$email = $_GET['email'] ?? '';

// Simular el proceso de Google OAuth
if ($step == '2' && $email) {
    $userInfo = simulateGoogleLogin($email);
    
    // Guardar datos temporales para demostración
    $_SESSION['temp_google_data'] = [
        'email' => $userInfo['email'],
        'nombres' => $userInfo['given_name'],
        'apellidos' => $userInfo['family_name'],
        'google_auth' => true
    ];
    
    header('Location: completar_perfil_google.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Google OAuth Demo - SYSPRE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #3eb489 0%, #2d8659 100%);
        }
        .demo-card {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
        }
    </style>
</head>

<body class="gradient-bg min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-lg">
        <div class="demo-card rounded-2xl shadow-2xl p-8">
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fab fa-google text-2xl text-red-500"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Google    </h2>
                <p class="text-gray-600">Proceso de autenticación con Google</p>
            </div>

            <?php if ($step == '1'): ?>
                <div class="space-y-4">
                    

                    <form class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Email de Google (para demostración)
                            </label>
                            <input 
                                type="email" 
                                name="email" 
                                placeholder="ejemplo@gmail.com"
                                required 
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                            >
                        </div>

                        <button 
                            type="submit" 
                            name="step" 
                            value="2"
                            class="w-full bg-red-500 hover:bg-red-600 text-white font-semibold py-3 px-6 rounded-lg transition duration-200 flex items-center justify-center"
                        >
                            <i class="fab fa-google mr-2"></i>
                            Simular Login con Google
                        </button>
                    </form>

                    <div class="mt-6 pt-4 border-t border-gray-200">
                        <h4 class="font-semibold text-gray-700 mb-2">¿Cómo funciona?</h4>
                        <div class="space-y-2 text-sm text-gray-600">
                            <p><strong>1.</strong> Usuario hace clic en "Iniciar con Google"</p>
                            <p><strong>2.</strong> Se redirige a Google para autenticación</p>
                            <p><strong>3.</strong> Google devuelve datos básicos (email, nombre)</p>
                            <p><strong>4.</strong> Sistema pide datos adicionales (código, escuela, etc.)</p>
                            <p><strong>5.</strong> Se crea el usuario completo en la base de datos</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="mt-6 text-center">
                <a href="login_estudiante.php" class="text-green-600 hover:text-green-700 font-semibold">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Volver al login
                </a>
            </div>
        </div>
    </div>
</body>
</html>