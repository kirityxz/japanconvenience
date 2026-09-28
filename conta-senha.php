<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
requireLogin();

$stmt = $pdo->prepare("SELECT senha FROM usuarios WHERE id = ?");
$stmt->execute([$_SESSION['usuario_id']]);
$temSenha = !empty($stmt->fetchColumn());

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $atual = $_POST['atual'] ?? '';
    $nova = $_POST['nova'] ?? '';
    $nova2 = $_POST['nova2'] ?? '';
    $stmt = $pdo->prepare("SELECT senha FROM usuarios WHERE id = ?");
    $stmt->execute([$_SESSION['usuario_id']]);
    $hash = $stmt->fetchColumn();
    if ($temSenha && !password_verify($atual, (string)$hash)) {
        $_SESSION['erro'] = 'Senha atual incorreta.';
    } elseif (!senhaForte($nova)) {
        $_SESSION['erro'] = 'A nova senha precisa de 8+ caracteres, com maiúscula, minúscula, número e símbolo.';
    } elseif ($nova !== $nova2) {
        $_SESSION['erro'] = 'As novas senhas não conferem.';
    } else {
        $pdo->prepare("UPDATE usuarios SET senha = ? WHERE id = ?")
            ->execute([password_hash($nova, PASSWORD_DEFAULT), $_SESSION['usuario_id']]);
        $_SESSION['sucesso'] = $temSenha ? 'Senha alterada!' : 'Senha criada! Agora você também entra com e-mail e senha.';
    }
    header('Location: conta-senha.php'); exit;
}

include 'includes/header.php';
?>

<section class="container" style="margin-top:18px;max-width:640px">
  <p class="small text-muted mb-2"><a href="conta.php" class="text-decoration-none text-muted">Minha conta</a> &nbsp;/&nbsp; <span>Trocar senha</span></p>
  <div class="section-heading">
    <div><span class="section-mark"></span><h2><?= $temSenha ? 'Trocar senha' : 'Criar senha' ?></h2></div>
  </div>
  <div class="adm-card">
    <?php if (!$temSenha): ?>
      <div class="alert alert-info small">Sua conta entrou via Google e ainda não tem senha. Crie uma para entrar também com e-mail e senha.</div>
    <?php endif; ?>
    <form method="POST">
      <?php if ($temSenha): ?>
        <label class="small text-muted">Senha atual</label>
        <input type="password" name="atual" class="form-control mb-3" required autocomplete="current-password">
      <?php endif; ?>
      <label class="small text-muted">Nova senha</label>
      <input type="password" name="nova" class="form-control mb-1" required minlength="8" autocomplete="new-password">
      <p class="pass-hint">Mín. 8 caracteres, com maiúscula, minúscula, número e símbolo.</p>
      <label class="small text-muted">Confirmar nova senha</label>
      <input type="password" name="nova2" class="form-control mb-3" required autocomplete="new-password">
      <button class="jc-btn-red w-100">Salvar nova senha</button>
    </form>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
