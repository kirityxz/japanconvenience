<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
requireLogin();

$stmt = $pdo->prepare("SELECT nome, email FROM usuarios WHERE id = ?");
$stmt->execute([$_SESSION['usuario_id']]);
$eu = $stmt->fetch();

include 'includes/header.php';
?>

<section class="container" style="margin-top:18px">
  <div class="section-heading">
    <div><span class="section-mark"></span><h2>Olá, <?= e(explode(' ', $eu['nome'] ?? 'visitante')[0]) ?></h2></div>
  </div>
  <p class="small text-muted mb-3"><?= e($eu['email'] ?? '') ?> · Gerencie seus dados, senha, endereços e pedidos.</p>

  <div class="conta-grid">
    <a class="conta-card glass" href="conta-dados.php">
      <span class="conta-ico">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="5" width="20" height="14" rx="2"/><circle cx="9" cy="11" r="2"/><path d="M13.5 11H17"/><path d="M13.5 14.5H17"/><path d="M5.5 15.5c.6-1.4 1.9-2 3.5-2s2.9.6 3.5 2"/></svg>
      </span>
      <b>Meu cadastro</b><small>Nome e telefone</small>
      <span class="category-arrow">›</span>
    </a>
    <a class="conta-card glass" href="conta-senha.php">
      <span class="conta-ico">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="7.5" cy="15.5" r="5.5"/><path d="m21 2-9.6 9.6"/><path d="m15.5 7.5 3 3L22 7l-3-3"/></svg>
      </span>
      <b>Trocar senha</b><small>Altere sua senha de acesso</small>
      <span class="category-arrow">›</span>
    </a>
    <a class="conta-card glass" href="meus-pedidos.php">
      <span class="conta-ico">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
      </span>
      <b>Meus pedidos</b><small>Acompanhe suas compras</small>
      <span class="category-arrow">›</span>
    </a>
    <a class="conta-card glass" href="conta-endereco.php">
      <span class="conta-ico">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
      </span>
      <b>Meus endereços</b><small>Endereço de entrega</small>
      <span class="category-arrow">›</span>
    </a>
    <a class="conta-card glass" href="index.php#sobre">
      <span class="conta-ico">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2.992 16.342a2 2 0 0 1 .094 1.167l-1.065 3.29a1 1 0 0 0 1.236 1.168l3.413-.998a2 2 0 0 1 1.099.092 10 10 0 1 0-4.777-4.719"/></svg>
      </span>
      <b>Atendimento</b><small>Fale com a nossa equipe</small>
      <span class="category-arrow">›</span>
    </a>
    <a class="conta-card glass" href="logout.php">
      <span class="conta-ico">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
      </span>
      <b>Sair</b><small>Encerrar a sessão</small>
      <span class="category-arrow">›</span>
    </a>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
