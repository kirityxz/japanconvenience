<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

// ---------- KPIs (dados reais) ----------
$fatHoje  = (float)$pdo->query("SELECT COALESCE(SUM(total),0) FROM pedidos WHERE DATE(criado_em)=CURDATE() AND status!='cancelado'")->fetchColumn();
$fatOntem = (float)$pdo->query("SELECT COALESCE(SUM(total),0) FROM pedidos WHERE DATE(criado_em)=CURDATE()-INTERVAL 1 DAY AND status!='cancelado'")->fetchColumn();
$pedHoje  = (int)$pdo->query("SELECT COUNT(*) FROM pedidos WHERE DATE(criado_em)=CURDATE()")->fetchColumn();
$mediaSem = (float)$pdo->query("SELECT COUNT(*)/7 FROM pedidos WHERE criado_em >= CURDATE()-INTERVAL 7 DAY")->fetchColumn();
$ticket   = (float)$pdo->query("SELECT COALESCE(AVG(total),0) FROM pedidos WHERE status!='cancelado'")->fetchColumn();
$ticketOntem = (float)$pdo->query("SELECT COALESCE(AVG(total),0) FROM pedidos WHERE DATE(criado_em)=CURDATE()-INTERVAL 1 DAY AND status!='cancelado'")->fetchColumn();
$criticos = $pdo->query("SELECT * FROM produtos WHERE estoque <= 10 AND ativo=1 ORDER BY estoque LIMIT 5")->fetchAll();
$nCriticos = (int)$pdo->query("SELECT COUNT(*) FROM produtos WHERE estoque <= 10 AND ativo=1")->fetchColumn();
$recentes = $pdo->query("SELECT pe.id, pe.total, pe.status, u.nome FROM pedidos pe JOIN usuarios u ON u.id=pe.usuario_id ORDER BY pe.id DESC LIMIT 4")->fetchAll();

function delta($atual, $base, $sufixo) {
    if ($base <= 0) return '<span class="text-muted delta">sem base anterior</span>';
    $p = ($atual - $base) / $base * 100;
    $cls = $p >= 0 ? 'text-success' : 'text-danger';
    $seta = $p >= 0 ? '▲' : '▼';
    return "<span class=\"$cls delta\">$seta " . number_format(abs($p), 1, ',', '.') . "% $sufixo</span>";
}

// ---------- Grafico: vendas por mes no ano atual ----------
$ano = date('Y');
$stmt = $pdo->prepare("SELECT MONTH(criado_em) m, COALESCE(SUM(total),0) t FROM pedidos WHERE YEAR(criado_em)=? AND status!='cancelado' GROUP BY m");
$stmt->execute([$ano]);
$porMes = array_fill(1, 12, 0);
foreach ($stmt->fetchAll() as $r) $porMes[(int)$r['m']] = (float)$r['t'];
$meses = ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];

$statusPill = ['pendente' => 'warning', 'pago' => 'success', 'enviado' => 'info', 'entregue' => 'primary', 'cancelado' => 'danger'];

$titulo = 'Visão Geral do E-commerce';
$menuAtivo = 'dashboard';
include '_layout_top.php';
?>
<div class="row g-3 mb-4">
  <div class="col-md-3"><div class="adm-kpi"><small>FATURAMENTO HOJE</small><h3><?= precoBR($fatHoje) ?></h3><?= delta($fatHoje, $fatOntem, 'desde ontem') ?></div></div>
  <div class="col-md-3"><div class="adm-kpi"><small>NOVOS PEDIDOS</small><h3><?= $pedHoje ?></h3><?= delta($pedHoje, $mediaSem, 'vs média semanal') ?></div></div>
  <div class="col-md-3"><div class="adm-kpi"><small>TICKET MÉDIO</small><h3><?= precoBR($ticket) ?></h3><?= delta($ticket, $ticketOntem, 'desde ontem') ?></div></div>
  <div class="col-md-3"><div class="adm-kpi"><small>ESTOQUE CRÍTICO</small><h3 class="text-danger"><?= $nCriticos ?> itens</h3><span class="text-danger delta">Necessita reposição</span></div></div>
</div>

<div class="adm-card mb-4">
  <h6>Histórico de Vendas Mensal (<?= $ano ?>)</h6>
  <div style="height:260px"><canvas id="grafVendas"></canvas></div>
</div>

<div class="row g-3">
  <div class="col-lg-8">
    <div class="adm-card">
      <h6>Pedidos Recentes</h6>
      <?php if (!$recentes): ?><p class="text-muted small mb-0">Nenhum pedido ainda.</p><?php else: ?>
      <table class="table adm-table mb-0">
        <thead><tr><th>Pedido</th><th>Cliente</th><th>Total</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($recentes as $r): ?>
          <tr><td><b>#<?= str_pad($r['id'], 4, '0', STR_PAD_LEFT) ?></b></td><td><?= e($r['nome']) ?></td>
          <td><?= precoBR($r['total']) ?></td>
          <td><span class="badge bg-<?= $statusPill[$r['status']] ?? 'secondary' ?>"><?= ucfirst(e($r['status'])) ?></span></td></tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="adm-card">
      <h6>Alerta de Estoque</h6>
      <?php if (!$criticos): ?><p class="text-success small mb-0">Estoque normal.</p><?php endif; ?>
      <?php foreach ($criticos as $c): $foto = imgProduto($c); ?>
      <div class="d-flex align-items-center gap-2 py-2 border-bottom">
        <?php if ($foto): ?><img src="<?= $foto ?>" width="40" height="40" class="rounded" style="object-fit:cover"><?php else: ?><span class="fs-3"><?= e($c['imagem']) ?></span><?php endif; ?>
        <div class="small"><b><?= e($c['nome']) ?></b><br>
          <span class="text-muted"><?= $c['estoque'] > 0 ? "Apenas {$c['estoque']} unidades restando" : 'Estoque Esgotado Hoje' ?></span></div>
      </div>
      <?php endforeach; ?>
      <a href="produtos.php" class="btn btn-sm btn-outline-secondary mt-2">Repor estoque</a>
    </div>
  </div>
</div>

<script>
new Chart(document.getElementById('grafVendas'), {
  type: 'line',
  data: { labels: <?= json_encode($meses) ?>,
    datasets: [{ data: <?= json_encode(array_values($porMes)) ?>,
      borderColor: '#e63946', backgroundColor: 'rgba(230,57,70,.08)',
      fill: true, tension: .35, pointBackgroundColor: '#e63946' }] },
  options: { responsive: true, maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: { y: { ticks: { callback: v => 'R$ ' + v } } } }
});
</script>
<?php include '_layout_bottom.php'; ?>
