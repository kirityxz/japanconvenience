<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();
if (isset($_GET['status'], $_GET['id'])) {
    $pdo->prepare("UPDATE pedidos SET status=? WHERE id=?")->execute([$_GET['status'], (int)$_GET['id']]);
    $_SESSION['sucesso'] = 'Status atualizado!';
    header('Location: pedidos.php'); exit;
}
$pedidos = $pdo->query("SELECT pe.*, u.nome, u.email FROM pedidos pe JOIN usuarios u ON u.id=pe.usuario_id ORDER BY pe.id DESC")->fetchAll();
$pill = ['pendente' => 'warning', 'pago' => 'success', 'enviado' => 'info', 'entregue' => 'primary', 'cancelado' => 'danger'];

$titulo = 'Gestão de Pedidos';
$menuAtivo = 'pedidos';
include '_layout_top.php';
?>
<?php if (!$pedidos): ?><div class="adm-card"><p class="text-muted mb-0">Nenhum pedido ainda.</p></div><?php endif; ?>
<?php foreach ($pedidos as $ped): ?>
<div class="adm-card mb-3">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
    <b>#<?= str_pad($ped['id'], 4, '0', STR_PAD_LEFT) ?> — <?= e($ped['nome']) ?> <small class="text-muted"><?= e($ped['email']) ?></small></b>
    <span class="badge bg-<?= $pill[$ped['status']] ?? 'secondary' ?>"><?= ucfirst(e($ped['status'])) ?></span>
  </div>
  <?php $it = $pdo->prepare("SELECT i.*, p.nome FROM pedido_itens i JOIN produtos p ON p.id=i.produto_id WHERE i.pedido_id=?"); $it->execute([$ped['id']]); ?>
  <ul class="small my-2"><?php foreach ($it->fetchAll() as $r): ?><li><?= e($r['nome']) ?> x<?= $r['quantidade'] ?> — <?= precoBR($r['preco_unit'] * $r['quantidade']) ?></li><?php endforeach; ?></ul>
  <p class="small mb-2">Total: <b><?= precoBR($ped['total']) ?></b> · <?= e($ped['endereco_entrega']) ?> · <?= e($ped['forma_pagamento']) ?> · <?= $ped['criado_em'] ?></p>
  <div class="btn-group btn-group-sm">
    <a href="pedidos.php?id=<?= $ped['id'] ?>&status=pago" class="btn btn-outline-success">Pago</a>
    <a href="pedidos.php?id=<?= $ped['id'] ?>&status=enviado" class="btn btn-outline-primary">Enviado</a>
    <a href="pedidos.php?id=<?= $ped['id'] ?>&status=entregue" class="btn btn-outline-secondary">Entregue</a>
    <a href="pedidos.php?id=<?= $ped['id'] ?>&status=cancelado" class="btn btn-outline-danger">Cancelar</a>
  </div>
</div>
<?php endforeach; ?>
<?php include '_layout_bottom.php'; ?>
