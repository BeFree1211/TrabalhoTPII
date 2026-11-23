<?php
declare(strict_types=1);
require __DIR__ . '/util.php';
iniciar();
verificarLogin();

$pdo = conectar();
$acao = campo('acao');

if ($acao === 'listar') {
    $sql = $pdo->query('SELECT pedidos.*, clientes.nome AS cliente, produtos.sku, itens_pedido.quantidade, usuarios.nome AS usuario
                        FROM pedidos
                        INNER JOIN clientes ON clientes.id = pedidos.cliente_id
                        INNER JOIN itens_pedido ON itens_pedido.pedido_id = pedidos.id
                        INNER JOIN produtos ON produtos.id = itens_pedido.produto_id
                        INNER JOIN usuarios ON usuarios.id = pedidos.usuario_id
                        ORDER BY pedidos.id DESC');
    responder(true, [], ['lista' => $sql->fetchAll()]);
}

// novo pedido
$erros = [];
obrigatorios(['cliente_id', 'produto_id', 'quantidade', 'forma_pagamento', 'data_pedido'], $erros);

$formas = ['Dinheiro', 'Pix', 'Cartão de crédito', 'Cartão de débito', 'Boleto'];
$quantidade = campo('quantidade');

if (!isset($erros['quantidade']) && (!ctype_digit($quantidade) || (int) $quantidade < 1)) {
    $erros['quantidade'] = 'Informe um número inteiro maior que zero.';
}
if (!isset($erros['forma_pagamento']) && !in_array(campo('forma_pagamento'), $formas, true)) {
    $erros['forma_pagamento'] = 'Forma de pagamento inválida.';
}
if (!isset($erros['data_pedido'])) {
    $data = dataValida(campo('data_pedido'));
    if ($data === null) {
        $erros['data_pedido'] = 'Data inválida.';
    } elseif ($data > new DateTime('today')) {
        $erros['data_pedido'] = 'A data do pedido não pode estar no futuro.';
    }
}

// busca o produto para pegar o preço e o estoque
$sql = $pdo->prepare('SELECT * FROM produtos WHERE id = ?');
$sql->execute([campo('produto_id')]);
$produto = $sql->fetch();
if (!$produto && !isset($erros['produto_id'])) {
    $erros['produto_id'] = 'Produto não encontrado.';
}

// Lógica extra: não deixa vender mais do que tem no estoque
if ($produto && !isset($erros['quantidade']) && (int) $quantidade > (int) $produto['estoque_atual']) {
    $erros['quantidade'] = 'Estoque insuficiente. Tem ' . $produto['estoque_atual'] . ' unidade(s).';
}

if ($erros) {
    responder(false, $erros, [], 422);
}

// Lógica extra: 10% de desconto para 10 unidades ou mais; Pix ganha 5% adicionais.
$qtd = (int) $quantidade;
$preco = (float) $produto['preco_venda'];
$subtotal = $qtd * $preco;
$desconto = 0;
if ($qtd >= 10) {
    $desconto = 10;
}
if (campo('forma_pagamento') === 'Pix') {
    $desconto = $desconto + 5;
}
$total = $subtotal - ($subtotal * $desconto / 100);

// grava o pedido (o usuário que registrou vem da sessão)
$sql = $pdo->prepare('INSERT INTO pedidos (cliente_id, usuario_id, data_pedido, forma_pagamento, total) VALUES (?, ?, ?, ?, ?)');
$sql->execute([campo('cliente_id'), $_SESSION['usuario']['id'], campo('data_pedido'), campo('forma_pagamento'), $total]);
$pedidoId = $pdo->lastInsertId();

// grava o item do pedido
$sql = $pdo->prepare('INSERT INTO itens_pedido (pedido_id, produto_id, quantidade, preco_unitario) VALUES (?, ?, ?, ?)');
$sql->execute([$pedidoId, $produto['id'], $qtd, $preco]);

// tira a quantidade vendida do estoque
$sql = $pdo->prepare('UPDATE produtos SET estoque_atual = estoque_atual - ? WHERE id = ?');
$sql->execute([$qtd, $produto['id']]);

responder(true, [], [
    'mensagem' => 'Pedido ' . $pedidoId . ' registrado com sucesso.',
    'subtotal' => number_format($subtotal, 2, ',', '.'),
    'desconto_percentual' => $desconto,
    'total' => number_format($total, 2, ',', '.'),
]);
