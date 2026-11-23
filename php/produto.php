<?php
declare(strict_types=1);
require __DIR__ . '/util.php';
iniciar();
verificarLogin();

$pdo = conectar();
$acao = campo('acao');
$id = campo('id');

if ($acao === 'listar') {
    // JOIN para trazer o nome da categoria e do fornecedor
    $sql = $pdo->query('SELECT produtos.*, categorias.nome AS categoria, fornecedores.razao_social AS fornecedor
                        FROM produtos
                        INNER JOIN categorias ON categorias.id = produtos.categoria_id
                        INNER JOIN fornecedores ON fornecedores.id = produtos.fornecedor_id
                        ORDER BY produtos.sku');
    responder(true, [], ['lista' => $sql->fetchAll()]);
}

if ($acao === 'excluir') {
    // produto que já foi vendido ou movimentado não pode ser excluído
    $sql = $pdo->prepare('SELECT (SELECT COUNT(*) FROM itens_pedido WHERE produto_id = ?) + (SELECT COUNT(*) FROM movimentacoes WHERE produto_id = ?)');
    $sql->execute([$id, $id]);
    if ($sql->fetchColumn() > 0) {
        responder(false, ['_geral' => 'Este produto já tem pedidos ou movimentações e não pode ser excluído.'], [], 422);
    }
    $sql = $pdo->prepare('DELETE FROM produtos WHERE id = ?');
    $sql->execute([$id]);
    responder(true, [], ['mensagem' => 'Produto excluído.']);
}

// cadastrar ou alterar
$erros = [];
obrigatorios(['nome', 'sku', 'categoria_id', 'fornecedor_id', 'preco_custo', 'preco_venda', 'estoque_minimo'], $erros);

$sku = strtoupper(campo('sku'));
$custo = numero(campo('preco_custo'));
$venda = numero(campo('preco_venda'));
$minimo = campo('estoque_minimo');

if (!isset($erros['nome']) && mb_strlen(campo('nome')) < 3) {
    $erros['nome'] = 'O nome precisa ter pelo menos 3 caracteres.';
}
if (!isset($erros['sku']) && !preg_match('/^[A-Z]{3}-\d{4}$/', $sku)) {
    $erros['sku'] = 'Formato esperado: três letras, hífen e quatro números (ex.: ELE-0001).';
}
if (!isset($erros['preco_custo']) && ($custo === null || $custo <= 0)) {
    $erros['preco_custo'] = 'Informe um valor maior que zero.';
}
if (!isset($erros['preco_venda']) && ($venda === null || $venda <= 0)) {
    $erros['preco_venda'] = 'Informe um valor maior que zero.';
}
if (!isset($erros['estoque_minimo']) && !ctype_digit($minimo)) {
    $erros['estoque_minimo'] = 'Informe um número inteiro maior ou igual a zero.';
}

// Lógica extra: o preço de venda não pode ser menor que o custo.
if (!isset($erros['preco_custo']) && !isset($erros['preco_venda']) && $venda < $custo) {
    $erros['preco_venda'] = 'O preço de venda não pode ser menor que o custo.';
}

// SKU não pode repetir
if (!isset($erros['sku'])) {
    $sql = $pdo->prepare('SELECT id FROM produtos WHERE sku = ? AND id <> ?');
    $sql->execute([$sku, $id === '' ? 0 : $id]);
    if ($sql->fetch()) {
        $erros['sku'] = 'Já existe um produto com este SKU.';
    }
}

if ($erros) {
    responder(false, $erros, [], 422);
}

// o estoque atual não entra aqui, ele muda pelas movimentações e pelos pedidos
if ($id === '') {
    $sql = $pdo->prepare('INSERT INTO produtos (categoria_id, fornecedor_id, nome, sku, preco_custo, preco_venda, estoque_minimo) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $sql->execute([campo('categoria_id'), campo('fornecedor_id'), campo('nome'), $sku, $custo, $venda, $minimo]);
    $mensagem = 'Produto cadastrado com sucesso.';
} else {
    $sql = $pdo->prepare('UPDATE produtos SET categoria_id = ?, fornecedor_id = ?, nome = ?, sku = ?, preco_custo = ?, preco_venda = ?, estoque_minimo = ? WHERE id = ?');
    $sql->execute([campo('categoria_id'), campo('fornecedor_id'), campo('nome'), $sku, $custo, $venda, $minimo, $id]);
    $mensagem = 'Produto alterado com sucesso.';
}

$margem = ($venda - $custo) / $custo * 100;
responder(true, [], [
    'mensagem' => $mensagem,
    'sku' => $sku,
    'margem_percentual' => round($margem, 2),
    'alerta' => $margem < 10 ? 'Margem abaixo de 10%: revise o preço.' : 'Margem saudável.',
]);
