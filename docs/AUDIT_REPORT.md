# Auditoria tecnica da TME

Data: 2026-06-18
Projeto: TME - Theo Mind Educacional
Repositorio publico informado: `https://github.com/theooiiii/tme-platform`
Workspace auditado: `C:\Users\Danielle\Documents\tme-plataform`

## 1. Sumario executivo

A TME ja possui uma base funcional ampla: MVC proprio em PHP, autenticacao, aprovacoes, portal, cursos, matriculas, progresso, atividades, biblioteca, certificados, gamificacao, comunidade, eventos, turmas, frequencia, provas, chat, financeiro, notificacoes e analytics.

O projeto esta em um estagio de prototipo funcional expandido, nao ainda em produto SaaS enterprise pronto para milhares de usuarios. O principal valor existente e que muitos fluxos de produto ja estao modelados. O principal risco e que a arquitetura cresceu horizontalmente em controllers grandes, rotas planas e regras repetidas, sem camadas formais de validacao, repositorio, eventos, filas, testes automatizados e isolamento multi-tenant.

Esta auditoria recomenda evolucao em fases. A primeira fase aplicada nesta entrega adiciona fundacao de seguranca, rate limit de login, protecao de uploads, documentacao e uma migration base para multi-instituicao, CRM, suporte, automacoes, API e auditoria.

## 2. Estado atual identificado

### Estrutura

- `app/controllers`: muitos controllers concentrando validacao, upload, regras de negocio, logs e redirecionamento.
- `app/models`: models acessam PDO diretamente e tambem concentram consultas complexas.
- `app/services`: existe camada de services, mas ainda parcial.
- `config/routes.php`: todas as rotas estao em um unico arquivo grande.
- `database/migrations`: migrations incrementais existem, mas nao ha runner de migrations, versionamento aplicado ou rollback.
- `public/index.php`: front controller simples com autoload artesanal.
- `assets/css`: CSS ja esta modularizado, mas ainda precisa de design tokens mais rigidos e inventario de componentes.
- `modules/` e `ai/python/`: existem como placeholders de expansao.

### Pontos positivos

- Sem Laravel, como solicitado originalmente.
- Uso de PDO e prepared statements na maior parte do codigo.
- CSRF presente nos principais formularios.
- Sessao com `session_regenerate_id(true)` apos login.
- Roles e aprovacoes de conta ja implementados.
- Muitas tabelas de produto ja existem.
- README e scripts de validacao ja foram iniciados.
- Estrutura visual ja foi separada em arquivos CSS por responsabilidade.

## 3. Achados criticos

### 3.1 Arquitetura

Severidade: alta

O projeto funciona como MVC artesanal, mas ainda nao tem:

- Container de injecao de dependencias.
- Request/Response objects.
- Repository Pattern formal.
- Validation Layer central.
- Event Dispatcher.
- Queue Worker.
- API layer separada.
- Politicas centralizadas de upload.
- Testes automatizados.

Impacto: cada modulo novo aumenta duplicacao e risco de regressao. Controllers como `AdminCourseController`, `ActivityController`, `LibraryController` e `ExamController` acumulam responsabilidades demais.

Recomendacao:

1. Criar `app/Repositories`.
2. Criar `app/Validators`.
3. Criar `app/Events` e `app/Listeners`.
4. Criar `app/Http/Request` e `app/Http/Response`.
5. Migrar controllers grandes por modulo, sem reescrever tudo de uma vez.

### 3.2 Multi-tenant ausente

Severidade: critica

Existe tabela de instituicoes, mas nao ha isolamento SaaS real por organizacao. Usuarios, cursos, biblioteca, eventos, planos, posts, turmas, notificacoes e financeiro ainda operam essencialmente em escopo global.

Impacto:

- Risco de vazamento de dados entre escolas/empresas.
- Dificuldade para dominio proprio, tema proprio e faturamento por cliente.
- Falta de base para SaaS comercial.

Recomendacao:

- Introduzir `organizations`, `organization_domains`, `organization_members`.
- Adicionar `organization_id` nas entidades principais.
- Criar `TenantResolver` por dominio/subdominio/sessao.
- Aplicar filtros obrigatorios por tenant em repositories.
- Impedir queries diretas em controllers depois da migracao.

### 3.3 Uploads publicos

Severidade: alta

Arquivos enviados ficam dentro de `public/uploads`. Ha validacoes pontuais, mas a politica esta duplicada e nao ha controle central contra extensoes executaveis em todos os fluxos.

Impacto:

- Risco de arquivo malicioso ser servido ou executado se o servidor estiver mal configurado.
- Risco de permissao inadequada para materiais privados.

Acao aplicada nesta entrega:

- Adicionado `public/uploads/.htaccess` bloqueando extensoes executaveis.

Recomendacao complementar:

- Criar `StorageService` e `UploadService`.
- Armazenar arquivos privados fora de `public`.
- Servir downloads por controller com checagem de permissao.
- Normalizar allowlist por tipo de modulo.

### 3.4 Sessao e login

Severidade: alta

Antes desta entrega, a sessao era iniciada diretamente com `session_start()` sem parametros centrais de cookie.

Acao aplicada nesta entrega:

- Criado `Security::configureSession()`.
- Cookies de sessao com `HttpOnly`, `SameSite`, `secure` quando HTTPS e `use_strict_mode`.
- Criados headers de seguranca.
- Criado `RateLimiter`.
- Login protegido contra tentativas repetidas.

Recomendacao complementar:

- Registrar tentativas de login em tabela.
- Criar bloqueio inteligente por risco.
- Implementar 2FA.
- Implementar recuperacao de senha com token.
- Politica de senha forte e historico de senha.

### 3.5 CSRF duplicado

Severidade: media

Varios controllers possuem metodos proprios ou chamadas repetidas de verificacao CSRF.

Impacto:

- Mais pontos para erro.
- Dificuldade de manter mensagens e comportamento consistentes.

Recomendacao:

- Criar `CsrfMiddleware`.
- Rotas POST/PUT/PATCH/DELETE devem aplicar CSRF automaticamente.
- Remover guards duplicados aos poucos.

### 3.6 Banco de dados

Severidade: alta

O banco tem cerca de 50 tabelas e cobre muitos modulos. O problema nao e excesso de tabelas, e sim ausencia de:

- versionamento aplicado;
- tenant isolation;
- indices compostos por tenant/status/data;
- convencao unica para timestamps;
- convencao unica para status;
- documentacao relacional;
- estrategia de arquivamento e auditoria.

Observacao importante:

- `institutions` representa instituicoes educacionais, mas nao deve ser usado como tenant SaaS principal. Uma organizacao SaaS pode ser escola, universidade, empresa ou criador de cursos. Por isso a camada correta e `organizations`.

### 3.7 Performance

Severidade: media/alta

Riscos:

- Listagens sem paginacao consistente.
- Muitos `SELECT *`.
- Dashboards com multiplas queries por request.
- Chat baseado em refresh.
- Sem cache de metricas.
- Sem fila para notificacoes, e-mails, certificados e relatorios.
- Assets sem manifest/versionamento/minificacao.

Recomendacao:

- Paginacao obrigatoria em todos os repositories.
- Cache de metricas de dashboard.
- Jobs para tarefas demoradas.
- Worker CLI para filas.
- Query plans para relatorios.
- CDN ready para assets estaticos.

### 3.8 UX/UI

Severidade: media

A refatoracao visual anterior melhorou bastante a base, mas ainda ha:

- Hierarquia visual inconsistente em alguns modulos.
- Tabelas densas sem filtros/paginacao universal.
- Estados vazios nao padronizados em todos os modulos.
- Comunidade/chat ainda com experiencia basica.
- Player de aula ainda nao e player profissional.
- Dashboards precisam de metricas mais acionaveis.

Recomendacao:

- Criar design system documentado.
- Componentizar page header, cards, tabelas, filtros, tabs, badges, alerts e empty states.
- Criar inventario de telas principais.
- Validar mobile com screenshots.

### 3.9 Codigo morto ou incompleto

Severidade: media

Foram identificadas areas planejadas ou parcialmente implementadas:

- `ai/python/`: preparado, sem integracao real.
- `modules/`: placeholder de modulos.
- IA educacional: ainda conceitual.
- Marketplace/creator economy: estrutura inicial, sem split real 80/20 em gateway.
- Automacoes de e-mail: ainda nao ha worker nem templates transacionais.
- Relatorios PDF/Excel/CSV: ainda nao ha gerador central.
- PWA/offline/push: ausente.
- API REST/Swagger/tokens: ausente antes da migration de fundacao.
- Central de suporte: ausente antes da migration de fundacao.
- CRM: ausente antes da migration de fundacao.

### 3.10 Duplicacoes

Severidade: media

Duplicacoes principais:

- CSRF em controllers.
- Uploads por modulo.
- Validacoes inline.
- Logs chamados manualmente por cada fluxo.
- Regras de permissao em rotas e em controllers.
- Montagem de filtros SQL nos models.
- Layouts de tabela/cards parecidos em views diferentes.

## 4. Tabelas candidatas a revisao

Nao foram identificadas tabelas claramente desnecessarias para a visao de produto atual. O que existe e uma mistura de tabelas maduras e tabelas preparatorias.

Tabelas preparatorias que devem ser mantidas, mas documentadas:

- `ai_requests`
- `creator_wallets`
- `content_moderation`
- `subject_teachers`
- `chat_channels`
- `gamification_events`

Tabelas que precisam de escopo tenant:

- `users`
- `courses`
- `classes`
- `subjects`
- `library_items`
- `events`
- `posts`
- `plans`
- `transactions`
- `notifications`
- `logs`

## 5. Controllers excessivos ou grandes

Prioridade de refatoracao:

1. `AdminCourseController`: separar cursos, modulos, aulas, materiais e upload.
2. `ExamController`: separar admin/professor/aluno/correcao.
3. `ActivityController`: separar criacao, entrega, correcao e boletim.
4. `LibraryController`: separar catalogo, admin, favoritos e moderacao.
5. `CommunityController`: separar feed, moderacao e interacoes.
6. `EventController`: separar catalogo, admin, inscricoes e certificados.

## 6. Riscos de seguranca priorizados

1. Upload publico sem StorageService central.
2. Ausencia de rate limiting antes desta entrega.
3. Sessao sem parametros centrais antes desta entrega.
4. Falta de 2FA.
5. Falta de recuperacao de senha segura.
6. Falta de CSP/headers antes desta entrega.
7. Falta de politica de senha forte.
8. Falta de tenant isolation.
9. Falta de auditoria imutavel.
10. Falta de testes automatizados de permissao.

## 7. Roadmap tecnico recomendado

### Fase 1 - Fundacao de producao

- Security bootstrap.
- Rate limiting.
- Headers.
- Upload hardening.
- Documentacao base.
- Migration SaaS foundation.
- CI com PHP lint e validacao.

Status: iniciado nesta entrega.

### Fase 2 - Arquitetura interna

- Request/Response.
- Container simples.
- Repositories.
- Validators.
- Services por dominio.
- Middleware CSRF central.
- Event Dispatcher.

### Fase 3 - Multi-tenant

- TenantResolver.
- Escopo por organizacao.
- Admin global vs admin da organizacao.
- Tema/logotipo/dominio por organizacao.
- Migracao de dados existentes para organizacao padrao.

### Fase 4 - LMS comercial

- Trilhas.
- Pre-requisitos.
- Player profissional.
- Provas avancadas.
- Historico academico.
- Certificados com QR Code.

### Fase 5 - SaaS e monetizacao

- CRM educacional.
- Planos e assinaturas reais.
- Gateways Pix/Mercado Pago/Stripe/PayPal.
- Cupons.
- Relatorios financeiros.
- Split creator 80/20.

### Fase 6 - Escala

- Filas.
- Cache.
- Observabilidade.
- API REST.
- Swagger/OpenAPI.
- PWA.
- Push notifications.
- Deploy com HTTPS, backups e monitoramento.

## 8. Decisao tecnica

Nao e recomendavel tentar transformar tudo em produto enterprise com uma unica reescrita. A melhor abordagem e evolucao por camadas, protegendo o que ja funciona.

A TME deve seguir como MVC proprio, mas com disciplina de arquitetura:

- Controller fino.
- Service para regra de negocio.
- Repository para persistencia.
- Validator para entrada.
- Event para efeitos colaterais.
- Queue para tarefas demoradas.
- Middleware para seguranca e permissao.
- View sem regra de negocio.

## 9. Entrega aplicada nesta fase

- `Security.php`: sessao segura e headers.
- `RateLimiter.php`: limitador de tentativas.
- `AuthController`: login com rate limit.
- `.env.example`: novas variaveis de seguranca.
- `public/uploads/.htaccess`: bloqueio de scripts em uploads.
- `2026_06_18_saas_foundation.sql`: base SaaS/multi-instituicao, CRM, suporte, automacoes, API e auditoria.
- Documentacao tecnica inicial.
