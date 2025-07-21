<?php
// Configuración de Google OAuth
define('GOOGLE_CLIENT_ID', '213185857577-rf458dhq0ra8f3oo0pk5gidgp75rs54s.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', ''); // Necesario para completar el flujo
define('GOOGLE_REDIRECT_URI', 'http://localhost:5001/views/google_callback.php');

// Función para obtener la URL de autorización de Google
function getGoogleAuthURL() {
    $params = [
        'client_id' => GOOGLE_CLIENT_ID,
        'redirect_uri' => GOOGLE_REDIRECT_URI,
        'response_type' => 'code',
        'scope' => 'openid email profile',
        'access_type' => 'offline',
        'prompt' => 'select_account'
    ];
    
    return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
}

// Función para obtener el token de acceso
function getGoogleAccessToken($code) {
    $url = 'https://oauth2.googleapis.com/token';
    
    $data = [
        'client_id' => GOOGLE_CLIENT_ID,
        'client_secret' => GOOGLE_CLIENT_SECRET,
        'code' => $code,
        'grant_type' => 'authorization_code',
        'redirect_uri' => GOOGLE_REDIRECT_URI
    ];
    
    $options = [
        'http' => [
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data)
        ]
    ];
    
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    
    return json_decode($result, true);
}

// Función para obtener información del usuario de Google
function getGoogleUserInfo($accessToken) {
    $url = 'https://www.googleapis.com/oauth2/v2/userinfo?access_token=' . $accessToken;
    
    $result = file_get_contents($url);
    return json_decode($result, true);
}

// Función para simular login con Google (para demostración)
function simulateGoogleLogin($email) {
    // Simular datos de Google para demostración
    $parts = explode('@', $email);
    $name = $parts[0];
    
    return [
        'email' => $email,
        'name' => ucfirst($name),
        'given_name' => ucfirst($name),
        'family_name' => 'Google User',
        'picture' => 'https://via.placeholder.com/150',
        'id' => 'google_' . time()
    ];
}
?>