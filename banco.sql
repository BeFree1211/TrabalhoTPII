-- Script do banco de dados do sistema Giro (controle de estoque)
-- Tecnologias para Internet II - Trabalho Momento II
-- Gabriely Vieira Dias e Mariana Ferreira Alves
--
-- Para instalar, basta executar este arquivo no phpMyAdmin (aba SQL ou Importar).
-- Usuário para entrar no sistema
--   e-mail admin@giro.com
--   senha  admin123

DROP DATABASE IF EXISTS giro_estoque;
CREATE DATABASE giro_estoque CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE giro_estoque;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL
);

CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(60) NOT NULL
);

CREATE TABLE fornecedores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    razao_social VARCHAR(150) NOT NULL,
    cnpj VARCHAR(18) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL,
    telefone VARCHAR(20) NOT NULL,
    cidade VARCHAR(100) NOT NULL,
    uf CHAR(2) NOT NULL
);

CREATE TABLE produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    categoria_id INT NOT NULL,
    fornecedor_id INT NOT NULL,
    nome VARCHAR(120) NOT NULL,
    sku VARCHAR(8) NOT NULL UNIQUE,
    preco_custo DECIMAL(10,2) NOT NULL,
    preco_venda DECIMAL(10,2) NOT NULL,
    estoque_minimo INT NOT NULL,
    estoque_atual INT NOT NULL DEFAULT 0,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id),
    FOREIGN KEY (fornecedor_id) REFERENCES fornecedores(id)
);

CREATE TABLE clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    cpf VARCHAR(14) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL,
    telefone VARCHAR(20) NOT NULL,
    data_nascimento DATE NOT NULL,
    cidade VARCHAR(100) NOT NULL
);

CREATE TABLE pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    usuario_id INT NOT NULL,
    data_pedido DATE NOT NULL,
    forma_pagamento VARCHAR(30) NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

CREATE TABLE itens_pedido (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    produto_id INT NOT NULL,
    quantidade INT NOT NULL,
    preco_unitario DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id),
    FOREIGN KEY (produto_id) REFERENCES produtos(id)
);

CREATE TABLE movimentacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produto_id INT NOT NULL,
    usuario_id INT NOT NULL,
    tipo VARCHAR(10) NOT NULL,
    quantidade INT NOT NULL,
    data_movimentacao DATE NOT NULL,
    observacao VARCHAR(255),
    FOREIGN KEY (produto_id) REFERENCES produtos(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- Dados iniciais

-- a senha está criptografada com password_hash do PHP (senha original: admin123)
INSERT INTO usuarios (nome, email, senha) VALUES
('Administrador', 'admin@giro.com', '$2y$10$Zoc2CweZpsSZbKu0aB57H.jHNg0Nd2vofzvG6FDZNoi5xHN.5trGS');

INSERT INTO categorias (nome) VALUES
('Eletrônicos'), ('Alimentos'), ('Vestuário'), ('Papelaria'), ('Limpeza');

INSERT INTO fornecedores (razao_social, cnpj, email, telefone, cidade, uf) VALUES
('Distribuidora Triângulo Ltda', '11.222.333/0001-81', 'vendas@triangulo.com.br', '(34) 3222-1000', 'Uberlândia', 'MG'),
('Papel e Cia Atacado Ltda', '44.555.666/0001-81', 'contato@papelecia.com.br', '(11) 4002-2000', 'São Paulo', 'SP'),
('Nordeste Limpeza S.A.', '77.888.999/0001-81', 'comercial@nelimpeza.com.br', '(81) 3030-3000', 'Recife', 'PE');

INSERT INTO produtos (categoria_id, fornecedor_id, nome, sku, preco_custo, preco_venda, estoque_minimo, estoque_atual) VALUES
(1, 1, 'Fone de ouvido Bluetooth', 'ELE-0001', 45.00, 89.90, 5, 12),
(1, 1, 'Carregador USB-C 20W', 'ELE-0002', 28.00, 59.90, 5, 3),
(4, 2, 'Caderno universitário', 'PAP-0001', 9.50, 18.90, 20, 40),
(4, 2, 'Caneta esferográfica azul', 'PAP-0002', 0.90, 2.50, 50, 150),
(5, 3, 'Detergente neutro 500ml', 'LIM-0001', 1.80, 3.49, 30, 24);

INSERT INTO clientes (nome, cpf, email, telefone, data_nascimento, cidade) VALUES
('Ana Paula Ribeiro', '123.456.789-09', 'ana.ribeiro@email.com', '(34) 99111-2222', '1990-04-12', 'Uberlândia'),
('Carlos Mendes', '987.654.321-00', 'carlos.mendes@email.com', '(34) 99333-4444', '1958-09-30', 'Uberaba'),
('Juliana Freitas', '456.789.123-64', 'ju.freitas@email.com', '(34) 99555-6666', '2001-01-25', 'Araguari');
