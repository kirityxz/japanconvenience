<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pix = max(0, min(90, (float)str_replace(',', '.', $_POST['pix_desconto'] ?? '0')));
    $pdo->prepare("INSERT INTO configuracoes (chave, valor) VALUES ('pix_desconto', ?) ON DUPLICATE KEY UPDATE valor = ?")
        ->execute([(string)$pix, (string)$pix]);
    $_SESSION['sucesso'] = 'Configurações salvas! O desconto vale para todos os produtos, inclusive novos.';
    header('Location: configuracoes.php'); exit;
}

$atual = 10;
try { $v = $pdo->query("SELECT valor FROM configuracoes WHERE chave = 'pix_desconto'")->fetchColumn(); if ($v !== false) $atual = (float)$v; } catch (Exception $e) {}

$titulo = 'Configurações da Loja';
$menuAtivo = 'config';
include '_layout_top.php';
?>
<div class="adm-card mb-3">
  <h6>Desconto no PIX</h6>
  <p class="small text-muted">Vale automaticamente para <b>todos</b> os produtos à venda — os atuais e qualquer produto novo cadastrado depois. Aparece nas vitrines, no carrinho e no checkout.</p>
  <form method="POST" class="row g-2 align-items-end">
    <div class="col-md-3">
      <label class="small text-muted">% de desconto no PIX</label>
      <div class="input-group input-group-sm">
        <input name="pix_desconto" type="number" min="0" max="90" step="1" class="form-control" required value="<?= e($atual) ?>">
        <span class="input-group-text">%</span>
      </div>
    </div>
    <div class="col-md-3"><button class="btn btn-danger btn-sm">Salvar</button></div>
  </form>
</div>
<div class="adm-card">
  <h6>Como aparece na loja (com <?= e($atual) ?>%)</h6>
  <p class="small mb-1">Produto de <del><?= precoBR(100) ?></del> sai por <b class="price"><?= precoBR(100 * (1 - $atual / 100)) ?></b> no PIX.</p>
  <p class="small text-muted mb-0">Use 0% para desativar o desconto.</p>
</div>
<?php include '_layout_bottom.php'; ?>
