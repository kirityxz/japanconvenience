<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();
if (isset($_GET['del'])) {
    $id = (int)$_GET['del'];
    $n = $pdo->prepare("SELECT COUNT(*) FROM produtos WHERE categoria_id=?");
    $n->execute([$id]);
    if ($n->fetchColumn() > 0) $_SESSION['erro'] = 'Não pode excluir: há produtos nesta categoria!';
    else { $pdo->prepare("DELETE FROM categorias WHERE id=?")->execute([$id]); $_SESSION['sucesso'] = 'Categoria excluída!'; }
    header('Location: categorias.php'); exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']); $desc = trim($_POST['descricao']); $icone = trim($_POST['icone'] ?: '🍱');
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) $pdo->prepare("UPDATE categorias SET nome=?, descricao=?, icone=? WHERE id=?")->execute([$nome, $desc, $icone, $id]);
    else $pdo->prepare("INSERT INTO categorias (nome, descricao, icone) VALUES (?,?,?)")->execute([$nome, $desc, $icone]);
    $_SESSION['sucesso'] = 'Categoria salva!';
    header('Location: categorias.php'); exit;
}
$edit = null;
if (isset($_GET['edit'])) { $s = $pdo->prepare("SELECT * FROM categorias WHERE id=?"); $s->execute([(int)$_GET['edit']]); $edit = $s->fetch(); }
$lista = $pdo->query("SELECT c.*, (SELECT COUNT(*) FROM produtos p WHERE p.categoria_id=c.id) AS qtd FROM categorias c ORDER BY c.id")->fetchAll();

$titulo = 'Gestão de Categorias';
$menuAtivo = 'categorias';
include '_layout_top.php';
?>
<div class="adm-card mb-3">
  <h6><?= $edit ? 'Editar categoria' : 'Nova categoria' ?></h6>
  <form method="POST" class="row g-2">
    <input type="hidden" name="id" value="<?= $edit['id'] ?? 0 ?>">
    <div class="col-md-4"><input name="nome" class="form-control form-control-sm" placeholder="Nome *" required value="<?= e($edit['nome'] ?? '') ?>"></div>
    <div class="col-md-1"><input name="icone" class="form-control form-control-sm" placeholder="-" value="<?= e($edit['icone'] ?? '') ?>"></div>
    <div class="col-md-5"><input name="descricao" class="form-control form-control-sm" placeholder="Descrição" value="<?= e($edit['descricao'] ?? '') ?>"></div>
    <div class="col-md-2"><button class="btn btn-danger btn-sm w-100">Salvar</button></div>
  </form>
</div>
<div class="adm-card">
  <table class="table adm-table mb-0">
    <thead><tr><th>Código</th><th>Categoria</th><th>Descrição</th><th>Produtos</th><th>Ações</th></tr></thead>
    <tbody>
    <?php foreach ($lista as $c): ?>
      <tr><td class="text-muted">CAT-<?= str_pad($c['id'], 2, '0', STR_PAD_LEFT) ?></td>
      <td><b><?= e($c['nome']) ?></b></td><td><?= e($c['descricao']) ?></td><td><?= $c['qtd'] ?></td>
      <td class="text-nowrap"><a href="categorias.php?edit=<?= $c['id'] ?>" class="btn btn-sm btn-outline-secondary">Editar</a>
      <a href="categorias.php?del=<?= $c['id'] ?>" class="btn btn-sm btn-outline-danger btn-excluir">Excluir</a></td></tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php include '_layout_bottom.php'; ?>
