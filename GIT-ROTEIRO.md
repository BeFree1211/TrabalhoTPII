# Roteiro de git (rode você mesmo, na ordem, e copie para o relatório)

Antes: crie no GitHub um repositório PÚBLICO vazio (ex.: `estoque-tpi2`).
Troque `<URL>` pela URL do repositório. A Mari usa a `branch-aluno-2`
(ela precisa fazer ao menos um commit real nela).

```bash
cd C:\TrabalhoTPII\estoque-tpi2
git init
git branch -M main
git remote add origin https://github.com/BeFree1211/TrabalhoTPII.git

# 1) main: base do projeto
git add README.md css index.html
git commit -m "Base do projeto: pagina inicial, menu e estilo"
git push -u origin main

# 2) branch do aluno 1 (você)
git checkout -b branch-aluno-1
git add js php produtos.html fornecedores.html clientes.html pedidos.html movimentacoes.html
git commit -m "Formularios, JS e validacoes PHP"
git push -u origin branch-aluno-1

# 3) branch do aluno 2 (Mari) - ela faz a parte dela e dá push
git checkout main
git checkout -b branch-aluno-2
# (Mari altera/cria algo seu, ex.: ajustes no CSS ou o questionário dela)
git add .
git commit -m "Contribuicao do aluno 2"
git push -u origin branch-aluno-2

# 4) merge final na main
git checkout main
git merge branch-aluno-1
git merge branch-aluno-2
git push origin main
```

Dica: `git log --oneline --graph --all` mostra o histórico para o relatório.
