# Guitton — site institucional e blog

Site responsivo da Guitton com páginas institucionais, blog público em PHP, painel administrativo, newsletter e SEO técnico. O projeto foi pensado para hospedagem compartilhada Apache/HostGator e não depende de Node.js ou de processos permanentes no servidor.

## Tecnologias

- HTML5 semântico, CSS e JavaScript;
- PHP 8.1 ou superior, com `dom`, `fileinfo`, `intl`, `json`, `mbstring` e `openssl`;
- arquivos JSON com gravação atômica e bloqueio de arquivo;
- Apache, `.htaccess` e deploy pelo Git Version Control do cPanel;
- SMTP autenticado para notificações da newsletter.

## Estrutura

- `index.html`, `pos-locacao.html`, `consultoria.html`, `parceiros.html`: páginas institucionais;
- `blog.php`: busca, destaque, grade, paginação e newsletter;
- `post.php`: notícia individual por slug;
- `admin/`: painel, primeiro acesso, editor, prévia e documentação;
- `app/`: autenticação, segurança, armazenamento, conteúdo, newsletter e SMTP;
- `partials/`: cabeçalho e rodapé compartilhados das páginas PHP;
- `data/`: dados persistentes protegidos pelo Apache;
- `assets/uploads/`: imagens enviadas no painel;
- `sitemap.php`, `robots.txt` e `404.php`: SEO e tratamento de erros;
- `.cpanel.yml`: publicação automatizada sem sobrescrever dados privados.

## Teste local

Copie `.env.example` para `.env` e substitua apenas os valores locais. Nunca faça commit do `.env`.

```bash
php -S 127.0.0.1:8080
```

Abra `http://127.0.0.1:8080/blog.php`. O servidor interno do PHP não interpreta `.htaccess`; as regras de proteção de diretório devem ser validadas também no Apache da hospedagem.

Para executar a suíte de lógica:

```bash
php tests/run.php
```

## Primeiro administrador

1. Defina um `ADMIN_SETUP_TOKEN` longo e aleatório no `.env` local do servidor ou nas variáveis de ambiente do PHP.
2. Acesse `https://guitton.com.br/admin/` após o primeiro deploy.
3. Informe o token, escolha o usuário e crie uma senha com pelo menos 6 caracteres, uma letra, um número e um símbolo.
4. Depois da criação, a configuração inicial é bloqueada automaticamente.

O usuário e o hash de senha ficam em `data/admins.json`, arquivo ignorado pelo Git. Para trocar a senha, entre no painel e use o item **Senha**.

## SMTP e newsletter

Configure no `.env` do servidor:

```dotenv
SMTP_HOST=smtp.exemplo.com
SMTP_PORT=587
SMTP_USERNAME=usuario_exemplo
SMTP_PASSWORD=troque_esta_senha
SMTP_ENCRYPTION=tls
SMTP_FROM_EMAIL=contato@exemplo.com
SMTP_FROM_NAME=Guitton
SITE_URL=https://guitton.com.br
ADMIN_SETUP_TOKEN=crie_um_token_longo_e_aleatorio
```

Use `tls` normalmente na porta 587 ou `ssl` quando o provedor exigir conexão segura direta. A publicação continua funcionando se o SMTP estiver indisponível, e a edição de uma notícia já publicada nunca dispara novamente o aviso.

## Domínio e cPanel

O domínio adicional deve apontar o Document Root para a pasta `guitton.com.br` dentro da conta. O repositório deve ser clonado pelo **Git Version Control** do cPanel em uma pasta separada, por exemplo `repositories/Guitton`; não clone o Git dentro da pasta pública.

Fluxo de atualização:

1. Faça commit e push para a branch configurada no cPanel.
2. No Git Version Control, clique em **Update from Remote**. Isso baixa os commits para a cópia do repositório, mas ainda não altera o site publicado.
3. Clique em **Deploy HEAD Commit**. Isso executa `.cpanel.yml` e copia os arquivos públicos para `$HOME/guitton.com.br`.

O deploy cria `data` e `assets/uploads`, atualiza o código e preserva `admins.json`, inscritos, tentativas de login, configurações, uploads e o `posts.json` que já existir em produção. O `posts.json` vazio do repositório só é copiado no primeiro deploy.

## Publicações e imagens

Para cada notícia, use preferencialmente imagem horizontal de 1200 × 675 px, WebP ou JPEG, bem iluminada, nítida e abaixo de 500 KB. O painel aceita JPEG, WebP e PNG válidos até 5 MB, renomeia os arquivos aleatoriamente e bloqueia extensões executáveis.

O texto alternativo é opcional, mas recomendado por acessibilidade e SEO. Rascunhos não aparecem no site nem no sitemap.

## SEO e compartilhamento

- confira `https://guitton.com.br/sitemap.php` no navegador e envie essa URL ao Google Search Console;
- valide as prévias no [Facebook Sharing Debugger](https://developers.facebook.com/tools/debug/) e no [LinkedIn Post Inspector](https://www.linkedin.com/post-inspector/);
- as notícias usam a própria imagem nas tags Open Graph, com fallback para `assets/guitton-social.jpg`;
- buscas usam `noindex, follow`; 404 e painel usam `noindex, nofollow`.

## Segurança

- não versione `.env`, credenciais SMTP, usuários, hashes, inscritos, logs, sessões, uploads, rascunhos ou tentativas de login;
- `app`, `data` e `partials` são bloqueados por `.htaccess` e também pelo `.htaccess` raiz;
- senhas usam `password_hash`/`password_verify`, sessão segura, CSRF, honeypot e limitação progressiva de login;
- antes de cada push, execute `git status`, revise `git diff --cached` e faça uma varredura por padrões de segredo;
- se uma credencial real já tiver entrado em qualquer commit, revogue-a imediatamente e limpe todo o histórico antes de publicar o repositório.

O guia não técnico do painel está em [GUIA-DO-CLIENTE.md](GUIA-DO-CLIENTE.md) e também dentro de `/admin/docs.php`.
