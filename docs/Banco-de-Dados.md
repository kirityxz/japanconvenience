# 🗄️ Estrutura do Banco de Dados — JapanConvenience

Banco: `japan_convenience` (utf8mb4). Arquivo de criação: `database.sql` (importar no phpMyAdmin).

## 1. DER (recriar no Lucidchart: 5 entidades, ligar com as FKs abaixo)
usuarios 1───N pedidos 1───N pedido_itens N───1 produtos N───1 categorias

## 2. Tabelas
**usuarios**(id PK AI, nome 100 NN, email 150 UNIQUE NN, senha 255 NN hash, tipo ENUM cliente/admin, telefone 20, endereco 255, criado_em TIMESTAMP)
**categorias**(id PK AI, nome 100 NN, descricao 255, icone 10 emoji, criado_em)
**produtos**(id PK AI, categoria_id FK→categorias.id RESTRICT NN, nome 120 NN, descricao TEXT, preco DECIMAL(10,2) NN, estoque INT NN, imagem 255 emoji, destaque BOOL, ativo BOOL, criado_em)
**pedidos**(id PK AI, usuario_id FK→usuarios.id CASCADE NN, total DECIMAL(10,2) NN, status ENUM pendente/pago/enviado/entregue/cancelado, endereco_entrega 255 NN, forma_pagamento 50, criado_em)
**pedido_itens**(id PK AI, pedido_id FK→pedidos.id CASCADE NN, produto_id FK→produtos.id RESTRICT NN, quantidade INT NN, preco_unit DECIMAL(10,2) NN)

## 3. Relacionamentos (para o Lucidchart)
- categorias ─< produtos (uma categoria tem N produtos)
- usuarios ─< pedidos (um cliente tem N pedidos)
- pedidos ─< pedido_itens (um pedido tem N itens)
- produtos ─< pedido_itens (um produto aparece em N itens)

## 4. Dados de exemplo (já no database.sql)
- 4 categorias: Doces 🍬, Snacks 🍘, Lamen & Bebidas 🍜, Conveniência 🍱
- 9 produtos (Mochi 12.90, Pocky 9.90, Dorayaki 8.50, KitKat Matcha 14.90, Calbee 11.90, Senbei 10.50, Lamen 13.90, Ramune 9.50, Onigiri 10.90)
- admin@japanconvenience.com / admin123 | cliente@teste.com / 123456

## 5. Como importar
1. Ligue Apache + MySQL no XAMPP. 2. Abra http://localhost/phpmyadmin → Importar → escolha `database.sql` → Executar.
