<?php
require_once '../config/session.php';
require_once '../models/User.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $user = new User();
    $userData = $user->authenticate($email, $password);
    
    if ($userData && $userData['tipo'] == 'admin') {
        $_SESSION['user_id'] = $userData['id'];
        $_SESSION['user_email'] = $userData['email'];
        $_SESSION['user_nombres'] = $userData['nombres'];
        $_SESSION['user_apellidos'] = $userData['apellidos'];
        $_SESSION['user_codigo'] = $userData['codigo'];
        $_SESSION['user_type'] = $userData['tipo'];
        $_SESSION['user_especialidad'] = $userData['especialidad'];
        
        header("Location: admin_dashboard.php");
        exit();
    } else {
        $error = 'Credenciales inválidas o no tiene permisos de administrador';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login Administrador - SYSPRE 2025</title>

  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
  
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            principal: '#d63a3a',
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
      background: linear-gradient(135deg, #d63a3a 0%, #b82e2e 100%);
    }
    .login-card {
      backdrop-filter: blur(10px);
      background: rgba(255, 255, 255, 0.95);
    }
    .input-group {
      position: relative;
    }
    .input-group input:focus + label,
    .input-group input:not(:placeholder-shown) + label {
      transform: translateY(-20px) scale(0.8);
      color: #d63a3a;
    }
    .input-group label {
      position: absolute;
      left: 12px;
      top: 12px;
      transition: all 0.2s ease;
      pointer-events: none;
      color: #64748b;
    }
  </style>
</head>

<body class="gradient-bg min-h-screen flex items-center justify-center p-4">
  <div class="w-full max-w-md">
    <!-- Header -->
    <div class="text-center mb-8">
      <div class="inline-block mb-4">
        <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center shadow-lg">
          <i class="fas fa-user-cog text-3xl text-principal"></i>
        </div>
      </div>
      <h1 class="text-3xl font-bold text-white mb-2">Acceso Administrador</h1>
      <p class="text-red-100">Sistema de Gestión de Prácticas Pre-Profesionales</p>
    </div>

    <!-- Login Form -->
    <div class="login-card rounded-2xl shadow-2xl p-8 mb-6">
      <form method="POST" class="space-y-6">
        
        <?php if ($error): ?>
          <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
          </div>
        <?php endif; ?>

        <!-- Email Input -->
        <div class="input-group">
          <input 
            type="email" 
            name="email" 
            placeholder=" " 
            required 
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-principal focus:border-transparent transition duration-200"
          >
          <label>Email institucional</label>
        </div>

        <!-- Password Input -->
        <div class="input-group">
          <input 
            type="password" 
            name="password" 
            placeholder=" " 
            required 
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-principal focus:border-transparent transition duration-200"
          >
          <label>Contraseña</label>
        </div>

        <!-- Login Button -->
        <button 
          type="submit" 
          class="w-full bg-principal hover:bg-red-600 text-white font-semibold py-3 px-6 rounded-lg transition duration-200 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-principal focus:ring-opacity-50"
        >
          <i class="fas fa-sign-in-alt mr-2"></i>
          Iniciar Sesión
        </button>
      </form>
    </div>

    <!-- Back to Home -->
    <div class="text-center">
      <a href="../index.php" class="text-white hover:text-red-100 transition duration-200">
        <i class="fas fa-arrow-left mr-2"></i>
        Volver al inicio
      </a>
    </div>
  </div>
</body>
</html>