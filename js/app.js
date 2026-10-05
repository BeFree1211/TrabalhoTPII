// Envio genérico dos formulários: cada <form> aponta para o seu endpoint PHP
// em data-endpoint. O PHP responde JSON e esta função desenha o resultado.

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

document.querySelectorAll('form[data-endpoint]').forEach((form) => {
    const caixa = document.getElementById('resultado');
    form.addEventListener('submit', async (evento) => {
        evento.preventDefault();
        limparErros(form);
        try {
            const resposta = await fetch(form.dataset.endpoint, {
                method: 'POST',
                body: new FormData(form),
            });
            const json = await resposta.json();
            if (!json.ok) mostrarErros(form, json.erros);
            mostrarResultado(caixa, json.ok, json.dados, json.erros);
        } catch (e) {
            caixa.className = 'resultado falha';
            caixa.textContent = 'Não foi possível falar com o servidor PHP. Ele está rodando?';
        }
    });
});
