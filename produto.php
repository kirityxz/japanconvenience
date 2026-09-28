<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT p.*, c.nome AS cat_nome FROM produtos p JOIN categorias c ON c.id=p.categoria_id WHERE p.id=? AND p.ativo=1");
$stmt->execute([$id]);
$p = $stmt->fetch();
if (!$p) { $_SESSION['erro'] = 'Produto não encontrado.'; header('Location: index.php'); exit; }
include 'includes/header.php';
?>
<div class="container mt-4">
  <a href="index.php" class="btn btn-sm btn-outline-secondary mb-3">Voltar</a>
  <div class="row g-4">
    <div class="col-md-5">
      <?php $gal = imgGaleria($p); ?>
      <?php if ($gal): ?>
        <div class="galeria prod-galeria" data-gal='<?= e(json_encode($gal)) ?>'>
          <img src="<?= $gal[0] ?>" id="prodFoto" class="img-fluid rounded w-100" style="height:380px;object-fit:cover" alt="<?= e($p['nome']) ?>">
          <?php if (count($gal) > 1): ?>
            <button type="button" class="gal-btn gal-prev" aria-label="Foto anterior">‹</button>
            <button type="button" class="gal-btn gal-next" aria-label="Próxima foto">›</button>
            <span class="gal-count">1 / <?= count($gal) ?></span>
          <?php endif; ?>
        </div>
        <?php if (count($gal) > 1): ?>
        <div class="gal-thumbs">
          <?php foreach ($gal as $k => $g): ?>
            <button type="button" class="gal-thumb<?= $k === 0 ? ' on' : '' ?>" data-k="<?= $k ?>" aria-label="Ver foto <?= $k + 1 ?>"><img src="<?= $g ?>" alt=""></button>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      <?php else: ?>
        <div class="card text-center p-5" style="font-size:8rem"><?= e($p['imagem']) ?></div>
      <?php endif; ?>
    </div>
    <div class="col-md-7">
      <span class="badge bg-danger"><?= e($p['cat_nome']) ?></span>
      <h2 class="mt-2"><?= e($p['nome']) ?></h2>
      <p class="text-muted"><?= e($p['descricao']) ?></p>
      <?php $__pix = pctPix(); ?>
      <?php if ($__pix > 0): ?><p class="mb-0 text-muted"><del><?= precoBR($p['preco']) ?></del></p><?php endif; ?>
      <h3 class="price fs-2"><?= precoBR($__pix > 0 ? precoPix($p['preco']) : $p['preco']) ?></h3>
      <?php if ($__pix > 0): ?><p class="pix-big">-<?= $__pix ?>% à vista no PIX <small class="text-muted">(<?= precoBR($p['preco']) ?> no cartão)</small></p><?php endif; ?>
      <p class="<?= $p['estoque']>0?'text-muted':'text-danger' ?>"><?= $p['estoque']>0 ? "{$p['estoque']} disponíveis em estoque" : "Esgotado no momento" ?></p>
      <?php if ($p['estoque'] > 0): ?>
      <form action="carrinho.php" method="GET" id="form-comprar" class="d-flex gap-2 align-items-center">
        <input type="hidden" name="add" value="<?= $p['id'] ?>">
        <div class="input-group" style="max-width:160px">
          <button type="button" class="btn btn-outline-secondary" onclick="alterarQtd(<?= $p['id'] ?>,-1)">−</button>
          <input type="number" name="qtd" id="qtd-<?= $p['id'] ?>" value="1" min="1" max="<?= $p['estoque'] ?>" class="form-control text-center">
          <button type="button" class="btn btn-outline-secondary" onclick="alterarQtd(<?= $p['id'] ?>,1)">+</button>
        </div>
        <button class="btn btn-outline-danger btn-lg">Adicionar ao carrinho</button>
        <button name="agora" value="1" class="btn btn-danger btn-lg">Comprar agora</button>
      </form>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
