# Changelog

## 2026-06-18

### Adicionado

- Configuracao Vercel com `vercel.json`, `api/index.php`, `.vercelignore`, `api/php.ini` e `composer.json`.
- Guia `VERCEL.md` para deploy serverless.
- API REST inicial em `/api/v1` com health, token, cursos, planos, ranking e validacao de certificados.
- Contrato OpenAPI em `docs/openapi.yaml`.
- Painel admin com usuarios, permissoes, logs, categorias e busca global.
- Player de aula com iframe/video, materiais e anotacoes locais.
- Anexos no chat com migration dedicada.
- QR Code de validacao nos certificados.
- Cache leve para analytics.
- Auditoria tecnica em `docs/AUDIT_REPORT.md`.
- Documentacao de arquitetura em `docs/ARCHITECTURE.md`.
- Documentacao de banco em `docs/DATABASE.md`.
- Planejamento de API em `docs/API.md`.
- Guia de instalacao em `INSTALL.md`.
- Guia de deploy em `DEPLOY.md`.
- `Security.php` para sessao segura e headers HTTP.
- `RateLimiter.php` para controle de tentativas.
- `Container.php`, `Validator.php`, `EventDispatcher.php`, `BaseRepository.php`, `QueueService.php`, `TenantResolver.php` e `UploadService.php` como fundacao arquitetural.
- Variaveis de seguranca em `.env.example`.
- Protecao Apache em `public/uploads/.htaccess`.
- Migration `2026_06_18_saas_foundation.sql` com base para multi-instituicao, CRM, suporte, automacoes, API e auditoria.

### Alterado

- Dashboard admin agora exibe receita, matriculas ativas, taxa de conclusao e atividade 24h.
- Helpers de URL agora suportam ambiente serverless.
- Banco aceita `DATABASE_URL` e `MYSQL_URL`.
- `public/index.php` agora configura seguranca antes de iniciar sessao.
- Login agora possui rate limit por IP e e-mail.
- `.gitignore` permite versionar a protecao `.htaccess` dos uploads.

### Seguranca

- Cookies de sessao recebem configuracao central.
- Headers basicos de seguranca sao enviados pela aplicacao.
- Tentativas repetidas de login sao bloqueadas temporariamente.
- Uploads receberam bloqueio contra execucao de extensoes de script no Apache.
