<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$categorias = $pdo->query("SELECT * FROM categorias ORDER BY nome")->fetchAll();

// Quantidade de produtos ativos por categoria
$counts = [];
try {
    $q = $pdo->query("SELECT categoria_id, COUNT(*) n FROM produtos WHERE ativo = 1 GROUP BY categoria_id");
    foreach ($q->fetchAll() as $r) $counts[$r['categoria_id']] = (int)$r['n'];
} catch (Exception $e) {}

include 'includes/header.php';
?>

<section class="category-section container" id="categorias" style="margin-top:18px">
  <div class="section-heading">
    <div><span class="section-mark"></span><h2>Todas as categorias</h2></div>
    <a href="index.php#produtos">Ver todos os produtos →</a>
  </div>
  <p class="small text-muted mb-3">Escolha uma categoria para ver os produtos.</p>

  <div class="category-grid">
    <?php foreach ($categorias as $c): [$foto, $sub] = catFoto($c['nome']); $n = $counts[$c['id']] ?? 0; ?>
      <a class="category-card glass" href="categoria.php?id=<?= $c['id'] ?>">
        <div class="category-image">
          <img loading="lazy" src="/JapanConvenience/assets/img/<?= $foto ?>" alt="<?= e($c['nome']) ?>">
        </div>
        <div class="category-body">
          <div class="category-icon"><?= catIcon($c['nome']) ?></div>
          <div class="category-copy">
            <h3><?= e($c['nome']) ?></h3>
            <p><?= e($sub) ?> · <?= $n ?> <?= $n === 1 ? 'produto' : 'produtos' ?></p>
          </div>
          <span class="category-arrow">›</span>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<?php include 'includes/footer.php'; ?>

