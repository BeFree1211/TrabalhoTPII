<?php
declare(strict_types=1);
require __DIR__ . '/util.php';
iniciar();

$erros = [];
obrigatorios(['email', 'senha'], $erros);
if ($erros) {
    responder(false, $erros, [], 422);
}

// procura o usuário pelo e-mail na tabela usuarios
$pdo = conectar();
$sql = $pdo->prepare('SELECT * FROM usuarios WHERE email = ?');
$sql->execute([campo('email')]);
$usuario = $sql->fetch();

// password_verify compara a senha digitada com a senha criptografada do banco
if (!$usuario || !password_verify($_POST['senha'], $usuario['senha'])) {
    responder(false, ['_geral' => 'E-mail ou senha incorretos.'], [], 422);
}

// guarda o usuário logado na sessão
$_SESSION['usuario'] = [
    'id' => $usuario['id'],
    'nome' => $usuario['nome'],
    'email' => $usuario['email'],
];

responder(true, [], ['mensagem' => 'Login realizado.']);
