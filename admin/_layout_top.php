<?php
// Layout compartilhado do painel admin (sidebar escura + topbar clara).
// Cada pagina define $titulo e $menuAtivo antes de incluir este arquivo.
// Requer: config/database.php, includes/functions.php e requireAdmin() ja executados.
$menuAtivo = $menuAtivo ?? 'dashboard';
$critCount = 0;
try { $critCount = (int)$pdo->query("SELECT COUNT(*) FROM produtos WHERE estoque <= 10 AND ativo=1")->fetchColumn(); } catch (Exception $e) {}
function menuClass($m, $atual) { return $m === $atual ? 'adm-menu-item active' : 'adm-menu-item'; }
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($titulo ?? 'Admin') ?> — JapanConvenience</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="/JapanConvenience/assets/css/style.css?v=21">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>
<body class="adm-body">
<div class="adm-shell">
  <!-- SIDEBAR -->
  <aside class="adm-side">
    <a href="index.php" class="adm-brand"><span class="jc-logo-icon">J</span><span><b>JapanConv</b><br><small>PAINEL ADMIN</small></span></a>
    <nav class="adm-nav">
      <a href="index.php" class="<?= menuClass('dashboard', $menuAtivo) ?>">Dashboard</a>
      <a href="produtos.php" class="<?= menuClass('produtos', $menuAtivo) ?>">Produtos e Estoque</a>
      <a href="categorias.php" class="<?= menuClass('categorias', $menuAtivo) ?>">Categorias</a>
      <a href="pedidos.php" class="<?= menuClass('pedidos', $menuAtivo) ?>">Pedidos</a>
      <a href="clientes.php" class="<?= menuClass('clientes', $menuAtivo) ?>">Clientes</a>
      <a href="configuracoes.php" class="<?= menuClass('config', $menuAtivo) ?>">Configurações</a>
    </nav>
    <div class="adm-user">
      <span class="adm-avatar"><?= e(mb_strtoupper(mb_substr($_SESSION['usuario_nome'] ?? 'A', 0, 1))) ?></span>
      <span><b><?= e($_SESSION['usuario_nome'] ?? 'Admin') ?></b><br><small>Gerente Geral</small></span>
      <a href="../logout.php" title="Sair" class="ms-auto text-decoration-none text-white small">Sair</a>
    </div>
  </aside>

  <!-- CONTEUDO -->
  <div class="adm-main">
    <div class="adm-topbar">
      <h4 class="mb-0 fw-bold"><?= e($titulo ?? '') ?></h4>
      <div class="d-flex align-items-center gap-3 ms-auto">
        <a href="produtos.php" class="position-relative text-dark small text-decoration-none" title="<?= $critCount ?> itens com estoque crítico">Estoque
          <?php if ($critCount > 0): ?><span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:.6rem"><?= $critCount ?></span><?php endif; ?>
        </a>
        <a href="../index.php" target="_blank" class="btn btn-sm btn-outline-dark">Ver loja</a>
      </div>
    </div>
    <div class="adm-content">
      <?php if ($m = flash('sucesso')): ?><div class="alert alert-success"><?= e($m) ?></div><?php endif; ?>
      <?php if ($m = flash('erro')): ?><div class="alert alert-danger"><?= e($m) ?></div><?php endif; ?>
















