// Tela de movimentações: lista o histórico do estoque

async function listarMovimentacoes() {
    const lista = await buscarLista('php/movimentacao.php');
    const corpo = document.getElementById('corpo-tabela');
    corpo.innerHTML = '';
    lista.forEach((m) => {
        corpo.append(linhaTabela([formatarData(m.data_movimentacao), m.sku, m.tipo, m.quantidade, m.responsavel, m.observacao]));
    });
}

function depoisDeSalvar() {
    limparFormulario();
    listarMovimentacoes();
}

preencherSelect('produto_id', 'php/produto.php', 'nome');
listarMovimentacoes();
