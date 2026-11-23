# Giro | Tecnologias para Internet II (Momento II)

Sistema administrativo de controle de estoque para uma pequena loja. No Momento II o sistema passou a usar banco de dados (MySQL) e ganhou login com sessão.

## Como instalar

1. Ligar o Apache e o MySQL no XAMPP.
2. Executar o arquivo `banco.sql` no phpMyAdmin. Ele cria o banco `giro_estoque`, as tabelas e alguns dados de exemplo.
3. Copiar a pasta do projeto para qualquer lugar dentro do `htdocs`.
4. Abrir `http://localhost/nome-da-pasta/login.html`.

Para entrar no sistema use o e-mail `admin@giro.com` e a senha `admin123`.

Os links e as chamadas do JavaScript usam caminho relativo, então o sistema funciona em qualquer pasta.

## Estrutura

- `login.html` e `js/login.js` tela de login
- `index.html` página inicial com menu e lista de produtos para repor
- `produtos.html`, `fornecedores.html`, `clientes.html`, `pedidos.html`, `movimentacoes.html`, `usuarios.html` formulário e tabela com os dados do banco
- `js/app.js` funções usadas por todas as páginas (envio com `fetch`, mensagens, tabela, verificar login)
- `js/<pagina>.js` listar, editar e excluir de cada tela
- `php/conexao.php` conexão com o banco (PDO)
- `php/util.php` funções comuns e validações
- `php/login.php`, `php/sessao.php`, `php/logout.php` login e sessão
- `php/*.php` um arquivo por cadastro, recebendo `acao` = listar, excluir ou salvar
- `banco.sql` comandos para criar o banco

## Login e sessão

O `login.php` busca o usuário pelo e-mail na tabela `usuarios` (a mesma do cadastro de usuários) e confere a senha com `password_verify`. Se estiver certo, guarda o usuário em `$_SESSION['usuario']`. Todos os outros arquivos PHP chamam `verificarLogin()` e, se não tiver ninguém na sessão, respondem 401. Quando o JavaScript recebe 401 ele manda a pessoa para o `login.html`.

## Tabelas

usuarios, categorias, fornecedores, produtos, clientes, pedidos, itens_pedido, movimentacoes

## Regras extras

| Formulário | Regra |
|---|---|
| Usuário | senha com 6 caracteres e um número; e-mail não repete; não exclui o usuário logado |
| Produto | venda não pode ser menor que custo; calcula a margem; SKU não repete; mostra "Repor" quando o estoque está abaixo do mínimo |
| Fornecedor | valida o CNPJ; prazo de pagamento conforme a UF |
| Cliente | valida o CPF; exige maioridade; classifica cliente sênior (60+) |
| Pedido | 10% de desconto para 10+ unidades, +5% no Pix; não vende mais do que tem no estoque; tira do estoque |
| Movimentação | saída e ajuste exigem justificativa; atualiza o estoque; responsável é o usuário logado |
