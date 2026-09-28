<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
requireLogin();
expirarPedidos($pdo ?? null);

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM pedidos WHERE id = ? AND usuario_id = ?");
$stmt->execute([$id, $_SESSION['usuario_id']]);
$ped = $stmt->fetch();

if (!$ped) { $_SESSION['erro'] = 'Pedido não encontrado.'; header('Location: meus-pedidos.php'); exit; }
if ($ped['status'] === 'pago') { $_SESSION['sucesso'] = "Pedido #$id já está pago. Obrigado!"; header('Location: meus-pedidos.php'); exit; }
if ($ped['status'] !== 'pendente') { $_SESSION['erro'] = "Pedido #$id não está mais aberto."; header('Location: meus-pedidos.php'); exit; }
if (!empty($ped['expira_em']) && strtotime($ped['expira_em']) <= time()) {
    cancelarPedido($pdo, $id);
    $_SESSION['erro'] = "O prazo de 30 min do pedido #$id acabou e ele foi cancelado.";
    header('Location: meus-pedidos.php'); exit;
}

// Confirmação do pagamento (demonstração: simula a operadora)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['confirmar'] ?? '') === '1') {
    $metodo = $ped['forma_pagamento'];
    if (str_contains($metodo, 'cartao')) {
        $num = preg_replace('/\D/', '', $_POST['cartao_num'] ?? '');
        $nome = trim($_POST['cartao_nome'] ?? '');
        $val = trim($_POST['cartao_val'] ?? '');
        $cvv = preg_replace('/\D/', '', $_POST['cartao_cvv'] ?? '');
        $okNum = strlen($num) >= 13 && strlen($num) <= 19;
        if ($okNum) { // Luhn
            $s = 0; $alt = false;
            for ($i = strlen($num) - 1; $i >= 0; $i--) {
                $d = (int)$num[$i];
                if ($alt) { $d *= 2; if ($d > 9) $d -= 9; }
                $s += $d; $alt = !$alt;
            }
            $okNum = ($s % 10 === 0);
        }
        $okVal = preg_match('/^(0[1-9]|1[0-2])\/(\d{2})$/', $val, $m)
            && (int)($m[2]) * 100 + (int)$m[1] >= (int)date('y') * 100 + (int)date('m');
        if (!$okNum) $_SESSION['erro'] = 'Número do cartão inválido.';
        elseif (mb_strlen($nome) < 3) $_SESSION['erro'] = 'Informe o nome impresso no cartão.';
        elseif (!$okVal) $_SESSION['erro'] = 'Validade inválida ou vencida. Use MM/AA.';
        elseif (strlen($cvv) < 3 || strlen($cvv) > 4) $_SESSION['erro'] = 'CVV inválido.';
        else {
            $pdo->prepare("UPDATE pedidos SET status = 'pago' WHERE id = ? AND status = 'pendente'")->execute([$id]);
            $_SESSION['sucesso'] = "Pagamento do pedido #$id confirmado. Obrigado!";
            header('Location: meus-pedidos.php'); exit;
        }
        header("Location: pagamento.php?id=$id"); exit;
    }
    // PIX: simula a confirmação do banco
    $pdo->prepare("UPDATE pedidos SET status = 'pago' WHERE id = ? AND status = 'pendente'")->execute([$id]);
    $_SESSION['sucesso'] = "Pagamento do pedido #$id confirmado. Obrigado!";
    header('Location: meus-pedidos.php'); exit;
}

$isPix = !str_contains($ped['forma_pagamento'], 'cartao');
$pixCode = 'JAPAN' . str_pad((string)$id, 6, '0', STR_PAD_LEFT) . strtoupper(substr(md5($id . '-japanconvenience'), 0, 16));
$expiraTs = !empty($ped['expira_em']) ? strtotime($ped['expira_em']) : (time() + 1800);

include 'includes/header.php';
?>

<section class="container" style="margin-top:18px;max-width:680px">
  <div class="cart-card glass">
    <div class="cart-head">
      <span class="cart-ico">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="18" height="11" x="3" y="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
      </span>
      <div>
        <h2>Pagamento do pedido #<?= $ped['id'] ?></h2>
        <p><?= e(ucfirst($ped['forma_pagamento'])) ?> · Total <b><?= precoBR($ped['total']) ?></b></p>
      </div>
      <span class="pay-timer" id="payTimer" data-expira="<?= $expiraTs ?>">--:--</span>
    </div>

    <div class="alert alert-warning small">Conclua em até <b>30 minutos</b>, ou o pedido é cancelado e os produtos voltam ao estoque.</div>
    <p class="small text-muted">Ambiente de demonstração — nenhum valor é cobrado de verdade.</p>

    <?php if ($isPix): ?>
      <label class="small text-muted">PIX copia e cola</label>
      <div class="pix-code">
        <code id="pixCode"><?= e($pixCode) ?></code>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnCopiaPix">Copiar</button>
      </div>
      <ol class="small text-muted mt-2 mb-3">
        <li>Abra o app do seu banco e pague com o código acima.</li>
        <li>Depois clique em “Já paguei” para confirmar.</li>
      </ol>
      <form method="POST">
        <input type="hidden" name="confirmar" value="1">
        <button class="login-btn-red w-100">Já paguei <?= precoBR($ped['total']) ?></button>
      </form>
    <?php else: ?>
      <form method="POST" id="form-cartao">
        <input type="hidden" name="confirmar" value="1">
        <label class="small text-muted">Número do cartão</label>
        <input name="cartao_num" id="cc-num" class="form-control mb-2" inputmode="numeric" placeholder="0000 0000 0000 0000" required maxlength="19">
        <label class="small text-muted">Nome impresso</label>
        <input name="cartao_nome" class="form-control mb-2" required placeholder="Como está no cartão">
        <div class="row g-2">
          <div class="col-6"><label class="small text-muted">Validade (MM/AA)</label>
            <input name="cartao_val" id="cc-val" class="form-control" required placeholder="12/28" maxlength="5"></div>
          <div class="col-6"><label class="small text-muted">CVV</label>
            <input name="cartao_cvv" class="form-control" required inputmode="numeric" placeholder="123" maxlength="4"></div>
        </div>
        <button class="login-btn-red w-100 mt-3" id="btnPagarCartao">Pagar <?= precoBR($ped['total']) ?></button>
      </form>
    <?php endif; ?>
  </div>
</section>

<script>
// Contagem regressiva dos 30 min
(function () {
  const el = document.getElementById('payTimer');
  if (!el) return;
  const fim = parseInt(el.dataset.expira, 10) * 1000;
  function tick() {
    let s = Math.max(0, Math.floor((fim - Date.now()) / 1000));
    const m = String(Math.floor(s / 60)).padStart(2, '0');
    el.textContent = m + ':' + String(s % 60).padStart(2, '0');
    el.classList.toggle('urgent', s < 300);
    if (s <= 0) { el.textContent = 'Expirado'; setTimeout(() => location.reload(), 2000); }
    else setTimeout(tick, 1000);
  }
  tick();
})();
// Copiar código PIX
document.getElementById('btnCopiaPix')?.addEventListener('click', async e => {
  const t = document.getElementById('pixCode').textContent;
  try { await navigator.clipboard.writeText(t); } catch (err) {
    const ta = document.createElement('textarea');
    ta.value = t; document.body.appendChild(ta); ta.select();
    document.execCommand('copy'); ta.remove();
  }
  e.target.textContent = 'Copiado!';
  setTimeout(() => { e.target.textContent = 'Copiar'; }, 2000);
});
// Máscaras do cartão + simula processamento
document.getElementById('cc-num')?.addEventListener('input', e => {
  e.target.value = e.target.value.replace(/\D/g, '').slice(0, 16).replace(/(\d{4})(?=\d)/g, '$1 ');
});
document.getElementById('cc-val')?.addEventListener('input', e => {
  let d = e.target.value.replace(/\D/g, '').slice(0, 4);
  e.target.value = d.length > 2 ? d.slice(0, 2) + '/' + d.slice(2) : d;
});
document.getElementById('form-cartao')?.addEventListener('submit', e => {
  const b = document.getElementById('btnPagarCartao');
  b.disabled = true;
  b.textContent = 'Processando pagamento…';
});
</script>

<?php include 'includes/footer.php'; ?>
