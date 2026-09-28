<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$categorias = $pdo->query("SELECT * FROM categorias ORDER BY nome")->fetchAll();

// 6 destaques (flag destaque=1) para a vitrine inicial
$destaques = $pdo->query("SELECT p.*, c.nome AS cat_nome FROM produtos p JOIN categorias c ON c.id=p.categoria_id WHERE p.ativo=1 AND p.destaque=1 ORDER BY p.id LIMIT 6")->fetchAll();

// % de desconto PIX (global, vale para todos os produtos)
$__pix = pctPix();

include 'includes/header.php';
?>

<section class="hero container">
  <div class="hero-overlay glass">
    <div class="eyebrow">SABORES DO JAPÃO</div>
    <h1>A verdadeira magia dos <span>Konbinis</span> direto na sua mesa</h1>
    <p>Doces, salgadinhos, lamen e bebidas típicas japonesas — com entrega rápida e preço de conveniência.</p>
    <a href="#destaques" class="primary-btn">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m2.05 2.05 1.099-.028a1 1 0 0 1 1.008.815l2.69 14.347A1 1 0 0 0 7.83 18H18"/><path d="M4.563 5h16.435a1 1 0 0 1 .981 1.204l-1.026 6.226A2 2 0 0 1 18.962 14H6.25"/><circle cx="18" cy="20" r="2"/><circle cx="8" cy="20" r="2"/></svg>
      Ver Promoções <span>→</span>
    </a>
    <div class="hero-dots" role="tablist" aria-label="Trocar banner">
      <button type="button" class="active" data-slide="0" aria-label="Banner 1"></button><button type="button" data-slide="1" aria-label="Banner 2"></button><button type="button" data-slide="2" aria-label="Banner 3"></button>
    </div>
  </div>
  <img class="hero-photo" id="heroPhoto" src="/JapanConvenience/assets/img/hero-snacks.jpg" alt="Produtos japoneses: Pocky, KitKat, Cup Noodles e Ramune">
</section>

<section class="category-section container" id="categorias">
  <div class="section-heading">
    <div><span class="section-mark"></span><h2>Explore por Categoria</h2></div>
    <a href="categorias.php">Ver todas as categorias →</a>
  </div>

  <div class="category-grid">
    <?php foreach ($categorias as $c): [$foto, $sub] = catFoto($c['nome']); ?>
      <a class="category-card glass" href="categoria.php?id=<?= $c['id'] ?>">
        <div class="category-image">
          <img loading="lazy" src="/JapanConvenience/assets/img/<?= $foto ?>" alt="<?= e($c['nome']) ?>">
        </div>
        <div class="category-body">
          <div class="category-icon"><?= catIcon($c['nome']) ?></div>
          <div class="category-copy">
            <h3><?= e($c['nome']) ?></h3>
            <p><?= e($sub) ?></p>
          </div>
          <span class="category-arrow">›</span>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<section class="products-section container" id="destaques">
  <div class="section-heading">
    <div><span class="section-mark"></span><h2>Destaques da Conveniência</h2></div>
    <a href="categorias.php">Ver todos os produtos →</a>
  </div>

  <div class="product-grid">
    <?php foreach ($destaques as $p): $gal = imgGaleria($p); ?>
      <article class="product-card glass produto-item" data-nome="<?= e(mb_strtolower($p['nome'].' '.$p['cat_nome'])) ?>">
        <?php if ($p['estoque'] > 0 && $p['estoque'] <= 5): ?>
          <span class="product-badge">Últimas unidades</span>
        <?php endif; ?>
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
          <p class="product-brand"><?= e($p['cat_nome']) ?></p>
          <div class="price-row"><div>
            <?php if ($__pix > 0): ?><del><?= precoBR($p['preco']) ?></del><?php endif; ?>
            <strong><?= precoBR($__pix > 0 ? precoPix($p['preco']) : $p['preco']) ?></strong>
            <?php if ($__pix > 0): ?><span class="pix-tag">-<?= $__pix ?>% no PIX</span><?php endif; ?>
          </div></div>
          <?php if ($p['estoque'] > 0): ?>
            <a class="add-btn" href="carrinho.php?add=<?= $p['id'] ?>">Adicionar ao carrinho</a>
            <a class="buy-now" href="carrinho.php?add=<?= $p['id'] ?>&agora=1">Comprar agora <span>→</span></a>
          <?php else: ?>
            <button class="add-btn" disabled style="opacity:.5">Esgotado</button>
          <?php endif; ?>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
</section>

<section class="benefit-strip container glass" id="sobre">
  <div>
    <span class="benefit-ico">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.62l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/></svg>
    </span>
    <div><b>Entrega rápida</b><small>Liberdade e região em até 40 min</small></div>
  </div>
  <div>
    <span class="benefit-ico">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1 1 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></svg>
    </span>
    <div><b>Produtos originais</b><small>Importados e selecionados</small></div>
  </div>
  <div>
    <span class="benefit-ico">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
    </span>
    <div><b>Loja física + online</b><small>Retirada na Liberdade, SP</small></div>
  </div>
  <div>
    <span class="benefit-ico">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2.992 16.342a2 2 0 0 1 .094 1.167l-1.065 3.29a1 1 0 0 0 1.236 1.168l3.413-.998a2 2 0 0 1 1.099.092 10 10 0 1 0-4.777-4.719"/></svg>
    </span>
    <div><b>Atendimento</b><small>Fale com a nossa equipe</small></div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>

