<?php
require_once '../config/session.php';
require_once '../config/google_oauth.php';
require_once '../models/User.php';

// Verificar si hay un código de autorización
if (!isset($_GET['code'])) {
    header('Location: login_estudiante.php?error=google_auth_failed');
    exit();
}

$code = $_GET['code'];

// Obtener el token de acceso (por ahora simulamos)
// En producción necesitarías el client_secret
$userInfo = [
    'email' => 'ejemplo@gmail.com',
    'name' => 'Usuario Google',
    'given_name' => 'Usuario',
    'family_name' => 'Google',
    'picture' => 'https://via.placeholder.com/150'
];

// Verificar si el usuario ya existe
$user = new User();
$existingUser = $user->getUserByEmail($userInfo['email']);

if ($existingUser) {
    // Usuario existe, iniciar sesión
    if ($existingUser['tipo'] == 'estudiante') {
        $_SESSION['user_id'] = $existingUser['id'];
        $_SESSION['user_email'] = $existingUser['email'];
        $_SESSION['user_nombres'] = $existingUser['nombres'];
        $_SESSION['user_apellidos'] = $existingUser['apellidos'];
        $_SESSION['user_codigo'] = $existingUser['codigo'];
        $_SESSION['user_type'] = $existingUser['tipo'];
        $_SESSION['user_especialidad'] = $existingUser['especialidad'];
        $_SESSION['google_auth'] = true;
        
        header('Location: estudiante_dashboard.php');
        exit();
    } else {
        header('Location: login_estudiante.php?error=not_student');
        exit();
    }
} else {
    // Usuario no existe, crear perfil parcial y redirigir a completar perfil
    $tempData = [
        'email' => $userInfo['email'],
        'nombres' => $userInfo['given_name'],
        'apellidos' => $userInfo['family_name'],
        'google_auth' => true
    ];
    
    $_SESSION['temp_google_data'] = $tempData;
    header('Location: completar_perfil_google.php');
    exit();
}
?>