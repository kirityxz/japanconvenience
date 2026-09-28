# 🎨 Design das Telas — JapanConvenience

Identidade: vermelho japonês (#C81E1E) + creme (#FFF8F0), navbar ⛩️, emojis como imagens (funciona offline), Bootstrap 5 responsivo.

## 1. Mapa de telas
- `index.php` — vitrine (hero + busca JS + pílulas de categoria + cards)
- `produto.php` — detalhe (emoji grande + preço + qtd + adicionar)
- `cadastro.php` / `login.php` — forms centralizados em card
- `carrinho.php` — tabela + total + finalizar
- `checkout.php` — resumo + endereço + pagamento
- `meus-pedidos.php` — cards de pedidos com status
- `admin/index.php` — 4 cards KPI + últimos pedidos + estoque baixo + por categoria
- `admin/produtos.php`, `categorias.php`, `pedidos.php`, `clientes.php` — form no topo + tabela

## 2. Wireframes (para recriar no Figma/Canva se o professor pedir)
HOME: [NAVBAR: logo | Início Produtos | Carrinho Entrar] [HERO vermelho] [BUSCA] [CATEGORIAS: Todas Doces Snacks Lamen] [CARDS 4/col: emoji, categoria, nome, preço, Ver Comprar] [FOOTER]
DETALHE: [Voltar] [EMOJI 8rem | categoria, nome, desc, preço, estoque, qtd + Adicionar]
CARRINHO: [tabela: produto preço qtd subtotal 🗑️] [total] [continuar | limpar | finalizar ✅]
ADMIN: [SIDEBAR escura] [KPIs coloridos] [2 colunas: recentes | estoque + categorias]

## 3. Paleta e componentes
- Botões: `btn-danger` (comprar/cadastrar), `btn-success` (finalizar), `btn-outline-danger` (ver)
- Badges de categoria, alerts de sucesso/erro via sessão, cards `.jp-card` com hover
- Validações JS em `assets/js/main.js`: filtro `data-nome`, `confirm()` excluir, senha ≥6, `alterarQtd()`
