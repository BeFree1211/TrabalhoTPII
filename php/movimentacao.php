<?php
declare(strict_types=1);
require __DIR__ . '/util.php';
iniciar();

$erros = [];
obrigatorios(['sku', 'tipo', 'quantidade', 'data_movimentacao', 'responsavel'], $erros);

$tipos = ['entrada', 'saida', 'ajuste'];
$tipo = campo('tipo');
$quantidade = campo('quantidade');
$obs = campo('observacao');

if (!isset($erros['sku']) && !preg_match('/^[A-Z]{3}-\d{4}$/', strtoupper(campo('sku')))) {
    $erros['sku'] = 'Formato esperado: três letras, hífen e quatro números (ex.: ELE-0001).';
}
if (!isset($erros['tipo']) && !in_array($tipo, $tipos, true)) {
    $erros['tipo'] = 'Tipo inválido.';
}
if (!isset($erros['quantidade']) && (!ctype_digit($quantidade) || (int) $quantidade < 1)) {
    $erros['quantidade'] = 'Informe um número inteiro maior que zero.';
}
if (!isset($erros['data_movimentacao'])) {
    $data = dataValida(campo('data_movimentacao'));
    if ($data === null) {
        $erros['data_movimentacao'] = 'Data inválida.';
    } elseif ($data > new DateTime('today')) {
        $erros['data_movimentacao'] = 'A data não pode estar no futuro.';
    }
}

// Lógica extra: saída e ajuste exigem justificativa na observação.
if (in_array($tipo, ['saida', 'ajuste'], true) && mb_strlen($obs) < 5) {
    $erros['observacao'] = 'Para saída ou ajuste, descreva o motivo (mínimo 5 caracteres).';
}

if ($erros) {
    responder(false, $erros, [], 422);
}

$qtd = (int) $quantidade;
responder(true, [], [
    'mensagem' => 'Movimentação validada com sucesso.',
    'efeito_no_estoque' => $tipo === 'saida' ? -$qtd : $qtd,
    'resumo' => ucfirst($tipo) . ' de ' . $qtd . ' unidade(s) do produto ' . strtoupper(campo('sku')) . '.',
]);
