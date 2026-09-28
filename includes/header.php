<?php require_once __DIR__ . '/functions.php';
// Categorias para o menu (as paginas da loja incluem o banco antes do topo)
$navCats = [];
if (isset($pdo)) { try { $navCats = $pdo->query("SELECT id, nome FROM categorias ORDER BY nome")->fetchAll(); } catch (Exception $e) {} }
$__cartCount = function_exists('cartCount') ? cartCount() : 0;
$__cartTotal = (function_exists('cartTotal') && isset($pdo)) ? cartTotal($pdo) : 0;
$__isHome = basename($_SERVER['PHP_SELF'] ?? '') === 'index.php' && empty($_GET['categoria']);
// Itens para a prévia do carrinho (dropdown)
$__miniItens = [];
if ($__cartCount > 0 && isset($pdo) && !empty($_SESSION['carrinho'])) {
    try {
        $ids = array_keys($_SESSION['carrinho']);
        $ph = implode(',', array_fill(0, count($ids), '?'));
        $st = $pdo->prepare("SELECT id, nome, preco FROM produtos WHERE id IN ($ph)");
        $st->execute($ids);
        foreach ($st->fetchAll() as $m) {
            $q = (int)($_SESSION['carrinho'][$m['id']] ?? 0);
            if ($q <= 0) continue;
            $__miniItens[] = ['id' => $m['id'], 'nome' => $m['nome'], 'preco' => $m['preco'], 'qtd' => $q, 'foto' => imgProduto(['id' => $m['id']])];
        }
    } catch (Exception $e) {}
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>JapanConvenience — Loja Japonesa Online</title>
<meta name="description" content="Doces, salgados, lamen e bebidas japonesas com entrega rápida.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+JP:wght@400;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="/JapanConvenience/assets/css/style.css?v=20">
</head>
<body>
<header class="site-header glass">
  <div class="header-top container">
    <a class="brand" href="/JapanConvenience/index.php">
      <span class="brand-mark" aria-hidden="true"><span class="brand-mountain"></span></span>
      <span class="brand-text">
        <strong><span>Japan</span>Convenience</strong>
        <small>Loja Japonesa Online</small>
      </span>
    </a>

    <div class="search-wrap">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m21 21-4.4-4.4m2.4-5.6A8 8 0 1 1 3 11a8 8 0 0 1 16 0Z"/></svg>
      <input id="busca" type="search" placeholder="Busque por produtos, marcas ou categorias..." autocomplete="off">
    </div>

    <div class="header-actions">
      <?php if (isLogged()): ?>
        <a class="account action-pill text-decoration-none" href="/JapanConvenience/meus-pedidos.php" style="color:inherit">
          <span class="icon-circle">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          </span>
          <span class="action-copy"><b>Olá, <?= e(explode(' ', $_SESSION['usuario_nome'])[0]) ?></b><small>Meus pedidos · <span style="text-decoration:underline">Sair</span></small></span>
        </a>
      <?php else: ?>
        <a class="account action-pill text-decoration-none" href="/JapanConvenience/login.php" style="color:inherit">
          <span class="icon-circle">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          </span>
          <span class="action-copy"><b>Minha Conta</b><small>Entrar ou Cadastrar</small></span>
        </a>
      <?php endif; ?>
      <div class="cart-wrap">
        <a class="cart action-pill text-decoration-none" href="/JapanConvenience/carrinho.php" style="color:inherit" aria-label="Abrir carrinho">
          <span class="icon-circle">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m2.05 2.05 1.099-.028a1 1 0 0 1 1.008.815l2.69 14.347A1 1 0 0 0 7.83 18H18"/><path d="M4.563 5h16.435a1 1 0 0 1 .981 1.204l-1.026 6.226A2 2 0 0 1 18.962 14H6.25"/><circle cx="18" cy="20" r="2"/><circle cx="8" cy="20" r="2"/></svg>
          </span>
          <span class="action-copy"><b>Carrinho</b><small><?= precoBR($__cartTotal) ?><?= $__cartCount > 0 ? ' · ' . $__cartCount . ($__cartCount === 1 ? ' item' : ' itens') : '' ?></small></span>
          <?php if ($__cartCount > 0): ?><span class="counter"><?= $__cartCount ?></span><?php endif; ?>
        </a>
        <div class="mini-cart glass" aria-hidden="true">
          <?php if (!$__miniItens): ?>
            <p class="mini-empty">Seu carrinho está vazio.</p>
            <a href="/JapanConvenience/index.php#destaques" class="add-btn">Ver produtos</a>
          <?php else: ?>
            <ul>
              <?php foreach (array_slice($__miniItens, 0, 4) as $mi): ?>
                <li>
                  <?php if ($mi['foto']): ?><img src="<?= $mi['foto'] ?>" alt="<?= e($mi['nome']) ?>">
                  <?php else: ?><span class="mini-noimg">JC</span><?php endif; ?>
                  <div class="mini-info"><b><?= e($mi['nome']) ?></b>
                    <div class="mini-qty">
                      <a href="/JapanConvenience/carrinho.php?upd=<?= $mi['id'] ?>&qtd=<?= $mi['qtd'] - 1 ?>" aria-label="Diminuir">−</a><span><?= $mi['qtd'] ?></span><a href="/JapanConvenience/carrinho.php?upd=<?= $mi['id'] ?>&qtd=<?= $mi['qtd'] + 1 ?>" aria-label="Aumentar">+</a>
                      <small>× <?= precoBR($mi['preco']) ?></small>
                    </div>
                  </div>
                  <b class="mini-sub"><?= precoBR($mi['preco'] * $mi['qtd']) ?></b>
                </li>
              <?php endforeach; ?>
            </ul>
            <?php if (count($__miniItens) > 4): ?><p class="mini-more">+ <?= count($__miniItens) - 4 ?> outros itens…</p><?php endif; ?>
            <div class="mini-total"><span>Total</span><b><?= precoBR($__cartTotal) ?></b></div>
            <div class="mini-actions">
              <a href="/JapanConvenience/carrinho.php" class="btn btn-sm btn-outline-secondary flex-fill">Ver carrinho</a>
              <a href="/JapanConvenience/checkout.php" class="add-btn flex-fill">Finalizar</a>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <div class="header-nav container">
    <button class="all-categories" id="categoryButton" type="button">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
      Todas as Categorias
      <small>⌄</small>
    </button>
    <nav>
      <a class="<?= $__isHome ? 'active' : '' ?>" href="/JapanConvenience/index.php">Início</a>
      <?php foreach ($navCats as $c): ?>
        <a href="/JapanConvenience/categoria.php?id=<?= $c['id'] ?>"><?= e($c['nome']) ?></a>
      <?php endforeach; ?>
      <a href="/JapanConvenience/categorias.php">+ Ver Mais</a>
    </nav>

    <div class="mini-services">
      <span>
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.62l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/></svg>
        <b>Entrega rápida</b><small>para todo o Brasil</small>
      </span>
      <span>
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1 1 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></svg>
        <b>Compra segura</b><small>e protegida</small>
      </span>
      <span>
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
        <b>Produtos</b><small>autênticos do Japão</small>
      </span>
    </div>
  </div>
</header>
<div class="container mt-3">
<?php if ($ok = flash('sucesso')): ?><div class="alert alert-success"><?= e($ok) ?></div><?php endif; ?>
<?php if ($er = flash('erro')): ?><div class="alert alert-danger"><?= e($er) ?></div><?php endif; ?>
</div>
<main>
















