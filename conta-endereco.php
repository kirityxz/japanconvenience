<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
requireLogin();

$ufs = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cepNum = preg_replace('/\D/', '', $_POST['cep'] ?? '');
    $bairro = trim($_POST['bairro'] ?? '');
    $rua = trim($_POST['rua'] ?? '');
    $numero = trim($_POST['numero'] ?? '');
    $cidade = trim($_POST['cidade'] ?? '');
    $uf = strtoupper(trim($_POST['uf'] ?? ''));
    if (!preg_match('/^\d{8}$/', $cepNum)) {
        $_SESSION['erro'] = 'CEP inválido: digite os 8 números do CEP.';
    } elseif (!preg_match('/^\d{1,4}$/', $numero)) {
        $_SESSION['erro'] = 'Número inválido: só números, até 4 dígitos.';
    } elseif (!in_array($uf, $ufs, true)) {
        $_SESSION['erro'] = 'Selecione um estado válido.';
    } elseif (strlen($bairro) < 2 || strlen($rua) < 3 || strlen($cidade) < 2) {
        $_SESSION['erro'] = 'Preencha o endereço completo.';
    } elseif (mb_strlen($rua, 'UTF-8') > 50) {
        $_SESSION['erro'] = 'Nome da rua muito longo: máximo 50 caracteres.';
    } elseif (mb_strlen($bairro, 'UTF-8') > 30) {
        $_SESSION['erro'] = 'Nome do bairro muito longo: máximo 30 caracteres.';
    } else {
        $cepFmt = substr($cepNum, 0, 5) . '-' . substr($cepNum, 5, 3);
        $pdo->prepare("UPDATE usuarios SET cep = ?, rua = ?, numero = ?, bairro = ?, cidade = ?, uf = ? WHERE id = ?")
            ->execute([$cepFmt, $rua, $numero, $bairro, $cidade, $uf, $_SESSION['usuario_id']]);
        $_SESSION['sucesso'] = 'Endereço salvo!';
    }
    header('Location: conta-endereco.php'); exit;
}

$stmt = $pdo->prepare("SELECT cep, rua, numero, bairro, cidade, uf FROM usuarios WHERE id = ?");
$stmt->execute([$_SESSION['usuario_id']]);
$en = $stmt->fetch() ?: [];

include 'includes/header.php';
?>

<section class="container" style="margin-top:18px;max-width:640px">
  <p class="small text-muted mb-2"><a href="conta.php" class="text-decoration-none text-muted">Minha conta</a> &nbsp;/&nbsp; <span>Meus endereços</span></p>
  <div class="section-heading">
    <div><span class="section-mark"></span><h2>Meu endereço de entrega</h2></div>
  </div>
  <div class="adm-card">
    <form method="POST">
      <div class="row g-2">
        <div class="col-md-6"><label class="small text-muted">CEP (só números)</label>
          <input name="cep" class="form-control" required inputmode="numeric" maxlength="9" value="<?= e($en['cep'] ?? '') ?>"></div>
        <div class="col-md-6"><label class="small text-muted">Bairro</label>
          <input name="bairro" class="form-control" required maxlength="30" value="<?= e($en['bairro'] ?? '') ?>"></div>
        <div class="col-md-8"><label class="small text-muted">Rua / Avenida</label>
          <input name="rua" class="form-control" required maxlength="50" value="<?= e($en['rua'] ?? '') ?>"></div>
        <div class="col-md-4"><label class="small text-muted">Número</label>
          <input name="numero" class="form-control" required inputmode="numeric" maxlength="4" value="<?= e($en['numero'] ?? '') ?>"></div>
        <div class="col-md-6"><label class="small text-muted">Estado</label>
          <select name="uf" class="form-select" required>
            <option value="">Selecione...</option>
            <?php foreach ($ufs as $sigla): ?>
              <option value="<?= $sigla ?>" <?= ($en['uf'] ?? '') === $sigla ? 'selected' : '' ?>><?= $sigla ?></option>
            <?php endforeach; ?>
          </select></div>
        <div class="col-md-6"><label class="small text-muted">Cidade</label>
          <input name="cidade" class="form-control" required value="<?= e($en['cidade'] ?? '') ?>"></div>
      </div>
      <button class="jc-btn-red w-100 mt-3">Salvar endereço</button>
    </form>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
