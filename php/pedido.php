<?php
declare(strict_types=1);
require __DIR__ . '/util.php';
iniciar();

$erros = [];
obrigatorios(['cliente_cpf', 'sku', 'quantidade', 'preco_unitario', 'forma_pagamento', 'data_pedido'], $erros);

$formas = ['Dinheiro', 'Pix', 'Cartão de crédito', 'Cartão de débito', 'Boleto'];
$quantidade = campo('quantidade');
$preco = numero(campo('preco_unitario'));

if (!isset($erros['cliente_cpf']) && !validarCpf(campo('cliente_cpf'))) {
    $erros['cliente_cpf'] = 'CPF inválido.';
}
if (!isset($erros['sku']) && !preg_match('/^[A-Z]{3}-\d{4}$/', strtoupper(campo('sku')))) {
    $erros['sku'] = 'Formato esperado: três letras, hífen e quatro números (ex.: ELE-0001).';
}
if (!isset($erros['quantidade']) && (!ctype_digit($quantidade) || (int) $quantidade < 1)) {
    $erros['quantidade'] = 'Informe um número inteiro maior que zero.';
}
if (!isset($erros['preco_unitario']) && ($preco === null || $preco <= 0)) {
    $erros['preco_unitario'] = 'Informe um valor maior que zero.';
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

if ($erros) {
    responder(false, $erros, [], 422);
}

// Lógica extra: 10% de desconto para 10 unidades ou mais; Pix ganha 5% adicionais.
$qtd = (int) $quantidade;
$subtotal = $qtd * $preco;
$desconto = $qtd >= 10 ? 0.10 : 0.0;
if (campo('forma_pagamento') === 'Pix') {
    $desconto += 0.05;
}
$total = $subtotal * (1 - $desconto);

responder(true, [], [
    'mensagem' => 'Pedido validado com sucesso.',
    'subtotal' => number_format($subtotal, 2, ',', '.'),
    'desconto_percentual' => $desconto * 100,
    'total' => number_format($total, 2, ',', '.'),
]);
