<?php
declare(strict_types=1);
require __DIR__ . '/util.php';
iniciar();
verificarLogin();

$pdo = conectar();
$acao = campo('acao');

if ($acao === 'listar') {
    $sql = $pdo->query('SELECT movimentacoes.*, produtos.sku, usuarios.nome AS responsavel
                        FROM movimentacoes
                        INNER JOIN produtos ON produtos.id = movimentacoes.produto_id
                        INNER JOIN usuarios ON usuarios.id = movimentacoes.usuario_id
                        ORDER BY movimentacoes.id DESC');
    responder(true, [], ['lista' => $sql->fetchAll()]);
}

// nova movimentação
$erros = [];
obrigatorios(['produto_id', 'tipo', 'quantidade', 'data_movimentacao'], $erros);

$tipos = ['entrada', 'saida', 'ajuste'];
$tipo = campo('tipo');
$quantidade = campo('quantidade');
$obs = campo('observacao');

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

$sql = $pdo->prepare('SELECT * FROM produtos WHERE id = ?');
$sql->execute([campo('produto_id')]);
$produto = $sql->fetch();
if (!$produto && !isset($erros['produto_id'])) {
    $erros['produto_id'] = 'Produto não encontrado.';
}

// saída diminui o estoque, entrada e ajuste aumentam
$qtd = (int) $quantidade;
$efeito = $tipo === 'saida' ? -$qtd : $qtd;

// a saída não pode deixar o estoque negativo
if ($produto && $tipo === 'saida' && $qtd > (int) $produto['estoque_atual']) {
    $erros['quantidade'] = 'Estoque insuficiente. Tem ' . $produto['estoque_atual'] . ' unidade(s).';
}

if ($erros) {
    responder(false, $erros, [], 422);
}

// o responsável é o usuário que está logado
$sql = $pdo->prepare('INSERT INTO movimentacoes (produto_id, usuario_id, tipo, quantidade, data_movimentacao, observacao) VALUES (?, ?, ?, ?, ?, ?)');
$sql->execute([$produto['id'], $_SESSION['usuario']['id'], $tipo, $qtd, campo('data_movimentacao'), $obs]);

// atualiza o estoque do produto
$sql = $pdo->prepare('UPDATE produtos SET estoque_atual = estoque_atual + ? WHERE id = ?');
$sql->execute([$efeito, $produto['id']]);

// nome do tipo com acento para mostrar na tela
$nomesTipo = ['entrada' => 'Entrada', 'saida' => 'Saída', 'ajuste' => 'Ajuste'];

responder(true, [], [
    'mensagem' => 'Movimentação registrada com sucesso.',
    'efeito_no_estoque' => $efeito,
    'resumo' => $nomesTipo[$tipo] . ' de ' . $qtd . ' unidade(s) do produto ' . $produto['sku'] . '.',
]);
