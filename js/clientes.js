// Tela de clientes: lista, edição e exclusão

async function listarClientes() {
    const lista = await buscarLista('php/cliente.php');
    const corpo = document.getElementById('corpo-tabela');
    corpo.innerHTML = '';
    lista.forEach((c) => {
        const tr = linhaTabela([c.nome, c.cpf, c.telefone, formatarData(c.data_nascimento), c.cidade]);
        tr.append(colunaBotoes(() => editarCliente(c), () => excluirRegistro('php/cliente.php', c.id, listarClientes)));
        corpo.append(tr);
    });
}

function editarCliente(c) {
    document.getElementById('id').value = c.id;
    document.getElementById('nome').value = c.nome;
    document.getElementById('cpf').value = c.cpf;
    document.getElementById('email').value = c.email;
    document.getElementById('telefone').value = c.telefone;
    document.getElementById('data_nascimento').value = c.data_nascimento;
    document.getElementById('cidade').value = c.cidade;
    window.scrollTo(0, 0);
}

function depoisDeSalvar() {
    limparFormulario();
    listarClientes();
}

listarClientes();
