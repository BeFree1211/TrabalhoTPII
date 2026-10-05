<?php
declare(strict_types=1);
require __DIR__ . '/util.php';
iniciar();

$erros = [];
obrigatorios(['nome', 'cpf', 'email', 'telefone', 'data_nascimento', 'cidade'], $erros);

if (!isset($erros['nome']) && mb_strlen(campo('nome')) < 3) {
    $erros['nome'] = 'O nome precisa ter pelo menos 3 caracteres.';
}
if (!isset($erros['cpf']) && !validarCpf(campo('cpf'))) {
    $erros['cpf'] = 'CPF inválido.';
}
if (!isset($erros['email']) && !validarEmail(campo('email'))) {
    $erros['email'] = 'E-mail inválido.';
}
if (!isset($erros['telefone']) && !validarTelefone(campo('telefone'))) {
    $erros['telefone'] = 'Telefone deve ter 10 ou 11 dígitos (com DDD).';
}

$idade = null;
if (!isset($erros['data_nascimento'])) {
    $nasc = dataValida(campo('data_nascimento'));
    if ($nasc === null) {
        $erros['data_nascimento'] = 'Data inválida.';
    } else {
        $idade = $nasc->diff(new DateTime('today'))->y;
        // Lógica extra: só cadastra clientes maiores de idade.
        if ($nasc > new DateTime('today')) {
            $erros['data_nascimento'] = 'A data não pode estar no futuro.';
        } elseif ($idade < 18) {
            $erros['data_nascimento'] = 'O cliente precisa ser maior de 18 anos.';
        }
    }
}

if ($erros) {
    responder(false, $erros, [], 422);
}

responder(true, [], [
    'mensagem' => 'Cliente validado com sucesso.',
    'idade' => $idade,
    'categoria_cliente' => $idade >= 60 ? 'Sênior (desconto especial)' : 'Padrão',
]);
