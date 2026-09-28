<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

if (isset($_GET['del'])) {
    $id = (int)$_GET['del'];
    $pdo->prepare("DELETE FROM pedido_itens WHERE produto_id=?")->execute([$id]);
    $pdo->prepare("DELETE FROM produtos WHERE id=?")->execute([$id]);
    $_SESSION['sucesso'] = 'Produto excluído!';
    header('Location: produtos.php'); exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']); $cat = (int)$_POST['categoria_id'];
    $desc = trim($_POST['descricao']); $preco = (float)str_replace(',', '.', $_POST['preco']);
    $est = (int)$_POST['estoque']; $img = trim($_POST['imagem'] ?: '🍱');
    $dest = isset($_POST['destaque']) ? 1 : 0;
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        $pdo->prepare("UPDATE produtos SET categoria_id=?, nome=?, descricao=?, preco=?, estoque=?, imagem=?, destaque=? WHERE id=?")
            ->execute([$cat, $nome, $desc, $preco, $est, $img, $dest, $id]);
        $_SESSION['sucesso'] = 'Produto atualizado!';
    } else {
        $pdo->prepare("INSERT INTO produtos (categoria_id, nome, descricao, preco, estoque, imagem, destaque) VALUES (?,?,?,?,?,?,?)")
            ->execute([$cat, $nome, $desc, $preco, $est, $img, $dest]);
        $_SESSION['sucesso'] = 'Produto cadastrado!';
    }
    header('Location: produtos.php'); exit;
}

// Filtros (como no layout)
$fcat = (int)($_GET['fcat'] ?? 0);
$fest = $_GET['fest'] ?? 'todos';
$where = []; $params = [];
if ($fcat > 0) { $where[] = 'p.categoria_id = ?'; $params[] = $fcat; }
if ($fest === 'ok') $where[] = 'p.estoque > 10';
elseif ($fest === 'baixo') $where[] = 'p.estoque BETWEEN 1 AND 10';
elseif ($fest === 'esgotado') $where[] = 'p.estoque = 0';
$sql = "SELECT p.*, c.nome AS cat FROM produtos p JOIN categorias c ON c.id=p.categoria_id";
if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
$sql .= " ORDER BY p.id";
$stmt = $pdo->prepare($sql); $stmt->execute($params);
$lista = $stmt->fetchAll();

$edit = null;
if (isset($_GET['edit'])) {
    $s = $pdo->prepare("SELECT * FROM produtos WHERE id=?"); $s->execute([(int)$_GET['edit']]); $edit = $s->fetch();
}
$cats = $pdo->query("SELECT * FROM categorias ORDER BY nome")->fetchAll();

$titulo = 'Gestão de Produtos';
$menuAtivo = 'produtos';
include '_layout_top.php';
?>
<div class="d-flex gap-2 flex-wrap align-items-center mb-3">
  <form method="GET" class="d-flex gap-2 flex-wrap">
    <select name="fcat" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
      <option value="0">Filtrar Categoria</option>
      <?php foreach ($cats as $c): ?><option value="<?= $c['id'] ?>" <?= $fcat == $c['id'] ? 'selected' : '' ?>><?= e($c['nome']) ?></option><?php endforeach; ?>
    </select>
    <select name="fest" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
      <option value="todos" <?= $fest === 'todos' ? 'selected' : '' ?>>Status Estoque</option>
      <option value="ok" <?= $fest === 'ok' ? 'selected' : '' ?>>Em estoque</option>
      <option value="baixo" <?= $fest === 'baixo' ? 'selected' : '' ?>>Crítico (≤10)</option>
      <option value="esgotado" <?= $fest === 'esgotado' ? 'selected' : '' ?>>Esgotado</option>
    </select>
  </form>
  <button class="btn btn-danger btn-sm ms-auto fw-bold" data-bs-toggle="collapse" data-bs-target="#formProd">+ Cadastrar Produto</button>
</div>

<div class="collapse <?= $edit ? 'show' : '' ?> mb-3" id="formProd">
  <div class="adm-card">
    <h6><?= $edit ? 'Editar produto PRO-' . str_pad($edit['id'], 2, '0', STR_PAD_LEFT) : 'Novo produto' ?></h6>
    <form method="POST" class="row g-2">
      <input type="hidden" name="id" value="<?= $edit['id'] ?? 0 ?>">
      <div class="col-md-4"><input name="nome" class="form-control form-control-sm" placeholder="Nome *" required value="<?= e($edit['nome'] ?? '') ?>"></div>
      <div class="col-md-2"><select name="categoria_id" class="form-select form-select-sm"><?php foreach ($cats as $c): ?>
        <option value="<?= $c['id'] ?>" <?= (($edit['categoria_id'] ?? '') == $c['id']) ? 'selected' : '' ?>><?= e($c['nome']) ?></option><?php endforeach; ?></select></div>
      <div class="col-md-2"><input name="preco" type="number" step="0.01" class="form-control form-control-sm" placeholder="Preço R$ *" required value="<?= e($edit['preco'] ?? '') ?>"></div>
      <div class="col-md-1"><input name="estoque" type="number" class="form-control form-control-sm" placeholder="Est." required value="<?= e($edit['estoque'] ?? '10') ?>"></div>
      <div class="col-md-1"><input name="imagem" class="form-control form-control-sm" placeholder="Img" value="<?= e($edit['imagem'] ?? '') ?>"></div>
      <div class="col-md-2"><input name="descricao" class="form-control form-control-sm" placeholder="Descrição" value="<?= e($edit['descricao'] ?? '') ?>"></div>
      <div class="col-12 d-flex gap-2 align-items-center">
        <div class="form-check"><input type="checkbox" name="destaque" class="form-check-input" <?= !empty($edit['destaque']) ? 'checked' : '' ?>><label class="form-check-label small">Destaque</label></div>
        <button class="btn btn-danger btn-sm"><?= $edit ? 'Salvar' : 'Cadastrar' ?></button>
        <?php if ($edit): ?><a href="produtos.php" class="btn btn-secondary btn-sm">Cancelar</a><?php endif; ?>
      </div>
    </form>
  </div>
</div>

<div class="adm-card">
  <div class="table-responsive"><table class="table adm-table mb-0">
    <thead><tr><th>Código</th><th>Nome do Produto</th><th>Categoria</th><th>Preço</th><th>Estoque Atual</th><th>Ações</th></tr></thead>
    <tbody>
    <?php foreach ($lista as $p): ?>
      <tr>
        <td class="text-muted">PRO-<?= str_pad($p['id'], 2, '0', STR_PAD_LEFT) ?></td>
        <td><b><?= e($p['nome']) ?></b></td>
        <td><?= e($p['cat']) ?></td>
        <td><?= precoBR($p['preco']) ?></td>
        <td>
          <?php if ($p['estoque'] == 0): ?><span class="dot out"></span><span class="text-danger">0 un (Esgotado)</span>
          <?php elseif ($p['estoque'] <= 10): ?><span class="dot low"></span><?= $p['estoque'] ?> un (Crítico)
          <?php else: ?><span class="dot ok"></span><?= $p['estoque'] ?> un<?php endif; ?>
        </td>
        <td class="text-nowrap">
          <a href="produtos.php?edit=<?= $p['id'] ?>#formProd" class="btn btn-sm btn-outline-secondary" title="Editar">Editar</a>
          <a href="produtos.php?del=<?= $p['id'] ?>" class="btn btn-sm btn-outline-danger btn-excluir" title="Excluir">Excluir</a>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$lista): ?><tr><td colspan="6" class="text-muted">Nenhum produto com este filtro.</td></tr><?php endif; ?>
    </tbody>
  </table></div>
</div>
<?php include '_layout_bottom.php'; ?>
