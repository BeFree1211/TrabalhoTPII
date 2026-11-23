<?php
declare(strict_types=1);
require __DIR__ . '/util.php';
iniciar();
verificarLogin();

$pdo = conectar();
$acao = campo('acao');
$id = campo('id');

if ($acao === 'listar') {
    $sql = $pdo->query('SELECT * FROM fornecedores ORDER BY razao_social');
    $lista = $sql->fetchAll();
    // Lógica extra (mesma do trabalho 1): prazo de pagamento conforme a UF
    foreach ($lista as $i => $f) {
        $lista[$i]['prazo'] = in_array($f['uf'], ['MG', 'SP', 'RJ', 'ES']) ? '30 dias' : '45 dias';
    }
    responder(true, [], ['lista' => $lista]);
}

if ($acao === 'excluir') {
    // fornecedor que tem produto cadastrado não pode ser excluído
    $sql = $pdo->prepare('SELECT COUNT(*) FROM produtos WHERE fornecedor_id = ?');
    $sql->execute([$id]);
    if ($sql->fetchColumn() > 0) {
        responder(false, ['_geral' => 'Este fornecedor tem produtos cadastrados e não pode ser excluído.'], [], 422);
    }
    $sql = $pdo->prepare('DELETE FROM fornecedores WHERE id = ?');
    $sql->execute([$id]);
    responder(true, [], ['mensagem' => 'Fornecedor excluído.']);
}

// cadastrar ou alterar
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

// o CNPJ é salvo sempre formatado
$c = soDigitos(campo('cnpj'));
$cnpj = sprintf('%s.%s.%s/%s-%s', substr($c, 0, 2), substr($c, 2, 3), substr($c, 5, 3), substr($c, 8, 4), substr($c, 12, 2));

if (!isset($erros['cnpj'])) {
    $sql = $pdo->prepare('SELECT id FROM fornecedores WHERE cnpj = ? AND id <> ?');
    $sql->execute([$cnpj, $id === '' ? 0 : $id]);
    if ($sql->fetch()) {
        $erros['cnpj'] = 'Já existe um fornecedor com este CNPJ.';
    }
}

if ($erros) {
    responder(false, $erros, [], 422);
}

if ($id === '') {
    $sql = $pdo->prepare('INSERT INTO fornecedores (razao_social, cnpj, email, telefone, cidade, uf) VALUES (?, ?, ?, ?, ?, ?)');
    $sql->execute([campo('razao_social'), $cnpj, campo('email'), campo('telefone'), campo('cidade'), $uf]);
    $mensagem = 'Fornecedor cadastrado com sucesso.';
} else {
    $sql = $pdo->prepare('UPDATE fornecedores SET razao_social = ?, cnpj = ?, email = ?, telefone = ?, cidade = ?, uf = ? WHERE id = ?');
    $sql->execute([campo('razao_social'), $cnpj, campo('email'), campo('telefone'), campo('cidade'), $uf, $id]);
    $mensagem = 'Fornecedor alterado com sucesso.';
}

responder(true, [], [
    'mensagem' => $mensagem,
    'cnpj_formatado' => $cnpj,
    'prazo_pagamento' => in_array($uf, ['MG', 'SP', 'RJ', 'ES'], true) ? '30 dias' : '45 dias',
]);
