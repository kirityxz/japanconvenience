<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
// google.php não vai pro GitHub (.gitignore) — sem ele, o botão mostra "em breve"
if (file_exists(__DIR__ . '/config/google.php')) require_once 'config/google.php';
if (!function_exists('googleConfigurado')) { function googleConfigurado() { return false; } }

if (isLogged()) { header('Location: index.php'); exit; }

$erroLogin = ''; $erroCad = '';
$old = ['email' => '', 'nome' => '', 'email_cad' => ''];

// ---------- LOGIN ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'login') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $old['email'] = $email;
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $u = $stmt->fetch();
    if ($u && !empty($u['senha']) && password_verify($senha, $u['senha'])) {
        $_SESSION['usuario_id'] = $u['id'];
        $_SESSION['usuario_nome'] = $u['nome'];
        $_SESSION['usuario_tipo'] = $u['tipo'];
        $_SESSION['sucesso'] = 'Bem-vindo(a) de volta, ' . explode(' ', $u['nome'])[0] . '.';
        header('Location: ' . ($u['tipo'] === 'admin' ? 'admin/index.php' : 'index.php'));
        exit;
    }
    $erroLogin = 'E-mail ou senha inválidos!';
}

// ---------- CADASTRO ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'cadastro') {
    $nome  = trim(preg_replace('/\s+/', ' ', $_POST['nome'] ?? ''));
    $email = trim($_POST['email_cad'] ?? '');
    $senha = $_POST['senha_cad'] ?? '';
    $senha2 = $_POST['senha_cad2'] ?? '';
    $old['nome'] = $nome; $old['email_cad'] = $email;
    if (!nomeValido($nome)) $erroCad = 'Informe seu nome completo usando apenas letras.';
    elseif (!emailValido($email)) $erroCad = 'E-mail inválido. Use um e-mail real (ex: voce@gmail.com).';
    elseif (!senhaForte($senha)) $erroCad = 'A senha precisa de 8+ caracteres, com maiúscula, minúscula, número e símbolo.';
    elseif ($senha !== $senha2) $erroCad = 'As senhas não conferem.';
    else {
        $check = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) $erroCad = 'Este e-mail já está cadastrado. Faça login ao lado.';
        else {
            $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, tipo) VALUES (?,?,?, 'cliente')");
            $stmt->execute([$nome, $email, password_hash($senha, PASSWORD_DEFAULT)]);
            $_SESSION['usuario_id'] = $pdo->lastInsertId();
            $_SESSION['usuario_nome'] = $nome;
            $_SESSION['usuario_tipo'] = 'cliente';
            $_SESSION['sucesso'] = 'Conta criada com sucesso. Boas compras.';
            header('Location: index.php'); exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Entrar ou Cadastrar — JapanConvenience</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="/JapanConvenience/assets/css/style.css?v=17">
</head>
<body>
<div class="login-split">

  <!-- ESQUERDA: foto do konbini -->
  <aside class="login-photo">
    <a href="/JapanConvenience/index.php" class="login-brand">
      <span class="login-j">J</span>
      <span><b>JapanConvenience</b><small>LOJA JAPONESA ONLINE</small></span>
    </a>
    <div class="login-photo-text">
      <p class="login-kicker"><span></span>PRODUTOS JAPONESES AUTÊNTICOS</p>
      <h2>Os melhores sabores tradicionais e modernos <span>do Japão.</span></h2>
      <p>Entre na sua conta para garantir descontos exclusivos, entrega rápida com frete reduzido para o Brasil e prêmios a cada compra.</p>
    </div>
  </aside>

  <!-- DIREITA: acesso -->
  <main class="login-side">
    <div class="login-box">
      <span class="login-j login-j-center">J</span>
      <h1>Seja bem-vindo(a)!</h1>
      <p class="login-sub">Faça login ou crie sua conta para continuar suas compras.</p>

      <?php if (isset($_GET['erro']) && $_GET['erro'] === 'restrito'): ?>
        <div class="alert alert-warning py-2 small">Área restrita ao administrador. Faça login.</div>
      <?php endif; ?>

      <div class="login-card glass">
        <!-- LOGIN -->
        <div class="login-col">
          <h3>
            <span class="login-ico">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </span>
            Já sou cliente
          </h3>
          <p>Entre com seu e-mail e senha para acessar sua conta.</p>
          <?php if ($erroLogin): ?><div class="alert alert-danger small py-2"><?= e($erroLogin) ?></div><?php endif; ?>
          <form method="POST">
            <input type="hidden" name="acao" value="login">
            <div class="field">
              <svg class="f-left" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-10 6L2 7"/></svg>
              <input type="email" name="email" placeholder="E-mail" required value="<?= e($old['email']) ?>">
            </div>
            <div class="field">
              <svg class="f-left" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="18" height="11" x="3" y="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
              <input type="password" name="senha" id="senha-login" placeholder="Senha" required>
              <button type="button" class="f-eye" data-toggle-pass="senha-login" aria-label="Mostrar senha">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
              </button>
            </div>
            <div class="login-row">
              <label class="remember"><input type="checkbox" name="lembrar" value="1" checked> Lembrar de mim</label>
              <a href="#" class="forgot">Esqueceu sua senha?</a>
            </div>
            <button class="login-btn-red">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
              Entrar na conta
            </button>
          </form>
          <div class="login-div"><span></span>ou<span></span></div>
          <?php if (googleConfigurado()): ?>
          <a class="login-google" href="google-login.php">
            <svg width="17" height="17" viewBox="0 0 24 24" aria-hidden="true"><path fill="#4285F4" d="M23.5 12.3c0-.9-.1-1.5-.3-2.3H12v4.3h6.5c-.1 1.1-.8 2.7-2.4 3.8l3.6 2.8c2.2-2 3.8-5 3.8-8.6z"/><path fill="#34A853" d="M12 24c3.2 0 6-1.1 8-2.9l-3.9-3c-1 .7-2.5 1.2-4.1 1.2-3.1 0-5.8-2.1-6.8-5l-3.7 2.9C3.5 21.4 7.5 24 12 24z"/><path fill="#FBBC05" d="M5.2 14.3c-.2-.7-.4-1.5-.4-2.3s.1-1.6.4-2.3L1.4 6.9C.5 8.7 0 10.2 0 12s.5 3.3 1.4 4.7l3.8-2.4z"/><path fill="#EA4335" d="M12 4.7c1.8 0 3 .8 3.7 1.4l3.3-3.2C17.9 1.1 15.2 0 12 0 7.5 0 3.5 2.6 1.4 6.3l3.8 3c1-2.9 3.7-4.6 6.8-4.6z"/></svg>
            Continuar com o Google
          </a>
          <?php else: ?>
          <button type="button" class="login-google" onclick="showToast('Login com Google em breve. Use e-mail e senha.')">
            <svg width="17" height="17" viewBox="0 0 24 24" aria-hidden="true"><path fill="#4285F4" d="M23.5 12.3c0-.9-.1-1.5-.3-2.3H12v4.3h6.5c-.1 1.1-.8 2.7-2.4 3.8l3.6 2.8c2.2-2 3.8-5 3.8-8.6z"/><path fill="#34A853" d="M12 24c3.2 0 6-1.1 8-2.9l-3.9-3c-1 .7-2.5 1.2-4.1 1.2-3.1 0-5.8-2.1-6.8-5l-3.7 2.9C3.5 21.4 7.5 24 12 24z"/><path fill="#FBBC05" d="M5.2 14.3c-.2-.7-.4-1.5-.4-2.3s.1-1.6.4-2.3L1.4 6.9C.5 8.7 0 10.2 0 12s.5 3.3 1.4 4.7l3.8-2.4z"/><path fill="#EA4335" d="M12 4.7c1.8 0 3 .8 3.7 1.4l3.3-3.2C17.9 1.1 15.2 0 12 0 7.5 0 3.5 2.6 1.4 6.3l3.8 3c1-2.9 3.7-4.6 6.8-4.6z"/></svg>
            Continuar com o Google
          </button>
          <?php endif; ?>
        </div>

        <div class="login-sep" aria-hidden="true"></div>

        <!-- CADASTRO -->
        <div class="login-col" id="nova">
          <h3>
            <span class="login-ico">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 21a8 8 0 0 1 13.3-6"/><circle cx="10" cy="8" r="5"/><path d="M19 16v6"/><path d="M22 19h-6"/></svg>
            </span>
            Criar Nova Conta
          </h3>
          <p>Cadastre-se em poucos minutos e aproveite todos os benefícios da JapanConvenience.</p>
          <?php if ($erroCad): ?><div class="alert alert-danger small py-2"><?= e($erroCad) ?></div><?php endif; ?>
          <form method="POST" id="form-cadastro">
            <input type="hidden" name="acao" value="cadastro">
            <div class="field">
              <svg class="f-left" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
              <input name="nome" id="nome" placeholder="Nome completo" required value="<?= e($old['nome']) ?>">
            </div>
            <div class="field">
              <svg class="f-left" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-10 6L2 7"/></svg>
              <input type="email" name="email_cad" placeholder="E-mail" required value="<?= e($old['email_cad']) ?>">
            </div>
            <div class="field">
              <svg class="f-left" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="18" height="11" x="3" y="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
              <input type="password" name="senha_cad" id="senha" placeholder="Criar senha" required minlength="8" autocomplete="new-password">
              <button type="button" class="f-eye" data-toggle-pass="senha" aria-label="Mostrar senha">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
              </button>
            </div>
            <p class="pass-hint">Mín. 8 caracteres, com maiúscula, minúscula, número e símbolo.</p>
            <div class="field">
              <svg class="f-left" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="18" height="11" x="3" y="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
              <input type="password" name="senha_cad2" id="senha2" placeholder="Confirmar senha" required>
              <button type="button" class="f-eye" data-toggle-pass="senha2" aria-label="Mostrar senha">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
              </button>
            </div>
            <button class="login-btn-dark">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 21a8 8 0 0 1 13.3-6"/><circle cx="10" cy="8" r="5"/><path d="M19 16v6"/><path d="M22 19h-6"/></svg>
              Cadastrar e Comprar
            </button>
          </form>
        </div>
      </div>

      <p class="login-safe">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="18" height="11" x="3" y="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        Seus dados estão seguros conosco.
      </p>
      <details class="login-demo"><summary>Acesso demo</summary>Admin: admin@japanconvenience.com / admin123<br>Cliente: cliente@teste.com / 123456</details>
      <p class="login-back"><a href="/JapanConvenience/index.php">← Voltar à loja</a></p>
    </div>
  </main>
</div>
<div class="toast" id="toast" role="status" aria-live="polite"></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/JapanConvenience/assets/js/main.js?v=6"></script>
</body>
</html>







