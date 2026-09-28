<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
requireLogin();

$stmt = $pdo->prepare("SELECT nome, email, telefone FROM usuarios WHERE id = ?");
$stmt->execute([$_SESSION['usuario_id']]);
$eu = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim(preg_replace('/\s+/', ' ', $_POST['nome'] ?? ''));
    $tel = preg_replace('/\D/', '', $_POST['telefone'] ?? '');
    if (!nomeValido($nome)) {
        $_SESSION['erro'] = 'Informe seu nome completo usando apenas letras.';
    } elseif ($tel !== '' && (strlen($tel) < 10 || strlen($tel) > 11)) {
        $_SESSION['erro'] = 'Telefone inválido. Use DDD + número (10 ou 11 dígitos).';
    } else {
        $pdo->prepare("UPDATE usuarios SET nome = ?, telefone = ? WHERE id = ?")
            ->execute([$nome, $tel === '' ? null : $tel, $_SESSION['usuario_id']]);
        $_SESSION['usuario_nome'] = $nome;
        $_SESSION['sucesso'] = 'Cadastro atualizado!';
    }
    header('Location: conta-dados.php'); exit;
}

include 'includes/header.php';
?>

<section class="container" style="margin-top:18px;max-width:640px">
  <p class="small text-muted mb-2"><a href="conta.php" class="text-decoration-none text-muted">Minha conta</a> &nbsp;/&nbsp; <span>Meu cadastro</span></p>
  <div class="section-heading">
    <div><span class="section-mark"></span><h2>Meu cadastro</h2></div>
  </div>
  <div class="adm-card">
    <form method="POST">
      <label class="small text-muted">Nome completo</label>
      <input name="nome" class="form-control mb-3" required value="<?= e($eu['nome']) ?>">
      <label class="small text-muted">E-mail (não pode ser alterado)</label>
      <input class="form-control mb-3" disabled value="<?= e($eu['email']) ?>">
      <label class="small text-muted">Telefone / WhatsApp (só números, com DDD)</label>
      <input name="telefone" class="form-control mb-3" inputmode="numeric" maxlength="11" placeholder="11999990000" value="<?= e($eu['telefone'] ?? '') ?>">
      <button class="jc-btn-red w-100">Salvar alterações</button>
    </form>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
