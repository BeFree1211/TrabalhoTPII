# Questionário de Desenvolvimento

**Aluno:** Gabriely Vieira Dias
**Grupo:** Gabriely Vieira Dias e Mariana Ferreira Alves
**Tema/projeto:** Giro – sistema administrativo de controle de estoque

> Revise cada resposta antes de entregar e corrija o que não corresponder ao que
> você realmente fez.

---

## 1. Qual foi exatamente a sua contribuição no projeto?

Defini o tema do sistema (controle de estoque de uma pequena loja) e as
funcionalidades de cada um dos cinco formulários: cadastro de produtos, de
fornecedores e de clientes, registro de pedidos e lançamento de movimentações de
entrada e saída. Trabalhei no código HTML dos formulários, no JavaScript
responsável pelo envio das requisições e nos arquivos PHP de validação, usando a
IA como apoio para dúvidas de estrutura e de sintaxe. Também defini a identidade
visual do sistema (nome, logotipo, paleta de cores e tipografia) e testei cada
formulário no navegador com o servidor local do PHP, conferindo tanto os casos
válidos quanto os inválidos.

Na parte de versionamento, criei o repositório público no GitHub, instalei e
configurei o Git, e executei os comandos da branch `main` e da `branch-aluno-1`,
incluindo `init`, `add`, `commit`, `push` e `checkout -b`.

## 2. Qual foi a parte do código que você teve mais dificuldade para desenvolver? Por quê?

A parte mais difícil foram as funções `validarCpf()` e `validarCnpj()`, no
arquivo `php/util.php`. A dificuldade começou antes mesmo de escrever o código:
eu não sabia por onde começar nem como era feita a verificação de que um número
de documento é realmente válido. Imaginava que bastaria conferir a quantidade de
dígitos, mas descobri que existe um cálculo específico, no qual cada dígito é
multiplicado por um peso diferente, os resultados são somados e o resto da
divisão por 11 define os dois dígitos verificadores. Entender essa lógica e
depois transformá-la em um laço de repetição que percorre os dígitos na ordem
correta foi o trecho que mais exigiu estudo.

## 3. Escolha uma função ou trecho de código que você desenvolveu e explique, com suas palavras, o que ele faz.

**Arquivo/função:** `php/produto.php` – cálculo da margem

**Explicação:** depois que todos os campos são validados, o arquivo calcula a
margem de lucro do produto com a fórmula `($venda - $custo) / $custo * 100`. O
resultado é arredondado para duas casas decimais e devolvido ao navegador junto
com um alerta: se a margem for menor que 10%, a resposta avisa que o preço deve
ser revisto; caso contrário, informa que a margem está saudável. Antes disso há
uma regra que impede o cadastro quando o preço de venda é menor que o custo, pois
isso representaria prejuízo na venda.

## 4. Você utilizaria outra linguagem para desenvolver o projeto? Se sim, qual e por quê? Se não, por quê?

Sim, eu utilizaria Java, por ter mais familiaridade com essa linguagem. O
projeto poderia ser desenvolvido em Java sem dificuldade, usando Servlets ou o
framework Spring Boot no lugar dos arquivos PHP: a parte do HTML, do CSS e do
JavaScript permaneceria exatamente a mesma, pois o envio das requisições com
`fetch` e a resposta em JSON não dependem da linguagem do servidor. Mudaria
apenas quem recebe a requisição e devolve o JSON.

Por outro lado, reconheço que o PHP foi uma escolha adequada para esta etapa do
trabalho, porque é a linguagem estudada na disciplina e porque exige menos
preparação do ambiente: basta instalar o PHP e executar `php -S localhost:8000`
para que o sistema funcione. Em Java seria necessário instalar o JDK, usar um
gerenciador de dependências como o Maven e configurar um servidor de aplicação,
o que acrescentaria etapas sem trazer benefício para um projeto deste tamanho.

## 5. Quais variáveis, funções ou estruturas de repetição/decisão você considera mais importantes no seu código? Explique a função delas.

- **`responder()` (`php/util.php`)** – é a única porta de saída dos arquivos PHP.
  Monta a resposta em JSON com três partes (`ok`, `erros` e `dados`), define o
  código HTTP e encerra a execução com `exit`. Todos os formulários respondem
  por meio dela, o que garante um formato único para o JavaScript interpretar.

- **`obrigatorios()` (`php/util.php`)** – usa um laço `foreach` para percorrer a
  lista de campos obrigatórios de cada formulário e preencher o array `$erros`
  com a mensagem "Campo obrigatório" para os que vierem vazios.

- **Array `$erros`** – acumula todos os problemas encontrados. A decisão final
  `if ($erros)` verifica se ele tem algum item: se tiver, a resposta é de falha
  com status 422; se estiver vazio, o cadastro é considerado válido.

- **Laço `for` em `validarCpf()` e `validarCnpj()`** – percorre os dígitos do
  documento multiplicando cada um pelo seu peso para calcular os dígitos
  verificadores e compará-los com os digitados.

## 6. Já está planejado o uso de banco de dados? O trabalho entregue já está construído considerando que haverá tal tratativa?

Sim, o uso do banco de dados está planejado, mas, conforme o enunciado, ele não
foi criado nesta etapa. Os formulários já foram construídos considerando sete
entidades: `categorias`, `fornecedores`, `produtos`, `clientes`, `pedidos`,
`itens_pedido` e `movimentacoes`. Os campos de cada formulário correspondem às
colunas previstas para essas tabelas, e os relacionamentos já estão refletidos
nas telas: o formulário de produtos pede categoria e fornecedor, o de pedidos
identifica o cliente pelo CPF e o produto pelo SKU, e o de movimentações
referencia o produto também pelo SKU. Dessa forma, quando o banco for criado, os
campos de texto poderão ser substituídos por listas de seleção alimentadas pelas
tabelas, sem necessidade de refazer os formulários.

## 7. Explique como os dados enviados por um formulário HTML chegam ao PHP.

No HTML, cada formulário tem o atributo `data-endpoint` apontando para o arquivo
PHP que vai recebê-lo (por exemplo, `php/produto.php`), e cada campo tem um
atributo `name`, que é o nome pelo qual o dado será identificado.

Quando o botão é clicado, o JavaScript (`js/app.js`) intercepta o evento
`submit` e executa `evento.preventDefault()`, impedindo o recarregamento padrão
da página. Em seguida, `new FormData(form)` lê todos os campos preenchidos e
monta um pacote com os pares nome/valor. Esse pacote é enviado pela função
`fetch`, com `method: 'POST'`, para o endereço indicado no `data-endpoint`.

Do lado do servidor, o PHP recebe esses dados no array superglobal `$_POST`, em
que a chave é exatamente o `name` do campo no HTML. No projeto, a leitura é
feita pela função `campo()` do arquivo `util.php`, que faz `$_POST[$nome]` e
aplica `trim()` para remover espaços em branco. O PHP valida os dados e devolve
a resposta em JSON, que o JavaScript utiliza para exibir os erros nos campos ou
o resultado do processamento.

## 8. No código de vocês, qual é a diferença entre $_GET e $_POST? Vocês utilizaram algum deles? Por quê?

A diferença está na forma como os dados trafegam. Com `$_GET`, os valores vão na
própria URL, ficando visíveis na barra de endereços e no histórico do navegador,
além de terem limite de tamanho menor; por isso ele é indicado para buscas e
filtros, que apenas consultam informações. Com `$_POST`, os dados vão no corpo
da requisição, não aparecem na URL e admitem um volume maior; por isso é o
método indicado para operações que cadastram ou alteram informações.

No nosso projeto utilizamos apenas o `$_POST`. Todos os formulários são enviados
pelo JavaScript com `method: 'POST'`, e os arquivos PHP leem os dados por meio de
`$_POST` dentro da função `campo()`. A escolha se deve ao fato de os formulários
tratarem de cadastros com dados sensíveis, como CPF, CNPJ, preços e valores de
pedidos, que não devem ficar expostos na URL. Além disso, a função `iniciar()`
do arquivo `util.php` bloqueia qualquer outro método: se a requisição não for
POST, ela responde com a mensagem "Use o método POST" e o código de erro 405.

## 9. Escolha um erro ou problema que ocorreu durante o desenvolvimento e explique

**Qual era o problema?** Logo após instalar o Git pelo gerenciador de pacotes do
Windows, o comando `git --version` continuava retornando a mensagem de que o
termo não era reconhecido como cmdlet ou programa operável, mesmo tendo sido
exibida a confirmação "Successfully installed". O mesmo aconteceu depois com o
PHP.

**Como descobrimos a causa?** Verificamos que a instalação havia sido concluída
sem erros, o que indicava que o problema não era a instalação em si, mas o
terminal. A variável de ambiente PATH, que informa ao sistema onde procurar os
programas, é lida apenas no momento em que a janela do PowerShell é aberta.
Como a janela estava aberta desde antes da instalação, ela ainda trabalhava com
a lista antiga de caminhos.

**Como resolvemos?** Fechamos a janela do PowerShell e abrimos uma nova. Ao
repetir o comando, o Git respondeu normalmente com a versão 2.55.0, e o mesmo
procedimento resolveu o caso do PHP 8.4.25.

## 10. Houve algum trecho de código que vocês encontraram na internet, documentação ou outra fonte e adaptaram?

( ) Não   (X) Sim

Sim. O principal caso foi o algoritmo de validação dos dígitos verificadores do
CPF e do CNPJ. O problema que esse código resolvia era identificar se o número
digitado é realmente um documento válido, e não apenas uma sequência com a
quantidade certa de dígitos, como "111.111.111-11". O cálculo segue uma regra
oficial, definida pela Receita Federal e amplamente publicada, que não teria
como ser deduzida por nós.

As alterações que fizemos foram de adaptação ao projeto: reunimos a lógica em
duas funções próprias, `validarCpf()` e `validarCnpj()`, dentro do arquivo
`php/util.php`, para que pudessem ser reaproveitadas por mais de um formulário;
criamos a função auxiliar `soDigitos()`, que remove pontos, barras e hifens com
expressão regular, permitindo que o usuário digite o documento formatado;
acrescentamos a verificação de dígitos repetidos; e fizemos com que as funções
retornassem apenas verdadeiro ou falso, de modo que a mensagem de erro fosse
definida por cada formulário e enviada no formato JSON usado em todo o sistema.

Também consultamos a documentação da MDN sobre `fetch` e `FormData` para
entender como enviar os dados do formulário sem recarregar a página.

## 11. Houve utilização de ferramentas de IA durante o desenvolvimento?

( ) Não   (X) Sim

Sim. Utilizei o Claude como apoio durante o desenvolvimento, principalmente para
orientar a estrutura do projeto, esclarecer dúvidas de sintaxe, explicar trechos
de código e revisar a redação do relatório. As respostas recebidas foram
analisadas e aplicadas ao projeto, e em seguida testei o sistema no navegador,
com o servidor local do PHP, para confirmar o funcionamento de cada formulário,
tanto com dados válidos quanto inválidos.

## 12. Escolha um trecho do seu próprio código e responda: o que aconteceria se esta linha fosse removida?

**Linha:** `evento.preventDefault();` (arquivo `js/app.js`)

**Resposta:** essa linha cancela o comportamento padrão do formulário HTML, que
é recarregar a página enviando os dados por conta própria. Se ela fosse
removida, ao clicar no botão o navegador recarregaria a página imediatamente.
Com isso, o envio feito pelo `fetch` seria interrompido antes de terminar e a
área de resultado seria apagada junto com a página, de modo que as mensagens de
erro e o resultado do cálculo nunca apareceriam na tela.

## 13. Se eu alterasse esta parte do projeto, o que precisaria ser alterado em outra parte do código?

Tomando como exemplo a inclusão de um novo campo em um formulário, seriam
necessárias alterações em três pontos, pois o projeto é dividido em camadas:

1. No arquivo HTML, criar o `<label>`, o `<input>` com o atributo `name` e a
   `<div class="erro">` correspondente;
2. No arquivo PHP do formulário, incluir o novo campo na lista da função
   `obrigatorios()` e acrescentar as validações específicas dele;
3. No arquivo `js/app.js`, acrescentar o novo campo ao objeto `ROTULOS`, caso
   ele deva aparecer com um nome amigável na área de resultado.

O ponto mais sensível é o atributo `name` do campo no HTML, pois é exatamente por
ele que o PHP localiza o valor dentro de `$_POST`. Se o `name` for alterado em um
lugar e não no outro, o campo passa a chegar vazio ao servidor e o sistema
acusa, equivocadamente, que ele não foi preenchido.

## 14. Qual o maior problema que ocorreu no desenvolvimento, como você investigou e corrigiu?

O maior problema não foi um erro de execução, e sim chegar ao resultado visual
que queríamos. A primeira versão do sistema funcionava corretamente, mas tinha
uma aparência genérica, e não era simples traduzir em código a ideia que
tínhamos na cabeça. Havia ainda a dificuldade de encontrar um meio-termo entre
as nossas preferências, já que somos duas pessoas decidindo sobre cores, fontes
e espaçamentos.

A investigação foi feita por tentativa e comparação: mantivemos o servidor local
em execução e fomos alterando o arquivo `css/style.css`, recarregando a página a
cada ajuste para avaliar o efeito de cada mudança. Em vez de mexer em cada tela
separadamente, centralizamos as cores em variáveis CSS declaradas no `:root`,
como `--rosa-escuro` e `--cinza-900`, o que permitiu testar uma paleta inteira
alterando poucas linhas e manteve as seis páginas sempre consistentes entre si.

A correção veio dessa organização: definimos a paleta em cinza, rosa e branco,
escolhemos a tipografia, criamos o logotipo em SVG e padronizamos o espaçamento
dos formulários. Um cuidado adicional foi conferir o contraste entre o texto e o
fundo, pois o tom de rosa que achávamos mais bonito não oferecia leitura
confortável com texto branco; por isso adotamos um rosa mais escuro nos botões.

Em paralelo, também exigiram atenção as regras específicas de cada formulário,
como o cálculo da margem do produto, o desconto por quantidade e forma de
pagamento e o efeito da movimentação no estoque. Cada uma precisou ser testada
com valores propositalmente errados, para confirmar que o sistema recusava o
envio e exibia a mensagem correta.

## 15. Existe alguma parte do código que você não consegue explicar completamente?

( ) Não   (X) Sim

Sim: o cálculo dos dígitos verificadores do CPF e do CNPJ, nas funções
`validarCpf()` e `validarCnpj()` do arquivo `php/util.php`. Consigo explicar o
que essas funções fazem e como estão organizadas: elas removem a formatação do
número, descartam sequências de dígitos repetidos, percorrem os dígitos com um
laço multiplicando cada um por um peso, somam os resultados e comparam o resto
da divisão por 11 com os dois últimos dígitos informados. O que não consigo
explicar completamente é a origem da regra em si, ou seja, por que os pesos são
exatamente esses e por que a divisão é por 11. Essa definição é estabelecida
pela Receita Federal, e nós a aplicamos como especificação, sem demonstrá-la
matematicamente.

## 16. Em uma escala de 1 a 5, quanto você considera que consegue explicar e modificar o código que está entregando?

**Resposta: 4** — Consigo explicar e fazer algumas alterações.

## 17. Se fosse necessário adicionar uma nova funcionalidade ao projeto amanhã, qual parte do código você precisaria modificar primeiro?

A funcionalidade que imaginei é um **alerta de reposição**: uma tela que informa
quais produtos estão com a quantidade em estoque abaixo do estoque mínimo
cadastrado, para que a loja saiba a hora de comprar novamente do fornecedor. Ela
faz sentido no tema porque o sistema já pede o estoque mínimo no cadastro do
produto e já registra entradas e saídas nas movimentações.

A primeira parte que eu precisaria modificar seria o lado servidor, criando um
novo arquivo em `php/`, por exemplo `php/reposicao.php`, que receberia a
requisição e devolveria o resultado em JSON. Começaria por ele porque é onde
fica a regra de negócio, ou seja, a comparação entre a quantidade atual e o
estoque mínimo. Esse arquivo reaproveitaria as funções já existentes em
`php/util.php`, como `iniciar()`, `campo()` e `responder()`, mantendo o mesmo
formato de resposta do restante do sistema.

Em seguida criaria a página `reposicao.html`, com o mesmo cabeçalho, o mesmo
menu e um formulário apontando para o novo arquivo pelo atributo
`data-endpoint`. No `js/app.js` não seria necessário criar nada novo, pois o
código já é genérico e atende qualquer formulário que tenha esse atributo;
bastaria acrescentar ao objeto `ROTULOS` os nomes amigáveis dos novos campos da
resposta. Por fim, adicionaria o link da nova página ao menu das seis páginas já
existentes, para não quebrar a navegação exigida pelo enunciado.

Vale observar que, enquanto o banco de dados não for criado, essa tela
trabalharia apenas com os valores informados no próprio formulário. A versão
completa da funcionalidade depende das tabelas `produtos` e `movimentacoes`,
previstas no planejamento das sete entidades.
