<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM categorias WHERE id = ?");
$stmt->execute([$id]);
$catAtual = $stmt->fetch();

if (!$catAtual) {
    $_SESSION['erro'] = 'Categoria não encontrada.';
    header('Location: categorias.php');
    exit;
}

$stmt = $pdo->prepare("SELECT p.*, c.nome AS cat_nome FROM produtos p JOIN categorias c ON c.id = p.categoria_id WHERE p.ativo = 1 AND p.categoria_id = ? ORDER BY p.destaque DESC, p.nome");
$stmt->execute([$id]);
$produtos = $stmt->fetchAll();

[$foto, $sub] = catFoto($catAtual['nome']);

// % de desconto PIX (global, vale para todos os produtos)
$__pix = pctPix();

include 'includes/header.php';
?>

<section class="category-section container" style="margin-top:18px">
  <p class="small text-muted mb-2">
    <a href="index.php" class="text-decoration-none text-muted">Início</a>
    &nbsp;/&nbsp;
    <a href="categorias.php" class="text-decoration-none text-muted">Categorias</a>
    &nbsp;/&nbsp;
    <span><?= e($catAtual['nome']) ?></span>
  </p>

  <div class="section-heading">
    <div><span class="section-mark"></span><h2><?= e($catAtual['nome']) ?></h2></div>
    <span class="small text-muted"><?= count($produtos) ?> <?= count($produtos) === 1 ? 'produto' : 'produtos' ?></span>
  </div>
  <p class="small text-muted mb-3"><?= e($sub) ?></p>

  <div class="category-hero mb-4">
    <img src="/JapanConvenience/assets/img/<?= $foto ?>" alt="<?= e($catAtual['nome']) ?>">
  </div>

  <?php if (!$produtos): ?>
    <div class="alert alert-warning">Nenhum produto nesta categoria ainda.</div>
  <?php else: ?>
  <div class="product-grid">
    <?php foreach ($produtos as $p): $fotoP = imgProduto($p); ?>
      <article class="product-card glass produto-item" data-nome="<?= e(mb_strtolower($p['nome'].' '.$p['cat_nome'])) ?>">
        <?php if ($p['estoque'] > 0 && $p['estoque'] <= 5): ?>
          <span class="product-badge">Últimas unidades</span>
        <?php endif; ?>
        <a class="product-link" href="produto.php?id=<?= $p['id'] ?>">
          <?php $gal = imgGaleria($p); ?>
          <?php if ($gal): ?>
          <div class="product-image galeria" data-gal='<?= e(json_encode($gal)) ?>'>
            <img loading="lazy" src="<?= $gal[0] ?>" alt="<?= e($p['nome']) ?>">
            <?php if (count($gal) > 1): ?>
              <button type="button" class="gal-btn gal-prev" aria-label="Foto anterior">‹</button>
              <button type="button" class="gal-btn gal-next" aria-label="Próxima foto">›</button>
              <span class="gal-count">1 / <?= count($gal) ?></span>
            <?php endif; ?>
          </div>
          <?php else: ?>
          <div class="product-image"><span class="fs-1"><?= e($p['imagem']) ?></span></div>
          <?php endif; ?>
          <div class="product-info">
            <p class="product-name"><?= e($p['nome']) ?></p>
            <p class="product-brand"><?= e($p['cat_nome']) ?> · <?= $p['estoque'] > 0 ? $p['estoque'] . ' em estoque' : 'Esgotado' ?></p>
          </div>
        </a>
        <div class="product-info product-buy">
          <div class="price-row"><div>
            <?php if ($__pix > 0): ?><del><?= precoBR($p['preco']) ?></del><?php endif; ?>
            <strong><?= precoBR($__pix > 0 ? precoPix($p['preco']) : $p['preco']) ?></strong>
            <?php if ($__pix > 0): ?><span class="pix-tag">-<?= $__pix ?>% no PIX</span><?php endif; ?>
          </div></div>
          <?php if ($p['estoque'] > 0): ?>
            <div class="card-btns">
              <a href="produto.php?id=<?= $p['id'] ?>" class="card-btn">Detalhes</a>
              <a href="carrinho.php?add=<?= $p['id'] ?>" class="add-btn">Adicionar</a>
            </div>
            <a href="carrinho.php?add=<?= $p['id'] ?>&agora=1" class="buy-now">Comprar agora <span>→</span></a>
          <?php else: ?>
            <div class="card-btns">
              <a href="produto.php?id=<?= $p['id'] ?>" class="card-btn">Detalhes</a>
              <button class="add-btn" disabled style="opacity:.5">Esgotado</button>
            </div>
          <?php endif; ?>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</section>

<?php include 'includes/footer.php'; ?>
