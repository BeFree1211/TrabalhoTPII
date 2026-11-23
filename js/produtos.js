// Tela de produtos: lista, edição e exclusão

async function listarProdutos() {
    const lista = await buscarLista('php/produto.php');
    const corpo = document.getElementById('corpo-tabela');
    corpo.innerHTML = '';
    lista.forEach((p) => {
        // mostra "Repor" quando o estoque está abaixo do mínimo
        let situacao = 'OK';
        if (Number(p.estoque_atual) < Number(p.estoque_minimo)) {
            situacao = 'Repor';
        }
        const tr = linhaTabela([p.sku, p.nome, p.categoria, p.fornecedor, formatarDinheiro(p.preco_venda), p.estoque_atual, p.estoque_minimo, situacao]);
        tr.append(colunaBotoes(() => editarProduto(p), () => excluirRegistro('php/produto.php', p.id, listarProdutos)));
        corpo.append(tr);
    });
}

// joga os dados do produto no formulário para alterar
function editarProduto(p) {
    document.getElementById('id').value = p.id;
    document.getElementById('nome').value = p.nome;
    document.getElementById('sku').value = p.sku;
    document.getElementById('categoria_id').value = p.categoria_id;
    document.getElementById('fornecedor_id').value = p.fornecedor_id;
    document.getElementById('preco_custo').value = p.preco_custo;
    document.getElementById('preco_venda').value = p.preco_venda;
    document.getElementById('estoque_minimo').value = p.estoque_minimo;
    window.scrollTo(0, 0);
}

function depoisDeSalvar() {
    limparFormulario();
    listarProdutos();
}

preencherSelect('categoria_id', 'php/categoria.php', 'nome');
preencherSelect('fornecedor_id', 'php/fornecedor.php', 'razao_social');
listarProdutos();
