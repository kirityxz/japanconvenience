# 📄 Documento de Requisitos — JapanConvenience

**Projeto:** JapanConvenience — Loja de conveniência japonesa online (doces, salgadinhos, lamen e bebidas)
**Tecnologias:** HTML • CSS • Bootstrap 5 • JavaScript • PHP 8 • MySQL (XAMPP)
**Autor:** trabalho escolar | **Data:** 2026

## 1. Objetivo
Criar um e-commerce simples onde clientes se cadastram, visualizam produtos japoneses, compram pelo carrinho e o dono gerencia tudo pelo dashboard (produtos, categorias, pedidos e clientes).

## 2. Requisitos Funcionais (RF)
| ID | Requisito | Prioridade |
|----|-----------|------------|
| RF01 | Cadastro de clientes (nome, e-mail único, senha ≥6, telefone, endereço) | Alta |
| RF02 | Login funcional (cliente e admin, senha com hash) | Alta |
| RF03 | Cadastro/edição/exclusão de categorias | Alta |
| RF04 | Cadastro/edição/exclusão de produtos (nome, categoria, preço, estoque, imagem/emoji, destaque) | Alta |
| RF05 | Visualização de produtos com filtro por categoria + busca em JS | Alta |
| RF06 | Página de detalhes do produto com escolha de quantidade | Média |
| RF07 | Carrinho (adicionar, remover, limpar, validar estoque) | Alta |
| RF08 | Checkout/Compra (endereço + pagamento, baixa de estoque, cria pedido) | Alta |
| RF09 | Meus pedidos (histórico do cliente) | Alta |
| RF10 | Dashboard do admin (total vendas, nº pedidos/clientes/produtos, últimos pedidos, estoque baixo, produtos por categoria) | Alta |
| RF11 | Gestão de pedidos (alterar status: pendente/pago/enviado/entregue/cancelado) | Alta |
| RF12 | Lista de clientes + nº de pedidos | Média |
| RF13 | Dados iniciais: ≥3 categorias e ≥6 produtos | Alta |

## 3. Requisitos Não Funcionais (RNF)
- RNF01: Rodar no XAMPP (Apache + MySQL) em `http://localhost/JapanConvenience`
- RNF02: Layout responsivo com Bootstrap 5
- RNF03: Senhas com `password_hash()` (nunca em texto puro)
- RNF04: PDO com prepared statements (anti SQL Injection)
- RNF05: Sessões PHP para login e carrinho
- RNF06: Validação em JS (busca instantânea, confirma exclusão, senha) + validação em PHP

## 4. Regras de Negócio
1. E-mail é único por usuário.
2. Só admin acessa `/admin/*`.
3. Não vender sem estoque (bloqueia e avisa o máximo).
4. Pedido só com cliente logado + endereço ≥5 caracteres.
5. Categoria com produtos não pode ser excluída.
6. Total do pedido = soma (preço_unit × qtd) no momento da compra.

## 5. Atores e Casos de Uso
- **Visitante:** ver produtos, buscar, cadastrar, logar.
- **Cliente:** tudo do visitante + carrinho, checkout, meus pedidos.
- **Admin/Dono:** dashboard, CRUD produtos/categorias, gerenciar pedidos, ver clientes.

## 6. Critérios de aceite (checklist do professor)
- [ ] Login funcional ✅ (`login.php` + `admin@japanconvenience.com / admin123`)
- [ ] Cadastro e login de clientes ✅ (`cadastro.php`)
- [ ] Cadastro de produtos ✅ (`admin/produtos.php`)
- [ ] Cadastro de categorias ✅ (`admin/categorias.php`)
- [ ] Visualização dos produtos ✅ (`index.php`, `produto.php`)
- [ ] Compra pelos clientes ✅ (`carrinho.php` → `checkout.php` → `meus-pedidos.php`)
- [ ] Dashboard do dono ✅ (`admin/index.php`)
- [ ] ≥6 produtos ✅ (9 cadastrados) | ≥3 categorias ✅ (4 cadastradas)
