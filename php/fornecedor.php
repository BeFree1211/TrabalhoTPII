<?php
declare(strict_types=1);
require __DIR__ . '/util.php';
iniciar();

$erros = [];
obrigatorios(['razao_social', 'cnpj', 'email', 'telefone', 'cidade', 'uf'], $erros);

$ufs = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'];
$uf = strtoupper(campo('uf'));

if (!isset($erros['cnpj']) && !validarCnpj(campo('cnpj'))) {
    $erros['cnpj'] = 'CNPJ inválido.';
}
if (!isset($erros['email']) && !validarEmail(campo('email'))) {
    $erros['email'] = 'E-mail inválido.';
}
if (!isset($erros['telefone']) && !validarTelefone(campo('telefone'))) {
    $erros['telefone'] = 'Telefone deve ter 10 ou 11 dígitos (com DDD).';
}
if (!isset($erros['uf']) && !in_array($uf, $ufs, true)) {
    $erros['uf'] = 'UF inválida.';
}

if ($erros) {
    responder(false, $erros, [], 422);
}

// Lógica extra: devolve o CNPJ formatado e o prazo de pagamento padrão.
$c = soDigitos(campo('cnpj'));
$formatado = sprintf('%s.%s.%s/%s-%s', substr($c, 0, 2), substr($c, 2, 3), substr($c, 5, 3), substr($c, 8, 4), substr($c, 12, 2));
responder(true, [], [
    'mensagem' => 'Fornecedor validado com sucesso.',
    'cnpj_formatado' => $formatado,
    'prazo_pagamento' => in_array($uf, ['MG', 'SP', 'RJ', 'ES'], true) ? '30 dias' : '45 dias',
]);
