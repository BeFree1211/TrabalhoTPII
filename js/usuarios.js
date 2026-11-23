// Tela de usuários: lista, edição e exclusão

async function listarUsuarios() {
    const lista = await buscarLista('php/usuario.php');
    const corpo = document.getElementById('corpo-tabela');
    corpo.innerHTML = '';
    lista.forEach((u) => {
        const tr = linhaTabela([u.nome, u.email]);
        tr.append(colunaBotoes(() => editarUsuario(u), () => excluirRegistro('php/usuario.php', u.id, listarUsuarios)));
        corpo.append(tr);
    });
}

// a senha não vem do banco, então fica em branco (se não digitar nada, mantém a atual)
function editarUsuario(u) {
    document.getElementById('id').value = u.id;
    document.getElementById('nome').value = u.nome;
    document.getElementById('email').value = u.email;
    document.getElementById('senha').value = '';
    window.scrollTo(0, 0);
}

function depoisDeSalvar() {
    limparFormulario();
    listarUsuarios();
    verificarLogin(); // caso tenha mudado o próprio nome
}

listarUsuarios();
