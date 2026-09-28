<?php
// Etapa 1 do login com Google: redireciona para o Google
require_once 'config/database.php';
require_once 'includes/functions.php';
if (file_exists(__DIR__ . '/config/google.php')) require_once 'config/google.php';
if (!function_exists('googleConfigurado')) { function googleConfigurado() { return false; } }

if (!googleConfigurado()) {
    $_SESSION['erro'] = 'Login com Google ainda não configurado. Use e-mail e senha.';
    header('Location: login.php');
    exit;
}
if (isLogged()) { header('Location: index.php'); exit; }

// Token anti-CSRF (conferido no callback)
$state = bin2hex(random_bytes(16));
$_SESSION['google_oauth_state'] = $state;

$params = [
    'client_id' => GOOGLE_CLIENT_ID,
    'redirect_uri' => GOOGLE_REDIRECT_URI,
    'response_type' => 'code',
    'scope' => 'openid email profile',
    'state' => $state,
    'prompt' => 'select_account',
];

header('Location: https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params));
exit;
