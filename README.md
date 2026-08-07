# Guitton

Site institucional e blog da Guitton, criado para apresentar os servicos da empresa e compartilhar conteudos sobre imoveis, contratos e patrimonio.

O projeto reune paginas institucionais, blog publico e um painel administrativo para gerenciar as publicacoes.

## Assistente de escrita

O editor administrativo pode criar, melhorar ou continuar uma publicação com o Gemini. Copie `.env.example` para `.env` e configure `GEMINI_API_KEY`. A chave é lida somente pelo PHP no servidor e nunca é enviada ao navegador.

## Envio de e-mails

A newsletter salva as inscrições e envia uma confirmação imediata. Configure no `.env` as variáveis `SMTP_HOST`, `SMTP_PORT`, `SMTP_USERNAME`, `SMTP_PASSWORD`, `SMTP_ENCRYPTION`, `SMTP_FROM_EMAIL` e `SMTP_FROM_NAME` com os dados fornecidos pelo provedor de e-mail.
