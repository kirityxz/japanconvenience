<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
requireLogin();
$stmt = $pdo->prepare("SELECT * FROM pedidos WHERE usuario_id=? ORDER BY id DESC");
$stmt->execute([$_SESSION['usuario_id']]);
$pedidos = $stmt->fetchAll();
include 'includes/header.php';
?>
<div class="container">
  <h3>Meus pedidos</h3>
  <?php if (!$pedidos): ?><div class="alert alert-info">Você ainda não fez pedidos. <a href="index.php">Ver produtos</a></div><?php endif; ?>
  <?php foreach ($pedidos as $ped): ?>
  <div class="card mb-3">
    <div class="card-header d-flex justify-content-between">
      <b>Pedido #<?= $ped['id'] ?></b>
      <span class="badge bg-<?= $ped['status']==='cancelado'?'danger':'success' ?>"><?= e($ped['status']) ?></span>
    </div>
    <div class="card-body">
      <?php
        $it = $pdo->prepare("SELECT i.*, p.nome, p.imagem FROM pedido_itens i JOIN produtos p ON p.id=i.produto_id WHERE i.pedido_id=?");
        $it->execute([$ped['id']]);
      ?>
      <ul class="mb-2"><?php foreach ($it->fetchAll() as $r): ?>
        <li><?= e($r['imagem']) ?> <?= e($r['nome']) ?> x<?= $r['quantidade'] ?> — <?= precoBR($r['preco_unit']*$r['quantidade']) ?></li>
      <?php endforeach; ?></ul>
      <small class="text-muted"><?= e($ped['endereco_entrega']) ?> · <?= e($ped['forma_pagamento']) ?> · <?= $ped['criado_em'] ?></small>
      <p class="mt-2 mb-0">Total: <b class="price"><?= precoBR($ped['total']) ?></b></p>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php include 'includes/footer.php'; ?>
