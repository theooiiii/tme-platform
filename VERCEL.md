# Deploy da TME na Vercel

## Status

A TME foi preparada para deploy serverless na Vercel usando o runtime comunitario `vercel-php`.

Arquivos principais:

- `vercel.json`
- `api/index.php`
- `api/php.ini`
- `.vercelignore`
- `composer.json`

## Como funciona

- Rotas web e API entram por `api/index.php`.
- `api/index.php` carrega `public/index.php`.
- Assets continuam em `/assets`.
- Uploads publicos legados sao roteados por `/uploads`.
- Cache temporario usa `/tmp` quando `VERCEL` ou `TME_SERVERLESS` estiver ativo.

## Variaveis de ambiente

Configure na Vercel:

```env
APP_NAME="TME - Theo Mind Educacional"
APP_URL=https://seu-dominio.com
APP_DEBUG=false
APP_TIMEZONE=America/Sao_Paulo

SESSION_SECURE=true
SESSION_SAMESITE=Lax
CSP_ENABLED=true
LOGIN_MAX_ATTEMPTS=5
LOGIN_DECAY_SECONDS=900
ANALYTICS_CACHE_TTL=90

DATABASE_URL=mysql://usuario:senha@host:3306/tme_platform
```

Tambem e possivel usar:

```env
MYSQL_URL=mysql://usuario:senha@host:3306/tme_platform
```

## Banco de dados

Nao use `localhost` em producao. Use um MySQL/MariaDB remoto, por exemplo:

- PlanetScale compativel MySQL.
- Railway MySQL.
- Aiven MySQL.
- DigitalOcean Managed MySQL.
- AWS RDS MySQL.

Depois de criar o banco remoto:

1. Importe `database/tme_initial.sql`.
2. Aplique todas as migrations em ordem.
3. Configure `DATABASE_URL` na Vercel.

## Uploads

Vercel Functions possuem filesystem efemero. Uploads gravados em `public/uploads` funcionam para ambiente local, mas nao devem ser usados como storage definitivo em producao serverless.

Recomendacao para producao:

- S3, Cloudflare R2, Supabase Storage ou outro storage externo.
- Servir arquivos privados via controller com checagem de permissao.

## Limites importantes

- PHP nao e runtime oficial nativo da Vercel; esta configuracao usa runtime comunitario.
- Funcoes serverless nao devem executar tarefas longas.
- Jobs, relatorios pesados e IA devem ir para fila externa.
- Storage local nao e permanente.

## Checklist de deploy

1. Conectar repositorio GitHub na Vercel.
2. Definir framework como `Other`.
3. Confirmar `vercel.json`.
4. Configurar variaveis de ambiente.
5. Apontar dominio.
6. Importar banco remoto.
7. Acessar `/api/v1/health`.
8. Testar `/login`, `/portal`, `/cursos`, `/biblioteca`, `/eventos`.
