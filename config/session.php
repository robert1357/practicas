<?php
// Configuración de seguridad para sesiones
if (session_status() == PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 0); // Cambiar a 1 en producción con HTTPS
    ini_set('session.use_strict_mode', 1);
    
    session_start();
}

// Función para verificar si el usuario está autenticado
function isAuthenticated() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_type']);
}

// Función para verificar el tipo de usuario
function hasRole($role) {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === $role;
}

// Función para obtener datos del usuario actual
function getCurrentUser() {
    if (!isAuthenticated()) {
        return null;
    }
    
    // Si tenemos información completa en la sesión, la usamos
    if (isset($_SESSION['user_complete_data']) && $_SESSION['user_complete_data']) {
        return [
            'id' => $_SESSION['user_id'],
            'email' => $_SESSION['user_email'],
            'nombres' => $_SESSION['user_nombres'],
            'apellidos' => $_SESSION['user_apellidos'],
            'codigo' => $_SESSION['user_codigo'],
            'tipo' => $_SESSION['user_type'],
            'especialidad' => $_SESSION['user_especialidad'] ?? null,
            'dni' => $_SESSION['user_dni'] ?? null,
            'telefono' => $_SESSION['user_telefono'] ?? null,
            'direccion' => $_SESSION['user_direccion'] ?? null,
            'semestre' => $_SESSION['user_semestre'] ?? null,
            'ciclo_academico' => $_SESSION['user_ciclo_academico'] ?? null,
            'categoria_docente' => $_SESSION['user_categoria_docente'] ?? null,
            'dedicacion' => $_SESSION['user_dedicacion'] ?? null,
            'grado_academico' => $_SESSION['user_grado_academico'] ?? null
        ];
    }
    
    // Si no tenemos información completa, la obtenemos de la base de datos
    require_once __DIR__ . '/database.php';
    require_once __DIR__ . '/../models/User.php';
    
    $userModel = new User();
    $userData = $userModel->getById($_SESSION['user_id']);
    
    if ($userData) {
        // Actualizamos la sesión con información completa
        $_SESSION['user_complete_data'] = true;
        $_SESSION['user_dni'] = $userData['dni'];
        $_SESSION['user_telefono'] = $userData['telefono'];
        $_SESSION['user_direccion'] = $userData['direccion'];
        $_SESSION['user_semestre'] = $userData['semestre'];
        $_SESSION['user_ciclo_academico'] = $userData['ciclo_academico'];
        $_SESSION['user_categoria_docente'] = $userData['categoria_docente'];
        $_SESSION['user_dedicacion'] = $userData['dedicacion'];
        $_SESSION['user_grado_academico'] = $userData['grado_academico'];
        
        return [
            'id' => $userData['id'],
            'email' => $userData['email'],
            'nombres' => $userData['nombres'],
            'apellidos' => $userData['apellidos'],
            'codigo' => $userData['codigo'],
            'tipo' => $userData['tipo'],
            'especialidad' => $userData['especialidad'],
            'dni' => $userData['dni'],
            'telefono' => $userData['telefono'],
            'direccion' => $userData['direccion'],
            'semestre' => $userData['semestre'],
            'ciclo_academico' => $userData['ciclo_academico'],
            'categoria_docente' => $userData['categoria_docente'],
            'dedicacion' => $userData['dedicacion'],
            'grado_academico' => $userData['grado_academico']
        ];
    }
    
    // Fallback si no encontramos el usuario
    return [
        'id' => $_SESSION['user_id'],
        'email' => $_SESSION['user_email'],
        'nombres' => $_SESSION['user_nombres'],
        'apellidos' => $_SESSION['user_apellidos'],
        'codigo' => $_SESSION['user_codigo'],
        'tipo' => $_SESSION['user_type'],
        'especialidad' => $_SESSION['user_especialidad'] ?? null
    ];
}

// Función para hacer logout
function logout() {
    session_destroy();
    header("Location: index.php");
    exit();
}

// Manejar logout si se solicita
if (isset($_GET['logout'])) {
    logout();
}

// Función para redirigir según el rol
function redirectToRole($role) {
    switch($role) {
        case 'estudiante':
            header("Location: views/estudiante_dashboard.php");
            break;
        case 'docente':
            header("Location: views/docente_dashboard.php");
            break;
        case 'coordinador':
            header("Location: views/coordinador_dashboard.php");
            break;
        case 'admin':
            header("Location: views/admin_dashboard.php");
            break;
        default:
            header("Location: index.php");
    }
    exit();
}
?>