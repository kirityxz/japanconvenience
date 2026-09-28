// JapanConvenience - busca, toast e utilidades
document.addEventListener('DOMContentLoaded', () => {
  // Filtro de produtos na home (busca do topo)
  const busca = document.getElementById('busca');
  const filtrar = () => {
    const t = (busca ? busca.value : '').toLowerCase().trim();
    document.querySelectorAll('.produto-item').forEach(card => {
      const nome = (card.dataset.nome || card.dataset.name || '').toLowerCase();
      card.style.display = !t || nome.includes(t) ? '' : 'none';
    });
  };
  if (busca) busca.addEventListener('input', filtrar);
  document.querySelector('.search-submit')?.addEventListener('click', () => {
    filtrar();
    document.getElementById('produtos')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
  });

  // Botão "Todas as Categorias" rola até as categorias
  document.getElementById('categoryButton')?.addEventListener('click', () => {
    document.getElementById('categorias')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
  });

  // Carrossel do banner principal (3 pontinhos)
  const heroSlides = [
    { src: '/JapanConvenience/assets/img/hero-snacks.jpg', alt: 'Prateleira de produtos japoneses' },
    { src: '/JapanConvenience/assets/img/hero-mesa.jpg', alt: 'Mesa com pratos japoneses' },
    { src: '/JapanConvenience/assets/img/cat-doces.jpg', alt: 'Doces japoneses variados' }
  ];
  heroSlides.forEach(s => { const im = new Image(); im.src = s.src; });
  const heroPhoto = document.getElementById('heroPhoto');
  const heroDots = [...document.querySelectorAll('.hero-dots button')];
  let heroIdx = 0, heroTimer = null;
  function showSlide(i) {
    heroIdx = ((i % heroSlides.length) + heroSlides.length) % heroSlides.length;
    heroDots.forEach((d, k) => d.classList.toggle('active', k === heroIdx));
    if (!heroPhoto || heroPhoto.getAttribute('src') === heroSlides[heroIdx].src) return;
    heroPhoto.style.opacity = '0';
    setTimeout(() => {
      heroPhoto.src = heroSlides[heroIdx].src;
      heroPhoto.alt = heroSlides[heroIdx].alt;
      heroPhoto.style.opacity = '1';
    }, 250);
  }
  function restartAuto() {
    clearInterval(heroTimer);
    heroTimer = setInterval(() => showSlide(heroIdx + 1), 6000);
  }
  if (heroPhoto && heroDots.length) {
    heroDots.forEach(d => {
      d.addEventListener('click', () => {
        showSlide(parseInt(d.dataset.slide || '0', 10));
        restartAuto();
      });
    });
    restartAuto();
  }

  // Confirma excluir (admin)
  document.querySelectorAll('.btn-excluir').forEach(b => {
    b.addEventListener('click', e => {
      if (!confirm('Tem certeza que deseja excluir?')) e.preventDefault();
    });
  });

  // Valida cadastro (espelha as regras do servidor)
  const formCad = document.getElementById('form-cadastro');
  if (formCad) {
    formCad.addEventListener('submit', e => {
      const nome = (document.getElementById('nome').value || '').trim().replace(/\s+/g, ' ');
      const email = (formCad.querySelector('input[name="email_cad"]').value || '').trim();
      const senha = document.getElementById('senha').value;
      const senha2 = document.getElementById('senha2');
      if (!/^[\p{L}\p{M}]+(?:[ '\-][\p{L}\p{M}]+)*$/u.test(nome) || nome.length < 3) {
        alert('Informe seu nome completo usando apenas letras.');
        e.preventDefault(); return;
      }
      if (!/^[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}$/.test(email) || email.length > 254) {
        alert('E-mail inválido. Use um e-mail real (ex: voce@gmail.com).');
        e.preventDefault(); return;
      }
      if (senha.length < 8 || !/[A-Z]/.test(senha) || !/[a-z]/.test(senha) || !/[0-9]/.test(senha) || !/[^A-Za-z0-9]/.test(senha)) {
        alert('A senha precisa de 8+ caracteres, com maiúscula, minúscula, número e símbolo.');
        e.preventDefault(); return;
      }
      if (senha2 && senha !== senha2.value) { alert('As senhas não conferem!'); e.preventDefault(); }
    });
  }

  // Mostra/oculta senha
  document.querySelectorAll('[data-toggle-pass]').forEach(b => {
    b.addEventListener('click', () => {
      const el = document.getElementById(b.dataset.togglePass);
      if (el) el.type = el.type === 'password' ? 'text' : 'password';
    });
  });
});

function alterarQtd(id, delta) {
  const el = document.getElementById('qtd-' + id);
  let v = parseInt(el.value || '1') + delta;
  if (v < 1) v = 1;
  el.value = v;
}

// ---------- Carrinho AJAX (adicionar / + / - sem sair da página) ----------
function escHtml(s) {
  return String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
}

function renderMiniCarrinho(d) {
  const box = document.querySelector('.mini-cart');
  if (!box) return;
  if (!d.items.length) {
    box.innerHTML = '<p class="mini-empty">Seu carrinho está vazio.</p><a href="/JapanConvenience/index.php#destaques" class="add-btn">Ver produtos</a>';
    return;
  }
  let h = '<ul>';
  d.items.slice(0, 4).forEach(it => {
    h += '<li>' + (it.foto ? '<img src="' + it.foto + '" alt="">' : '<span class="mini-noimg">JC</span>') +
      '<div class="mini-info"><b>' + escHtml(it.nome) + '</b>' +
      '<div class="mini-qty"><a href="/JapanConvenience/carrinho.php?upd=' + it.id + '&qtd=' + (it.qtd - 1) + '" aria-label="Diminuir">−</a><span>' + it.qtd + '</span><a href="/JapanConvenience/carrinho.php?upd=' + it.id + '&qtd=' + (it.qtd + 1) + '" aria-label="Aumentar">+</a>' +
      '<small>× ' + it.precoFmt + '</small></div></div>' +
      '<b class="mini-sub">' + it.subFmt + '</b></li>';
  });
  h += '</ul>';
  if (d.items.length > 4) h += '<p class="mini-more">+ ' + (d.items.length - 4) + ' outros itens…</p>';
  h += '<div class="mini-total"><span>Total</span><b>' + d.totalFmt + '</b></div>' +
    '<div class="mini-actions"><a href="/JapanConvenience/carrinho.php" class="btn btn-sm btn-outline-secondary flex-fill">Ver carrinho</a>' +
    '<a href="/JapanConvenience/checkout.php" class="add-btn flex-fill">Finalizar</a></div>';
  box.innerHTML = h;
}

function atualizaTopoCarrinho(d) {
  const pill = document.querySelector('.cart.action-pill');
  if (pill) {
    let badge = pill.querySelector('.counter');
    const small = pill.querySelector('.action-copy small');
    if (d.count > 0) {
      if (!badge) { badge = document.createElement('span'); badge.className = 'counter'; pill.appendChild(badge); }
      badge.textContent = d.count;
      if (small) small.textContent = d.totalFmt + ' · ' + d.countTxt;
    } else {
      if (badge) badge.remove();
      if (small) small.textContent = d.totalFmt;
    }
  }
  renderMiniCarrinho(d);
}

function atualizaPaginaCarrinho(d) {
  if (!document.querySelector('.cart-summary')) return;
  if (!d.items.length) { location.reload(); return; }
  const porId = {};
  d.items.forEach(it => { porId[it.id] = it; });
  document.querySelectorAll('.cart-row[data-row]').forEach(row => {
    const id = row.getAttribute('data-row');
    const it = porId[id];
    if (!it) { row.remove(); return; }
    const qn = row.querySelector('.qty-num');
    if (qn) qn.textContent = it.qtd;
    const sub = row.querySelector('.row-sub');
    if (sub) sub.textContent = it.subFmt;
    const links = row.querySelectorAll('.qty a');
    if (links[0]) links[0].setAttribute('href', 'carrinho.php?upd=' + id + '&qtd=' + (it.qtd - 1));
    if (links[1]) links[1].setAttribute('href', 'carrinho.php?upd=' + id + '&qtd=' + (it.qtd + 1));
  });
  const lab = document.getElementById('sumLabel');
  if (lab) lab.textContent = 'Subtotal (' + d.countTxt + ')';
  const st = document.getElementById('sumSubtotal');
  if (st) st.textContent = d.totalFmt;
  const tt = document.getElementById('sumTotal');
  if (tt) tt.textContent = d.totalFmt;
  const pd = document.getElementById('sumPixDesc');
  if (pd && d.pixDescFmt) pd.textContent = '− ' + d.pixDescFmt;
  const pt = document.getElementById('sumPixTotal');
  if (pt && d.pixTotalFmt) pt.textContent = d.pixTotalFmt;
  const cc = document.querySelector('.cart-head .cart-count');
  if (cc) cc.textContent = d.countTxt;
}

async function acaoCarrinho(url, origem) {
  try {
    const sep = url.includes('?') ? '&' : '?';
    const r = await fetch(url + sep + 'ajax=1', { headers: { 'X-Requested-With': 'fetch' } });
    const d = await r.json();
    if (!d || !d.ok) throw 0;
    atualizaTopoCarrinho(d);
    if (origem === 'add') showToast(d.msg || 'Produto adicionado ao carrinho.');
    else if (d.msg) showToast(d.msg);
    atualizaPaginaCarrinho(d);
  } catch (err) {
    location.href = url;
  }
}

document.addEventListener('click', e => {
  const t = e.target;
  const a = (t.closest ? t.closest('a[href*="carrinho.php?add="], a[href*="carrinho.php?upd="], a[href*="carrinho.php?rem="]') : null);
  if (!a) return;
  if (a.href.includes('agora=')) return; // Comprar agora: navega de verdade
  e.preventDefault();
  acaoCarrinho(a.getAttribute('href'), a.href.includes('add=') ? 'add' : 'upd');
});

// Form da página do produto (adicionar com quantidade, sem sair)
const formComprar = document.getElementById('form-comprar');
if (formComprar) {
  formComprar.addEventListener('submit', e => {
    if (e.submitter && e.submitter.name === 'agora') return; // Comprar agora: envia normal
    e.preventDefault();
    const fd = new FormData(formComprar);
    acaoCarrinho('carrinho.php?add=' + encodeURIComponent(fd.get('add')) + '&qtd=' + encodeURIComponent(fd.get('qtd') || 1), 'add');
  });
}

// ---------- Galeria de fotos (setas ‹ › nos cards e no produto) ----------
function galFotos(box) {
  try { return JSON.parse(box.dataset.gal || '[]'); } catch (e) { return []; }
}
function galMostrar(box, i) {
  const fotos = galFotos(box);
  if (fotos.length < 2) return;
  i = ((i % fotos.length) + fotos.length) % fotos.length;
  box.dataset.idx = i;
  const img = box.querySelector('img');
  if (img) img.src = fotos[i];
  const c = box.querySelector('.gal-count');
  if (c) c.textContent = (i + 1) + ' / ' + fotos.length;
  box.querySelectorAll('.gal-thumb').forEach((t, k) => t.classList.toggle('on', k === i));
}
document.addEventListener('click', e => {
  const t = e.target;
  if (!t.closest) return;
  const box = t.closest('.galeria');
  if (!box) return;
  const th = t.closest('.gal-thumb');
  if (th) {
    e.preventDefault();
    galMostrar(box, parseInt(th.dataset.k || '0', 10));
    return;
  }
  const prev = t.closest('.gal-prev');
  const next = t.closest('.gal-next');
  if (prev || next) {
    e.preventDefault();
    if (e.stopPropagation) e.stopPropagation();
    galMostrar(box, parseInt(box.dataset.idx || '0', 10) + (prev ? -1 : 1));
  }
});

function showToast(message) {
  const toast = document.getElementById('toast');
  if (!toast) return;
  toast.textContent = message;
  toast.classList.add('show');
  clearTimeout(window.__toastTimer);
  window.__toastTimer = setTimeout(() => toast.classList.remove('show'), 2200);
}
