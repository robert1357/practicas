<?php
require_once '../config/google_oauth.php';

// Redirigir a Google para la autenticación
$authUrl = getGoogleAuthURL();
header('Location: ' . $authUrl);
exit();
?>