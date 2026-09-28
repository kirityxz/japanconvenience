<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();
$clientes = $pdo->query("SELECT u.*, (SELECT COUNT(*) FROM pedidos p WHERE p.usuario_id=u.id) AS pedidos, (SELECT COALESCE(SUM(total),0) FROM pedidos p WHERE p.usuario_id=u.id AND p.status!='cancelado') AS gasto FROM usuarios u WHERE tipo='cliente' ORDER BY id DESC")->fetchAll();

$titulo = 'Gestão de Clientes';
$menuAtivo = 'clientes';
include '_layout_top.php';
?>
<div class="adm-card">
  <table class="table adm-table mb-0">
    <thead><tr><th>#</th><th>Nome</th><th>E-mail</th><th>Telefone</th><th>Pedidos</th><th>Total gasto</th><th>Cadastro</th></tr></thead>
    <tbody>
    <?php foreach ($clientes as $c): ?>
      <tr><td class="text-muted"><?= str_pad($c['id'], 3, '0', STR_PAD_LEFT) ?></td><td><b><?= e($c['nome']) ?></b></td>
      <td><?= e($c['email']) ?></td><td><?= e($c['telefone'] ?: '—') ?></td><td><?= $c['pedidos'] ?></td>
      <td><?= precoBR($c['gasto']) ?></td><td class="small text-muted"><?= $c['criado_em'] ?></td></tr>
    <?php endforeach; ?>
    <?php if (!$clientes): ?><tr><td colspan="7" class="text-muted">Nenhum cliente ainda.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
<?php include '_layout_bottom.php'; ?>
