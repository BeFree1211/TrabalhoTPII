# Giro – Tecnologias para Internet II (Momento I)

Sistema administrativo de controle de estoque para uma pequena loja.

## Como rodar

É preciso ter o PHP instalado. Na pasta do projeto:

```
php -S localhost:8000
```

Depois abra http://localhost:8000/index.html. (Abrir o HTML direto no navegador não funciona, pois o JS precisa chamar o PHP.)

## Estrutura

- `index.html` – página inicial com menu
- `produtos.html`, `fornecedores.html`, `clientes.html`, `pedidos.html`, `movimentacoes.html` – os 5 formulários
- `js/app.js` – envia cada formulário com `fetch` (POST) e mostra a resposta
- `php/*.php` – recebem a requisição, validam os campos e aplicam uma regra extra; respondem JSON (sem HTML)
- `css/style.css` – estilo (paleta cinza, rosa e branco)
- `img/logo.svg` – logotipo, usado no cabeçalho e como ícone da aba

## Entidades planejadas (7 tabelas – banco ainda não criado)

| Tabela | Campos principais |
|---|---|
| categorias | id, nome |
| fornecedores | id, razao_social, cnpj, email, telefone, cidade, uf |
| produtos | id, categoria_id, fornecedor_id, nome, sku, preco_custo, preco_venda, estoque_minimo |
| clientes | id, nome, cpf, email, telefone, data_nascimento, cidade |
| pedidos | id, cliente_id, data_pedido, forma_pagamento, total |
| itens_pedido | id, pedido_id, produto_id, quantidade, preco_unitario |
| movimentacoes | id, produto_id, tipo, quantidade, data, responsavel, observacao |

## Regras extras por formulário

| Formulário | Regra |
|---|---|
| Produto | venda não pode ser menor que custo; calcula a margem e alerta se < 10% |
| Fornecedor | valida dígitos do CNPJ; formata o CNPJ; define prazo de pagamento por UF |
| Cliente | valida dígitos do CPF; exige maioridade; classifica cliente sênior (60+) |
| Pedido | 10% de desconto para 10+ unidades, +5% no Pix; calcula o total |
| Movimentação | saída/ajuste exigem justificativa; calcula o efeito no estoque |
