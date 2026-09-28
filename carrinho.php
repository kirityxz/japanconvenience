<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Resposta JSON para ações AJAX (adicionar/alterar/remover sem sair da página)
function ajaxCarrinho($pdo) {
    $itens = []; $total = 0;
    if (!empty($_SESSION['carrinho'])) {
        $ids = array_keys($_SESSION['carrinho']);
        $ph = implode(',', array_fill(0, count($ids), '?'));
        try {
            $stmt = $pdo->prepare("SELECT p.*, c.nome AS cat_nome FROM produtos p JOIN categorias c ON c.id=p.categoria_id WHERE p.id IN ($ph)");
            $stmt->execute($ids);
            foreach ($stmt->fetchAll() as $p) {
                if (!isset($_SESSION['carrinho'][$p['id']])) continue;
                $qtd = (int)$_SESSION['carrinho'][$p['id']];
                $sub = $p['preco'] * $qtd;
                $total += $sub;
                $itens[] = [
                    'id' => (int)$p['id'], 'nome' => $p['nome'], 'cat' => $p['cat_nome'],
                    'preco' => (float)$p['preco'], 'estoque' => (int)$p['estoque'],
                    'qtd' => $qtd, 'subFmt' => precoBR($sub), 'precoFmt' => precoBR($p['preco']),
                    'foto' => imgProduto($p),
                ];
            }
        } catch (Exception $e) {}
    }
    $count = array_sum($_SESSION['carrinho'] ?? []);
    $msg = flash('sucesso');
    if ($msg === null) $msg = flash('erro');
    $pixDesc = $total * pixTaxa();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'ok' => true, 'count' => $count, 'totalFmt' => precoBR($total),
        'countTxt' => $count . ' ' . ($count === 1 ? 'item' : 'itens'),
        'items' => $itens, 'msg' => $msg,
        'pixDescFmt' => precoBR($pixDesc), 'pixTotalFmt' => precoBR($total - $pixDesc),
    ]);
    exit;
}

// Acoes do carrinho
if (isset($_GET['add'])) {
    $id = (int)$_GET['add'];
    $qtd = max(1, (int)($_GET['qtd'] ?? 1));
    $stmt = $pdo->prepare("SELECT estoque FROM produtos WHERE id=? AND ativo=1");
    $stmt->execute([$id]);
    $prod = $stmt->fetch();
    if ($prod) {
        $atual = $_SESSION['carrinho'][$id] ?? 0;
        if ($atual + $qtd > $prod['estoque']) {
            $_SESSION['erro'] = 'Estoque insuficiente! Máximo: ' . $prod['estoque'];
        } else {
            $_SESSION['carrinho'][$id] = $atual + $qtd;
            $_SESSION['sucesso'] = 'Produto adicionado ao carrinho.';
        }
    }
    if (!empty($_GET['ajax'])) ajaxCarrinho($pdo);
    // Comprar agora: adiciona e abre o carrinho (se o item entrou)
    if (!empty($_GET['agora']) && isset($_SESSION['carrinho'][$id])) { header('Location: carrinho.php'); exit; }
    header('Location: carrinho.php'); exit;
}
if (isset($_GET['rem'])) { unset($_SESSION['carrinho'][(int)$_GET['rem']]); if (!empty($_GET['ajax'])) ajaxCarrinho($pdo); header('Location: carrinho.php'); exit; }
if (isset($_GET['limpar'])) { $_SESSION['carrinho'] = []; header('Location: carrinho.php'); exit; }
if (isset($_GET['upd'])) {
    $id = (int)$_GET['upd'];
    $qtd = max(0, (int)($_GET['qtd'] ?? 1));
    if (isset($_SESSION['carrinho'][$id])) {
        if ($qtd <= 0) {
            unset($_SESSION['carrinho'][$id]);
        } else {
            $stmt = $pdo->prepare("SELECT estoque FROM produtos WHERE id=? AND ativo=1");
            $stmt->execute([$id]);
            $prod = $stmt->fetch();
            if ($prod) $_SESSION['carrinho'][$id] = min($qtd, max(1, (int)$prod['estoque']));
            else unset($_SESSION['carrinho'][$id]);
        }
    }
    if (!empty($_GET['ajax'])) ajaxCarrinho($pdo);
    header('Location: carrinho.php'); exit;
}

// Lista itens
$itens = []; $total = 0;
if (!empty($_SESSION['carrinho'])) {
    $ids = array_keys($_SESSION['carrinho']);
    $ph = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT p.*, c.nome AS cat_nome FROM produtos p JOIN categorias c ON c.id=p.categoria_id WHERE p.id IN ($ph)");
    $stmt->execute($ids);
    foreach ($stmt->fetchAll() as $p) {
        $qtd = $_SESSION['carrinho'][$p['id']];
        $sub = $p['preco'] * $qtd;
        $total += $sub;
        $itens[] = ['p' => $p, 'qtd' => $qtd, 'sub' => $sub];
    }
}
include 'includes/header.php';
?>
<?php $nItens = array_sum($_SESSION['carrinho'] ?? []); $__pix = pctPix(); $__pixDesc = $total * pixTaxa(); ?>
<div class="container cart-page">
  <?php if (!$itens): ?>
    <div class="cart-card glass cart-empty">
      <span class="cart-ico">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m2.05 2.05 1.099-.028a1 1 0 0 1 1.008.815l2.69 14.347A1 1 0 0 0 7.83 18H18"/><path d="M4.563 5h16.435a1 1 0 0 1 .981 1.204l-1.026 6.226A2 2 0 0 1 18.962 14H6.25"/><circle cx="18" cy="20" r="2"/><circle cx="8" cy="20" r="2"/></svg>
      </span>
      <h2>Seu carrinho está vazio</h2>
      <p>Que tal explorar os sabores do Japão?</p>
      <a href="index.php#destaques" class="primary-btn">Ver produtos <span>→</span></a>
    </div>
  <?php else: ?>
  <div class="row g-3">
    <div class="col-lg-8">
      <div class="cart-card glass">
        <div class="cart-head">
          <span class="cart-ico">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m2.05 2.05 1.099-.028a1 1 0 0 1 1.008.815l2.69 14.347A1 1 0 0 0 7.83 18H18"/><path d="M4.563 5h16.435a1 1 0 0 1 .981 1.204l-1.026 6.226A2 2 0 0 1 18.962 14H6.25"/><circle cx="18" cy="20" r="2"/><circle cx="8" cy="20" r="2"/></svg>
          </span>
          <div>
            <h2>Meu carrinho <span class="cart-count"><?= $nItens ?> <?= $nItens === 1 ? 'item' : 'itens' ?></span></h2>
            <p>Revise seus produtos antes de finalizar a compra.</p>
          </div>
        </div>

        <div class="cart-grid cart-grid-head" aria-hidden="true">
          <span>Produto</span><span>Preço</span><span>Quantidade</span><span>Subtotal</span><span></span>
        </div>

        <?php foreach ($itens as $i): $p = $i['p']; $foto = imgProduto($p); $max = max(1, (int)$p['estoque']); ?>
        <div class="cart-grid cart-row" data-row="<?= $p['id'] ?>">
          <div class="cart-prod">
            <?php if ($foto): ?><img src="<?= $foto ?>" alt="<?= e($p['nome']) ?>">
            <?php else: ?><span class="mini-noimg">JC</span><?php endif; ?>
            <div><b><?= e($p['nome']) ?></b><small><?= e($p['cat_nome']) ?></small></div>
          </div>
          <b class="cart-price"><?= precoBR($p['preco']) ?></b>
          <div class="qty">
            <a href="carrinho.php?upd=<?= $p['id'] ?>&qtd=<?= $i['qtd'] - 1 ?>" aria-label="Diminuir">−</a>
            <span class="qty-num"><?= $i['qtd'] ?></span>
            <?php if ($i['qtd'] < $max): ?>
              <a href="carrinho.php?upd=<?= $p['id'] ?>&qtd=<?= $i['qtd'] + 1 ?>" aria-label="Aumentar">+</a>
            <?php else: ?>
              <span class="qty-off" title="Estoque máximo">+</span>
            <?php endif; ?>
          </div>
          <b class="cart-price row-sub"><?= precoBR($i['sub']) ?></b>
          <a class="cart-remove" href="carrinho.php?rem=<?= $p['id'] ?>">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
            Remover
          </a>
        </div>
        <?php endforeach; ?>

        <div class="cart-foot">
          <a href="index.php#destaques" class="btn-soft">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
            Continuar comprando
          </a>
          <a href="carrinho.php?limpar=1" class="btn-soft">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
            Limpar carrinho
          </a>
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="cart-summary glass">
        <div class="summary-head">
          <span class="summary-ico">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z"/><path d="M8 7h8"/><path d="M8 11h8"/><path d="M8 15h5"/></svg>
          </span>
          <div><h2>Resumo do pedido</h2><p>Veja o valor total da sua compra.</p></div>
        </div>
        <div class="sum-row"><span id="sumLabel">Subtotal (<?= $nItens ?> <?= $nItens === 1 ? 'item' : 'itens' ?>)</span><b id="sumSubtotal"><?= precoBR($total) ?></b></div>
        <div class="sum-row"><span>Frete</span><b class="free">Grátis</b></div>
        <?php if ($__pix > 0): ?>
        <div class="sum-row"><span>Desconto no PIX (<?= $__pix ?>%)</span><b class="free" id="sumPixDesc">− <?= precoBR($__pixDesc) ?></b></div>
        <?php endif; ?>
        <hr>
        <div class="sum-total"><span>Total</span><b id="sumTotal"><?= precoBR($total) ?></b></div>
        <?php if ($__pix > 0): ?>
        <div class="pix-box">Pague <b id="sumPixTotal"><?= precoBR($total - $__pixDesc) ?></b> no PIX</div>
        <?php endif; ?>
        <a class="primary-btn w-100 justify-content-center" href="checkout.php">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="18" height="11" x="3" y="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
          Finalizar compra <span>→</span>
        </a>
        <div class="secure-note">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1 1 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></svg>
          <div><b>Compra segura</b><small>Seus dados estão protegidos</small></div>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>
