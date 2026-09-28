<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
requireLogin();
if (empty($_SESSION['carrinho'])) { header('Location: index.php'); exit; }
// Qualquer usuario logado (cliente OU admin) pode finalizar a compra.

// Itens do carrinho
$ids = array_keys($_SESSION['carrinho']);
$ph = implode(',', array_fill(0, count($ids), '?'));
$stmt = $pdo->prepare("SELECT * FROM produtos WHERE id IN ($ph)");
$stmt->execute($ids);
$prods = $stmt->fetchAll();
$subtotal = 0;
foreach ($prods as $p) $subtotal += $p['preco'] * $_SESSION['carrinho'][$p['id']];

// Pagamento escolhido (PIX = desconto global, vale para todos os produtos)
$__pix = pctPix();
$pag = $_POST['pagamento'] ?? 'pix';
$pagValidos = ['pix' => 'pix (-' . $__pix . '%)', 'credito' => 'cartao de credito', 'debito' => 'cartao de debito'];
if (!isset($pagValidos[$pag])) $pag = 'pix';
$desconto = ($pag === 'pix') ? round($subtotal * pixTaxa(), 2) : 0;
$total = $subtotal - $desconto;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ufs = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'];
    $cepNum = preg_replace('/\D/', '', $_POST['cep'] ?? '');
    $bairro = trim($_POST['bairro'] ?? '');
    $rua = trim($_POST['rua'] ?? '');
    $numero = trim($_POST['numero'] ?? '');
    $cidade = trim($_POST['cidade'] ?? '');
    $uf = strtoupper(trim($_POST['estado'] ?? ''));
    if (!preg_match('/^\d{8}$/', $cepNum)) {
        $_SESSION['erro'] = 'CEP inválido: digite os 8 números do CEP.';
        header('Location: checkout.php'); exit;
    }
    if (!preg_match('/^\d{1,4}$/', $numero)) {
        $_SESSION['erro'] = 'Número da casa inválido: só números, até 4 dígitos.';
        header('Location: checkout.php'); exit;
    }
    if (!in_array($uf, $ufs, true)) {
        $_SESSION['erro'] = 'Selecione um estado válido.';
        header('Location: checkout.php'); exit;
    }
    if (strlen($bairro) < 2 || strlen($rua) < 3 || strlen($cidade) < 2) {
        $_SESSION['erro'] = 'Preencha o endereço de entrega completo.';
        header('Location: checkout.php'); exit;
    }
    if (mb_strlen($rua, 'UTF-8') > 50) {
        $_SESSION['erro'] = 'Nome da rua muito longo: máximo 50 caracteres.';
        header('Location: checkout.php'); exit;
    }
    if (mb_strlen($bairro, 'UTF-8') > 30) {
        $_SESSION['erro'] = 'Nome do bairro muito longo: máximo 30 caracteres.';
        header('Location: checkout.php'); exit;
    }
    $cepFmt = substr($cepNum, 0, 5) . '-' . substr($cepNum, 5, 3);
    $endereco = "$rua, Nº $numero - $bairro, $cidade/$uf - CEP $cepFmt";
    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("INSERT INTO pedidos (usuario_id, total, endereco_entrega, forma_pagamento, status, expira_em) VALUES (?,?,?,?,'pendente', DATE_ADD(NOW(), INTERVAL 30 MINUTE))");
        $stmt->execute([$_SESSION['usuario_id'], $total, $endereco, $pagValidos[$pag]]);
        $pedidoId = $pdo->lastInsertId();
        foreach ($prods as $p) {
            $qtd = $_SESSION['carrinho'][$p['id']];
            $chk = $pdo->prepare("SELECT estoque FROM produtos WHERE id=? FOR UPDATE");
            $chk->execute([$p['id']]);
            if ($chk->fetchColumn() < $qtd) throw new Exception("Estoque insuficiente: " . $p['nome']);
            $pdo->prepare("INSERT INTO pedido_itens (pedido_id, produto_id, quantidade, preco_unit) VALUES (?,?,?,?)")
                ->execute([$pedidoId, $p['id'], $qtd, $p['preco']]);
            $pdo->prepare("UPDATE produtos SET estoque = estoque - ? WHERE id=?")->execute([$qtd, $p['id']]);
        }
        $pdo->commit();
        $_SESSION['carrinho'] = [];
        $_SESSION['sucesso'] = "Pedido #$pedidoId criado! Efetue o pagamento em até 30 min.";
        header("Location: pagamento.php?id=$pedidoId"); exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['erro'] = 'Erro ao finalizar: ' . $e->getMessage();
        header('Location: checkout.php'); exit;
    }
}
include 'includes/header.php';
$endSalvo = null;
try {
    $st = $pdo->prepare("SELECT cep, rua, numero, bairro, cidade, uf FROM usuarios WHERE id = ?");
    $st->execute([$_SESSION['usuario_id']]);
    $r = $st->fetch();
    if ($r && !empty($r['rua'])) $endSalvo = $r;
} catch (Exception $e) {}
?>
<div class="container">
  <h4 class="mb-4">Finalizar compra</h4>
  <form method="POST" id="form-checkout">
    <div class="row g-4">
      <!-- ESQUERDA -->
      <div class="col-lg-7">
        <div class="adm-card mb-3">
          <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h6 class="mb-0">Endereço de entrega</h6>
            <?php if ($endSalvo): ?>
              <button type="button" class="btn btn-sm btn-outline-danger" id="btn-end-salvo"
                data-cep="<?= e($endSalvo['cep']) ?>" data-rua="<?= e($endSalvo['rua']) ?>"
                data-numero="<?= e($endSalvo['numero']) ?>" data-bairro="<?= e($endSalvo['bairro']) ?>"
                data-cidade="<?= e($endSalvo['cidade']) ?>" data-uf="<?= e($endSalvo['uf']) ?>">
                Usar meu endereço
              </button>
            <?php endif; ?>
          </div>
          <div class="row g-2 mt-1">
            <div class="col-md-6"><label class="small text-muted">CEP (só números)</label>
              <input name="cep" id="cep" class="form-control" placeholder="01505-001" required inputmode="numeric" maxlength="9"></div>
            <div class="col-md-6"><label class="small text-muted">Bairro <span class="text-muted">(<span id="cont-bairro">0</span>/30)</span></label>
              <input name="bairro" id="bairro" class="form-control" placeholder="Liberdade" required maxlength="30"></div>
            <div class="col-md-8"><label class="small text-muted">Rua / Avenida <span class="text-muted">(<span id="cont-rua">0</span>/50)</span></label>
              <input name="rua" id="rua" class="form-control" placeholder="Rua Galvão Bueno" required maxlength="50"></div>
            <div class="col-md-4"><label class="small text-muted">Número (máx. 4 dígitos)</label>
              <input name="numero" id="numero" class="form-control" placeholder="400" required inputmode="numeric" maxlength="4"></div>
            <div class="col-md-8"><label class="small text-muted">Estado</label>
              <select name="estado" id="estado" class="form-select" required>
                <option value="">Selecione o estado...</option>
                <option value="AC">Acre</option><option value="AL">Alagoas</option><option value="AP">Amapá</option>
                <option value="AM">Amazonas</option><option value="BA">Bahia</option><option value="CE">Ceará</option>
                <option value="DF">Distrito Federal</option><option value="ES">Espírito Santo</option><option value="GO">Goiás</option>
                <option value="MA">Maranhão</option><option value="MT">Mato Grosso</option><option value="MS">Mato Grosso do Sul</option>
                <option value="MG">Minas Gerais</option><option value="PA">Pará</option><option value="PB">Paraíba</option>
                <option value="PR">Paraná</option><option value="PE">Pernambuco</option><option value="PI">Piauí</option>
                <option value="RJ">Rio de Janeiro</option><option value="RN">Rio Grande do Norte</option><option value="RS">Rio Grande do Sul</option>
                <option value="RO">Rondônia</option><option value="RR">Roraima</option><option value="SC">Santa Catarina</option>
                <option value="SP">São Paulo</option><option value="SE">Sergipe</option><option value="TO">Tocantins</option>
              </select></div>
            <div class="col-md-4"><label class="small text-muted">Cidade</label>
              <span id="cidade-wrap"><input class="form-control" placeholder="Escolha o estado ↑" disabled></span></div>
          </div>
        </div>
        <div class="adm-card">
          <h6>Forma de pagamento</h6>
          <div class="d-flex flex-column gap-2 mt-2">
            <label class="pay-opt <?= $pag === 'pix' ? 'selected' : '' ?>">
              <input type="radio" name="pagamento" value="pix" <?= $pag === 'pix' ? 'checked' : '' ?>>
              <span><b>PIX</b> <span class="badge bg-success"><?= $__pix ?>% de desconto</span><br><small class="text-muted">Aprovação imediata</small></span>
            </label>
            <label class="pay-opt <?= $pag === 'credito' ? 'selected' : '' ?>">
              <input type="radio" name="pagamento" value="credito" <?= $pag === 'credito' ? 'checked' : '' ?>>
              <span><b>Cartão de crédito</b><br><small class="text-muted">Em até 3x sem juros</small></span>
            </label>
            <label class="pay-opt <?= $pag === 'debito' ? 'selected' : '' ?>">
              <input type="radio" name="pagamento" value="debito" <?= $pag === 'debito' ? 'checked' : '' ?>>
              <span><b>Cartão de débito</b><br><small class="text-muted">Débito à vista</small></span>
            </label>
          </div>
        </div>
      </div>
      <!-- DIREITA: resumo -->
      <div class="col-lg-5">
        <div class="adm-card">
          <h6>Resumo do Pedido</h6>
          <?php foreach ($prods as $p): $foto = imgProduto($p); $qtd = $_SESSION['carrinho'][$p['id']]; ?>
          <div class="d-flex align-items-center gap-2 py-2 border-bottom">
            <?php if ($foto): ?><img src="<?= $foto ?>" width="44" height="44" class="rounded" style="object-fit:cover">
            <?php else: ?><span class="fs-3"><?= e($p['imagem']) ?></span><?php endif; ?>
            <div class="small flex-grow-1"><b><?= e($p['nome']) ?></b><br><span class="text-muted">Qtd <?= $qtd ?></span></div>
            <b class="small"><?= precoBR($p['preco'] * $qtd) ?></b>
          </div>
          <?php endforeach; ?>
          <div class="d-flex justify-content-between small mt-3"><span class="text-muted">Subtotal</span><span><?= precoBR($subtotal) ?></span></div>
          <div class="d-flex justify-content-between small"><span class="text-muted">Frete Conveniência</span><span class="text-success fw-bold">Grátis</span></div>
          <div class="d-flex justify-content-between small" id="linha-desconto" style="<?= $desconto ? '' : 'display:none' ?>">
            <span class="text-muted">Desconto PIX (<?= $__pix ?>%)</span><span class="text-success">− <span id="val-desconto"><?= precoBR($desconto) ?></span></span>
          </div>
          <hr>
          <div class="d-flex justify-content-between align-items-center mb-3"><b>Total a Pagar</b><b class="fs-5" id="val-total"><?= precoBR($total) ?></b></div>
          <button class="jc-btn-red w-100">Finalizar pagamento</button>
        </div>
      </div>
    </div>
  </form>
</div>
<script>
// Preenche com o endereço salvo na conta
document.getElementById('btn-end-salvo')?.addEventListener('click', e => {
  const b = e.currentTarget;
  const set = (id, v) => { const el = document.getElementById(id); if (el && v) { el.value = v; el.dispatchEvent(new Event('input')); } };
  set('cep', b.dataset.cep);
  set('bairro', b.dataset.bairro);
  set('rua', b.dataset.rua);
  set('numero', b.dataset.numero);
  const uf = b.dataset.uf, est = document.getElementById('estado');
  if (uf && est) est.value = uf;
  if (b.dataset.cidade) {
    document.getElementById('cidade-wrap').innerHTML =
      '<input name="cidade" class="form-control" required value="' + b.dataset.cidade.replace(/"/g, '&quot;') + '">';
  }
});
// CEP: so numeros, maximo 8, formata 00000-000
document.getElementById('cep').addEventListener('input', e => {
  let d = e.target.value.replace(/\D/g, '').slice(0, 8);
  e.target.value = d.length > 5 ? d.slice(0, 5) + '-' + d.slice(5) : d;
});
// Numero da casa: so numeros, maximo 4 digitos
document.getElementById('numero').addEventListener('input', e => {
  e.target.value = e.target.value.replace(/\D/g, '').slice(0, 4);
});
// Contadores de caracteres: rua (max 50) e bairro (max 30)
[['rua', 'cont-rua'], ['bairro', 'cont-bairro']].forEach(([id, cont]) => {
  document.getElementById(id).addEventListener('input', e => {
    document.getElementById(cont).textContent = e.target.value.length;
  });
});
// Estado -> Cidade (API oficial do IBGE; cai para texto se offline)
document.getElementById('estado').addEventListener('change', async e => {
  const uf = e.target.value, wrap = document.getElementById('cidade-wrap');
  if (!uf) { wrap.innerHTML = '<input class="form-control" placeholder="Escolha o estado ↑" disabled>'; return; }
  wrap.innerHTML = '<select name="cidade" class="form-select" required><option value="">Carregando cidades...</option></select>';
  try {
    const r = await fetch('https://servicodados.ibge.gov.br/api/v1/localidades/estados/' + uf + '/municipios');
    if (!r.ok) throw 0;
    const cidades = await r.json();
    wrap.innerHTML = '<select name="cidade" class="form-select" required><option value="">Selecione a cidade...</option>' +
      cidades.map(c => '<option>' + c.nome + '</option>').join('') + '</select>';
  } catch {
    wrap.innerHTML = '<input name="cidade" class="form-control" placeholder="Digite sua cidade" required>';
  }
});
// Atualiza o resumo ao trocar a forma de pagamento (sem recarregar)
const SUB = <?= json_encode($subtotal) ?>;
const PIXTAXA = <?= json_encode(pixTaxa()) ?>;
function br(v) { return 'R$ ' + v.toFixed(2).replace('.', ','); }
document.querySelectorAll('input[name=pagamento]').forEach(r => {
  r.addEventListener('change', () => {
    const escolha = document.querySelector('input[name=pagamento]:checked').value;
    const desc = escolha === 'pix' ? Math.round(SUB * PIXTAXA * 100) / 100 : 0;
    document.getElementById('linha-desconto').style.display = desc ? '' : 'none';
    document.getElementById('val-desconto').textContent = br(desc);
    document.getElementById('val-total').textContent = br(SUB - desc);
    document.querySelectorAll('.pay-opt').forEach(l => l.classList.remove('selected'));
    r.closest('.pay-opt').classList.add('selected');
  });
});
</script>
<?php include 'includes/footer.php'; ?>
