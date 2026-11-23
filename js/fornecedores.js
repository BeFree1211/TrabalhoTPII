// Tela de fornecedores: lista, edição e exclusão

async function listarFornecedores() {
    const lista = await buscarLista('php/fornecedor.php');
    const corpo = document.getElementById('corpo-tabela');
    corpo.innerHTML = '';
    lista.forEach((f) => {
        const tr = linhaTabela([f.razao_social, f.cnpj, f.telefone, f.cidade + '/' + f.uf, f.prazo]);
        tr.append(colunaBotoes(() => editarFornecedor(f), () => excluirRegistro('php/fornecedor.php', f.id, listarFornecedores)));
        corpo.append(tr);
    });
}

function editarFornecedor(f) {
    document.getElementById('id').value = f.id;
    document.getElementById('razao_social').value = f.razao_social;
    document.getElementById('cnpj').value = f.cnpj;
    document.getElementById('email').value = f.email;
    document.getElementById('telefone').value = f.telefone;
    document.getElementById('cidade').value = f.cidade;
    document.getElementById('uf').value = f.uf;
    window.scrollTo(0, 0);
}

function depoisDeSalvar() {
    limparFormulario();
    listarFornecedores();
}

listarFornecedores();
