// Página inicial: mostra os produtos com estoque abaixo do mínimo

async function listarRepor() {
    const lista = await buscarLista('php/produto.php');
    const ul = document.getElementById('lista-repor');
    ul.innerHTML = '';
    lista.forEach((p) => {
        if (Number(p.estoque_atual) < Number(p.estoque_minimo)) {
            const li = document.createElement('li');
            li.textContent = p.sku + ' - ' + p.nome + ' (estoque ' + p.estoque_atual + ', mínimo ' + p.estoque_minimo + ')';
            ul.append(li);
        }
    });
    if (ul.children.length === 0) {
        ul.innerHTML = '<li>Nenhum produto abaixo do estoque mínimo.</li>';
    }
}

listarRepor();
