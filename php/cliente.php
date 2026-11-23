<?php
declare(strict_types=1);
require __DIR__ . '/util.php';
iniciar();
verificarLogin();

$pdo = conectar();
$acao = campo('acao');
$id = campo('id');

if ($acao === 'listar') {
    $sql = $pdo->query('SELECT * FROM clientes ORDER BY nome');
    responder(true, [], ['lista' => $sql->fetchAll()]);
}

if ($acao === 'excluir') {
    // cliente com pedido não pode ser excluído
    $sql = $pdo->prepare('SELECT COUNT(*) FROM pedidos WHERE cliente_id = ?');
    $sql->execute([$id]);
    if ($sql->fetchColumn() > 0) {
        responder(false, ['_geral' => 'Este cliente tem pedidos e não pode ser excluído.'], [], 422);
    }
    $sql = $pdo->prepare('DELETE FROM clientes WHERE id = ?');
    $sql->execute([$id]);
    responder(true, [], ['mensagem' => 'Cliente excluído.']);
}

// cadastrar ou alterar
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

// o CPF é salvo sempre formatado e não pode repetir
$c = soDigitos(campo('cpf'));
$cpf = substr($c, 0, 3) . '.' . substr($c, 3, 3) . '.' . substr($c, 6, 3) . '-' . substr($c, 9, 2);

if (!isset($erros['cpf'])) {
    $sql = $pdo->prepare('SELECT id FROM clientes WHERE cpf = ? AND id <> ?');
    $sql->execute([$cpf, $id === '' ? 0 : $id]);
    if ($sql->fetch()) {
        $erros['cpf'] = 'Já existe um cliente com este CPF.';
    }
}

if ($erros) {
    responder(false, $erros, [], 422);
}

if ($id === '') {
    $sql = $pdo->prepare('INSERT INTO clientes (nome, cpf, email, telefone, data_nascimento, cidade) VALUES (?, ?, ?, ?, ?, ?)');
    $sql->execute([campo('nome'), $cpf, campo('email'), campo('telefone'), campo('data_nascimento'), campo('cidade')]);
    $mensagem = 'Cliente cadastrado com sucesso.';
} else {
    $sql = $pdo->prepare('UPDATE clientes SET nome = ?, cpf = ?, email = ?, telefone = ?, data_nascimento = ?, cidade = ? WHERE id = ?');
    $sql->execute([campo('nome'), $cpf, campo('email'), campo('telefone'), campo('data_nascimento'), campo('cidade'), $id]);
    $mensagem = 'Cliente alterado com sucesso.';
}

responder(true, [], [
    'mensagem' => $mensagem,
    'idade' => $idade,
    'categoria_cliente' => $idade >= 60 ? 'Sênior' : 'Padrão',
]);
