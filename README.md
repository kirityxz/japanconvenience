# ⛩️ JapanConvenience 🇯🇵 — Loja de Conveniência Japonesa Online

E-commerce em **HTML + CSS + Bootstrap + JavaScript + PHP + MySQL** (XAMPP).
Doces 🍬, salgadinhos 🍘, lamen 🍜 e conveniência 🍱.

## 🚀 Como rodar (5 min)
1. Instale/ligue o **XAMPP** (Apache + MySQL).
2. Pasta já está em `C:\xampp\htdocs\JapanConvenience`.
3. Abra **http://localhost/phpmyadmin** → **Importar** → selecione `database.sql` → Executar.
4. Acesse **http://localhost/JapanConvenience**

## 🔑 Logins de teste
- 👑 Admin: `admin@japanconvenience.com` / `admin123` → vai ao Dashboard
- 🧑 Cliente: `cliente@teste.com` / `123456` (ou cadastre um novo em Cadastrar)

## ✅ Funcionalidades (todas exigidas)
1. Login funcional → `login.php` 2. Cadastro + login clientes → `cadastro.php`
3. Cadastro produtos → `admin/produtos.php` 4. Cadastro categorias → `admin/categorias.php`
5. Visualização → `index.php` + `produto.php` (filtro + busca JS)
6. Compra → `carrinho.php` → `checkout.php` → `meus-pedidos.php`
7. Dashboard dono → `admin/index.php` (vendas, pedidos, clientes, estoque baixo)
8. 9 produtos + 4 categorias já cadastrados no `database.sql`

## 📁 Estrutura
config/database.php • includes/header, footer, functions • assets/css, js
index, produto, carrinho, checkout, meus-pedidos, login, cadastro, logout
admin/index, produtos, categorias, pedidos, clientes • database.sql • docs/

## 📄 Documentação
- `docs/Documento-Requisitos.md` • `docs/Design-Telas.md` • `docs/Banco-de-Dados.md` (com DER p/ Lucidchart)

Feito para apresentação escolar. Arigatou! 🇯🇵
