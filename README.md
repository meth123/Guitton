# Cristina Guitton — site institucional

Site estático responsivo para administração de imóveis, consultoria e parceiros.

## Publicação com GitHub e cPanel

1. Publique o projeto em um repositório GitHub.
2. No Git Version Control do cPanel, clone o repositório na pasta destinada ao projeto.
3. Configure o Document Root do subdomínio para essa pasta.
4. Em cada atualização, use `Update from Remote` e depois `Deploy HEAD Commit`.

O caminho final do subdomínio ainda precisa ser definido antes de criar um `.cpanel.yml`. O blog poderá ser integrado posteriormente em PHP sem alterar as páginas institucionais.

As imagens oficiais do site foram armazenadas localmente em `assets/images`, portanto a publicação não depende de imagens externas.

## Páginas

- `index.html` — início e contato
- `pos-locacao.html` — pós-locação
- `consultoria.html` — consultoria
- `parceiros.html` — parceiros
- `blog.html` — placeholder para o blog futuro
