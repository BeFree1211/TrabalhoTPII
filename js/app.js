// Funções usadas por todas as páginas.
// Cada página tem também o seu próprio arquivo JS (produtos.js, clientes.js...)
// com a parte de listar, editar e excluir.

const ROTULOS = {
    mensagem: 'Resultado',
    sku: 'SKU',
    margem_percentual: 'Margem (%)',
    alerta: 'Alerta',
    cnpj_formatado: 'CNPJ formatado',
    prazo_pagamento: 'Prazo de pagamento',
    idade: 'Idade',
    categoria_cliente: 'Perfil do cliente',
    subtotal: 'Subtotal (R$)',
    desconto_percentual: 'Desconto (%)',
    total: 'Total (R$)',
    efeito_no_estoque: 'Efeito no estoque',
    resumo: 'Resumo',
};

function limparErros(form) {
    form.querySelectorAll('.erro').forEach((el) => (el.textContent = ''));
    form.querySelectorAll('.invalido').forEach((el) => el.classList.remove('invalido'));
}

function mostrarErros(form, erros) {
    for (const [nome, msg] of Object.entries(erros)) {
        const campo = form.elements[nome];
        const alvo = form.querySelector(`[data-erro="${nome}"]`);
        if (alvo) alvo.textContent = msg;
        if (campo) campo.classList.add('invalido');
    }
}

function mostrarResultado(caixa, ok, dados, erros) {
    caixa.className = 'resultado ' + (ok ? 'sucesso' : 'falha');
    caixa.replaceChildren();
    const titulo = document.createElement('strong');
    // erro que não é de nenhum campo (ex.: senha errada) aparece como título
    if (!ok && erros._geral) {
        titulo.textContent = erros._geral;
        caixa.append(titulo);
        return;
    }
    titulo.textContent = ok ? dados.mensagem : 'Corrija os campos destacados.';
    caixa.append(titulo);
    const lista = document.createElement('ul');
    const itens = ok ? Object.entries(dados).filter(([k]) => k !== 'mensagem') : Object.entries(erros);
    for (const [chave, valor] of itens) {
        const li = document.createElement('li');
        li.textContent = `${ROTULOS[chave] ?? chave}: ${valor}`;
        lista.append(li);
    }
    caixa.append(lista);
}

// Envia os dados para o PHP e devolve o JSON.
// Se o PHP responder 401 é porque não tem ninguém logado.
async function enviar(url, dados) {
    const resposta = await fetch(url, { method: 'POST', body: dados });
    if (resposta.status === 401) {
        window.location.href = 'login.html';
    }
    return resposta.json();
}

// Pede a lista para o PHP (acao = listar)
async function buscarLista(url) {
    const dados = new FormData();
    dados.append('acao', 'listar');
    const json = await enviar(url, dados);
    if (!json.ok) {
        return [];
    }
    return json.dados.lista;
}

// Confere se tem usuário na sessão e mostra o nome dele no topo
async function verificarLogin() {
    const json = await enviar('php/sessao.php', new FormData());
    if (json.ok) {
        document.getElementById('nome-usuario').textContent = json.dados.usuario.nome;
    }
}

// Preenche um select com os dados que vêm do banco
async function preencherSelect(idSelect, url, campoTexto) {
    const select = document.getElementById(idSelect);
    const lista = await buscarLista(url);
    select.innerHTML = '<option value="">Selecione…</option>';
    lista.forEach((item) => {
        select.append(new Option(item[campoTexto], item.id));
    });
}

// Cria uma linha da tabela com os valores passados
function linhaTabela(valores) {
    const tr = document.createElement('tr');
    valores.forEach((valor) => {
        const td = document.createElement('td');
        td.textContent = valor;
        tr.append(td);
    });
    return tr;
}

// Coluna com os botões Editar e Excluir
function colunaBotoes(aoEditar, aoExcluir) {
    const td = document.createElement('td');
    td.className = 'acoes';
    const editar = document.createElement('button');
    editar.type = 'button';
    editar.className = 'pequeno';
    editar.textContent = 'Editar';
    editar.addEventListener('click', aoEditar);
    const excluir = document.createElement('button');
    excluir.type = 'button';
    excluir.className = 'pequeno';
    excluir.textContent = 'Excluir';
    excluir.addEventListener('click', aoExcluir);
    td.append(editar, excluir);
    return td;
}

// Exclui o registro (pergunta antes) e atualiza a tabela
async function excluirRegistro(url, id, aoTerminar) {
    if (!confirm('Tem certeza que deseja excluir?')) return;
    const dados = new FormData();
    dados.append('acao', 'excluir');
    dados.append('id', id);
    const json = await enviar(url, dados);
    mostrarResultado(document.getElementById('resultado'), json.ok, json.dados, json.erros);
    aoTerminar();
}

// Limpa o formulário e o campo id (assim o próximo envio é um cadastro novo)
function limparFormulario() {
    const form = document.querySelector('form[data-endpoint]');
    form.reset();
    if (form.elements.id) form.elements.id.value = '';
    limparErros(form);
}

function formatarData(data) {
    return data.split('-').reverse().join('/');
}

function formatarDinheiro(valor) {
    return 'R$ ' + Number(valor).toFixed(2).replace('.', ',');
}

// Envio dos formulários (igual ao trabalho 1).
// Se a página tiver a função depoisDeSalvar, ela é chamada quando dá certo.
document.querySelectorAll('form[data-endpoint]').forEach((form) => {
    const caixa = document.getElementById('resultado');
    form.addEventListener('submit', async (evento) => {
        evento.preventDefault();
        limparErros(form);
        try {
            const json = await enviar(form.dataset.endpoint, new FormData(form));
            if (!json.ok) mostrarErros(form, json.erros);
            mostrarResultado(caixa, json.ok, json.dados, json.erros);
            if (json.ok && typeof depoisDeSalvar === 'function') {
                depoisDeSalvar();
            }
        } catch (e) {
            caixa.className = 'resultado falha';
            caixa.textContent = 'Não foi possível falar com o servidor PHP. Ele está rodando?';
        }
    });
});

document.getElementById('limpar')?.addEventListener('click', limparFormulario);

// Toda página, menos a de login, confere se o usuário está logado
if (document.body.id !== 'pagina-login') {
    verificarLogin();
}
