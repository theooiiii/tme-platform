# Arquitetura da TME

## Visao atual

A TME usa MVC proprio em PHP 8+, MySQL, PDO, HTML, CSS e JavaScript puro.

Fluxo principal:

1. `public/index.php` recebe a requisicao.
2. `.env` e carregado por `Env`.
3. O autoload simples busca classes em `app/core`, `controllers`, `models`, `middleware`, `services` e `helpers`.
4. `Security` configura sessao e headers.
5. `Router` encontra a rota em `config/routes.php`.
6. Middlewares validam autenticacao, roles ou plano premium.
7. Controller executa o caso de uso.
8. Model/Service acessa banco via PDO.
9. View renderiza HTML.

## Fundacao adicionada

- `app/core/Security.php`: configuracao de sessao e headers HTTP.
- `app/core/RateLimiter.php`: limitador simples baseado em arquivos para login e outros fluxos sensiveis.
- `app/core/Container.php`: container simples para evolucao gradual para Dependency Injection.
- `app/core/Validator.php`: validador base para reduzir validacoes inline.
- `app/core/EventDispatcher.php`: dispatcher inicial para eventos de dominio.
- `app/repositories/BaseRepository.php`: base para repositories com PDO, paginacao e filtro por organizacao.
- `app/services/QueueService.php`: gravacao inicial de jobs em `automation_jobs`.
- `app/services/TenantResolver.php`: resolucao inicial de organizacao por usuario ou dominio.
- `app/services/UploadService.php`: politica central para validar e salvar uploads publicos.

## Padrao alvo

O padrao alvo para producao e:

```text
Controller -> Validator -> Service -> Repository -> Model/DTO -> Database
                    |
                    v
              Events/Listeners -> Queue -> Notification/Email/Logs
```

## Responsabilidades

### Controllers

Devem:

- receber a requisicao;
- chamar validadores;
- chamar services;
- escolher view ou redirect.

Nao devem:

- montar SQL complexo;
- manipular upload diretamente;
- decidir regras extensas de negocio;
- disparar muitas notificacoes manualmente.

### Services

Devem concentrar regras de dominio:

- matricula;
- progresso;
- certificados;
- gamificacao;
- financeiro;
- notificacoes;
- provas;
- comunidade.

### Repositories

Devem concentrar persistencia e queries.

Padrao recomendado:

```php
$courses = $courseRepository->publishedForTenant($tenantId, $filters, $page);
```

### Validators

Devem validar entrada antes do service.

Padrao recomendado:

```php
$data = CourseValidator::create($_POST, $_FILES);
```

### Events

Eventos recomendados:

- `UserApproved`
- `CourseEnrolled`
- `LessonCompleted`
- `CourseCompleted`
- `CertificateIssued`
- `ActivitySubmitted`
- `ActivityGraded`
- `PaymentConfirmed`
- `BadgeEarned`

### Queue

Tarefas recomendadas para fila:

- e-mails;
- certificados pesados;
- relatorios;
- notificacoes em massa;
- webhooks;
- importacoes INEP/e-MEC;
- processamento IA.

## Multi-tenant alvo

Entidades principais devem receber `organization_id`:

- users;
- courses;
- classes;
- subjects;
- library_items;
- events;
- posts;
- plans;
- transactions;
- notifications;
- chat_channels;
- logs.

Toda consulta de repository deve filtrar por tenant quando o usuario nao for administrador global.

## API alvo

Criar camada separada:

```text
app/
  Http/
    Api/
      Controllers/
      Middleware/
      Resources/
```

Padroes:

- JSON por padrao;
- tokens em `api_tokens`;
- versionamento `/api/v1`;
- OpenAPI em `docs/openapi.yaml`;
- rate limit por token e IP.

## Seguranca alvo

- CSRF por middleware.
- Rate limit global.
- 2FA.
- Password reset seguro.
- CSP mais restritivo.
- Storage privado fora de `public`.
- Auditoria em `audit_events`.
- Tenant isolation obrigatorio.
- Testes automatizados de permissoes.

## Ordem recomendada de refatoracao

1. Criar `app/Repositories`.
2. Migrar queries de cursos.
3. Migrar queries de matriculas/progresso.
4. Criar `CsrfMiddleware`.
5. Criar `UploadService`.
6. Criar `EventDispatcher`.
7. Criar `QueueService`.
8. Criar `TenantResolver`.
9. Criar API v1.
