// Tela de pedidos: lista os pedidos já registrados

async function listarPedidos() {
    const lista = await buscarLista('php/pedido.php');
    const corpo = document.getElementById('corpo-tabela');
    corpo.innerHTML = '';
    lista.forEach((p) => {
        corpo.append(linhaTabela([p.id, formatarData(p.data_pedido), p.cliente, p.sku, p.quantidade, p.forma_pagamento, formatarDinheiro(p.total), p.usuario]));
    });
}

function depoisDeSalvar() {
    limparFormulario();
    listarPedidos();
    preencherSelect('produto_id', 'php/produto.php', 'nome');
}

preencherSelect('cliente_id', 'php/cliente.php', 'nome');
preencherSelect('produto_id', 'php/produto.php', 'nome');
listarPedidos();
