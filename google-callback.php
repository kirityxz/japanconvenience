<?php
// Etapa 2 do login com Google: recebe o código, valida e entra/cria a conta
require_once 'config/database.php';
require_once 'includes/functions.php';
if (file_exists(__DIR__ . '/config/google.php')) require_once 'config/google.php';
if (!function_exists('googleConfigurado')) { function googleConfigurado() { return false; } }

function googleFalha($msg) {
    unset($_SESSION['google_oauth_state']);
    $_SESSION['erro'] = $msg;
    header('Location: login.php');
    exit;
}

if (!googleConfigurado()) googleFalha('Login com Google ainda não configurado. Use e-mail e senha.');
if (isLogged()) { header('Location: index.php'); exit; }

// Usuário negou na tela do Google
if (isset($_GET['error'])) googleFalha('Login com Google cancelado. Tente de novo ou use e-mail e senha.');

// Confere token anti-CSRF + código
$state = $_GET['state'] ?? '';
$code = $_GET['code'] ?? '';
if ($state === '' || $code === '' || !isset($_SESSION['google_oauth_state']) || !hash_equals($_SESSION['google_oauth_state'], $state)) {
    googleFalha('Sessão inválida. Tente entrar com o Google de novo.');
}

// Troca o código por token de acesso (cURL)
$ch = curl_init('https://oauth2.googleapis.com/token');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_TIMEOUT => 15,
    CURLOPT_POSTFIELDS => http_build_query([
        'code' => $code,
        'client_id' => GOOGLE_CLIENT_ID,
        'client_secret' => GOOGLE_CLIENT_SECRET,
        'redirect_uri' => GOOGLE_REDIRECT_URI,
        'grant_type' => 'authorization_code',
    ]),
]);
$resp = curl_exec($ch);
$http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
$tok = json_decode((string)$resp, true);
if ($http !== 200 || empty($tok['access_token'])) googleFalha('Google não autorizou. Tente de novo.');

// Busca os dados do perfil
$ch = curl_init('https://openidconnect.googleapis.com/v1/userinfo');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 15,
    CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $tok['access_token']],
]);
$resp = curl_exec($ch);
$http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
$perfil = json_decode((string)$resp, true);

$gid = $perfil['sub'] ?? '';
$email = trim($perfil['email'] ?? '');
$nome = trim($perfil['name'] ?? '');
if ($gid === '' || $email === '' || !emailValido($email)) googleFalha('Google não retornou um e-mail válido.');
if (($perfil['email_verified'] ?? true) !== true && ($perfil['verified_email'] ?? true) !== true) {
    googleFalha('Confirme seu e-mail no Google antes de entrar.');
}
if ($nome === '') $nome = explode('@', $email)[0];
$nome = mb_substr($nome, 0, 100);

try {
    // 1) Já vinculado pelo google_id? Entra direto
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE google_id = ?");
    $stmt->execute([$gid]);
    $u = $stmt->fetch();

    // 2) E-mail já cadastrado com senha? Vincula a conta Google (o Google prova que o e-mail é dele)
    if (!$u) {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $u = $stmt->fetch();
        if ($u) {
            $pdo->prepare("UPDATE usuarios SET google_id = ? WHERE id = ?")->execute([$gid, $u['id']]);
        }
    }

    // 3) Conta nova: cria como cliente, sem senha (só entra via Google)
    if (!$u) {
        $pdo->prepare("INSERT INTO usuarios (nome, email, senha, google_id, tipo) VALUES (?, ?, NULL, ?, 'cliente')")
            ->execute([$nome, $email, $gid]);
        $u = ['id' => $pdo->lastInsertId(), 'nome' => $nome, 'tipo' => 'cliente'];
    }

    unset($_SESSION['google_oauth_state']);
    $_SESSION['usuario_id'] = $u['id'];
    $_SESSION['usuario_nome'] = $u['nome'];
    $_SESSION['usuario_tipo'] = $u['tipo'];
    $_SESSION['sucesso'] = 'Bem-vindo(a), ' . explode(' ', $u['nome'])[0] . '.';
    header('Location: ' . ($u['tipo'] === 'admin' ? 'admin/index.php' : 'index.php'));
    exit;
} catch (Exception $e) {
    googleFalha('Erro ao entrar com o Google. Tente de novo.');
}
