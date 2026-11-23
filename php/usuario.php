<?php
declare(strict_types=1);
require __DIR__ . '/util.php';
iniciar();
verificarLogin();

$pdo = conectar();
$acao = campo('acao');
$id = campo('id');

if ($acao === 'listar') {
    // a senha não vai para a tela
    $sql = $pdo->query('SELECT id, nome, email FROM usuarios ORDER BY nome');
    responder(true, [], ['lista' => $sql->fetchAll()]);
}

if ($acao === 'excluir') {
    // Lógica extra: não deixa excluir o usuário que está logado
    if ($id == $_SESSION['usuario']['id']) {
        responder(false, ['_geral' => 'Você não pode excluir o seu próprio usuário.'], [], 422);
    }
    // não exclui usuário que já registrou pedido ou movimentação
    $sql = $pdo->prepare('SELECT (SELECT COUNT(*) FROM pedidos WHERE usuario_id = ?) + (SELECT COUNT(*) FROM movimentacoes WHERE usuario_id = ?)');
    $sql->execute([$id, $id]);
    if ($sql->fetchColumn() > 0) {
        responder(false, ['_geral' => 'Este usuário já tem registros no sistema e não pode ser excluído.'], [], 422);
    }
    $sql = $pdo->prepare('DELETE FROM usuarios WHERE id = ?');
    $sql->execute([$id]);
    responder(true, [], ['mensagem' => 'Usuário excluído.']);
}

// cadastrar ou alterar
$erros = [];
obrigatorios(['nome', 'email'], $erros);
$senha = campo('senha');

if (!isset($erros['nome']) && mb_strlen(campo('nome')) < 3) {
    $erros['nome'] = 'O nome precisa ter pelo menos 3 caracteres.';
}
if (!isset($erros['email']) && !validarEmail(campo('email'))) {
    $erros['email'] = 'E-mail inválido.';
}
// no cadastro a senha é obrigatória, na alteração pode ficar em branco
if ($id === '' && $senha === '') {
    $erros['senha'] = 'Campo obrigatório.';
}
// Lógica extra: senha com pelo menos 6 caracteres e pelo menos um número
if ($senha !== '' && (strlen($senha) < 6 || !preg_match('/[0-9]/', $senha))) {
    $erros['senha'] = 'A senha precisa ter pelo menos 6 caracteres e um número.';
}

// e-mail não pode repetir (o próprio usuário pode manter o dele)
if (!isset($erros['email'])) {
    $sql = $pdo->prepare('SELECT id FROM usuarios WHERE email = ? AND id <> ?');
    $sql->execute([campo('email'), $id === '' ? 0 : $id]);
    if ($sql->fetch()) {
        $erros['email'] = 'Já existe um usuário com este e-mail.';
    }
}

if ($erros) {
    responder(false, $erros, [], 422);
}

if ($id === '') {
    $sql = $pdo->prepare('INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)');
    $sql->execute([campo('nome'), campo('email'), password_hash($senha, PASSWORD_DEFAULT)]);
    responder(true, [], ['mensagem' => 'Usuário cadastrado com sucesso.']);
}

if ($senha === '') {
    $sql = $pdo->prepare('UPDATE usuarios SET nome = ?, email = ? WHERE id = ?');
    $sql->execute([campo('nome'), campo('email'), $id]);
} else {
    $sql = $pdo->prepare('UPDATE usuarios SET nome = ?, email = ?, senha = ? WHERE id = ?');
    $sql->execute([campo('nome'), campo('email'), password_hash($senha, PASSWORD_DEFAULT), $id]);
}

// se alterou o próprio usuário, atualiza o nome na sessão também
if ($id == $_SESSION['usuario']['id']) {
    $_SESSION['usuario']['nome'] = campo('nome');
    $_SESSION['usuario']['email'] = campo('email');
}

responder(true, [], ['mensagem' => 'Usuário alterado com sucesso.']);
