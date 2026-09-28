-- =============================================
-- JapanConvenience - Banco de Dados
-- Loja de conveniencia japonesa (doces, salgadinhos, bebidas)
-- Como usar: importe no phpMyAdmin (http://localhost/phpmyadmin)
-- ou via mysql: mysql -u root < database.sql
-- =============================================

CREATE DATABASE IF NOT EXISTS japan_convenience CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE japan_convenience;

-- ---------- TABELA: usuarios ----------
CREATE TABLE IF NOT EXISTS usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  senha VARCHAR(255) DEFAULT NULL,
  google_id VARCHAR(100) DEFAULT NULL UNIQUE,
  tipo ENUM('cliente','admin') NOT NULL DEFAULT 'cliente',
  telefone VARCHAR(20) DEFAULT NULL,
  endereco VARCHAR(255) DEFAULT NULL,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------- TABELA: configuracoes (opções da loja) ----------
CREATE TABLE IF NOT EXISTS configuracoes (
  chave VARCHAR(50) PRIMARY KEY,
  valor VARCHAR(50) NOT NULL
) ENGINE=InnoDB;

INSERT IGNORE INTO configuracoes (chave, valor) VALUES ('pix_desconto', '10');

-- ---------- TABELA: categorias ----------
CREATE TABLE IF NOT EXISTS categorias (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  descricao VARCHAR(255) DEFAULT NULL,
  icone VARCHAR(10) DEFAULT '🍱',
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------- TABELA: produtos ----------
CREATE TABLE IF NOT EXISTS produtos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  categoria_id INT NOT NULL,
  nome VARCHAR(120) NOT NULL,
  descricao TEXT,
  preco DECIMAL(10,2) NOT NULL,
  estoque INT NOT NULL DEFAULT 0,
  imagem VARCHAR(255) DEFAULT '🍱',
  destaque TINYINT(1) DEFAULT 0,
  ativo TINYINT(1) DEFAULT 1,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ---------- TABELA: pedidos ----------
CREATE TABLE IF NOT EXISTS pedidos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NOT NULL,
  total DECIMAL(10,2) NOT NULL,
  status ENUM('pendente','pago','enviado','entregue','cancelado') DEFAULT 'pendente',
  endereco_entrega VARCHAR(255) NOT NULL,
  forma_pagamento VARCHAR(50) NOT NULL DEFAULT 'pix',
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------- TABELA: pedido_itens ----------
CREATE TABLE IF NOT EXISTS pedido_itens (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pedido_id INT NOT NULL,
  produto_id INT NOT NULL,
  quantidade INT NOT NULL,
  preco_unit DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
  FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- =============================================
-- DADOS INICIAIS
-- =============================================

-- Categorias
INSERT INTO categorias (nome, descricao, icone) VALUES
('Doces Japoneses', 'Wagashi, chocolates, sobremesas', 'D'),
('Salgadinhos & Snacks', 'Batatas, algas, castanhas e mais', 'S'),
('Lamen & Râmen', 'Râmen, udon, soba e macarrão', 'L'),
('Conveniencia', 'Snacks, biscoitos, bebidas e muito mais', 'C'),
('Bebidas', 'Chás, energéticos, refrigerantes e sucos', 'B');

-- Admin (senha: admin123) + cliente teste (senha: 123456)
-- Hashes gerados com password_hash() do PHP
INSERT INTO usuarios (nome, email, senha, tipo, telefone, endereco) VALUES
('Administrador', 'admin@japanconvenience.com', '$2y$10$lYGEYHeyTokbXDeY59xfHOGkmy89bcj7Uj8/mgizAbBAOo3Ldt7au', 'admin', '(11) 99999-0000', 'Rua Sakura, 123 - Liberdade, SP'),
('Cliente Teste', 'cliente@teste.com', '$2y$10$7w7vhuT8M94At32f/T.yTemzzeHzHmMFJMyJWD8iXkF5hP5lacU6y', 'cliente', '(11) 98888-1111', 'Av. Paulista, 1000 - SP');

-- Produtos (minimo 6 exigido — aqui sao 9)
INSERT INTO produtos (categoria_id, nome, descricao, preco, estoque, imagem, destaque) VALUES
(1, 'Mochi de Morango', 'Bolinho de arroz glutinoso recheado com morango e anko. Tradicao japonesa!', 12.90, 50, '🍓', 1),
(1, 'Pocky Chocolate 47g', 'Palitinhos crocantes cobertos com chocolate. O snack mais famoso do Japao.', 9.90, 80, '🍫', 1),
(1, 'Dorayaki Azuki', 'Panquequinhas fofas recheadas com doce de feijao azuki. Favorito do Doraemon!', 8.50, 40, '🥞', 0),
(1, 'KitKat Matcha', 'KitKat sabor cha verde matcha, edicao japonesa original. Caixa com 10 mini.', 14.90, 60, '🍵', 1),
(2, 'Salgadinho Calbee 90g', 'Chips de camarao crocante, sucesso absoluto no Japao.', 11.90, 70, '🍤', 0),
(2, 'Senbei - Biscoito de Arroz', 'Biscoito de arroz com shoyu, crocante e levemente salgado. Pacote 150g.', 10.50, 45, '🍘', 0),
(3, 'Lamen Cup Nissin Shoyu', 'Lamen instantaneo sabor shoyu. Pronto em 3 minutos!', 13.90, 100, '🍜', 1),
(5, 'Refrigerante Ramune 200ml', 'Refrigerante japones da garrafinha com bolinha de gude. Sabor original.', 9.50, 90, '🥤', 1),
(4, 'Onigiri de Salmao', 'Bolinho de arroz com recheio de salmao grelhado e alga nori. Feito na hora!', 10.90, 30, '🍙', 1);
