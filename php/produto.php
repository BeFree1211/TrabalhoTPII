<?php
declare(strict_types=1);
require __DIR__ . '/util.php';
iniciar();

$erros = [];
obrigatorios(['nome', 'sku', 'categoria', 'fornecedor', 'preco_custo', 'preco_venda', 'estoque_minimo'], $erros);

$categorias = ['Eletrônicos', 'Alimentos', 'Vestuário', 'Papelaria', 'Limpeza'];
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
if (!isset($erros['categoria']) && !in_array(campo('categoria'), $categorias, true)) {
    $erros['categoria'] = 'Categoria inválida.';
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

if ($erros) {
    responder(false, $erros, [], 422);
}

$margem = ($venda - $custo) / $custo * 100;
responder(true, [], [
    'mensagem' => 'Produto validado com sucesso.',
    'sku' => $sku,
    'margem_percentual' => round($margem, 2),
    'alerta' => $margem < 10 ? 'Margem abaixo de 10%: revise o preço.' : 'Margem saudável.',
]);
